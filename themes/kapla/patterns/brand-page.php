<?php
/**
 * Title: Kapla ブランドページ
 * Slug: kapla/brand-page
 * Categories: text
 * Keywords: brand, ブランド, about
 * Viewport Width: 1440
 */

$kapla_poster = esc_url( get_theme_file_uri( 'assets/video/hero-poster.jpg' ) );
?>
<!-- wp:cover {"url":"<?php echo $kapla_poster; ?>","dimRatio":45,"overlayColor":"night","isUserOverlayColor":true,"minHeight":68,"minHeightUnit":"vh","contentPosition":"center center","isDark":true,"align":"full","className":"kapla-bp-hero","style":{"spacing":{"padding":{"left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-cover alignfull is-light kapla-bp-hero" style="padding-right:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60);min-height:68vh"><span aria-hidden="true" class="wp-block-cover__background has-night-background-color has-background-dim-45 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="<?php echo $kapla_poster; ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","className":"kapla-eyebrow"} -->
<p class="has-text-align-center kapla-eyebrow">BRAND</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":1,"textColor":"paper"} -->
<h1 class="wp-block-heading has-text-align-center has-paper-color has-text-color">空と大地の境目が、<br>消える一瞬。</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"paper"} -->
<p class="has-text-align-center has-paper-color has-text-color">ウユニ塩湖の夕暮れ。その静かな記憶を、香りに写し取る。</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|90","bottom":"var:preset|spacing|90","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--90);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--90);padding-left:var(--wp--preset--spacing--60)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|70"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"className":"kapla-eyebrow"} -->
<p class="kapla-eyebrow">STORY</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">香りで、記憶を持ち帰る</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Kaplaは、旅先で出会った景色や空気を香りに写し取るフレグランスブランドです。東京の小さな工房で、ひとつひとつ手作業で調香しています。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>見た景色を、あとから何度でも思い出せるように。香りの名前には、旅の記憶をそのまま使っています。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:cover {"url":"<?php echo $kapla_poster; ?>","dimRatio":0,"focalPoint":{"x":0.62,"y":0.78},"minHeight":520,"contentPosition":"center center","isDark":false} -->
<div class="wp-block-cover is-light" style="min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="<?php echo $kapla_poster; ?>" style="object-position:62% 78%" data-object-fit="cover" data-object-position="62% 78%"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","backgroundColor":"paper","style":{"spacing":{"padding":{"top":"var:preset|spacing|90","bottom":"var:preset|spacing|90","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull has-paper-background-color has-background" style="padding-top:var(--wp--preset--spacing--90);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--90);padding-left:var(--wp--preset--spacing--60)"><!-- wp:paragraph {"align":"center","className":"kapla-eyebrow"} -->
<p class="has-text-align-center kapla-eyebrow">PHILOSOPHY</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">三つのこだわり</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|70"},"blockGap":{"left":"var:preset|spacing|60"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--70)"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"kapla-bp-point","layout":{"type":"default"}} -->
<div class="wp-block-group kapla-bp-point"><!-- wp:paragraph {"className":"kapla-bp-num"} -->
<p class="kapla-bp-num">01</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">一本ずつ、小さなバッチで</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>数量を絞って作ることで、香りの鮮度を保ちます。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"kapla-bp-point","layout":{"type":"default"}} -->
<div class="wp-block-group kapla-bp-point"><!-- wp:paragraph {"className":"kapla-bp-num"} -->
<p class="kapla-bp-num">02</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">肌になじむオイルベース</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>ふんわりと長く、やさしく香ります。アルコールを使わないので、乾燥しにくいのも特長です。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"kapla-bp-point","layout":{"type":"default"}} -->
<div class="wp-block-group kapla-bp-point"><!-- wp:paragraph {"className":"kapla-bp-num"} -->
<p class="kapla-bp-num">03</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">景色から生まれる名前</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>SALAR(塩湖)、DUSK(夕景)、CEDAR(杉)。香りの名前は、旅の記憶から付けています。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|90","bottom":"var:preset|spacing|90","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--90);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--90);padding-left:var(--wp--preset--spacing--60)"><!-- wp:paragraph {"align":"center","className":"kapla-eyebrow"} -->
<p class="has-text-align-center kapla-eyebrow">SCENTS</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">景色から生まれる三つの香り</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|70"},"blockGap":{"left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--70)"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"kapla-swatch kapla-swatch--salar","layout":{"type":"default"}} -->
<div class="wp-block-group kapla-swatch kapla-swatch--salar"><!-- wp:paragraph {"className":"kapla-swatch__name"} -->
<p class="kapla-swatch__name">SALAR</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>塩湖。澄んだ空気と、静かな水面。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"kapla-swatch kapla-swatch--dusk","layout":{"type":"default"}} -->
<div class="wp-block-group kapla-swatch kapla-swatch--dusk"><!-- wp:paragraph {"className":"kapla-swatch__name"} -->
<p class="kapla-swatch__name">DUSK</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>夕景。アンバーとブラックペッパーの余韻。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"kapla-swatch kapla-swatch--cedar","layout":{"type":"default"}} -->
<div class="wp-block-group kapla-swatch kapla-swatch--cedar"><!-- wp:paragraph {"className":"kapla-swatch__name"} -->
<p class="kapla-swatch__name">CEDAR</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>杉。雨上がりの森を歩くような、落ち着いた香り。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|70"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--70)"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop/">ショップを見る</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","backgroundColor":"paper","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-paper-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--60)"><!-- wp:paragraph {"align":"center","textColor":"ink-soft","fontSize":"small"} -->
<p class="has-text-align-center has-ink-soft-color has-text-color has-small-font-size">※ このサイトはデモ用です。掲載内容はすべて架空のもので、実際の販売・決済・配送は行っていません。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
