Passly 6.0.0 is the first stable API release from the Svaroh Passly fork.

This release completes the visible Passly rebrand, ships the Team Shield assets across the API bundle, and adds the GitHub automation needed to publish releases and trigger packaging from tags.

It also includes the browser first-login relay used by Android to help set up the browser extension, the new passkey resource type, notification self-settings restored in the API bundle, and dependency/security maintenance carried forward from the fork.

## Highlights
The API now exposes browser first-login relay endpoints for the mobile to browser setup flow, including encrypted private key handoff support.

Passkey support is available as a v5 resource type, with follow-up handling for v4 metadata settings.

The product-facing API assets and wording now use Passly branding, including the Passly Team Shield logo in static and inline bundles.

Release, packaging, CI, and coverage workflows are now available in GitHub Actions for the fork.

## [6.0.0] - 2026-06-07
### Added
- Adds browser first login relay endpoints for secure Android to browser setup
- Adds encrypted private key relay support for browser first login
- Adds passkey resource type support
- Adds password and folder self-notification settings
- Adds GitHub Actions CI, release, packaging, and coverage workflows

### Changed
- Rebrands visible product wording and assets to Passly
- Replaces product logos and inline API logos with the Passly Team Shield
- Uses vault wording for resource actions

### Fixed
- Fixes browser first login response handling
- Restores notification self settings in the API bundle
- Allows passkey creation with v4 metadata settings
- Fixes TOTP resource type test expectations
- Fixes PHPUnit coverage metadata and CI stability issues

### Security
- Resolves remaining npm audit findings
- Fixes CodeQL code scanning alerts
- Updates vulnerable npm and Composer dependencies

### Maintenance
- Adds the Nix development shell for local API tooling
- Aligns Composer audit strict branch handling for the Passly fork

Release song: https://www.youtube.com/watch?v=gI6fQ2IXMjE

## [5.16.0] - 2026-09-16
### Added
- PB-52633 Add support for Offline Mode

### Fixed
- PB-54100 Fix account recovery notifications are sent to deleted admins
- PB-54504 Fix MissingTemplateException on .json endpoints when X-Requested-With: XMLHttpRequest is set
- PB-54574 Fix lock-wait-timeout on DELETE during group edit that removes a user

### Security
- PB-54489 Upgrade phpseclib/phpseclib to 3.0.57 (CVE-2026-84308, AIKIDO-2026-730961)
- PB-54532 Small upgrade for js-yaml

### Maintenance
- PB-49136 Update cakephp/migrations package to improve compatibility with PHP 8.5
- PB-53658 Use Request to DTO Mapping to automatic mapping of request data
- PB-53925 Add tests to assert session data do not trigger any code
- PB-54144 Update the Passbolt logo
- Renovate: Update adminer:standalone Docker digest
- Renovate: Update dependency cakephp/cakephp to v5.4.2
- Renovate: Update dependency composer/composer to v2.10.3
- Renovate: Update dependency league/flysystem to v3.35.3
