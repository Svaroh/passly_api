{
  description = "Passly API development and test shell";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-25.11";
  };

  outputs =
    { nixpkgs, ... }:
    let
      systems = [
        "aarch64-darwin"
        "x86_64-darwin"
        "aarch64-linux"
        "x86_64-linux"
      ];

      forAllSystems = nixpkgs.lib.genAttrs systems;
    in
    {
      devShells = forAllSystems (
        system:
        let
          pkgs = import nixpkgs { inherit system; };

          php = pkgs.php82.buildEnv {
            extensions =
              { enabled, all }:
              enabled
              ++ (with all; [
                curl
                gd
                gnupg
                intl
                ldap
                mbstring
                pdo_mysql
                pdo_pgsql
              ]);
            extraConfig = ''
              date.timezone = UTC
              memory_limit = 1G
            '';
          };
        in
        {
          default = pkgs.mkShell {
            packages = [
              php
              pkgs.php82Packages.composer
              pkgs.nodejs_22
              pkgs.gnupg
              pkgs.mysql84
              pkgs.git
              pkgs.jq
            ];

            shellHook = ''
              export COMPOSER_MEMORY_LIMIT="''${COMPOSER_MEMORY_LIMIT:--1}"
              export XDEBUG_MODE="''${XDEBUG_MODE:-develop}"
              export PATH="$PATH:$PWD/vendor/bin:$PWD/node_modules/.bin"

              export APP_FULL_BASE_URL="''${APP_FULL_BASE_URL:-http://127.0.0.1}"
              export DEBUG="''${DEBUG:-true}"
              export PASSBOLT_GPG_SERVER_KEY_PUBLIC="''${PASSBOLT_GPG_SERVER_KEY_PUBLIC:-config/gpg/unsecure.key}"
              export PASSBOLT_GPG_SERVER_KEY_PRIVATE="''${PASSBOLT_GPG_SERVER_KEY_PRIVATE:-config/gpg/unsecure_private.key}"
              export PASSBOLT_GPG_SERVER_KEY_FINGERPRINT="''${PASSBOLT_GPG_SERVER_KEY_FINGERPRINT:-2FC8945833C51946E937F9FED47B0811573EE67E}"
              export PASSBOLT_REGISTRATION_PUBLIC="''${PASSBOLT_REGISTRATION_PUBLIC:-1}"
              export PASSBOLT_SELENIUM_ACTIVE="''${PASSBOLT_SELENIUM_ACTIVE:-1}"

              export DATASOURCES_DEFAULT_DATABASE="''${DATASOURCES_DEFAULT_DATABASE:-non_existing_database}"
              export DATASOURCES_DEFAULT_USERNAME="''${DATASOURCES_DEFAULT_USERNAME:-user}"
              export DATASOURCES_DEFAULT_PASSWORD="''${DATASOURCES_DEFAULT_PASSWORD:-testing-password}"
              export DATASOURCES_DEFAULT_HOST="''${DATASOURCES_DEFAULT_HOST:-127.0.0.1}"
              export DATASOURCES_TEST_DATABASE="''${DATASOURCES_TEST_DATABASE:-test}"
              export DATASOURCES_TEST_USERNAME="''${DATASOURCES_TEST_USERNAME:-user}"
              export DATASOURCES_TEST_PASSWORD="''${DATASOURCES_TEST_PASSWORD:-testing-password}"
              export DATASOURCES_TEST_HOST="''${DATASOURCES_TEST_HOST:-127.0.0.1}"

              export GNUPGHOME="''${GNUPGHOME:-$PWD/tmp/gnupg}"
              mkdir -p "$GNUPGHOME" tmp/tests tmp/cache/database
              chmod 700 "$GNUPGHOME"
            '';
          };
        }
      );
    };
}
