<?php
/**
 * プレビュー用の WordPress 関数スタブ
 *
 * テーマのPHPテンプレートを WordPress 無しで実行し、静的HTMLに
 * 書き出すための最小限の実装。テンプレート本体には一切手を入れない
 * ので、プレビューとテーマ出力が乖離しない。
 *
 * 本番の WordPress とは別物なので、挙動の完全な再現は目的にしていない。
 * 「レイアウトとマークアップをブラウザで確認する」ことだけが目的。
 *
 * @package LomTheme\Preview
 */

declare( strict_types = 1 );

define( 'ABSPATH', __DIR__ );

/* ===========================================================================
 * プレビュー状態
 * ======================================================================== */

$GLOBALS['pv'] = array(
	'conds'    => array(),
	'posts'    => array(),
	'index'    => -1,
	'post'     => null,
	'fields'   => array(),   // 現在の対象オブジェクトの ACF 値
	'options'  => array(),   // オプションページの ACF 値
	'terms'    => array(),
	'rowstack' => array(),
	'query'    => null,
	'search'   => '',
	'title'    => '',
	'menus'    => array(),
);

function pv_state(): array {
	return $GLOBALS['pv'];
}

function pv_set( string $key, $value ): void {
	$GLOBALS['pv'][ $key ] = $value;
}

/* ===========================================================================
 * クラス
 * ======================================================================== */

class WP_Term {
	public int $term_id;
	public string $name;
	public string $slug;
	public int $count;

	public function __construct( int $id, string $name, string $slug, int $count = 1 ) {
		$this->term_id = $id;
		$this->name    = $name;
		$this->slug    = $slug;
		$this->count   = $count;
	}
}

class WP_Error {
	public function __construct( public string $code = '', public string $message = '' ) {}
}

class WP_Query {
	public array $posts = array();
	public int $current  = -1;
	public int $max_num_pages = 1;
	public int $found_posts   = 0;

	public function __construct( array|null $args = null ) {
		if ( is_array( $args ) ) {
			$limit       = (int) ( $args['posts_per_page'] ?? 6 );
			$this->posts = array_slice( $GLOBALS['pv']['all_posts'] ?? array(), 0, $limit );
		}
		$this->found_posts   = count( $this->posts );
		$this->max_num_pages = 1;
	}

	public function have_posts(): bool {
		return $this->current + 1 < count( $this->posts );
	}

	public function the_post(): void {
		$this->current++;
		$GLOBALS['pv']['post'] = $this->posts[ $this->current ];
	}
}

/* ===========================================================================
 * 条件分岐タグ
 * ======================================================================== */

function pv_is( string $name ): bool {
	return ! empty( $GLOBALS['pv']['conds'][ $name ] );
}

function is_front_page(): bool { return pv_is( 'front_page' ); }
function is_home(): bool       { return pv_is( 'home' ); }
function is_singular( $t = '' ): bool {
	if ( '' === $t ) { return pv_is( 'singular' ); }
	return pv_is( 'singular' ) && ( $GLOBALS['pv']['post']['type'] ?? '' ) === $t;
}
function is_page(): bool     { return pv_is( 'page' ); }
function is_archive(): bool  { return pv_is( 'archive' ); }
function is_category(): bool { return pv_is( 'category' ); }
function is_search(): bool   { return pv_is( 'search' ); }
function is_404(): bool      { return pv_is( 'is404' ); }

/* ===========================================================================
 * ループ
 * ======================================================================== */

function have_posts(): bool {
	return $GLOBALS['pv']['index'] + 1 < count( $GLOBALS['pv']['posts'] );
}

function the_post(): void {
	$GLOBALS['pv']['index']++;
	$GLOBALS['pv']['post'] = $GLOBALS['pv']['posts'][ $GLOBALS['pv']['index'] ];
	$GLOBALS['pv']['fields'] = $GLOBALS['pv']['post']['fields'] ?? array();
}

function wp_reset_postdata(): void {
	$i = $GLOBALS['pv']['index'];
	if ( $i >= 0 && isset( $GLOBALS['pv']['posts'][ $i ] ) ) {
		$GLOBALS['pv']['post']   = $GLOBALS['pv']['posts'][ $i ];
		$GLOBALS['pv']['fields'] = $GLOBALS['pv']['post']['fields'] ?? array();
	}
}

function pv_post( string $key, $default = '' ) {
	return $GLOBALS['pv']['post'][ $key ] ?? $default;
}

