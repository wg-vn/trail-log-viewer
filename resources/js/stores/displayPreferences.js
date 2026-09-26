import { defineStore } from 'pinia';
import { useLocalStorage } from '@vueuse/core';

export const useDisplayPreferencesStore = defineStore('displayPreferences', {
  state: () => ({
    fontFamily: useLocalStorage('trail_fontFamily', 'monospace'),
    fontSize: useLocalStorage('trail_fontSize', 'default'),
    density: useLocalStorage('trail_density', 'comfort'),
    highlightMatches: useLocalStorage('trail_highlightMatches', true),
    truncateMessage: useLocalStorage('trail_truncateMessage', true),
    utcTimestamps: useLocalStorage('trail_utcTimestamps', false),
    columnVisibility: useLocalStorage('trail_columnVisibility', {
      severity: true,
      datetime: true,
      env: true,
      message: true,
    }),
  }),

  getters: {
    fontFamilyClass: (state) => {
      switch (state.fontFamily) {
        case 'jetbrains':
          return 'font-mono font-jetbrains';
        case 'fira':
          return 'font-mono font-fira';
        case 'source':
          return 'font-mono font-source';
        case 'courier':
          return 'font-serif font-courier';
        default:
          return 'font-mono';
      }
    },

    fontSizeClass: (state) => {
      switch (state.fontSize) {
        case 'compact':
          return 'text-xs';
        case 'large':
          return 'text-base';
        default:
          return 'text-sm';
      }
    },

    rowPaddingClass: (state) => {
      return state.density === 'compact' ? 'py-0.5' : 'py-1.5';
    },

    isCompact: (state) => state.density === 'compact',
  },

  actions: {
    setFontFamily(family) {
      this.fontFamily = family;
    },

    setFontSize(size) {
      this.fontSize = size;
    },

    setDensity(density) {
      this.density = density;
    },

    toggleHighlightMatches() {
      this.highlightMatches = !this.highlightMatches;
    },

    toggleTruncateMessage() {
      this.truncateMessage = !this.truncateMessage;
    },

    toggleUtcTimestamps() {
      this.utcTimestamps = !this.utcTimestamps;
    },

    toggleColumn(column) {
      if (this.columnVisibility[column] !== undefined) {
        this.columnVisibility[column] = !this.columnVisibility[column];
      }
    },
  },
});
