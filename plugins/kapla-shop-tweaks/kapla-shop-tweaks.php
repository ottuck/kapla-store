<?php
/**
 * Plugin Name: Kapla Shop Tweaks
 * Description: Kapla ストア用の小さな調整。テーマには置かない注文まわりの処理をここにまとめる。
 * Version: 0.1.0
 * Requires Plugins: woocommerce
 * Text Domain: kapla-shop-tweaks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stripe プラグインに日本語訳が無いカード決済の名称を日本語にする。
 */
add_filter(
	'gettext',
	static function ( string $translated, string $text, string $domain ): string {
		if ( 'woocommerce-gateway-stripe' === $domain && 'Credit / Debit Card' === $text ) {
			return 'クレジットカード / デビットカード';
		}
		return $translated;
	},
	10,
	3
);

/**
 * 送料無料の条件(8,000円以上)を満たすときは、有料の送料を選択肢から外す。
 * WooCommerce の標準では無料と有料の両方が表示されるため。
 */
add_filter(
	'woocommerce_package_rates',
	static function ( array $rates ): array {
		$free = array();
		foreach ( $rates as $rate_id => $rate ) {
			if ( 'free_shipping' === $rate->get_method_id() ) {
				$free[ $rate_id ] = $rate;
			}
		}
		return $free ? $free : $rates;
	},
	100
);
