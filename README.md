# Passly API

Passly API is a Svaroh-maintained fork of `passbolt/passbolt_api`, the open
source password manager API for teams.

## Rebranding Scope

- User-facing product naming in this fork uses `Passly`.
- Runtime compatibility names such as the CakePHP `passbolt` configuration key,
  `PASSBOLT_*` environment variables, PHP namespaces, plugin directories, and
  database identifiers are intentionally preserved unless a dedicated migration
  changes them.
- Upstream copyright notices, dependency names, and the AGPL license text are
  preserved.

## License

This program is free software: you can redistribute it and/or modify it under the
terms of the GNU Affero General Public License (AGPL) as published by the Free
Software Foundation version 3.

The name "Passbolt" is a registered trademark of Passbolt SA, and Passbolt SA
hereby declines to grant a trademark license to "Passbolt" pursuant to the GNU
Affero General Public License version 3 Section 7(e), without a separate
agreement with Passbolt SA.

Passly is a separate Svaroh fork name. This fork does not claim sponsorship,
endorsement, or trademark rights from Passbolt SA.

## Development

Use the commands documented in the workspace instructions for this checkout:

```bash
composer test
composer cs-check
composer stan
composer psalm
npm run lint
```

Run tests only locally. Do not run automated tests on production servers.
