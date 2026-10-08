# MCP OAuth Companion

**Version 0.6.2**

**Authors:** Jonathan Grice and Sol.

A companion plugin for the **separately installed WordPress MCP Adapter**. MCP OAuth Companion adds OAuth-enabled MCP connectivity and WordPress content-management abilities; it is not a replacement for the adapter.

## Features

- Search, read, draft, revise, publish, and inspect revisions of WordPress posts
- Search and edit pages, create drafts, and publish approved drafts
- Replace a uniquely matched content passage with a last-modified check
- Search and upload images, and update image alt text
- Move posts and pages to recoverable Trash; image Trash when WordPress `MEDIA_TRASH` is enabled
- List and assign eligible authors, subject to WordPress permissions

## Requirements

- WordPress with a compatible, separately installed MCP Adapter (used with MCP Adapter 0.7.0)
- PHP 8.2 or newer and HTTPS
- An authorized WordPress account and suitable application-password/OAuth client configuration

## Installation

1. Back up your WordPress installation.
2. Install and activate a compatible WordPress MCP Adapter.
3. Upload the MCP OAuth Companion plugin ZIP via **Plugins → Add New → Upload Plugin** and activate it.
4. Configure your MCP client to use **your own site's** OAuth and MCP endpoints. Never reuse another site's client credentials.
5. Connect using a dedicated, least-privileged WordPress account and test read-only operations first.

## Version 0.6.2

This maintenance release strengthens JWT validation: normal verification rejects tokens with a missing, non-integer, or expired `exp` claim. The revocation endpoint retains the ability to inspect expired signed tokens. Focused JWT regression tests have passed locally.

## Security and deployment notes

This plugin includes a site-relative static ChatGPT OAuth client-registration path and a fixed ChatGPT redirect URI; review client registration and redirect settings before using it on another site. The OAuth implementation has not undergone an independent security audit. Consult [SECURITY.md](SECURITY.md) and [SECURITY-REVIEW.md](SECURITY-REVIEW.md).

## Development

See [CHANGELOG.md](CHANGELOG.md), [THIRD-PARTY-NOTICES.md](THIRD-PARTY-NOTICES.md), and [LICENSE](LICENSE). Verify third-party source attribution and license compatibility before wider public redistribution.

## License

Project license metadata: **GPL-3.0-or-later**. See [LICENSE](LICENSE) and [THIRD-PARTY-NOTICES.md](THIRD-PARTY-NOTICES.md) for licensing and upstream attribution information.
