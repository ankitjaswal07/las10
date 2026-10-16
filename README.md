# Perfect 10 WordPress theme

The installable theme source is in [`perfect10/`](perfect10/). The repository originally contained only `perfect10-fixed-v5.zip`; the archive has been extracted without removing the original package. See [`AUDIT-AND-IMPLEMENTATION.md`](AUDIT-AND-IMPLEMENTATION.md) for the audit findings, tested scope, blockers, and implementation plan.

## Install

Upload `perfect10/` as a theme directory (or zip that directory for WordPress), activate it, and visit **Perfect 10 → Setup & Import**. The importer is designed to add the theme's sample pages and records without overwriting existing content. Back up the site before importing into a populated production installation.

The current importer creates the sample data embedded in the theme; it does **not** contain 42 verified reference profiles or their complete authorized galleries. Do not treat sample records or stock imagery as source-site data. See the audit report before using imported demonstration content publicly.

## Elementor

The theme includes standard WordPress page templates and now declares Elementor theme support for Elementor Free. Elementor Free can edit ordinary pages and use its standard widgets; the theme's custom profile and location templates remain PHP-rendered dynamic templates. Elementor Pro Theme Builder and third-party dynamic-field widgets are not required or included.

## Privacy and external services

The existing theme's public forms save inquiries in WordPress and may send notification email. Configure and test mail delivery with your host or a transactional mail provider. No AI chatbot or AI API integration is included. Do not put API credentials in frontend code or in the repository.
