<template>
  <div class="flex-1 w-full">
    <div
      class="flex items-center w-full rounded-lg bg-gray-900 border border-gray-700/80 shadow-inner px-2 py-1 transition-all focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500"
      :class="{'border-red-500 ring-1 ring-red-500': logViewerStore.error}"
    >
      <!-- Prefix Icon -->
      <div class="flex items-center pl-1 pr-2 text-gray-400">
        <label for="query" class="sr-only">Search</label>
        <MagnifyingGlassIcon v-show="!logViewerStore.hasMoreResults" class="h-4 w-4 text-gray-400" />
        <SpinnerIcon v-show="logViewerStore.hasMoreResults" class="w-4 h-4 text-brand-400 animate-spin" />
      </div>

      <!-- Text Input -->
      <div class="relative flex-1">
        <input
          v-model="tempQuery"
          name="query"
          id="query"
          type="text"
          placeholder='Example: "access denied" 1.2.3.4 -sshd'
          class="w-full bg-transparent border-0 text-xs md:text-sm text-gray-100 placeholder-gray-500 focus:outline-hidden focus:ring-0 font-mono"
          @keydown.enter="submitQuery"
          @keydown.esc="(event) => event.target.blur()"
        />
        <div v-show="tempQuery" class="absolute right-1 top-1/2 -translate-y-1/2">
          <button @click="clearQuery" class="p-0.5 rounded text-gray-400 hover:text-gray-200">
            <XMarkIcon class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      <!-- Right Inside Controls: Searches, Save Search, Tips -->
      <div class="flex items-center space-x-1 pl-2 border-l border-gray-800 ml-1">
        <!-- Searches Popover (Recent / Saved) -->
        <SearchesPopover @select="onSearchSelected" />

        <!-- Save This Search Button -->
        <button
          type="button"
          @click="openSaveSearchModal"
          :disabled="!hasQueryText"
          :class="[
            hasQueryText ? 'text-gray-400 hover:text-brand-300 hover:bg-gray-800' : 'text-gray-600 cursor-not-allowed',
            isCurrentQuerySaved ? 'text-brand-400' : '',
            'p-1 rounded-md transition-colors'
          ]"
          title="Save this search"
          aria-label="Save this search"
        >
          <BookmarkIcon class="w-4 h-4" />
        </button>

        <!-- Search Tips Button -->
        <button
          type="button"
          @click="showTipsModal = true"
          class="p-1 rounded-md text-gray-400 hover:text-gray-200 hover:bg-gray-800 transition-colors"
          title="Search Tips"
          aria-label="Search Tips"
        >
          <QuestionMarkCircleIcon class="w-4 h-4" />
        </button>
      </div>

      <!-- Submit SEARCH Button -->
      <div class="ml-2 shrink-0">
        <button
          v-if="logViewerStore.hasMoreResults"
          disabled="disabled"
          class="px-3 py-1.5 rounded-md bg-gray-800 text-gray-400 text-xs font-semibold cursor-wait"
        >
          <span>SEARCHING...</span>
        </button>
        <button
          v-else
          @click="submitQuery"
          id="query-submit"
          class="px-3.5 py-1.5 rounded-md bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold tracking-wider transition-colors shadow-xs"
        >
          <span>SEARCH</span>
        </button>
      </div>
    </div>

    <!-- Progress bar when scanning -->
    <div class="relative h-0 w-full overflow-visible">
      <div
        class="h-0.5 bg-brand-500 transition-all duration-200"
        v-show="logViewerStore.hasMoreResults"
        :style="{ width: logViewerStore.percentScanned + '%' }"
      ></div>
    </div>
    <p class="mt-1 text-red-500 text-xs" v-show="logViewerStore.error" v-html="logViewerStore.error"></p>

    <!-- Save Search Modal -->
    <SaveSearchModal
      :open="showSaveModal"
      :query="tempQuery"
      @close="showSaveModal = false"
      @saved="onSearchSaved"
    />

    <!-- Search Tips Modal -->
    <SearchTipsModal
      :open="showTipsModal"
      @close="showTipsModal = false"
      @select="onSearchSelected"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { MagnifyingGlassIcon, XMarkIcon, BookmarkIcon, QuestionMarkCircleIcon } from '@heroicons/vue/24/outline';
import { useSearchStore } from '../stores/search.js';
import { useLogViewerStore } from '../stores/logViewer.js';
import { useSearchesStore } from '../stores/searches.js';
import SpinnerIcon from './SpinnerIcon.vue';
import SearchesPopover from './SearchesPopover.vue';
import SaveSearchModal from './SaveSearchModal.vue';
import SearchTipsModal from './SearchTipsModal.vue';
import { replaceQuery } from '../helpers.js';

const searchStore = useSearchStore();
const logViewerStore = useLogViewerStore();
const searchesStore = useSearchesStore();
const router = useRouter();
const route = useRoute();

const tempQuery = ref(route.query.query || '');
const showSaveModal = ref(false);
const showTipsModal = ref(false);

const hasQueryText = computed(() => Boolean(tempQuery.value && tempQuery.value.trim().length > 0));
const isCurrentQuerySaved = computed(() => searchesStore.isSaved(tempQuery.value));

const submitQuery = () => {
  const q = tempQuery.value === '' ? null : tempQuery.value;
  if (q) {
    searchesStore.addRecentSearch(q);
  }
  replaceQuery(router, 'query', q);
  document.getElementById('query-submit')?.focus();
};

const clearQuery = () => {
  tempQuery.value = '';
  submitQuery();
};

const openSaveSearchModal = () => {
  if (hasQueryText.value) {
    showSaveModal.value = true;
  }
};

const onSearchSelected = (q) => {
  tempQuery.value = q;
  submitQuery();
};

const onSearchSaved = () => {
  // trigger reactivity
};

watch(
  () => route.query.query,
  (query) => tempQuery.value = query || '',
);
</script>
