<template>
  <Popover class="relative inline-block" v-slot="{ open, close }">
    <PopoverButton
      class="p-2 rounded-lg text-gray-400 hover:text-gray-100 hover:bg-gray-800 focus:outline-hidden transition-colors"
      :class="{ 'text-brand-400 bg-gray-800': open }"
      title="Seek to date or time"
      aria-label="Seek to date or time"
    >
      <ClockIcon class="w-5 h-5" />
    </PopoverButton>

    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <PopoverPanel
        class="absolute right-0 bottom-full mb-2 w-88 md:w-96 rounded-xl bg-gray-900 border border-gray-700 shadow-2xl p-4 z-50 text-gray-200"
      >
        <div class="flex items-center justify-between border-b border-gray-800 pb-2.5 mb-3">
          <span class="font-semibold text-sm tracking-wide">Seek to date or time</span>
          <button @click="close" class="text-gray-400 hover:text-gray-200">
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-3">
          <!-- Text Input + Calendar Button -->
          <div class="flex items-center space-x-2">
            <div class="relative flex-1">
              <input
                v-model="inputQuery"
                type="text"
                placeholder="8:11am, Tue 4pm, Dec 1 15:30"
                class="w-full px-3 py-2 text-xs rounded-md bg-gray-800 border border-gray-700 text-gray-100 placeholder-gray-500 focus:outline-hidden focus:border-brand-500 focus:ring-1 focus:ring-brand-500 font-mono"
                @keydown.enter="handleSeek(close)"
              />
            </div>

            <!-- Native date-time picker trigger -->
            <label class="p-2 rounded-md bg-gray-800 border border-gray-700 text-gray-400 hover:text-gray-200 hover:bg-gray-700/80 cursor-pointer relative" title="Pick date & time">
              <CalendarIcon class="w-4 h-4" />
              <input
                type="datetime-local"
                class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                @change="onDateTimePicked"
              />
            </label>

            <button
              @click="handleSeek(close)"
              :disabled="!parsedTimestamp"
              class="px-3.5 py-2 rounded-md bg-brand-600 hover:bg-brand-500 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-semibold text-white transition-colors"
            >
              Seek to
            </button>
          </div>

          <!-- Parsed date feedback -->
          <div v-if="parsedTimestamp" class="px-2.5 py-1.5 rounded-md bg-brand-950/60 border border-brand-800/40 text-brand-300 text-xs flex items-center space-x-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"></span>
            <span>Target: <strong>{{ formatTimestamp(parsedTimestamp) }}</strong></span>
          </div>

          <!-- Quick Presets -->
          <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Quick Jumps</div>
            <div class="grid grid-cols-3 gap-1.5 text-xs">
              <button
                v-for="p in presets"
                :key="p.label"
                type="button"
                @click="applyPreset(p.seconds, close)"
                class="px-2 py-1 rounded bg-gray-800/70 hover:bg-gray-700 text-gray-300 hover:text-white border border-gray-700/50 text-center transition-colors"
              >
                {{ p.label }}
              </button>
            </div>
          </div>

          <!-- Retention info -->
          <div class="pt-2 border-t border-gray-800 text-[11px] text-gray-500">
            <template v-if="earliestTimestamp && latestTimestamp">
              Logs span from {{ formatTimestamp(earliestTimestamp) }} to {{ formatTimestamp(latestTimestamp) }}
            </template>
            <template v-else>
              Enter any natural date, time, or relative duration.
            </template>
          </div>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { ClockIcon, CalendarIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useLogViewerStore } from '../stores/logViewer.js';

const emit = defineEmits(['seek']);
const logViewerStore = useLogViewerStore();

const inputQuery = ref('');

const earliestTimestamp = computed(() => logViewerStore.performance?.earliest_timestamp || null);
const latestTimestamp = computed(() => logViewerStore.performance?.latest_timestamp || null);

const presets = [
  { label: '5m ago', seconds: 5 * 60 },
  { label: '15m ago', seconds: 15 * 60 },
  { label: '1h ago', seconds: 60 * 60 },
  { label: '6h ago', seconds: 6 * 3600 },
  { label: '24h ago', seconds: 24 * 3600 },
  { label: 'Now', seconds: 0 },
];

const parsedTimestamp = computed(() => {
  const val = inputQuery.value.trim().toLowerCase();
  if (!val) return null;

  const nowSec = Math.floor(Date.now() / 1000);

  // Exact epoch seconds / millis
  if (/^\d{10,13}$/.test(val)) {
    return val.length === 13 ? Math.floor(Number(val) / 1000) : Number(val);
  }

  // Relative matches: e.g. "5m ago", "10 minutes ago", "2 hours ago"
  const relMatch = val.match(/^(\d+)\s*(s|sec|seconds?|m|min|minutes?|h|hr|hours?|d|days?)\s*(ago)?$/);
  if (relMatch) {
    const num = parseInt(relMatch[1], 10);
    const unit = relMatch[2][0];
    let mult = 60;
    if (unit === 's') mult = 1;
    if (unit === 'm') mult = 60;
    if (unit === 'h') mult = 3600;
    if (unit === 'd') mult = 86400;
    return nowSec - (num * mult);
  }

  if (val === 'now') return nowSec;
  if (val === 'yesterday') return nowSec - 86400;

  // Standard Date parse
  const parsed = Date.parse(inputQuery.value);
  if (!isNaN(parsed)) {
    return Math.floor(parsed / 1000);
  }

  return null;
});

const onDateTimePicked = (e) => {
  const val = e.target.value;
  if (val) {
    inputQuery.value = val.replace('T', ' ');
  }
};

const applyPreset = (seconds, close) => {
  const target = Math.floor(Date.now() / 1000) - seconds;
  emit('seek', target);
  if (close) close();
};

const handleSeek = (close) => {
  if (parsedTimestamp.value) {
    emit('seek', parsedTimestamp.value);
    if (close) close();
  }
};

const formatTimestamp = (ts) => {
  if (!ts) return '';
  const d = new Date(ts * 1000);
  return d.toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};
</script>
