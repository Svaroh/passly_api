Passly 6.1.3 delivers standalone Service Worker registration conforming to strict Content Security Policies, App Shell precache enhancements, and offline resilience improvements.

## [6.1.3] - 2026-10-26
### Fixed
- Fix CSP violation by extracting Service Worker registration into standalone `/js/app/service-worker-register.js`
- Bump App Shell cache to `passly-app-shell-v3` with precache registration script

## [6.1.2] - 2026-10-25
### Added
- Enhance App Shell Service Worker v2 with complete precache suite (api-vendors, api-triage, api-app, stylesheets)
- Implement quick network timeout and instant App Shell fallback to eliminate ERR_INTERNET_DISCONNECTED
- Register Service Worker across all layouts including login and default templates

## [6.1.0] - 2026-10-15
### Added
- Add perpetual offline mode settings and defaults for unlimited session duration, retention, and items
- Add sync plugin with change journal and /sync/meta and /sync/changes endpoints
- Add recoverable resource trash API for soft deletion and restoration
- Add sync log pruning command and retention service

### Changed
- Update API core and dependencies to upstream Passbolt CE
- Upgrade framework to CakePHP 5.4 with automatic Request to DTO mapping
- Load JwtSubject identifier in JwtAuthenticationService

### Fixed
- Fix nested transaction error in V601 and V602 offline settings migrations
- Ensure fallback system admin ID and clean migration execution in V601 and V602
- Fix delete notification assertions, install CLI quoting, and coverage metadata
- Fix offline feature check and email branding

### Security
- Upgrade phpseclib/phpseclib to 3.0.57 (CVE-2026-84308, AIKIDO-2026-730961)
- Update dependencies to resolve security advisories

### Maintenance
- Update dependency cakephp/cakephp to v5.4.2
- Update dependency composer/composer to v2.10.3
- Update dependency league/flysystem to v3.35.3
- Update dependency squizlabs/php_codesniffer to 3.13.6

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
