//Поиск персонажа для костюма через API Shikimori.
//Браузер обращается к нашему api/characters.php, а тот уже запрашивает shikimori.io:
//так не нужны разрешения CORS, а ответы кешируются на сервере.
(function () {
  //Shikimori разрешает не больше 5 запросов в секунду
  var DELAY = 350;

  function setStatus(box, text) {
    box.querySelector('[data-character-status]').textContent = text;
  }

  function card(ch, orderUrl) {
    var li = document.createElement('li');
    li.className = 'character-card';

    var img = document.createElement('img');
    img.className = 'character-card__image';
    img.src = ch.image;
    img.alt = ch.russian || ch.name;
    img.loading = 'lazy';
    img.referrerPolicy = 'no-referrer';

    var body = document.createElement('div');
    body.className = 'character-card__body';

    var name = document.createElement('p');
    name.className = 'character-card__name';
    name.textContent = ch.russian || ch.name;

    var original = document.createElement('p');
    original.className = 'character-card__original';
    original.textContent = ch.name;

    var source = document.createElement('p');
    source.className = 'character-card__source';
    source.textContent = 'Ищем произведение…';

    var more = document.createElement('a');
    more.className = 'character-card__link';
    more.href = ch.url;
    more.target = '_blank';
    more.rel = 'noopener';
    more.textContent = 'Страница на Shikimori';

    var order = document.createElement('a');
    order.className = 'button button--small character-card__order';
    var title = ch.russian && ch.russian !== ch.name ? ch.russian + ' (' + ch.name + ')' : ch.name;
    order.href = orderUrl + '&character=' + encodeURIComponent(title) + '&ref=' + encodeURIComponent(ch.url) + '#new-order';
    order.textContent = 'Заказать костюм';

    body.append(name, original, source, more, order);
    li.append(img, body);
    return { el: li, source: source };
  }

  function loadSource(id, el, delay) {
    setTimeout(function () {
      fetch('api/characters.php?id=' + id)
        .then(function (r) { return r.json(); })
        .then(function (json) {
          el.textContent = json.source || 'Произведение не указано';
        })
        .catch(function () { el.textContent = ''; });
    }, delay);
  }

  App.on(function (scope) {
    App.each(scope, '[data-character-search]', function (form) {
      var box = form.closest('.character-search');
      var input = form.querySelector('input[name="q"]');
      var list = box.querySelector('[data-character-results]');
      var orderUrl = form.dataset.orderUrl;

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var q = input.value.trim();
        if (q.length < 3) {
          setStatus(box, 'Введите не меньше трёх букв имени персонажа.');
          input.focus();
          return;
        }
        list.innerHTML = '';
        setStatus(box, 'Ищем «' + q + '»…');

        fetch('api/characters.php?q=' + encodeURIComponent(q))
          .then(function (r) {
            return r.json().then(function (json) { return { ok: r.ok, json: json }; });
          })
          .then(function (res) {
            if (!res.ok) throw new Error(res.json.error || 'Сервис поиска сейчас недоступен.');
            var items = res.json.items || [];
            if (!items.length) {
              setStatus(box, 'По запросу «' + q + '» никого не нашли. Попробуйте другое написание имени, например латиницей.');
              return;
            }
            setStatus(box, 'Найдено персонажей: ' + items.length + '. Выберите нужного и нажмите «Заказать костюм».');
            items.forEach(function (ch, i) {
              var c = card(ch, orderUrl);
              list.appendChild(c.el);
              loadSource(ch.id, c.source, DELAY * (i + 1));
            });
          })
          .catch(function (err) {
            setStatus(box, err.message && err.message.indexOf('JSON') === -1
              ? err.message
              : 'Не удалось связаться с сервисом поиска. Проверьте подключение к интернету и попробуйте ещё раз.');
          });
      });
    });
  });
})();
