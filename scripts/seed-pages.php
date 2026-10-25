<?php
/**
 * 日本語のダミーページとお知らせ記事を作る(何度実行しても同じ状態になる)。
 * 使い方: .\scripts\wp.ps1 eval-file scripts\seed-pages.php
 * 内容はすべてデモ用の架空の情報。実在の住所・電話・メールは入れない。
 */

function kp_p( string $text, string $extra = '' ): string {
	return "<!-- wp:paragraph {$extra} -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
}

function kp_h2( string $text ): string {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->\n\n";
}

function kp_h3( string $text ): string {
	return "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">{$text}</h3>\n<!-- /wp:heading -->\n\n";
}

function kp_list( array $items ): string {
	$out = "<!-- wp:list -->\n<ul class=\"wp-block-list\">";
	foreach ( $items as $item ) {
		$out .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
	}
	return $out . "</ul>\n<!-- /wp:list -->\n\n";
}

function kp_details( string $q, string $a ): string {
	return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{$q}</summary><!-- wp:paragraph -->\n<p>{$a}</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->\n\n";
}

function kp_table( array $rows ): string {
	$out = "<!-- wp:table {\"className\":\"is-style-stripes\"} -->\n<figure class=\"wp-block-table is-style-stripes\"><table class=\"has-fixed-layout\"><tbody>";
	foreach ( $rows as $label => $value ) {
		$out .= "<tr><th>{$label}</th><td>{$value}</td></tr>";
	}
	return $out . "</tbody></table></figure>\n<!-- /wp:table -->\n\n";
}

function kp_notice(): string {
	return kp_p( '※ このサイトはデモ用です。掲載内容はすべて架空のもので、実際の販売・決済・配送は行っていません。', '{"textColor":"ink-soft","fontSize":"small"}' );
}

function kp_upsert_page( string $slug, string $title, string $content, int $id = 0 ): int {
	$existing = $id ? get_post( $id ) : get_page_by_path( $slug );
	$args     = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
	);
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		return (int) wp_update_post( $args );
	}
	return (int) wp_insert_post( $args );
}

/* --- 既定のサンプル(Hello world! / Sample Page)を削除 --- */
$hello = get_post( 1 );
if ( $hello && 'Hello world!' === $hello->post_title ) {
	wp_delete_post( 1, true );
}
$sample = get_post( 2 );
if ( $sample && 'Sample Page' === $sample->post_title ) {
	wp_delete_post( 2, true );
}

/* --- WooCommerce が作ったページの日本語化 --- */
foreach ( array(
	'shop'      => array( 'shop', 'ショップ' ),
	'cart'      => array( 'cart', 'カート' ),
	'checkout'  => array( 'checkout', 'ご注文手続き' ),
	'myaccount' => array( 'my-account', 'マイアカウント' ),
) as $key => $info ) {
	$pid = wc_get_page_id( $key );
	if ( $pid > 0 ) {
		wp_update_post( array( 'ID' => $pid, 'post_title' => $info[1], 'post_name' => $info[0] ) );
	}
}

/* --- ブランド --- */
$brand  = kp_p( 'ウユニ塩湖の夕暮れ。空と大地の境目が消える、静かな一瞬を覚えていますか。', '{"align":"center","fontSize":"large"}' );
$brand .= kp_h2( '香りで、記憶を持ち帰る' );
$brand .= kp_p( 'Kaplaは、旅先で出会った景色や空気を香りに写し取るフレグランスブランドです。東京の小さな工房で、ひとつひとつ手作業で調香しています。' );
$brand .= kp_h3( '三つのこだわり' );
$brand .= kp_list( array(
	'<strong>一本ずつ、小さなバッチで</strong>:数量を絞り、香りの鮮度を保ちます。',
	'<strong>肌になじむオイルベース</strong>:ふんわりと長く、やさしく香ります。',
	'<strong>景色から生まれる名前</strong>:SALAR(塩湖)、DUSK(夕景)、CEDAR(杉)。香りの名前は旅の記憶から。',
) );
$brand .= kp_notice();
kp_upsert_page( 'brand', 'ブランド', $brand );

/* --- 店舗 --- */
$store  = kp_p( 'Kapla 神宮前アトリエ(架空)では、すべての香りを試していただけます。' );
$store .= kp_table( array(
	'住所'   => '〒150-0001 東京都渋谷区神宮前1-1-1(架空の住所)',
	'営業時間' => '11:00〜19:00(水曜定休)',
	'アクセス' => '東京メトロ「明治神宮前」駅 徒歩5分(架空)',
	'電話'   => '03-0000-0000(ダミー)',
) );
$store .= kp_notice();
kp_upsert_page( 'store', '店舗のご案内', $store );

