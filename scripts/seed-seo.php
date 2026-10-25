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

update_option( 'blog_public', '0' );

echo "seo settings applied (noindex while demo)\n";
