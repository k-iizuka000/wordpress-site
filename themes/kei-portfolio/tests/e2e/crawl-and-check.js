#!/usr/bin/env node
/**
 * E2E: 全画面表示とコンソールエラーチェック + Dockerログ [error] 検出
 * - 対象: 同一オリジン内のリンクをクローリング（最大100ページ）
 * - 既定BASE_URL: http://localhost:8080 （環境変数 BASE_URL で上書き可）
 * - 結果出力: リポジトリルート tests/e2e/結果_YYYYMMDD-hhmm.json
 */

const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');
const puppeteer = require('puppeteer');

const BASE_URL = process.env.BASE_URL || 'http://localhost:8080';
const MAX_PAGES = Number(process.env.MAX_PAGES || 100);
const START_PATHS = ['/', '/about/', '/skills/', '/contact/', '/portfolio/'];
const DOCKER_CONTAINER = process.env.DOCKER_CONTAINER || 'kei-portfolio-dev';

function nowIso() {
  return new Date().toISOString();
}

function sleep(ms) {
  return new Promise((res) => setTimeout(res, ms));
}

function isInternalUrl(url, origin) {
  try {
    const u = new URL(url, origin);
    const o = new URL(origin);
    if (u.origin !== o.origin) return false;
    if (u.hash && u.pathname === o.pathname) return false; // anchor only
    const href = u.href;
    if (/wp-admin|wp-login|logout/i.test(href)) return false;
    if (/^mailto:|^tel:/i.test(href)) return false;
    return true;
  } catch (e) {
    return false;
  }
}

async function getSitemapUrls(origin) {
  const urls = [];
  try {
    const sitemapUrl = new URL('/sitemap.xml', origin).href;
    const res = await fetch(sitemapUrl);
    if (res.ok) {
      const xml = await res.text();
      const matches = [...xml.matchAll(/<loc>(.*?)<\/loc>/g)];
      for (const m of matches) {
        const u = m[1].trim();
        if (isInternalUrl(u, origin)) urls.push(u);
      }
    }
  } catch (_) {}
  return urls;
}

function getOutPath() {
  const themeRoot = path.resolve(__dirname, '../../');
  const outDir = path.join(themeRoot, 'tests', 'e2e');
  fs.mkdirSync(outDir, { recursive: true });
  const stamp = new Date().toISOString().replace(/[-:T]/g, '').slice(0, 12);
  return path.join(outDir, `結果_${stamp}.json`);
}

