# Passly API / OCI Notes

This repository is a local checkout of the `Svaroh/passly_api` fork, based on
`passbolt/passbolt_api`, used together with a NixOS deployment on Oracle Cloud
Infrastructure.

Current local remotes:

- `origin`: `https://github.com/Svaroh/passly_api.git`
- `legacy-66ton99`: `git@github.com:66Ton99/passbolt_api.git`
- `upstream`: `git@github.com:passbolt/passbolt_api.git`

The primary local and GitHub default branch is `main`.

On `2026-06-02`, the former local `rebrand/passly` branch was renamed to
`main`, pushed to `origin/main`, and `Svaroh/passly_api` default branch was set
to `main`. The migrated `main` head is `06f3a49116`
(`Replace inline API logos with Passly Team Shield`). Old `master` and
`rebrand/passly` refs may still exist for history; use `main` for new local and
remote work unless the user explicitly asks for a legacy branch.

Also on `2026-06-02`, the two API commits after `91a13ea74a` were rewritten to
fix author/committer metadata from
`45160296+66Ton99@users.noreply.github.com` to `github@66ton99.org.ua`.
The corrected commits are `4af3a532ca` and `06f3a49116`; both `origin/main`
and legacy `origin/rebrand/passly` were force-updated to the corrected history.

## Communication / Safety

- The user usually writes in Ukrainian. Reply in Ukrainian unless they switch language.
- The user cares about security posture.
- Do not ask the user to paste private SSH keys, passwords, API keys, OCI session tokens, or SMTP credentials in chat.
- Private SSH keys may live under Google Drive CloudStorage and can be unreadable from shell. Prefer the SSH agent.
- Do not store secrets in this repository, including:
  - OCI session tokens
  - private SSH keys
  - user passwords
  - API private keys
  - SMTP passwords
  - browser auth URLs after use

## Passly Release Notes

- Do not add or invent `PB-` / Passbolt ticket prefixes in new Passly API
  release notes, changelog entries, GitHub releases, commit summaries, or
  deployment summaries. Use plain descriptions, or real Passly issue IDs only
  when they actually exist. Existing historical upstream Passbolt changelog
  entries may keep their original identifiers.

## Testing / Remote Production Rules

- Never run automated tests on the remote Passbolt server. This includes `vendor/bin/phpunit`, `composer test`, `composer cs-check`, PHPUnit groups, fixture generation, test database setup, Selenium test runs, and any command that enables test mode.
- Run tests only locally in `passly_api/`, using the local test database/container and local test GPG keys.
- Use the repository Nix dev shell for local API work and tests. `flake.nix`
  and `flake.lock` define the expected local toolchain, including PHP 8.2 with
  required extensions, Composer, Node/npm, `gnupg`, and MySQL client tools such
  as `mysqldump`. Enter it with `nix develop` from the repo root before running
  Composer, PHPUnit, npm, GPG, or MySQL client commands.
- Keep `flake.nix` and `flake.lock` updated when PHP extensions, Composer/npm
  tooling, test CLI tools, or local test requirements change.
- Do not silence PHPUnit coverage metadata warnings with flags such as
  `--do-not-fail-on-phpunit-warning`; fix the invalid `@covers` / `@uses`
  annotations or the underlying metadata instead.
- Resource type tests should not assert the absolute count of all default
  resource types unless the total count is the behavior under test. The default
  set changes when new types such as `v5-passkey` are added; prefer asserting
  the required slugs are present or absent.
- The remote Passbolt server must stay production-like at all times, with maximum practical security. Do not enable `DEBUG=true`, test keys, test fixtures, Xdebug, Selenium, dev/test Composer dependencies, permissive file permissions, temporary public endpoints, or test-only config on the server.
- Remote verification is limited to production-safe checks such as `systemctl` status, `journalctl`, Nginx/PHP-FPM status, Passbolt health checks, HTTP(S) endpoint checks, and read-only inspection of deployment paths and configuration.
- Do not create ad-hoc backup directories or backup copies on production servers
  during deploys unless the user explicitly asks for one. Use git history and
  build artifacts as the rollback source.
- If a change needs test coverage, run the tests locally first, then deploy the already verified committed git version to the server.

## Common Commands

Run commands from `passly_api/` inside the Nix dev shell (`nix develop`):

```bash
nix develop
```

```bash
composer test
composer cs-check
composer cs-fix
composer stan
composer psalm
npm run lint
```

## Local OCI CLI

- OCI CLI profile: `antonshapka`
- Region: `eu-amsterdam-1`
- Tenancy / root compartment OCID:
  `ocid1.tenancy.oc1..aaaaaaaaxbqxcevsbmyuomp4jfzlxht3546fa25kp5q7b2kec5nhrnec7zdq`
- Use OCI CLI with:

