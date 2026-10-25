# テーマのパターン一覧など、WordPress がキャッシュしている情報を消す。
# パターン(themes/kapla/patterns/*.php)を追加・変更したあとに実行する。
#
# 注意: パターン一覧のキャッシュは「テーマのパス」から作ったキーで保存される。WP-CLI(このスクリプト)と
# Web からのアクセスでパスの表記が違うため、キーが2つできる。ここでは名前に関係なくすべて消す。
& "$PSScriptRoot\wp.ps1" eval 'global $wpdb; $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE ''%wp_theme_files_patterns%'' OR option_name LIKE ''%wp_theme_files_templates%''"); wp_cache_flush(); WC_Cache_Helper::get_transient_version("shipping", true); echo "cache cleared", PHP_EOL;'
