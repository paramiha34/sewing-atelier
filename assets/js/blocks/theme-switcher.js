//Переключатель темы: четыре набора цветов, выбор хранится в localStorage
(function () {
  var KEY = 'atelier-theme';
  var root = document.documentElement;

  function closeAll() {
    document.querySelectorAll('.theme-switcher__menu').forEach(function (menu) {
      menu.hidden = true;
      var btn = menu.parentNode.querySelector('[data-theme-toggle]');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  function markActive(box) {
    var theme = root.getAttribute('data-theme') || 'paper';
    box.querySelectorAll('[data-theme-value]').forEach(function (o) {
      o.setAttribute('aria-checked', o.dataset.themeValue === theme ? 'true' : 'false');
    });
  }

  App.on(function (scope) {
    App.each(scope, '[data-theme-switcher]', function (box) {
      var toggle = box.querySelector('[data-theme-toggle]');
      var menu = box.querySelector('.theme-switcher__menu');
      markActive(box);

      toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        var willOpen = menu.hidden;
        closeAll();
        menu.hidden = !willOpen;
        toggle.setAttribute('aria-expanded', String(willOpen));
      });

      box.querySelectorAll('[data-theme-value]').forEach(function (option) {
        option.addEventListener('click', function () {
          root.setAttribute('data-theme', option.dataset.themeValue);
          try { localStorage.setItem(KEY, option.dataset.themeValue); } catch (e) {}
          markActive(box);
          closeAll();
          toggle.focus();
        });
      });
    });
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-theme-switcher]')) closeAll();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAll();
  });
})();
