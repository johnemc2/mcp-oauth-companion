# Third-party source and licensing review

The OAuth implementation in `inc/Auth/`, `inc/Transport/`, `inc/Views/`, `inc/Logging/`, `inc/Bootstrap.php` and related files is based on or adapted from **WP Media MCP OAuth** source. Its **precise upstream repository, commit, copyright notices and redistribution terms have not yet been independently verified**. Do not present this candidate as a fully cleared public release.

`vendor/wp-media/apply-filters-typed/` identifies its publisher as **WP Media** and declares **GPL-3.0-or-later** in its `composer.json`. Confirm and preserve upstream notices and bundled license requirements.

`vendor/composer/` includes Composer-generated autoload files and Composer's license file. Verify its notices against the exact distributed dependency versions.

The `inc/ContentAbilities.php` companion abilities and release packaging were developed for this project. Project authorship credit: **Jonathan Grice and Sol**. That credit does not replace the copyright or license notices of upstream authors.

Before publishing: locate the authoritative upstream source, compare each copied/modified file, retain attribution, review all license compatibility obligations, and record upstream version/commit in this document.
