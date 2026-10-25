<?php
/**
 * Kapla theme setup.
 *
 * 블록 테마라서 레이아웃은 템플릿과 theme.json이 맡고, 여기서는 style.css 로딩만 처리한다.
 * 결제·주문 로직은 넣지 않는다 (WooCommerce 표준 기능을 사용).
 */

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'kapla-style',
			get_stylesheet_uri(),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
);

// 商品画像のマウスオーバー拡大(ズーム)は使わない。
// WooCommerce のクラシックテンプレート用ブロックが同じフィルターで true を返すため、優先度を遅らせて最後に false にする。
add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false', 999 );

add_action(
	'after_setup_theme',
	static function () {
		add_editor_style( 'style.css' );
	}
);

/*
 * ページ見出し帯の「小見出し(英字)」と「リード文」は、固定ページのカスタムフィールドで編集する。
 * 空のときは表示されない(style.css の :empty)。テンプレートは page.html。
 */
add_action(
	'init',
	static function () {
		// 編集画面の「カスタムフィールド」パネルから値を変えられるようにする
		add_post_type_support( 'page', 'custom-fields' );
		foreach ( array( 'kapla_eyebrow', 'kapla_lead' ) as $key ) {
			register_post_meta(
				'page',
				$key,
				array(
					'type'         => 'string',
					'single'       => true,
					'default'      => '',
					'show_in_rest' => true,
				)
			);
		}
	}
);
