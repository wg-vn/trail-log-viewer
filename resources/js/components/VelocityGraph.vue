<template>
  <div class="w-full bg-gray-900 border-t border-gray-800 text-gray-200 select-none transition-all">
    <!-- Top Controls Bar -->
    <div class="flex items-center justify-between px-4 py-1.5 border-b border-gray-800 text-xs bg-gray-950/60">
      <div class="flex items-center space-x-3">
        <button
          @click="fetchVelocity"
          :class="[loading ? 'animate-spin text-brand-400' : 'text-gray-400 hover:text-gray-200', 'p-1 rounded transition-colors']"
          title="Refresh Velocity Graph"
        >
          <ArrowPathIcon class="w-3.5 h-3.5" />
        </button>

        <label class="flex items-center space-x-1.5 cursor-pointer text-gray-400 hover:text-gray-200">
          <input
            v-model="autoRefresh"
            type="checkbox"
            class="rounded bg-gray-800 border-gray-700 text-brand-600 focus:ring-brand-500 w-3.5 h-3.5"
          />
          <span class="text-[11px]">Auto refresh</span>
        </label>
      </div>

      <div class="flex items-center space-x-3">
        <!-- Rate vs Count Toggle -->
        <div class="flex rounded bg-gray-800 p-0.5 border border-gray-700/80 text-[11px]">
          <button
            type="button"
            @click="mode = 'rate'"
            :class="[
              mode === 'rate' ? 'bg-gray-700 text-white font-medium shadow-xs' : 'text-gray-400 hover:text-gray-200',
              'px-2 py-0.5 rounded transition-colors'
            ]"
          >
            Rate
          </button>
          <button
            type="button"
            @click="mode = 'count'"
            :class="[
              mode === 'count' ? 'bg-gray-700 text-white font-medium shadow-xs' : 'text-gray-400 hover:text-gray-200',
              'px-2 py-0.5 rounded transition-colors'
            ]"
          >
            Count
          </button>
        </div>

        <!-- Time Range Dropdown -->
        <select
          v-model="selectedRange"
          @change="fetchVelocity"
          class="px-2 py-0.5 rounded bg-gray-800 border border-gray-700 text-gray-300 text-[11px] focus:outline-hidden focus:border-brand-500"
        >
          <option value="15m">15 minutes</option>
          <option value="1h">1 hour</option>
          <option value="6h">6 hours</option>
          <option value="12h">12 hours</option>
          <option value="24h">24 hours</option>
          <option value="all">All Available</option>
        </select>

        <!-- Close Button -->
        <button
          @click="$emit('close')"
          class="p-1 rounded text-gray-400 hover:text-gray-200 hover:bg-gray-800 transition-colors"
          title="Close Velocity Graph"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Chart Body -->
    <div class="relative px-4 py-2 h-36 flex flex-col justify-end bg-gradient-to-b from-gray-900 to-gray-950">
      <!-- Loading overlay -->
      <div v-if="loading && buckets.length === 0" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500">
        Loading velocity data...
      </div>

      <div v-else-if="buckets.length === 0" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500">
        No log events in the selected time range.
      </div>

      <!-- Interactive SVG Chart -->
      <div v-else class="relative w-full h-full flex flex-col justify-between" ref="chartContainer">
        <!-- SVG Canvas -->
        <svg class="w-full h-24 overflow-visible" preserveAspectRatio="none" viewBox="0 0 1000 100">
          <defs>
            <linearGradient id="velocityGrad" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stop-color="#0284c7" stop-opacity="0.4" />
              <stop offset="100%" stop-color="#0284c7" stop-opacity="0.02" />
            </linearGradient>
            <linearGradient id="barGrad" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stop-color="#38bdf8" />
              <stop offset="100%" stop-color="#0284c7" />
            </linearGradient>
          </defs>

          <!-- Horizontal Grid Lines -->
          <line x1="0" y1="20" x2="1000" y2="20" stroke="#374151" stroke-width="0.5" stroke-dasharray="2 2" opacity="0.4" />
          <line x1="0" y1="50" x2="1000" y2="50" stroke="#374151" stroke-width="0.5" stroke-dasharray="2 2" opacity="0.4" />
          <line x1="0" y1="80" x2="1000" y2="80" stroke="#374151" stroke-width="0.5" stroke-dasharray="2 2" opacity="0.4" />
          <line x1="0" y1="99" x2="1000" y2="99" stroke="#4b5563" stroke-width="1" opacity="0.6" />

          <!-- Bars / Area -->
          <g v-for="(b, i) in normalizedBuckets" :key="i">
            <rect
              :x="b.x"
              :y="b.y"
              :width="b.w"
              :height="b.h"
              fill="url(#barGrad)"
              class="cursor-pointer hover:opacity-80 transition-opacity"
              rx="1"
              @mouseenter="onHoverBucket($event, b)"
              @mouseleave="hoveredBucket = null"
              @click="onBucketClick(b)"
            />
          </g>
        </svg>

        <!-- X-axis Time Labels -->
        <div class="flex justify-between items-center text-[10px] text-gray-500 pt-1 border-t border-gray-800/80 font-mono">
          <span>{{ startTimeLabel }}</span>
          <span v-for="(lbl, idx) in midTimeLabels" :key="idx" class="hidden sm:inline">{{ lbl }}</span>
          <span>{{ endTimeLabel }}</span>
        </div>

        <!-- Tooltip -->
        <div
          v-if="hoveredBucket"
          class="absolute z-20 pointer-events-none transform -translate-x-1/2 -translate-y-full mb-2 px-2.5 py-1.5 rounded-md bg-gray-800 border border-gray-700 shadow-xl text-xs text-gray-100"
          :style="{ left: hoveredBucket.screenX + 'px', top: hoveredBucket.screenY + 'px' }"
        >
          <div class="font-semibold font-mono text-[11px] text-brand-300">{{ hoveredBucket.timeLabel }}</div>
          <div class="mt-0.5 text-gray-300">
            <strong>{{ hoveredBucket.count }}</strong> event{{ hoveredBucket.count === 1 ? '' : 's' }}
            <span v-if="mode === 'rate'" class="text-gray-400">({{ hoveredBucket.rate }}/min)</span>
          </div>
          <div v-if="hoveredBucket.hasLevels" class="flex space-x-2 mt-1 text-[10px]">
            <span v-if="hoveredBucket.levels.error || hoveredBucket.levels.critical || hoveredBucket.levels.emergency" class="text-red-400">
              {{ (hoveredBucket.levels.error || 0) + (hoveredBucket.levels.critical || 0) + (hoveredBucket.levels.emergency || 0) }} Err
            </span>
            <span v-if="hoveredBucket.levels.warning" class="text-amber-400">
              {{ hoveredBucket.levels.warning }} Warn
            </span>
            <span v-if="hoveredBucket.levels.info" class="text-sky-400">
              {{ hoveredBucket.levels.info }} Info
            </span>
          </div>
          <div class="text-[9px] text-gray-400 mt-0.5 italic">Click to seek to this time</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { ArrowPathIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useFileStore } from '../stores/files.js';
