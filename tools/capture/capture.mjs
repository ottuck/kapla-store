// ポートフォリオ用の画面キャプチャ。
// 使い方: cd tools/capture && npm install && npm run capture
// 環境変数: BASE_URL(既定 http://kapla.local), CHROME_PATH(既定 Windows の Chrome)
// 出力: docs/portfolio/screenshots/*.jpg
import puppeteer from 'puppeteer-core';
import { mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const BASE = process.env.BASE_URL ?? 'http://kapla.local';
const CHROME = process.env.CHROME_PATH ?? 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const OUT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../docs/portfolio/screenshots');
const QUALITY = 82;

// カートに入れる商品(ID はシードの順: 27 = CEDAR オイルパフューム, 36 = ギフトセット)
const CART_IDS = [27, 36];

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const failures = [];

async function settle(page) {
  await page.evaluate(() => document.fonts.ready);
  // 遅延読み込みの画像を出すため、ゆっくり最後までスクロールして戻す
  await page.evaluate(async () => {
    const step = Math.max(400, Math.floor(window.innerHeight * 0.7));
    for (let y = 0; y < document.documentElement.scrollHeight; y += step) {
      window.scrollTo({ top: y, behavior: 'instant' });
      await new Promise((r) => setTimeout(r, 160));
    }
    window.scrollTo({ top: 0, behavior: 'instant' });
  });
  await page
    .waitForFunction(() => [...document.images].every((i) => i.complete && (i.naturalWidth > 0 || !i.currentSrc)), { timeout: 15000 })
    .catch(() => {});
  await sleep(700);
}

async function shot(page, name, { fullPage = true } = {}) {
  const file = path.join(OUT, `${name}.jpg`);
  await page.screenshot({ path: file, type: 'jpeg', quality: QUALITY, fullPage });
  console.log('saved', name);
}

async function step(name, fn) {
  try {
    await fn();
  } catch (e) {
    failures.push(name);
    console.error('FAILED', name, '-', e.message);
  }
}

async function open(page, url, waitFor) {
  await page.goto(`${BASE}${url}`, { waitUntil: 'networkidle2', timeout: 60000 });
  if (waitFor) await page.waitForSelector(waitFor, { timeout: 20000 });
  await settle(page);
}

async function addToCart(page) {
  for (const id of CART_IDS) {
    await page.goto(`${BASE}/?add-to-cart=${id}`, { waitUntil: 'networkidle2', timeout: 60000 });
  }
}

async function fillCheckout(page) {
  const type = async (sel, text) => {
    await page.waitForSelector(sel, { timeout: 8000 });
    await page.click(sel, { clickCount: 3 });
    await page.type(sel, text);
  };
  await type('#email', 'demo@example.com');
  await type('#shipping-last_name', '山田');
  await type('#shipping-first_name', '太郎');
  await type('#shipping-postcode', '150-0001');
  await type('#shipping-city', '渋谷区');
  await type('#shipping-address_1', '神宮前1-1-1');
  await page.select('#shipping-state', 'JP13').catch(() => {});
  await sleep(800);
}

mkdirSync(OUT, { recursive: true });
const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--hide-scrollbars'] });

// ---------- PC (1440px) ----------
{
  const ctx = await browser.createBrowserContext();
  const page = await ctx.newPage();
  await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

  await step('pc-home', async () => {
    await open(page, '/', 'video, .kapla-hero');
    await page.evaluate(() => {
      const v = document.querySelector('video');
      if (v) v.currentTime = 4;
    });
    await sleep(900);
    await shot(page, '01-home-hero-pc', { fullPage: false });
    await shot(page, '02-home-pc');
  });
  await step('pc-shop', async () => {
    await open(page, '/shop/', '.kapla-shop__grid li');
    await shot(page, '03-shop-pc');
  });
  await step('pc-product', async () => {
    await open(page, '/product/kp-oil-salar/', '.woocommerce-product-gallery');
    await shot(page, '04-product-pc');
  });
  await step('pc-brand', async () => {
    await open(page, '/brand/');
    await shot(page, '05-brand-pc');
  });
  await step('pc-store', async () => {
    await open(page, '/store/');
    await shot(page, '08-store-pc');
  });
  await step('pc-faq', async () => {
    await open(page, '/faq/');
    await shot(page, '09-faq-pc');
  });
  await step('pc-cart', async () => {
    await addToCart(page);
    await open(page, '/cart/', '.wc-block-cart-items');
    await shot(page, '06-cart-pc');
  });
  await step('pc-checkout', async () => {
    await open(page, '/checkout/', '#email');
    await fillCheckout(page).catch((e) => console.warn('checkout fill skipped:', e.message));
    await shot(page, '07-checkout-pc');
  });
  await ctx.close();
}

// ---------- スマホ (390px) ----------
{
  const ctx = await browser.createBrowserContext();
  const page = await ctx.newPage();
  await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true });

  await step('sp-home', async () => {
    await open(page, '/', 'video, .kapla-hero');
    await page.evaluate(() => {
      const v = document.querySelector('video');
      if (v) v.currentTime = 4;
    });
    await sleep(900);
    await shot(page, '01-home-sp', { fullPage: false });
  });
  await step('sp-menu', async () => {
    await open(page, '/shop/', '.kapla-shop__grid li');
    await page.click('.wp-block-navigation__responsive-container-open');
    await sleep(1200);
    await shot(page, '02-menu-sp', { fullPage: false });
  });
  await step('sp-shop', async () => {
    await open(page, '/shop/', '.kapla-shop__grid li');
    await shot(page, '03-shop-sp');
  });
  await step('sp-product', async () => {
    await open(page, '/product/kp-oil-salar/', '.woocommerce-product-gallery');
    await shot(page, '04-product-sp');
  });
  await step('sp-drawer', async () => {
    await addToCart(page);
    await open(page, '/shop/', '.wc-block-mini-cart__button');
    await page.click('.wc-block-mini-cart__button');
    await sleep(1500);
    await shot(page, '05-cart-drawer-sp', { fullPage: false });
  });
  await step('sp-checkout', async () => {
    await open(page, '/checkout/', '#email');
    await fillCheckout(page).catch((e) => console.warn('checkout fill skipped:', e.message));
    await shot(page, '06-checkout-sp');
  });
  await ctx.close();
}

await browser.close();
if (failures.length) {
  console.error('\n失敗したステップ:', failures.join(', '));
  process.exitCode = 1;
} else {
  console.log('\nすべて保存しました:', OUT);
}
