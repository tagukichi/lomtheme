<?php
/**
 * ACF フィールド定義
 *
 * PHPで登録する理由:
 * - テーマに同梱でき、各LOMでのJSONインポート作業が不要になる
 * - Gitで差分管理できる
 * - 担当者が管理画面から誤ってフィールドを壊せない
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 画像サブフィールドの共通定義を返す。
 */
function lomtheme_acf_image_field( string $key, string $label = '画像', array $extra = array() ): array {
	return array_merge( array(
		'key'           => $key,
		'label'         => $label,
		'name'          => 'image',
		'type'          => 'image',
		'return_format' => 'array',
		'preview_size'  => 'medium',
		'library'       => 'all',
		'mime_types'    => 'jpg,jpeg,png,gif,webp,svg',
	), $extra );
}

/**
 * サイト共通設定のオプションページを追加する。
 *
 * 各LOMがテーマファイルを触らずに法人名・住所・SNSを設定できるようにする。
 */
function lomtheme_register_options_page(): void {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( array(
		'page_title' => __( 'サイト共通設定', 'lomtheme' ),
		'menu_title' => __( 'サイト共通設定', 'lomtheme' ),
		'menu_slug'  => 'lomtheme-settings',
		'capability' => 'manage_options',
		'icon_url'   => 'dashicons-admin-site-alt3',
		'position'   => 59,
		'redirect'   => false,
	) );
}
add_action( 'acf/init', 'lomtheme_register_options_page' );

/**
 * フィールドグループを登録する。
 */
