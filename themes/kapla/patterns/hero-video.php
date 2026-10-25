<?php
/**
 * Title: Kapla ヒーロー(動画)
 * Slug: kapla/hero-video
 * Categories: banner
 * Keywords: hero, video, ヒーロー
 * Viewport Width: 1440
 */

$kapla_hero_video = esc_url( get_theme_file_uri( 'assets/video/hero.mp4' ) );
?>
<!-- wp:cover {"url":"<?php echo $kapla_hero_video; ?>","dimRatio":30,"overlayColor":"night","isUserOverlayColor":true,"backgroundType":"video","minHeight":100,"minHeightUnit":"vh","contentPosition":"bottom left","isDark":true,"align":"full","className":"kapla-hero","layout":{"type":"constrained","contentSize":"1320px"}} -->
<div class="wp-block-cover alignfull has-custom-content-position is-position-bottom-left kapla-hero" style="min-height:100vh"><span aria-hidden="true" class="wp-block-cover__background has-night-background-color has-background-dim-30 has-background-dim"></span><video class="wp-block-cover__video-background intrinsic-ignore" autoplay muted loop playsinline src="<?php echo $kapla_hero_video; ?>" data-object-fit="cover"></video><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"textColor":"paper","fontSize":"display"} -->
<h1 class="wp-block-heading has-paper-color has-text-color has-display-font-size">塩湖に沈む夕日を、<br>瓶に。</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"paper","fontSize":"medium"} -->
<p class="has-paper-color has-text-color has-medium-font-size">小さな工房で、一本ずつ。夕暮れの余韻をまとう香りをお届けします。</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"paper","borderColor":"paper","className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-paper-color has-text-color has-border-color has-paper-border-color wp-element-button" href="/shop/">ショップを見る</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->
