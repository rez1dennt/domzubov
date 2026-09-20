(() => {
  'use strict';

  const SELECTOR = '.dz-select';
  const closeTimers = new WeakMap();

  function parts(select) {
    return {
      trigger: select.querySelector('[data-select-trigger]'),
      options: select.querySelector('.dz-select-options[role="listbox"]'),
      label: select.querySelector('[data-select-label]'),
      choices: Array.from(select.querySelectorAll('button[role="option"][data-value]')),
    };
  }

  function openSelect(select, focus = false) {
    const { trigger, options, choices } = parts(select);
    if (!trigger || !options) return;
    const timer = closeTimers.get(select);
    if (timer) clearTimeout(timer);
    closeTimers.delete(select);
    options.hidden = false;
    requestAnimationFrame(() => select.classList.add('is-open'));
    trigger.setAttribute('aria-expanded', 'true');
    if (focus) {
      const selected = choices.find((choice) => choice.getAttribute('aria-selected') === 'true');
      (selected || choices[0])?.focus({ preventScroll: true });
    }
  }

  function closeSelect(select, returnFocus = false) {
    const { trigger, options } = parts(select);
    if (!trigger || !options) return;
    select.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
    const previous = closeTimers.get(select);
    if (previous) clearTimeout(previous);
    closeTimers.set(select, setTimeout(() => {
      if (!select.classList.contains('is-open')) options.hidden = true;
      closeTimers.delete(select);
    }, 180));
    if (returnFocus) trigger.focus({ preventScroll: true });
  }

  function choose(select, choice) {
    const { label, choices } = parts(select);
    const value = choice.dataset.value ?? '';
    choices.forEach((item) => item.setAttribute('aria-selected', String(item === choice)));
    if (label) label.textContent = choice.textContent.trim();
    select.dataset.value = value;
    select.dispatchEvent(new CustomEvent('dz:filter', {
      bubbles: true,
      detail: { value },
    }));
    closeSelect(select, true);
  }

  function moveChoice(select, current, direction) {
    const { choices } = parts(select);
    if (!choices.length) return;
    let index = choices.indexOf(current);
    if (direction === 'home') index = 0;
    else if (direction === 'end') index = choices.length - 1;
    else index = (Math.max(index, 0) + direction + choices.length) % choices.length;
    choices[index].focus({ preventScroll: true });
  }

  function initSelect(select, index) {
    const { trigger, options, choices } = parts(select);
    if (!trigger || !options || !choices.length) return;
    if (!options.id) options.id = `dz-select-options-${index + 1}`;
    trigger.setAttribute('aria-controls', options.id);
    trigger.setAttribute('aria-expanded', 'false');
    options.hidden = true;
    choices.forEach((choice) => {
      if (!choice.hasAttribute('aria-selected')) choice.setAttribute('aria-selected', 'false');
    });
  }

  function filterDoctors(select, value) {
    if (!select.matches('.dz-doctor-filter') && !select.closest('.dz-doctor-filter')) return;
    const scope = select.closest('[data-doctor-list]') || document;
    const wanted = String(value || '').trim().toLocaleLowerCase('ru');
    scope.querySelectorAll('[data-doctor-specialty]').forEach((card) => {
      const specialties = (card.dataset.doctorSpecialty || '')
        .toLocaleLowerCase('ru')
        .split(/[|,;]/)
        .map((item) => item.trim())
        .filter(Boolean);
      card.hidden = Boolean(wanted && wanted !== 'all' && wanted !== 'все' && !specialties.includes(wanted));
    });
  }

  function initCarousel(root) {
    const first = root.querySelector('[data-review-card]');
    window.DZCarousel.mount({ root, kind: 'review', track: first?.parentElement, previous: root.querySelector('[data-review-prev]'), next: root.querySelector('[data-review-next]') });
  }

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-select-trigger]');
    if (trigger) {
      const select = trigger.closest(SELECTOR);
      if (!select) return;
      const isOpen = select.classList.contains('is-open');
      document.querySelectorAll(`${SELECTOR}.is-open`).forEach((other) => {
        if (other !== select) closeSelect(other);
      });
      if (isOpen) closeSelect(select);
      else openSelect(select);
      return;
    }

    const choice = event.target.closest('button[role="option"][data-value]');
    if (choice) {
      const select = choice.closest(SELECTOR);
      if (select) choose(select, choice);
      return;
    }

    document.querySelectorAll(`${SELECTOR}.is-open`).forEach((select) => {
      if (!select.contains(event.target)) closeSelect(select);
    });
  });

  document.addEventListener('keydown', (event) => {
    const select = event.target.closest(SELECTOR);
    if (!select) {
      if (event.key === 'Escape') {
        document.querySelectorAll(`${SELECTOR}.is-open`).forEach((item) => closeSelect(item));
      }
      return;
    }

    const trigger = event.target.closest('[data-select-trigger]');
    const choice = event.target.closest('button[role="option"][data-value]');
    if (trigger && ['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
      event.preventDefault();
      openSelect(select, true);
      const selected = parts(select).choices.find((item) => item.getAttribute('aria-selected') === 'true');
      if (event.key === 'Home' || event.key === 'End') moveChoice(select, selected, event.key.toLowerCase());
      else if (event.key === 'ArrowUp') moveChoice(select, selected, -1);
      return;
    }
    if (trigger && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      if (select.classList.contains('is-open')) closeSelect(select);
      else openSelect(select, true);
      return;
    }
    if (choice) {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        moveChoice(select, choice, event.key === 'ArrowDown' ? 1 : -1);
      } else if (event.key === 'Home' || event.key === 'End') {
        event.preventDefault();
        moveChoice(select, choice, event.key.toLowerCase());
      } else if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        choose(select, choice);
      } else if (event.key === 'Escape') {
        event.preventDefault();
        closeSelect(select, true);
      }
    } else if (event.key === 'Escape') {
      closeSelect(select, true);
    }
  });

  document.addEventListener('dz:filter', (event) => {
    const select = event.target.closest?.(SELECTOR);
    if (select) filterDoctors(select, event.detail?.value);
  });

  const init = () => {
    document.querySelectorAll('main select').forEach((native, index) => {
      if(native.closest('.dz-select'))return;
      const wrapper=document.createElement('div');wrapper.className='dz-select';
      const trigger=document.createElement('button');trigger.type='button';trigger.dataset.selectTrigger='';trigger.setAttribute('aria-haspopup','listbox');
      const label=document.createElement('span');label.dataset.selectLabel='';label.textContent=native.selectedOptions[0]?.textContent||'';
      trigger.append(label);trigger.insertAdjacentHTML('beforeend','<svg class="dz-icon a11y-keep" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>');
      const options=document.createElement('div');options.className='dz-select-options';options.id='native-options-'+index;options.setAttribute('role','listbox');options.setAttribute('aria-label',native.getAttribute('aria-label')||'Выбор варианта');
      [...native.options].forEach(option=>{const button=document.createElement('button');button.type='button';button.setAttribute('role','option');button.dataset.value=option.value;button.textContent=option.textContent;button.setAttribute('aria-selected',String(option.selected));options.append(button);});
      native.before(wrapper);wrapper.append(trigger,options,native);native.hidden=true;
      const arrow=wrapper.nextElementSibling;if(arrow?.tagName.toLowerCase()==='svg')arrow.hidden=true;
      wrapper.addEventListener('dz:filter',event=>{native.value=event.detail.value;native.dispatchEvent(new Event('change',{bubbles:true}));});
      native.addEventListener('change',()=>{label.textContent=native.selectedOptions[0]?.textContent||'';options.querySelectorAll('[role=option]').forEach(o=>o.setAttribute('aria-selected',String(o.dataset.value===native.value)));});
    });
    document.querySelectorAll(SELECTOR).forEach(initSelect);
    document.querySelectorAll('[data-review-carousel]').forEach(initCarousel);
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
