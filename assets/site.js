(() => {
  'use strict';
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const menu = $('#mobile-menu');
  const menuToggle = $('.dz-menu-toggle');
  const openers = new WeakMap();
  const skipRestore = new WeakSet();
  const closeTimers = new WeakMap();
  let pageScroll = 0;
  function closeDialog(dialog) {
    if(!dialog?.open || dialog.classList.contains('is-closing'))return;
    dialog.classList.remove('is-open');dialog.classList.add('is-closing');
    closeTimers.set(dialog,setTimeout(()=>{dialog.classList.remove('is-closing');dialog.close();closeTimers.delete(dialog);},matchMedia('(prefers-reduced-motion: reduce)').matches?0:220));
  }
  function openDialog(dialog, opener) {
    clearTimeout(closeTimers.get(dialog));closeTimers.delete(dialog);
    pageScroll = window.scrollY;
    $$('dialog[open]').filter(other=>other!==dialog).forEach(other => {skipRestore.add(other);other.classList.remove('is-open');other.close();});
    const source=opener || document.activeElement;
    openers.set(dialog,source?.closest('dialog:not([open])')?menuToggle:source);
    document.dispatchEvent(new Event('dz:dialog-open'));
    dialog.classList.remove('is-closing');
    dialog.showModal();
    requestAnimationFrame(()=>requestAnimationFrame(()=>dialog.classList.add('is-open')));
    if (dialog === menu) menuToggle.setAttribute('aria-expanded', 'true');
  }
  window.dzDialogs={open:openDialog,close:closeDialog};
  addEventListener('pagehide',()=>$$('dialog[open]').forEach(dialog=>{clearTimeout(closeTimers.get(dialog));skipRestore.add(dialog);dialog.classList.remove('is-open','is-closing');dialog.close();}));
  $$('dialog').forEach(dialog => {
    dialog.addEventListener('close', () => {
      if (dialog === menu) menuToggle.setAttribute('aria-expanded', 'false');
      if(skipRestore.has(dialog)){skipRestore.delete(dialog);return;}
      const opener = openers.get(dialog);
      if (opener?.isConnected) opener.focus({ preventScroll: true });
      window.scrollTo({top:pageScroll,behavior:'instant'});
    });
    dialog.addEventListener('cancel',event=>{event.preventDefault();closeDialog(dialog);});
    dialog.addEventListener('click', event => {
      if (event.target !== dialog) return;
      const rect = dialog.getBoundingClientRect();
      if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) closeDialog(dialog);
    });
  });
  menuToggle.addEventListener('click', () => openDialog(menu, menuToggle));
  document.addEventListener('click', event => { const button=event.target.closest('[data-close]'); if(button) closeDialog(button.closest('dialog')); });
})();