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
            <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-xl bg-gray-900 border border-gray-700 p-6 text-left align-middle shadow-2xl transition-all text-gray-200">
              <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                <DialogTitle as="h3" class="text-base font-semibold text-gray-100 flex items-center space-x-2">
                  <ExclamationTriangleIcon class="w-5 h-5 text-amber-400" />
                  <span>Create Alert for this Search</span>
                </DialogTitle>
                <button @click="close" class="text-gray-400 hover:text-gray-200">
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>

              <!-- Tabs: New Alert / Existing Alerts -->
              <div class="flex border-b border-gray-800 mt-3 text-xs font-semibold">
                <button
                  type="button"
                  @click="activeTab = 'new'"
                  :class="[
                    activeTab === 'new' ? 'border-brand-500 text-brand-400 border-b-2' : 'text-gray-400 hover:text-gray-200 border-b-2 border-transparent',
                    'pb-2 px-3 transition-colors uppercase tracking-wider'
                  ]"
                >
                  Create Alert
                </button>
                <button
                  type="button"
                  @click="activeTab = 'manage'"
                  :class="[
                    activeTab === 'manage' ? 'border-brand-500 text-brand-400 border-b-2' : 'text-gray-400 hover:text-gray-200 border-b-2 border-transparent',
                    'pb-2 px-3 transition-colors uppercase tracking-wider'
                  ]"
                >
                  Manage Alerts ({{ alertsStore.alerts.length }})
                </button>
              </div>

              <!-- Create Alert Tab -->
              <div v-if="activeTab === 'new'" class="mt-4 space-y-4 text-xs">
                <div>
                  <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    Alert Name
                  </label>
                  <input
                    v-model="name"
                    type="text"
                    placeholder="e.g. Critical Database Connection Errors"
                    class="w-full px-3 py-2 text-xs rounded-md bg-gray-800 border border-gray-700 text-gray-100 focus:outline-hidden focus:border-brand-500"
                  />
                </div>

                <div>
                  <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    Search Query Filter
                  </label>
                  <input
                    v-model="queryVal"
                    type="text"
                    placeholder="e.g. error OR exception"
                    class="w-full px-3 py-2 text-xs rounded-md bg-gray-800 border border-gray-700 text-brand-300 font-mono focus:outline-hidden focus:border-brand-500"
                  />
                </div>

                <!-- Severity Selector -->
                <div>
                  <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                    Severity
                  </label>
                  <div class="grid grid-cols-3 gap-2">
                    <button
                      type="button"
                      @click="severity = 'critical'"
                      :class="[
                        severity === 'critical' ? 'bg-red-950 border-red-500 text-red-300 ring-1 ring-red-500' : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-gray-200',
                        'px-2.5 py-1.5 rounded-md border text-center font-medium transition-colors'
                      ]"
                    >
                      Critical
                    </button>
                    <button
                      type="button"
                      @click="severity = 'warning'"
                      :class="[
                        severity === 'warning' ? 'bg-amber-950 border-amber-500 text-amber-300 ring-1 ring-amber-500' : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-gray-200',
                        'px-2.5 py-1.5 rounded-md border text-center font-medium transition-colors'
                      ]"
                    >
                      Warning
                    </button>
                    <button
                      type="button"
                      @click="severity = 'info'"
                      :class="[
                        severity === 'info' ? 'bg-sky-950 border-sky-500 text-sky-300 ring-1 ring-sky-500' : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-gray-200',
                        'px-2.5 py-1.5 rounded-md border text-center font-medium transition-colors'
                      ]"
                    >
                      Info
                    </button>
                  </div>
                </div>

                <!-- Threshold condition -->
                <div>
                  <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                    Trigger Condition
                  </label>
                  <div class="flex items-center space-x-2 text-gray-300">
                    <span>When more than</span>
                    <input
                      v-model.number="thresholdCount"
                      type="number"
                      min="1"
                      class="w-16 px-2 py-1 rounded bg-gray-800 border border-gray-700 text-center font-mono focus:outline-hidden focus:border-brand-500"
                    />
                    <span>events occur within</span>
                    <input
                      v-model.number="thresholdMinutes"
                      type="number"
                      min="1"
                      class="w-16 px-2 py-1 rounded bg-gray-800 border border-gray-700 text-center font-mono focus:outline-hidden focus:border-brand-500"
                    />
                    <span>min</span>
                  </div>
                </div>

                <!-- Notification Channel -->
                <div>
                  <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                    Notification Action
                  </label>
                  <div class="grid grid-cols-3 gap-2 mb-2">
                    <button
                      type="button"
                      @click="channel = 'browser'"
                      :class="[
                        channel === 'browser' ? 'bg-brand-950 border-brand-500 text-brand-300 ring-1 ring-brand-500' : 'bg-gray-800 border-gray-700 text-gray-400',
                        'px-2 py-1.5 rounded-md border text-center font-medium transition-colors'
                      ]"
                    >
                      Browser Alert
                    </button>
                    <button
                      type="button"
                      @click="channel = 'webhook'"
                      :class="[
                        channel === 'webhook' ? 'bg-brand-950 border-brand-500 text-brand-300 ring-1 ring-brand-500' : 'bg-gray-800 border-gray-700 text-gray-400',
                        'px-2 py-1.5 rounded-md border text-center font-medium transition-colors'
                      ]"
                    >
                      Webhook
                    </button>
                    <button
                      type="button"
                      @click="channel = 'email'"
                      :class="[
                        channel === 'email' ? 'bg-brand-950 border-brand-500 text-brand-300 ring-1 ring-brand-500' : 'bg-gray-800 border-gray-700 text-gray-400',
                        'px-2 py-1.5 rounded-md border text-center font-medium transition-colors'
                      ]"
                    >
                      Email
                    </button>
                  </div>

                  <input
                    v-if="channel !== 'browser'"
                    v-model="destination"
                    type="text"
                    :placeholder="channel === 'webhook' ? 'https://hooks.slack.com/services/...' : 'ops@example.com'"
                    class="w-full px-3 py-2 text-xs rounded-md bg-gray-800 border border-gray-700 text-gray-100 focus:outline-hidden focus:border-brand-500"
                  />
                  <p v-else class="text-[11px] text-gray-400">
                    Displays in-app notifications and triggers desktop browser push notifications during Live Tail.
                  </p>
                </div>

                <div class="mt-6 flex justify-end space-x-3 pt-3 border-t border-gray-800">
                  <button
                    type="button"
                    @click="close"
                    class="px-3.5 py-1.5 rounded-md text-xs font-medium text-gray-400 hover:text-gray-200 transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="handleCreateAlert"
                    class="px-4 py-1.5 rounded-md text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-sm transition-colors"
                  >
                    Create Alert
                  </button>
                </div>
              </div>

              <!-- Manage Alerts Tab -->
              <div v-else class="mt-4 space-y-2 max-h-80 overflow-y-auto pr-1 text-xs">
                <div v-if="alertsStore.alerts.length === 0" class="py-8 text-center text-gray-400">
                  No alerts configured yet. Switch to "Create Alert" to set one up.
                </div>
                <div
                  v-for="a in alertsStore.alerts"
                  :key="a.id"
                  class="p-3 rounded-lg bg-gray-800/80 border border-gray-700/60 flex items-center justify-between"
                >
                  <div class="truncate mr-3">
                    <div class="flex items-center space-x-2">
                      <span
                        :class="[
                          a.severity === 'critical' ? 'bg-red-500/20 text-red-400 border-red-800' :
                          a.severity === 'warning' ? 'bg-amber-500/20 text-amber-400 border-amber-800' :
                          'bg-sky-500/20 text-sky-400 border-sky-800',
                          'px-1.5 py-0.5 rounded text-[10px] font-semibold border uppercase'
                        ]"
                      >
                        {{ a.severity }}
                      </span>
                      <span class="font-medium text-gray-100 truncate">{{ a.name }}</span>
                    </div>
                    <div class="text-[11px] text-gray-400 mt-1 font-mono truncate">
                      Query: <span class="text-brand-300">{{ a.query }}</span> &middot; {{ a.thresholdCount }} events / {{ a.thresholdMinutes }}m
                    </div>
                  </div>

                  <div class="flex items-center space-x-2 shrink-0">
                    <button
                      type="button"
                      @click="testAlert(a)"
                      class="px-2 py-1 rounded bg-gray-700 text-gray-300 hover:text-white text-[11px]"
                      title="Test fire this alert"
                    >
                      Test
                    </button>
                    <button
                      type="button"
                      @click="alertsStore.toggleAlert(a.id)"
                      :class="[
                        a.enabled ? 'text-green-400 hover:text-green-300' : 'text-gray-500 hover:text-gray-400',
                        'text-xs font-semibold'
                      ]"
                    >
                      {{ a.enabled ? 'ON' : 'OFF' }}
                    </button>
                    <button
                      type="button"
                      @click="alertsStore.deleteAlert(a.id)"
                      class="p-1 text-gray-400 hover:text-red-400"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { ExclamationTriangleIcon, XMarkIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useAlertsStore } from '../stores/alerts.js';

const props = defineProps({
  open: { type: Boolean, default: false },
  query: { type: String, default: '' },
});

const emit = defineEmits(['close']);
const alertsStore = useAlertsStore();

const activeTab = ref('new');
const name = ref('');
const queryVal = ref('');
const severity = ref('warning');
const thresholdCount = ref(1);
const thresholdMinutes = ref(1);
const channel = ref('browser');
const destination = ref('');

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    queryVal.value = props.query;
    name.value = props.query ? `Alert for "${props.query}"` : 'High Error Alert';
  }
});

const close = () => {
  emit('close');
};

const handleCreateAlert = () => {
  alertsStore.createAlert({
    name: name.value,
    query: queryVal.value,
    severity: severity.value,
    thresholdCount: thresholdCount.value,
    thresholdMinutes: thresholdMinutes.value,
    channel: channel.value,
    destination: destination.value,
  });
  activeTab.value = 'manage';
};

const testAlert = (alert) => {
  alertsStore.fireAlert(alert, 1, 'Manual alert test triggered successfully');
};
</script>
