<template>
  <Popover class="relative inline-block" v-slot="{ open, close }">
    <PopoverButton
      class="p-1 rounded-md text-gray-400 hover:text-gray-200 hover:bg-gray-700/50 focus:outline-hidden transition-colors"
      title="Searches"
      aria-label="Searches"
    >
      <QueueListIcon class="w-4 h-4" />
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
        class="absolute right-0 bottom-full mb-2 w-80 md:w-96 rounded-lg bg-gray-900 border border-gray-700 shadow-2xl p-3 z-50 text-gray-200"
      >
        <div class="flex items-center justify-between border-b border-gray-700 pb-2 mb-3">
          <span class="font-semibold text-sm tracking-wide">Searches</span>
          <button @click="close" class="text-gray-400 hover:text-gray-200">
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-700 text-xs font-semibold mb-3">
          <button
            @click="activeTab = 'recent'"
            :class="[
              activeTab === 'recent'
                ? 'border-brand-500 text-brand-400 border-b-2'
                : 'text-gray-400 hover:text-gray-200 border-b-2 border-transparent',
              'pb-2 px-3 transition-colors uppercase tracking-wider'
            ]"
          >
            Recent
            <span v-if="searchesStore.recentSearches.length" class="ml-1 px-1.5 py-0.5 rounded-full bg-gray-800 text-[10px] text-gray-400">
              {{ searchesStore.recentSearches.length }}
            </span>
          </button>
          <button
            @click="activeTab = 'saved'"
            :class="[
              activeTab === 'saved'
                ? 'border-brand-500 text-brand-400 border-b-2'
                : 'text-gray-400 hover:text-gray-200 border-b-2 border-transparent',
              'pb-2 px-3 transition-colors uppercase tracking-wider'
            ]"
          >
            Saved
            <span v-if="searchesStore.savedSearches.length" class="ml-1 px-1.5 py-0.5 rounded-full bg-gray-800 text-[10px] text-gray-400">
              {{ searchesStore.savedSearches.length }}
            </span>
          </button>
        </div>

        <!-- Recent Tab Content -->
        <div v-if="activeTab === 'recent'" class="space-y-1 max-h-64 overflow-y-auto pr-1">
          <div v-if="searchesStore.recentSearches.length === 0" class="py-6 text-center text-xs text-gray-400">
            No recent searches found. Check back after performing a few searches.
          </div>
          <div
            v-for="(item, idx) in searchesStore.recentSearches"
            :key="idx"
            class="group flex items-center justify-between px-2.5 py-1.5 rounded-md hover:bg-gray-800/80 cursor-pointer text-xs transition-colors"
            @click="selectSearch(item.query, close)"
          >
            <div class="flex items-center space-x-2 truncate">
              <ClockIcon class="w-3.5 h-3.5 text-gray-500 shrink-0" />
              <span class="truncate font-mono text-gray-200 group-hover:text-brand-300">{{ item.query }}</span>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
              <span class="text-[10px] text-gray-500">{{ formatTimeAgo(item.timestamp) }}</span>
              <button
                @click.stop="searchesStore.removeRecentSearch(item.query)"
                class="opacity-0 group-hover:opacity-100 p-0.5 hover:text-red-400 transition-opacity"
                title="Remove"
              >
                <XMarkIcon class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <div v-if="searchesStore.recentSearches.length > 0" class="pt-2 border-t border-gray-800 flex justify-end">
            <button
              @click="searchesStore.clearRecentSearches"
              class="text-[11px] text-gray-400 hover:text-red-400 transition-colors"
            >
              Clear recent searches
            </button>
          </div>
        </div>

        <!-- Saved Tab Content -->
        <div v-if="activeTab === 'saved'" class="space-y-1 max-h-64 overflow-y-auto pr-1">
          <div v-if="searchesStore.savedSearches.length === 0" class="py-6 text-center text-xs text-gray-400 leading-relaxed">
            No searches have been saved.<br />
            To save a search, enter a search and use the <BookmarkIcon class="w-3.5 h-3.5 inline text-gray-300 -mt-0.5" /> icon.
          </div>
          <div
            v-for="s in searchesStore.savedSearches"
            :key="s.id"
            class="group flex items-center justify-between px-2.5 py-2 rounded-md hover:bg-gray-800/80 cursor-pointer text-xs transition-colors"
            @click="selectSearch(s.query, close)"
          >
            <div class="truncate mr-2">
              <div class="font-medium text-gray-100 group-hover:text-brand-300 truncate">{{ s.name }}</div>
              <div class="font-mono text-[11px] text-gray-400 truncate">{{ s.query }}</div>
            </div>
            <button
              @click.stop="searchesStore.removeSavedSearch(s.id)"
              class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-400 transition-opacity"
              title="Delete saved search"
            >
              <TrashIcon class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { ref } from 'vue';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { QueueListIcon, XMarkIcon, BookmarkIcon, ClockIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useSearchesStore } from '../stores/searches.js';

const emit = defineEmits(['select']);
const searchesStore = useSearchesStore();
const activeTab = ref('recent');

const selectSearch = (query, close) => {
  emit('select', query);
  if (close) close();
};

const formatTimeAgo = (ts) => {
  if (!ts) return '';
  const diffSec = Math.floor((Date.now() - ts) / 1000);
  if (diffSec < 60) return 'just now';
  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `${diffMin}m ago`;
  const diffHr = Math.floor(diffMin / 60);
  if (diffHr < 24) return `${diffHr}h ago`;
  const diffDays = Math.floor(diffHr / 24);
  return `${diffDays}d ago`;
};
</script>
