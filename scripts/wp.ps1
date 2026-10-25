# Local(LocalWP)의 kapla 사이트에 WP-CLI를 실행하는 래퍼. 사용: .\scripts\wp.ps1 option get siteurl
# 전제: Local에서 kapla 사이트가 Start 상태여야 한다. PHP/WP-CLI 경로와 MySQL 포트(10005)는 Local 설치 기준이다.
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
  "-d", "error_reporting=24575", # E_ALL & ~E_DEPRECATED: 번들 WP-CLI의 PHP 8.5 deprecated 경고 숨김
  $wp, "--path=$path"
)
& "$b\php.exe" @phpArgs @args
