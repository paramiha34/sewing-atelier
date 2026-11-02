//Маска телефона +7 (___) ___-__-__ для полей с атрибутом data-phone-mask
(function () {
  function format(digits) {
    //первую 7 или 8 считаем кодом страны и отбрасываем
    if (digits.charAt(0) === '8' || digits.charAt(0) === '7') digits = digits.slice(1);
    digits = digits.slice(0, 10);
    var out = '+7';
    if (digits.length > 0) out += ' (' + digits.slice(0, 3);
    if (digits.length >= 3) out += ')';
    if (digits.length > 3) out += ' ' + digits.slice(3, 6);
    if (digits.length > 6) out += '-' + digits.slice(6, 8);
    if (digits.length > 8) out += '-' + digits.slice(8, 10);
    return out;
  }

  App.on(function (scope) {
    App.each(scope, '[data-phone-mask]', function (input) {
      input.addEventListener('focus', function () {
        if (!input.value) input.value = '+7 (';
      });
      input.addEventListener('input', function () {
        var digits = input.value.replace(/\D/g, '');
        input.value = digits.length ? format(digits) : '';
      });
      input.addEventListener('blur', function () {
        if (input.value === '+7 (' || input.value === '+7') input.value = '';
      });
    });
  });
})();