```bash
oci ... --profile antonshapka --auth security_token
```

- If the OCI session expires, try:

```bash
oci session refresh --profile antonshapka
```

- If refresh fails with "session is no longer valid", re-authenticate:

```bash
oci session authenticate --profile-name antonshapka --region eu-amsterdam-1
```

This opens browser auth / prints a login URL. Wait for the user to complete login before retrying OCI commands.

## OCI Network

- Public subnet:
  - Name: `public-subnet-a1`
  - CIDR: `10.0.0.0/24`
  - Subnet OCID:
    `ocid1.subnet.oc1.eu-amsterdam-1.aaaaaaaasnct6kvztert7wch2zf3kvdcy4sweaelalesxnje5zmwrpgn7xbq`
- Multiple instances can share this public subnet. They do not conflict just because they are in the same subnet; each gets its own private IP/VNIC and, if configured, its own public IP.
- OCI default security list used for these hosts:
  `ocid1.securitylist.oc1.eu-amsterdam-1.aaaaaaaaotigwedep6t6vxjsoy3ixgoim35aoshumva7ewraltn44phucobq`
- The security list allows public TCP `80` and `443` for the Passbolt/reverse-proxy host.

## Passbolt VM

- OCI display name: `passbolt`
- Instance OCID:
  `ocid1.instance.oc1.eu-amsterdam-1.anqw2ljrmll4g6yc3d6qxrx2ng5ozxnkge6yzct633hzm2eszahhaea2wdya`
- Shape: `VM.Standard.E2.1.Micro`
- Availability domain: `QEGv:eu-amsterdam-1-AD-1`
- Fault domain: `FAULT-DOMAIN-2`
- OS: NixOS `25.11 (Xantusia)`, converted from Ubuntu via `nixos-infect`
- Kernel at last verification: `6.12.83`
- Hostname: `passbolt`
- Public IP: none; `passbolt` is private-only behind `nixos-micro` since `2026-05-04`.
- Private IP: `10.0.0.35`
- VNIC OCID:
  `ocid1.vnic.oc1.eu-amsterdam-1.abqw2ljrdgsa6ql5lqwkerk2v6zvkeolq2vxy7mcxfhrkwlnqrb73mshyzkq`
- Reserved public IP OCID:
  `ocid1.publicip.oc1.eu-amsterdam-1.amaaaaaamll4g6yam5juhpdrpmil2cw4uqtfsb7ohiro52h73fafkyprodpq`
- Reserved public IP display name: `reverse-proxy-reserved-ip`
- Old ephemeral public IP `92.5.237.225` was released on `2026-05-01`.
- Secondary private IP OCID:
  `ocid1.privateip.oc1.eu-amsterdam-1.aaaaaaaatybbsxt3c2xeywi23omox4u6fgmmzcykqtsf3fwlmmg6muzpudhq`
- Secondary private IP `10.0.0.97` remains on the VNIC without a public IP.

SSH:

```bash
ssh -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35
```

If there is a host-key conflict from the old IP transition, use:

```bash
ssh -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35
```

SSH public key file used at launch:

```text
/Users/ton/oci/oci-passbolt.pub
```

Public key:

```text
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIMaWBHfvafYA5T47kpRUDMxd/sIGRDMNtyi1SGJK7Ctj ton@mac-m4
```

Shielded launch was attempted first but OCI rejected it:

```text
VM.Standard.E2.1.Micro does not support the provided platform configuration.
```

Shape metadata showed `platform-config-options: null`, so this Always Free micro shape does not support the requested Shielded platform config.

## NixOS Deployment

Important server-side files:

- `/etc/nixos/configuration.nix`
- `/etc/nixos/hardware-configuration.nix`
- `/etc/nixos/passbolt.nix`
- `/etc/nixos/pkgs/passbolt-api/package.nix`
- `/etc/nixos/site.nix` on `nixos-micro` now owns the public reverse proxy.

PHP/CakePHP sessions on production:

- On `2026-05-07`, Passbolt production was switched from file-backed PHP sessions to CakePHP database sessions in MySQL.
- Runtime config `/var/lib/passbolt/config/passbolt.php` has:

```php
'Session' => [
    'cookie' => 'passbolt_session',
    'defaults' => 'database',
    'handler' => [
        'engine' => 'DatabaseSession',
        'model' => 'Sessions',
    ],
],
```

- NixOS source of truth: `/etc/nixos/passbolt.nix`.
- MySQL session table: `sessions`.
- Requested `ENGINE=MEMORY` was attempted, but OCI managed MySQL rejected it with:
  `Storage engine MEMORY is disabled (Table creation is disallowed).`
