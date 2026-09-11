# WP Media Categories contributor guidance

## Compatibility

- Preserve PHP 7.0 and WordPress 5.0 compatibility unless a dedicated pull
  request explicitly changes the published minimums.
- Treat attachment taxonomy relationships, bulk actions, AJAX requests, custom
  count queries, and media-library integration as elevated-risk code.
- Preserve public functions, hooks, filter arguments, shortcode behavior,
  taxonomy names, rewrite slugs, and widget output unless a deprecation path is
  part of the change.

## Tests

- Add a regression test before changing observed behavior.
- Characterize taxonomy arguments, query filtering, attachment permissions,
  nonces, bulk actions, and count updates when changing those paths.
- Run `composer test`, the declared PHP syntax matrix, and metadata/artifact
  validation before requesting review.

## Automation

Follow the organization-level safety boundaries. AI-authored implementation
must remain a draft pull request and cannot modify workflows, release policy,
ownership, security policy, or this file.
