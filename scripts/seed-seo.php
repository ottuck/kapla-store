<?php
/**
 * SEO SIMPLE PACK の基本設定と、デモサイトの検索エンジン非表示設定(何度実行しても同じ状態になる)。
 * 使い方: .\scripts\wp.ps1 eval-file scripts\seed-seo.php
 *
 * デモサイトとして公開する間は noindex にする(blog_public = 0)。
 * 本番として公開するときは WordPress の「検索エンジンがサイトをインデックスしないようにする」を外す。
 */

$ssp = get_option( 'ssp_settings', array() );
if ( ! is_array( $ssp ) ) {
	$ssp = array();
}
$ssp['home_desc']     = 'ウユニ塩湖の夕暮れから生まれた香りのブランド Kapla。東京の小さな工房で一本ずつ調香する、オイルパフューム・ハンドパフューム・キャンドルなど。(デモサイト)';
$ssp['home_title']    = '%_site_title_% %_sep_% %_tagline_%';
update_option( 'ssp_settings', $ssp );

// OGP 画像(テーマ内の画像)。URL は公開先の URL に合わせて search-replace で置き換わる。
$ogp = get_option( 'ssp_ogp', array() );
if ( ! is_array( $ogp ) ) {
	$ogp = array();
}
$ogp['og_image'] = get_theme_file_uri( 'assets/images/og-image.jpg' );
$ogp['tw_card']  = 'summary_large_image';
update_option( 'ssp_ogp', $ogp );

update_option( 'blog_public', '0' );

echo "seo settings applied (noindex while demo)\n";