- A direct probe `CREATE TABLE passbolt_memory_probe ... ENGINE=MEMORY` failed with the same MySQL error, so MEMORY tables are not usable on the current managed MySQL service.
- Deployed fallback: `sessions` table uses `ENGINE=InnoDB`, with `id`, `data`, and `expires` columns, still stored in MySQL and independent of PHP-FPM process lifetime.
- Verification after deployment:
  - `Session.defaults=database` and `DatabaseSession` were present in `/var/lib/passbolt/config/passbolt.php`.
  - `sessions.engine=InnoDB`.
  - Creating a `passbolt_session` cookie through `GET /auth/login`, restarting `phpfpm-passbolt`, then calling `GET /auth/login` with the same cookie kept the same session id.
  - No new `Set-Cookie: passbolt_session` was returned after the PHP-FPM restart.
  - The same session row existed in MySQL before and after restart.
  - `passbolt-setup`, `phpfpm-passbolt`, and `nginx` were active; failed units: `0`; public healthcheck returned HTTP `200` / `"OK"`.

Passbolt/Passly package:

- Built from the local server checkout via `lib.cleanSource /etc/nixos/pkgs/passbolt-api/repo` in `/etc/nixos/pkgs/passbolt-api/package.nix`.
- Since `2026-05-08`, `package.nix` no longer pins a fixed `builtins.fetchGit rev`; `nixos-rebuild switch` copies the current checkout contents into the Nix store.
- On `2026-06-02`, production was deployed from `Svaroh/passly_api` branch
  `rebrand/passly` at commit `91a13ea74ab0c1325ad81aa3ca38730ced9cb985`
  (`Rebrand visible product name to Passly`). The public login page title
  verified as `Passly | Open source password manager for teams`, and
  `/healthcheck/status.json` returned HTTP `200` with body `"OK"`.
- On `2026-06-02`, the Team Shield Passly logo/icon update was deployed from
  `Svaroh/passly_api` branch `rebrand/passly` at commit
  `4af3a532caac81ac573c91e93134288129d79793`
  (`Replace brand assets with Passly Team Shield`). Public verification:
  failed units `0`; `passbolt-setup`, `phpfpm-passbolt`, and `nginx` active;
  `/healthcheck/status.json` returned HTTP `200` with body `"OK"`; public
  `/favicon.svg` and `/img/logo/logo.svg` contained the Team Shield colors
  `#17413F` and `#8BBF45`; login title remained
  `Passly | Open source password manager for teams`.
- On `2026-06-02`, the inline API SVG logo fix was deployed from
  `Svaroh/passly_api` branch `rebrand/passly` at commit
  `06f3a491160a8d3bc61a2ba7284b1cea8caf9893`
  (`Replace inline API logos with Passly Team Shield`). Public verification:
  failed units `0`; `passbolt-setup`, `phpfpm-passbolt`, and `nginx` active;
  `/healthcheck/status.json` returned HTTP `200` with body `"OK"`; public
  `api-account-recovery.js`, `api-feedback.js`, `api-recover.js`,
  `api-setup.js`, and `api-triage.js` all had
  `This is the logo of Passly.` and Team Shield marker `#17413F`, and no
  `This is the logo of passbolt` string.
- The local checkout is currently on branch `main`.
- The server checkout may still be on branch `rebrand/passly` unless it has been
  explicitly migrated. Its `origin`
  remote is `git@github.com:Svaroh/passly_api.git`; the old `66Ton99` remote is
  `legacy-66ton99`.
- Direct GitHub pull from `Svaroh/passly_api` is currently not usable from the
  private VM because GitHub reported `Deploy keys are disabled for this repository`
  for this fork. Do not add broad user-level SSH access just to work around this
  without an explicit security decision.
- Until direct Svaroh pull access is resolved, deploy a committed local Svaroh
  state by creating a git bundle locally and fetching it into the server checkout:

```bash
cd passly_api
git status -sb
git bundle create /tmp/passly_api_main.bundle main
scp -o BatchMode=yes \
  -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  /tmp/passly_api_main.bundle ton@10.0.0.35:/tmp/passly_api_main.bundle

ssh -o BatchMode=yes \
  -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35 \
  'cd /etc/nixos/pkgs/passbolt-api/repo
   sudo git status -sb
   sudo git bundle verify /tmp/passly_api_main.bundle
   sudo git fetch /tmp/passly_api_main.bundle refs/heads/main
   sudo git merge --ff-only FETCH_HEAD
   sudo rm -f /tmp/passly_api_main.bundle'
```

- To deploy after the server checkout has been updated, run:

```bash
cd /etc/nixos/pkgs/passbolt-api/repo
sudo nixos-rebuild switch --option substitute false
```

- A plain `sudo systemctl restart phpfpm-passbolt.service` is still not enough for code changes, because the active app runs from an immutable Nix store path. Rebuild is required after `git pull`.
- Keep the repo clean before rebuild; untracked non-functional files in the server checkout can be copied by `lib.cleanSource` unless filtered by Nix defaults.
- Current deployed server checkout commit after the Passly deployment on
  `2026-06-02`:
  `06f3a491160a8d3bc61a2ba7284b1cea8caf9893`
  (`Rebrand visible product name to Passly`).
