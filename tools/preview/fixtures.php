<?php
/**
 * プレビュー用のサンプルデータ
 *
 * 画像は外部読み込みを避けるため、その場で生成したSVGのデータURIを使う。
 * file:// で開いても崩れないようにするため。
 *
 * @package LomTheme\Preview
 */

declare( strict_types = 1 );

/**
 * プレースホルダ画像を生成する。
 */
function pv_img( int $w, int $h, string $label, int $hue = 200 ): string {
	$size = max( 12, intdiv( min( $w, $h ), 9 ) );
	$svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">'
		. '<rect width="100%" height="100%" fill="hsl(' . $hue . ' 42% 84%)"/>'
		. '<text x="50%" y="50%" font-family="sans-serif" font-size="' . $size . '"'
		. ' fill="hsl(' . $hue . ' 38% 30%)" text-anchor="middle" dominant-baseline="middle">'
		. htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' ) . '</text></svg>';

	return 'data:image/svg+xml;charset=utf-8,' . rawurlencode( $svg );
}

/**
 * ACF の画像フィールドが返す配列の形に合わせる。
 */
function pv_acf_img( int $w, int $h, string $label, string $alt = '', int $hue = 200 ): array {
	$url = pv_img( $w, $h, $label, $hue );

	return array(
		'url'    => $url,
		'alt'    => $alt,
		'width'  => $w,
		'height' => $h,
		'sizes'  => array(
			'thumbnail'    => $url,
			'medium'       => $url,
			'medium_large' => $url,
			'large'        => $url,
		),
	);
}

/* ===========================================================================
 * 分類
 * ======================================================================== */

$GLOBALS['pv']['terms'] = array(
	new WP_Term( 1, 'お知らせ', 'news', 24 ),
	new WP_Term( 2, '開催案内', 'event-info', 8 ),
	new WP_Term( 3, '開催報告', 'event-report', 12 ),
);

// 分類の色はテーマ本体の lomtheme_default_terms() が持っているので、
// プレビュー側では定義しない（get_term_meta が空を返し、既定値に落ちる）。

/* ===========================================================================
 * サイト共通設定（ACFオプションページ相当）
 * ======================================================================== */

$GLOBALS['pv']['options'] = array(
	'org_name'      => '一般社団法人サンプル青年会議所',
	'postal_code'   => '000-0000',
	'address'       => '○○県○○市○○町1-2-3 ○○ビル4F',
	'tel'           => '000-000-0000',
	'fax'           => '000-000-0001',
	'email'         => 'info@example.jc',
	'join_page_url' => 'page-join.html',
	'copyright'     => '',
	'social_youtube'   => 'https://example.com/',
	'social_facebook'  => 'https://example.com/',
	'social_instagram' => 'https://example.com/',
	'social_x'         => 'https://example.com/',
);

$GLOBALS['pv']['logo'] = pv_img( 268, 122, 'JCI LOGO', 200 );

$GLOBALS['pv']['menus'] = array(
	'primary' => array(
		'HOME'         => 'front.html',
		'最新情報'      => 'archive.html',
		'理事長あいさつ' => 'page-greeting.html',
		'活動について'   => 'page-activity.html',
		'入会のご案内'   => 'page-join.html',
	),
	'footer' => array(
		'アクセス'        => '#',
		'個人情報保護方針' => '#',
	),
);

/* ===========================================================================
 * 投稿
 * ======================================================================== */

$pv_news = array(
	array( 'LIFESTYLE MARKETは明日16日(日)も開催', 'news', '2025-11-15', 12 ),
	array( '10月例会「M&Aが地域の未来を変える」開催！', 'event-report', '2025-10-20', 200 ),
	array( '第74回全国大会 佐賀大会', 'event-report', '2025-10-17', 30 ),
	array( '地域の未来をつなぐ新たな選択肢', 'news', '2025-09-26', 150 ),
	array( '【9月例会】「生成AI活用セミナー」を開催しました！', 'event-report', '2025-09-19', 260 ),
	array( 'より良い防災まちづくりのため市へ政策提言をして参りました', 'news', '2025-09-19', 90 ),
	array( '11月例会のご案内 ─ 会員拡大について考える', 'event-info', '2025-09-05', 45 ),
	array( 'サッカー練習in○○フットサルコート', 'event-report', '2025-09-11', 120 ),
	array( '創立65周年記念式典のお知らせ', 'event-info', '2025-09-10', 320 ),
);

