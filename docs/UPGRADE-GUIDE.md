# Las10s / Perfect 10 upgrade — review and staging guide

Date: **2026-10-17 (Asia/Calcutta)**. Branch: **arena/5cd1cd5f-las10**. Theme: **1.1.1**.

This is an upgrade of the supplied classic theme, **not a replacement design**. Do not treat this as a verified production migration, fully editable homepage, or completed source-gallery import.

The 1.1.1 responsive follow-up adds 97vw shells, bounded viewport-based typography/spacing, and fit-driven header navigation. See [RESPONSIVE-LAYOUT.md](RESPONSIVE-LAYOUT.md) for implementation, browser evidence and rollback details.

## 1. Audit and backups

Inspected the tracked source, original `perfect10-fixed-v5.zip`, both WXR files, branch/status, templates, CPTs, metadata, CSS/JS, shortcodes, widgets, SEO, forms, REST queries and original sample importer **before editing**. The original ZIP contains 73 files (81 entries including directories). The extracted source initially differed only in `inc/setup.php` and `assets/css/main.css`. Neither copy contained a chatbot or substantive Elementor integration beyond the existing support declaration.

Pre-edit source/documentation backup: `backups/las10-before-upgrade.tar.gz`. The original ZIP and WXR files remain unchanged. The Git base commit also preserves the original checkout. These are **file backups only**, not a production database/uploads backup. Never restore the archive over a live installation without a rollback plan.

### Existing structure retained

- Public CPTs: `p10_profile`, `p10_review`, `p10_package`. Private enquiries: `p10_enquiry` (now administrator-only, including existing enquiry records).
- Profile taxonomies: `p10_county`, `p10_city`, `p10_category`, `p10_nationality`; county/city custom nested rewrite/permalink rules retained.
- Classic templates: homepage, page/single, profile/review archives, county/city/category/nationality taxonomy archives, search, 404, comments, sidebar, header/footer.
- Original page templates: blank, booking, cities, directory, packages, register. Existing booking/listing/concierge form implementations remain available; only the floating launcher was replaced.
- Existing shortcodes and WordPress sidebar widgets remain; see `inc/shortcodes.php` and `inc/widgets.php`. The new Elementor components are separately registered widgets, not whole-page shortcode/HTML wrappers.
- `inc/meta.php` owns profile fields (rates, currency, availability, verification, statistics, contacts, services, galleries), review/package metadata, term covers/blurbs and county relationships.
- Customizer controls still own the PHP sections, branding, design tokens, menus, header/footer, hero, contact and age gate. Original `main.js` still handles menus, filters, galleries, age gate, forms and reveals. CSS design system and section order were not replaced.
- Removed the unnecessary early inclusion of the location dataset: it already loads lazily. This fixes an observed WordPress 6.8 early translation-loading notice without changing the dataset.

### Export findings (rechecked, not assumed)

See [SOURCE-INVENTORY.md](SOURCE-INVENTORY.md) for **all 44 profile rows and all 240 attachment outcomes**; JSON package: `perfect10/demo/source-manifest.json`.

- Large WXR: 284 items = **42 published profiles, 2 draft profiles, 240 attachments**. Small WXR: **zero items**.
- **43/44 bodies empty**; the one supplied nonempty published body is retained after WordPress HTML sanitization. No biographies were authored to fill gaps.
- No standalone page items, term definitions/descriptions, location/category SEO copy or Elementor page-layout export. Term relationships must be reconstructed from verified public metadata, not claimed as standalone imported pages.
- No source upload/media archive or verified media-reuse authorization. Attachment URLs are references, not image binaries. Source images were not fetched, hotlinked or bundled. Most attachment paths are in `_bsr_external_attached_file`, not `_wp_attached_file`; remaining paths are derived from the explicit attachment URL's `/wp-content/uploads/` portion, without fetching it.
- Existing theme images/placeholders and its original screenshot are not source-profile galleries. Synthetic test images are test fixtures only and are **not** in the theme ZIP.
- Supplied XML/source text remains user-supplied material, not a rights grant. The minimized manifest is a review/import artifact, not approval for redistribution/publication. Obtain documented content/media permission before deploying or distributing it; the admin importer requires explicit attestation. Internal notes and verification review data are omitted from the manifest and importer allowlist.