async function crawlAndCheck() {
  const startTime = nowIso();
  const origin = BASE_URL.replace(/\/$/, '');
  const queue = [];
  const visited = new Set();
  const results = [];

  // 初期URL: sitemap + 既定START_PATHS
  const sitemapUrls = await getSitemapUrls(origin);
  for (const u of sitemapUrls) queue.push(u);
  for (const p of START_PATHS) queue.push(new URL(p, origin).href);

  const isMac = process.platform === 'darwin';
  const isLinux = process.platform === 'linux';
  const args = [
    '--no-sandbox',
    '--disable-setuid-sandbox',
    '--disable-gpu',
    '--disable-dev-shm-usage',
    '--no-first-run',
    '--no-default-browser-check',
  ];
  // Linux/Docker向けの安定化フラグはmacOSでは不安定なため付与しない
  if (isLinux) {
    args.push('--single-process', '--no-zygote');
  }

  const launchOpts = {
    headless: 'new',
    args,
  };
  // 実行ファイルの自動検出（環境変数が無い場合のみ）
  if (!process.env.PUPPETEER_EXECUTABLE_PATH) {
    const candidates = [];
    if (isMac) {
      candidates.push(
        '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        '/Applications/Chromium.app/Contents/MacOS/Chromium'
      );
    } else if (isLinux) {
      candidates.push(
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser'
      );
    }
    for (const p of candidates) {
      try {
        if (fs.existsSync(p)) {
          launchOpts.executablePath = p;
          break;
        }
      } catch (_) {}
    }
  } else {
    launchOpts.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
  }
  if (process.env.PUPPETEER_EXECUTABLE_PATH) {
    launchOpts.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
  }
  const browser = await puppeteer.launch(launchOpts);
  try {
    while (queue.length && visited.size < MAX_PAGES) {
      const url = queue.shift();
      if (!url || visited.has(url)) continue;
      if (!isInternalUrl(url, origin)) continue;
      visited.add(url);

      const page = await browser.newPage();
      const pageResult = {
        url,
        status: null,
        consoleErrors: [],
        pageErrors: [],
        requestFailures: [],
        ok: false,
        discoveredLinks: [],
      };

      page.on('console', (msg) => {
        if (msg.type() === 'error') {
          pageResult.consoleErrors.push(String(msg.text()));
        }
      });
      page.on('pageerror', (err) => {
        pageResult.pageErrors.push(String(err.message || err));
      });
      page.on('requestfailed', (req) => {
        // 許容する失敗種別があればここでフィルタ
        pageResult.requestFailures.push({
          url: req.url(),
          failure: req.failure()?.errorText || 'unknown',
        });
      });

      let response;
      try {
        response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
        // 追加で少し待機して遅延JSのエラーも拾う
        await sleep(800);
      } catch (e) {
        pageResult.pageErrors.push(`navigation-error: ${e.message}`);
      }

      pageResult.status = response ? response.status() : null;
      // リンク抽出
      try {
        const links = await page.evaluate(() => Array.from(document.querySelectorAll('a[href]')).map(a => a.href));
        for (const href of links) {
          if (isInternalUrl(href, location.origin)) {
            pageResult.discoveredLinks.push(href);
            if (!visited.has(href)) queue.push(href);
          }
        }
      } catch (_) {}

      pageResult.ok = (pageResult.status === 200)
        && pageResult.consoleErrors.length === 0
        && pageResult.pageErrors.length === 0
        && pageResult.requestFailures.length === 0;

      results.push(pageResult);
      await page.close();
    }
  } finally {
    await browser.close();
  }

  // Dockerログ確認（開始時刻以降）
  let dockerErrorLines = [];
  try {
    const args = ['logs', DOCKER_CONTAINER, '--since', startTime];
    const r = spawnSync('docker', args, { encoding: 'utf8' });
    if (r.status === 0 && r.stdout) {
      dockerErrorLines = r.stdout.split('\n').filter((l) => /\[error\]/i.test(l));
    } else if (r.stderr) {
      dockerErrorLines.push(`docker-error: ${r.stderr.trim()}`);
    }
  } catch (e) {
    dockerErrorLines.push(`docker-exec-error: ${e.message}`);
  }

  // サマリ
  const summary = {
    baseUrl: origin,
    pagesVisited: visited.size,
    pagesPassed: results.filter((r) => r.ok).length,
    pagesFailed: results.filter((r) => !r.ok).length,
    dockerErrorCount: dockerErrorLines.length,
    startedAt: startTime,
    finishedAt: nowIso(),
  };

  const outPath = getOutPath();
  fs.writeFileSync(
    outPath,
    JSON.stringify({ summary, results, dockerErrorLines }, null, 2)
  );

  // コンソールに要約を表示
  console.log('=== E2E Summary ===');
  console.log(`Base URL: ${summary.baseUrl}`);
  console.log(`Visited: ${summary.pagesVisited}, Passed: ${summary.pagesPassed}, Failed: ${summary.pagesFailed}`);
  if (summary.dockerErrorCount > 0) {
    console.log(`[WARN] Docker [error] detected: ${summary.dockerErrorCount}`);
  } else {
    console.log('Docker [error]: none');
  }

  // プロセス終了コード
  const hasFailures = summary.pagesFailed > 0 || summary.dockerErrorCount > 0;
  process.exit(hasFailures ? 1 : 0);
}

crawlAndCheck().catch((e) => {
  try {
    const outPath = getOutPath();
    const payload = {
      summary: {
        baseUrl: BASE_URL,
        pagesVisited: 0,
        pagesPassed: 0,
        pagesFailed: 0,
        dockerErrorCount: null,
        startedAt: nowIso(),
        finishedAt: nowIso(),
        fatalError: String(e && e.message ? e.message : e),
      },
      results: [],
      dockerErrorLines: [],
    };
    fs.writeFileSync(outPath, JSON.stringify(payload, null, 2));
    console.error('E2E fatal error (report written):', e);
  } catch (w) {
    console.error('E2E fatal error (report write failed):', w);
    console.error('Original error:', e);
  }
  process.exit(1);
});
