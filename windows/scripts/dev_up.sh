#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

require_command() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Error: required command '$1' not found." >&2
    exit 1
  fi
}

require_command php
require_command mysql
require_command brew

cd "${PROJECT_DIR}"

chmod +x scripts/mac_mysql_setup.sh scripts/mac_import_sql.sh
chmod +x scripts/mac_apply_schema.sh

echo "[1/4] Starting MySQL service..."
brew services start mysql >/dev/null || true

echo "[2/4] Ensuring database and app user..."
if ./scripts/mac_mysql_setup.sh; then
  root_setup_ok=true
else
  root_setup_ok=false
fi

echo "[3/4] Applying schema and seed..."
if [[ "${root_setup_ok}" == "true" ]]; then
  ./scripts/mac_import_sql.sh
else
  echo "Root setup unavailable. Falling back to campus_user schema apply..."
  ./scripts/mac_apply_schema.sh
fi

echo "[4/4] Verifying app DB connection..."
mysql -h 127.0.0.1 -u campus_user -p'Ga$9vL!2QxR#8tPm' -D campus_db -e "SELECT COUNT(*) AS students FROM students;"

echo ""
echo "Student Management System is starting on http://localhost:8000"
echo "Login page: http://localhost:8000/login.php"
exec php -S localhost:8000
