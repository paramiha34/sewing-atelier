//Фильтр по типу услуги. Без JavaScript кнопки работают как ссылки ?type=...,
//со скриптом карточки скрываются сразу, без запроса к серверу.
App.on(function (scope) {
  App.each(scope, '[data-filter]', function (bar) {
    var target = document.querySelector(bar.dataset.filter);
    if (!target) return;
    var buttons = bar.querySelectorAll('[data-filter-value]');

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var value = btn.dataset.filterValue;
        buttons.forEach(function (b) {
          b.classList.toggle('filter__button--active', b === btn);
          b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
        });
        target.querySelectorAll('[data-filter-item]').forEach(function (item) {
          item.hidden = !(value === 'all' || item.dataset.filterItem === value);
        });
        history.replaceState(history.state, '', value === 'all' ? location.pathname : '?type=' + value);
      });
    });
  });
});
