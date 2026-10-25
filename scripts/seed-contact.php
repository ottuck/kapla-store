<?php
/**
 * お問い合わせフォーム(Contact Form 7)を作り、お問い合わせページに設置する(何度実行しても同じ状態になる)。
 * 使い方: .\scripts\wp.ps1 eval-file scripts\seed-contact.php
 *
 * スパム対策: ハニーポット(kp_website)は plugins/kapla-shop-tweaks が判定する。
 * Cloudflare Turnstile などの追加対策は公開前に検討する。
 */

if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
	echo "Contact Form 7 is not active\n";
	return;
}

$title = 'お問い合わせ';

$form = <<<'FORM'
<p><label>お名前(必須)<br>
[text* your-name autocomplete:name]</label></p>

<p><label>メールアドレス(必須)<br>
[email* your-email autocomplete:email]</label></p>

<p><label>お問い合わせの種類<br>
[select your-subject "ご注文について" "商品について" "配送・返品について" "その他"]</label></p>

<p><label>お問い合わせ内容(必須)<br>
[textarea* your-message]</label></p>

<p class="kapla-hp" aria-hidden="true"><label>Website<input type="text" name="kp_website" value="" tabindex="-1" autocomplete="off"></label></p>

<p>[acceptance your-consent] <a href="/privacy-policy/">プライバシーポリシー</a>に同意します [/acceptance]</p>

<p>[submit "送信する"]</p>
FORM;

$mail_body = <<<'BODY'
お名前: [your-name]
メールアドレス: [your-email]
種類: [your-subject]

お問い合わせ内容:
[your-message]

--
このメールは [_site_title] のお問い合わせフォームから送信されました。
BODY;

$mail2_body = <<<'BODY'
[your-name] 様

お問い合わせありがとうございます。以下の内容で受け付けました。2営業日以内にご返信します。

種類: [your-subject]

お問い合わせ内容:
[your-message]

--
[_site_title]
(デモサイトのため、実際の対応は行いません)
BODY;

$existing = get_posts(
	array(
		'post_type'      => 'wpcf7_contact_form',
		'post_status'    => 'publish',
		'title'          => $title,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	)
);

$contact_form = $existing ? WPCF7_ContactForm::get_instance( (int) $existing[0] ) : WPCF7_ContactForm::get_template();
$contact_form->set_title( $title );
$contact_form->set_locale( 'ja' );
$contact_form->set_properties(
	array(
		'form'   => $form,
		'mail'   => array(
			'active'             => true,
			'subject'            => '[_site_title] お問い合わせ: [your-subject]',
			'sender'             => '[_site_title] <wordpress@kapla.local>',
			'recipient'          => '[_site_admin_email]',
			'body'               => $mail_body,
			'additional_headers' => 'Reply-To: [your-email]',
			'attachments'        => '',
			'use_html'           => false,
			'exclude_blank'      => false,
		),
		'mail_2' => array(
			'active'             => true,
			'subject'            => '[_site_title] お問い合わせを受け付けました',
			'sender'             => '[_site_title] <wordpress@kapla.local>',
			'recipient'          => '[your-email]',
			'body'               => $mail2_body,
			'additional_headers' => '',
			'attachments'        => '',
			'use_html'           => false,
			'exclude_blank'      => false,
		),
	)
);
$form_id = $contact_form->save();

$page = get_page_by_path( 'contact' );
if ( $page ) {
	$content  = "<!-- wp:paragraph -->\n<p>ご質問・ご相談は、下記のフォームからお送りください。2営業日以内にご返信します。</p>\n<!-- /wp:paragraph -->\n\n";
	$content .= "<!-- wp:contact-form-7/contact-form-selector {\"id\":{$form_id},\"title\":\"{$title}\"} -->\n<div class=\"wp-block-contact-form-7-contact-form-selector\">[contact-form-7 id=\"{$form_id}\" title=\"{$title}\"]</div>\n<!-- /wp:contact-form-7/contact-form-selector -->\n\n";
	$content .= "<!-- wp:paragraph {\"textColor\":\"ink-soft\",\"fontSize\":\"small\"} -->\n<p class=\"has-ink-soft-color has-text-color has-small-font-size\">※ このサイトはデモ用です。掲載内容はすべて架空のもので、実際の販売・決済・配送は行っていません。</p>\n<!-- /wp:paragraph -->\n";
	wp_update_post( array( 'ID' => $page->ID, 'post_content' => $content ) );
}

echo 'contact form #' . $form_id . " ready\n";
