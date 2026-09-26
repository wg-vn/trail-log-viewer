<?php

namespace WgVn\TrailLogViewer\Readers;

use Carbon\CarbonInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use WgVn\TrailLogViewer\Direction;
use WgVn\TrailLogViewer\Exceptions\CannotOpenFileException;
use WgVn\TrailLogViewer\Facades\LogViewer;
use WgVn\TrailLogViewer\LevelCount;
use WgVn\TrailLogViewer\LogFile;
use WgVn\TrailLogViewer\LogFileCollection;
use WgVn\TrailLogViewer\Logs\Log;

class MultipleLogReader
{
    protected LogFileCollection $fileCollection;
    protected ?int $limit = null;
    protected ?int $skip = null;
    protected ?string $query = null;
    protected string $direction;
    protected ?array $exceptLevels = null;

    public function __construct(mixed $files)
    {
        if ($files instanceof LogFile) {
            $this->fileCollection = new LogFileCollection([$files]);
        } elseif (is_array($files)) {
            $this->fileCollection = new LogFileCollection($files);
        } else {
            $this->fileCollection = $files;
        }

        $this->setDirection(Direction::Forward);
    }

    public function exceptLevels($levels = null): self
    {
        $this->exceptLevels = $levels;

        return $this;
    }

    public function allLevels(): self
    {
        $this->exceptLevels = null;

        return $this;
    }

    public function setDirection(?string $direction = null): self
    {
        $this->direction = $direction === Direction::Backward
            ? Direction::Backward
            : Direction::Forward;

        if ($this->direction === Direction::Forward) {
            $this->fileCollection = $this->fileCollection->sortByEarliestFirst();
        } elseif ($this->direction === Direction::Backward) {
            $this->fileCollection = $this->fileCollection->sortByLatestFirst();
        }

        return $this;
    }

    public function forward(): self
    {
        return $this->setDirection(Direction::Forward);
    }

    public function reverse(): self
    {
        return $this->setDirection(Direction::Backward);
    }

    public function skip(int $number): self
    {
        $this->skip = $number;

        return $this;
    }

    public function limit(int $number): self
    {
        $this->limit = $number;

        return $this;
    }

    public function search(?string $query = null): self
    {
        $this->query = $query;

        return $this;
    }

    public function getLevelCounts(): array
    {
        $totalCounts = [];

        /** @var LogFile $file */
        foreach ($this->fileCollection as $file) {
            foreach ($this->getLogQueryForFile($file)->getLevelCounts() as $levelCount) {
                $level = $levelCount->level->value;

                if (! isset($totalCounts[$level])) {
                    $totalCounts[$level] = new LevelCount($levelCount->level, 0, $levelCount->selected);
                }

                $totalCounts[$level]->count += $levelCount->count;
            }
        }

        return array_values($totalCounts);
    }

    public function total(): int
    {
        return $this->fileCollection->sum(function (LogFile $file) {
            return $this->getLogQueryForFile($file)->total();
        });
    }

    public function paginate($perPage = 25, ?int $page = null): LengthAwarePaginator
    {
        $page = $page ?: Paginator::resolveCurrentPage('page');

        $this->skip(max(0, $page - 1) * $perPage);

        return new LengthAwarePaginator(
            $this->get($perPage),
            $this->total(),
            $perPage,
            $page
        );
    }

    /**
     * Get the logs from this file collection.
     *
     * @return array|Log[]
     */
    public function get(?int $limit = null): array
    {
        $skip = $this->skip ?? null;
        $limit = $limit ?? $this->limit ?? null;
        $logs = [];

        // First, how do we skip an X amount of logs across multiple files?
        // that should be done based on direction

        // Second, some files might have very few results - way below the limit.
        // That's when we need to know when to jump to another file for results.

        // Third, keep an eye on the limit. Once we have the X number of logs, exit early.

        /** @var LogFile $file */
        foreach ($this->fileCollection as $file) {
            $logQuery = $this->getLogQueryForFile($file);

            if (isset($skip)) {
                $logsToSkip = min($skip, $logQuery->total());
                $logQuery->reset()->skip($logsToSkip);
                $skip -= $logsToSkip;
            }

            while ($log = $logQuery->next()) {
                $logs[] = $log;

                if (isset($limit) && (--$limit <= 0)) {
                    break 2;
                }
            }

            if (isset($limit) && $limit <= 0) {
                // we've gotten the required amount of logs! exit early
                break;
            }
        }

        return $logs;
    }

