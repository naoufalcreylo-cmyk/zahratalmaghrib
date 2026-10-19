# Validation report

**Environment.** WordPress **7.1.3** (the same version as the live site) on WordPress Playground
(PHP 8.3, SQLite), site language العربية, permalinks `/%postname%/`. The content was **sample
content only**: 53 posts in the real category names/slugs, 4 pages (incl. an ad-slot test page), 2 authors, 15 images, 2
comments. Every sample post is tagged «محتوى تجريبي» and ends with a sample-content note. Nothing
was published to, or changed on, zahratalmaghrib.com. Rendering was checked in Chrome (headless and
the in-app browser).

Status key: ✅ verified · ⚠️ partial / with caveat · ⏳ not tested here.

## Functional checks

| Area | Check | Result |
|---|---|---|
| Homepage | Lead (sticky post as lead, latest posts fill the rest), latest grid, category sections in 5 layouts, dark band, three columns, editors' selection, opinion band | ✅ |
| Homepage | No story repeated between lead → latest → sections (sections with no unseen posts hide themselves) | ✅ |
| Homepage | Unconfigured ad slots and empty editors' selection render nothing | ✅ |
| Pagination | `/page/2/` … `/page/6/` real URLs, visible h1 «آخر الأخبار — الصفحة 2», السابق/التالي, out-of-range `/page/99/` → 404 | ✅ |
| Long Arabic article | Breadcrumbs, kicker, standfirst (manual excerpt only), byline + updated date, captioned featured image with credit, TOC (h2 + nested h3), table, lists, quote, pullquote, image, gallery, separator, tags, share, author card, comments, related | ✅ |
| Mixed direction | Arabic + French quotation + numbers + email + URL in one paragraph render in the right order; links isolated | ✅ |
| French article | Title/standfirst/body/TOC LTR in Noto Serif; header, breadcrumbs, byline stay Arabic RTL; French cards LTR inside Arabic grids | ✅ |
| Category archive | Title, description, sub-section chips (En français), count, list, pagination | ✅ |
| Tag archive | `/tag/الرياض/`: title, count «22 مقالاً», pagination (3 pages) | ✅ |
| Author archive | Avatar, bio, list | ✅ |
| Search | Arabic query returns results; empty query; no-results state with tips, section chips, link home | ✅ |
| 404 | Arabic page, search form, button home; HTTP 404 | ✅ |
| Missing images | Sand placeholder with the eight-point star in every card type | ✅ |
| Long headlines | 200-character headline wraps cleanly in lead side list, grid, archive row, related | ✅ |
| Logo | Existing white logo on the burgundy masthead/mobile bar; temporary wordmark when no logo (`screenshots/wordmark-fallback-*.png`) | ✅ |
| Ads | Filled slot shows «إشهار» label + reserved 90/100 px; empty slot on the same page hidden (`/ad-test/`) | ✅ |
| Site Editor | All 9 templates, 3 parts, 17 patterns parse with **0 invalid blocks**; ServerSideRender previews; Arabic block settings; post picker searches Arabic titles; logo picker | ✅ |
| Translations | 216 strings, all translated (`ar.po/.mo`, editor JSON); front end and editor show Arabic | ✅ |
| SEO safety | Theme outputs no Open Graph / JSON-LD; one h1 on every template type | ✅ |
| PHP | 40 theme files syntax-checked (PHP 8.3 tokenizer); 22 URLs crawled with a warning logger: **0 theme notices/warnings** | ✅ |

## Responsive (375 / 768 / 1440 px)

| Width | Result |
|---|---|
| 375 | Single burgundy bar (menu, logo, search). After the first story, grids become compact rows. No horizontal scroll on home, article or tag archive. |
| 768 | Hamburger menu kept until 1024 px. 2-column grids. Stacked column lists open with a side-by-side story. |
| 1440 | Burgundy masthead + slim sticky nav, 7/5 lead split, 3–4 column grids. |

## Accessibility

| Check | Result |
|---|---|
| Skip link first in tab order («تخطى إلى المحتوى») | ✅ |
| Visible focus: 2 px burgundy outline (white on burgundy, gold on dark) | ✅ |
| Mobile menu: opens with button, Escape closes, focus returns to toggle; items right-aligned | ✅ |
| Landmarks: header, nav «القائمة الرئيسية», breadcrumbs nav «مسار التنقل», main, footer; sections labelled by their headings | ✅ |
| Copy-link button announces «تم نسخ الرابط» in a live region; new-tab links say so to screen readers | ✅ |
| Text contrast: burgundy on ivory 7.97:1 · muted text 5.49:1 · text-safe gold 5.0:1 · white on burgundy ≥ 8:1 | ✅ (computed) |
| `prefers-reduced-motion` disables transitions and smooth scrolling | ✅ (CSS) |
| Screen-reader pass with NVDA / VoiceOver | ⏳ |

## Performance (lab, local, **not** field data)

Measured with headless Chrome against Playground, with no network throttling.

| Page | LCP | Server response (TTFB) | CLS | Requests | Transfer | JS files |
|---|---|---|---|---|---|---|
| Home, 375 px | 5.06 s | ≈ 4.5 s | 0 | 21 | 745 KB | 4 |
| Article, 375 px | 8.28 s | ≈ 7.2 s | 0 | 23 | 823 KB | 6 |
| Home, 1440 px | 5.24 s | ≈ 4.5 s | 0.018 | 24 | 1 032 KB | 4 |

- Playground runs PHP in WebAssembly, so the server response dominates. The front end adds roughly
  0.6–1 s after the first byte. These numbers say nothing about production LCP.
- What was verified: every image has `width`/`height`; only the lead image (homepage), the featured
  image (article) or the first result (archives) gets `fetchpriority="high"`; everything below the
  fold is lazy.
- Front-end JS is core's navigation/search modules plus a 1.5 KB copy-link script. There is no
  jQuery from the theme.
- Fonts are self-hosted variable woff2, Arabic + Latin subsets only. The two Arabic files are preloaded.
- **Targets, not results:** LCP < 2.5 s, CLS < 0.1, INP < 200 ms on production with page caching.
  Measure after go-live with PageSpeed Insights / Site Kit. Removing the legacy plugins listed in
  `MIGRATION.md` #10 matters more than anything the theme can do.

## Known limitations

- **No production or staging access.** Real-volume behaviour (11,839 posts), real media sizes, and
  interplay with AIOSEO, Site Kit, the Better* plugins and the live caching are untested. The
  shortcode audit in `MIGRATION.md` must be run on staging.
- Only tested in Chromium. Safari and Firefox were not tested. The static-header variant uses
  `:has()` (Safari 15.4+, Firefox 121+); without it the header simply stays sticky.
- Arabic search uses WordPress's standard search. It does not normalise hamza/alef or ta
  marbuta/ha forms; the no-results page suggests trying both spellings. A search plugin (e.g.
  Relevanssi) can add normalisation without theme changes.
- PHP 7.4 compatibility was kept in mind (no PHP 8-only syntax), but the code was only executed on PHP 8.3.
- WordPress Coding Standards (PHPCS) and Theme Check were not run: there is no PHP toolchain on this machine.
- The table of contents and share links read `post_content` and the permalink only; heading anchors
  are added to level-2/3 headings that have no id.
