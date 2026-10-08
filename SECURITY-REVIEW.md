# Pre-publication security review — October 2026

## Changes in this publication candidate

- Aligned plugin metadata to intended `GPL-3.0-or-later` and credited **Jonathan Grice and Sol**. This does **not** establish the licensing provenance of all derived source files.
- Replaced the single `edit_posts` registration gate with capability-specific gates for media and page abilities. The individual execute callbacks retain their own object-level checks.
- Suppressed potentially misleading/permission-sensitive `total_found` and `total_pages` query counts; clients must not depend on these fields until permission-aware pagination is implemented.
- Rewrote generic installation documentation, added third-party attribution checklist and security policy.

## Blockers before public GitHub release

1. **Source provenance and licensing**: independently identify and verify the upstream WP Media OAuth project, exact source version/commit, all copyright notices and redistribution obligations. The project-level GPL-3.0-or-later designation is provisional until this is complete.
2. **OAuth security review**: verify client identity, fixed ChatGPT redirect URI, PKCE and state, token issuance/refresh/revocation, scope and account binding, JWT signature and key rotation, SSRF and DNS rebinding defenses, logging/redaction, and consent enforcement with adversarial tests.
3. **Integration test suite**: test WordPress and MCP Adapter together, including Subscriber/Author/Editor/Administrator capabilities, private content, ownership, cross-user updates, media permissions, and concurrent edits.
4. **Release reproducibility**: document exact dependency versions, source checksums, build process and supported WordPress/PHP versions.

## Known limitations

- `expected_modified_gmt` detects many stale writes but is not an atomic compare-and-swap. Concurrent updates may race.
- Draft post updates do not require `expected_modified_gmt` (legacy behavior); consider adding it in a backward-compatible future release.
- Media upload is limited to 5 MiB with extension/MIME/content checks, but resource limits for image processing need adversarial testing.
- Media trash requires `MEDIA_TRASH`; it fails closed rather than permanently deleting.
- Query count metadata is intentionally `null` pending permission-aware pagination.
- A site-relative static ChatGPT OAuth client path and fixed redirect URI are present; validate appropriateness for multi-site deployments.

## Previous live testing (v0.6.1)

On one site: ability discovery, media search/upload/alt text, page create/update/publish/trash, exact-snippet editing, stale/ambiguous edit rejection. Recoverable media trash disabled was correctly refused. **The changes in this publication candidate have not been deployed or live-tested.**

## Static checks

Run `php -l` on all PHP files; inspect release archives; scan for credential-like values. These checks cannot prove absence of vulnerabilities.
