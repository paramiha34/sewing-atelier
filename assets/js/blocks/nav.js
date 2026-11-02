//Кнопка меню на узких экранах
App.on(function (scope) {
  App.each(scope, '[data-nav-toggle]', function (btn) {
    var nav = document.getElementById(btn.getAttribute('aria-controls'));
    btn.addEventListener('click', function () {
      var open = nav.classList.toggle('header__nav--open');
      btn.setAttribute('aria-expanded', String(open));
    });
  });
});