- On `2026-10-13`, the API vendor derivation was updated for upstream v5.13:
  CakePHP 5.4.2 is required by `#[MetadataRequestToDto]` in `ResourcesUpdateController`
  and `ResourcesAddController`. In `/etc/nixos/pkgs/passbolt-api/package.nix`,
  `vendorHash` was updated to `"sha256-/osH6imJ9mwJVAoquAJde3sZnF5TmFwVyqMYzNEYHkM="`
  after rebuilding on `nixos-micro` and importing into the private VM's store.
  This resolved `404 Not Found` (`InvalidParameterException: Failed to inject dependency`)
  when updating or creating resources via `PUT` or `POST`.
- Local Nix package file: `/etc/nixos/pkgs/passbolt-api/package.nix`
- Current package contains local modifications:
  - CakePHP SSL detector patched to trust HTTPS / reverse-proxy indicators.
  - `ResourceUpdateEmailRedactor` patched so own password-update emails are controlled separately by `send.password.updateSelf`.

The local notification patch preserves shared-password notifications:

- `send.password.update` controls update notifications to other users with access to the password.
- `send.password.updateSelf` controls whether the actor receives their own update notification.
- Production DB currently has `send.password.update=true` and `send.password.updateSelf=false`.
- If user A edits a shared password, user A is skipped and other users with access still receive update notifications.
- If user A edits their own private password, no update email is sent while `send.password.updateSelf=false`.
- The key implementation is in `src/Notification/Email/Redactor/Resource/ResourceUpdateEmailRedactor.php` in the Nix-built package:

```php
$sendUpdateSelfEmail = EmailNotificationSettings::get('send.password.updateSelf');

if ($user->id === $owner->id && !$sendUpdateSelfEmail) {
    continue;
}
```

inside the `foreach ($users as $user)` loop before `addEmail(...)`.

NixOS rebuild usually uses:

```bash
sudo env NIXOS_REBUILD_I_UNDERSTAND_THE_CONSEQUENCES_PLEASE_BREAK_MY_SYSTEM=1 nixos-rebuild switch
```

This workaround has been used because the host can show the known guard:

```text
No passwd entry for user '/run/systemd/system'
```

The Passbolt VM is private-only and may not have general outbound internet. If `nixos-rebuild switch` fails while trying to reach `cache.nixos.org`, retry with substitutes disabled:

```bash
sudo -n nixos-rebuild switch --option substitute false
```

This worked for the notification fix because all dependencies were already present in the local Nix store and only the Passbolt source derivation needed rebuilding.

## Passbolt Runtime

Runtime state:

- `/var/lib/passbolt/config/passbolt.php`
- `/var/lib/passbolt/config/gpg/`
- `/var/lib/passbolt/config/jwt/`
- `/var/lib/passbolt/tmp`
- `/var/lib/passbolt/logs`
- `/var/lib/passbolt/.gnupg`

Services:

- `mysql.service`
- `passbolt-setup.service`
- `phpfpm-passbolt.service`
- `nginx.service`
- `passbolt-email-queue.service`
- `passbolt-email-queue.timer`

Useful health checks:

```bash
ssh -o BatchMode=yes \
  -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35 \
  'systemctl --failed --no-pager; systemctl is-active mysql passbolt-setup phpfpm-passbolt nginx'

ssh -o BatchMode=yes \
  -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35 \
  'app_dir=$(sudo systemctl show -P WorkingDirectory passbolt-setup.service)
   sudo -u passbolt HOME=/var/lib/passbolt GNUPGHOME=/var/lib/passbolt/.gnupg "$app_dir/bin/cake" passbolt healthcheck'

curl -i https://pass.66ton99.org.ua/healthcheck/status.json
```

External endpoints verified on `2026-05-04`:

- `https://pass.66ton99.org.ua/auth/login` returns Passbolt HTML with HTTP `200`.
- `https://pass.66ton99.org.ua/healthcheck/status.json` returns JSON `body: "OK"` with HTTP `200`.
- `http://pass.66ton99.org.ua/auth/login` redirects to HTTPS with HTTP `301`.

Core runtime config state:

```php
'fullBaseUrl' => 'https://pass.66ton99.org.ua',
'secure' => true,
'force' => true,
```

Passbolt active admin user was changed on `2026-05-01`:

- Username/email: `passbolt@66ton99.org.ua`
- Previous active admin username/email was replaced.
- A deleted admin user `github@66ton99.org.ua` still exists in the database history.

## SMTP

