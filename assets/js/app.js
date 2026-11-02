//Реестр инициализаций для всех блоков.
//Блок регистрирует функцию через App.on, она вызывается при первой загрузке
//и повторно после перехода между страницами без перезагрузки (см. blocks/pjax.js).
window.App = (function () {
  var inits = [];
  var started = false;

  function on(fn) {
    inits.push(fn);
    //скрипт страницы мог подгрузиться уже после старта - запускаем его сразу
    if (started) fn(document);
  }

  function run(root) {
    inits.forEach(function (fn) { fn(root || document); });
  }

  //Вызывает fn один раз для каждого найденного элемента, повторно не привязывает
  function each(root, selector, fn) {
    root.querySelectorAll(selector).forEach(function (el) {
      if (el.dataset.ready) return;
      el.dataset.ready = '1';
      fn(el);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    started = true;
    run(document);
  });

  return { on: on, run: run, each: each };
})();
