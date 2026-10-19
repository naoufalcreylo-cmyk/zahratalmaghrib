# Zahrat Al Maghrib — WordPress theme

Arabic-first editorial block theme for **زهرة المغرب — مجلة إلكترونية نسائية متخصصة**.

| Path | What it is |
|---|---|
| `zahrat-almaghrib/` | Theme source (this is what gets installed) |
| `dist/zahrat-almaghrib.zip` | Installable package |
| `screenshots/` | Desktop / tablet / mobile captures from the local test site |
| `MIGRATION.md` | What changes when switching from Publisher, and what to do before go-live |
| `VALIDATION.md` | What was tested, results, and known limitations |
| `dev/` | **Development only**: WordPress Playground blueprint, sample-content seed, helpers |
| `tools/` | Translation extractor and Arabic `.po/.mo/.json` builder |

---

## 1. Installation

> Install on a **staging copy first**. Do not activate on the live site until the checklist in
> `MIGRATION.md` is done and the result is approved.

1. *Appearance → Themes → Add New → Upload Theme* → choose `dist/zahrat-almaghrib.zip` → **Install**.
   (Or copy the `zahrat-almaghrib/` folder into `wp-content/themes/`.)
2. Check the staging site with **Appearance → Themes → Live Preview** before activating.
3. **Activate**. Activation changes no posts, pages, terms, menus, URLs or options.
4. Do the five setup steps in §2 below (logo, menu, footer links, Editors' selection, ads).

Requirements: WordPress 6.6+ (built and tested against 7.1), PHP 7.4+. No plugins required.
Site language should stay **العربية** (Settings → General) so the interface shows in Arabic.

---

## 2. Editing guide

All editing happens in **Appearance → Editor** (the Site Editor) unless noted.

### Logo
The theme shows the **Site Logo**. Publisher kept its logo in its own options, so after switching
you need to set it once:

*Editor → Patterns → Header* → select the logo / wordmark block (**الشعار أو الاسم**) → sidebar →
**اختيار الشعار** → choose the existing `logozahra.png` from the Media Library. This saves WordPress's
site-wide *Site Logo* setting, the same one the core Site Logo block uses.

The masthead and mobile bar are deep burgundy precisely because the existing logo is white with a
pink lotus. While no logo is set, a **temporary text wordmark** “زهرة المغرب” is shown. It is a
placeholder, not a new logo.

### Colours and fonts
*Editor → Styles*. The palette (ivory, sand, charcoal, burgundy, gold…) and the three font
families are defined in `theme.json`, so changes made in Styles apply site-wide and survive theme
updates. Use **“Gold (text-safe)”** for gold text. **“Muted gold (decorative)”** is only for lines
and ornaments, because it does not have enough contrast for text.

### Main menu
*Editor → Navigation*. The header uses the core Navigation block.
- To reuse the current Publisher menu: open the Navigation block's menu picker → **Import classic menus**.
- A suggested structure (about 10 top-level items) is in `MIGRATION.md`. Put rarer sections under a **المزيد** sub-menu.
- On phones and tablets (under 1024 px) the menu collapses into an accessible overlay that reads right to left.

### Footer links (institutional pages)
*Appearance → Menus* → create a menu (e.g. «روابط التذييل») with **إتصل بنا**, **للإشهار** and
**فريق العمل** → *Manage Locations* → assign it to **Footer links**. Until one is assigned, that column hides itself.

### Social links
*Editor → Patterns → Header / Footer* → the empty **Social Icons** blocks → add the magazine's real
accounts. They stay hidden while empty. (The current site's icons point at BetterStudio's demo
accounts, so they were not carried over.)

### Homepage sections
*Editor → Templates → Blog Home*. Each section is a block. Select it and use the sidebar
(Arabic labels):

| Block | Settings |
|---|---|
| **المواضيع الرئيسية** (Lead stories) | Pick stories by hand (first = lead); otherwise **sticky posts**, then latest posts fill it. 2–4 supporting stories. Optional category limit. |
| **آخر المقالات** (Latest articles) | Title, excerpts on/off. Uses real `/page/2/` pagination. 9 per page (filter `zam_home_per_page`). |
| **قسم من المجلة** (Category section) | Category, title, layout (موضوع بارز + قائمة / شبكة / شبكة صور عمودية / قائمة عمودية / نص فقط), number of stories, background (none / sand / dark), “More” link. |
| **مختارات التحرير** (Editors' selection) | Search and pick 3, 4, 6 or 8 stories, then reorder. **Hidden on the site until stories are picked.** |
| **مساحة إشهارية** (Advertising slot) | See below. |

- **No duplicates.** A story already shown higher on the page is skipped further down. Hand-picked editors' choices always show.
- **Making a story the lead** without opening the Site Editor: tick **Stick to the top of the blog** in the post's sidebar.
- **Adding sections.** Use the inserter's *Zahrat Al Maghrib* pattern category: feature section, dark portrait band, opinion band, three columns, editors' selection, ad leaderboard.
- **Missing categories.** A section whose category doesn't exist renders nothing on the site and shows a notice in the editor.

### Advertising
Each **مساحة إشهارية** block contains a *Custom HTML* block: paste the ad code (AdSense etc.) there.
- The slot is labelled **«إشهار»** and reserves its height to avoid layout shift: 90 px leaderboard on desktop, 100 px on mobile, 250 px for rectangle/in-article.
- **Empty slots render nothing.**
- Only users allowed to post unfiltered HTML (administrators/editors) can add script tags.
- Pre-placed slots: under the lead stories, before the French section, after the article body, and at the top of archives.
- Google Site Kit / AdSense Auto ads keep working independently of these slots.

### French articles (LTR)
Posts in **En français** or any of its sub-categories render title, standfirst, body and table of
contents left-to-right in Noto Serif, while the header, breadcrumbs and byline stay Arabic.

Detection order:
1. Custom field **`zam_lang`** set to `fr` or `ar` (*Post editor → ⋮ → Preferences → Panels → Custom fields*).
2. The French category tree.
3. A title written only in Latin script.

For French passages inside an Arabic article, use the paragraph block's text-direction control or `<span lang="fr" dir="ltr">`.

### Article page
- **Standfirst:** appears only when the post has a hand-written **Excerpt**.
- **Updated date:** shown when a post was modified a day or more after publication.
- **Table of contents:** appears automatically when an article has three or more level-2 headings. To remove it for one post, choose the template **“مقال بدون فهرس”** in the post sidebar.
- **Author card:** shown when the author's profile has a *Biographical Info*.
- **Image credit:** comes from the image's IPTC “credit” metadata, or the `zam_image_credit` filter. The caption comes from the Media Library caption.

### Sticky or static header
*Editor → Patterns → Header*: swap the template part for **“الترويسة (ثابتة)”** if you don't want the
navigation bar to stick while scrolling.

---

## 3. Local development (optional)

`dev/` contains a [WordPress Playground](https://wordpress.github.io/wordpress-playground/) setup with
**clearly labelled Arabic/French sample content**. It is for development only and never connects to the
live site.

```bash
npx -y @wp-playground/cli@latest server --port=9400 --mount=./zahrat-almaghrib:/wordpress/wp-content/themes/zahrat-almaghrib --mount=./dev:/wordpress/zam-dev --mount=./dev/mu-zam-dev.php:/wordpress/wp-content/mu-plugins/zam-dev.php --blueprint=./dev/blueprint.json
```

Then open `http://127.0.0.1:9400`. Use the `127.0.0.1` address, not `localhost`, because WordPress's
script modules are served from that origin. Seeding takes 1–2 minutes because image thumbnails are
generated in WebAssembly. `?zam_nologo=1` previews the wordmark fallback.

### Rebuilding translations
After changing any user-facing string:

```bash
node tools/build-i18n.mjs zahrat-almaghrib
```

This extracts strings from PHP/JS/block.json/theme.json/pattern headers into
`languages/zahrat-almaghrib.pot`. It then compiles `ar.po`, `ar.mo` and the editor JSON from
`tools/ar.mjs`, and lists any string still missing an Arabic translation.

### Packaging
```bash
tar -a -cf dist/zahrat-almaghrib.zip zahrat-almaghrib
```
