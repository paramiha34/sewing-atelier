//Конфетный дождь: нажатие на тыкву в теме «Хэллоуин» высыпает сверху 5-10 конфет и несколько звёздочек сахара.
//Между нажатиями небольшая пауза, а число конфет на экране ограничено, чтобы анимация не тормозила.
(function () {
  var COOLDOWN = 450;
  var MAX_ON_SCREEN = 70;
  var COLORS = ['#FF7A1A', '#FFC93C', '#9BD34B', '#B07CF2', '#FF6FA3', '#FF4F4F', '#7FD3F0'];
  var lastDrop = 0;
  var uid = 0;
  var layer = null;

  //Случайное число в диапазоне и случайный элемент массива
  function rand(min, max) { return min + Math.random() * (max - min); }
  function pick(list) { return list[Math.floor(Math.random() * list.length)]; }

  //Два разных цвета из палитры
  function pair() {
    var a = pick(COLORS), b = pick(COLORS);
    while (b === a) b = pick(COLORS);
    return [a, b];
  }

  //Конфета в фантике: два хвостика и полосатая или горошковая серединка
  function wrapped() {
    var c = pair();
    var id = 'candy-w' + (++uid);
    var dots = Math.random() < .5;
    var middle = dots
      ? '<circle cx="24" cy="12" r="2.2" fill="' + c[1] + '"/><circle cx="31" cy="18" r="2.2" fill="' + c[1] + '"/><circle cx="37" cy="11" r="2.2" fill="' + c[1] + '"/>'
      : '<path d="M22 5 L27 25 M30 4 L35 25 M38 6 L41 22" stroke="' + c[1] + '" stroke-width="3.2" stroke-linecap="round"/>';
    return '<svg viewBox="0 0 60 30"><path d="M16 15 L3 5 Q1 15 3 25 Z M44 15 L57 5 Q59 15 57 25 Z" fill="' + c[1] + '"/>'
      + '<rect x="15" y="4" width="30" height="22" rx="11" fill="' + c[0] + '"/><clipPath id="' + id + '"><rect x="15" y="4" width="30" height="22" rx="11"/></clipPath>'
      + '<g clip-path="url(#' + id + ')">' + middle + '</g></svg>';
  }

  //Леденец на палочке со спиралью из колец
  function lollipop() {
    var c = pair();
    return '<svg viewBox="0 0 40 64"><rect x="18" y="30" width="4" height="32" rx="2" fill="#F3E3C8"/>'
      + '<circle cx="20" cy="18" r="17" fill="' + c[0] + '"/><circle cx="20" cy="18" r="12.5" fill="none" stroke="' + c[1] + '" stroke-width="3.5"/>'
      + '<circle cx="20" cy="18" r="6" fill="none" stroke="' + c[1] + '" stroke-width="3.5"/><circle cx="20" cy="18" r="1.8" fill="' + c[1] + '"/></svg>';
  }

  //Кукурузная конфета: белый кончик, оранжевая середина, жёлтое основание
  function candyCorn() {
    var id = 'candy-c' + (++uid);
    return '<svg viewBox="0 0 40 44"><clipPath id="' + id + '"><path d="M20 2 C24 2 38 34 37 38 C36 43 4 43 3 38 C2 34 16 2 20 2 Z"/></clipPath>'
      + '<g clip-path="url(#' + id + ')"><rect width="40" height="44" fill="#FFC93C"/><rect width="40" height="31" fill="#FF7A1A"/><rect width="40" height="15" fill="#FFF6E6"/></g></svg>';
  }

  //Белая четырёхлучевая звёздочка — крупинка сахара
  function star() {
    return '<svg viewBox="0 0 20 20"><path d="M10 0 C11 7 13 9 20 10 C13 11 11 13 10 20 C9 13 7 11 0 10 C7 9 9 7 10 0 Z" fill="#FFFFFF"/></svg>';
  }

  //Слой создаётся один раз и остаётся при переходах без перезагрузки
  function getLayer() {
    if (!layer || !document.body.contains(layer)) {
      layer = document.createElement('div');
      layer.className = 'candy-rain';
      layer.setAttribute('aria-hidden', 'true');
      document.body.appendChild(layer);
    }
    return layer;
  }

  //Добавляет одну падающую фигуру и удаляет её после окончания анимации
  function drop(svg, size, isStar) {
    var item = document.createElement('div');
    item.className = 'candy-rain__item' + (isStar ? ' candy-rain__item--star' : '');
    item.style.setProperty('--x', rand(2, 96) + 'vw');
    item.style.setProperty('--size', size + 'px');
    item.style.setProperty('--time', rand(2.6, 4.2).toFixed(2) + 's');
    item.style.setProperty('--dx', rand(-60, 60).toFixed(0) + 'px');
    var r0 = rand(-40, 40);
    item.style.setProperty('--r0', r0.toFixed(0) + 'deg');
    item.style.setProperty('--r1', (r0 + rand(-260, 260)).toFixed(0) + 'deg');
    item.style.animationDelay = rand(0, .5).toFixed(2) + 's';
    item.innerHTML = svg;
    item.addEventListener('animationend', function (e) {
      if (e.target === item) item.remove();
    });
    getLayer().appendChild(item);
  }

  //Одна порция: 5-10 конфет случайных видов и размеров и 2-4 звёздочки
  function shower() {
    var box = getLayer();
    var free = MAX_ON_SCREEN - box.childElementCount;
    if (free <= 0) return;
    var count = Math.min(free, 5 + Math.floor(Math.random() * 6));
    var makers = [wrapped, wrapped, lollipop, candyCorn];
    for (var i = 0; i < count; i++) {
      var make = pick(makers);
      var base = make === lollipop ? 34 : make === candyCorn ? 30 : 46;
      drop(make(), Math.round(base * rand(.8, 1.2)), false);
    }
    var stars = 2 + Math.floor(Math.random() * 3);
    for (var j = 0; j < stars && box.childElementCount < MAX_ON_SCREEN; j++) {
      drop(star(), Math.round(rand(12, 20)), true);
    }
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-candy-rain]')) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var now = Date.now();
    if (now - lastDrop < COOLDOWN) return;
    lastDrop = now;
    shower();
  });
})();
