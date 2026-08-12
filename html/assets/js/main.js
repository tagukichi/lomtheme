/**
 * ○○青年会議所 サイトスクリプト
 *
 * 依存ライブラリなし。それぞれの機能は対象の要素が無ければ何もしない。
 * JavaScript が動かない環境でも、内容の閲覧は成立するように作ってある。
 */
(function () {
  'use strict';

  /* -----------------------------------------------------------------------
     グローバルナビの開閉（スマートフォン）
     ----------------------------------------------------------------------- */
  (function navToggle() {
    var header = document.querySelector('.site-header');
    var button = document.querySelector('.nav-toggle');
    if (!header || !button) { return; }

    function setOpen(open) {
      header.classList.toggle('is-open', open);
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
      button.querySelector('.nav-toggle__label').textContent =
        open ? 'メニューを閉じる' : 'メニューを開く';
    }

    button.addEventListener('click', function () {
      setOpen(button.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
        setOpen(false);
        button.focus();
      }
    });

    // 画面が広がってナビが常時表示になったら、開いた状態を解除しておく。
    var wide = window.matchMedia('(min-width: 981px)');
    wide.addEventListener('change', function (event) {
      if (event.matches) { setOpen(false); }
    });
  }());


  /* -----------------------------------------------------------------------
     メインビジュアルのスライダー

     スライドが1枚のときは何もしない。ユーザーが「視差効果を減らす」設定に
     している場合は自動送りしない。
     ----------------------------------------------------------------------- */
  (function mainVisual() {
    var root = document.querySelector('[data-mv]');
    if (!root) { return; }

    var slides = root.querySelectorAll('.mv__slide');
    var dots = root.querySelectorAll('.mv__dot');
    if (slides.length < 2) { return; }

    var index = 0;
    var timer = null;
    var INTERVAL = 6000;
    var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function go(next) {
      index = (next + slides.length) % slides.length;

      slides.forEach(function (slide, i) {
        var on = i === index;
        slide.classList.toggle('is-active', on);
        slide.setAttribute('aria-hidden', on ? 'false' : 'true');
      });

      dots.forEach(function (dot, i) {
        var on = i === index;
        dot.classList.toggle('is-active', on);
        dot.setAttribute('aria-selected', on ? 'true' : 'false');
      });
    }

    function start() {
      if (still || timer) { return; }
      timer = window.setInterval(function () { go(index + 1); }, INTERVAL);
    }

    function stop() {
      if (!timer) { return; }
      window.clearInterval(timer);
      timer = null;
    }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        go(i);
        stop(); // 手動で選んだあとは勝手に動かさない
      });
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { stop(); } else { start(); }
    });

    start();
  }());


  /* -----------------------------------------------------------------------
     お知らせの分類絞り込み

     サーバーを使わず、既に表示されているカードの表示・非表示で切り替える。
     JavaScript が無ければ全件が並ぶだけで、内容は失われない。
     ----------------------------------------------------------------------- */
  (function newsFilter() {
    var filter = document.querySelector('[data-filter]');
    var grid = document.querySelector('[data-news-grid]');
    if (!filter || !grid) { return; }

    var buttons = filter.querySelectorAll('button');
    var cards = grid.querySelectorAll('[data-cat]');
    var empty = document.querySelector('[data-news-empty]');

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var want = button.dataset.target;
        var shown = 0;

        cards.forEach(function (card) {
          var hit = want === 'all' || card.dataset.cat === want;
          card.hidden = !hit;
          if (hit) { shown++; }
        });

        buttons.forEach(function (other) {
          other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
        });

        if (empty) { empty.hidden = shown > 0; }
      });
    });
  }());


  /* -----------------------------------------------------------------------
     フッターの西暦
     ----------------------------------------------------------------------- */
  (function copyrightYear() {
    var slot = document.querySelector('[data-year]');
    if (slot) { slot.textContent = String(new Date().getFullYear()); }
  }());
}());