## 2. Install and safe import

1. Back up **the target database, theme, uploads and configuration**; verify recovery on staging. Record existing page IDs/slugs, active templates, menus, Customizer options, SEO plugin and rewrite settings.
2. Install `deliverables/perfect10-las10-upgrade-1.1.1.zip` (directory `perfect10/`) on **staging**, not directly on production. No core/Elementor plugin files are included or modified.
3. Review the inventory, missing fields, conflicting metadata and rights basis. Do **not** run the original **Perfect 10 → Setup & Import** sample-site importer for the source migration. It is a separate legacy workflow that can configure sample homepage/menu settings. Its stock-media fetching is now off by default; the existing `p10_demo_fetch_remote` filter can opt in after license review.
4. Use **Appearance → Authorized Source Import** as an administrator. Confirm reuse authorization. Each click processes **5 profiles** or **10 assets**. Continue until all cursors are processed (9 profile batches + 24 media batches). The action uses capability checks and an admin nonce. A lock prevents concurrent batches; interrupted locks expire after five minutes (a retry clears a stale lock, then the next request can acquire it).
5. Drafts are always excluded from public import. Importing them as drafts would require a separate explicit change/request; there is no accidental “publish drafts” toggle.
6. Slug collisions with unrelated profiles are reported for manual review. Existing source-keyed complete records are left unchanged. Interrupted partially mapped records can resume by adding missing fields, not overwriting editor changes. Terms are added/associated without replacing existing descriptions or changing an existing city's county. A relationship conflict is reported, not silently overwritten.
7. Source IDs are preserved in `_p10_source_key` (`lasperfect10s-2026-10-16:<ID>`), **not forced into destination database IDs**. Source title/slug/status/date, compatible SEO fields and available public metadata are mapped. Original source fields use a protected metadata key; no admin notes are copied.
8. `country` maps to county and `city` to city. Legacy serialized category names/slugs map to `p10_category`; `profile_type` is used only if no legacy category is present. County/city names use the supplied slugs if no verified display name is provided; no marketing description is generated. Explicit feet/inches convert to centimetres. Explicit body type, cup size, tattoo text and service/language values are mapped where supplied; service codes are preserved verbatim, not expanded into invented descriptions. Supplied nationality names map to the nationality taxonomy. Measurements, weight, source hours/availability, smoking/drinking text and location context are retained as protected source fields when supplied; ambiguous unit/enum/live-status mappings are deliberately not guessed. Weight is not converted because units are unspecified. Unknown availability is labelled **not verified**, not “available”; no verification/premium flags are invented.
9. Exported Yoast title/description/focus-keyword metadata is retained. Without an SEO plugin, plain exported titles and descriptions are used for source profiles; plugin-specific title variables are left to the SEO plugin. Canonical/rewrite logic remains the original theme's logic. All 42 published destination path strings matched their exported source paths in the disposable import; actual production HTTP/redirect/canonical validation is still required.
10. Download the **JSON mapping/outcome report** from the importer. It includes every processed source record, destination ID/URL, missing fields, conflicts and media outcomes. “Processed” does **not** mean every asset succeeded.

### Local media import and retries

No source downloads are implemented: this avoids hotlinking, SSRF and unauthorized remote copying. There are no remote-download success claims or transient-network retry claims. Obtain authorized binaries before media import.

