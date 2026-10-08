# Changelog

## 0.6.2
- Hardened JWT expiration validation: reject missing, non-integer, and expired `exp` claims during normal token verification.
- Preserved expired-token decoding for the revocation endpoint.
- Added JWT security regression tests and a GitHub Actions PHP testing workflow.
- Updated plugin version and release documentation.
- Focused JWT regression tests passed locally; no independent security audit is claimed.

## Unreleased — earlier GitHub publication preparation (based on v0.6.1)
- Project authors credited as Jonathan Grice and Sol.
- Updated intended project license metadata and added upstream attribution checklist.
- Introduced capability-specific registration gates for page and media abilities.
- Suppressed permission-sensitive query count metadata until safe pagination is implemented.
- Added security policy and generic documentation. **Not independently deployment-tested.**

## 0.6.1
- Added guarded recoverable Trash operations for posts/pages and image attachments.

## 0.6.0
- Added exact-snippet editing, page draft/edit/publish, image search/upload/alt text.

## 0.5.0
- Added content search and reading, author listing and reassignment.

## 0.4.1
- Fixed revision retrieval by-reference issue.

## 0.4.0
- Added published-post updates, publishing, and revision access.

## 0.3.0
- Added basic post listing, reading and draft operations.
