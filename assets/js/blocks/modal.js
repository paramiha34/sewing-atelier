//Окно с подробностями о работе на странице «Примеры работ»
App.on(function (scope) {
  App.each(scope, '[data-modal]', function (dialog) {
    var img = dialog.querySelector('.modal__image');
    var title = dialog.querySelector('.modal__title');
    var text = dialog.querySelector('.modal__text');
    var kind = dialog.querySelector('.modal__type');
    var character = dialog.querySelector('.modal__character');
    var characterText = dialog.querySelector('.modal__character-text');
    var extra = dialog.querySelector('.modal__extra');

    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        img.src = btn.dataset.image;
        img.alt = btn.dataset.title;
        title.textContent = btn.dataset.title;
        text.textContent = btn.dataset.text;
        kind.textContent = btn.dataset.kind;
        //блок о персонаже есть только у косплея: описание и ссылка с Shikimori
        character.hidden = !btn.dataset.link && !btn.dataset.character;
        characterText.textContent = btn.dataset.character || '';
        characterText.hidden = !btn.dataset.character;
        extra.hidden = !btn.dataset.link;
        if (btn.dataset.link) extra.href = btn.dataset.link;
        dialog.showModal();
      });
    });

    dialog.querySelector('[data-modal-close]').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (e) {
      if (e.target === dialog) dialog.close();
    });
  });
});
