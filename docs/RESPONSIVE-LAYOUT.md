# 97vw responsive layout update — 1.1.1

Work remains on `arena/5cd1cd5f-las10`. **No PR created.** This adjusts the existing theme; no pages, menus, profile content, imagery or colors are replaced in the target site.

## Changes

- Main and wide shells now use `97vw`, with no 1280px/1560px desktop caps. The header, hero, footer and normal content align to these shells. The P10 Elementor content wrapper is also centered with a 97vw maximum. Booking/registration no longer override the outer shell with a fixed 900px/960px cap; the blank landing template now uses the shared shell.
- Section/hero backgrounds remain full bleed, as before. Paragraph line-length limits, individual panels/dialogs, images and author-selected Elementor layouts can be narrower; **97vw is a maximum page-content width, not a demand to stretch every paragraph or component**. Saved Elementor widget/layout controls are not overwritten.
- Theme-owned fixed type and spacing use two shared viewport scales:

```css
--p10-fluid-type: clamp(1rem, calc(0.875rem + 0.138889vw), 1.25rem);
--p10-fluid-space: clamp(0.75rem, 1.111111vw, 1.5rem);
```

At a 1440px viewport, both resolve to the previous 16px baseline. Fixed font sizes and margin/padding/gap values now multiply these scales. Existing bounded `vw` heading/section sizes are retained. The hero CTA margin now uses a bounded `vw` value as well. Body text has a 1rem floor; theme form text is at least 1rem (16px at default browser font settings), instead of shrinking to tiny pure-`vw` text on mobile. Rem bounds also respect the browser's font settings. Decorative border widths, accessible offscreen helpers, fixed touch targets and viewport-height constraints are intentionally not blindly converted to `vw`.
- Critical inline CSS uses the same width/scales as the main CSS; it no longer overrides the 97vw shells with old hard-coded caps or overrides the mobile header height with 76px. Block-editor and chatbot theme styles use the bounded scales too. Existing theme-authored inline spacing in profile/page templates uses the same variables; saved post content is untouched.
- Footer Customizer padding values are preserved as baseline pixel-equivalents at 1440px and scale with the shared viewport spacing. The control labels explain this; no saved options are rewritten.
- Wide tables scroll locally. Long URLs/text can wrap; image/full-width blocks and grid minimums stay inside their parent shell.
- Actual menu labels were overflowing the header during testing. The header now measures whether the full desktop menu fits, and switches to the **existing drawer** when it does not. It does not make labels unreadably small or remove destinations. Primary submenu destinations are retained as indented drawer links. Drawer ARIA visibility, Escape focus return and unlocking page scroll after a desktop resize were checked. Header data/order and saved WordPress menus are not rewritten.
- Theme version/cache key is **1.1.1**. The latest ZIP is `deliverables/perfect10-las10-upgrade-1.1.1.zip`; the prior 1.1.0 ZIP is retained. `scripts/package-theme.py` now derives the ZIP version from the theme header and updates archive checksums.

## Test scope and evidence

The disposable WordPress 6.8.3 / PHP 8.2 WebAssembly / SQLite environment from the previous implementation was reused. Layout fixtures are new, explicitly labelled test pages in that disposable database only; source profiles were not edited. Reproduction:

```sh
P10_WP_ROOT=/path/to/disposable/wp P10_DISPOSABLE_TEST=1 php tests/layout-fixtures.php
# Start that disposable WordPress site with the actual browser origin configured.
P10_TEST_URL=http://your-disposable-site node tests/layout.cjs
P10_TEST_URL=http://your-disposable-site node tests/navigation.cjs
P10_WP_ROOT=/path/to/disposable/wp P10_DISPOSABLE_TEST=1 php tests/navigation-data.php
```

Use `QA_DEPS` to specify your isolated Playwright/Chromium dependency directory. The sandbox used its bundled Chromium support libraries in `LD_LIBRARY_PATH`, as described in TESTING.md.

- **53 page/viewport combinations**, across **13 widths**: 320, 390, 768, 1024, 1100, 1180, 1280, 1366, 1440, 1536, 1920, 2560 and 3840px.
- Homepage checked at every width. Standard page, blank landing, booking, registration, directory, cities, packages, profile, city archive and category archive checked at 320, 768, 1440 and 2560px.
- Checked computed shell widths, actual 97vw homepage shell sizing, visible controls and header-link bounds, document horizontal overflow, minimum form text size, increasing large-screen type/spacing, and JS page exceptions. Offscreen honeypots/hidden dialogs/drawers are deliberately excluded from visible-control checks.
- Drawer open/close, link bounds, ARIA state, Escape focus return, submenu-data preservation and resize-to-desktop scroll unlocking checked separately.
- Existing live-REST chatbot smoke checks rerun at desktop/tablet/mobile after the CSS/JS change.
- PHP parser, all frontend JS syntax, all six CSS parses and Git whitespace checks rerun.

Representative computed values at a default 16px browser root font:

| Viewport | Homepage shell | Body type | Footer top padding |
|---:|---:|---:|---:|
| 320px | 310.39px | 16px | 24px (existing mobile variant, now fluid) |
| 1440px | 1396.80px | 16px | 48px |
| 2560px | 2483.19px | 17.56px | 72px |
| 3840px | 3724.80px | 19.33px | 72px (bounded) |

Evidence: `test-evidence/layout.txt`, `test-evidence/navigation.txt`, `test-evidence/chat-fluid.txt`, and `test-evidence/fluid-static.txt`. Browser screenshots were inspected locally. External network requests were blocked in the layout test: Google Fonts are not available under the sandbox allowlist, so typography was checked using the theme's fallback font stack. The disposable PHP-WASM development server required a restart after repeated test runs exhausted its socket handling; this is not evidence about production-host performance.

## Limits and rollback

These are finite Chromium smoke checks, **not a guarantee that every device, browser, installed menu, webfont, third-party widget, custom author layout or text-zoom configuration renders perfectly**. Real packaged Elementor editor/Pro and full accessibility/visual-regression acceptance remain staging checks. Content-specific menus trigger the measured drawer when needed; unusual logos or third-party injected controls still need target-site review.

Source backup before this change: `backups/las10-before-fluid-layout.tar.gz` (theme files only). The prior pushed commit `e54885e` also preserves the pre-fluid implementation. Back up the target site's database/uploads separately before updating; file-only rollback restores the preceding theme archive and clears caches. This update does not migrate production database content.
