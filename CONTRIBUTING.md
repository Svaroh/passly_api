# Contributing to Passbolt API

## Prerequisite

* git
* Docker provider ([see supported list](https://ddev.readthedocs.io/en/stable/users/install/docker-installation/))
* [ddev](https://ddev.com/get-started/)
* mkcert, nss library installed ([see](https://docs.ddev.com/en/stable/users/install/ddev-installation/))

## Setup

Quickly spin up development environment with default configuration:
1. Clone passbolt api repo
2. Start ddev
    ```sh
    ddev start
    ```
3. Initiate dev environment (run only once)
    ```sh
    ddev init_passbolt
    ```

All set! Navigate to https://passbolt-api.ddev.site/ and start contributing to passbolt 💪

## Running tests

The test database is already created for you.

To run full test suite:
```
ddev composer test
```

To run a subset of tests:
```
ddev exec -d /var/www/html "vendor/bin/phpunit --filter <TestName>"
```

## Run code quality checks

- Coding style violations: `ddev composer cs-check`
- Autofix coding style errors: `ddev composer cs-fix`
- Static analysis: `ddev composer stan` and `ddev composer psalm`
- All in one command (run PHPStan, Psalm and coding standards check): `ddev analyze`

## Contributing to the Clients

* Browser extension(Firefox, Edge & Chrome): https://github.com/passbolt/passbolt_browser_extension
* Styleguide: https://github.com/passbolt/passbolt_styleguide
* CLI: https://github.com/passbolt/go-passbolt-cli
* Windows desktop application: https://github.com/passbolt/passbolt-windows

## How do I contribute to the translation

For contributing to the translations of this repository, you will need to create an account and propose changes at https://passbolt.crowdin.com/.