/* --- よくあるご質問 --- */
$faq  = kp_h2( 'ご注文・お支払い' );
$faq .= kp_details( '支払い方法は何がありますか?', 'クレジットカードに対応する予定です。デモサイトのため、現在は実際の決済は行われません。' );
$faq .= kp_details( '注文後にキャンセルできますか?', '発送前であればキャンセルできます。お問い合わせフォームからご連絡ください。' );
$faq .= kp_h2( '配送' );
$faq .= kp_details( '送料はいくらですか?', '全国一律500円(税込)です。8,000円(税込)以上のご購入で送料無料になります。' );
$faq .= kp_details( 'どのくらいで届きますか?', 'ご注文から通常1〜3営業日で発送します。地域や天候により遅れる場合があります。' );
$faq .= kp_h2( '返品・交換' );
$faq .= kp_details( '返品・交換はできますか?', '未開封の商品に限り、到着後7日以内にご連絡いただければ承ります。往復の送料はお客様負担となります。' );
$faq .= kp_details( '商品が破損していました。', '到着後すぐにお問い合わせフォームから写真を添えてご連絡ください。新しい商品と交換いたします。' );
$faq .= kp_h2( '商品について' );
$faq .= kp_details( '香りの持続時間はどのくらいですか?', 'オイルパフュームで約4〜6時間が目安です。肌質や気温によって異なります。' );
$faq .= kp_details( '敏感肌でも使えますか?', 'すべての方にアレルギーが起きないわけではありません。初めての場合は腕の内側などで試してからご使用ください。' );
$faq .= kp_notice();
kp_upsert_page( 'faq', 'よくあるご質問', $faq );

/* --- お問い合わせ(フォームは Phase 2 で Contact Form 7 に置き換える) --- */
$contact  = kp_p( 'ご質問・ご相談は、下記のフォームからお送りください。2営業日以内にご返信します。' );
$contact .= kp_p( '(お問い合わせフォームは準備中です。)', '{"textColor":"ink-soft"}' );
$contact .= kp_notice();
kp_upsert_page( 'contact', 'お問い合わせ', $contact );

/* --- 特定商取引法に基づく表記 --- */
$legal  = kp_table( array(
	'販売事業者'     => 'Kapla(デモ)',
	'運営統括責任者'   => '山田 花子(架空)',
	'所在地'       => '〒150-0001 東京都渋谷区神宮前1-1-1(架空の住所)',
	'電話番号'      => '03-0000-0000(ダミー)',
	'メールアドレス'   => 'info@example.com(ダミー)',
	'販売価格'      => '各商品ページに税込価格で表示',
	'商品代金以外の必要料金' => '送料 全国一律500円(税込)。8,000円(税込)以上で無料',
	'お支払い方法'    => 'クレジットカード(予定)',
	'お支払い時期'    => 'ご注文時',
	'商品の引渡し時期'  => 'ご注文から1〜3営業日以内に発送',
	'返品・交換'     => '未開封品に限り到着後7日以内。往復の送料はお客様負担',
	'不良品の対応'    => '到着後7日以内にご連絡いただければ、新品と交換します',
) );
$legal .= kp_notice();
kp_upsert_page( 'legal', '特定商取引法に基づく表記', $legal );

/* --- プライバシーポリシー(WordPress が作った下書きを更新して公開) --- */
$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
$privacy    = kp_p( 'Kapla(デモ)は、お客様の個人情報を適切に取り扱います。' );
$privacy   .= kp_h2( '取得する情報' );
$privacy   .= kp_p( 'お問い合わせの際にいただくお名前・メールアドレス、ご注文の際にいただく配送先情報を取得します。' );
$privacy   .= kp_h2( '利用目的' );
$privacy   .= kp_list( array( 'ご注文の処理と商品の発送', 'お問い合わせへの対応', 'サービス改善のための統計的な分析' ) );
$privacy   .= kp_h2( '第三者への提供' );
$privacy   .= kp_p( '法令に基づく場合を除き、本人の同意なく第三者に提供しません。' );
$privacy   .= kp_notice();
kp_upsert_page( 'privacy-policy', 'プライバシーポリシー', $privacy, $privacy_id );

/* --- 返品・交換について(WooCommerce が作った下書きを更新して公開) --- */
$refund_post = get_page_by_path( 'refund_returns' );
if ( ! $refund_post ) {
	$refund_post = get_page_by_title( 'Refund and Returns Policy' );
}
$returns  = kp_p( '未開封の商品に限り、到着後7日以内にご連絡いただければ返品・交換を承ります。' );
$returns .= kp_list( array( '往復の送料はお客様負担です。', '使用済み・開封済みの商品はお受けできません。', '不良品・破損品は送料当社負担で新品と交換します。' ) );
$returns .= kp_notice();
kp_upsert_page( 'returns-policy', '返品・交換について', $returns, $refund_post ? (int) $refund_post->ID : 0 );

/* --- ホーム(固定フロントページ)とお知らせ(投稿ページ) --- */
$home_id = kp_upsert_page( 'home', 'ホーム', '' );
$news_id = kp_upsert_page( 'news', 'お知らせ', '' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $news_id );

/* --- お知らせ記事 --- */
wp_update_term( 1, 'category', array( 'name' => 'お知らせ', 'slug' => 'info' ) );
$posts = array(
	'open-notice'  => array( 'Kapla オンラインストア(デモ)オープンのお知らせ', 'Kaplaのオンラインストアがオープンしました。全国どこへでもお届けします。(デモ用のお知らせです)' ),
	'dusk-release' => array( '新作「DUSK 夕景」発売のお知らせ', '夕暮れの余韻をまとう新しい香り「DUSK」が登場します。アンバーとブラックペッパーが静かに重なります。(デモ用のお知らせです)' ),
);
foreach ( $posts as $slug => $info ) {
	$found = get_page_by_path( $slug, OBJECT, 'post' );
	$args  = array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => $info[0],
		'post_name'    => $slug,
		'post_content' => kp_p( $info[1] ),
	);
	if ( $found ) {
		$args['ID'] = $found->ID;
		wp_update_post( $args );
	} else {
		wp_insert_post( $args );
	}
}

echo "pages seeded\n";
