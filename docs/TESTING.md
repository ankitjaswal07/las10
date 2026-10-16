# Test evidence and reproduction

Date: 2026-10-17 Asia/Calcutta. These are scoped tests, **not production acceptance**.

## Environment

Initially no native PHP, WordPress, WP-CLI, database server or browser was installed. An isolated, ignored `.local/` test environment was provisioned:

- PHP **8.2.33** via `@php-wasm/cli` **3.1.58**. This is real PHP running as WebAssembly, not a mocked WordPress API. The CLI can return process exit code 0 even after a PHP fatal; logs were inspected for `FAIL`, `Fatal error`, warnings and final completion markers instead of relying on that exit code.
- WordPress **6.8.3**, pinned GitHub source archive; WordPress SQLite database integration `develop` fetched for the disposable test only. This does **not** test MySQL/MariaDB, production webserver behavior or every supported WordPress/PHP version.
- Elementor Free **4.0.8**, GitHub source tag. The source archive lacks built editor assets/vendor bundles. PHP registration and server-side rendering worked; opening/saving the packaged editor in-browser was **not** tested. The plugin source emits an unrelated deprecated false-to-array conversion notice in `includes/api.php` when its remote API is unavailable (recorded in `elementor.txt`). No plugin files were edited to hide it.
- No Elementor Pro installation/license. `theme-builder-contract.php` simulates the conditional renderer return contract; it does **not** verify Pro's real display-condition engine.
- Node **22.22.3**, PostCSS **8.5.30**, Playwright **1.64.1**, npm-packaged Chromium **153.0.0**. The latest PHP-WASM packages declare newer Node engines; npm warned, but the recorded PHP execution worked. Prefer the packages' supported Node version when reproducing.
- PHP_CodeSniffer **3.13.5**, WordPress Coding Standards **3.2.0**, PHPCSUtils **1.1.2**, PHPCSExtra **1.4.1**, installed only under `.local/`.

No dependencies/test database/synthetic profile images are shipped inside the theme. The running disposable test server is stopped after QA; there is no deployed production site.

## Recorded results

| Evidence | Scope |
|---|---|
| `test-evidence/static.txt` | PHP's parser accepted all 68 theme PHP files; Node accepted all 4 scripts; PostCSS parsed all 6 stylesheets; WordPress-Core coding standard clean for the new integration PHP and replacement concierge template. Not an all-theme WordPress-Docs/Extra certification. |
| `test-evidence/runtime.txt` | Actual WP/SQLite 42-profile import and rerun; both drafts excluded; unrelated page unchanged; relationships present; private notes omitted; unknown availability; nonce/origin/type/length checks; administrator-only enquiries/import; no private/draft/password knowledge; consent/notice checks; failed-mail status; email-keyed erasure; HTTP 429 rate limit. |
| `DISPOSABLE-IMPORT-REPORT.json` | All 44 source-profile outcomes and all 240 attachment outcomes, with source/destination mappings. Before synthetic fixtures: zero source images imported; 196 missing binary outcomes, 44 unused/no imported public-profile relationship outcomes. All 42 published source/destination URL path strings match; this is not a production HTTP/canonical crawl. |
| `test-evidence/media.txt` | Three tiny colored synthetic image files; two identical binaries deduplicated; local WordPress Media Library sideloading, source gallery order, unrelated slug and editor-gallery protection. No source photos or gallery completeness verification. |
| `test-evidence/provider.txt` | Mocked provider HTTP: email redaction, unknown/injected IDs rejected, provider-authored prose never shown, provider-error local fallback. No live provider call/cost/accuracy test. |
| `test-evidence/elementor.txt` | Real Free source runtime: support declarations, all three widget controls registered, native heading rendering, no prose wrapper, one classic header/footer, classic page fallback, published listing/facts/gallery widget output. Editor JS/assets not tested. |
| `test-evidence/theme-builder-contract.txt` | Mock renderer: no-match PHP fallback; matched replacement; no duplicate header; dedicated 404 to native single routing. Not real Pro conditions. |
| `test-evidence/browser.txt` | Live WordPress HTTP/REST-backed chatbot in Chromium: desktop 1440×1000, tablet 820×1180, mobile 390×844; fits viewport; no contact form at opening; public profile response links; optional follow-up consent cancellation; reset; Escape; focus return; no JS page exceptions with Elementor disabled. Browser web security was not disabled. |