- An administrator stages verified files at `WP_CONTENT_DIR/p10-source-media/<manifest path>` (usually `YYYY/MM/filename.ext`). Extract archives securely outside the webroot first; never trust archive paths/symlinks. Restrict access to the staging directory at the webserver. The importer resolves real paths and rejects files escaping that directory.
- Accepts valid WordPress-supported images only; limits are **20 MB and 40 megapixels**. Uses WordPress sideloading and attachment metadata generation, not external image URLs. Stores stable source keys and SHA-256 checksums; identical files share a Media Library record.
- Reports missing, inaccessible, invalid/oversized, failed, unused/draft-only, imported, duplicate-source and duplicate-checksum outcomes. Copy/storage failures can be retried after remediation. **Retry/reconcile** restarts cursors safely; existing complete posts stay unchanged. No automatic download retries are necessary because no network downloads occur.
- Galleries link only successfully imported local attachment IDs, in source order, deduplicated. Partial galleries report expected versus linked IDs. Profiles with no files retain explicitly labelled placeholders. An editor-changed gallery is left alone and reported as a conflict.
- Expected with the supplied files: **zero real source media imports**. In the disposable no-binaries run, 196 attachment records were missing and 44 had no relationship to an imported public profile. All 240 outcomes appear in `DISPOSABLE-IMPORT-REPORT.json`. This is evidence from a test site, not a production destination map.
- The source package does not invent featured/premium status, so imported records will not automatically appear in homepage “featured/premium” sections. They are available in the directory/taxonomy listings. Configure editorial placement deliberately on staging if desired.
- After review, remove the **staging files only**, using your host/file manager. Do not delete Media Library files, original XML, importer IDs/checksums or unrelated content. There is no destructive “reset demo” button. Rollback uses the pre-import backup or a manually reviewed source-ID selection, not a bulk unrelated-content deletion.

## 3. Elementor usage — Free versus Pro

### Elementor Free (implemented, with limited test scope)

- Supports ordinary pages and the existing public profile/review/package CPT editors. Check **Elementor → Settings → General → Post Types** if the editor button is not enabled for your existing installation; no plugin settings are overwritten.
- A singular document actually built with Elementor uses the full-width theme content template, retaining header/footer and avoiding the classic 78-character prose wrapper/title. IDs, URLs, metadata and media are not migrated or recreated.
- Select **P10 Full Width (header & footer)** where appropriate. Native Elementor Canvas and Header/Footer templates are respected but never forced. Without a saved Elementor document or with Elementor disabled, original PHP templates remain available; selecting the P10 template without a document also falls back to classic rendering.
- Native text/image/button/gallery/layout widgets should be used for ordinary content. The dynamic widgets are:
  - **P10 Profile Listing**: published/password-free data, real county/city/category selectors, current taxonomy context, max 48 items, responsive columns/gaps and title color. These are editor/query filters; the widget does **not** claim an additional AJAX visitor-filter UI. The original directory template retains its visitor filtering.
  - **P10 Current Profile Facts**: an explicit selectable public-field allowlist, text color, current published profile only. Choose an actual published profile as preview context, rather than a private/draft profile.
  - **P10 Current Profile Gallery**: current published profile's linked Media Library images, responsive columns/gaps, image size, optional full-image links; no remote source galleries.
- Controls were registered and all three widgets rendered actual data in Elementor Free's PHP runtime. Native heading content rendered without a prose wrapper; one classic header/footer was verified. **Editor UI opening and packaged frontend assets were not verified**, so these are not claims of full editor compatibility across releases.

### Homepage and existing special sections

The current PHP homepage remains hero → ticker → static page-content insertion → search → featured → premium → profile types → destinations → call band → about → reviews → advertisers. Existing Customizer-controlled sections have **not** been converted into opaque HTML widgets or blanked out.

On the default PHP homepage, saved Elementor content appears only at the existing content insertion, without its prose wrapper. Selecting P10 Full Width explicitly opts into a full document replacement **only when an Elementor document exists**. Do not select it on production expecting the hard-coded sections to migrate automatically.

For full-section editing, obtain a **current target database export** (pages, `_elementor_data`, templates, theme mods, menus, widgets, term/meta relationships and Media Library mapping). The supplied profile WXR cannot represent the live homepage layout. Clone to staging, build native sections and these dynamic widgets beside the original, compare breakpoints/interactions, then assign the document to the **same page ID** only after approval. Keep a database backup and revert template/document metadata to roll back. No automated live-page conversion was run.

### Elementor Pro / Theme Builder

Header, footer, single and archive locations are registered; optional search/404 locations are supported by the bridge. Search also tries native `archive`, and 404 tries native `single`, since Pro versions commonly implement their conditions through those locations. The code calls Elementor's **conditional location renderer**, never manually selects a template ID. It renders the original PHP template if no location matches, avoiding duplicate page content/header/footer.

**Pro Theme Builder and Pro Dynamic Tags are not Free features.** Pro was unavailable. A mocked renderer contract test verifies fallback/replacement routing, not real Pro display conditions. Test include/exclude conditions, search, 404, archives and header/footer overrides against your actual licensed version before deployment.

