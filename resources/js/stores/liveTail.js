import { defineStore } from 'pinia';
import { useLogViewerStore } from './logViewer.js';
import { useAlertsStore } from './alerts.js';

export const useLiveTailStore = defineStore('liveTail', {
  state: () => ({
    isActive: false,
    isPaused: false,
    pauseReason: null,
    pollInterval: 2500,
    pollTimer: null,
    isProgrammaticScroll: false,
  }),

  actions: {
    start() {
      this.isActive = true;
      this.isPaused = false;
      this.pauseReason = null;
      this.clearTimer();

      // Trigger initial load
      const logViewerStore = useLogViewerStore();
      logViewerStore.loadLogs({ silently: true });

      this.pollTimer = setInterval(() => {
        if (this.isActive && !this.isPaused) {
          const logViewerStore = useLogViewerStore();
          const alertsStore = useAlertsStore();

          logViewerStore.loadLogs({ silently: true });
          
          if (logViewerStore.logs && logViewerStore.logs.length > 0) {
            alertsStore.checkLogs(logViewerStore.logs.slice(0, 10));
          }

          // Auto-scroll to newest logs if not paused
          this.scrollToNewest();
        }
      }, this.pollInterval);

      this.scrollToNewest();
    },

    pause(reason = 'user') {
      if (!this.isActive) return;
      this.isPaused = true;
      this.pauseReason = reason;
    },

    resume() {
      if (!this.isActive) {
        this.start();
        return;
      }
      this.isPaused = false;
      this.pauseReason = null;
      const logViewerStore = useLogViewerStore();
      logViewerStore.loadLogs({ silently: true });
      this.scrollToNewest();
    },

    stop() {
      this.isActive = false;
      this.isPaused = false;
      this.pauseReason = null;
      this.clearTimer();
    },

    toggle() {
      if (this.isActive && !this.isPaused) {
        this.pause('user');
      } else if (this.isActive && this.isPaused) {
        this.resume();
      } else {
        this.start();
      }
    },

    clearTimer() {
      if (this.pollTimer) {
        clearInterval(this.pollTimer);
        this.pollTimer = null;
      }
    },

    scrollToNewest() {
      this.isProgrammaticScroll = true;
      setTimeout(() => {
        const container = document.querySelector('.log-item-container');
        if (!container) {
          this.isProgrammaticScroll = false;
          return;
        }
        const logViewerStore = useLogViewerStore();
        if (logViewerStore.direction === 'asc') {
          // Oldest first -> scroll to bottom
          container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        } else {
          // Newest first -> scroll to top
          container.scrollTo({ top: 0, behavior: 'smooth' });
        }
        setTimeout(() => {
          this.isProgrammaticScroll = false;
        }, 350);
      }, 50);
    },
  },
});
