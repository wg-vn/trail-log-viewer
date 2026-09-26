<?php

namespace WgVn\TrailLogViewer;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use WgVn\TrailLogViewer\Utils\Utils;

class LogIndex
{
    use Concerns\LogIndex\CanCacheIndex;
    use Concerns\LogIndex\CanFilterIndex;
    use Concerns\LogIndex\CanIterateIndex;
    use Concerns\LogIndex\CanSplitIndexIntoChunks;
    use Concerns\LogIndex\HasMetadata;
    use Concerns\LogIndex\PreservesIndexingProgress;

    const DEFAULT_CHUNK_SIZE = 50_000;

    public string $identifier;
    protected int $nextLogIndexToCreate;
    protected int $lastScannedFilePosition;
    protected int $lastScannedIndex;
    protected bool $rebuildRequired;

    public function __construct(
        public LogFile $file,
        protected ?string $query = null
    ) {
        $this->identifier = Utils::shortMd5($this->query ?? '');
        $this->loadMetadata();
    }

    public function addToIndex(int $filePosition, int|CarbonInterface $timestamp, string $severity, ?int $index = null): int
    {
        $logIndex = $index ?? $this->nextLogIndexToCreate ?? 0;

        if ($timestamp instanceof CarbonInterface) {
            $timestamp = $timestamp->timestamp;
        }

        $this->getCurrentChunk()->addToIndex($logIndex, $filePosition, $timestamp, $severity);

        $this->nextLogIndexToCreate = $logIndex + 1;

        if ($this->getCurrentChunk()->size >= $this->getMaxChunkSize()) {
            $this->rotateCurrentChunk();
        }

        return $logIndex;
    }

    public function getPositionForIndex(int $indexToFind): ?int
    {
        $itemsSkipped = 0;

        foreach ($this->getChunkDefinitions() as $chunkDefinition) {
            if (($itemsSkipped + $chunkDefinition['size']) <= $indexToFind) {
                // not in this index, let's move on
                $itemsSkipped += $chunkDefinition['size'];

                continue;
            }

            foreach ($this->getChunkData($chunkDefinition['index']) as $timestamp => $tsIndex) {
                foreach ($tsIndex as $level => $levelIndex) {
                    foreach ($levelIndex as $index => $position) {
                        if ($index === $indexToFind) {
                            return $position;
                        }
                    }
                }
            }
        }

        return null;
    }

    public function get(?int $limit = null): array
    {
        $results = [];
        $itemsAdded = 0;
        $limit = $limit ?? $this->limit;
        $skip = $this->skip;
        $chunkDefinitions = $this->getChunkDefinitions();

        $this->sortKeys($chunkDefinitions);

        foreach ($chunkDefinitions as $chunkDefinition) {
            if (isset($skip)) {
                $relevantItemsInChunk = $this->getRelevantItemsInChunk($chunkDefinition);

                if ($relevantItemsInChunk <= $skip) {
                    $skip -= $relevantItemsInChunk;

                    continue;
                }
            }

            $chunk = $this->getChunkData($chunkDefinition['index']);

            if (empty($chunk)) {
                continue;
            }

            $this->sortKeys($chunk);

            foreach ($chunk as $timestamp => $tsIndex) {
                if (isset($this->filterFrom) && $timestamp < $this->filterFrom) {
                    continue;
                }
                if (isset($this->filterTo) && $timestamp > $this->filterTo) {
                    continue;
                }

                // Timestamp is valid, let's start adding
                if (! isset($results[$timestamp])) {
                    $results[$timestamp] = [];
                }

                foreach ($tsIndex as $level => $levelIndex) {
                    if (! $this->isLevelSelected($level)) {
                        continue;
                    }

                    // severity is valid, let's start adding
                    if (! isset($results[$timestamp][$level])) {
                        $results[$timestamp][$level] = [];
                    }

                    $this->sortKeys($levelIndex);

                    foreach ($levelIndex as $idx => $position) {
                        if ($skip > 0) {
                            $skip--;

                            continue;
                        }

                        $results[$timestamp][$level][$idx] = $position;

                        if (isset($limit) && ++$itemsAdded >= $limit) {
                            break 4;
                        }
                    }

                    if (empty($results[$timestamp][$level])) {
                        unset($results[$timestamp][$level]);
                    }
                }

                if (empty($results[$timestamp])) {
                    unset($results[$timestamp]);
                }
            }
        }

        return $results;
    }