$GLOBALS['pv']['all_posts'] = array();
foreach ( $pv_news as $i => $item ) {
	list( $title, $term, $date, $hue ) = $item;
	$GLOBALS['pv']['all_posts'][] = array(
		'id'        => 100 + $i,
		'type'      => 'post',
		'title'     => $title,
		'url'       => 'single.html',
		'timestamp' => strtotime( $date . ' 10:00:00' ),
		'terms'     => array( $term ),
		'thumb'     => pv_img( 480, 640, '写真 ' . ( $i + 1 ), $hue ),
		'content'   => '<p>本文のサンプルです。実際の記事ではここに事業の報告や告知が入ります。'
			. '写真や見出しを含む通常のWordPress本文がそのまま表示されます。</p>'
			. '<h2>当日の様子</h2>'
			. '<p>会場には多くの方にお越しいただきました。ご協力いただいた皆様に'
			. '心より御礼申し上げます。</p>'
			. '<ul><li>開催日：2025年11月15日（土）</li><li>会場：○○市民会館</li>'
			. '<li>参加者：約120名</li></ul>',
		'fields'    => array(),
	);
}

/* ===========================================================================
 * 固定ページ
 * ======================================================================== */

/** 活動について ─ セクションの繰り返しだけで組む */
function pv_page_activity(): array {
	$sections = array(
		array(
			'heading' => '地域の魅力体験事業',
			'body'    => '地域の良さをあらためて実感していただくため、市内の魅力ある資源をめぐる'
				. '体験型の事業を実施しました。参加者からは「知らなかった場所がたくさんあった」'
				. 'という声を多くいただきました。',
			'caption' => '写真は2019年5月に開催された事業の様子です',
			'count'   => 4,
			'hue'     => 130,
		),
		array(
			'heading' => '第66回全国大会 主管',
			'body'    => '全国から多くのメンバーが集う全国大会を主管しました。'
				. '延べ5,000名を超える来場者をお迎えし、地域の魅力を全国に発信する'
				. '機会となりました。',
			'caption' => '写真は全国大会 記念式典の様子です',
			'count'   => 4,
			'hue'     => 215,
		),
		array(
			'heading' => '「夢の力」イルミネーション事業',
			'body'    => '地域の皆様に笑顔になっていただけるよう、冬の街を彩る'
				. 'イルミネーション事業を開催しました。まちの明かりを灯すことで、'
				. '訪れる方々に季節の楽しみを感じていただきました。',
			'caption' => '写真はイルミネーション点灯式の様子です',
			'count'   => 3,
			'hue'     => 275,
		),
		array(
			'heading' => '青少年育成事業 ─ わんぱく相撲 ─',
			'body'    => '子どもたちが真剣に取り組む姿を通じて、礼儀と思いやりの心を'
				. '育む機会をつくっています。毎年多くの小学生に参加いただいています。',
			'caption' => '写真は地区大会・全国大会の様子です',
			'count'   => 4,
			'hue'     => 25,
		),
	);

	$blocks = array(
		array(
			'acf_fc_layout' => 'hero_image',
			'image'         => pv_acf_img( 900, 1150, 'ONE TEAM', 'スローガン ONE TEAM', 190 ),
			'ratio'         => 'portrait',
		),
	);

	foreach ( $sections as $s ) {
		$images = array();
		for ( $i = 1; $i <= $s['count']; $i++ ) {
			$images[] = array(
				'image' => pv_acf_img( 600, 450, '写真 ' . $i, $s['heading'] . ' の様子 ' . $i, $s['hue'] ),
			);
		}
		$blocks[] = array(
			'acf_fc_layout' => 'section_gallery',
			'heading'       => $s['heading'],
			'body'          => '<p>' . $s['body'] . '</p>',
			'images'        => $images,
			'columns'       => (string) $s['count'],
			'caption'       => $s['caption'],
		);
	}

	return $blocks;
}

