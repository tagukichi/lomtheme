<?php
/**
 * アーカイブ用のカード1枚
 *
 * 既存サイトでは同じ役割のコンポーネントが2世代（jaycee-arc / jaycee-archive）
 * 併存していた。ここで1つに統合している。
 *
 * ショートコード経由ではなくPHPで直接出力するため、wpautop に
 * マークアップを壊されることがない（既存サイトではアンカーが
 * 二重になっていた）。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_term = lomtheme_primary_term();
?>
<article <?php post_class( 'lom-card' ); ?>>
	<a class="lom-card__link" href="<?php the_permalink(); ?>">
		<div class="lom-card__thumb">
			<?php lomtheme_the_thumbnail( 'lomtheme-card' ); ?>
			<?php lomtheme_the_badge( $lom_term ); ?>
		</div>
		<div class="lom-card__body">
			<?php lomtheme_the_date(); ?>
			<h3 class="lom-card__title"><?php the_title(); ?></h3>
		</div>
	</a>
</article>