SMTP settings were migrated from `/var/lib/passbolt/config/passbolt.php` into the Passbolt database on `2026-05-01`.

- DB table: `organization_settings`
- DB property: `smtp`
- Passbolt reports SMTP settings source as `db`.
- SMTP value is encrypted by Passbolt with the server GPG key.
- SMTP settings plugin endpoints are disabled via:

```php
'passbolt' => [
    'security' => [
        'smtpSettings' => [
            'endpointsDisabled' => true,
        ],
    ],
],
```

- `/var/lib/passbolt/config/passbolt.php` no longer contains `EmailTransport`, `Email`, or `smtp-secret.ini` references.
- `/etc/nixos/passbolt.nix` no longer generates an `Email` sender block for Passbolt; SMTP/sender settings should be managed from Passbolt administration.
- `/var/lib/passbolt/config/smtp-secret.ini` may still exist as an unused old secret file. Do not print or store its contents.

Known SMTP settings, without secrets:

- Host: `smtp.email.eu-amsterdam-1.oci.oraclecloud.com`
- Port: `587`
- TLS: enabled
- Sender: `passbolt@66ton99.org.ua`
- Sender name: `Passbolt`

Current SMTP transport path:

- After the reserved public IP moved to `nixos-micro`, the private-only Passbolt VM lost direct outbound internet and SMTP connections timed out.
- On `2026-05-04`, SMTP delivery was restored through a raw TCP relay on `nixos-micro`.
- `nixos-micro` NixOS module: `/etc/nixos/smtp-proxy.nix`
- Socket: `oci-smtp-proxy.socket`
- Listen: `10.0.0.140:587`
- Upstream: `smtp.email.eu-amsterdam-1.oci.oraclecloud.com:587`
- Passbolt runtime `/etc/hosts` maps `smtp.email.eu-amsterdam-1.oci.oraclecloud.com` to `10.0.0.140`.
- Passbolt `/etc/nixos/configuration.nix` also contains the `networking.extraHosts` entry, but the rebuild could not be activated while general outbound internet was unavailable; runtime `/etc/hosts` was replaced with a mutable copy.
- The stuck recovery email for `ton@66ton99.org.ua` from `2026-05-04 10:35:40` had `send_tries = 4`; that row was reset and sent successfully.
- `cake passbolt show_queued_emails --failed` returned no records after the fix.

GitHub access from the private-only Passbolt VM:

- Direct `github.com:22` and `github.com:443` time out because the VM has no general outbound internet.
- `nixos-micro` runs a raw GitHub SSH TCP relay in `/etc/nixos/smtp-proxy.nix`.
- Socket: `github-ssh-proxy.socket`
- Listen: `10.0.0.140:2222`
- Upstream: `github.com:22`
- `/etc/nixos/pkgs/passbolt-api/repo` is root-owned and uses a root deploy key at `/root/.ssh/github_passbolt_api`.
- The public key was added to GitHub repo `66Ton99/passbolt_api` as read-only deploy key `passbolt-vm-readonly-20260505`.
- The repo local Git config sets:

```bash
core.sshCommand = ssh -i /root/.ssh/github_passbolt_api -p 2222 -o HostName=10.0.0.140 -o HostKeyAlias=github.com -o StrictHostKeyChecking=accept-new
```

- `sudo git pull --ff-only` works from `/etc/nixos/pkgs/passbolt-api/repo` through the `nixos-micro` GitHub SSH relay.

SMTP healthcheck after endpoint disable:

```text
[PASS] The SMTP Settings plugin is enabled.
[PASS] SMTP Settings coherent. You may send a test email to validate them.
[PASS] The SMTP Settings source is: database.
[PASS] The SMTP Settings plugin endpoints are disabled.
[PASS] No custom SSL configuration for SMTP server.
[PASS] No error found.
```

Useful SMTP checks:

```bash
ssh -o BatchMode=yes \
  -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35 \
  'sudo -u passbolt HOME=/var/lib/passbolt GNUPGHOME=/var/lib/passbolt/.gnupg /run/current-system/sw/bin/cake passbolt healthcheck --smtpSettings'

ssh -o BatchMode=yes \
  -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35 \
  'sudo mariadb -N -B passbolt -e "SELECT property, LENGTH(value), created, modified FROM organization_settings WHERE property=\"smtp\";"'
```

## DNS / HTTPS / Reverse Proxy

Domains:

- `pass.66ton99.org.ua`

DNS:

- `pass.66ton99.org.ua` points to reserved public IP `158.101.193.86`.
- Apex `66ton99.org.ua` and `www.66ton99.org.ua` also point to `158.101.193.86`.

Nginx / ACME:

- Passbolt vhost uses `serverName = "pass.66ton99.org.ua";`
- `enableACME = true`
- `forceSSL = true`
- ACME email: `passbolt@66ton99.org.ua`
- Let's Encrypt certificate was issued successfully.
- HTTP redirects to HTTPS.
- Unknown HTTPS hostnames are rejected instead of falling through to Passbolt.

