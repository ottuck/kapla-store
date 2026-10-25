<?php
/**
 * 配送地域・送料・テスト用の支払い方法を設定する(何度実行しても同じ状態になる)。
 * 使い方: .\scripts\wp.ps1 eval-file scripts\seed-checkout.php
 *
 * 送料: 全国一律500円、8,000円(税込)以上で無料。デモなので送料は非課税扱いにして 500 円ちょうどで表示する。
 * 支払い: Stripe のキー設定前でも注文の流れを確認できるよう、代金引換を有効にする。
 */

$zone_name = '日本';
$zone      = null;
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( $zone_name === $z['zone_name'] ) {
		$zone = new WC_Shipping_Zone( $z['id'] );
		break;
	}
}
if ( ! $zone ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( $zone_name );
	$zone->add_location( 'JP', 'country' );
	$zone->save();
}

$has = array();
foreach ( $zone->get_shipping_methods() as $method ) {
	$has[ $method->id ] = $method;
}

if ( ! isset( $has['flat_rate'] ) ) {
	$instance_id = $zone->add_shipping_method( 'flat_rate' );
	$has['flat_rate'] = WC_Shipping_Zones::get_shipping_method( $instance_id );
}
update_option(
	'woocommerce_flat_rate_' . $has['flat_rate']->get_instance_id() . '_settings',
	array(
		'title'      => '全国一律送料',
		'tax_status' => 'none',
		'cost'       => '500',
	)
);

if ( ! isset( $has['free_shipping'] ) ) {
	$instance_id = $zone->add_shipping_method( 'free_shipping' );
	$has['free_shipping'] = WC_Shipping_Zones::get_shipping_method( $instance_id );
}
update_option(
	'woocommerce_free_shipping_' . $has['free_shipping']->get_instance_id() . '_settings',
	array(
		'title'           => '送料無料(8,000円以上)',
		'requires'        => 'min_amount',
		'min_amount'      => '8000',
		'ignore_discounts' => 'no',
	)
);

$cod = get_option( 'woocommerce_cod_settings', array() );
$cod = array_merge(
	is_array( $cod ) ? $cod : array(),
	array(
		'enabled'      => 'yes',
		'title'        => '代金引換',
		'description'  => '商品お受け取り時にお支払いください。(デモ用の支払い方法です)',
		'instructions' => 'ご注文ありがとうございます。(デモサイトのため、実際の発送・請求は行いません)',
	)
);
update_option( 'woocommerce_cod_settings', $cod );

echo 'zone #' . $zone->get_id() . " configured\n";