    public function getFlatIndex(?int $limit = null): array
    {
        $results = [];
        $itemsAdded = 0;
        $limit = $limit ?? $this->limit;
        $skip = $this->skip;
        $chunkDefinitions = $this->getChunkDefinitions();

        $this->sortKeys($chunkDefinitions);

        foreach ($chunkDefinitions as $chunkDefinition) {
            if (isset($skip)) {
                $relevantItemsInChunk = $this->getRelevantItemsInChunk($chunkDefinition);

                if ($relevantItemsInChunk <= $skip) {
                    $skip -= $relevantItemsInChunk;

                    continue;
                }
            }

            $chunk = $this->getChunkData($chunkDefinition['index']);

            if (is_null($chunk)) {
                continue;
            }

            $this->sortKeys($chunk);

            foreach ($chunk as $timestamp => $tsIndex) {
                if (isset($this->filterFrom) && $timestamp < $this->filterFrom) {
                    continue;
                }
                if (isset($this->filterTo) && $timestamp > $this->filterTo) {
                    continue;
                }

                $itemsWithinThisTimestamp = [];

                foreach ($tsIndex as $level => $levelIndex) {
                    if (! $this->isLevelSelected($level)) {
                        continue;
                    }

                    foreach ($levelIndex as $idx => $filePosition) {
                        $itemsWithinThisTimestamp[$idx] = $filePosition;
                    }
                }

                $this->sortKeys($itemsWithinThisTimestamp);

                foreach ($itemsWithinThisTimestamp as $idx => $filePosition) {
                    if ($skip > 0) {
                        $skip--;

                        continue;
                    }

                    $results[$idx] = $filePosition;

                    if (isset($limit) && ++$itemsAdded >= $limit) {
                        break 3;
                    }
                }
            }
        }

        return $results;
    }

    public function save(): void
    {
        if (isset($this->currentChunk)) {
            $this->saveChunkToCache($this->currentChunk);
        }

        $this->saveMetadata();
    }

    public function getLevelCounts(): Collection
    {
        $counts = collect([]);

        if (! $this->hasDateFilters()) {
            // without date filters, we can use a faster approach
            foreach ($this->getChunkDefinitions() as $chunkDefinition) {
                foreach ($chunkDefinition['level_counts'] as $severity => $count) {
                    if (! $counts->has($severity)) {
                        $counts[$severity] = 0;
                    }

                    $counts[$severity] += $count;
                }
            }
        } else {
            foreach ($this->get() as $timestamp => $tsIndex) {
                foreach ($tsIndex as $severity => $logIndex) {
                    if (! $counts->has($severity)) {
                        $counts[$severity] = 0;
                    }

                    $counts[$severity] += count($logIndex);
                }
            }
        }

        return $counts;
    }

    public function count(): int
    {
        if ($this->hasDateFilters()) {
            return count($this->getFlatIndex());
        }

        return array_reduce($this->getChunkDefinitions(), function ($sum, $chunkDefinition) {
            foreach ($chunkDefinition['level_counts'] as $level => $count) {
                if ($this->isLevelSelected($level)) {
                    $sum += $count;
                }
            }

            return $sum;
        }, 0);
    }

    public function findPageForTimestamp(int $targetTimestamp, int $perPage, string $direction = 'desc'): int
    {
        $chunkDefinitions = $this->getChunkDefinitions();
        if (empty($chunkDefinitions) || $perPage < 1) {
            return 1;
        }

        $countBefore = 0;

        foreach ($chunkDefinitions as $chunkDefinition) {
            $chunkEarliest = $chunkDefinition['earliest_timestamp'] ?? null;
            $chunkLatest = $chunkDefinition['latest_timestamp'] ?? null;

            if ($direction === 'desc') {
                if (isset($chunkEarliest) && $chunkEarliest > $targetTimestamp) {
                    foreach ($chunkDefinition['level_counts'] as $level => $cnt) {
                        if ($this->isLevelSelected($level)) {
                            $countBefore += $cnt;
                        }
                    }

                    continue;
                }
                if (isset($chunkLatest) && $chunkLatest < $targetTimestamp) {
                    continue;
                }
            } else {
                if (isset($chunkLatest) && $chunkLatest < $targetTimestamp) {
                    foreach ($chunkDefinition['level_counts'] as $level => $cnt) {
                        if ($this->isLevelSelected($level)) {
                            $countBefore += $cnt;
                        }
                    }

                    continue;
                }
                if (isset($chunkEarliest) && $chunkEarliest > $targetTimestamp) {
                    continue;
                }
            }

            $chunkData = $this->getChunkData($chunkDefinition['index']);
            if (empty($chunkData)) {
                continue;
            }

            foreach ($chunkData as $timestamp => $tsIndex) {
                $isBefore = ($direction === 'desc')
                    ? ($timestamp > $targetTimestamp)
                    : ($timestamp < $targetTimestamp);

                if ($isBefore) {
                    foreach ($tsIndex as $level => $levelIndex) {
                        if ($this->isLevelSelected($level)) {
                            $countBefore += count($levelIndex);
                        }
                    }
                }
            }
        }

        return (int) floor($countBefore / $perPage) + 1;
    }

