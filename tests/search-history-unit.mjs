import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../assets/search.js', import.meta.url), 'utf8');
const key = 'dz-search-history';
function searchWidget(stored) {
  const storage = new Map([[key, stored]]);
  const nodes = new Map();
  const node = selector => {
    if (!nodes.has(selector)) nodes.set(selector, {
      value: '', dataset: {}, handlers: new Map(),
      addEventListener(type, handler) { this.handlers.set(type, handler); },
      querySelector: node, querySelectorAll: () => [], closest: node,
      style: {setProperty() {}}, classList: {toggle() {}},
    });
    return nodes.get(selector);
  };
  vm.runInNewContext(source, {
    document: {querySelector: node, querySelectorAll: () => [], addEventListener() {}, documentElement: node('html')},
    sessionStorage: {getItem: name => storage.get(name), setItem: (name, value) => storage.set(name, value)},
    window: {}, innerHeight: 900, addEventListener() {},
  });
  return {
    history: () => JSON.parse(storage.get(key)),
    submit(query) { node('[data-search-input]').value = query; node('form').handlers.get('submit')(); },
  };
}

const widget = searchWidget(JSON.stringify([
  'детский стоматолог', 'стоматолог для ребёнка', 'лечение подростков',
  'молочные зубы', 'брекеты', 'костный гребень',
]));
assert.deepEqual(widget.history(), ['брекеты', 'костный гребень'], 'Existing pediatric history is purged on startup without removing adult bone-treatment wording');
for (const query of ['ДЕТСКИЙ СТОМАТОЛОГ', 'стоматолог для ребенка', 'приём ребёнка', 'лечение детей', 'помощь подросткам']) {
  widget.submit(query);
  assert.deepEqual(widget.history(), ['брекеты', 'костный гребень'], `Pediatric query is not saved: ${query}`);
}
widget.submit('Марет');
assert.deepEqual(widget.history(), ['Марет', 'брекеты', 'костный гребень'], 'Adult doctor search remains available');
widget.submit('БРЕКЕТЫ');
assert.deepEqual(widget.history(), ['БРЕКЕТЫ', 'Марет', 'костный гребень'], 'Adult history still deduplicates case-insensitively');
assert.deepEqual(searchWidget('{invalid json').history(), [], 'Invalid stored history resets safely');
assert.deepEqual(searchWidget(JSON.stringify({query: 'брекеты'})).history(), [], 'Non-array stored history resets safely');
widget.submit('Удаление молочного зуба');
assert.equal(widget.history()[0], 'Удаление молочного зуба', 'Specifically approved extraction can be saved in search history');
console.log('PASS: unavailable pediatric queries omitted; adult history and approved extraction retained');
