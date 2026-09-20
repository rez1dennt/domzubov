(() => {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const cardAnimations = new WeakMap();

  function animateCardChange(cards, previousRects, direction) {
    if (reduceMotion.matches || !Element.prototype.animate) return;
    cards.forEach((card) => {
      cardAnimations.get(card)?.cancel();
      cardAnimations.delete(card);
    });
    const visible = cards.filter((card) => !card.hidden);
    const referenceWidth = visible[0]?.getBoundingClientRect().width || 0;
    visible.forEach((card) => {
      const nextRect = card.getBoundingClientRect();
      const previousRect = previousRects.get(card);
      const offsetX = previousRect ? previousRect.left - nextRect.left : direction * referenceWidth;
      const offsetY = previousRect ? previousRect.top - nextRect.top : 0;
      const animation = card.animate([
        { transform: `translate(${offsetX}px, ${offsetY}px)`, opacity: previousRect ? 1 : 0.2 },
        { transform: 'translate(0, 0)', opacity: 1 },
      ], { duration: 300, easing: 'cubic-bezier(.22,.61,.36,1)' });
      cardAnimations.set(card, animation);
      const clear = () => { if (cardAnimations.get(card) === animation) cardAnimations.delete(card); };
      animation.addEventListener('finish', clear, { once: true });
      animation.addEventListener('cancel', clear, { once: true });
    });
  }

  function initTeamCarousel(root) {
    const cards = Array.from(root.querySelectorAll('[data-team-card]'));
    const previous = root.querySelector('[data-team-prev]');
    const next = root.querySelector('[data-team-next]');
    if (!cards.length || root.dataset.teamReady === 'true') return;
    root.dataset.teamReady = 'true';

    let start = 0;
    const track = root.querySelector('[data-team-track]');
    const columns = () => Math.max(1, getComputedStyle(track || root).gridTemplateColumns.split(/\s+/).filter(Boolean).length);
    let perPage = columns();

    const render = () => {
      const maximum = Math.max(0, cards.length - perPage);
      start = Math.min(start, maximum);
      cards.forEach((card, index) => {
        card.hidden = index < start || index >= start + perPage;
        card.setAttribute('aria-hidden', String(card.hidden));
      });
      if (previous) previous.disabled = cards.length <= perPage;
      if (next) next.disabled = cards.length <= perPage;
    };

    const move = (direction) => {
      const maximum = Math.max(0, cards.length - perPage);
      if (!maximum) return;
      const previousRects = new Map(cards.filter((card) => !card.hidden).map((card) => [card, card.getBoundingClientRect()]));
      start += direction;
      if (start > maximum) start = 0;
      if (start < 0) start = maximum;
      render();
      animateCardChange(cards, previousRects, direction);
    };

    previous?.addEventListener('click', () => move(-1));
    next?.addEventListener('click', () => move(1));

    const onBreakpoint = () => {
      const updated = columns();
      if (updated === perPage) return;
      perPage = updated;
      start = Math.min(start, Math.max(0, cards.length - perPage));
      render();
    };
    new ResizeObserver(onBreakpoint).observe(track || root);
    render();
  }

  function initGallery(root) {
    if (root.dataset.galleryReady === 'true') return;
    const track = root.querySelector('[data-gallery-track]');
    if (!track) return;
    root.dataset.galleryReady = 'true';

    const items = Array.from(track.children);
    track.style.scrollSnapType = 'x mandatory';
    items.forEach((item) => { item.style.scrollSnapAlign = 'start'; });
    const move = (direction) => {
      if (!items.length) return;
      const current = items.reduce((closest, item, index) => (
        Math.abs(item.offsetLeft - track.scrollLeft) < Math.abs(items[closest].offsetLeft - track.scrollLeft) ? index : closest
      ), 0);
      const target = Math.min(Math.max(current + direction, 0), items.length - 1);
      track.scrollTo({
        left: items[target].offsetLeft,
        behavior: reduceMotion.matches ? 'auto' : 'smooth',
      });
    };

    root.querySelector('[data-gallery-prev]')?.addEventListener('click', () => move(-1));
    root.querySelector('[data-gallery-next]')?.addEventListener('click', () => move(1));
    const updateControls=()=>{
      const previous=root.querySelector('[data-gallery-prev]');const next=root.querySelector('[data-gallery-next]');
      if(previous)previous.disabled=track.scrollLeft<=1;
      if(next)next.disabled=track.scrollLeft>=track.scrollWidth-track.clientWidth-1;
    };
    track.addEventListener('scroll',updateControls,{passive:true});
    new ResizeObserver(updateControls).observe(track);updateControls();
  }

  function initComparison(frame) {
    if (frame.dataset.comparisonReady === 'true') return;
    const images = Array.from(frame.querySelectorAll('img'));
    const beforeImage = images.find((image) => /^до$/i.test(image.alt.trim())) || images[1];
    const before = beforeImage?.parentElement;
    if (!before || before === frame) return;
    frame.dataset.comparisonReady = 'true';
    const handles = Array.from(frame.children).filter((element) => element !== before && element.style.left);
    const range = document.createElement('input');
    range.type = 'range';
    range.min = '0';
    range.max = '100';
    range.value = '50';
    range.className = 'dz-comparison-range';
    range.setAttribute('aria-label', 'Сравнение изображения до и после');
    Object.assign(range.style, { position: 'absolute', inset: '0', width: '100%', height: '100%', opacity: '0', pointerEvents: 'none', zIndex: '20' });
    frame.append(range);

    const update = (rawValue) => {
      const value = Math.min(100, Math.max(0, Number(rawValue) || 0));
      before.style.width = `${value}%`;
      beforeImage.style.width = `${frame.clientWidth}px`;
      beforeImage.style.maxWidth = 'none';
      handles.forEach((handle) => { handle.style.left = `${value}%`; });
      range.value = String(value);
      range.setAttribute('aria-valuenow', String(Math.round(value)));
    };
    const valueFromPointer = (event) => {
      const box = frame.getBoundingClientRect();
      return ((event.clientX - box.left) / Math.max(1, box.width)) * 100;
    };

    frame.addEventListener('pointerdown', (event) => {
      if (event.target.closest('button, a')) return;
      event.preventDefault();
      frame.setPointerCapture?.(event.pointerId);
      range.focus({ preventScroll: true });
      update(valueFromPointer(event));
    });
    frame.addEventListener('pointermove', (event) => {
      if (!frame.hasPointerCapture?.(event.pointerId)) return;
      event.preventDefault();
      update(valueFromPointer(event));
    });
    const release = (event) => {
      if (frame.hasPointerCapture?.(event.pointerId)) frame.releasePointerCapture(event.pointerId);
    };
    frame.addEventListener('pointerup', release);
    frame.addEventListener('pointercancel', release);
    range.addEventListener('keydown', (event) => {
      const step = event.shiftKey ? 10 : 2;
      if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') { event.preventDefault(); update(Number(range.value) - step); }
      else if (event.key === 'ArrowRight' || event.key === 'ArrowUp') { event.preventDefault(); update(Number(range.value) + step); }
      else if (event.key === 'Home') { event.preventDefault(); update(0); }
      else if (event.key === 'End') { event.preventDefault(); update(100); }
    });
    frame.querySelectorAll('button').forEach((button) => button.addEventListener('click', () => {
      const label = button.textContent.trim().toLocaleLowerCase('ru');
      if (label === 'до') update(100);
      if (label === 'после') update(0);
    }));
    if ('ResizeObserver' in window) new ResizeObserver(() => update(range.value)).observe(frame);
    else window.addEventListener('resize', () => update(range.value), { passive: true });
    update(50);
  }

  function parseLocations() {
    const node = document.querySelector('#dz-location-data');
    if (!node) return [];
    try {
      const parsed = JSON.parse(node.textContent || '[]');
      return Array.isArray(parsed) ? parsed : Object.values(parsed || {});
    } catch (error) {
      console.warn('Не удалось прочитать данные клиник.', error);
      return [];
    }
  }

  function initClinicPicker(root, locations) {
    if (!locations.length || root.dataset.clinicReady === 'true') return;
    root.dataset.clinicReady = 'true';
    const tabs = Array.from(root.querySelectorAll('[data-clinic-tab]'));
    const select = root.querySelector('[data-clinic-select]');
    const map = root.querySelector('[data-clinic-map]');
    const title = root.querySelector('[data-clinic-title]');
    const address = root.querySelector('[data-clinic-address]');
    const description = root.querySelector('[data-clinic-description]');
    const directions = root.querySelector('[data-clinic-directions]');

    const setLocation = (rawIndex) => {
      const index = Math.min(Math.max(Number.parseInt(rawIndex, 10) || 0, 0), locations.length - 1);
      const location = locations[index] || {};
      const query = String(location.query || location.address || location.name || '').trim();
      if (title) title.textContent = location.title || location.name || location.area || '';
      if (address) address.textContent = location.address || '';
      if (description) description.textContent = location.description || location.directions || '';
      if (map && query) {
        map.src = `https://yandex.ru/map-widget/v1/?text=${encodeURIComponent(query)}&z=16`;
        map.title = `Карта: ${location.address || location.name || query}`;
      }
      if (directions && query) {
        directions.href = `https://yandex.ru/maps/?text=${encodeURIComponent(query)}`;
        directions.target = '_blank';
        directions.rel = 'noopener noreferrer';
      }
      tabs.forEach((tab) => {
        const active = Number.parseInt(tab.dataset.clinicTab, 10) === index;
        tab.classList.toggle('is-active', active);
        tab.setAttribute('aria-selected', String(active));
        tab.tabIndex = active ? 0 : -1;
      });
      if (select) {
        if (select.matches('select')) select.value = String(index);
        else select.dataset.value = String(index);
      }
    };

    tabs.forEach((tab) => tab.addEventListener('click', () => setLocation(tab.dataset.clinicTab)));
    tabs.forEach((tab,index)=>tab.addEventListener('keydown',event=>{
      if(!['ArrowDown','ArrowUp','ArrowLeft','ArrowRight','Home','End','Enter',' '].includes(event.key))return;
      event.preventDefault();let next=index;
      if(event.key==='Home')next=0;else if(event.key==='End')next=tabs.length-1;
      else if(['ArrowDown','ArrowRight'].includes(event.key))next=(index+1)%tabs.length;
      else if(['ArrowUp','ArrowLeft'].includes(event.key))next=(index-1+tabs.length)%tabs.length;
      setLocation(tabs[next].dataset.clinicTab);tabs[next].focus({preventScroll:true});
    }));
    select?.addEventListener('change', (event) => setLocation(event.target.value));
    select?.addEventListener('dz:filter', (event) => setLocation(event.detail?.value));
    root.addEventListener('dz:filter', (event) => {
      if (event.target.closest?.('[data-clinic-select]')) setLocation(event.detail?.value);
    });
    const initial = select?.matches('select') ? select.value : select?.dataset.value;
    setLocation(initial || tabs.find((tab) => tab.getAttribute('aria-selected') === 'true')?.dataset.clinicTab || 0);
  }

  function fieldValue(form, name) {
    return String(form.elements.namedItem(name)?.value || '').trim();
  }

  function initContactForm(form) {
    if(form.dataset.formReady==='true'||!window.DZFormGuard)return;
    form.dataset.formReady='true';
    const submit=form.querySelector('button[type="submit"]'),status=form.querySelector('[data-form-status]');
    let nextAllowed=0;
    if(submit)submit.disabled=false;
    form.addEventListener('submit',event=>{
      event.preventDefault();
      if(!form.reportValidity())return;
      if(Date.now()<nextAllowed){if(status)status.textContent='Письмо уже подготовлено. Повторить можно через несколько секунд.';return;}
      const result=window.DZFormGuard.validate({name:fieldValue(form,'name'),phone:fieldValue(form,'phone'),email:fieldValue(form,'email'),message:fieldValue(form,'message')||fieldValue(form,'comment'),website:fieldValue(form,'website'),consent:form.elements.namedItem('consent')?.checked===true});
      if(!result.ok){if(status){status.textContent=result.error;status.setAttribute('role','alert');}form.elements.namedItem(result.field)?.focus({preventScroll:true});return;}
      nextAllowed=Date.now()+30000;
      if(status){status.textContent='Заявка не отправлена автоматически. Открываем почтовую программу с подготовленным письмом — проверьте его и нажмите «Отправить».';status.setAttribute('role','status');}
      window.location.href=window.DZFormGuard.mailto(result);
    });
  }
  function navigate(target) {
    if (!target) return;
    window.location.assign(target);
  }

  document.addEventListener('click', (event) => {
    const reviewButton = event.target.closest('[data-read-review]');
    if (reviewButton) {
      const card = reviewButton.closest('[data-review-card], [data-review], article, li');
      const detail=document.querySelector('#patient-review-detail');
      if(detail&&card){
        detail.querySelector('[data-review-detail-body]').textContent=card.querySelector('.dz-source-review-text')?.textContent||'';
        detail.querySelector('[data-review-detail-author]').textContent=card.querySelector('.dz-source-review-author')?.textContent||'Отзыв пациента';
        document.dispatchEvent(new Event('dz:content-updated'));
        window.dzDialogs.open(detail,reviewButton);return;
      }
      const expanded = reviewButton.getAttribute('aria-expanded') === 'true';
      reviewButton.setAttribute('aria-expanded', String(!expanded));
      card?.classList.toggle('is-review-expanded', !expanded);
      const expandedLabel = reviewButton.dataset.expandedLabel || 'Скрыть';
      const collapsedLabel = reviewButton.dataset.collapsedLabel || 'Читать полностью';
      reviewButton.textContent = expanded ? collapsedLabel : expandedLabel;
      return;
    }

    const gift = event.target.closest('[data-gift]');
    if (gift) {
      event.preventDefault();
      navigate('/gift-certificates');
      return;
    }

    const appointment = event.target.closest('[data-appointment]');
    if (appointment) {
      const url = appointment.dataset.appointment || appointment.getAttribute('href');
      if (url && url !== '#') {
        event.preventDefault();
        navigate(url);
      }
      return;
    }

    const linked = event.target.closest('[data-href]');
    if (!linked || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (event.target.closest('a, button, input, select, textarea') && event.target !== linked) return;
    event.preventDefault();
    navigate(linked.dataset.href);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    const linked = event.target.closest('[data-href]');
    if (!linked || event.target.closest('a, button, input, select, textarea')) return;
    event.preventDefault();
    navigate(linked.dataset.href);
  });

  function init() {
    document.querySelectorAll('main button').forEach(button=>{
      if(button.textContent.trim()!=='Подробнее о кейсе' || !button.nextElementSibling)return;
      const panel=button.nextElementSibling;panel.hidden=true;button.setAttribute('aria-expanded','false');
      button.addEventListener('click',()=>{const opened=button.getAttribute('aria-expanded')!=='true';button.setAttribute('aria-expanded',String(opened));panel.hidden=!opened;panel.style.maxHeight=opened?'none':'0';panel.style.opacity=opened?'1':'0';panel.classList.toggle('dz-disclosure-open',opened);});
    });
    const comparisonFrames = new Set(document.querySelectorAll('main [class*="cursor-ew-resize"]'));
    document.querySelectorAll('[data-case-demo]').forEach((demo) => {
      const frame = demo.matches('[class*="cursor-ew-resize"]') ? demo : demo.querySelector('[class*="cursor-ew-resize"]');
      if (frame) comparisonFrames.add(frame);
    });
    comparisonFrames.forEach(initComparison);
    document.querySelectorAll('[data-team-carousel]').forEach(initTeamCarousel);
    document.querySelectorAll('[data-gallery]').forEach(initGallery);
    const locations = parseLocations();
    document.querySelectorAll('[data-clinic-picker]').forEach((root) => initClinicPicker(root, locations));
    document.querySelectorAll('[data-contact-form]').forEach(initContactForm);
    document.querySelectorAll('form:not([data-contact-form]):not([role="search"])').forEach(form=>form.addEventListener('submit',event=>{event.preventDefault();window.location.assign('https://idotvip.ru/domzubov');}));
    document.querySelectorAll('[data-href]').forEach((element) => {
      if (!element.hasAttribute('tabindex')) element.tabIndex = 0;
      if (!element.hasAttribute('role')) element.setAttribute('role', 'link');
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
