//Переходы между страницами без перезагрузки.
//По клику на внутреннюю ссылку страница загружается через fetch, затем заменяются
//шапка и основное содержимое, подключаются недостающие стили и скрипты,
//а адрес меняется через history.pushState. Формы отправляются как обычно.
(function () {
  if (!window.fetch || !window.DOMParser || !history.pushState) return;
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

  var current = null;

  function progress(on) {
    document.documentElement.classList.toggle('is-loading', on);
  }

  function shouldHandle(link, e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return false;
    if (link.target && link.target !== '_self') return false;
    if (link.hasAttribute('download') || link.hasAttribute('data-no-pjax')) return false;
    if (link.origin !== location.origin) return false;
    if (/\/handlers\/|\/api\/|logout\.php/.test(link.pathname)) return false;
    if (!/(\.php|\/)$/.test(link.pathname)) return false;
    //ссылка на якорь той же страницы - пусть браузер просто прокрутит
    if (link.pathname === location.pathname && link.search === location.search && link.hash) return false;
    return true;
  }

  function addStyles(doc) {
    var waits = [];
    doc.querySelectorAll('link[rel="stylesheet"]').forEach(function (l) {
      var href = l.getAttribute('href');
      if (document.querySelector('link[rel="stylesheet"][href="' + href + '"]')) return;
      var el = document.createElement('link');
      el.rel = 'stylesheet';
      el.href = href;
      waits.push(new Promise(function (done) { el.onload = el.onerror = done; }));
      document.head.appendChild(el);
    });
    return Promise.all(waits);
  }

  function addScripts(doc) {
    var waits = [];
    doc.querySelectorAll('script[src]').forEach(function (s) {
      var src = s.getAttribute('src');
      if (document.querySelector('script[src="' + src + '"]')) return;
      var el = document.createElement('script');
      el.src = src;
      el.async = false;
      waits.push(new Promise(function (done) { el.onload = el.onerror = done; }));
      document.head.appendChild(el);
    });
    return Promise.all(waits);
  }

  function swap(doc, url, push) {
    var newMain = doc.querySelector('.page__main');
    var newHeader = doc.querySelector('.header');
    document.title = doc.title;
    document.body.className = doc.body.className;
    document.querySelector('.header').replaceWith(newHeader);
    document.querySelector('.page__main').replaceWith(newMain);
    if (push) history.pushState({ pjax: true }, '', url);

    var hash = url.split('#')[1];
    var anchor = hash && document.getElementById(hash);
    if (anchor) anchor.scrollIntoView();
    else window.scrollTo(0, 0);

    newMain.classList.add('page__main--enter');
    newMain.focus({ preventScroll: true });
    document.dispatchEvent(new CustomEvent('pjax:done'));
  }

  function go(url, push) {
    if (current) current.abort();
    var ctrl = new AbortController();
    current = ctrl;
    progress(true);

    fetch(url, { credentials: 'same-origin', signal: ctrl.signal, headers: { 'X-Requested-With': 'pjax' } })
      .then(function (res) {
        var type = res.headers.get('Content-Type') || '';
        if (type.indexOf('text/html') === -1) throw new Error('not html');
        //после редиректа (например, на страницу входа) показываем конечный адрес
        if (res.redirected) url = res.url + (url.indexOf('#') > -1 ? '#' + url.split('#')[1] : '');
        return res.text();
      })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        if (!doc.querySelector('.page__main') || !doc.querySelector('.header')) throw new Error('no layout');
        return addStyles(doc).then(function () {
          swap(doc, url, push);
          return addScripts(doc);
        });
      })
      .then(function () {
        App.run(document);
        progress(false);
      })
      .catch(function (err) {
        if (err.name === 'AbortError') return;
        location.href = url;
      });
  }

  document.addEventListener('click', function (e) {
    var link = e.target.closest('a[href]');
    if (!link || !shouldHandle(link, e)) return;
    e.preventDefault();
    go(link.href, true);
  });

  window.addEventListener('popstate', function () {
    go(location.href, false);
  });
})();
