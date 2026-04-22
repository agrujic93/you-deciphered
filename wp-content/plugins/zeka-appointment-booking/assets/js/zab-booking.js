(function () {
  'use strict';

  function escapeHtml(value) {
    if (typeof value !== 'string') {
      return '';
    }

    return value
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function renderMessage(container, message, tone) {
    var className = 'zab-slot-results__message';

    if (tone === 'error') {
      className += ' zab-slot-results__message--error';
    }

    container.innerHTML = '<p class="' + className + '">' + escapeHtml(message) + '</p>';
  }

  function getBrowserTimeZone() {
    try {
      return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    } catch (error) {
      return '';
    }
  }

  function parseUtcDateTime(value) {
    if (typeof value !== 'string' || !value) {
      return null;
    }

    var normalized = value.replace(' ', 'T') + 'Z';
    var date = new Date(normalized);

    return Number.isNaN(date.getTime()) ? null : date;
  }

  function formatYmdInTimeZone(date, timeZone) {
    if (!(date instanceof Date) || Number.isNaN(date.getTime()) || !timeZone) {
      return '';
    }

    try {
      var parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: timeZone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      }).formatToParts(date);

      var year = '';
      var month = '';
      var day = '';

      parts.forEach(function (part) {
        if (part.type === 'year') year = part.value;
        if (part.type === 'month') month = part.value;
        if (part.type === 'day') day = part.value;
      });

      return year && month && day ? year + '-' + month + '-' + day : '';
    } catch (error) {
      return '';
    }
  }

  function getVisitorLocalDateYmd() {
    var now = new Date();

    if (Number.isNaN(now.getTime())) {
      return '';
    }

    var year = String(now.getFullYear());
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');

    return year + '-' + month + '-' + day;
  }

  function getPreferredDefaultDate(dateInput) {
    var businessDate = (dateInput && dateInput.value) ? String(dateInput.value) : '';
    var visitorDate = getVisitorLocalDateYmd();

    if (!visitorDate) {
      return businessDate;
    }

    if (!businessDate) {
      return visitorDate;
    }

    return visitorDate > businessDate ? visitorDate : businessDate;
  }

  function formatSlotDisplay(slot, config) {
    var fallback = String(slot.start || '') + ' - ' + String(slot.end || '');
    var startDate = parseUtcDateTime(slot.utc_start);
    var endDate = parseUtcDateTime(slot.utc_end);
    var browserTimeZone = getBrowserTimeZone();

    if (!startDate || !endDate || !browserTimeZone) {
      return {
        label: fallback,
        summary: fallback,
        timezoneText: (config.labels.businessTimezoneFormat || '').replace('%1$s', String(config.siteTimezone || 'UTC'))
      };
    }

    try {
      var timeFormatter = new Intl.DateTimeFormat(undefined, {
        hour: 'numeric',
        minute: '2-digit'
      });
      var dateFormatter = new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric'
      });
      var startTime = timeFormatter.format(startDate);
      var endTime = timeFormatter.format(endDate);
      var localDate = formatYmdInTimeZone(startDate, browserTimeZone);
      var siteDate = slot.site_date || '';
      var includeDate = localDate && siteDate && localDate !== siteDate;
      var label = includeDate ? dateFormatter.format(startDate) + ' ' + startTime + ' - ' + endTime : startTime + ' - ' + endTime;
      var summary = dateFormatter.format(startDate) + ' ' + startTime + ' - ' + endTime;
      var timezoneText = (config.labels.localTimezoneFormat || '')
        .replace('%1$s', browserTimeZone)
        .replace('%2$s', String(config.siteTimezone || 'UTC'));

      return {
        label: label,
        summary: summary,
        timezoneText: timezoneText
      };
    } catch (error) {
      return {
        label: fallback,
        summary: fallback,
        timezoneText: (config.labels.businessTimezoneFormat || '').replace('%1$s', String(config.siteTimezone || 'UTC'))
      };
    }
  }

  function updateTimezoneNotice(widget, text) {
    var timezoneNotice = widget.querySelector('.zab-booking-timezone');

    if (!timezoneNotice) {
      return;
    }

    timezoneNotice.textContent = text || '';
  }

  function renderSlots(widget, slots, labels) {
    var config = zabBookingData || {};
    var container = widget.querySelector('.zab-slot-results');
    var selectedStart = widget.querySelector('.zab-selected-slot-start');
    var selectedEnd = widget.querySelector('.zab-selected-slot-end');
    var actionWrap = widget.querySelector('.zab-booking-action');
    var selectedText = widget.querySelector('.zab-booking-action__selected');

    selectedStart.value = '';
    selectedEnd.value = '';

    if (actionWrap) {
      actionWrap.hidden = true;
    }

    if (selectedText) {
      selectedText.textContent = '';
    }

    if (!Array.isArray(slots) || slots.length === 0) {
      updateTimezoneNotice(widget, (config.labels.businessTimezoneFormat || '').replace('%1$s', String(config.siteTimezone || 'UTC')));
      renderMessage(container, labels.noSlots);
      return;
    }

    var html = '<h3 class="zab-slot-results__title">' + escapeHtml(labels.slotsHeader) + '</h3>';
    html += '<div class="zab-slot-results__grid">';

    slots.forEach(function (slot) {
      var display = formatSlotDisplay(slot, config);
      html += '<button type="button" class="zab-slot-button" data-start="' + escapeHtml(slot.start) + '" data-end="' + escapeHtml(slot.end) + '" data-utc-start="' + escapeHtml(slot.utc_start || '') + '" data-utc-end="' + escapeHtml(slot.utc_end || '') + '" data-site-date="' + escapeHtml(slot.site_date || '') + '">';
      html += '<span>' + escapeHtml(display.label) + '</span>';
      html += '</button>';

      updateTimezoneNotice(widget, display.timezoneText);
    });

    html += '</div>';
    container.innerHTML = html;
  }

  function getRestoreSelection(widget, config) {
    var selection = (config && config.cartSelection) ? config.cartSelection : null;

    if (!selection || typeof selection !== 'object') {
      return null;
    }

    if (!selection.date || !Array.isArray(selection.slots) || selection.slots.length === 0) {
      return null;
    }

    var dateInput = widget.querySelector('.zab-booking-date');
    if (!dateInput || dateInput.value !== selection.date) {
      return null;
    }

    var serviceId = getServiceId(widget);
    var targetServiceId = parseInt(selection.serviceId || 0, 10);

    if (targetServiceId > 0 && serviceId !== targetServiceId) {
      return null;
    }

    return selection;
  }

  function applyRestoredSelection(widget, config) {
    if (widget.dataset.zabRestoreApplied === '1') {
      return;
    }

    var selection = getRestoreSelection(widget, config);
    if (!selection) {
      return;
    }

    var slotsMap = {};
    selection.slots.forEach(function (slot) {
      if (!slot || !slot.start || !slot.end) {
        return;
      }
      slotsMap[String(slot.start) + '|' + String(slot.end)] = true;
    });

    var firstActivated = false;
    var buttons = widget.querySelectorAll('.zab-slot-button');
    buttons.forEach(function (button) {
      var key = (button.getAttribute('data-start') || '') + '|' + (button.getAttribute('data-end') || '');
      if (!slotsMap[key]) {
        return;
      }

      if (isMultiMode(widget, config)) {
        button.classList.add('is-active');
        return;
      }

      if (!firstActivated) {
        button.classList.add('is-active');
        firstActivated = true;
      }
    });

    widget.dataset.zabRestoreApplied = '1';
    updateSelectionSummary(widget, config);
  }

  function getServiceId(widget) {
    var select = widget.querySelector('.zab-booking-service');
    if (!select) {
      var fallback = parseInt((zabBookingData && zabBookingData.defaultServiceId) || 0, 10);
      return Number.isNaN(fallback) ? 0 : fallback;
    }

    var parsed = parseInt(select.value, 10);
    return Number.isNaN(parsed) ? 0 : parsed;
  }

  function isMultiMode(widget, config) {
    if (!config.allowMultipleAppointments) {
      return false;
    }

    var toggle = widget.querySelector('.zab-multi-toggle');
    return !!(toggle && toggle.checked);
  }

  function getSelectedSlots(widget) {
    var selected = [];
    var activeButtons = widget.querySelectorAll('.zab-slot-button.is-active');

    activeButtons.forEach(function (button) {
      var start = button.getAttribute('data-start') || '';
      var end = button.getAttribute('data-end') || '';
      var utcStart = button.getAttribute('data-utc-start') || '';
      var utcEnd = button.getAttribute('data-utc-end') || '';
      var siteDate = button.getAttribute('data-site-date') || '';

      if (start && end) {
        selected.push({ start: start, end: end, utc_start: utcStart, utc_end: utcEnd, site_date: siteDate });
      }
    });

    selected.sort(function (a, b) {
      return a.start.localeCompare(b.start);
    });

    return selected;
  }

  function updateSelectionSummary(widget, config) {
    var selectedStart = widget.querySelector('.zab-selected-slot-start');
    var selectedEnd = widget.querySelector('.zab-selected-slot-end');
    var dateInput = widget.querySelector('.zab-booking-date');
    var actionWrap = widget.querySelector('.zab-booking-action');
    var selectedText = widget.querySelector('.zab-booking-action__selected');
    var slots = getSelectedSlots(widget);
    var multiFormat = config.labels.multiSelectedFormat || 'Selected %1$d slots on %2$s';

    if (slots.length === 0) {
      if (selectedStart) selectedStart.value = '';
      if (selectedEnd) selectedEnd.value = '';
      if (actionWrap) actionWrap.hidden = true;
      if (selectedText) selectedText.textContent = '';
      return;
    }

    if (selectedStart) selectedStart.value = slots[0].start;
    if (selectedEnd) selectedEnd.value = slots[0].end;

    if (!actionWrap || !selectedText) {
      return;
    }

    if (slots.length > 1) {
      selectedText.textContent = multiFormat
        .replace('%1$d', String(slots.length))
        .replace('%2$s', dateInput ? (dateInput.value || '') : '');
    } else {
      var display = formatSlotDisplay(slots[0], config);
      selectedText.textContent = display.summary;

      updateTimezoneNotice(widget, display.timezoneText);
    }

    actionWrap.hidden = false;
  }

  function setActionButtonState(widget, isBusy, labels) {
    var actionButton = widget.querySelector('.zab-booking-action__button');

    if (!actionButton) {
      return;
    }

    if (isBusy) {
      actionButton.disabled = true;
      actionButton.classList.add('is-busy');
      actionButton.textContent = labels.processingCheckout || labels.continueCheckout;
      return;
    }

    actionButton.disabled = false;
    actionButton.classList.remove('is-busy');
    actionButton.textContent = labels.continueCheckout;
  }

  function startCheckout(widget, config) {
    var dateInput = widget.querySelector('.zab-booking-date');

    var slots = getSelectedSlots(widget);

    if (!dateInput || !dateInput.value || slots.length === 0) {
      window.alert(config.labels.selectSlotFirst || config.labels.selectDateFirst || '');
      return;
    }

    if (widget.dataset.zabProcessing === '1') {
      return;
    }

    var cartSelection = (config && config.cartSelection) ? config.cartSelection : null;
    var hasExisting = cartSelection && cartSelection.date && Array.isArray(cartSelection.slots) && cartSelection.slots.length > 0;
    if (hasExisting && !config.allowMultipleAppointments) {
      var isNewSelection = cartSelection.date !== dateInput.value || !isSameSlotsSelected(slots, cartSelection.slots);
      if (isNewSelection && !window.confirm(config.labels.replaceSelectionConfirm || '')) {
        return;
      }
    }

    widget.dataset.zabProcessing = '1';
    setActionButtonState(widget, true, config.labels);

    var formData = new FormData();
    formData.append('action', 'zab_start_checkout');
    formData.append('nonce', config.nonce);
    formData.append('date', dateInput.value);
    formData.append('start_time', slots[0].start);
    formData.append('end_time', slots[0].end);
    formData.append('slots', JSON.stringify(slots));
    formData.append('service_id', String(getServiceId(widget)));
    formData.append('browser_tz', getBrowserTimeZone());

    fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || payload.success !== true || !payload.data || !payload.data.checkout_url) {
          var errorMessage = (payload && payload.data && payload.data.message) ? payload.data.message : config.labels.checkoutError;
          window.alert(errorMessage || '');
          return;
        }

        window.location.href = payload.data.checkout_url;
      })
      .catch(function () {
        window.alert(config.labels.checkoutError || '');
      })
      .finally(function () {
        widget.dataset.zabProcessing = '0';
        setActionButtonState(widget, false, config.labels);
      });
  }

  function fetchSlots(widget, config) {
    var dateInput = widget.querySelector('.zab-booking-date');
    var resultContainer = widget.querySelector('.zab-slot-results');

    if (!dateInput || !resultContainer) {
      return;
    }

    if (!dateInput.value) {
      renderMessage(resultContainer, config.labels.selectDateFirst);
      return;
    }

    renderMessage(resultContainer, config.labels.loading);

    var formData = new FormData();
    formData.append('action', 'zab_get_available_slots');
    formData.append('nonce', config.nonce);
    formData.append('date', dateInput.value);
    formData.append('service_id', String(getServiceId(widget)));

    fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || payload.success !== true) {
          renderMessage(resultContainer, config.labels.error, 'error');
          return;
        }

        renderSlots(widget, payload.data.slots, config.labels);
        applyRestoredSelection(widget, config);
      })
      .catch(function () {
        renderMessage(resultContainer, config.labels.error, 'error');
      });
  }

  function bindSlotSelection(widget, config) {
    widget.addEventListener('click', function (event) {
      var button = event.target.closest('.zab-slot-button');
      if (!button) {
        return;
      }

      var buttons = widget.querySelectorAll('.zab-slot-button');

      if (isMultiMode(widget, config)) {
        button.classList.toggle('is-active');
      } else {
        buttons.forEach(function (item) {
          item.classList.remove('is-active');
        });
        button.classList.add('is-active');
      }

      updateSelectionSummary(widget, config);

      if (config.redirectToCheckout && !isMultiMode(widget, config)) {
        startCheckout(widget, config);
      }
    });

    widget.addEventListener('click', function (event) {
      var continueButton = event.target.closest('.zab-booking-action__button');
      if (!continueButton) {
        return;
      }

      if (getSelectedSlots(widget).length === 0) {
        window.alert(config.labels.noSlotSelected || '');
        return;
      }

      startCheckout(widget, config);
    });
  }

  function initDatePicker(widget, config) {
    var dateInput = widget.querySelector('.zab-booking-date');
    if (!dateInput) {
      return;
    }

    var preferredDefaultDate = getPreferredDefaultDate(dateInput);

    if (preferredDefaultDate) {
      dateInput.value = preferredDefaultDate;
    }

    if (typeof flatpickr === 'function') {
      flatpickr(dateInput, {
        dateFormat: 'Y-m-d',
        defaultDate: preferredDefaultDate || null,
        minDate: dateInput.getAttribute('data-min-date') || 'today',
        disableMobile: true,
        onChange: function () {
          fetchSlots(widget, config);
        }
      });
      return;
    }

    dateInput.setAttribute('type', 'date');
    dateInput.setAttribute('min', dateInput.getAttribute('data-min-date') || '');
    dateInput.addEventListener('change', function () {
      fetchSlots(widget, config);
    });
  }

  function initWidget(widget, config) {
    var serviceSelect = widget.querySelector('.zab-booking-service');
    var continueButton = widget.querySelector('.zab-booking-action__button');
    var multiToggle = widget.querySelector('.zab-multi-toggle');
    var cartSelection = (config && config.cartSelection) ? config.cartSelection : null;

    if (cartSelection && typeof cartSelection === 'object') {
      if (serviceSelect && cartSelection.serviceId) {
        serviceSelect.value = String(cartSelection.serviceId);
      }

      var dateInput = widget.querySelector('.zab-booking-date');
      if (dateInput && cartSelection.date) {
        dateInput.value = String(cartSelection.date);
      }
    }

    initDatePicker(widget, config);
    bindSlotSelection(widget, config);

    if (continueButton && config.labels && config.labels.continueCheckout) {
      continueButton.textContent = config.labels.continueCheckout;
    }

    widget.dataset.zabProcessing = '0';

    updateTimezoneNotice(
      widget,
      (config.labels.businessTimezoneFormat || '').replace('%1$s', String(config.siteTimezone || 'UTC'))
    );

    if (serviceSelect) {
      serviceSelect.addEventListener('change', function () {
        fetchSlots(widget, config);
      });
    }

    if (multiToggle) {
      multiToggle.addEventListener('change', function () {
        if (!multiToggle.checked) {
          var activeButtons = widget.querySelectorAll('.zab-slot-button.is-active');
          activeButtons.forEach(function (button, index) {
            if (index > 0) {
              button.classList.remove('is-active');
            }
          });
        }

        updateSelectionSummary(widget, config);
      });

      if (cartSelection && Array.isArray(cartSelection.slots) && cartSelection.slots.length > 1) {
        multiToggle.checked = true;
      }
    }

    fetchSlots(widget, config);
  }

  function isSameSlotsSelected(slots1, slots2) {
    if (!Array.isArray(slots1) || !Array.isArray(slots2)) {
      return false;
    }
    if (slots1.length !== slots2.length) {
      return false;
    }
    var map1 = {};
    slots1.forEach(function (slot) {
      if (slot && slot.start && slot.end) {
        map1[String(slot.start) + '|' + String(slot.end)] = true;
      }
    });
    for (var i = 0; i < slots2.length; i++) {
      var slot = slots2[i];
      if (!slot || !slot.start || !slot.end) {
        return false;
      }
      var key = String(slot.start) + '|' + String(slot.end);
      if (!map1[key]) {
        return false;
      }
    }
    return true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof zabBookingData === 'undefined') {
      return;
    }

    var widgets = document.querySelectorAll('[data-zab-widget="1"]');

    widgets.forEach(function (widget) {
      initWidget(widget, zabBookingData);
    });
  });
})();