function get_the_ID(): int         { return (int) pv_post( 'id', 0 ); }
function get_queried_object_id(): int { return (int) ( $GLOBALS['pv']['queried_id'] ?? get_the_ID() ); }
function get_queried_object()      { return $GLOBALS['pv']['queried_object'] ?? null; }
function the_title(): void         { echo esc_html( (string) pv_post( 'title' ) ); }
function get_the_title( $id = 0 ): string { return (string) pv_post( 'title' ); }
function the_permalink(): void     { echo esc_url( (string) pv_post( 'url', '#' ) ); }
function get_permalink( $id = 0 ): string { return (string) pv_post( 'url', '#' ); }
function the_content(): void       { echo pv_post( 'content' ); }
function get_the_content(): string { return (string) pv_post( 'content' ); }

function the_title_attribute( $args = array() ): string {
	$t = esc_attr( (string) pv_post( 'title' ) );
	if ( ! empty( $args['echo'] ) ) { echo $t; }
	return $t;
}

function post_class( string $extra = '' ): void {
	$type = (string) pv_post( 'type', 'post' );
	echo 'class="' . esc_attr( trim( $extra . ' post-' . pv_post( 'id', 0 ) . ' ' . $type ) ) . '"';
}

function get_the_date( string $format = '', $id = 0 ): string {
	$ts = (int) pv_post( 'timestamp', time() );
	if ( DATE_W3C === $format ) { return gmdate( DATE_W3C, $ts ); }
	return gmdate( '' !== $format ? $format : 'Y.m.d', $ts );
}

function has_post_thumbnail( $id = 0 ): bool {
	return '' !== (string) pv_post( 'thumb', '' );
}

function get_the_post_thumbnail( $id = 0, string $size = '', array $attr = array() ): string {
	$src = (string) pv_post( 'thumb', '' );
	if ( '' === $src ) { return ''; }
	return sprintf(
		'<img src="%s" alt="%s" loading="%s">',
		esc_url( $src ),
		esc_attr( (string) ( $attr['alt'] ?? '' ) ),
		esc_attr( (string) ( $attr['loading'] ?? 'lazy' ) )
	);
}

/* ===========================================================================
 * タクソノミー
 * ======================================================================== */

function pv_terms(): array { return $GLOBALS['pv']['terms']; }

function get_the_terms( $id, string $tax ) {
	$slugs = (array) pv_post( 'terms', array() );
	$out   = array();
	foreach ( pv_terms() as $term ) {
		if ( in_array( $term->slug, $slugs, true ) ) { $out[] = $term; }
	}
	return array() === $out ? false : $out;
}

function get_terms( array $args = array() ): array { return pv_terms(); }

function get_term_by( string $field, string $value, string $tax ) {
	foreach ( pv_terms() as $term ) {
		if ( 'slug' === $field && $term->slug === $value ) { return $term; }
	}
	return false;
}

function get_term_link( $term ): string {
	return is_object( $term ) ? 'archive.html' : '#';
}

function get_term_meta( int $id, string $key, bool $single = false ) { return ''; }
function update_term_meta( int $id, string $key, $value ) { return true; }
function wp_insert_term( string $name, string $tax, array $args = array() ) { return array( 'term_id' => 1 ); }
function is_wp_error( $thing ): bool { return $thing instanceof WP_Error; }
function sanitize_title( string $s ): string { return strtolower( trim( $s ) ); }

/* ===========================================================================
 * ACF
 * ======================================================================== */

function get_field( string $name, $post_id = null ) {
	if ( 'option' === $post_id ) {
		return $GLOBALS['pv']['options'][ $name ] ?? null;
	}
	return $GLOBALS['pv']['fields'][ $name ] ?? null;
}

/**
 * 現在の行コンテキストを踏まえて、指定名の行配列を解決する。
 */
function pv_resolve_rows( string $selector ): array {
	$stack = $GLOBALS['pv']['rowstack'];
	if ( array() !== $stack ) {
		$top = $stack[ count( $stack ) - 1 ];
		$row = $top['rows'][ $top['i'] ] ?? null;
		if ( is_array( $row ) && isset( $row[ $selector ] ) && is_array( $row[ $selector ] ) ) {
			return $row[ $selector ];
		}
	}
	$value = $GLOBALS['pv']['fields'][ $selector ] ?? array();

	return is_array( $value ) ? $value : array();
}

