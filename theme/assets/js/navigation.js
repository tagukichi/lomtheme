/**
 * ドロワーメニューの開閉
 *
 * hidden 属性で開閉する。CSS だけで隠すとキーボード操作で
 * 見えないリンクにフォーカスが入ってしまうため。
 */
( function () {
	'use strict';

	var toggle = document.querySelector( '.lom-header__toggle' );
	var drawer = document.getElementById( 'lom-drawer' );

	if ( ! toggle || ! drawer ) {
		return;
	}

	function setOpen( open ) {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		drawer.hidden = ! open;
	}

	toggle.addEventListener( 'click', function () {
		setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
	} );

	// Esc で閉じ、フォーカスをトグルへ戻す。
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}
		if ( toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
			setOpen( false );
			toggle.focus();
		}
	} );
}() );
