# Contributing

Thanks for helping maintain WP Media Categories.

## Before changing behavior

Describe the observable behavior, compatibility expectations, and acceptance
criteria in a GitHub issue. Security reports belong in the private reporting
channel described in `SECURITY.md`.

## Pull requests

- Keep each pull request focused and reversible.
- Add regression coverage for behavior changes and bug fixes.
- Preserve the declared PHP and WordPress minimum versions.
- Exercise both list and grid media-library views when changing filters.
- Identify taxonomy, attachment, capability, nonce, database, and release impact.
- Do not commit credentials, build caches, development databases, or generated
  release ZIP files.
- Wait for every required check and resolve review conversations before merge.

AI-assisted contributions are welcome, but the contributor remains responsible
for understanding and validating the result.

## Development requirements

The shipped plugin and its Composer development toolchain require PHP 7.4 or
newer. Production Composer installs should omit development dependencies.

Install the locked development tools and run the local checks with:

```sh
composer install
composer test
composer phpcs
composer phpstan
```

PHPCS compares the current code with a committed legacy baseline, while PHPStan
starts with no ignored findings. Existing PHPCS findings may be removed as code
improves, but new findings and increased allowances fail CI.

## Real WordPress smoke test

The planned shared portfolio workflow will load `tests/integration/smoke.php`
after the production build is activated in an isolated single-site WordPress
environment.
The smoke test checks taxonomy registration, attachment term persistence,
custom term counts, and a real attachment query against the oldest supported
WordPress version, current stable WordPress, and WordPress trunk. Keep this
entry point self-contained, deterministic, credential-free, and compatible
with the published PHP and WordPress minimums.
