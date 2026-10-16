# Las10s / Perfect 10 WordPress theme upgrade

The existing theme is in [`perfect10/`](perfect10/), version **1.1.0**. Work is on **arena/5cd1cd5f-las10**. The original ZIP and two WordPress exports are retained.

## Start here

- [Upgrade, Elementor, import, chatbot/provider and privacy guide](docs/UPGRADE-GUIDE.md)
- [Source inventory: all 42 published profiles, 2 excluded drafts, 240 attachment records](docs/SOURCE-INVENTORY.md)
- [Testing, evidence and known limitations](docs/TESTING.md)
- [Disposable import mapping/outcomes — not a production import](docs/DISPOSABLE-IMPORT-REPORT.json)
- Installable theme: `deliverables/perfect10-las10-upgrade-1.1.0.zip`
- Pre-edit source backup: `backups/las10-before-upgrade.tar.gz`

## Important boundaries

Use staging and back up the **target database and uploads** before installing/importing. The new source importer is **Appearance → Authorized Source Import**, separate from the original sample-site importer. Confirm reuse rights before publication. No source photo binaries/archive were supplied; no source photos were downloaded or bundled. Missing photos remain labelled placeholders.

Elementor Free PHP rendering and widget registration were exercised, but the packaged editor UI was unavailable. Existing homepage sections remain PHP/Customizer-driven pending an approved database-backed migration; this is **not** a claim that the entire site is Elementor-editable. Pro Theme Builder conditions were not tested with Pro.

The floating launcher opens text chat, not a contact form. Local published-content retrieval is the default; optional server-side OpenAI relevance ranking requires separate provider configuration. Follow-up enquiries require consent and are disabled by default. Live provider, voice services and email delivery remain unverified.

Static checks, disposable WordPress/SQLite import/re-import and security tests, synthetic media tests and desktop/tablet/mobile chatbot smoke tests are recorded under `docs/test-evidence/`. They are not a substitute for production MySQL, content authorization, visual regression, Elementor editor and licensed-Pro acceptance testing.

The earlier [`AUDIT-AND-IMPLEMENTATION.md`](AUDIT-AND-IMPLEMENTATION.md) is a historical audit; the new guide supersedes its environment and outstanding-feature statements.