Screenshots of the assistant were inspected locally; they are not baseline design comparisons. Theme Google Fonts are outside the allowed network hosts, so exact font parity was not established. Browser tests do not assert that every network resource succeeded, or that all legacy menus/forms/filter/gallery behavior is regression-free.

## Reproduction (disposable site only)

The test PHP scripts require both variables and refuse to run without them:

```sh
export P10_WP_ROOT=/absolute/path/to/DISPOSABLE/wordpress
export P10_DISPOSABLE_TEST=1
php tests/syntax.php
php tests/runtime.php
# Before media.php, stage the synthetic fixtures, never source photos without rights:
python tests/make-media.py "$P10_WP_ROOT"
php tests/media.php
php tests/provider.php
# Activate the pinned Free plugin in this disposable install first:
php tests/elementor.php
# Deactivate Free/Pro before the mock-renderer contract test:
php tests/theme-builder-contract.php
```

`runtime.php` expects a **fresh** disposable database with administrator ID 1 and this theme active, no source records/media staging yet, configured at `http://example.test`. It deliberately creates test users/pages/records/options and should never be pointed at production. The report is captured by `media.php` **before** its synthetic assets are sideloaded. Set up a local WordPress install with your usual tooling or the official SQLite drop-in, activate this theme, and disable real outbound mail/network during tests. Source PHP tests do not require a native MySQL service.

The actual sandbox commands used the PHP-WASM CLI with `PHP=8.2`, substituting it for `php` above. That runtime and the source archives live in ignored `.local/` and must be reinstalled if not present in a fresh checkout. For JavaScript/CSS checks:

```sh
npm install --prefix .local/qa postcss@8.5.30 @playwright/test@1.64.1 @sparticuz/chromium@153.0.0
node tests/static.cjs
# Start the disposable WordPress server separately, bound to 0.0.0.0,
# with WP_HOME/WP_SITEURL accepting its actual browser/proxy origin.
P10_TEST_URL=http://your-disposable-site node tests/browser.cjs
```

`QA_DEPS` can override the isolated npm dependency directory. On this minimal sandbox, Chromium's supplied `al2023.tar.br` libraries had to be extracted and their `lib/` directory placed in `LD_LIBRARY_PATH`; no system libraries were changed. Browser capture paths are ignored `.local/` files. The script uses its server URL only for testing; the theme itself uses WordPress-generated/same-origin URLs, never hard-coded localhost.

Coding standard command used the new module/template paths with `--standard=WordPress-Core --report=summary`. `git diff --check` also passed. No claim of full-theme PHPCS security or documentation-standard conformance is made.

## Still unverified / release gate

- Packaged Elementor editor opening, save/reload, all controls at breakpoints, native Canvas/Full Width and third-party plugin interoperability.
- Actual licensed Pro include/exclude conditions for all locations, search, 404, header and footer.
- A real authorized uploads archive, per-image provenance, full source-gallery ordering/coverage against those binaries.
- Current production database/page export and preservation-equivalent whole-homepage migration. No source layout data was supplied.
- Production MySQL/MariaDB; host mail/SMTP acceptance **and actual delivery**; actual provider billing/errors; spoken response/recognition on real devices and services.
- Full manual keyboard/screen-reader/accessibility evaluation, full design/imagery/font comparison, all legacy menus/forms/gallery/filter regressions, performance/load/atomic rate limiting and CDN-cache behavior.
- Live source/destination HTTP status, redirects and canonical/SEO comparison. Only mapped path strings were compared here.
- Legal content/media permission, provider terms and privacy/retention review. No automatic deletion policy is implemented.
