#!/usr/bin/env bash
set -euo pipefail

APP_DB="campus_db"

mysql_root() {
  if [[ -n "${MYSQL_ROOT_PASSWORD:-}" ]]; then
    mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "$@"
  else
    mysql -u root "$@"
  fi
}

echo "[1/4] Checking Homebrew..."
if ! command -v brew >/dev/null 2>&1; then
  echo "Error: Homebrew is not installed. Install it first from https://brew.sh" >&2
  exit 1
fi

echo "[2/4] Ensuring MySQL is installed via Homebrew..."
if ! brew list mysql >/dev/null 2>&1; then
  brew install mysql
else
  echo "MySQL already installed."
fi

echo "[3/4] Starting MySQL service..."
brew services start mysql >/dev/null

echo "[4/4] Creating database (root local-dev mode)..."
SQL="
CREATE DATABASE IF NOT EXISTS ${APP_DB} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
"

if mysql_root -e "${SQL}"; then
  echo "Setup complete."
  echo "Database: ${APP_DB}"
  echo "Runtime user: root (blank password expected for local setup)"
  echo "Root auth : MYSQL_ROOT_PASSWORD env var used if set; otherwise socket login with -u root"
else
  echo "MySQL setup failed. Ensure root auth is correct."
  echo "Tip: export MYSQL_ROOT_PASSWORD='your-root-password' and re-run this script."
  exit 1
fi
