<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" @close="close" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-200 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-150 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-200 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-150 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-xl bg-gray-900 border border-gray-700 p-6 text-left align-middle shadow-2xl transition-all text-gray-200">
              <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-100 flex items-center space-x-2">
                  <BookmarkIcon class="w-5 h-5 text-brand-400" />
                  <span>Save this search</span>
                </DialogTitle>
                <button @click="close" class="text-gray-400 hover:text-gray-200">
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>

              <div class="mt-4 space-y-4 text-sm">
                <div>
                  <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    Search Query
                  </label>
                  <div class="p-2.5 rounded-md bg-gray-800/90 font-mono text-xs text-brand-300 border border-gray-700/60 truncate">
                    {{ query }}
                  </div>
                </div>

                <div>
                  <label for="search-name" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    Search Name <span class="text-red-400">*</span>
                  </label>
                  <input
                    id="search-name"
                    ref="nameInput"
                    v-model="name"
                    type="text"
                    placeholder="e.g. Production 500 Errors"
                    class="w-full px-3 py-2 rounded-md bg-gray-800 border border-gray-700 text-gray-100 text-sm focus:outline-hidden focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                    @keydown.enter="handleSave"
                  />
                  <p class="text-[11px] text-gray-500 mt-1">
                    Give this search a descriptive name so you can quickly recall it later.
                  </p>
                </div>
              </div>

              <div class="mt-6 flex justify-end space-x-3">
                <button
                  type="button"
                  @click="close"
                  class="px-3.5 py-1.5 rounded-md text-xs font-medium text-gray-400 hover:text-gray-200 hover:bg-gray-800 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  @click="handleSave"
                  class="px-4 py-1.5 rounded-md text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-sm transition-colors"
                >
                  Save Search
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { BookmarkIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useSearchesStore } from '../stores/searches.js';

const props = defineProps({
  open: { type: Boolean, default: false },
  query: { type: String, default: '' },
});

const emit = defineEmits(['close', 'saved']);
const searchesStore = useSearchesStore();
const name = ref('');
const nameInput = ref(null);

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    name.value = props.query;
    nextTick(() => {
      nameInput.value?.focus();
      nameInput.value?.select();
    });
  }
});

const close = () => {
  emit('close');
};

const handleSave = () => {
  if (!name.value.trim()) {
    name.value = props.query;
  }
  searchesStore.saveSearch(name.value, props.query);
  emit('saved');
  close();
};
</script>