## 4. Chatbot/provider/privacy setup

The floating message launcher is now an accessible chatbot icon and native dialog. Existing non-floating forms are retained. The dialog has text entry, source links, busy/error feedback, reset, Escape/focus return, responsive layout, browser speech recognition, mute/stop/repeat controls and text fallback. It opens with **no contact form**. Speech output is initially muted. Voice recognition does not auto-send: the visitor reviews text before pressing Send.

### Knowledge and providers

**Appearance → Website Assistant**:

- Default **Local** mode is extractive WordPress search: no AI account, API calls, or per-message provider charges. Replies contain real published extracts and links, not generated profile facts. It is stateless; there is no stored conversation memory.
- Published/password-free profiles and nonempty public profile terms are eligible. Only explicitly approved **published page IDs** are included for FAQs, policies and other public pages. Blank approved-page IDs excludes pages, not profiles. Drafts, enquiries, arbitrary metadata and private records are never knowledge. Shortcodes are stripped, not executed.
- Retrieval is bounded lexical search (up to six conversational token fallbacks plus term lookup), not an exhaustive semantic index. It can miss indirect phrasing. It says information is unavailable rather than fabricating an answer. Current prices, availability and contact details are not confirmed in chat; visitors get public page links.
- Optional **OpenAI relevance ranking** sends a bounded redacted question and public extracts to the fixed server-side OpenAI Chat Completions endpoint. Set the model in the admin screen and define `P10_CHAT_API_KEY` in the host's environment-backed `wp-config.php`/secret manager, **never** theme files, frontend JS, Git or chat. The setting does not provide a frontend API-key field.
- Provider output is restricted to matching source identifiers: invented IDs, prose, failures and invalid JSON fall back to local extracts. This intentionally does **not** implement free-form AI-generated answers or send a transcript. Emails/phone-like numeric strings are redacted before provider calls; redaction is not a guarantee of anonymization. Ask visitors not to submit sensitive details.
- Cost: local mode has zero provider fees. OpenAI mode makes at most one call per answered request with candidate sources, up to 12 bounded source extracts, a 600-character question, and a 150-token response limit. Estimate cost as `(input tokens × current input price + output tokens × current output price) / 1,000,000`; typical input is a few thousand tokens. Verify the selected model's current pricing, JSON support, content terms and spending caps. No price/availability guarantee or provider-wide billing cap is enforced by this theme.
- Live OpenAI calls were not tested (no provider credentials, and this environment's outbound allowlist excludes that API host). Only the request/redaction/output-validation/failure contract was tested using mocked HTTP.

### Voice limitations

Speech recognition depends on the browser, microphone permission and usually HTTPS. The browser may send audio to **its own** external speech service; this is disclosed before the microphone control. The theme sends **no audio** to OpenAI. Speech synthesis uses the browser/device service, whose handling may vary. Real microphone, recognition-provider behavior and audible synthesis need testing on supported browsers/devices. These features degrade to text when unavailable.

### Public endpoint controls

`POST /wp-json/p10/v1/assistant` and `/assistant/followup` (or WordPress's `?rest_route=` equivalent) validate the public REST nonce and reject mismatched supplied origins, unexpected types and excessive lengths. API keys remain server-side. Frontend messages use text nodes, not injected response HTML. Responses are `no-store`.

Rate limits: 20 requests/minute per HMAC-hashed direct remote address, plus 120/minute site-wide. Chat text/IP addresses are not stored as transcripts; transient counters contain no raw IP. Transient increments are **not atomic across workers** and direct IPs can represent a shared reverse proxy. Add host/CDN/WAF rate limits and an appropriate trusted-proxy policy for public deployment. Nonces/Origin checks are CSRF friction, not bot authentication. Exclude or refresh cached nonce-bearing pages before nonce expiry; an expired chat nonce asks the visitor to reload. Access/server/provider logs have their own retention, outside this theme's no-transcript default.

### Consent and follow-up administration

Follow-up is **disabled by default** and also requires a published WordPress privacy policy. Review/approve that policy before enabling it. After a normal response, the visitor may choose “Request follow-up.” The assistant first explains the optional collection and asks consent to continue; then asks for name, **email OR phone**, related profile/location context, and short enquiry. A final explicit **Consent and save enquiry** button performs persistence. Closing/resetting clears unsaved collection; voice collection is disabled during follow-up. No transcript is saved.

The enquiry is private, accessible only with `manage_options`, in the existing **Enquiries** dashboard. Search is the standard WordPress title/body search (name and enquiry text); status filtering, existing notes/status controls and a consent/notification detail box are provided. Arbitrary email/phone metadata is not added to public search. Consent includes boolean status, UTC timestamp and notice version `2026-10-17-v1`. Related profile IDs, if supplied by an API client, must be public; the conversational UI saves the visitor's profile/location text as context rather than guessing a profile ID.

Notifications contain an admin review link, **not contact details/transcript**. `wp_mail()` true is recorded only as **transport_accepted_delivery_unverified**; false is **transport_failed**. A saved enquiry is never described as delivered email. Configure/test SMTP and actual mailbox delivery separately.

Before production, document controller/contact, purpose, recipients/provider subprocessors, lawful basis, voice-service processing, user rights, international transfers, retention period and deletion process. Choose and enforce a retention schedule (for example, review/delete unneeded enquiries after an approved 30-day period); **no automatic retention deletion is implemented**. Administrators can permanently delete records through WordPress. Email-keyed WordPress privacy exporter/eraser callbacks cover these new follow-up records; phone-only requests require administrator identity verification and manual deletion. Delete the record from backups/mail infrastructure according to your policy as well. Do not switch themes expecting WordPress post/meta records to disappear.

## 5. Evidence, limitations and acceptance checklist

See [TESTING.md](TESTING.md) and `test-evidence/`. Implemented and tested claims are deliberately narrower than production acceptance:

- PHP parsing, JS syntax, CSS parsing and WordPress-Core coding standard checks for new integration PHP.
- Disposable WordPress/SQLite import/re-import with all 42 published profiles and draft handling, URL path mappings and missing-media outcomes.
- Synthetic local media sideload/checksum/order/editor-preservation tests, **not source-photo success**.
- Elementor Free PHP registration, actual native/dynamic rendering and classic fallbacks; renderer contract simulation, **not editor UI or real Pro conditions**.
- Chat REST permissions, private-content exclusion, validation, consent, mail-failure reporting, privacy erasure, rate limiting; mocked provider contract.
- Chromium chatbot smoke checks at 1440×1000, 820×1180 and 390×844, live local REST results, optional/cancellable follow-up consent, reset, Escape/focus return and no JS page exceptions with Elementor disabled.

Still required on the actual staging site: install packaged Elementor Free and test editor opening/saving plus all controls; licensed Pro condition matrix; actual production MySQL/MariaDB and PHP versions; real authorized media; complete homepage migration/database export; source-to-target HTTP/canonical/SEO comparison; browser/device/manual accessibility audit; menus/forms/gallery/filter regression; fonts/imagery parity; actual provider, voice and notification delivery; privacy/rights approval; edge-rate-limit/concurrency/load testing. Supplied theme Google Fonts could not be fetched through the sandbox allowlist, so screenshot font parity is not verified.

## 6. Deliverables / modified-file summary

- Updated existing theme source: `perfect10/`; installable ZIP: `deliverables/perfect10-las10-upgrade-1.1.1.zip`.
- New modules: `inc/elementor.php`, four `inc/elementor/class-p10-elementor-*.php` widgets/base; full-width and internal conditional bridge templates; `inc/source-import.php`; minimized source manifest; `inc/chatbot.php`; scoped chat CSS/JS.
- Existing edits: bootstrap/version/lazy location load; header/footer conditional locations; homepage content insertion; unknown-availability schema/label; administrator-only enquiry capabilities; source SEO preservation; labelled missing-photo placeholders; small scoped editor/grid styles; floating concierge markup; legacy remote-demo-images default off.
- No generic theme, new generated homepage, production content deletion, core/plugin edits, API keys, external source photos, or theme runtime dependencies were added.
- Version-controlled package builder `scripts/build-demo.py`, tests, inventory, disposable mapping report, setup guide, test evidence and pre-edit backup. Original audit retained as historical context; this guide supersedes its missing-feature/environment statements.
- Everything is on the active session branch. Git diff/commit is the review surface; no change of branch is needed.
