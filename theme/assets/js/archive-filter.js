/**
 * 分類タブの絞り込み
 *
 * タブは通常のリンクとして書かれているので、この JS が読み込まれ
 * なくてもカテゴリーアーカイブへ遷移して機能する。ここでは
 * ページ遷移を AJAX に置き換えて体験を良くしているだけ。
 */
( function () {
	'use strict';

	var config = window.lomthemeArchive;
	if ( ! config ) {
		return;
	}

	var root = document.querySelector( '[data-lom-archive]' );
	var tabs = document.querySelectorAll( '.lom-filter__tab' );
	if ( ! root || ! tabs.length ) {
		return;
	}

	var grid = root.querySelector( '[data-lom-grid]' );
	var status = root.querySelector( '[data-lom-status]' );
	var pagination = root.querySelector( '.lom-pagination' );
	var inFlight = null;

	function setStatus( message ) {
		if ( ! status ) {
			return;
		}
		status.textContent = message || '';
		status.hidden = ! message;
	}

	function setActive( tab ) {
		Array.prototype.forEach.call( tabs, function ( item ) {
			var active = item === tab;
			item.classList.toggle( 'is-active', active );
			if ( active ) {
				item.setAttribute( 'aria-current', 'page' );
			} else {
				item.removeAttribute( 'aria-current' );
			}
		} );
	}

	function load( tab ) {
		var term = tab.getAttribute( 'data-term' ) || '';

		if ( inFlight ) {
			inFlight.abort();
		}
		inFlight = new AbortController();

		root.classList.add( 'is-loading' );
		setStatus( config.loading );

		var body = new URLSearchParams();
		body.set( 'action', config.action );
		body.set( 'nonce', config.nonce );
		body.set( 'term', term );
		body.set( 'paged', '1' );

		fetch( config.endpoint, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
			signal: inFlight.signal
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'HTTP ' + response.status );
				}
				return response.json();
			} )
			.then( function ( payload ) {
				if ( ! payload || ! payload.success ) {
					throw new Error( 'unexpected payload' );
				}

				grid.innerHTML = payload.data.html ||
					'<p class="lom-empty">' + config.empty + '</p>';

				// 絞り込み結果は1ページ目のみを出すので、
				// 元のページ送りは意味を失う。消しておく。
				if ( pagination ) {
					pagination.hidden = true;
				}

				setActive( tab );
				setStatus( '' );

				// URL を更新しておくと、再読み込みや共有で同じ状態に戻れる。
				if ( window.history && window.history.replaceState ) {
					window.history.replaceState( null, '', tab.href );
				}
			} )
			.catch( function ( error ) {
				if ( 'AbortError' === error.name ) {
					return;
				}
				setStatus( config.error );
			} )
			.finally( function () {
				root.classList.remove( 'is-loading' );
				inFlight = null;
			} );
	}

	Array.prototype.forEach.call( tabs, function ( tab ) {
		tab.addEventListener( 'click', function ( event ) {
			// 修飾キー付きクリックや中クリックは通常の遷移に任せる。
			if ( event.metaKey || event.ctrlKey || event.shiftKey || 1 !== event.which ) {
				return;
			}
			event.preventDefault();
			load( tab );
		} );
	} );
}() );
