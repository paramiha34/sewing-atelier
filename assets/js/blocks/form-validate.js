//Проверка форм в браузере. Правила задаются атрибутами полей:
//required, type="email", data-pattern (+ data-pattern-message), data-match, data-min/data-max.
//Сервер всё равно проверяет данные заново, здесь только подсказки пользователю.
(function () {
  var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  //Проверка даты дд.мм.гггг: дата существует и, если задано, не раньше чем через minDays дней
  function checkDate(value, minDays) {
    var m = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(value);
    if (!m) return 'Введите дату в формате дд.мм.гггг.';
    var date = new Date(+m[3], +m[2] - 1, +m[1]);
    if (date.getDate() !== +m[1] || date.getMonth() !== +m[2] - 1) return 'Такой даты нет.';
    if (minDays) {
      var min = new Date();
      min.setHours(0, 0, 0, 0);
      min.setDate(min.getDate() + Number(minDays));
      if (date < min) return 'Не раньше ' + min.toLocaleDateString('ru-RU') + '.';
    }
    return '';
  }

  function check(field, form) {
    var value = field.type === 'checkbox' ? field.checked : field.value.trim();
    var msg = '';

    if (field.required && !value) {
      msg = field.type === 'checkbox' ? 'Подтвердите согласие.' : 'Заполните это поле.';
    } else if (value && field.type === 'email' && !EMAIL.test(value)) {
      msg = 'Введите адрес в формате name@mail.ru.';
    } else if (value && field.dataset.pattern && !new RegExp(field.dataset.pattern).test(value)) {
      msg = field.dataset.patternMessage || 'Проверьте формат значения.';
    } else if (value && field.dataset.date !== undefined) {
      msg = checkDate(value, field.dataset.minDays);
    } else if (value && field.dataset.match) {
      var other = form.querySelector('[name="' + field.dataset.match + '"]');
      if (other && other.value !== field.value) msg = 'Пароли не совпадают.';
    } else if (value && (field.dataset.min || field.dataset.max)) {
      var n = Number(String(value).replace(',', '.'));
      if (isNaN(n) || n < Number(field.dataset.min) || n > Number(field.dataset.max)) {
        msg = 'Допустимо от ' + field.dataset.min + ' до ' + field.dataset.max + '.';
      }
    }

    var box = field.closest('.form__field');
    if (box) {
      box.classList.toggle('form__field--invalid', !!msg);
      var err = box.querySelector('.form__error');
      if (err) err.textContent = msg;
    }
    field.setAttribute('aria-invalid', msg ? 'true' : 'false');
    return !msg;
  }

  function skip(f) {
    return f.type === 'hidden' || f.type === 'submit' || f.type === 'radio';
  }

  App.on(function (scope) {
    App.each(scope, 'form[data-validate]', function (form) {
      form.setAttribute('novalidate', '');
      var fields = form.querySelectorAll('input, select, textarea');

      fields.forEach(function (f) {
        if (skip(f)) return;
        f.addEventListener('blur', function () { check(f, form); });
        f.addEventListener('input', function () {
          if (f.closest('.form__field--invalid')) check(f, form);
        });
      });

      form.addEventListener('submit', function (e) {
        var first = null;
        fields.forEach(function (f) {
          if (!skip(f) && !check(f, form) && !first) first = f;
        });
        if (first) {
          e.preventDefault();
          first.focus();
        }
      });
    });

    //в имени и фамилии оставляем только буквы, пробел и дефис
    App.each(scope, '[data-letters-only]', function (f) {
      f.addEventListener('input', function () {
        f.value = f.value.replace(/[^A-Za-zА-Яа-яЁё\- ]/g, '');
      });
    });

    App.each(scope, '[data-number-only]', function (f) {
      f.addEventListener('input', function () {
        f.value = f.value.replace(/[^0-9.,]/g, '');
      });
    });

    App.each(scope, '[data-counter]', function (f) {
      var out = document.getElementById(f.dataset.counter);
      var max = f.getAttribute('maxlength');
      function update() { out.textContent = f.value.length + ' / ' + max; }
      f.addEventListener('input', update);
      update();
    });
  });
})();
