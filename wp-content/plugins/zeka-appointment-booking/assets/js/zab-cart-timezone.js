(function () {
  'use strict';

  function getBrowserTimeZone() {
    try {
      return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    } catch (error) {
      return '';
    }
  }

  function hasLocalTimeInAppointmentLine() {
    return document.body.textContent.indexOf('(Your local time)') !== -1;
  }

  function hasAppointmentLine() {
    return /Appointment/i.test(document.body.textContent || '');
  }

  function setTimezoneCookie(timeZone) {
    if (!timeZone) {
      return;
    }

    document.cookie = 'zab_browser_tz=' + encodeURIComponent(timeZone) + '; path=/; max-age=2592000; SameSite=Lax';
  }

  function syncTimezoneAndReload() {
    if (!window.zabCartTimeData || !window.zabCartTimeData.ajaxUrl || !window.zabCartTimeData.nonce) {
      return;
    }

    var browserTz = getBrowserTimeZone();
    if (!browserTz) {
      return;
    }

    var onceKey = 'zab-cart-tz-sync-' + window.location.pathname + '-' + browserTz;
    if (window.sessionStorage && window.sessionStorage.getItem(onceKey) === '1') {
      return;
    }

    if (window.sessionStorage) {
      window.sessionStorage.setItem(onceKey, '1');
    }

    var formData = new FormData();
    formData.append('action', 'zab_set_cart_timezone');
    formData.append('nonce', window.zabCartTimeData.nonce);
    formData.append('browser_tz', browserTz);

    fetch(window.zabCartTimeData.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (payload && payload.success && payload.data && payload.data.updated) {
          window.location.reload();
        }
      })
      .catch(function () {
        // Ignore sync errors to avoid disrupting checkout flow.
      });
  }

  function maybeSyncTimezone() {
    var browserTz = getBrowserTimeZone();
    setTimezoneCookie(browserTz);

    if (!hasAppointmentLine() || hasLocalTimeInAppointmentLine()) {
      return;
    }

    var reloadKey = 'zab-cart-local-time-reload-' + window.location.pathname;
    if (window.sessionStorage && window.sessionStorage.getItem(reloadKey) !== '1') {
      window.sessionStorage.setItem(reloadKey, '1');
      window.location.reload();
      return;
    }

    syncTimezoneAndReload();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', maybeSyncTimezone);
  } else {
    maybeSyncTimezone();
  }

  document.body.addEventListener('updated_checkout', maybeSyncTimezone);
  document.body.addEventListener('wc_fragments_refreshed', maybeSyncTimezone);

  if (window.jQuery) {
    window.jQuery(document.body).on('updated_checkout wc_fragments_refreshed', maybeSyncTimezone);
  }
})();
