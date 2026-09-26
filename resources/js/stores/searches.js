import { defineStore } from 'pinia';
import { useLocalStorage } from '@vueuse/core';

export const useSearchesStore = defineStore('searches', {
  state: () => ({
    recentSearches: useLocalStorage('trail_recent_searches', []),
    savedSearches: useLocalStorage('trail_saved_searches', []),
  }),

  actions: {
    addRecentSearch(query) {
      if (!query || String(query).trim() === '') return;
      const clean = String(query).trim();
      // Remove existing duplicate
      const filtered = this.recentSearches.filter(item => item.query !== clean);
      filtered.unshift({
        query: clean,
        timestamp: Date.now(),
      });
      // Limit to 25 items
      this.recentSearches = filtered.slice(0, 25);
    },

    removeRecentSearch(query) {
      this.recentSearches = this.recentSearches.filter(item => item.query !== query);
    },

    clearRecentSearches() {
      this.recentSearches = [];
    },

    saveSearch(name, query) {
      if (!query || String(query).trim() === '') return;
      const cleanQuery = String(query).trim();
      const cleanName = (name && String(name).trim() !== '') ? String(name).trim() : cleanQuery;
      
      const newSaved = {
        id: 'search_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
        name: cleanName,
        query: cleanQuery,
        timestamp: Date.now(),
      };

      // Add to list or replace if query identical
      const existingIdx = this.savedSearches.findIndex(s => s.query === cleanQuery);
      if (existingIdx >= 0) {
        this.savedSearches[existingIdx] = newSaved;
      } else {
        this.savedSearches.unshift(newSaved);
      }
      return newSaved;
    },

    removeSavedSearch(id) {
      this.savedSearches = this.savedSearches.filter(s => s.id !== id);
    },

    isSaved(query) {
      if (!query) return false;
      const clean = String(query).trim();
      return this.savedSearches.some(s => s.query === clean);
    }
  },
});