    public function getVelocity(int $bucketCount = 40, ?int $from = null, ?int $to = null): array
    {
        $chunkDefinitions = $this->getChunkDefinitions();
        if (empty($chunkDefinitions)) {
            return [
                'buckets' => [],
                'bucket_size' => 60,
                'total' => 0,
                'earliest_timestamp' => null,
                'latest_timestamp' => null,
            ];
        }

        $earliest = null;
        $latest = null;
        foreach ($chunkDefinitions as $def) {
            if (isset($def['earliest_timestamp']) && $def['earliest_timestamp'] > 0) {
                $earliest = min($earliest ?? $def['earliest_timestamp'], $def['earliest_timestamp']);
            }
            if (isset($def['latest_timestamp']) && $def['latest_timestamp'] > 0) {
                $latest = max($latest ?? $def['latest_timestamp'], $def['latest_timestamp']);
            }
        }

        if (is_null($earliest) || is_null($latest) || $earliest === 0) {
            return [
                'buckets' => [],
                'bucket_size' => 60,
                'total' => 0,
                'earliest_timestamp' => null,
                'latest_timestamp' => null,
            ];
        }

        $rangeFrom = $from ?? $earliest;
        $rangeTo = $to ?? $latest;

        if ($rangeTo <= $rangeFrom) {
            $rangeTo = $rangeFrom + 60;
        }

        $totalSpan = $rangeTo - $rangeFrom;
        $bucketSize = max(1, (int) ceil($totalSpan / max(1, $bucketCount)));

        $bucketMap = [];
        for ($t = $rangeFrom; $t <= $rangeTo; $t += $bucketSize) {
            $bucketKey = (int) (floor(($t - $rangeFrom) / $bucketSize) * $bucketSize + $rangeFrom);
            $bucketMap[$bucketKey] = [
                'timestamp' => $bucketKey,
                'label' => date('H:i', $bucketKey),
                'count' => 0,
                'levels' => [],
            ];
        }

        $total = 0;
        foreach ($chunkDefinitions as $chunkDefinition) {
            if (isset($chunkDefinition['latest_timestamp']) && $chunkDefinition['latest_timestamp'] < $rangeFrom) {
                continue;
            }
            if (isset($chunkDefinition['earliest_timestamp']) && $chunkDefinition['earliest_timestamp'] > $rangeTo) {
                continue;
            }

            $chunkData = $this->getChunkData($chunkDefinition['index']);
            if (empty($chunkData)) {
                continue;
            }

            foreach ($chunkData as $timestamp => $tsIndex) {
                if ($timestamp < $rangeFrom || $timestamp > $rangeTo) {
                    continue;
                }

                $bucketKey = (int) (floor(($timestamp - $rangeFrom) / $bucketSize) * $bucketSize + $rangeFrom);
                if (! isset($bucketMap[$bucketKey])) {
                    $bucketMap[$bucketKey] = [
                        'timestamp' => $bucketKey,
                        'label' => date('H:i', $bucketKey),
                        'count' => 0,
                        'levels' => [],
                    ];
                }

                foreach ($tsIndex as $level => $levelIndex) {
                    if (! $this->isLevelSelected($level)) {
                        continue;
                    }
                    $cnt = count($levelIndex);
                    $bucketMap[$bucketKey]['count'] += $cnt;
                    $bucketMap[$bucketKey]['levels'][$level] = ($bucketMap[$bucketKey]['levels'][$level] ?? 0) + $cnt;
                    $total += $cnt;
                }
            }
        }

        ksort($bucketMap);

        return [
            'buckets' => array_values($bucketMap),
            'bucket_size' => $bucketSize,
            'total' => $total,
            'earliest_timestamp' => $earliest,
            'latest_timestamp' => $latest,
            'from' => $rangeFrom,
            'to' => $rangeTo,
        ];
    }

    protected function sortKeys(array &$array): void
    {
        if ($this->isBackward()) {
            krsort($array);
        } else {
            ksort($array);
        }
    }
}
