# Migration notes: Publisher → Zahrat Al Maghrib theme

Based on inspecting the public site on 19 October 2026. No admin, database or staging access was
available, so everything below comes from rendered pages and the public REST API. Items marked
**verify on staging** could not be confirmed from outside.

## What does not change

Switching themes in WordPress does not touch content, and this theme adds no activation routine.

- Post and page IDs, slugs, permalinks (`/%postname%/`), categories, tags, authors, media, comments: **unchanged**.
- SEO metadata stays in AIOSEO. The theme prints no `<meta>` description, Open Graph, Twitter cards or
  JSON-LD. Only WordPress core's own `rel=canonical` remains, and AIOSEO replaces it as it does today.
- **No URL changes → no redirects are needed.**

## Things that need action before go-live

| # | Item | Why | Action |
|---|---|---|---|
| 1 | **Logo** | Publisher stores its logo in its own options, not in WordPress's Site Logo. | Header → logo block → **اختيار الشعار** → pick `logozahra.png` (already in the Media Library). Also fix its alt text: it currently reads “Publisher”. |
| 2 | **Main menu** | Publisher uses its own `main-menu` location; block themes use the Navigation block. | Editor → Navigation → **Import classic menus**, then trim (suggested structure below). |
| 3 | **Footer menu** | New classic location “Footer links”. | Appearance → Menus → assign إتصل بنا (843), للإشهار (839), فريق العمل (9125). |
| 4 | **Social links** | The live header links to `facebook.com/BetterSTU`, `twitter.com/BetterSTU`, `instagram.com/betterstudio`: BetterStudio's demo accounts, not the magazine's. | Add the real accounts to the header/footer Social Icons blocks. They stay hidden until then. |
| 5 | **Ads** | Better Ads Manager placements are tied to Publisher's hooks and won't appear in the new layout. | Paste ad units into the **مساحة إشهارية** blocks (home ×2, article, archives). AdSense Auto ads via Site Kit keep working. |
| 6 | **Sidebar widgets** | Publisher's sidebars (e.g. the “Trending Now” box) have no equivalent in a block theme. | List them on staging (Appearance → Widgets under Publisher). Recreate any that are still wanted as blocks in templates. Popularity widgets were deliberately not reproduced without real readership data. |
| 7 | **Shortcodes in old posts** | The 160 posts sampled (2021–2026) rendered clean Gutenberg markup with no visible shortcodes. But Publisher was active while rendering them, so its own shortcodes would have been expanded and hidden. **Verify on staging.** | Run the queries below. For any hits, keep the providing plugin active, or convert those posts. |
| 8 | **WPBakery / Visual Composer** | Page 9124 “Front Page” (Publisher demo) already shows raw `[vc_row]` shortcodes publicly. None of the inspected real pages use Visual Composer Website Builder markup. | Decide whether to unpublish page 9124 *(needs approval: content change)*. If nothing else uses Visual Composer, the plugin can be deactivated later. |
| 9 | **Publisher-bundled plugins** | `better-adsmanager`, `better-social-counter`, `better-reviews`, `blockquote-pack-pro`, `financial-pack-pro`, `newsletter-pack-pro`, `smart-lists-pack-pro`, `better-playlist`, plus the `bs-booster-cache` folder, all ship with Publisher. | Check each after switching. Deactivate those with no remaining use, once item 7 confirms they leave no shortcodes behind. |
| 10 | **Legacy front-end plugins** | `featured-content-gallery` (MooTools), `front-slider`, `cardoza-wordpress-poll`, `cool-poll`, `responsive-twentyten` and the Font Awesome CDN load on every page today. | Not needed by this theme. Audit and deactivate whatever is unused; this is the biggest performance gain available. |
| 11 | **Demo pages** | About 30 published Publisher/TieLabs demo pages (sliders, “Shop/Cart/Checkout”, “Sample Page”…). | Editorial decision; the theme changes nothing. Unpublishing them needs approval. |
| 12 | **Privacy policy** | No privacy policy page was found. | Recommended (legal text must come from the publisher, not the theme). |
| 13 | **Date format** | WordPress's default `F j, Y` reads «أكتوبر 19, 2026». | The theme already displays `j F Y` («19 أكتوبر 2026») when the default is in use. Set a custom format in Settings → General to override this. |

### Finding legacy shortcodes (staging database)

```sql
-- Count posts containing shortcode-like tags, grouped by tag name (MySQL 8+).
SELECT REGEXP_SUBSTR(post_content, '\\[[a-z_-]+') AS tag, COUNT(*) AS posts
FROM wp_posts
WHERE post_status = 'publish' AND post_type IN ('post','page')
  AND post_content REGEXP '\\[[a-z_-]+[ \\]]'
GROUP BY tag ORDER BY posts DESC;

-- Visual Composer / WPBakery content specifically
SELECT ID, post_type, post_title FROM wp_posts
WHERE post_status = 'publish' AND (post_content LIKE '%[vc_%' OR post_content LIKE '%vcv-%');
```

(Adjust the `wp_` prefix if the site uses another one.) The same check is possible with WP-CLI:
`wp db query "…"`.

## Suggested primary menu

Based on the existing categories and their post counts:

الرئيسية · أخبار وقضايا (6 439) · جمال واناقة (270) · أسرة ومجتمع (1 116) · نجوم و مشاهير (1 420) ·
آداب و فنون (602) · رياضة و رشاقة (952) · شهيوات زهرة (150) · مقالات الرأي (42) · **المزيد ▾**
(حياتك الجنسية · فن و ديكور · تحت العشرين · دردشة · زهرة TV) · En français ▾ (its 7 sub-sections)

The live menu also has a «هي و هو» item pointing to `#` (its category is empty except for
sub-categories). Consider removing it or pointing it at the category.

## Homepage sections are tied to category slugs

The homepage template references categories by slug, so it works on any copy of the site:
`جمال-واناقة`, `أسرة-ومجتمع`, `نجوم-و-مشاهير`, `آداب-و-فنون`, `رياضة-و-رشاقة`, `شهيوات-زهرة`,
`مقالات-الرأي`, `أخبار-وقضايا`, `en-francais`. If a slug is renamed later, that section quietly
disappears from the site and shows a notice in the editor. Re-select the category in the block
settings to fix it.

## Go-live checklist

1. Copy production to staging (files + database).
2. Upload and **Live Preview** the theme; run the shortcode queries; note widgets and ad placements.
3. Activate on staging; complete items 1–6.
4. Check: homepage, 3–4 articles (Arabic, French, one with a gallery/embeds), a category, the tag
   `/tag/الرياض/`, search, 404, a paginated archive, on phone and desktop.
5. Check AIOSEO output is unchanged (view-source: one canonical, AIOSEO's schema, no duplicate Open Graph).
6. **Only after approval**, activate on production. Rollback is reactivating Publisher; nothing needs undoing.
