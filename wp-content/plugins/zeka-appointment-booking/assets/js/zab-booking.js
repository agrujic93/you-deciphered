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

    var currentDate = getCurrentDate(widget);
    var currentServiceId = getServiceId(widget);

    slots.forEach(function (slot) {
      var display = formatSlotDisplay(slot, config);
      var slotDate = slot.site_date || currentDate;

      html += '<button type="button" class="zab-slot-button" data-service-id="' + escapeHtml(String(currentServiceId)) + '" data-date="' + escapeHtml(slotDate || '') + '" data-start="' + escapeHtml(slot.start) + '" data-end="' + escapeHtml(slot.end) + '" data-utc-start="' + escapeHtml(slot.utc_start || '') + '" data-utc-end="' + escapeHtml(slot.utc_end || '') + '" data-site-date="' + escapeHtml(slot.site_date || slotDate || '') + '">';
      html += '<span>' + escapeHtml(display.label) + '</span>';
      html += '</button>';

      updateTimezoneNotice(widget, display.timezoneText);
    });

    html += '</div>';
    container.innerHTML = html;
  }

  function makeSelectionKey(slot) {
    return [slot.service_id || 0, slot.date || '', slot.start || '', slot.end || ''].join('|');
  }

  function getSelectionStore(widget) {
    if (!widget.zabSelectionStore || typeof widget.zabSelectionStore !== 'object') {
      widget.zabSelectionStore = {};
    }

    return widget.zabSelectionStore;
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

  function getCurrentDate(widget) {
    var dateInput = widget.querySelector('.zab-booking-date');
    return dateInput && dateInput.value ? String(dateInput.value) : '';
  }

  function normalizeSlotForStore(widget, slot) {
    if (!slot || !slot.start || !slot.end) {
      return null;
    }

    var serviceId = parseInt(slot.service_id || getServiceId(widget), 10);
    if (Number.isNaN(serviceId)) {
      serviceId = 0;
    }

    var date = String(slot.date || slot.site_date || getCurrentDate(widget) || '');

    if (!date) {
      return null;
    }

    return {
      service_id: serviceId,
      date: date,
      start: String(slot.start),
      end: String(slot.end),
      utc_start: String(slot.utc_start || ''),
      utc_end: String(slot.utc_end || ''),
      site_date: String(slot.site_date || date)
    };
  }

  function seedSelectionStoreFromCart(widget, config) {
    var store = getSelectionStore(widget);

    if (Object.keys(store).length > 0) {
      return;
    }

    var cartSelection = (config && config.cartSelection) ? config.cartSelection : null;

    if (!cartSelection || typeof cartSelection !== 'object') {
      return;
    }

    var sourceSlots = [];
    if (Array.isArray(cartSelection.selections) && cartSelection.selections.length > 0) {
      sourceSlots = cartSelection.selections;
    } else if (Array.isArray(cartSelection.slots) && cartSelection.slots.length > 0) {
      sourceSlots = cartSelection.slots.map(function (slot) {
        return {
          service_id: cartSelection.serviceId || 0,
          date: cartSelection.date || '',
          start: slot.start,
          end: slot.end,
          utc_start: slot.utc_start || '',
          utc_end: slot.utc_end || '',
          site_date: slot.site_date || cartSelection.date || ''
        };
      });
    }

    sourceSlots.forEach(function (slot) {
      var normalized = normalizeSlotForStore(widget, slot);
      if (!normalized) {
        return;
      }

      store[makeSelectionKey(normalized)] = normalized;
    });
  }

  function setSlotSelected(widget, slot, selected, clearAllFirst) {
    var store = getSelectionStore(widget);
    var normalized = normalizeSlotForStore(widget, slot);

    if (!normalized) {
      return;
    }

    if (clearAllFirst) {
      widget.zabSelectionStore = {};
      store = getSelectionStore(widget);
    }

    var key = makeSelectionKey(normalized);

    if (selected) {
      store[key] = normalized;
      return;
    }

    delete store[key];
  }

  function isSlotSelected(widget, slot) {
    var normalized = normalizeSlotForStore(widget, slot);

    if (!normalized) {
      return false;
    }

    var store = getSelectionStore(widget);
    return !!store[makeSelectionKey(normalized)];
  }

  function getSelectedSlots(widget) {
    var store = getSelectionStore(widget);
    var selected = Object.keys(store).map(function (key) {
      return store[key];
    });

    selected.sort(function (a, b) {
      var dateCompare = String(a.date).localeCompare(String(b.date));
      if (dateCompare !== 0) {
        return dateCompare;
      }

      var startCompare = String(a.start).localeCompare(String(b.start));
      if (startCompare !== 0) {
        return startCompare;
      }

      return String(a.end).localeCompare(String(b.end));
    });

    return selected;
  }

  function syncActiveButtonsFromStore(widget) {
    var buttons = widget.querySelectorAll('.zab-slot-button');

    buttons.forEach(function (button) {
      var slot = {
        service_id: button.getAttribute('data-service-id') || getServiceId(widget),
        date: button.getAttribute('data-date') || '',
        start: button.getAttribute('data-start') || '',
        end: button.getAttribute('data-end') || '',
        utc_start: button.getAttribute('data-utc-start') || '',
        utc_end: button.getAttribute('data-utc-end') || '',
        site_date: button.getAttribute('data-site-date') || ''
      };

      if (isSlotSelected(widget, slot)) {
        button.classList.add('is-active');
      } else {
        button.classList.remove('is-active');
      }
    });
  }

  function pruneUnavailableSelectionsForCurrentView(widget, slots) {
    var serviceId = getServiceId(widget);
    var date = getCurrentDate(widget);

    if (!date) {
      return;
    }

    var available = {};

    (Array.isArray(slots) ? slots : []).forEach(function (slot) {
      if (!slot || !slot.start || !slot.end) {
        return;
      }

      var normalized = normalizeSlotForStore(widget, {
        service_id: serviceId,
        date: slot.site_date || date,
        start: slot.start,
        end: slot.end,
        utc_start: slot.utc_start || '',
        utc_end: slot.utc_end || '',
        site_date: slot.site_date || date
      });

      if (!normalized) {
        return;
      }

      available[makeSelectionKey(normalized)] = true;
    });

    var store = getSelectionStore(widget);
    Object.keys(store).forEach(function (key) {
      var slot = store[key];

      if (!slot) {
        return;
      }

      if (parseInt(slot.service_id, 10) !== serviceId || String(slot.date) !== String(date)) {
        return;
      }

      if (!available[key]) {
        delete store[key];
      }
    });
  }

  function updateSelectionSummary(widget, config) {
    var selectedStart = widget.querySelector('.zab-selected-slot-start');
    var selectedEnd = widget.querySelector('.zab-selected-slot-end');
    var actionWrap = widget.querySelector('.zab-booking-action');
    var selectedText = widget.querySelector('.zab-booking-action__selected');
    var slots = getSelectedSlots(widget);
    var multiFormat = config.labels.multiSelectedGlobalFormat || config.labels.multiSelectedFormat || 'Selected %1$d appointments';

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
      var countText = multiFormat.replace('%1$d', String(slots.length));
      var appointmentDetails = slots.map(function (slot) {
        return String(slot.date || '') + ' ' + String(slot.start || '') + ' - ' + String(slot.end || '');
      }).join(' | ');
      selectedText.textContent = countText + ': ' + appointmentDetails;
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

    if (slots.length === 0) {
      window.alert(config.labels.selectSlotFirst || config.labels.selectDateFirst || '');
      return;
    }

    if (!dateInput || !dateInput.value) {
      window.alert(config.labels.selectDateFirst || '');
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
    formData.append('date', String(slots[0].date || dateInput.value || ''));
    formData.append('start_time', slots[0].start);
    formData.append('end_time', slots[0].end);
    formData.append('slots', JSON.stringify(slots));
    formData.append('service_id', String(slots[0].service_id || getServiceId(widget)));
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
        if (!payload || payload.success !== true || !payload.data) {
          var errorMessage = (payload && payload.data && payload.data.message) ? payload.data.message : config.labels.checkoutError;
          window.alert(errorMessage || '');
          return;
        }

        var redirectUrl = payload.data.cart_url || payload.data.checkout_url || '';

        if (!redirectUrl) {
          window.alert(config.labels.checkoutError || '');
          return;
        }

        window.location.href = redirectUrl;
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
        pruneUnavailableSelectionsForCurrentView(widget, payload.data.slots);
        syncActiveButtonsFromStore(widget);
        updateSelectionSummary(widget, config);
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

      var slot = {
        service_id: button.getAttribute('data-service-id') || getServiceId(widget),
        date: button.getAttribute('data-date') || getCurrentDate(widget),
        start: button.getAttribute('data-start') || '',
        end: button.getAttribute('data-end') || '',
        utc_start: button.getAttribute('data-utc-start') || '',
        utc_end: button.getAttribute('data-utc-end') || '',
        site_date: button.getAttribute('data-site-date') || button.getAttribute('data-date') || ''
      };

      if (isMultiMode(widget, config)) {
        var nextSelected = !isSlotSelected(widget, slot);
        setSlotSelected(widget, slot, nextSelected, false);
      } else {
        setSlotSelected(widget, slot, true, true);
      }

      syncActiveButtonsFromStore(widget);
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

    seedSelectionStoreFromCart(widget, config);

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
          var selectedSlots = getSelectedSlots(widget);

          if (selectedSlots.length > 1) {
            setSlotSelected(widget, selectedSlots[0], true, true);
          }
        }

        syncActiveButtonsFromStore(widget);
        updateSelectionSummary(widget, config);
      });

      if (getSelectedSlots(widget).length > 1) {
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