function lomtheme_register_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/* ===================================================================
	 * サイト共通設定
	 * =================================================================== */

	acf_add_local_field_group( array(
		'key'             => 'group_lomtheme_settings',
		'title'           => __( 'サイト共通設定', 'lomtheme' ),
		'menu_order'      => 0,
		'style'           => 'default',
		'label_placement' => 'left',
		'location'        => array(
			array(
				array( 'param' => 'options_page', 'operator' => '==', 'value' => 'lomtheme-settings' ),
			),
		),
		'fields'          => array(
			array(
				'key'          => 'field_lomtheme_org_name',
				'label'        => __( '法人名', 'lomtheme' ),
				'name'         => 'org_name',
				'type'         => 'text',
				'instructions' => __( '例: 一般社団法人○○青年会議所', 'lomtheme' ),
			),
			array(
				'key'         => 'field_lomtheme_postal',
				'label'       => __( '郵便番号', 'lomtheme' ),
				'name'        => 'postal_code',
				'type'        => 'text',
				'placeholder' => '000-0000',
			),
			array(
				'key'   => 'field_lomtheme_address',
				'label' => __( '住所', 'lomtheme' ),
				'name'  => 'address',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_lomtheme_tel',
				'label' => __( '電話番号', 'lomtheme' ),
				'name'  => 'tel',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_lomtheme_fax',
				'label' => __( 'FAX', 'lomtheme' ),
				'name'  => 'fax',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_lomtheme_email',
				'label' => __( 'メールアドレス', 'lomtheme' ),
				'name'  => 'email',
				'type'  => 'email',
			),
			array(
				'key'          => 'field_lomtheme_join_url',
				'label'        => __( '入会案内ページのURL', 'lomtheme' ),
				'name'         => 'join_page_url',
				'type'         => 'url',
				'instructions' => __( 'ヘッダー右のボタンのリンク先。空欄ならボタンを表示しません。', 'lomtheme' ),
			),
			array(
				'key'          => 'field_lomtheme_copyright',
				'label'        => __( 'コピーライト表記', 'lomtheme' ),
				'name'         => 'copyright',
				'type'         => 'text',
				'instructions' => __( '空欄なら「© 年 サイト名 All Rights Reserved.」を自動表示します。', 'lomtheme' ),
			),
			array(
				'key'   => 'field_lomtheme_social_youtube',
				'label' => 'YouTube',
				'name'  => 'social_youtube',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_lomtheme_social_facebook',
				'label' => 'Facebook',
				'name'  => 'social_facebook',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_lomtheme_social_instagram',
				'label' => 'Instagram',
				'name'  => 'social_instagram',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_lomtheme_social_x',
				'label' => 'X (Twitter)',
				'name'  => 'social_x',
				'type'  => 'url',
			),
		),
	) );

	/* ===================================================================
	 * ページコンテンツ（フレキシブルコンテンツ）
	 * =================================================================== */

	acf_add_local_field_group( array(
		'key'                   => 'group_lomtheme_page_content',
		'title'                 => __( 'ページコンテンツ', 'lomtheme' ),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		// 本文エディタは隠さない。既存ページを移行する際、本文に中身が
		// 残ったままエディタだけ消えると編集できなくなるため。
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'page',
				),
			),
		),
		'fields'                => array(
			array(
				'key'          => 'field_lomtheme_page_content',
				'label'        => __( 'コンテンツ', 'lomtheme' ),
				'name'         => 'page_content',
				'type'         => 'flexible_content',
				'button_label' => __( 'ブロックを追加', 'lomtheme' ),
				'instructions' => __( '上から順に表示されます。ドラッグで並べ替えできます。', 'lomtheme' ),
				'layouts'      => array(

					/* --- ヒーロー画像 ------------------------------------ */
					'layout_lomtheme_hero' => array(
						'key'        => 'layout_lomtheme_hero',
						'name'       => 'hero_image',
						'label'      => __( 'ヒーロー画像', 'lomtheme' ),
						'display'    => 'block',
						'sub_fields' => array(
							lomtheme_acf_image_field( 'field_lomtheme_hero_image' ),
							array(
								'key'           => 'field_lomtheme_hero_ratio',
								'label'         => __( '表示比率', 'lomtheme' ),
								'name'          => 'ratio',
								'type'          => 'select',
								'default_value' => 'wide',
								'choices'       => array(
									'wide'     => __( '横長（16:9）', 'lomtheme' ),
									'standard' => __( '標準（4:3）', 'lomtheme' ),
									'portrait' => __( '縦長（3:4）', 'lomtheme' ),
									'auto'     => __( '画像のまま', 'lomtheme' ),
								),
							),
						),
					),

					/* --- リード文 ---------------------------------------- */
					'layout_lomtheme_lead' => array(
						'key'        => 'layout_lomtheme_lead',
						'name'       => 'lead',
						'label'      => __( 'リード文（中央・強調）', 'lomtheme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'          => 'field_lomtheme_lead_text',
								'label'        => __( 'テキスト', 'lomtheme' ),
								'name'         => 'text',
								'type'         => 'textarea',
								'rows'         => 3,
								'new_lines'    => 'br',
								'instructions' => __( '改行はそのまま反映されます。', 'lomtheme' ),
							),
						),
					),

					/* --- 本文 -------------------------------------------- */
					'layout_lomtheme_rich_text' => array(
						'key'        => 'layout_lomtheme_rich_text',
						'name'       => 'rich_text',
						'label'      => __( '本文', 'lomtheme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'          => 'field_lomtheme_rich_text_body',
								'label'        => __( '本文', 'lomtheme' ),
								'name'         => 'body',
								'type'         => 'wysiwyg',
								'media_upload' => 1,
								'toolbar'      => 'basic',
							),
							array(
								'key'           => 'field_lomtheme_rich_text_boxed',
								'label'         => __( '枠で囲む', 'lomtheme' ),
								'name'          => 'boxed',
								'type'          => 'true_false',
								'ui'            => 1,
								'default_value' => 0,
							),
						),
					),

					/* --- セクション＋画像グリッド -------------------------- */
					'layout_lomtheme_section_gallery' => array(
						'key'        => 'layout_lomtheme_section_gallery',
						'name'       => 'section_gallery',
						'label'      => __( 'セクション（見出し＋本文＋画像）', 'lomtheme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'   => 'field_lomtheme_sg_heading',
								'label' => __( '見出し', 'lomtheme' ),
								'name'  => 'heading',
								'type'  => 'text',
							),
							array(
								'key'       => 'field_lomtheme_sg_body',
								'label'     => __( '本文', 'lomtheme' ),
								'name'      => 'body',
								'type'      => 'textarea',
								'rows'      => 5,
								'new_lines' => 'wpautop',
							),
							array(
								'key'          => 'field_lomtheme_sg_images',
								'label'        => __( '画像', 'lomtheme' ),
								'name'         => 'images',
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( '画像を追加', 'lomtheme' ),
								'instructions' => __( '枚数は自由です。指定した列数で折り返します。', 'lomtheme' ),
								'sub_fields'   => array(
									lomtheme_acf_image_field( 'field_lomtheme_sg_image' ),
								),
							),
							array(
								'key'           => 'field_lomtheme_sg_columns',
								'label'         => __( '列数', 'lomtheme' ),
								'name'          => 'columns',
								'type'          => 'select',
								'default_value' => '4',
								'choices'       => array(
									'2' => __( '2列', 'lomtheme' ),
									'3' => __( '3列', 'lomtheme' ),
									'4' => __( '4列', 'lomtheme' ),
								),
							),
							array(
								'key'          => 'field_lomtheme_sg_caption',
								'label'        => __( 'キャプション', 'lomtheme' ),
								'name'         => 'caption',
								'type'         => 'text',
								'instructions' => __( '例: 写真は2019年5月に開催された事業の様子です', 'lomtheme' ),
							),
						),
					),

					/* --- 特徴カード --------------------------------------- */
					'layout_lomtheme_feature_cards' => array(
						'key'        => 'layout_lomtheme_feature_cards',
						'name'       => 'feature_cards',
						'label'      => __( 'カード（画像＋テキスト）', 'lomtheme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'   => 'field_lomtheme_fc_heading',
								'label' => __( 'セクション見出し', 'lomtheme' ),
								'name'  => 'heading',
								'type'  => 'text',
							),
							array(
								'key'          => 'field_lomtheme_fc_cards',
								'label'        => __( 'カード', 'lomtheme' ),
								'name'         => 'cards',
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'カードを追加', 'lomtheme' ),
								'sub_fields'   => array(
									array(
										'key'   => 'field_lomtheme_fc_card_heading',
										'label' => __( '小見出し', 'lomtheme' ),
										'name'  => 'heading',
										'type'  => 'text',
									),
									lomtheme_acf_image_field( 'field_lomtheme_fc_card_image' ),
									array(
										'key'       => 'field_lomtheme_fc_card_body',
										'label'     => __( '本文', 'lomtheme' ),
										'name'      => 'body',
										'type'      => 'textarea',
										'rows'      => 4,
										'new_lines' => 'wpautop',
									),
									array(
										'key'           => 'field_lomtheme_fc_card_position',
										'label'         => __( '画像の位置', 'lomtheme' ),
										'name'          => 'image_position',
										'type'          => 'select',
										'default_value' => 'left',
										'choices'       => array(
											'left'  => __( '左', 'lomtheme' ),
											'right' => __( '右', 'lomtheme' ),
										),
									),
								),
							),
						),
					),

					/* --- 箇条書きボックス --------------------------------- */
					'layout_lomtheme_column_boxes' => array(
						'key'        => 'layout_lomtheme_column_boxes',
						'name'       => 'column_boxes',
						'label'      => __( '箇条書きボックス（横並び）', 'lomtheme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'           => 'field_lomtheme_cb_columns',
								'label'         => __( '列数', 'lomtheme' ),
								'name'          => 'columns',
								'type'          => 'select',
								'default_value' => '2',
								'choices'       => array(
									'2' => __( '2列', 'lomtheme' ),
									'3' => __( '3列', 'lomtheme' ),
								),
							),
							array(
								'key'          => 'field_lomtheme_cb_boxes',
								'label'        => __( 'ボックス', 'lomtheme' ),
								'name'         => 'boxes',
								'type'         => 'repeater',
								'layout'       => 'block',
								'button_label' => __( 'ボックスを追加', 'lomtheme' ),
								'sub_fields'   => array(
									array(
										'key'   => 'field_lomtheme_cb_box_heading',
										'label' => __( '見出し', 'lomtheme' ),
										'name'  => 'heading',
										'type'  => 'text',
									),
									array(
										'key'          => 'field_lomtheme_cb_box_items',
										'label'        => __( '項目', 'lomtheme' ),
										'name'         => 'items',
										'type'         => 'repeater',
										'layout'       => 'table',
										'button_label' => __( '項目を追加', 'lomtheme' ),
										'sub_fields'   => array(
											array(
												'key'   => 'field_lomtheme_cb_box_item',
												'label' => __( '内容', 'lomtheme' ),
												'name'  => 'text',
												'type'  => 'text',
											),
										),
									),
								),
							),
						),
					),

					/* --- 案内ボックス ------------------------------------- */
					'layout_lomtheme_info_box' => array(
						'key'        => 'layout_lomtheme_info_box',
						'name'       => 'info_box',
						'label'      => __( '案内ボックス（番号付きリスト）', 'lomtheme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'   => 'field_lomtheme_ib_heading',
								'label' => __( '見出し', 'lomtheme' ),
								'name'  => 'heading',
								'type'  => 'text',
							),
							array(
								'key'          => 'field_lomtheme_ib_items',
								'label'        => __( '番号付き項目', 'lomtheme' ),
								'name'         => 'items',
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( '項目を追加', 'lomtheme' ),
								'sub_fields'   => array(
									array(
										'key'   => 'field_lomtheme_ib_item',
										'label' => __( '内容', 'lomtheme' ),
										'name'  => 'text',
										'type'  => 'text',
									),
								),
							),
							array(
								'key'          => 'field_lomtheme_ib_note',
								'label'        => __( '補足', 'lomtheme' ),
								'name'         => 'note',
								'type'         => 'wysiwyg',
								'media_upload' => 0,
								'toolbar'      => 'basic',
								'instructions' => __( '受付方法・住所・連絡先など。', 'lomtheme' ),
							),
						),
					),
				),
			),
		),
	) );

	/* ===================================================================
	 * ページ設定
	 * =================================================================== */

	acf_add_local_field_group( array(
		'key'             => 'group_lomtheme_page_settings',
		'title'           => __( 'ページ設定', 'lomtheme' ),
		'menu_order'      => 10,
		'position'        => 'side',
		'style'           => 'default',
		'label_placement' => 'top',
		'location'        => array(
			array(
				array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ),
			),
		),
		'fields'          => array(
			array(
				'key'           => 'field_lomtheme_show_sidebar',
				'label'         => __( 'サイドバーを表示', 'lomtheme' ),
				'name'          => 'show_sidebar',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 1,
			),
		),
	) );

	/* ===================================================================
	 * メインビジュアル（フロントページ）
	 * =================================================================== */

	acf_add_local_field_group( array(
		'key'             => 'group_lomtheme_mainvisual',
		'title'           => __( 'メインビジュアル', 'lomtheme' ),
		'menu_order'      => 0,
		'position'        => 'normal',
		'style'           => 'default',
		'label_placement' => 'top',
		'location'        => array(
			array(
				array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ),
			),
		),
		'fields'          => array(
			array(
				'key'          => 'field_lomtheme_mv_slides',
				'label'        => __( 'スライド', 'lomtheme' ),
				'name'         => 'mainvisual_slides',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => __( 'スライドを追加', 'lomtheme' ),
				'instructions' => __( '1枚だけでも動作します（その場合は自動送りしません）。', 'lomtheme' ),
				'sub_fields'   => array(
					lomtheme_acf_image_field( 'field_lomtheme_mv_image' ),
					array(
						'key'          => 'field_lomtheme_mv_alt',
						'label'        => __( '画像の説明', 'lomtheme' ),
						'name'         => 'alt',
						'type'         => 'text',
						'instructions' => __( '音声読み上げと検索エンジンのために必ず入力してください。', 'lomtheme' ),
						'required'     => 1,
					),
					array(
						'key'          => 'field_lomtheme_mv_link',
						'label'        => __( 'リンク先', 'lomtheme' ),
						'name'         => 'link',
						'type'         => 'url',
						'instructions' => __( '空欄ならリンクなしで表示します。', 'lomtheme' ),
					),
				),
			),
		),
	) );
}
add_action( 'acf/init', 'lomtheme_register_acf_fields' );
