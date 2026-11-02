//Подтверждение перед удалением записи в админ-панели
App.on(function (scope) {
  App.each(scope, 'form[data-confirm]', function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.dataset.confirm)) e.preventDefault();
    });
  });
});
