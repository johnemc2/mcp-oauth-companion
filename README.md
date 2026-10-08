# MCP OAuth Companion

**Pre-release publication candidate — not security-certified.**

**Authors:** Jonathan Grice and Sol. Developed collaboratively for a self-hosted WordPress website, originally to reduce reliance on quota-limited third-party management tools.

A companion to the **separately installed WordPress MCP Adapter**. It provides OAuth-enabled MCP connectivity and content-management abilities. It is **not** the WordPress MCP Adapter itself.

## Features

- Search, read, draft, revise, publish, and inspect revisions of WordPress posts
- Search and edit pages, create drafts, publish approved drafts
- Replace a uniquely matched content passage with a last-modified check
- Search and upload images, update image alt text
- Move posts and pages to recoverable Trash; image Trash only when WordPress `MEDIA_TRASH` is enabled
- List and assign eligible authors, subject to WordPress permissions

## Requirements

- WordPress and a compatible, separately installed MCP Adapter (tested on one installation with MCP Adapter 0.7.0)
- PHP 8.2+, HTTPS, and an authorized WordPress account
- WordPress application-password and OAuth-related configuration appropriate to your client

## Installation

1. Back up your site and install a compatible WordPress MCP Adapter.
2. Install this plugin ZIP in **Plugins → Add New → Upload Plugin**, and activate it.
3. Configure your MCP client with **your own site's** OAuth and MCP endpoints; do not copy someone else's client IDs, URLs, or credentials.
4. Connect using a dedicated, least-privileged WordPress account. Test read-only actions before write actions.

**Caution:** This source contains a site-relative static ChatGPT OAuth client-registration path and a fixed ChatGPT redirect URI. Review these choices for your deployment before use. The OAuth and client-identity implementation has not received an independent security audit.

## Development and release

See [SECURITY.md](SECURITY.md), [SECURITY-REVIEW.md](SECURITY-REVIEW.md), [THIRD-PARTY-NOTICES.md](THIRD-PARTY-NOTICES.md), and [CHANGELOG.md](CHANGELOG.md).

This repository is **not yet approved for public distribution** until upstream source provenance, applicable licenses, and the security-review blockers are resolved. A PHP syntax check is not a penetration test.

## License

Intended project license: **GPL-3.0-or-later**, subject to verifying licensing and attribution of all upstream-derived components before publication. See `LICENSE` and `THIRD-PARTY-NOTICES.md`.
