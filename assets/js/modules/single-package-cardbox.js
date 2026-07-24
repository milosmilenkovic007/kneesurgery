document.addEventListener('DOMContentLoaded', function () {
  var intlTelInputFactory = window.intlTelInput;
  var fallbackCountry = 'gb';

  var lookupCountryByIp = function (success) {
    var storageKey = 'hj-visitor-country';
    var controller = typeof window.AbortController === 'function'
      ? new window.AbortController()
      : null;
    var settled = false;
    var timeoutId = null;
    var finish = function (country) {
      if (settled) {
        return;
      }
      settled = true;
      window.clearTimeout(timeoutId);
      success(country);
    };
    timeoutId = window.setTimeout(function () {
      if (controller) {
        controller.abort();
      }
      finish(fallbackCountry);
    }, 3000);

    try {
      var cachedCountry = window.sessionStorage.getItem(storageKey);
      if (/^[a-z]{2}$/i.test(cachedCountry || '')) {
        finish(cachedCountry.toLowerCase());
        return;
      }
    } catch (error) {
      // Storage can be unavailable in private browsing; continue with the lookup.
    }

    fetch('https://api.country.is/', {
      method: 'GET',
      mode: 'cors',
      credentials: 'omit',
      signal: controller ? controller.signal : undefined,
      headers: {
        Accept: 'application/json'
      }
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Country lookup failed.');
        }
        return response.json();
      })
      .then(function (data) {
        var country = data && typeof data.country === 'string'
          ? data.country.toLowerCase()
          : '';

        if (!/^[a-z]{2}$/.test(country)) {
          throw new Error('Country lookup returned an invalid country.');
        }

        try {
          window.sessionStorage.setItem(storageKey, country);
        } catch (error) {
          // The selected country still works when storage is unavailable.
        }

        finish(country);
      })
      .catch(function () {
        finish(fallbackCountry);
      });
  };

  var syncFallbackButtonState = function (form, submitButton) {
    if (!form || !submitButton) {
      return;
    }

    submitButton.disabled = !form.checkValidity();
  };

  document.querySelectorAll('.hj-spc-form').forEach(function (form) {
    var submitButton = document.querySelector('.hj-spc-form__submit[form="' + form.id + '"]');
    var phoneInput = form.querySelector('.hj-spc-phone-input');
    var phoneField = phoneInput ? phoneInput.closest('.hj-spc-form__field--phone') : null;
    var hiddenPhoneInput = form.querySelector('input[type="hidden"][name="phone"]');
    var hiddenCountryInput = form.querySelector('input[type="hidden"][name="country_code"]');

    if (!submitButton || !phoneInput || typeof intlTelInputFactory !== 'function') {
      if (hiddenPhoneInput && phoneInput) {
        hiddenPhoneInput.value = phoneInput.value.trim();
      }
      syncFallbackButtonState(form, submitButton);
      form.addEventListener('input', function () {
        if (hiddenPhoneInput && phoneInput) {
          hiddenPhoneInput.value = phoneInput.value.trim();
        }
        syncFallbackButtonState(form, submitButton);
      });
      form.addEventListener('change', function () {
        if (hiddenPhoneInput && phoneInput) {
          hiddenPhoneInput.value = phoneInput.value.trim();
        }
        syncFallbackButtonState(form, submitButton);
      });
      return;
    }

    var iti = intlTelInputFactory(phoneInput, {
      initialCountry: fallbackCountry,
      nationalMode: false,
      formatAsYouType: true,
      autoPlaceholder: 'polite',
      placeholderNumberType: 'MOBILE',
      strictMode: true,
      countrySearch: true,
      fixDropdownWidth: false
    });
    var geoCountryPending = true;
    var applyingGeoCountry = false;

    phoneInput.addEventListener('countrychange', function () {
      if (!applyingGeoCountry) {
        geoCountryPending = false;
      }
    });

    lookupCountryByIp(function (country) {
      if (geoCountryPending && /^[a-z]{2}$/.test(country)) {
        applyingGeoCountry = true;
        iti.setCountry(country);
        applyingGeoCountry = false;
      }
      geoCountryPending = false;
    });

    var syncPhoneState = function () {
      var hasValue = phoneInput.value.trim() !== '';
      var isPhoneValid = hasValue && iti.isValidNumber();
      var selectedCountry = iti.getSelectedCountryData();

      if (hiddenPhoneInput) {
        hiddenPhoneInput.value = hasValue ? iti.getNumber() : '';
      }

      if (hiddenCountryInput) {
        hiddenCountryInput.value = selectedCountry && selectedCountry.iso2 ? selectedCountry.iso2 : '';
      }

      if (hasValue && !isPhoneValid) {
        phoneInput.setCustomValidity('Please enter a valid mobile number.');
        if (phoneField) {
          phoneField.classList.add('is-invalid');
        }
      } else {
        phoneInput.setCustomValidity('');
        if (phoneField) {
          phoneField.classList.remove('is-invalid');
        }
      }

      submitButton.disabled = !form.checkValidity() || !isPhoneValid;
    };

    form.addEventListener('input', syncPhoneState);
    form.addEventListener('change', syncPhoneState);
    phoneInput.addEventListener('blur', syncPhoneState);
    phoneInput.addEventListener('countrychange', function () {
      syncPhoneState();
    });
    form.addEventListener('submit', function (event) {
      syncPhoneState();
      if (submitButton.disabled) {
        event.preventDefault();
      }
    });

    if (iti.promise && typeof iti.promise.then === 'function') {
      iti.promise.then(syncPhoneState).catch(syncPhoneState);
    } else {
      syncPhoneState();
    }
  });
});
