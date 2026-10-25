# Stripe のテスト用キーをローカルサイトに設定する。
# キーはこのウィンドウで入力する(画面に表示されず、チャットや Git には残らない)。
# 使い方: .\scripts\stripe-test-keys.ps1
# 前提: Local で kapla サイトが Start 状態で、woocommerce-gateway-stripe が有効であること。

$pk = Read-Host "Publishable key (pk_test_ で始まるテスト用)"
$skSecure = Read-Host "Secret key (sk_test_ で始まるテスト用)" -AsSecureString
$sk = [System.Net.NetworkCredential]::new("", $skSecure).Password

if ($pk -notmatch '^pk_test_' -or $sk -notmatch '^sk_test_') {
    Write-Host "テスト用キー(pk_test_ / sk_test_)ではありません。本番キーは設定しません。" -ForegroundColor Red
    exit 1
}

$env:KP_STRIPE_PK = $pk
$env:KP_STRIPE_SK = $sk
try {
    & "$PSScriptRoot\wp.ps1" eval '
        $s = get_option("woocommerce_stripe_settings", array());
        if (!is_array($s)) { $s = array(); }
        $s["enabled"] = "yes";
        $s["testmode"] = "yes";
        $s["test_publishable_key"] = getenv("KP_STRIPE_PK");
        $s["test_secret_key"] = getenv("KP_STRIPE_SK");
        update_option("woocommerce_stripe_settings", $s);
        echo "Stripe test mode enabled\n";
    '
} finally {
    Remove-Item Env:KP_STRIPE_PK -ErrorAction SilentlyContinue
    Remove-Item Env:KP_STRIPE_SK -ErrorAction SilentlyContinue
}
