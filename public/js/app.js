'use strict';

(() => {
  const SLOW_AFTER_MS = 5000;

  function openDialog(dialog) {
    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
      return true;
    }
    return false;
  }

  function setupDropdowns() {
    const triggers = document.querySelectorAll('[data-dropdown]');
    const closeAll = (except) => {
      triggers.forEach((btn) => {
        if (btn === except) return;
        btn.setAttribute('aria-expanded', 'false');
        btn.parentElement.classList.remove('is-open');
      });
    };

    triggers.forEach((btn) => {
      btn.addEventListener('click', (event) => {
        event.stopPropagation();
        const open = btn.getAttribute('aria-expanded') !== 'true';
        closeAll(btn);
        btn.setAttribute('aria-expanded', String(open));
        btn.parentElement.classList.toggle('is-open', open);
      });
    });
    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeAll();
    });
  }

  function setupEditDialog() {
    const dialog = document.getElementById('edit-dialog');
    const form = document.getElementById('edit-form');
    if (!dialog || !form) return;

    document.querySelectorAll('[data-edit]').forEach((link) => {
      link.addEventListener('click', (event) => {
        let asset;
        try {
          asset = JSON.parse(link.dataset.edit);
        } catch {
          return;
        }
        form.action = `/assets/${encodeURIComponent(asset.id)}`;
        ['hostname', 'ip_address', 'location', 'environment', 'status', 'notes'].forEach((name) => {
          const field = form.elements.namedItem(name);
          if (field) field.value = asset[name] ?? '';
        });
        resetBusy(form);
        if (openDialog(dialog)) {
          event.preventDefault();
          form.elements.namedItem('hostname').focus();
        }
      });
    });
  }

  function setupDeleteDialog() {
    const dialog = document.getElementById('delete-dialog');
    const form = document.getElementById('delete-form');
    if (!dialog || !form) return;

    document.querySelectorAll('[data-delete]').forEach((link) => {
      link.addEventListener('click', (event) => {
        form.action = `/assets/${encodeURIComponent(link.dataset.delete)}`;
        document.getElementById('delete-hostname').textContent = link.dataset.hostname || '';
        resetBusy(form);
        if (openDialog(dialog)) event.preventDefault();
      });
    });
  }

  function setupDialogDismissal() {
    document.querySelectorAll('dialog').forEach((dialog) => {
      dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
      });
      dialog.querySelectorAll('[data-close]').forEach((btn) => {
        btn.addEventListener('click', () => dialog.close());
      });
    });
  }

  function resetBusy(form) {
    form.removeAttribute('aria-busy');
    clearTimeout(form._slowTimer);
    form.querySelector('.slow-hint')?.remove();
    const button = form.querySelector('button[type="submit"]');
    if (button && button.dataset.label) {
      button.textContent = button.dataset.label;
      button.disabled = false;
    }
  }

  function setupBusyForms() {
    const banner = document.getElementById('offline-banner');

    document.querySelectorAll('form[data-busy-label]').forEach((form) => {
      form.addEventListener('submit', (event) => {
        if (!navigator.onLine) {
          event.preventDefault();
          if (banner) banner.hidden = false;
          return;
        }
        if (form.getAttribute('aria-busy') === 'true') {
          event.preventDefault();
          return;
        }
        const button = form.querySelector('button[type="submit"]');
        form.setAttribute('aria-busy', 'true');
        if (button) {
          button.dataset.label = button.textContent;
          button.textContent = form.dataset.busyLabel;
          setTimeout(() => { button.disabled = true; }, 0);
        }
        form._slowTimer = setTimeout(() => {
          const hint = document.createElement('p');
          hint.className = 'r-hint slow-hint';
          hint.setAttribute('role', 'status');
          hint.textContent = 'Still working. Your connection seems slow, so please keep this page open.';
          (button?.parentElement || form).appendChild(hint);
        }, SLOW_AFTER_MS);
      });
    });

    window.addEventListener('pageshow', () => {
      document.querySelectorAll('form[aria-busy="true"]').forEach(resetBusy);
    });

    if (banner) {
      const sync = () => { banner.hidden = navigator.onLine; };
      window.addEventListener('online', sync);
      window.addEventListener('offline', sync);
      sync();
    }
  }

  function setupAutoSubmitFilters() {
    document.querySelectorAll('.filter-bar select').forEach((select) => {
      select.addEventListener('change', () => select.form.requestSubmit());
    });
  }

  setupDropdowns();
  setupEditDialog();
  setupDeleteDialog();
  setupDialogDismissal();
  setupBusyForms();
  setupAutoSubmitFilters();
})();
