document.addEventListener('DOMContentLoaded', function () {
  var intlTelInputFactory = window.intlTelInput;
  var fallbackCountry = 'gb';

  var lookupCountryByIp = function (success, failure) {
    var storageKey = 'hj-visitor-country';

    try {
      var cachedCountry = window.sessionStorage.getItem(storageKey);
      if (/^[a-z]{2}$/i.test(cachedCountry || '')) {
        success(cachedCountry.toLowerCase());
        return;
      }
    } catch (error) {
      // Storage can be unavailable in private browsing; continue with the lookup.
    }

    fetch('https://ipapi.co/json/', {
      method: 'GET',
      mode: 'cors',
      credentials: 'omit',
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
        var country = data && typeof data.country_code === 'string'
          ? data.country_code.toLowerCase()
          : '';

        if (!/^[a-z]{2}$/.test(country)) {
          throw new Error('Country lookup returned an invalid country.');
        }

        try {
          window.sessionStorage.setItem(storageKey, country);
        } catch (error) {
          // The selected country still works when storage is unavailable.
        }

        success(country);
      })
      .catch(function () {
        success(fallbackCountry);
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
      initialCountry: 'auto',
      geoIpLookup: lookupCountryByIp,
      nationalMode: false,
      formatAsYouType: true,
      autoPlaceholder: 'polite',
      placeholderNumberType: 'MOBILE',
      strictMode: true,
      countrySearch: true,
      fixDropdownWidth: false
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
    phoneInput.addEventListener('countrychange', syncPhoneState);
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
