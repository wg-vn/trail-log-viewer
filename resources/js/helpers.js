import { ref } from 'vue';

export const highlightSearchResult = (text, query = null) => {
  text = text || '';

  if (query) {
    try {
      text = text.replace(new RegExp(query, 'gi'), '<mark>$&</mark>');
    } catch (e) {
      // in case the regex is invalid, we want to just continue without marking any text.
    }
  }

  // Let's return the <mark> tags which we use for highlighting the search results
  // while escaping the rest of the HTML entities
  return escapeHtml(text)
    .replace(/&lt;mark&gt;/g, '<mark>')
    .replace(/&lt;\/mark&gt;/g, '</mark>')
    .replace(/&lt;br\/&gt;/g, '<br/>');
};

export const escapeHtml = (text) => {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, m => map[m]);
}

export const copyToClipboard = (str) => {
  const el = document.createElement('textarea');
  el.value = str;
  el.setAttribute('readonly', '');
  el.style.position = 'absolute';
  el.style.left = '-9999px';
  document.body.appendChild(el);
  const selected =
    document.getSelection().rangeCount > 0
      ? document.getSelection().getRangeAt(0)
      : false;
  el.select();
  document.execCommand('copy');
  document.body.removeChild(el);
  if (selected) {
    document.getSelection().removeAllRanges();
    document.getSelection().addRange(selected);
  }
};

export const replaceQuery = (router, key, value) => {
  const route = router.currentRoute.value;
  const query = {
    ...route.query,
  };

  if (key === 'host') {
    delete query.file;
    delete query.page;
  } else if (key === 'file' && query.page !== undefined) {
    delete query.page;
  }

  if (value === undefined || value === null || value === '') {
    delete query[key];
  } else {
    query[key] = String(value);
  }

  router.push({ name: 'home', query });
};

export const useDropdownDirection = () => {
  const dropdownDirections = ref({});

  const getDropdownDirection = (buttonElement) => {
    const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
    const boundingRect = buttonElement.getBoundingClientRect();

    return (boundingRect.bottom + 190) < viewportHeight ? 'down' : 'up';
  }

  const calculateDropdownDirection = (toggleButton) => {
    dropdownDirections.value[toggleButton.dataset.toggleId] = getDropdownDirection(toggleButton);
  }

  return { dropdownDirections, calculateDropdownDirection };
}

export const isMobile = () => {
  return window.matchMedia('(max-width: 768px)').matches;
}