/** 入会のご案内 ─ 7種のうち6種を使う */
function pv_page_join(): array {
	return array(
		array(
			'acf_fc_layout' => 'hero_image',
			'image'         => pv_acf_img( 1200, 675, 'メンバー集合写真', '会員の集合写真', 210 ),
			'ratio'         => 'wide',
		),
		array(
			'acf_fc_layout' => 'lead',
			'text'          => "サンプル青年会議所では\n新入会員を募集しています！",
		),
		array(
			'acf_fc_layout' => 'rich_text',
			'boxed'         => true,
			'body'          => '<p>一般社団法人サンプル青年会議所は、○○市・○○町を活動エリアとし、'
				. '地域社会をより活発に、より魅力的にするための活動をしている20歳から40歳までの'
				. '青年経済人で構成された団体です。国籍・性別・職業に制限なく入会できます。'
				. 'あなたも私たちと一緒に活動してみませんか？</p>',
		),
		array(
			'acf_fc_layout' => 'feature_cards',
			'heading'       => '入会のメリット',
			'cards'         => array(
				array(
					'heading'        => '自己研鑽',
					'image'          => pv_acf_img( 480, 320, '研修の様子', '研修に参加する会員', 205 ),
					'body'           => "<p>魅力ある人間に成長します！<br>会社組織の活性化にもつながります！</p>",
					'image_position' => 'left',
				),
				array(
					'heading'        => '地域貢献',
					'image'          => pv_acf_img( 480, 320, '事業の様子', '地域事業で活動する会員', 130 ),
					'body'           => '<p>地域に貢献する楽しさが体感できます！</p>',
					'image_position' => 'left',
				),
				array(
					'heading'        => '人脈構築',
					'image'          => pv_acf_img( 480, 320, '懇親会の様子', '懇親会に集まる会員', 25 ),
					'body'           => '<p>深いきずなを持った仲間づくりができます！</p>',
					'image_position' => 'left',
				),
			),
		),
		array(
			'acf_fc_layout' => 'column_boxes',
			'columns'       => '2',
			'boxes'         => array(
				array(
					'heading' => '入会資格',
					'items'   => array(
						array( 'text' => '20歳以上40歳未満の方（会員資格を取得した時点の年齢）' ),
						array( 'text' => '○○市、○○町およびその周辺地域に住所または勤務先を有する方' ),
					),
				),
				array(
					'heading' => '費用について',
					'items'   => array(
						array( 'text' => '入会金：30,000円' ),
						array( 'text' => '年会費：120,000円（年度途中の入会は月割り）' ),
					),
				),
			),
		),
		array(
			'acf_fc_layout' => 'info_box',
			'heading'       => '入会申込みについて',
			'items'         => array(
				array( 'text' => '入会申込書（本人の自筆・捺印が必要）' ),
				array( 'text' => '写真2枚（タテ4センチ×ヨコ3センチ）' ),
				array( 'text' => '運転免許証・健康保険証など（本人確認ができるもの）' ),
			),
			'note'          => '<p><strong>受付方法</strong><br>'
				. '必要書類をご準備のうえ、下記までご連絡ください。</p>'
				. '<p>〒000-0000 ○○県○○市○○町1-2-3 ○○ビル4F<br>'
				. '一般社団法人サンプル青年会議所 事務局<br>'
				. 'TEL：000-000-0000　FAX：000-000-0001<br>'
				. 'E-mail：<a href="mailto:info@example.jc">info@example.jc</a></p>',
		),
	);
}

