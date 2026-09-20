/* Shared continuous rail. Embla Carousel 8.6.0 is vendored locally under the MIT license. */
(() => {
  'use strict';
  const mounted = new WeakMap();
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');

  function mount({ root, track, kind, previous, next }) {
    if (!track || !window.EmblaCarousel || mounted.has(track)) return mounted.get(track);
    const slides = Array.from(track.children);
    if (!slides.length) return;
    const gap = parseFloat(getComputedStyle(track).columnGap) || 20;
    const viewport = document.createElement('div');
    viewport.className = `dz-carousel-viewport dz-carousel--${kind}`;
    viewport.tabIndex = 0;
    viewport.dataset.carouselMoving = 'false';
    if (kind === 'gallery') viewport.style.setProperty('--dz-carousel-gap', `${gap}px`);
    if (kind === 'review') viewport.style.setProperty('--dz-carousel-desktop-count', String(Number(root.dataset.reviewPageSize) || 4));
    track.before(viewport);
    viewport.append(track);
    track.classList.add('dz-carousel-track');
    slides.forEach(slide => {
      slide.hidden = false;
      slide.inert = false;
      slide.removeAttribute('aria-hidden');
      slide.classList.add('dz-carousel-slide');
      slide.querySelectorAll('img').forEach(image => { image.draggable = false; });
    });
    let pointerActive = false;
    const api = window.EmblaCarousel(viewport, {
      loop: true,
      align: 'start',
      slidesToScroll: 1,
      containScroll: 'trimSnaps',
      duration: 36,
      skipSnaps: false,
      watchFocus: false,
      breakpoints: { '(prefers-reduced-motion: reduce)': { duration: 0 } },
    });
    mounted.set(track, api);
    viewport.dzCarousel = api;

    function update() {
      const box = viewport.getBoundingClientRect();
      const visible = slides.map(slide => {
        const rect = slide.getBoundingClientRect();
        return rect.right > box.left + .5 && rect.left < box.right - .5;
      });
      slides.forEach((slide, index) => {
        const value = String(visible[index]);
        if (slide.dataset.carouselVisible === value) return;
        if (!visible[index] && slide.contains(document.activeElement)) viewport.focus({ preventScroll: true });
        slide.dataset.carouselVisible = value;
        slide.inert = !visible[index];
        slide.setAttribute('aria-hidden', String(!visible[index]));
      });
      viewport.dataset.carouselIndex = String(api.selectedScrollSnap());
      if (previous) previous.disabled = !api.canScrollPrev();
      if (next) next.disabled = !api.canScrollNext();
    }
    function move(direction) {
      if (direction < 0 ? !api.canScrollPrev() : !api.canScrollNext()) return;
      viewport.dataset.carouselMoving = 'true';
      if (direction < 0) api.scrollPrev(reducedMotion.matches);
      else api.scrollNext(reducedMotion.matches);
      if (reducedMotion.matches) { update();viewport.dataset.carouselMoving = 'false'; }
    }
    previous?.addEventListener('click', () => move(-1));
    next?.addEventListener('click', () => move(1));
    api.on('scroll', () => { viewport.dataset.carouselMoving = 'true';update(); });
    api.on('select', update);
    api.on('settle', () => { viewport.dataset.carouselMoving = 'false';update(); });
    api.on('reInit', () => { viewport.dataset.carouselMoving = 'false';update(); });
    api.on('pointerUp', () => {
      if (!reducedMotion.matches) return;
      api.scrollTo(api.selectedScrollSnap(), true);
      viewport.dataset.carouselMoving = 'false';
      update();
    });

    viewport.addEventListener('keydown', event => {
      if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
      event.preventDefault();move(event.key === 'ArrowRight' ? 1 : -1);
    });
    viewport.addEventListener('pointerdown', () => { pointerActive = true; }, { passive: true });
    const releasePointer = () => { setTimeout(() => { pointerActive = false; }, 0); };
    window.addEventListener('pointerup', releasePointer, { passive: true });
    window.addEventListener('pointercancel', releasePointer, { passive: true });
    viewport.addEventListener('focusin', event => {
      if (pointerActive || event.target === viewport) return;
      const index = slides.findIndex(slide => slide.contains(event.target));
      if (index < 0) return;
      viewport.scrollLeft = 0;
      const box = viewport.getBoundingClientRect(), rect = slides[index].getBoundingClientRect();
      if (rect.left >= box.left - 1 && rect.right <= box.right + 1) return;
      viewport.dataset.carouselMoving = 'true';
      api.scrollTo(index, reducedMotion.matches);
      if (reducedMotion.matches) { update();viewport.dataset.carouselMoving = 'false'; }
    });
    let lastWheel = 0;
    viewport.addEventListener('wheel', event => {
      const delta = event.deltaX || (event.shiftKey ? event.deltaY : 0);
      if (Math.abs(delta) < 8 || (!event.shiftKey && Math.abs(delta) <= Math.abs(event.deltaY))) return;
      if (!api.canScrollNext() && !api.canScrollPrev()) return;
      event.preventDefault();
      if (Date.now() - lastWheel < 280) return;
      lastWheel = Date.now();move(delta > 0 ? 1 : -1);
    }, { passive: false });
    viewport.addEventListener('dragstart', event => event.preventDefault());
    update();
    return api;
  }
  window.DZCarousel = { mount };
})();
