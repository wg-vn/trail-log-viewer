<template>
  <Popover class="relative inline-block" v-slot="{ open, close }">
    <PopoverButton
      class="p-2 rounded-lg text-gray-400 hover:text-gray-100 hover:bg-gray-800 focus:outline-hidden transition-colors"
      :class="{ 'text-brand-400 bg-gray-800': open }"
      title="Display preferences"
      aria-label="Display preferences"
    >
      <Cog6ToothIcon class="w-5 h-5" />
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
          <span class="font-semibold text-sm tracking-wide">Display Preferences</span>
          <button @click="close" class="text-gray-400 hover:text-gray-200">
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4 text-xs">
          <!-- Font and Density -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                Font Family
              </label>
              <select
                v-model="displayPrefs.fontFamily"
                class="w-full px-2.5 py-1.5 rounded-md bg-gray-800 border border-gray-700 text-gray-200 text-xs focus:outline-hidden focus:border-brand-500"
              >
                <option value="monospace">System Monospace</option>
                <option value="jetbrains">JetBrains Mono</option>
                <option value="fira">Fira Code</option>
                <option value="source">Source Code Pro</option>
                <option value="courier">Courier New</option>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                Density
              </label>
              <div class="flex rounded-md bg-gray-800 p-0.5 border border-gray-700">
                <button
                  type="button"
                  @click="displayPrefs.setDensity('comfort')"
                  :class="[
                    displayPrefs.density === 'comfort' ? 'bg-gray-700 text-white font-semibold' : 'text-gray-400 hover:text-gray-200',
                    'flex-1 py-1 rounded text-center transition-colors'
                  ]"
                >
                  Comfort
                </button>
                <button
                  type="button"
                  @click="displayPrefs.setDensity('compact')"
                  :class="[
                    displayPrefs.density === 'compact' ? 'bg-gray-700 text-white font-semibold' : 'text-gray-400 hover:text-gray-200',
                    'flex-1 py-1 rounded text-center transition-colors'
                  ]"
                >
                  Compact
                </button>
              </div>
            </div>
          </div>

          <!-- Font Size and Theme -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                Font Size
              </label>
              <select
                v-model="displayPrefs.fontSize"
                class="w-full px-2.5 py-1.5 rounded-md bg-gray-800 border border-gray-700 text-gray-200 text-xs focus:outline-hidden focus:border-brand-500"
              >
                <option value="compact">Compact (12px)</option>
                <option value="default">Default (14px)</option>
                <option value="large">Large (16px)</option>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                Theme
              </label>
              <select
                :value="logViewerStore.theme"
                @change="onThemeChange"
                class="w-full px-2.5 py-1.5 rounded-md bg-gray-800 border border-gray-700 text-gray-200 text-xs focus:outline-hidden focus:border-brand-500"
              >
                <option value="System">System Default</option>
                <option value="Dark">Dark Mode</option>
                <option value="Light">Light Mode</option>
              </select>
            </div>
          </div>

          <!-- Toggles -->
          <div class="pt-2 border-t border-gray-800 space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="text-gray-300">Highlight Matches</span>
              <button
                type="button"
                @click="displayPrefs.toggleHighlightMatches"
                :class="[displayPrefs.highlightMatches ? 'bg-brand-600' : 'bg-gray-700', 'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors']"
              >
                <span :class="[displayPrefs.highlightMatches ? 'translate-x-4' : 'translate-x-0.5', 'inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 mt-0.5 shadow-sm']" />
              </button>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-gray-300">Truncate Message (Single Line)</span>
              <button
                type="button"
                @click="displayPrefs.toggleTruncateMessage"
                :class="[displayPrefs.truncateMessage ? 'bg-brand-600' : 'bg-gray-700', 'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors']"
              >
                <span :class="[displayPrefs.truncateMessage ? 'translate-x-4' : 'translate-x-0.5', 'inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 mt-0.5 shadow-sm']" />
              </button>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-gray-300">UTC Timestamps</span>
              <button
                type="button"
                @click="displayPrefs.toggleUtcTimestamps"
                :class="[displayPrefs.utcTimestamps ? 'bg-brand-600' : 'bg-gray-700', 'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors']"
              >
                <span :class="[displayPrefs.utcTimestamps ? 'translate-x-4' : 'translate-x-0.5', 'inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 mt-0.5 shadow-sm']" />
              </button>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-gray-300">Reverse Tail (Chronological)</span>
              <button
                type="button"
                @click="toggleReverseTail"
                :class="[logViewerStore.direction === 'asc' ? 'bg-brand-600' : 'bg-gray-700', 'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors']"
              >
                <span :class="[logViewerStore.direction === 'asc' ? 'translate-x-4' : 'translate-x-0.5', 'inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 mt-0.5 shadow-sm']" />
              </button>
            </div>
          </div>

          <!-- Column Visibility Controls -->
          <div class="pt-2 border-t border-gray-800">
            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">
              Columns Visibility
            </label>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <button
                type="button"
                @click="displayPrefs.toggleColumn('datetime')"
                :class="[
                  displayPrefs.columnVisibility.datetime ? 'bg-brand-950/70 border-brand-700 text-brand-300' : 'bg-gray-800 border-gray-700 text-gray-500 line-through',
                  'px-2 py-1.5 rounded border transition-colors'
                ]"
              >
                Datetime
              </button>
              <button
                type="button"
                @click="displayPrefs.toggleColumn('severity')"
                :class="[
                  displayPrefs.columnVisibility.severity ? 'bg-brand-950/70 border-brand-700 text-brand-300' : 'bg-gray-800 border-gray-700 text-gray-500 line-through',
                  'px-2 py-1.5 rounded border transition-colors'
                ]"
              >
                Severity
              </button>
              <button
                type="button"
                @click="displayPrefs.toggleColumn('env')"
                :class="[
                  displayPrefs.columnVisibility.env ? 'bg-brand-950/70 border-brand-700 text-brand-300' : 'bg-gray-800 border-gray-700 text-gray-500 line-through',
                  'px-2 py-1.5 rounded border transition-colors'
                ]"
              >
                Env
              </button>
            </div>
          </div>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { Cog6ToothIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useDisplayPreferencesStore } from '../stores/displayPreferences.js';
import { useLogViewerStore } from '../stores/logViewer.js';

const displayPrefs = useDisplayPreferencesStore();
const logViewerStore = useLogViewerStore();

const onThemeChange = (e) => {
  logViewerStore.theme = e.target.value;
  logViewerStore.syncTheme();
};

const toggleReverseTail = () => {
  logViewerStore.direction = logViewerStore.direction === 'desc' ? 'asc' : 'desc';
  logViewerStore.loadLogs();
};
</script>
