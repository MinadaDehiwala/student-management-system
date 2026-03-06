#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
SQL_FILE="${PROJECT_DIR}/database.sql"

mysql_root() {
  if [[ -n "${MYSQL_ROOT_PASSWORD:-}" ]]; then
    mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "$@"
  else
    mysql -u root "$@"
  fi
}

if [[ ! -f "${SQL_FILE}" ]]; then
  echo "Error: database.sql not found at ${SQL_FILE}" >&2
  exit 1
fi

echo "Importing ${SQL_FILE} into campus_db..."
if mysql_root campus_db < "${SQL_FILE}"; then
  echo "Import successful."
else
  echo "Import failed."
  echo "Check root authentication and make sure campus_db exists (run ./scripts/mac_mysql_setup.sh first)."
  exit 1
fi
