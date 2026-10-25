# ダミーページ・商品・配送・お問い合わせフォーム・SEO設定を投入する。何度実行しても同じ状態になる。
# 前提: Local で kapla サイトが Start 状態で、woocommerce・contact-form-7・seo-simple-pack・kapla-shop-tweaks が有効であること。
$root = Split-Path -Parent $PSScriptRoot
foreach ($script in 'seed-pages.php', 'seed-products.php', 'seed-checkout.php', 'seed-contact.php', 'seed-seo.php', 'seed-branding.php') {
    Write-Host "== $script"
    & "$PSScriptRoot\wp.ps1" eval-file "$root\scripts\$script"
}
& "$PSScriptRoot\wp.ps1" rewrite flush
