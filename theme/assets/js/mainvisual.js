/**
 * メインビジュアル・スライダー
 *
 * 依存ライブラリなし。スライドが1枚のときは何もしない。
 * ユーザーが動きを減らす設定にしている場合は自動送りしない。
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-lom-mv]' );
	if ( ! root ) {
		return;
	}

	var slides = root.querySelectorAll( '.lom-mv__slide' );
	if ( slides.length < 2 ) {
		return;
	}

	var dots = root.querySelectorAll( '.lom-mv__dot' );
	var thumbs = root.querySelectorAll( '.lom-mv__thumb' );
	var current = 0;
	var timer = null;
	var INTERVAL = 6000;

	var reduceMotion = window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function apply( collection, index, attr ) {
		Array.prototype.forEach.call( collection, function ( node, i ) {
			var active = i === index;
			node.classList.toggle( 'is-active', active );
			if ( attr ) {
				node.setAttribute( attr, active ? 'true' : 'false' );
			}
		} );
	}

	function goto( index ) {
		current = ( index + slides.length ) % slides.length;

		Array.prototype.forEach.call( slides, function ( slide, i ) {
			var active = i === current;
			slide.classList.toggle( 'is-active', active );
			if ( active ) {
				slide.removeAttribute( 'aria-hidden' );
			} else {
				slide.setAttribute( 'aria-hidden', 'true' );
			}
		} );

		apply( dots, current, 'aria-selected' );
		apply( thumbs, current, null );
	}

	function start() {
		if ( reduceMotion || timer ) {
			return;
		}
		timer = window.setInterval( function () {
			goto( current + 1 );
		}, INTERVAL );
	}

	function stop() {
		if ( ! timer ) {
			return;
		}
		window.clearInterval( timer );
		timer = null;
	}

	root.querySelectorAll( '[data-lom-mv-goto]' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			goto( parseInt( button.getAttribute( 'data-lom-mv-goto' ), 10 ) || 0 );
			// 操作されたら自動送りは止める。勝手に動くと使いにくい。
			stop();
		} );
	} );

	root.addEventListener( 'mouseenter', stop );
	root.addEventListener( 'mouseleave', start );
	root.addEventListener( 'focusin', stop );

	// 別タブに移っている間は動かさない。
	document.addEventListener( 'visibilitychange', function () {
		if ( document.hidden ) {
			stop();
		} else {
			start();
		}
	} );

	start();
}() );
