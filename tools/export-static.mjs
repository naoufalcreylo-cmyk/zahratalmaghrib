// Export the local WordPress Playground site as a static snapshot for preview hosting (e.g. Vercel).
// Usage: node tools/export-static.mjs [origin] [outDir]
// - Crawls internal pages from the homepage, saves each as <path>/index.html.
// - Non-ASCII paths (Arabic slugs) are mapped to short ASCII folders so any static host serves them.
// - Downloads every same-origin asset referenced by pages and CSS (styles, scripts, modules, fonts, images).
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const ORIGIN = (process.argv[2] || 'http://127.0.0.1:9400').replace(/\/$/, '');
const OUT = path.resolve(process.argv[3] || 'static-preview');
const MAX_PAGES = 400;
// Public address of the static copy: used in share links (WhatsApp / Facebook / copy link).
const PUBLIC = (process.env.PUBLIC_URL || 'https://zahratalmaghrib.vercel.app').replace(/\/$/, '');

const SKIP = [/^\/wp-admin/, /^\/wp-login/, /^\/wp-json/, /^\/xmlrpc/, /\/feed\/?$/, /^\/zam-dev\//, /\/comments\/feed/, /^\/wp-comments-post/, /\/embed\/?$/];

fs.rmSync(OUT, { recursive: true, force: true });
fs.mkdirSync(OUT, { recursive: true });

const pageMap = new Map(); // canonical pathname+search -> static path ("/x/ab12cd34/")
const queue = [];
const assets = new Set();

function canonical(u) {
	const url = new URL(u, ORIGIN + '/');
	if (url.origin !== ORIGIN) return null;
	let p = url.pathname;
	try { p = decodeURI(p); } catch {}
	// Keep only meaningful query strings (?p=, ?page_id=) – drop the rest.
	const keep = [...url.searchParams].filter(([k]) => k === 'p' || k === 'page_id');
	return p + (keep.length ? '?' + keep.map(([k, v]) => `${k}=${v}`).join('&') : '');
}

function isAsset(pathname) {
	return /\.(css|js|mjs|woff2?|ttf|png|jpe?g|gif|webp|avif|svg|ico|json)$/i.test(pathname) || pathname.startsWith('/wp-content/uploads/');
}

function staticPathFor(key) {
	if (pageMap.has(key)) return pageMap.get(key);
	const [p, q] = key.split('?');
	let out;
	if (!q && /^[\x20-\x7e]*$/.test(p)) {
		out = p.endsWith('/') ? p : p + '/';
	} else {
		// Keep a readable ASCII prefix (e.g. /category/, /tag/, /page/N/) and hash the rest.
		const segs = p.split('/').filter(Boolean);
		const ascii = [];
		for (const s of segs) {
			if (/^[a-z0-9-]+$/i.test(s)) ascii.push(s);
			else { ascii.push(crypto.createHash('md5').update(key).digest('hex').slice(0, 10)); break; }
		}
		if (q) ascii.push(crypto.createHash('md5').update(key).digest('hex').slice(0, 10));
		out = '/' + ascii.join('/') + '/';
		// Preserve trailing pagination (/tag/الرياض/page/2/ -> /tag/<hash>/page/2/).
		const pg = p.match(/\/page\/(\d+)\/?$/);
		if (pg && !out.endsWith(`/page/${pg[1]}/`)) {
			const base = staticPathFor(p.replace(/page\/\d+\/?$/, ''));
			out = base + `page/${pg[1]}/`;
		}
	}
	pageMap.set(key, out);
	return out;
}

function enqueue(href) {
	if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;
	let url;
	try { url = new URL(href.replace(/&amp;/g, '&'), ORIGIN + '/'); } catch { return; }
	if (url.origin !== ORIGIN) return;
	if (isAsset(url.pathname)) { assets.add(url.pathname); return; }
	if (url.searchParams.has('s') || url.searchParams.has('replytocom') || url.searchParams.has('zam_nologo')) return;
	const key = canonical(url.href);
	if (!key || SKIP.some((r) => r.test(key.split('?')[0]))) return;
	if (!pageMap.has(key)) { staticPathFor(key); queue.push(key); }
}

function rewriteHtml(html, out = '/') {
	// Share links: point at this page's public static address instead of the local server.
	const pub = PUBLIC + out;
	const encLocal = encodeURIComponent(ORIGIN + '/');
	// Encoded local URL (e.g. inside wa.me/?text=… and sharer.php?u=…) up to the next quote, & or space.
	html = html.split(encLocal).join('\u0000').replace(/\u0000[^"&\s]*/g, encodeURIComponent(pub));
	html = html.replace(/data-zam-copy="[^"]*"/g, `data-zam-copy="${pub}"`);
	// Head links that only make sense with a live WordPress (feeds, REST API, oEmbed, XML-RPC).
	html = html.replace(/<link[^>]+rel=["'](?:alternate|EditURI|https:\/\/api\.w\.org\/|shortlink|pingback)["'][^>]*>\s*/g, '');
	html = html.replace(/<link rel=.dns-prefetch. href=.\/\/(?:127\.0\.0\.1|localhost). \/>\s*/g, '');
	// Absolute origin URLs (plain and JSON-escaped) -> root-relative.
	const esc = ORIGIN.replace(/\//g, '\\/');
	html = html.split(esc).join('').split(ORIGIN).join('');
	html = html.split(ORIGIN.replace('127.0.0.1', 'localhost')).join('');
	// Internal page links -> static paths.
	html = html.replace(/(href)=(["'])([^"']+)\2/g, (m, attr, q, href) => {
		if (/^(#|mailto:|tel:|https?:|\/\/|data:)/.test(href)) return m;
		let url;
		try { url = new URL(href.replace(/&amp;/g, '&'), ORIGIN + '/'); } catch { return m; }
		if (isAsset(url.pathname)) return m;
		const key = canonical(url.href);
		if (key && pageMap.has(key)) return `${attr}=${q}${pageMap.get(key)}${url.hash}${q}`;
		return m;
	});
	// Static snapshot: search and comments cannot work without PHP.
	html = html.replace(/<form([^>]*role="search"[^>]*|[^>]*class="[^"]*search[^"]*"[^>]*)>/g, (m) => m.replace(/action="[^"]*"/, 'action="/recherche-indisponible/"').replace(/method="[^"]*"/, 'method="get"'));
	html = html.replace(/<div id="respond"[\s\S]*?<\/form>\s*<\/div>/, '<div id="respond" class="comment-respond"><p class="zam-static-note">التعليقات غير متاحة في هذه النسخة التجريبية الثابتة.</p></div>');
	// Banner identifying the snapshot.
	html = html.replace(/<body([^>]*)>/, '<body$1><div style="background:#202020;color:#f6eef0;font:13px/1.6 system-ui,sans-serif;text-align:center;padding:6px 12px">نسخة عرض ثابتة بمحتوى تجريبي — Static preview with sample content, not the live site</div>');
	return html;
}

async function get(pathname) {
	const res = await fetch(ORIGIN + pathname, { redirect: 'follow' });
	return res;
}

// 1. Crawl pages.
enqueue('/');
for (const extra of ['/page/2/', '/no-such-page-404/']) enqueue(extra);
let n = 0;
const pages = new Map();
async function crawlOne(key) {
	const res = await get(encodeURI(key));
	const html = await res.text();
	if (!res.headers.get('content-type')?.includes('text/html')) return;
	pages.set(key, { html, status: res.status });
	for (const m of html.matchAll(/href=["']([^"']+)["']/g)) enqueue(m[1]);
	for (const m of html.matchAll(/(?:src|data-src)=["']([^"']+)["']/g)) enqueue(m[1]);
	for (const m of html.matchAll(/srcset=["']([^"']+)["']/g)) m[1].split(',').forEach((s) => enqueue(s.trim().split(/\s+/)[0]));
	for (const m of html.matchAll(/"(\/[^"]+?\.(?:m?js|css)(?:\?[^"]*)?)"/g)) enqueue(m[1]);
	// Inline styles (theme.json @font-face, background images).
	for (const m of html.matchAll(/url\(\s*['"]?([^'")]+)['"]?\s*\)/g)) enqueue(m[1]);
	for (const m of html.matchAll(/(https?:\\?\/\\?\/[^"'\s]+?\.(?:m?js|css|png|jpe?g|webp|svg)(?:\?[^"'\s]*)?)/g)) enqueue(m[1].replace(/\\\//g, '/'));
}
// Crawl with a few parallel workers (Playground serves several requests at once).
const CONCURRENCY = 6;
let active = 0;
await new Promise((resolve) => {
	const pump = () => {
		while (active < CONCURRENCY && queue.length && n < MAX_PAGES) {
			const key = queue.shift();
			n++;
			active++;
			crawlOne(key)
				.catch((e) => console.warn('page failed', key, e.message))
				.finally(() => {
					active--;
					if (n % 10 === 0) console.log(`crawled ${pages.size}, queued ${queue.length}`);
					pump();
				});
		}
		if (!active && (!queue.length || n >= MAX_PAGES)) resolve();
	};
	pump();
});

// 2. Search placeholder page (search needs PHP).
const notFound = [...pages.entries()].find(([k]) => k.startsWith('/no-such-page'));

// 3. Write pages.
for (const [key, { html, status }] of pages) {
	if (key.startsWith('/no-such-page')) continue;
	const out = pageMap.get(key);
	const file = path.join(OUT, decodeURIComponent(out), 'index.html');
	fs.mkdirSync(path.dirname(file), { recursive: true });
	fs.writeFileSync(file, rewriteHtml(html, out));
}
if (notFound) {
	fs.writeFileSync(path.join(OUT, '404.html'), rewriteHtml(notFound[1].html));
	const msg = rewriteHtml(notFound[1].html)
		.replace(/(<h1[^>]*>)[^<]*/, '$1البحث غير متاح في النسخة الثابتة')
		.replace(/<p>قد يكون الرابط[^<]*<\/p>/, '<p>هذه نسخة عرض ثابتة بدون خادم PHP، لذا لا يعمل البحث هنا. على موقع ووردبريس الحقيقي يعمل البحث بشكل كامل.</p>');
	fs.mkdirSync(path.join(OUT, 'recherche-indisponible'), { recursive: true });
	fs.writeFileSync(path.join(OUT, 'recherche-indisponible', 'index.html'), msg);
}

// 4. Download assets (and assets referenced from CSS / JS modules).
const done = new Set();
async function fetchAsset(p) {
	const clean = p.split('?')[0].split('#')[0];
	if (done.has(clean)) return;
	done.add(clean);
	const res = await get(clean);
	if (!res.ok) { console.warn('asset', res.status, clean); return; }
	const buf = Buffer.from(await res.arrayBuffer());
	const file = path.join(OUT, decodeURIComponent(clean));
	fs.mkdirSync(path.dirname(file), { recursive: true });
	let data = buf;
	if (/\.(css|m?js|json)$/.test(clean)) {
		let text = buf.toString('utf8').split(ORIGIN.replace(/\//g, '\\/')).join('').split(ORIGIN).join('');
		data = Buffer.from(text);
		const base = clean.slice(0, clean.lastIndexOf('/') + 1);
		const refs = [];
		for (const m of text.matchAll(/url\(\s*['"]?([^'")]+)['"]?\s*\)/g)) refs.push(m[1]);
		for (const m of text.matchAll(/(?:import|from)\s*\(?\s*["'](\.{1,2}\/[^"']+|\/[^"']+)["']/g)) refs.push(m[1]);
		for (const r of refs) {
			if (/^(data:|https?:|#)/.test(r)) continue;
			const abs = new URL(r, ORIGIN + base).pathname;
			await fetchAsset(abs);
		}
	}
	fs.writeFileSync(file, data);
}
for (const a of assets) await fetchAsset(a);

console.log(`pages: ${pages.size}, assets: ${done.size}, out: ${OUT}`);
