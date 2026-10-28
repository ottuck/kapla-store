<?php
/**
 * Kapla theme setup.
 *
 * ブロックテーマのため、レイアウトはテンプレートと theme.json に任せ、ここでは style.css の読み込みと表示の小さな調整を行う。
 * 決済・注文のロジックは入れない(WooCommerce の標準機能を使う)。
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

// 商品一覧のカートボタン: すでにカートに入っているときの表示を「お買い物カゴ内の1」から「追加済み(1点)」に変える。
// 文言は WooCommerce が data-wp-context(JSON)に入れており、### が個数に置き換わる。
add_filter(
	'render_block_woocommerce/product-button',
	static function ( $html ) {
		$text = substr( wp_json_encode( '追加済み（###点）' ), 1, -1 );
		return preg_replace_callback(
			'/(inTheCartText(?:&quot;|")\s*:\s*(?:&quot;|"))(.*?)(?=&quot;|")/u',
			static function ( $m ) use ( $text ) {
				return $m[1] . $text;
			},
			$html
		);
	}
);

// サイト内検索は商品だけを対象にする(お知らせや固定ページが商品カードの形で混ざらないように)。
add_action(
	'pre_get_posts',
	static function ( $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
			$query->set( 'post_type', 'product' );
		}
	}
);

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
