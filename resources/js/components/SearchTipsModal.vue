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
            <DialogPanel class="w-full max-w-xl transform overflow-hidden rounded-xl bg-gray-900 border border-gray-700 p-6 text-left align-middle shadow-2xl transition-all text-gray-200">
              <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                <DialogTitle as="h3" class="text-base font-semibold text-gray-100 flex items-center space-x-2">
                  <QuestionMarkCircleIcon class="w-5 h-5 text-brand-400" />
                  <span>Search Syntax Examples</span>
                </DialogTitle>
                <button @click="close" class="text-gray-400 hover:text-gray-200">
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>

              <div class="mt-4 space-y-3.5 text-xs">
                <div
                  v-for="(tip, idx) in syntaxTips"
                  :key="idx"
                  class="p-3 rounded-lg bg-gray-800/80 border border-gray-700/60 hover:border-gray-600 transition-colors"
                >
                  <div class="flex items-center justify-between mb-1.5">
                    <code
                      class="px-2 py-1 rounded bg-black/50 text-brand-300 font-mono text-[13px] select-all cursor-pointer hover:bg-black/80"
                      @click="useTip(tip.query)"
                      title="Click to use in search"
                    >
                      {{ tip.query }}
                    </code>
                    <button
                      @click="useTip(tip.query)"
                      class="text-[11px] text-brand-400 hover:text-brand-300 font-medium ml-2"
                    >
                      Use
                    </button>
                  </div>
                  <p class="text-gray-400 text-[12px] leading-relaxed">
                    {{ tip.description }}
                  </p>
                </div>
              </div>

              <div class="mt-6 flex justify-end">
                <button
                  type="button"
                  @click="close"
                  class="px-4 py-1.5 rounded-md text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-sm transition-colors"
                >
                  Done
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
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { QuestionMarkCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
  open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'select']);

const syntaxTips = [
  {
    query: '"Accepted password" sudo',
    description: 'Events containing "Accepted password" and "sudo" in any part of the message.',
  },
  {
    query: '-CROND',
    description: 'Events not containing "CROND" (negation search).',
  },
  {
    query: '(message-id postfix/smtpd) OR -google.com',
    description: 'Events containing "message-id" and "postfix/smtpd", or not containing "google.com".',
  },
  {
    query: '10.1.2 -("TTL exceeded" OR "1 packet")',
    description: 'Events containing 10.1.2 and neither "TTL exceeded" nor "1 packet".',
  },
  {
    query: '/exception|fatal|error/i',
    description: 'Regular expression match for pattern (case-insensitive).',
  },
];

const close = () => {
  emit('close');
};

const useTip = (query) => {
  emit('select', query);
  close();
};
</script>
