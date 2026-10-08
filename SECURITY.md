# Security policy

This is a pre-release project and is not currently offered as a security-certified package.

Do not post access tokens, OAuth authorization codes, application passwords, private keys, or other credentials in public issues. If you discover a vulnerability, contact the maintainer privately through a channel they explicitly designate after the repository is created.

Use HTTPS, a dedicated least-privileged WordPress account, site backups, and the latest supported versions of WordPress and its MCP Adapter. The companion exposes authenticated write abilities; installing it increases the consequences of a compromised MCP client or WordPress account.

A complete independent OAuth review and WordPress permission-boundary integration test suite are pending. See `SECURITY-REVIEW.md`.
