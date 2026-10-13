(() => {
  const originalFetch = window.fetch.bind(window);
  function csrfToken() {
    const token = document.cookie.split('; ').find(row => row.startsWith('TYTCSRF='));
    return token ? decodeURIComponent(token.split('=').slice(1).join('=')) : '';
  }
  window.fetch = (input, init = {}) => {
    const method = String(init.method || 'GET').toUpperCase();
    const headers = new Headers(init.headers || {});
    if (!['GET','HEAD','OPTIONS'].includes(method)) headers.set('X-CSRF-Token', csrfToken());
    return originalFetch(input, {...init, headers});
  };
})();