Reverse proxy:

- Public reverse proxy moved to `nixos-micro` on `2026-05-04`.
- `/etc/nixos/reverse-proxy.nix` is no longer imported on the Passbolt host.
- Current reverse proxy config lives in `/etc/nixos/site.nix` on `nixos-micro`.
- Current public reverse proxy mapping includes:

```nix
virtualHosts."pass.66ton99.org.ua" = {
  enableACME = true;
  forceSSL = true;
  locations."/".proxyPass = "http://10.0.0.35";
};
```

- `services.nginx.recommendedProxySettings` already sets the standard proxy headers.
- Do not duplicate `proxy_set_header Host ...` in per-location `extraConfig`; duplicated `Host` headers caused backend Nginx to return `400 Bad Request`.
- Add future backend subdomains in `/etc/nixos/site.nix` on `nixos-micro`, for example:

```nix
virtualHosts."app.66ton99.org.ua" = {
  enableACME = true;
  forceSSL = true;
  locations."/".proxyPass = "http://10.0.0.140:8080";
};
```

- For each proxied subdomain, create DNS `A` or `CNAME` pointing to reserved public IP `158.101.193.86` before running `nixos-rebuild switch`, so ACME HTTP-01 can issue the certificate.

## SSH / Sudo / Security

Passbolt host:

- Root SSH login disabled.
- SSH password auth disabled.
- `ton` has UID `1000`, home `/home/ton`, is in `wheel`, and currently has passwordless sudo.

Important settings:

```nix
services.openssh.settings = {
  PasswordAuthentication = false;
  KbdInteractiveAuthentication = false;
  PermitRootLogin = "no";
};

users.users.ton = {
  isNormalUser = true;
  uid = 1000;
  home = "/home/ton";
  extraGroups = [ "wheel" ];
  openssh.authorizedKeys.keys = [
    "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIMaWBHfvafYA5T47kpRUDMxd/sIGRDMNtyi1SGJK7Ctj ton@mac-m4"
  ];
};

security.sudo.wheelNeedsPassword = false;
```

Non-interactive root/passbolt maintenance commands via SSH should work with `sudo -n`.

Security tradeoff:

- Passwordless sudo is convenient for automation.
- It is weaker if the SSH key or running session is compromised.
- If sudo is changed to require a password, do not ask the user to paste it in chat. Ask them to type it locally in their SSH session.

## Hardware Configuration

Important `/etc/nixos/hardware-configuration.nix` settings on `passbolt`:

```nix
fileSystems."/boot/efi" = {
  device = "/dev/disk/by-uuid/3948-2A5F";
  fsType = "vfat";
};

fileSystems."/" = {
  device = "/dev/sda1";
  fsType = "ext4";
};

boot.initrd.availableKernelModules = [
  "ata_piix" "uhci_hcd" "xen_blkfront" "vmw_pvscsi"
  "virtio_pci" "virtio_scsi" "sd_mod" "sr_mod"
];

boot.initrd.kernelModules = [ "nvme" ];
```


## Managed MySQL

- OCI display name: `mysql`
- Purpose: managed Always Free MySQL for later Passbolt work.
- DB system OCID:
  `ocid1.mysqldbsystem.oc1.eu-amsterdam-1.aaaaaaaa7hqcg7swlxygzgpf5gxvyx76kimfxylv4k7qjy7rlzq6ihghbwna`
- Shape: `MySQL.Free`
- MySQL version: `9.7.0`
- Region: `eu-amsterdam-1`
- Availability domain: `QEGv:eu-amsterdam-1-AD-1`
- Subnet: `public-subnet-a1`
- Hostname:
  `mysql.publica1.vcn04292250.oraclevcn.com`
- Private endpoint:
  `10.0.0.110:3306`
- Port X:
  `33060`
- Last verified state: `ACTIVE`

Admin credentials:

- Admin user: `admin`
- Admin password is stored in macOS Keychain, not in this repository.
- Keychain item:
  - account: `admin`
  - service: `oci-mysql-mysql-admin`
  - label: `OCI MySQL mysql admin`
- Retrieve locally only when needed:

```bash
security find-generic-password -a admin -s oci-mysql-mysql-admin -w
```

Network access:

- MySQL endpoint is private, not public.
- Security list ingress rule added:
  - Source: `10.0.0.35/32`
  - Protocol: TCP
  - Destination port: `3306`
- Security list ingress rule added for SMTP relay:
  - Source: `10.0.0.35/32`
  - Protocol: TCP
  - Destination port: `587`
- Security list ingress rule added for GitHub SSH relay:
  - Source: `10.0.0.35/32`
  - Protocol: TCP
  - Destination port: `2222`
