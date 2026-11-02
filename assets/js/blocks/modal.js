//Окно с подробностями о работе на странице «Примеры работ»
App.on(function (scope) {
  App.each(scope, '[data-modal]', function (dialog) {
    var img = dialog.querySelector('.modal__image');
    var title = dialog.querySelector('.modal__title');
    var text = dialog.querySelector('.modal__text');
    var kind = dialog.querySelector('.modal__type');
    var extra = dialog.querySelector('.modal__extra');

    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        img.src = btn.dataset.image;
        img.alt = btn.dataset.title;
        title.textContent = btn.dataset.title;
        text.textContent = btn.dataset.text;
        kind.textContent = btn.dataset.kind;
        //ссылка на персонажа есть только у косплея
        if (btn.dataset.link) {
          extra.hidden = false;
          extra.href = btn.dataset.link;
        } else {
          extra.hidden = true;
        }
        dialog.showModal();
      });
    });

    dialog.querySelector('[data-modal-close]').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (e) {
      if (e.target === dialog) dialog.close();
    });
  });
});
