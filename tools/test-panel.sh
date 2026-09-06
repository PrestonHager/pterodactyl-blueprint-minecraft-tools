#!/usr/bin/env bash
set -euo pipefail

COMMAND="${1:-setup}"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUNTIME="$ROOT/.test-panel"
COMPOSE_FILE="$ROOT/tools/test-panel/docker-compose.yml"
COMPOSE_PROJECT="minecraft-tools-test-panel"
export TEST_PANEL_DIR="./.test-panel"
export PANEL_PORT="${PANEL_PORT:-8080}"

compose() {
  docker compose -p "$COMPOSE_PROJECT" --project-directory "$ROOT" -f "$COMPOSE_FILE" "$@"
}

assert_tools() {
  command -v docker >/dev/null 2>&1 || {
    echo "Docker Desktop or Docker Engine is required." >&2
    exit 1
  }
  docker compose version
  docker info >/dev/null 2>&1 || {
    echo "Docker is installed but its engine is not running. Start Docker Desktop and run this command again." >&2
    exit 1
  }
}

sync_extension() {
  local extension="$RUNTIME/extensions/minecraft-tools"
  mkdir -p "$extension" "$RUNTIME/logs"
  rsync -a --delete \
    --exclude '.git/' \
    --exclude '.test-panel/' \
    --exclude 'node_modules/' \
    --exclude 'vendor/' \
    --exclude 'dist/' \
    "$ROOT/" "$extension/"
}

case "$COMMAND" in
  setup)
    assert_tools
    sync_extension
    compose up -d
    compose exec -T -w /blueprint_extensions/minecraft-tools panel blueprint -build
    echo "Test panel: http://localhost:$PANEL_PORT"
    echo 'Create an admin user with: docker compose exec panel php artisan p:user:make'
    ;;
  start)
    assert_tools
    compose up -d
    echo "Test panel: http://localhost:$PANEL_PORT"
    ;;
  stop)
    assert_tools
    compose down
    ;;
  logs)
    assert_tools
    compose logs -f panel
    ;;
  refresh)
    assert_tools
    sync_extension
    compose exec -T -w /blueprint_extensions/minecraft-tools panel blueprint -build
    ;;
  reset)
    assert_tools
    compose down -v
    rm -rf "$RUNTIME"
    echo 'Test panel data and containers removed.'
    ;;
  *)
    echo "Usage: $0 {setup|start|stop|logs|refresh|reset}" >&2
    exit 2
    ;;
esac