- Connectivity from the Passbolt VM was verified:

```text
Connection to 10.0.0.110 3306 port [tcp/mysql] succeeded!
```

Backup / deletion policy:

- Always Free MySQL backup policy is managed by OCI and cannot be modified with `UpdateDbSystem`.
- Attempting to update backup policy returned:
  `Cannot modify backup policy with 'UpdateDbSystem' operation for an Always Free DB system.`
- Current backup policy:
  - Automatic backups: enabled
  - Retention: `1` day
  - PITR: disabled
  - Soft delete: disabled
  - Backup window start: `18:21` UTC

Useful managed MySQL checks:

```bash
oci mysql db-system get \
  --db-system-id ocid1.mysqldbsystem.oc1.eu-amsterdam-1.aaaaaaaa7hqcg7swlxygzgpf5gxvyx76kimfxylv4k7qjy7rlzq6ihghbwna \
  --profile antonshapka \
  --auth security_token

ssh -o BatchMode=yes \
  -o ProxyCommand='ssh -o UserKnownHostsFile=/Users/ton/oci/known_hosts_micro_reserved -W %h:%p ton@158.101.193.86' \
  -o UserKnownHostsFile=/Users/ton/oci/known_hosts_private \
  ton@10.0.0.35 \
  'nc -vz -w 5 10.0.0.110 3306'
```

## Repo Workflow Notes

- This checkout is upstream Passbolt API source plus local work-in-progress changes used for validation before deployment.
- Do local search, edits, tests, and browser checks here first. Deploy to the server only after local verification.
- Do not create scratch/tooling artefacts inside this repository. In particular, do not create `.playwright-cli/`, `output/`, screenshots, tarballs, browser profiles, logs, or other non-functional files under `passly_api/`.
- Put temporary Playwright/browser output, screenshots, archives, and other scratch files under `/tmp`, `/var/tmp`, or another path outside the repository.
- If implementing or revisiting the "control own password update emails" behavior in this repository, the relevant file is:
  `src/Notification/Email/Redactor/Resource/ResourceUpdateEmailRedactor.php`
- The relevant method is `onSubscribedEvent(Event $event)`.
- The upstream default behavior sends password update notifications to everybody with access to the resource, including the user who performed the update.
- The desired local behavior is:
  - `send.password.update` controls update notifications for other users who can access the resource.
  - `send.password.updateSelf` controls whether the user who updated the password receives their own update notification.
  - In production, `send.password.updateSelf` is saved in the database as `false`, so own edit emails are suppressed while shared-password update emails remain enabled for other users.
- The admin UI/API field is `send_password_updateSelf`.
- The environment override is `PASSBOLT_EMAIL_SEND_PASSWORD_UPDATE_SELF`.
- The label is `When a password is updated, notify the user who updated it.`
- The Ukrainian translation is `Коли пароль оновлено, повідомити користувача, який його оновив.`
- The previous `send.password.updateShared` / `send_password_updateShared` naming was wrong and was removed.
- Local verification for the fixed behavior on `2026-05-06`:
  - PHPUnit targeted suite passed: `71 tests`, `478 assertions`, `2 skipped`, `22 PHPUnit Deprecations`.
  - `git diff --check` passed.
  - Local Playwright screenshot was generated during verification; future screenshots must be written outside the repository, for example under `/tmp`.
- Remote deployment for base notification commit `96add72f87890d31d0965e5d55da9cc3acec5960`:
  - `/etc/nixos/pkgs/passbolt-api/package.nix` used to pin a fixed `rev`; since `2026-05-08`, it uses `lib.cleanSource /etc/nixos/pkgs/passbolt-api/repo` instead.
  - `sudo -n nixos-rebuild switch --option substitute false` succeeded.
  - Production DB setting now reports `send.password.update=true`, `send.password.updateSelf=false`, `sources.database=true`.
- Translation follow-up deployed on `2026-05-06`:
  - Commit: `4b3c174fcf6dc059642a639b22aed27793788c30` (`Translate password update self notification setting`).
  - `webroot/locales/*/common.json` now translates the new UI label instead of leaving English fallback text in non-English locales.
  - `resources/locales/*/default.po` now translates the new validation message instead of leaving English fallback text in non-English locales.
  - `en_UK/default.po` keeps `msgstr ""`, matching the surrounding English catalogue style.
  - `sudo -n nixos-rebuild switch --option substitute false` succeeded; production public locale checks for `uk-UA` and `de-DE` returned translated text.
