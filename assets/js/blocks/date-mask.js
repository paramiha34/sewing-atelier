//Поле даты в формате дд.мм.гггг. Стандартное поле type="date" браузер показывает
//в формате системы (часто мм/дд/гггг), поэтому используем обычное поле с маской.
App.on(function (scope) {
  App.each(scope, '[data-date]', function (input) {
    input.addEventListener('input', function () {
      var d = input.value.replace(/\D/g, '').slice(0, 8);
      var out = d.slice(0, 2);
      if (d.length > 2) out += '.' + d.slice(2, 4);
      if (d.length > 4) out += '.' + d.slice(4, 8);
      input.value = out;
    });
  });
});
