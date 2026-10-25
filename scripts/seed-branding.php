<?php
/**
 * サイトアイコン(ファビコン)を Kapla のものに差し替える(何度実行しても同じ状態になる)。
 * 画像: themes/kapla/assets/images/favicon.png (scripts/build-favicon.py で作る 512x512)
 * 使い方: .\scripts\wp.ps1 eval-file scripts\seed-branding.php
 */
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$file = get_theme_file_path( 'assets/images/favicon.png' );
if ( ! file_exists( $file ) ) {
	echo "favicon.png not found\n";
	return;
}

$hash     = md5_file( $file );
$existing = get_posts( array(
	'post_type'      => 'attachment',
	'post_status'    => 'inherit',
	'meta_key'       => '_kapla_favicon_hash',
	'meta_value'     => $hash,
	'fields'         => 'ids',
	'posts_per_page' => 1,
) );

if ( $existing ) {
	$id = (int) $existing[0];
} else {
	$tmp = wp_tempnam( 'kapla-favicon.png' );
	copy( $file, $tmp );
	$id = media_handle_sideload( array( 'name' => 'kapla-favicon.png', 'tmp_name' => $tmp ), 0, 'Kapla favicon' );
	if ( is_wp_error( $id ) ) {
		echo 'favicon upload failed: ' . $id->get_error_message() . "\n";
		return;
	}
	update_post_meta( $id, '_kapla_favicon_hash', $hash );
}

// 以前のアイコン(この仕組みで作ったもの)は消して、メディアに残らないようにする
$old = (int) get_option( 'site_icon' );
if ( $old && $old !== $id && get_post_meta( $old, '_kapla_favicon_hash', true ) ) {
	wp_delete_attachment( $old, true );
}
update_option( 'site_icon', $id );
echo "site icon #{$id}\n";
