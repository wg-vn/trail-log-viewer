import { defineStore } from 'pinia';
import { useLocalStorage } from '@vueuse/core';

export const useAlertsStore = defineStore('alerts', {
  state: () => ({
    alerts: useLocalStorage('trail_saved_alerts', []),
    activeToasts: [],
  }),

  actions: {
    createAlert({ name, query, severity = 'warning', thresholdCount = 1, thresholdMinutes = 1, channel = 'browser', destination = '' }) {
      const alert = {
        id: 'alert_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
        name: name || `Alert for "${query}"`,
        query: query || '',
        severity, // 'critical' | 'warning' | 'info'
        thresholdCount: Number(thresholdCount) || 1,
        thresholdMinutes: Number(thresholdMinutes) || 1,
        channel, // 'browser' | 'webhook' | 'email'
        destination: destination || '',
        enabled: true,
        createdAt: Date.now(),
        lastTriggeredAt: null,
      };

      this.alerts.unshift(alert);

      if (channel === 'browser' && 'Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
      }

      return alert;
    },

    deleteAlert(id) {
      this.alerts = this.alerts.filter(a => a.id !== id);
    },

    toggleAlert(id) {
      const alert = this.alerts.find(a => a.id === id);
      if (alert) {
        alert.enabled = !alert.enabled;
      }
    },

    fireAlert(alert, matchingCount = 1, sampleMessage = '') {
      alert.lastTriggeredAt = Date.now();

      const toast = {
        id: 'toast_' + Date.now() + '_' + Math.random().toString(36).substring(2, 6),
        name: alert.name,
        severity: alert.severity,
        message: sampleMessage ? `Matched: ${sampleMessage.slice(0, 100)}` : `Detected ${matchingCount} event(s) matching "${alert.query}"`,
        timestamp: Date.now(),
      };

      this.activeToasts.unshift(toast);
      // Auto-dismiss after 6s
      setTimeout(() => {
        this.dismissToast(toast.id);
      }, 6000);

      // Browser Web Notification
      if (alert.channel === 'browser' && 'Notification' in window && Notification.permission === 'granted') {
        try {
          new Notification(`[Trail Alert] ${alert.name}`, {
            body: toast.message,
            icon: '/img/log-viewer-64.png',
          });
        } catch (e) {
          console.warn('Web notification error:', e);
        }
      }
    },

    dismissToast(id) {
      this.activeToasts = this.activeToasts.filter(t => t.id !== id);
    },

    checkLogs(logs) {
      if (!logs || logs.length === 0) return;
      const enabledAlerts = this.alerts.filter(a => a.enabled && a.query);
      if (enabledAlerts.length === 0) return;

      enabledAlerts.forEach(alert => {
        // Cooldown: at least 15 seconds between triggers
        if (alert.lastTriggeredAt && (Date.now() - alert.lastTriggeredAt) < 15000) {
          return;
        }

        try {
          const regex = new RegExp(alert.query, 'i');
          const matches = logs.filter(log => {
            const text = (log.message || '') + ' ' + (log.full_text || '');
            return regex.test(text);
          });

          if (matches.length >= alert.thresholdCount) {
            this.fireAlert(alert, matches.length, matches[0]?.message);
          }
        } catch (e) {
          // If query is plain substring
          const needle = alert.query.toLowerCase();
          const matches = logs.filter(log => {
            const text = ((log.message || '') + ' ' + (log.full_text || '')).toLowerCase();
            return text.includes(needle);
          });
          if (matches.length >= alert.thresholdCount) {
            this.fireAlert(alert, matches.length, matches[0]?.message);
          }
        }
      });
    },
  },
});
