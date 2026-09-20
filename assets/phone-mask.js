((global, factory) => {
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  else global.DzPhoneMask = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, () => {
  'use strict';

  const onlyDigits = (value) => String(value || '').replace(/\D/g, '');

  function nationalDigits(value) {
    const source = String(value || '');
    let digits = onlyDigits(source);
    if (/^\s*\+7/.test(source) || /^\s*8[\s(\-]/.test(source) || (digits.length === 11 && (digits[0] === '7' || digits[0] === '8'))) digits = digits.slice(1);
    else if (digits.length === 1 && (digits === '7' || digits === '8')) digits = '';
    return digits.slice(0, 10);
  }

  function formatPhone(value) {
    const digits = nationalDigits(value);
    if (!digits) return '';
    let output = '+7 (' + digits.slice(0, 3);
    if (digits.length >= 3) output += ') ';
    if (digits.length > 3) output += digits.slice(3, 6);
    if (digits.length > 6) output += '-' + digits.slice(6, 8);
    if (digits.length > 8) output += '-' + digits.slice(8, 10);
    return output;
  }

  function nationalCountBefore(value, caret) {
    const fragment = String(value || '').slice(0, Math.max(0, caret));
    const digits = onlyDigits(fragment);
    const hasCountryPrefix = /^\s*(?:\+?7|8)/.test(fragment);
    return Math.max(0, digits.length - (hasCountryPrefix ? 1 : 0));
  }

  function caretForDigitCount(formatted, count) {
    if (!formatted) return 0;
    if (count <= 0) return Math.min(4, formatted.length);
    let seen = 0;
    for (let index = 0; index < formatted.length; index += 1) {
      if (!/\d/.test(formatted[index]) || index === 1) continue;
      seen += 1;
      if (seen === count) return index + 1;
    }
    return formatted.length;
  }

  function removeDigit(value, digitIndex) {
    const digits = nationalDigits(value);
    if (digitIndex < 0 || digitIndex >= digits.length) return digits;
    return digits.slice(0, digitIndex) + digits.slice(digitIndex + 1);
  }

  function validate(input) {
    const count = nationalDigits(input.value).length;
    input.setCustomValidity(input.value && count !== 10 ? 'Введите номер полностью: +7 (999) 999-99-99.' : '');
  }

  function setMaskedValue(input, digits, digitCaret) {
    const formatted = formatPhone(digits);
    input.value = formatted;
    const caret = caretForDigitCount(formatted, digitCaret);
    input.setSelectionRange?.(caret, caret);
    validate(input);
  }

  function initInput(input) {
    if (input.dataset.phoneMaskReady === 'true') return;
    input.dataset.phoneMaskReady = 'true';
    input.removeAttribute('maxlength');
    input.inputMode = 'tel';
    if (!input.autocomplete) input.autocomplete = 'tel';
    if (!input.placeholder) input.placeholder = '+7 (999) 999-99-99';
    if (input.value) input.value = formatPhone(input.value);
    validate(input);

    input.addEventListener('beforeinput', (event) => {
      if (!['deleteContentBackward', 'deleteContentForward'].includes(event.inputType)) return;
      const start = input.selectionStart ?? 0;
      const end = input.selectionEnd ?? start;
      if (start !== end) return;
      const before = nationalCountBefore(input.value, start);
      const removeAt = event.inputType === 'deleteContentBackward' ? before - 1 : before;
      if (removeAt < 0 || removeAt >= nationalDigits(input.value).length) return;
      event.preventDefault();
      setMaskedValue(input, removeDigit(input.value, removeAt), event.inputType === 'deleteContentBackward' ? before - 1 : before);
    });

    input.addEventListener('input', () => {
      const start = input.selectionStart ?? input.value.length;
      const caretDigits = nationalCountBefore(input.value, start);
      setMaskedValue(input, nationalDigits(input.value), caretDigits);
    });
    input.addEventListener('blur', () => validate(input));
  }

  function init(root = document) {
    root.querySelectorAll('input[type="tel"]').forEach(initInput);
  }

  if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init(), { once: true });
    else init();
  }

  return { onlyDigits, nationalDigits, formatPhone, nationalCountBefore, caretForDigitCount, removeDigit, initInput, init };
});
