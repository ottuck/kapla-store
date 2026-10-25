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

add_action(
	'after_setup_theme',
	static function () {
		add_editor_style( 'style.css' );
	}
);