/** 理事長あいさつ ─ 通常の本文で書くページの例 */
function pv_page_greeting(): array {
	return array(
		array(
			'acf_fc_layout' => 'hero_image',
			'image'         => pv_acf_img( 1200, 675, '理事長 写真', '理事長のポートレート', 200 ),
			'ratio'         => 'wide',
		),
		array(
			'acf_fc_layout' => 'lead',
			'text'          => "2026年度 第69代 理事長\n山田 太郎",
		),
		array(
			'acf_fc_layout' => 'rich_text',
			'boxed'         => false,
			'body'          => '<p>平素より一般社団法人サンプル青年会議所の活動に対しまして、'
				. '格別のご理解とご協力を賜り厚く御礼申し上げます。</p>'
				. '<p>私たち青年会議所は「修練」「奉仕」「友情」の三信条のもと、'
				. '明るい豊かな社会の実現を目指して活動してまいりました。'
				. '本年度も地域の課題に真正面から向き合い、一歩ずつ着実に'
				. '歩みを進めてまいります。</p>'
				. '<h2>本年度の方針</h2>'
				. '<p>地域とともに歩む運動を展開し、次世代へつなぐまちづくりに'
				. '取り組んでまいります。</p>',
		),
	);
}

/**
 * 固定ページの定義を返す。
 */
function pv_pages(): array {
	return array(
		'page-activity' => array(
			'id'      => 10,
			'type'    => 'page',
			'title'   => '活動について',
			'url'     => 'page-activity.html',
			'content' => '',
			'fields'  => array( 'page_content' => pv_page_activity(), 'show_sidebar' => true ),
		),
		'page-join' => array(
			'id'      => 11,
			'type'    => 'page',
			'title'   => '入会のご案内',
			'url'     => 'page-join.html',
			'content' => '',
			'fields'  => array( 'page_content' => pv_page_join(), 'show_sidebar' => true ),
		),
		'page-greeting' => array(
			'id'      => 12,
			'type'    => 'page',
			'title'   => '理事長あいさつ',
			'url'     => 'page-greeting.html',
			'content' => '',
			'fields'  => array( 'page_content' => pv_page_greeting(), 'show_sidebar' => true ),
		),
	);
}

/**
 * トップページ。
 */
function pv_front_page(): array {
	return array(
		'id'      => 1,
		'type'    => 'page',
		'title'   => 'HOME',
		'url'     => 'front.html',
		'content' => '',
		'fields'  => array(
			'show_sidebar'      => false,
			'mainvisual_slides' => array(
				array(
					'image' => pv_acf_img( 1600, 900, 'メインビジュアル 1', '', 200 ),
					'alt'   => '2026年度スローガン',
					'link'  => 'page-greeting.html',
				),
				array(
					'image' => pv_acf_img( 1600, 900, 'メインビジュアル 2', '', 140 ),
					'alt'   => '地域事業の様子',
					'link'  => '',
				),
				array(
					'image' => pv_acf_img( 1600, 900, 'メインビジュアル 3', '', 30 ),
					'alt'   => '会員募集のご案内',
					'link'  => 'page-join.html',
				),
			),
			'page_content'      => array(
				array(
					'acf_fc_layout' => 'section_gallery',
					'heading'       => '青年会議所とは',
					'body'          => '<p>20歳から40歳までの青年経済人によって構成される団体です。'
						. '「修練」「奉仕」「友情」の三信条のもと、明るい豊かな社会の実現を'
						. '目指して活動しています。</p>',
					'images'        => array(
						array( 'image' => pv_acf_img( 600, 450, '修練', '研修の様子', 205 ) ),
						array( 'image' => pv_acf_img( 600, 450, '奉仕', '地域奉仕の様子', 130 ) ),
						array( 'image' => pv_acf_img( 600, 450, '友情', '会員交流の様子', 25 ) ),
					),
					'columns'       => '3',
					'caption'       => '',
				),
			),
		),
	);
}
