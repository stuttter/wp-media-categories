# Changelog

## Unreleased

- Add locked PHPCS and PHPStan regression gates with committed legacy baselines.

## 2.2.1 (2026-09-16)

- Require WordPress 6.4 or newer

## 2.2.0 (2026-09-14)

- Require the Media Library bulk-action nonce before changing attachment categories (CVE-2025-60134)
- Require PHP 7.4 or newer
- Correct attachment-query pagination metadata
- Preserve media-library loading behavior introduced in WordPress 5.8
- Accept taxonomy names and taxonomy objects in the count callback
- Use prepared count queries and current taxonomy-query signatures
- Preserve percent-encoded and non-Latin category slugs in Media Library filters and redirects
- Keep bulk category actions available through the current WordPress footer-script lifecycle
- Harden Media Library and widget data for their HTML and JavaScript output contexts
- Reject malformed bulk-action values without PHP 8 type errors
- Correct category-widget redirect URLs containing ampersands

## 2.1.0 (2020-05-13)

- Improve PHP 7.2 compatibility
- Improve the gallery shortcode implementation

## 2.0.0 (2018-06-05)

- Correct taxonomy count targeting
- Use the media taxonomy rewrite slug in the dropdown widget

## 1.1.0 (2016-09-28)

- Correct uncategorized attachment filtering in the administration area

## 1.0.1 (2016-09-06)

- Avoid interfering with front-end searches
- Improve uncategorized attachment filtering performance

## 1.0.0 (2016-09-06)

- Correct bulk editing
- Improve must-use plugin compatibility

## 0.1.1 (2015-10-28)

- Improve the media-grid layout

## 0.1.0 (2015-10-21)

- Publish the initial release
