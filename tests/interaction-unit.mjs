import assert from 'node:assert/strict';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const phone = require('../assets/phone-mask.js');

assert.equal(phone.formatPhone(''), '');
assert.equal(phone.formatPhone('9991234567'), '+7 (999) 123-45-67');
assert.equal(phone.formatPhone('89991234567'), '+7 (999) 123-45-67');
assert.equal(phone.formatPhone('+7 (999) 123-45-67'), '+7 (999) 123-45-67');
assert.equal(phone.nationalDigits('8 916 12'), '91612');
assert.equal(phone.nationalDigits('8 916 123 45 67'), '9161234567');
assert.equal(phone.nationalDigits('+7 916 123 45 67 extra'), '9161234567');

const formatted = '+7 (999) 123-45-67';
assert.equal(phone.nationalCountBefore(formatted, formatted.indexOf(')')), 3);
assert.equal(phone.caretForDigitCount(formatted, 3), 7);
assert.equal(phone.formatPhone(phone.removeDigit(formatted, 2)), '+7 (991) 234-56-7');
assert.equal(phone.formatPhone(phone.removeDigit(formatted, 3)), '+7 (999) 234-56-7');
assert.equal(phone.removeDigit(formatted, 20), '9991234567');

console.log('interaction-unit: ok');
