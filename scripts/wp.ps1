# Local(LocalWP)の kapla サイトに対して WP-CLI を実行するラッパー。使い方: .\scripts\wp.ps1 option get siteurl
# 前提: Local で kapla サイトを Start していること。PHP / WP-CLI のパスと MySQL のポート(10005)は Local のインストールに合わせている。
$b    = "$env:APPDATA\Local\lightning-services\php-8.5.12+0\bin\win64"
$wp   = "$env:LOCALAPPDATA\Programs\Local\resources\extraResources\bin\wp-cli\wp-cli.phar"
$path = "$env:USERPROFILE\Local Sites\kapla\app\public"

$phpArgs = @(
  "-d", "extension_dir=$b\ext",
  "-d", "extension=mysqli", "-d", "extension=curl", "-d", "extension=mbstring",
  "-d", "extension=openssl", "-d", "extension=zip", "-d", "extension=fileinfo",
  "-d", "extension=gd", "-d", "extension=intl", "-d", "extension=sodium",
  "-d", "mysqli.default_port=10005", "-d", "mysqli.default_host=127.0.0.1",
  "-d", "memory_limit=512M",
  "-d", "error_reporting=24575", # E_ALL & ~E_DEPRECATED: 同梱の WP-CLI で出る PHP 8.5 の deprecated 警告を隠す
  $wp, "--path=$path"
)
& "$b\php.exe" @phpArgs @args