import { useSearchStore } from '../stores/search.js';
import { useSeverityStore } from '../stores/severity.js';
import { useHostStore } from '../stores/hosts.js';

const emit = defineEmits(['close', 'seek']);

const fileStore = useFileStore();
const searchStore = useSearchStore();
const severityStore = useSeverityStore();
const hostStore = useHostStore();

const loading = ref(false);
const autoRefresh = ref(true);
const mode = ref('count');
const selectedRange = ref('1h');
const buckets = ref([]);
const totalCount = ref(0);
const earliestTs = ref(null);
const latestTs = ref(null);
const bucketSize = ref(60);

const hoveredBucket = ref(null);
const chartContainer = ref(null);

let refreshInterval = null;

const fetchVelocity = async () => {
  if (!fileStore.selectedFile && !searchStore.hasQuery) return;
  loading.value = true;

  try {
    const params = {
      host: hostStore.hostQueryParam,
      file: fileStore.selectedFile?.identifier,
      query: searchStore.query || '',
      range: selectedRange.value,
      exclude_levels: severityStore.excludedLevels,
      exclude_file_types: fileStore.fileTypesExcluded,
    };

    const res = await axios.get(`${window.LogViewer.basePath}/api/logs/velocity`, { params });
    buckets.value = res.data.buckets || [];
    totalCount.value = res.data.total || 0;
    earliestTs.value = res.data.earliest_timestamp || null;
    latestTs.value = res.data.latest_timestamp || null;
    bucketSize.value = res.data.bucket_size || 60;
  } catch (e) {
    console.error('Velocity fetch error:', e);
  } finally {
    loading.value = false;
  }
};

const maxVal = computed(() => {
  if (buckets.value.length === 0) return 1;
  const counts = buckets.value.map(b => mode.value === 'rate' ? (b.count / (bucketSize.value / 60)) : b.count);
  return Math.max(...counts, 1);
});

const normalizedBuckets = computed(() => {
  const len = buckets.value.length;
  if (len === 0) return [];

  const totalWidth = 1000;
  const barWidth = Math.max(2, (totalWidth / len) - 2);

  return buckets.value.map((b, i) => {
    const val = mode.value === 'rate' ? (b.count / (bucketSize.value / 60)) : b.count;
    const barHeight = Math.max(3, (val / maxVal.value) * 90);
    const x = (i / len) * totalWidth;
    const y = 99 - barHeight;

    return {
      ...b,
      val,
      x,
      y,
      w: barWidth,
      h: barHeight,
      rate: (b.count / (bucketSize.value / 60)).toFixed(1),
      timeLabel: new Date(b.timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
      hasLevels: Object.keys(b.levels || {}).length > 0,
    };
  });
});

const startTimeLabel = computed(() => {
  if (buckets.value.length === 0) return '';
  return new Date(buckets.value[0].timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

const endTimeLabel = computed(() => {
  if (buckets.value.length === 0) return '';
  return new Date(buckets.value[buckets.value.length - 1].timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

const midTimeLabels = computed(() => {
  if (buckets.value.length < 4) return [];
  const idx1 = Math.floor(buckets.value.length * 0.33);
  const idx2 = Math.floor(buckets.value.length * 0.66);
  return [
    new Date(buckets.value[idx1].timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    new Date(buckets.value[idx2].timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
  ];
});

const onHoverBucket = (event, bucket) => {
  const rect = event.target.getBoundingClientRect();
  const parentRect = chartContainer.value?.getBoundingClientRect() || { left: 0, top: 0 };
  hoveredBucket.value = {
    ...bucket,
    screenX: rect.left - parentRect.left + (rect.width / 2),
    screenY: rect.top - parentRect.top,
  };
};

const onBucketClick = (bucket) => {
  emit('seek', bucket.timestamp);
};

watch([() => fileStore.selectedFile, () => searchStore.query], () => {
  fetchVelocity();
});

onMounted(() => {
  fetchVelocity();
  refreshInterval = setInterval(() => {
    if (autoRefresh.value) {
      fetchVelocity();
    }
  }, 10000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>