function have_rows( string $selector, $post_id = null ): bool {
	$stack = &$GLOBALS['pv']['rowstack'];
	$depth = count( $stack );

	if ( $depth > 0 && $stack[ $depth - 1 ]['selector'] === $selector ) {
		// 実行中のループ。次の行があるか。
		if ( $stack[ $depth - 1 ]['i'] + 1 < count( $stack[ $depth - 1 ]['rows'] ) ) {
			return true;
		}
		array_pop( $stack );

		return false;
	}

	$rows = pv_resolve_rows( $selector );
	if ( array() === $rows ) {
		return false;
	}
	$stack[] = array( 'selector' => $selector, 'rows' => $rows, 'i' => -1 );

	return true;
}

function the_row(): void {
	$stack = &$GLOBALS['pv']['rowstack'];
	if ( array() !== $stack ) {
		$stack[ count( $stack ) - 1 ]['i']++;
	}
}

function pv_current_row(): array {
	$stack = $GLOBALS['pv']['rowstack'];
	if ( array() === $stack ) { return array(); }
	$top = $stack[ count( $stack ) - 1 ];

	return $top['rows'][ $top['i' ] ] ?? array();
}

function get_row_layout(): string {
	return (string) ( pv_current_row()['acf_fc_layout'] ?? '' );
}

function get_sub_field( string $name ) {
	return pv_current_row()[ $name ] ?? null;
}

function acf_add_local_field_group( array $g ): void {}
function acf_add_options_page( array $a ): void {}

/* ===========================================================================
 * テンプレート読み込み
 * ======================================================================== */

function get_template_directory(): string      { return dirname( __DIR__, 2 ) . '/theme'; }
function get_template_directory_uri(): string  { return $GLOBALS['pv']['theme_uri']; }

function get_template_part( string $slug, ?string $name = null ): void {
	$file = get_template_directory() . '/' . $slug . ( $name ? "-{$name}" : '' ) . '.php';
	if ( is_readable( $file ) ) {
		include $file;
	}
}

function get_header(): void  { include get_template_directory() . '/header.php'; }
function get_footer(): void  { include get_template_directory() . '/footer.php'; }
function get_sidebar(): void { include get_template_directory() . '/sidebar.php'; }
function get_search_form(): void { include get_template_directory() . '/searchform.php'; }

/* ===========================================================================
 * 出力・エスケープ
 * ======================================================================== */

function esc_html( $t ): string   { return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $t ): string   { return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $u ): string    { return htmlspecialchars( (string) $u, ENT_QUOTES, 'UTF-8' ); }
function esc_url_raw( $u ): string { return (string) $u; }
function esc_textarea( $t ): string { return esc_html( $t ); }

function __( string $t, string $d = '' ): string        { return $t; }
function esc_html__( string $t, string $d = '' ): string { return esc_html( $t ); }
function esc_attr__( string $t, string $d = '' ): string { return esc_attr( $t ); }
function esc_html_e( string $t, string $d = '' ): void   { echo esc_html( $t ); }
function esc_attr_e( string $t, string $d = '' ): void   { echo esc_attr( $t ); }
function _e( string $t, string $d = '' ): void           { echo esc_html( $t ); }

function wp_kses( $t, $allowed ): string { return (string) $t; }
function wp_kses_post( $t ): string      { return (string) $t; }
function wp_strip_all_tags( $t ): string { return strip_tags( (string) $t ); }
function wp_unslash( $v ) { return $v; }

function number_format_i18n( $n ): string { return number_format( (float) $n ); }
function date_i18n( string $f ): string   { return gmdate( $f ); }

$GLOBALS['pv_uid'] = 0;
function wp_unique_id( string $prefix = '' ): string {
	return $prefix . ( ++$GLOBALS['pv_uid'] );
}

/* ===========================================================================
 * サイト情報
 * ======================================================================== */

function home_url( string $path = '/' ): string { return 'index.html'; }
function admin_url( string $p = '' ): string    { return '#'; }
function get_option( string $k, $d = false )    { return $d; }

function get_bloginfo( string $show = 'name' ): string {
	return match ( $show ) {
		'charset'     => 'UTF-8',
		'description' => '',
		default       => (string) ( $GLOBALS['pv']['options']['org_name'] ?? 'LOM Theme デモ' ),
	};
}

function bloginfo( string $show = 'name' ): void { echo esc_html( get_bloginfo( $show ) ); }
function language_attributes(): void { echo 'lang="ja"'; }

function body_class( string $extra = '' ): void {
	$classes = array_keys( array_filter( $GLOBALS['pv']['conds'] ) );
	echo 'class="' . esc_attr( trim( $extra . ' ' . implode( ' ', $classes ) ) ) . '"';
}

function has_custom_logo(): bool { return true; }

