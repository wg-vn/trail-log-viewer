<template>
  <div class="flex flex-col h-full w-full log-list relative select-text">
    <!-- Top Bar: Level Buttons, Mobile Hamburger, Reload, Header Info -->
    <div class="px-3 md:px-6 py-2.5 flex items-center justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/90 shrink-0">
      <div class="flex items-center space-x-3">
        <div class="md:hidden">
          <button type="button" class="menu-button p-1 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white" @click="fileStore.toggleSidebar">
            <Bars3Icon class="w-5 h-5" />
          </button>
        </div>

        <!-- Selected File or Global Scope Badge -->
        <div class="flex items-center space-x-2">
          <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Scope:
          </span>
          <span class="px-2 py-0.5 rounded-md text-xs font-mono font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-700 truncate max-w-xs">
            {{ selectedFile ? selectedFile.name : 'Everything (All Files)' }}
          </span>
        </div>

        <div v-if="showLevelsDropdown" class="hidden sm:block">
          <LevelButtons />
        </div>
      </div>

      <div class="flex items-center space-x-3 text-xs">
        <!-- Live Tail Status Badge -->
        <div v-if="liveTailStore.isActive" class="flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
             :class="liveTailStore.isPaused ? 'bg-amber-950/80 text-amber-300 border border-amber-700/60' : 'bg-emerald-950/80 text-emerald-300 border border-emerald-700/60'"
        >
          <span class="w-2 h-2 rounded-full" :class="liveTailStore.isPaused ? 'bg-amber-400' : 'bg-emerald-400 animate-pulse'"></span>
          <span>{{ liveTailStore.isPaused ? 'Tail Paused' : 'Live Tail' }}</span>
          <button v-if="liveTailStore.isPaused" @click="liveTailStore.resume" class="underline ml-1 hover:text-white">Resume</button>
        </div>

        <!-- Pagination Short Summary -->
        <div v-if="paginationStore.total > 0" class="text-gray-500 dark:text-gray-400 hidden lg:block font-mono">
          <span>{{ paginationStore.from }}-{{ paginationStore.to }} of {{ paginationStore.total }}</span>
        </div>

        <pagination-options class="hidden md:block" />
      </div>
    </div>

    <!-- Main Log View / Content Area -->
    <div class="relative flex-1 overflow-hidden min-h-0">
      <div v-if="displayLogs" class="relative h-full flex flex-col">
        <!-- Scrollable Log Table Container -->
        <div
          class="log-item-container flex-1 overflow-y-auto overflow-x-auto px-1 md:px-4"
          @scroll="handleScroll"
          ref="logContainer"
        >
          <div class="inline-block min-w-full align-middle pb-6">
            <base-log-table />
          </div>
        </div>

        <!-- Floating "Tail Paused" Banner when user scrolls away from newest logs -->
        <div
          v-if="liveTailStore.isActive && liveTailStore.isPaused && liveTailStore.pauseReason === 'scroll'"
          class="absolute top-3 left-1/2 -translate-x-1/2 z-30"
        >
          <button
            @click="liveTailStore.resume"
            class="px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg flex items-center space-x-2 transition-transform hover:scale-105"
          >
            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
            <span>Tail paused (scrolled) &middot; Click to jump to newest logs</span>
          </button>
        </div>

        <!-- Pagination Controls at bottom of list if not live tailing -->
        <div v-if="!liveTailStore.isActive && paginationStore.hasPages" class="px-4 py-2 border-t border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50 shrink-0">
          <Pagination :loading="logViewerStore.loading" />
        </div>

        <!-- Loading State Overlay -->
        <div class="absolute inset-0 z-20 pointer-events-none" v-show="logViewerStore.loading && !liveTailStore.isActive">
          <div class="w-full h-full bg-black/20 dark:bg-black/40 backdrop-blur-[1px] flex items-center justify-center pointer-events-auto">
            <div class="p-4 rounded-xl bg-gray-900 border border-gray-700 shadow-2xl flex items-center space-x-3 text-gray-100 text-sm">
              <SpinnerIcon class="w-6 h-6 text-brand-400 animate-spin" />
              <span>Scanning logs...</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex h-full flex-col items-center justify-center text-gray-500 dark:text-gray-400 space-y-2">
        <span v-if="logViewerStore.hasMoreResults" class="flex items-center space-x-2">
          <SpinnerIcon class="w-5 h-5 text-brand-400 animate-spin" />
          <span>Scanning files for matching logs...</span>
        </span>
        <span v-else class="text-sm">Select a file from the sidebar or enter a search query to inspect logs.</span>
      </div>
    </div>

    <!-- Velocity Graph (Collapsible Bar directly above toolbar) -->
    <div v-if="showVelocityGraph" class="shrink-0 border-t border-gray-800">
      <VelocityGraph @close="showVelocityGraph = false" @seek="onSeekTimestamp" />
    </div>

    <!-- Papertrail Docked Bottom Toolbar -->
    <div class="shrink-0 border-t border-gray-800 bg-gray-950 px-3 md:px-5 py-2.5 flex items-center space-x-2 md:space-x-3 z-30 shadow-2xl">
      <!-- Search Bar with embedded Recent/Saved, Save Button, Tips, and SEARCH button -->
      <div class="flex-1 min-w-0">
        <SearchInput />
      </div>

      <!-- Toolbar Tool Buttons -->
      <div class="flex items-center space-x-1 shrink-0">
        <!-- 1. Seek to date or time -->
        <SeekPopover @seek="onSeekTimestamp" />

        <!-- 2. Velocity Graph Toggle -->
        <button
          type="button"
          @click="showVelocityGraph = !showVelocityGraph"
          :class="[
            showVelocityGraph ? 'text-brand-400 bg-gray-800' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800',
            'p-2 rounded-lg transition-colors'
          ]"
          title="Toggle Velocity Graph"
          aria-label="Velocity Graph"
        >
          <PresentationChartLineIcon class="w-5 h-5" />
        </button>

        <!-- 3. Display Preferences -->
        <DisplayPreferencesPopover />

        <!-- 4. Create Alert for this Search Query -->
        <button
          type="button"
          @click="showAlertModal = true"
          class="p-2 rounded-lg text-gray-400 hover:text-gray-100 hover:bg-gray-800 transition-colors"
          title="Create alert for this search"
          aria-label="Create alert for this search"
        >
          <ExclamationTriangleIcon class="w-5 h-5" />
        </button>

        <!-- 5. Reload logs button -->
        <button
          type="button"
          @click="logViewerStore.loadLogs()"
          class="p-2 rounded-lg text-gray-400 hover:text-gray-100 hover:bg-gray-800 transition-colors hidden sm:inline-block"
          title="Reload logs"
        >
          <ArrowPathIcon class="w-5 h-5" />
        </button>

        <!-- 6. Live Tail Play/Pause Button -->
        <button
          type="button"
          @click="liveTailStore.toggle()"
          :class="[
            liveTailStore.isActive && !liveTailStore.isPaused
              ? 'bg-emerald-600 hover:bg-emerald-500 text-white ring-2 ring-emerald-400/50'
              : (liveTailStore.isActive && liveTailStore.isPaused
                  ? 'bg-amber-600 hover:bg-amber-500 text-white'
                  : 'bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white'),
            'p-2 rounded-lg transition-all shadow-sm'
          ]"
          :title="liveTailStore.isActive ? (liveTailStore.isPaused ? 'Resume Live Tail' : 'Pause Live Tail') : 'Start Live Tail'"
          aria-label="Live tail"
        >
          <PauseIcon v-if="liveTailStore.isActive && !liveTailStore.isPaused" class="w-5 h-5" />
          <PlayIcon v-else class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Create Alert Modal -->
    <CreateAlertModal
      :open="showAlertModal"
      :query="searchStore.query"
      @close="showAlertModal = false"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
  Bars3Icon,
  ArrowPathIcon,
} from '@heroicons/vue/24/solid';
import {
  PresentationChartLineIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import { PlayIcon, PauseIcon } from '@heroicons/vue/24/solid';

import { useLogViewerStore } from '../stores/logViewer.js';
import { useSearchStore } from '../stores/search.js';
import { useFileStore } from '../stores/files.js';
import { usePaginationStore } from '../stores/pagination.js';
import { useLiveTailStore } from '../stores/liveTail.js';
import { replaceQuery } from '../helpers.js';

import Pagination from './Pagination.vue';
import LevelButtons from './LevelButtons.vue';
import SearchInput from './SearchInput.vue';
import SpinnerIcon from './SpinnerIcon.vue';
import BaseLogTable from './BaseLogTable.vue';
import PaginationOptions from './PaginationOptions.vue';
import SeekPopover from './SeekPopover.vue';
import VelocityGraph from './VelocityGraph.vue';
import DisplayPreferencesPopover from './DisplayPreferencesPopover.vue';
import CreateAlertModal from './CreateAlertModal.vue';

const router = useRouter();
const fileStore = useFileStore();
const logViewerStore = useLogViewerStore();
const searchStore = useSearchStore();
const paginationStore = usePaginationStore();
const liveTailStore = useLiveTailStore();

const showVelocityGraph = ref(false);
const showAlertModal = ref(false);
const logContainer = ref(null);

const selectedFile = computed(() => logViewerStore.selectedFile);

const showLevelsDropdown = computed(() => {
  return fileStore.selectedFile || String(searchStore.query || '').trim().length > 0;
});

const displayLogs = computed(() => {
  return logViewerStore.logs && (logViewerStore.logs.length > 0 || !logViewerStore.hasMoreResults) && (logViewerStore.selectedFile || searchStore.hasQuery);
});

const onSeekTimestamp = (ts) => {
  if (liveTailStore.isActive) {
    liveTailStore.pause('user');
  }
  replaceQuery(router, 'seek', ts);
  logViewerStore.seekTimestamp = ts;
  logViewerStore.loadLogs();
};

const handleScroll = (event) => {
  logViewerStore.onScroll(event);

  if (liveTailStore.isActive && !liveTailStore.isPaused && !liveTailStore.isProgrammaticScroll) {
    const el = event.target;
    const isNewestFirst = logViewerStore.direction === 'desc';
    const isAwayFromTop = el.scrollTop > 60;
    const isAwayFromBottom = (el.scrollHeight - el.scrollTop - el.clientHeight) > 60;

    if ((isNewestFirst && isAwayFromTop) || (!isNewestFirst && isAwayFromBottom)) {
      liveTailStore.pause('scroll');
    }
  }
};

watch(
  [
    () => logViewerStore.direction,
    () => logViewerStore.resultsPerPage,
  ],
  () => logViewerStore.loadLogs()
);
</script>
