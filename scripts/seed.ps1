# ダミーページと商品を投入する。何度実行しても同じ状態になる。
# 前提: Local で kapla サイトが Start 状態であること。
$root = Split-Path -Parent $PSScriptRoot
& "$PSScriptRoot\wp.ps1" eval-file "$root\scripts\seed-pages.php"
& "$PSScriptRoot\wp.ps1" eval-file "$root\scripts\seed-products.php"
& "$PSScriptRoot\wp.ps1" rewrite flush
