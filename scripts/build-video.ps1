# ヒーロー動画(原本 assets/source/mainVideo.mp4)を軽量化し、poster 画像と OGP 画像を作る。
# 使い方: .\scripts\build-video.ps1 -Ffmpeg <ffmpeg.exe のパス>
# 出力: themes/kapla/assets/video/hero.mp4 (音声なし), hero-poster.jpg, themes/kapla/assets/images/og-image.jpg
param(
    [Parameter(Mandatory = $true)][string]$Ffmpeg,
    [int]$Crf = 28,
    [double]$PosterSecond = 3
)

$root = Split-Path -Parent $PSScriptRoot
$src = Join-Path $root 'assets\source\mainVideo.mp4'
$videoDir = Join-Path $root 'themes\kapla\assets\video'
$imageDir = Join-Path $root 'themes\kapla\assets\images'
New-Item -ItemType Directory -Force $videoDir, $imageDir | Out-Null

# 音声を外し、H.264 で再エンコード(先頭から再生できるよう faststart)
& $Ffmpeg -y -v error -i $src -an -c:v libx264 -preset slow -crf $Crf -pix_fmt yuv420p -movflags +faststart (Join-Path $videoDir 'hero.mp4')

# 動画の読み込み中・モーション軽減時に表示する静止画
& $Ffmpeg -y -v error -ss $PosterSecond -i $src -frames:v 1 -q:v 4 (Join-Path $videoDir 'hero-poster.jpg')

# OGP 画像 (1200x630)
& $Ffmpeg -y -v error -ss $PosterSecond -i $src -frames:v 1 -vf 'scale=1200:-2,crop=1200:630' -q:v 4 (Join-Path $imageDir 'og-image.jpg')

Get-ChildItem $videoDir, $imageDir -File | Select-Object Name, @{n = 'KB'; e = { [math]::Round($_.Length / 1KB) } } | Format-Table -AutoSize