function the_custom_logo(): void {
	printf(
		'<a href="%s" class="custom-logo-link"><img class="custom-logo" src="%s" alt="%s"></a>',
		esc_url( home_url() ),
		esc_url( $GLOBALS['pv']['logo'] ?? '' ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

function get_post_type_archive_link( string $t ) { return 'archive.html'; }
function get_search_query(): string { return (string) $GLOBALS['pv']['search']; }
function get_post_ancestors( $id ): array { return array(); }

function get_the_archive_title(): string { return (string) $GLOBALS['pv']['title']; }
function the_archive_description( string $before = '', string $after = '' ): void {}

/* ===========================================================================
 * ナビゲーション・ウィジェット
 * ======================================================================== */

function wp_nav_menu( array $args = array() ): void {
	$items = $GLOBALS['pv']['menus'][ $args['theme_location'] ?? '' ] ?? array();
	if ( array() === $items ) {
		return;
	}
	printf( '<nav><ul class="%s">', esc_attr( (string) ( $args['menu_class'] ?? '' ) ) );
	foreach ( $items as $label => $url ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul></nav>';
}

function register_nav_menus( array $m ): void {}
function register_sidebar( array $a ): void {}
function is_active_sidebar( string $id ): bool { return false; }
function dynamic_sidebar( string $id ): bool { return false; }

function paginate_links( array $args = array() ) {
	$total = (int) ( $args['total'] ?? 0 );
	if ( $total < 2 ) { return array(); }
	$out = array( '<span class="page-numbers current">1</span>' );
	for ( $i = 2; $i <= min( $total, 5 ); $i++ ) {
		$out[] = '<a class="page-numbers" href="#">' . $i . '</a>';
	}
	$out[] = '<a class="page-numbers" href="#">&raquo;</a>';

	return $out;
}

function previous_post_link( string $format = '', string $link = '' ): void {
	echo str_replace( '%link', '<a href="#">' . esc_html( str_replace( '%title', '前の記事', $link ) ) . '</a>', $format );
}
function next_post_link( string $format = '', string $link = '' ): void {
	echo str_replace( '%link', '<a href="#">' . esc_html( str_replace( '%title', '次の記事', $link ) ) . '</a>', $format );
}
function wp_link_pages( array $a = array() ): void {}
function comments_open(): bool { return false; }
function get_comments_number(): int { return 0; }
function comments_template(): void {}

/* ===========================================================================
 * フック・アセット（プレビューでは head/footer を自前で組む）
 * ======================================================================== */

function add_action( ...$a ): void {}
function add_filter( ...$a ): void {}
function apply_filters( string $tag, $value, ...$rest ) { return $value; }
function add_theme_support( ...$a ): void {}
function add_image_size( ...$a ): void {}
function load_theme_textdomain( ...$a ): void {}
function current_user_can( string $c ): bool { return false; }
/**
 * enqueue を記録する。
 *
 * どのCSS/JSを読むかはテーマ本体（inc/assets.php）に決めさせる。
 * プレビュー側で持つと条件分岐が二重管理になり、必ず乖離する。
 */
function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), $ver = null ): void {
	if ( '' !== $src && ! in_array( $src, $GLOBALS['pv']['styles'], true ) ) {
		$GLOBALS['pv']['styles'][] = $src;
	}
}

function wp_enqueue_script( string $handle, string $src = '', array $deps = array(), $ver = null, $args = false ): void {
	if ( '' !== $src && ! in_array( $src, $GLOBALS['pv']['scripts'], true ) ) {
		$GLOBALS['pv']['scripts'][] = $src;
	}
}
function wp_localize_script( ...$a ): void {}
function wp_create_nonce( string $a ): string { return 'preview'; }
function check_ajax_referer( ...$a ): bool { return true; }
function wp_send_json_success( $d = null ): void {}
function wp_send_json_error( $d = null, int $c = 0 ): void {}
function wp_body_open(): void {}

function wp_head(): void {
	echo '<title>' . esc_html( (string) ( $GLOBALS['pv']['doc_title'] ?? get_bloginfo( 'name' ) ) ) . "</title>\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	foreach ( $GLOBALS['pv']['styles'] as $src ) {
		echo '<link rel="stylesheet" href="' . esc_url( $src ) . '">' . "\n";
	}
	echo '<link rel="stylesheet" href="preview-bar.css">' . "\n";
}

function wp_footer(): void {
	foreach ( $GLOBALS['pv']['scripts'] as $src ) {
		echo '<script src="' . esc_url( $src ) . '"></script>' . "\n";
	}
}
