//Линейки и перекрестие на главной странице.
//Сверху - сантиметровая лента, которая прилипает к шапке, слева - линейка на всю высоту страницы.
//От обеих линеек к курсору идут полупрозрачные линии, рядом с курсором видны координаты.
//Отключается кнопкой «Линейка» в шапке, выбор хранится в localStorage.
(function () {
  var KEY = 'atelier-ruler';
  //50 пикселей = 5 делений, одно деление = 10 пикселей
  var STEP = 50;
  var root = document.documentElement;
  var frame = 0;
  var lastEvent = null;

  function enabled() {
    return root.getAttribute('data-ruler') !== 'off';
  }

  function parts() {
    var box = document.querySelector('[data-ruler]');
    if (!box) return null;
    return {
      box: box,
      side: box.querySelector('.ruler__side'),
      lineX: box.querySelector('.ruler__line--x'),
      lineY: box.querySelector('.ruler__line--y'),
      labelX: box.querySelector('.ruler__label--x'),
      labelY: box.querySelector('.ruler__label--y'),
      tape: document.querySelector('.tape--sticky'),
      header: document.querySelector('.header')
    };
  }

  //Линейка растянута по высоте body через top/bottom, а подписи строим по его высоте.
  //Берём offsetHeight body, а не scrollHeight документа: иначе линейка учитывала бы саму себя и страница росла
  function buildSide(p) {
    var height = document.body.offsetHeight;
    if (p.side.dataset.height === String(height)) return;
    p.side.dataset.height = height;
    var numbers = p.side.querySelector('.ruler__numbers');
    numbers.innerHTML = '';
    for (var y = 0; y < height; y += STEP) {
      var span = document.createElement('span');
      span.className = 'ruler__number';
      span.style.top = y + 'px';
      span.textContent = y / 10;
      numbers.appendChild(span);
    }
  }

  function hide(p) {
    p.box.classList.remove('ruler--active');
  }

  function format(value) {
    return (Math.round(value * 10) / 10).toFixed(1).replace('.', ',');
  }

  function draw() {
    frame = 0;
    var p = parts();
    var e = lastEvent;
    if (!p || !e || !enabled()) return;

    //линии начинаются под шапкой, сама лента и шапка рисуются поверх линий
    var top = p.header ? p.header.getBoundingClientRect().bottom : 0;
    var left = p.side.getBoundingClientRect().right;
    var tape = p.tape ? p.tape.getBoundingClientRect() : null;
    var overTape = tape && e.clientY >= tape.top && e.clientY <= tape.bottom;
    if (e.clientY < top || e.clientX < left || overTape || window.innerWidth < 1300) {
      hide(p);
      return;
    }
    p.box.classList.add('ruler--active');

    p.lineX.style.transform = 'translateX(' + e.clientX + 'px)';
    p.lineX.style.top = top + 'px';
    p.lineY.style.transform = 'translateY(' + e.clientY + 'px)';
    p.lineY.style.left = left + 'px';

    p.labelX.textContent = format(e.clientX / 10);
    p.labelY.textContent = format((e.clientY + window.scrollY) / 10);
    p.labelX.style.transform = 'translate(' + e.clientX + 'px,' + (e.clientY - 46) + 'px) translateX(-50%)';
    p.labelY.style.transform = 'translate(' + (e.clientX - 24) + 'px,' + e.clientY + 'px) translate(-100%, -50%)';

    //над текстом, кнопками и картинками подписи скрываются, остаются только линии
    var busy = e.target.closest && e.target.closest('a, button, input, select, textarea, label, img, svg, p, h1, h2, h3, li, dd, dt, td, th, span, strong');
    p.box.classList.toggle('ruler--dim', !!busy);
  }

  function schedule(e) {
    lastEvent = e;
    if (!frame) frame = requestAnimationFrame(draw);
  }

  function sync() {
    var p = parts();
    document.querySelectorAll('[data-ruler-toggle]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', String(enabled()));
    });
    if (!p) return;
    if (enabled()) buildSide(p);
    else hide(p);
  }

  App.on(function (scope) {
    App.each(scope, '[data-ruler-toggle]', function (btn) {
      btn.addEventListener('click', function () {
        var next = enabled() ? 'off' : 'on';
        root.setAttribute('data-ruler', next);
        try { localStorage.setItem(KEY, next); } catch (e) {}
        sync();
      });
    });
    sync();
  });

  //обработчики общие для всего документа, вешаем один раз
  document.addEventListener('mousemove', schedule, { passive: true });
  //Курсор ушёл за пределы окна (например, на второй монитор): прячем линии и подписи так же,
  //как над самой линейкой, и забываем последнюю позицию, чтобы прокрутка не вернула их на старое место.
  //Снова появятся при первом движении мыши там, куда вернётся курсор.
  function leave() {
    lastEvent = null;
    if (frame) cancelAnimationFrame(frame);
    frame = 0;
    var p = parts();
    if (p) hide(p);
  }
  root.addEventListener('mouseleave', leave);
  document.addEventListener('mouseout', function (e) {
    if (!e.relatedTarget) leave();
  });
  window.addEventListener('blur', leave);
  window.addEventListener('scroll', function () {
    if (lastEvent) schedule(lastEvent);
  }, { passive: true });
  window.addEventListener('resize', sync);
  window.addEventListener('load', sync);
})();