    public function requiresScan(): bool
    {
        return $this->fileCollection->some(function (LogFile $file) {
            return $this->getLogQueryForFile($file)->requiresScan();
        });
    }

    public function percentScanned(): int
    {
        $totalFileBytes = $this->fileCollection->sum->size();

        if ($totalFileBytes <= 0) {
            // empty files, so assume they've been fully scanned
            return 100;
        }

        $missingScansBytes = $this->fileCollection->sum(function (LogFile $file) {
            return $this->getLogQueryForFile($file)->numberOfNewBytes();
        });

        return 100 - intval($missingScansBytes / $totalFileBytes * 100);
    }

    public function scan(?int $maxBytesToScan = null, bool $force = false): void
    {
        $fileSizeScanned = 0;
        $stopScanningAfter = microtime(true) + LogViewer::lazyScanTimeout();

        /** @var LogFile $logFile */
        foreach ($this->fileCollection as $logFile) {
            $logQuery = $this->getLogQueryForFile($logFile);

            if (! $logQuery->requiresScan()) {
                continue;
            }

            $fileSizeScanned += $logQuery->numberOfNewBytes();

            try {
                $logQuery->scan($maxBytesToScan, $force);
            } catch (CannotOpenFileException $exception) {
                continue;
            }

            if (isset($maxBytesToScan) && $fileSizeScanned >= $maxBytesToScan) {
                break;
            }

            if ($stopScanningAfter < microtime(true)) {
                break;
            }
        }
    }

    protected ?int $dateFrom = null;
    protected ?int $dateTo = null;

    public function forDateRange(CarbonInterface|int|null $from = null, CarbonInterface|int|null $to = null): self
    {
        if ($from instanceof CarbonInterface) {
            $from = $from->timestamp;
        }
        if ($to instanceof CarbonInterface) {
            $to = $to->timestamp;
        }
        $this->dateFrom = $from;
        $this->dateTo = $to;

        return $this;
    }

    public function getVelocity(int $bucketCount = 40, ?int $from = null, ?int $to = null): array
    {
        $allBuckets = [];
        $earliest = null;
        $latest = null;
        $total = 0;

        /** @var LogFile $file */
        foreach ($this->fileCollection as $file) {
            $reader = $this->getLogQueryForFile($file);
            if ($reader instanceof IndexedLogReader) {
                $vel = $reader->getVelocity($bucketCount, $from, $to);
                if (! empty($vel['earliest_timestamp'])) {
                    $earliest = min($earliest ?? $vel['earliest_timestamp'], $vel['earliest_timestamp']);
                }
                if (! empty($vel['latest_timestamp'])) {
                    $latest = max($latest ?? $vel['latest_timestamp'], $vel['latest_timestamp']);
                }
                foreach ($vel['buckets'] as $bucket) {
                    $ts = $bucket['timestamp'];
                    if (! isset($allBuckets[$ts])) {
                        $allBuckets[$ts] = [
                            'timestamp' => $ts,
                            'label' => $bucket['label'],
                            'count' => 0,
                            'levels' => [],
                        ];
                    }
                    $allBuckets[$ts]['count'] += $bucket['count'];
                    $total += $bucket['count'];
                    foreach ($bucket['levels'] as $lvl => $cnt) {
                        $allBuckets[$ts]['levels'][$lvl] = ($allBuckets[$ts]['levels'][$lvl] ?? 0) + $cnt;
                    }
                }
            }
        }

        ksort($allBuckets);

        return [
            'buckets' => array_values($allBuckets),
            'bucket_size' => 60,
            'total' => $total,
            'earliest_timestamp' => $earliest,
            'latest_timestamp' => $latest,
            'from' => $from ?? $earliest,
            'to' => $to ?? $latest,
        ];
    }

    protected function getLogQueryForFile(LogFile $file): LogReaderInterface
    {
        $query = $file->logs()
            ->search($this->query)
            ->setDirection($this->direction)
            ->exceptLevels($this->exceptLevels)
            ->lazyScanning();

        if (isset($this->dateFrom) || isset($this->dateTo)) {
            $query->forDateRange($this->dateFrom, $this->dateTo);
        }

        return $query;
    }
}
