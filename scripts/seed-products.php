<?php
/**
 * カテゴリ・タグ・商品を作る(SKU をキーに、何度実行しても同じ状態になる)。
 * 使い方: .\scripts\wp.ps1 eval-file scripts\seed-products.php
 *
 * 商品画像は seed/images/<SKU>.jpg があれば自動で添付する(AI で生成した画像を置く)。
 * 価格は税込。商品名・香りはすべて架空。
 */

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

function kp_term( string $taxonomy, string $name, string $slug, int $parent = 0 ): int {
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	if ( $existing ) {
		wp_update_term( $existing->term_id, $taxonomy, array( 'name' => $name, 'parent' => $parent ) );
		return (int) $existing->term_id;
	}
	$res = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'parent' => $parent ) );
	return (int) $res['term_id'];
}

function kp_attach_image( int $product_id, string $sku ): void {
	$file = dirname( __DIR__ ) . '/seed/images/' . $sku . '.jpg';
	if ( ! file_exists( $file ) || get_post_thumbnail_id( $product_id ) ) {
		return;
	}
	$tmp = wp_tempnam( $sku . '.jpg' );
	copy( $file, $tmp );
	$id = media_handle_sideload( array( 'name' => $sku . '.jpg', 'tmp_name' => $tmp ), $product_id );
	if ( ! is_wp_error( $id ) ) {
		set_post_thumbnail( $product_id, $id );
	}
}

/* --- カテゴリ(原本: オイルパフューム / ハンドパフューム / ホーム&トラベル(ディフューザー・カーディフューザー・キャンドル) / ギフト) --- */
$cat_oil     = kp_term( 'product_cat', 'オイルパフューム', 'oil-perfume' );
$cat_hand    = kp_term( 'product_cat', 'ハンドパフューム', 'hand-perfume' );
$cat_home    = kp_term( 'product_cat', 'ホーム&トラベル', 'home-travel' );
$cat_diff    = kp_term( 'product_cat', 'ディフューザー', 'diffuser', $cat_home );
$cat_car     = kp_term( 'product_cat', 'カーディフューザー', 'car-diffuser', $cat_home );
$cat_candle  = kp_term( 'product_cat', 'キャンドル', 'candle', $cat_home );
$cat_gift    = kp_term( 'product_cat', 'ギフト', 'gift' );

/* --- タグ(原本の商品ステータス: New / Signature / Limited Edition) --- */
$tag_new       = kp_term( 'product_tag', '新作', 'new' );
$tag_signature = kp_term( 'product_tag', 'シグネチャー', 'signature' );
$tag_limited   = kp_term( 'product_tag', '限定', 'limited' );

$note_salar = '柑橘とソルト、ホワイトムスク。澄んだ空気と水面の光を思わせる、軽やかな香り。';
$note_dusk  = 'アンバー、ブラックペッパー、ベチバー。夕暮れの余韻のような、深く静かな香り。';
$note_cedar = 'シダーウッド、ヒノキ、苔。雨上がりの森を歩くような、落ち着いた香り。';
$demo       = '(デモ用の架空商品です)';

$products = array(
	array( 'KP-OIL-SALAR', 'SALAR 塩湖 オイルパフューム 10ml', 5500, array( $cat_oil ), array( $tag_signature ), $note_salar, 'ロールオンタイプのオイルパフューム。手首や首元にやさしくなじませてください。' ),
	array( 'KP-OIL-DUSK', 'DUSK 夕景 オイルパフューム 10ml', 5500, array( $cat_oil ), array( $tag_new ), $note_dusk, 'ロールオンタイプのオイルパフューム。夜の装いにも合う、深みのある香りです。' ),
	array( 'KP-OIL-CEDAR', 'CEDAR 杉 オイルパフューム 10ml', 5500, array( $cat_oil ), array(), $note_cedar, 'ロールオンタイプのオイルパフューム。男女を問わず使いやすい、木の香りです。' ),
	array( 'KP-HAND-SALAR', 'SALAR 塩湖 ハンドパフューム 30ml', 2200, array( $cat_hand ), array(), $note_salar, '手を洗ったあとにも気軽に使える、しっとりタイプのハンドクリーム。' ),
	array( 'KP-HAND-DUSK', 'DUSK 夕景 ハンドパフューム 30ml', 2200, array( $cat_hand ), array( $tag_new ), $note_dusk, '手を洗ったあとにも気軽に使える、しっとりタイプのハンドクリーム。' ),
	array( 'KP-DIFF-SALAR', 'SALAR 塩湖 リードディフューザー 100ml', 4400, array( $cat_diff ), array(), $note_salar, '玄関やリビングに置くだけで、空間がやわらかく香ります。約2か月使えます。' ),
	array( 'KP-DIFF-CEDAR', 'CEDAR 杉 リードディフューザー 100ml', 4400, array( $cat_diff ), array( $tag_limited ), $note_cedar, '玄関やリビングに置くだけで、空間がやわらかく香ります。数量限定です。' ),
	array( 'KP-CAR-DUSK', 'DUSK 夕景 カーディフューザー', 2750, array( $cat_car ), array(), $note_dusk, 'エアコンの送風口に挟むだけ。ドライブの時間をそっと彩ります。' ),
	array( 'KP-CANDLE-SALAR', 'SALAR 塩湖 キャンドル 120g', 3850, array( $cat_candle ), array(), $note_salar, '約25時間燃焼するソイワックスのキャンドル。' ),
	array( 'KP-CANDLE-DUSK', 'DUSK 夕景 キャンドル 120g', 3850, array( $cat_candle ), array( $tag_limited ), $note_dusk, '約25時間燃焼するソイワックスのキャンドル。数量限定です。' ),
	array( 'KP-GIFT-TRIAL', 'トライアルセット(オイルパフューム 3種 各3ml)', 3300, array( $cat_gift ), array( $tag_new ), 'SALAR・DUSK・CEDAR の3種を少量ずつ。はじめての方や、気軽な贈り物に。', 'ミニサイズのロールオン3本入り。ギフト用の箱に入れてお届けします。' ),
	array( 'KP-GIFT-SET', 'ギフトセット(オイルパフューム + キャンドル)', 8800, array( $cat_gift ), array( $tag_signature ), 'お好きな香りのオイルパフューム10mlとキャンドル120gのセット。', '同じ香りで揃えた特別なギフト。ギフトボックスと手提げ袋つきです。' ),
);

foreach ( $products as $row ) {
	list( $sku, $name, $price, $cats, $tags, $notes, $desc ) = $row;
	$pid     = wc_get_product_id_by_sku( $sku );
	$product = $pid ? wc_get_product( $pid ) : new WC_Product_Simple();

	$product->set_name( $name );
	$product->set_sku( $sku );
	$product->set_slug( strtolower( $sku ) );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_regular_price( (string) $price );
	$product->set_tax_status( 'taxable' );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 20 );
	$product->set_category_ids( $cats );
	$product->set_tag_ids( $tags );
	$product->set_short_description( $notes );
	$product->set_description( $desc . "\n\n" . $notes . "\n\n" . $demo );
	$saved = $product->save();

	kp_attach_image( $saved, $sku );
	echo $sku . ' => #' . $saved . "\n";
}

/* --- 既定の「未分類」カテゴリは使わないので、商品が無ければそのまま(削除はしない) --- */
echo "products seeded\n";
