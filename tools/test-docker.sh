#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="$ROOT/docker-compose.test.yml"

command="${1:-help}"

compose() {
  docker compose -f "$COMPOSE_FILE" "$@"
}

case "$command" in
  install)
    compose run --rm php composer install --no-interaction --prefer-dist --no-progress
    ;;
  unit)
    compose run --rm unit
    ;;
  integration)
    compose run --rm integration
    ;;
  shell)
    compose run --rm php
    ;;
  help|--help|-h)
    echo "Usage: $0 {install|unit|integration|shell}"
    ;;
  *)
    echo "Unknown command: $command" >&2
    echo "Usage: $0 {install|unit|integration|shell}" >&2
    exit 1
    ;;
 esac
