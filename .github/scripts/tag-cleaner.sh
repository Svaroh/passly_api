#!/usr/bin/env bash
set -euo pipefail

tag="${1:-}"

if [[ -z "$tag" ]]; then
  echo "Error: tag is empty." >&2
  exit 1
fi

if [[ "$tag" =~ ^v?([0-9]+\.[0-9]+\.[0-9]+(-(rc|test)\.[0-9]+)?)$ ]]; then
  echo "${BASH_REMATCH[1]}"
  exit 0
fi

echo "Error: unsupported release tag: $tag" >&2
exit 1
