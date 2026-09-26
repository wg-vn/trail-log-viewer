<template>
  <div class="absolute z-20 top-0 bottom-10 bg-gray-100 dark:bg-gray-900 md:left-0 md:w-88 md:flex-col md:fixed md:inset-y-0"
       :class="[
         fileStore.sidebarOpen ? 'left-0 right-0 md:left-auto md:right-auto' : 'left-[-200%] right-[200%] md:left-auto md:right-auto',
         fileStore.sidebarCollapsed ? 'md:hidden' : 'md:flex',
       ]"
  >
    <file-list></file-list>
  </div>

  <div v-if="fileStore.sidebarCollapsed" class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-12 md:justify-center md:pt-5 z-20">
    <button type="button" class="menu-button h-fit" title="Expand sidebar" @click="fileStore.toggleSidebarCollapsed">
      <ChevronDoubleRightIcon class="w-5 h-5" />
    </button>
  </div>

  <div class="flex flex-col flex-1 min-h-screen max-h-screen max-w-full" :class="fileStore.sidebarCollapsed ? 'md:pl-9' : 'md:pl-88'">
    <log-list class="pb-16 md:pb-12"></log-list>
  </div>

  <div class="absolute bottom-4 right-4 flex items-center">
    <p class="text-xs text-gray-500 dark:text-gray-400 -mb-0.5">
      <template v-if="logViewerStore.performance?.requestTime">
        <span><span class="hidden md:inline">Memory: </span><span class="font-semibold">{{ logViewerStore.performance.memoryUsage }}</span></span>
        <span class="mx-1.5">&middot;</span>
        <span><span class="hidden md:inline">Duration: </span><span class="font-semibold">{{ logViewerStore.performance.requestTime }}</span></span>
        <span class="mx-1.5">&middot;</span>
      </template>
      <span><span class="hidden md:inline">Version: </span><span class="font-semibold">{{ LogViewer.version }}</span></span>
    </p>
  </div>

  <keyboard-shortcuts-overlay />
</template>

<script setup>
import FileList from '../components/FileList.vue';
import LogList from '../components/LogList.vue';
import { useHostStore } from '../stores/hosts.js';
import { useLogViewerStore } from '../stores/logViewer.js';
import { useFileStore } from '../stores/files.js';
import { useSearchStore } from '../stores/search.js';
import { usePaginationStore } from '../stores/pagination.js';
import { useRoute, useRouter } from 'vue-router';
import { onBeforeMount, onBeforeUnmount, onMounted, watch } from 'vue';
import { replaceQuery } from '../helpers.js';
import { registerGlobalShortcuts, unregisterGlobalShortcuts } from '../keyboardNavigation';
import KeyboardShortcutsOverlay from '../components/KeyboardShortcutsOverlay.vue';
import { ChevronDoubleRightIcon } from '@heroicons/vue/24/outline';

const hostStore = useHostStore();
const logViewerStore = useLogViewerStore();
const fileStore = useFileStore();
const searchStore = useSearchStore();
const paginationStore = usePaginationStore();
const route = useRoute();
const router = useRouter();

onBeforeMount(() => {
  logViewerStore.syncTheme();
  registerGlobalShortcuts();
});

onBeforeUnmount(() => {
  unregisterGlobalShortcuts();
})

onMounted(() => {
  // This makes sure we react to device's dark mode changes
  setInterval(logViewerStore.syncTheme, 1000);
})

// watch for URL query changes and update the store values
watch(
  () => route.query,
  (query) => {
    fileStore.selectFile(query.file || null);
    paginationStore.setPage(query.page || 1);
    searchStore.setQuery(query.query || '');

    logViewerStore.loadLogs();
  },
  { immediate: true },
)

watch(
  () => route.query.host,
  async (newHost) => {
    hostStore.selectHost(newHost || null);

    if (newHost && !hostStore.selectedHostIdentifier) {
      // the host no longer exists, remove it from the URL
      replaceQuery(router, 'host', null);
    }

    fileStore.reset();
    await fileStore.loadFolders();
    logViewerStore.loadLogs();
  },
  { immediate: true },
)

onMounted(() => {
  window.onresize = function () {
    logViewerStore.setViewportDimensions(window.innerWidth, window.innerHeight);
  };
})
</script>
