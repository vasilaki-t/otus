#!/usr/bin/env bash
#
# deploy.sh — thin convenience wrapper around the one-command Envoy deploy.
#
# Usage:
#   ./deploy.sh                       # deploy using env defaults
#   ./deploy.sh --pretend             # dry-run: print commands, touch nothing
#   DEPLOY_BRANCH=v1.2.0 ./deploy.sh  # deploy a specific tag/branch
#
# All DEPLOY_* variables are read by Envoy.blade.php (@setup block).
set -euo pipefail

cd "$(dirname "$0")"

echo "==> Deploying otus"
echo "    host:   ${DEPLOY_HOST:-deploy@your-prod-host}"
echo "    branch: ${DEPLOY_BRANCH:-main}"
echo "    path:   ${DEPLOY_PATH:-/var/www/otus}"

exec vendor/bin/envoy run deploy "$@"