- UI order follow-up deployed on `2026-05-06`:
  - Commit: `9fce37e4824f52e83d4f708b65ca51f1d9441e68` (`Move password update self notification below create`).
  - The `send_password_updateSelf` / `passwordUpdateSelf` option is ordered immediately after `send_password_create` / `passwordCreate`, so the admin UI displays `When a password is updated, notify the user who updated it.` directly below `When a password is created, notify its creator.`
  - The old DOM id `send-password-update-shared-toggle-button` was replaced with `send-password-update-self-toggle-button`.
  - Production public `api-app.js` order was verified as `passwordCreate < passwordUpdateSelf < passwordUpdate`.
- Remote verification for deployed notification commits through `4b3c174fcf6dc059642a639b22aed27793788c30`:
  - `passbolt-setup`, `phpfpm-passbolt`, and `nginx` were active.
  - `systemctl --failed --no-pager` reported `0 loaded units listed`.
  - `https://pass.66ton99.org.ua/healthcheck/status.json` returned HTTP `200` with body `"OK"`.
  - Public `api-app.js` contains `send_password_updateSelf`, `passwordUpdateSelf`, and `When a password is updated, notify the user who updated it`.
  - Public `api-app.js` does not contain `send_password_updateShared`, `passwordUpdateShared`, or `When a shared password is updated`.
  - `cake passbolt healthcheck` still reports one expected failure because the private-only Passbolt VM cannot reach the upstream Passbolt repository for version checks; database, GPG, metadata, compiled JS, and public healthcheck are OK.
- Folder update self-email follow-up deployed on `2026-05-06`:
  - Commit: `79f39447d406a62b49e0359618d24395302dfc2d` (`Skip folder update email for editor`).
  - `PassboltCe/Folders/src/Notification/Email/UpdateFolderEmailRedactor.php` now skips the operator when building folder update recipients.
  - This suppresses `You edited a folder` / `Ви відредагували теку` emails to the user who edited the folder while preserving folder update notifications to other users with access.
  - Local targeted PHPUnit: `plugins/PassboltCe/Folders/tests/TestCase/Service/Folders/FoldersUpdateServiceTest.php` passed with `6 tests`, `11 assertions`.
  - Production rebuild with `sudo -n nixos-rebuild switch --option substitute false` succeeded; active store path contains the operator skip; public healthcheck returned HTTP `200` / `"OK"`.
- Folder delete self-email follow-up deployed on `2026-05-06`:
  - Commit: `23f8776d1b13ae24f45211d1a2681f5b2fe29ef3` (`Skip folder delete email for editor`).
  - `PassboltCe/Folders/src/Notification/Email/DeleteFolderEmailRedactor.php` now skips the operator when building folder deletion recipients.
  - This suppresses `You deleted a folder` / `Ви видалили теку` emails to the user who deleted the folder while preserving folder deletion notifications to other users with access.
  - Personal folder deletion now queues zero emails because only the operator had access.
  - Local targeted PHPUnit for folder update and delete services passed with `35 tests`, `208 assertions`, `2 PHPUnit Deprecations`; `git diff --check` passed.
  - Production rebuild with `sudo -n nixos-rebuild switch --option substitute false` succeeded; active store path contains the operator skip; public healthcheck returned HTTP `200` / `"OK"`; no failed units; `passbolt-setup`, `phpfpm-passbolt`, and `nginx` active.
  - `cake passbolt show_queued_emails --failed` returned no records after deployment. Recent `Ви відредагували теку` / `Ви видалили теку` rows in the general queue output were already sent before this deployment.
- Folder update self-notification setting deployed on `2026-05-06`:
  - Commit: `c404588d7d0a120e508257859b1ceef3d4d2025b` (`Add folder update self notification setting`).
  - New setting: `send.folder.updateSelf`.
  - API/UI field: `send_folder_updateSelf`.
  - Env var override: `PASSBOLT_EMAIL_SEND_FOLDER_UPDATE_SELF`.
  - Default: `true` for backward compatibility.
  - Admin UI label: `When a folder is updated, notify its creator.`
  - Ukrainian label: `Коли теку оновлено, повідомити її творця.`
  - UI order is `folderCreate`, then `folderUpdateSelf`, then `folderUpdate`, so the new option is directly under `When a folder is created, notify its creator.`
  - `UpdateFolderEmailRedactor` skips the operator only when `send.folder.updateSelf=false`; other users with access still receive folder update notifications when `send.folder.update=true`.
  - Production DB override is `send.folder.update=true`, `send.folder.updateSelf=false`, and `send.password.updateSelf=false`.
  - Local targeted PHPUnit passed with `83 tests`, `497 assertions`, `22 PHPUnit Deprecations`; `git diff --check` passed; locale JSON parse passed; local and public JS order checks passed.
  - Production rebuild with `sudo -n nixos-rebuild switch --option substitute false` succeeded; public healthcheck returned HTTP `200` / `"OK"`; no failed units; `passbolt-setup`, `phpfpm-passbolt`, and `nginx` active; `cake passbolt show_queued_emails --failed` returned no records.
