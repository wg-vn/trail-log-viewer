<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none max-w-sm w-full">
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in alertsStore.activeToasts"
        :key="toast.id"
        class="pointer-events-auto w-full rounded-lg shadow-xl border p-3 flex items-start space-x-3 transition-all"
        :class="[
          toast.severity === 'critical' ? 'bg-red-950 border-red-700 text-red-100' :
          toast.severity === 'warning' ? 'bg-amber-950 border-amber-700 text-amber-100' :
          'bg-gray-900 border-brand-600 text-gray-100'
        ]"
      >
        <ExclamationTriangleIcon
          class="w-5 h-5 shrink-0 mt-0.5"
          :class="[
            toast.severity === 'critical' ? 'text-red-400' :
            toast.severity === 'warning' ? 'text-amber-400' :
            'text-brand-400'
          ]"
        />
        <div class="flex-1 min-w-0 text-xs">
          <p class="font-semibold truncate">{{ toast.name }}</p>
          <p class="mt-0.5 text-gray-300 break-words">{{ toast.message }}</p>
        </div>
        <button
          @click="alertsStore.dismissToast(toast.id)"
          class="text-gray-400 hover:text-white shrink-0"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useAlertsStore } from '../stores/alerts.js';

const alertsStore = useAlertsStore();
</script>
