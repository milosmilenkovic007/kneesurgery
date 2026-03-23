document.addEventListener('DOMContentLoaded', function () {
  var intlTelInputFactory = window.intlTelInput;

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
      initialCountry: 'gb',
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