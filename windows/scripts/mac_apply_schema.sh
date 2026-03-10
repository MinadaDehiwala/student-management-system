#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
SQL_FILE="${SCRIPT_DIR}/schema_updates.sql"

if [[ ! -f "${SQL_FILE}" ]]; then
  echo "Error: schema_updates.sql not found at ${SQL_FILE}" >&2
  exit 1
fi

mysql -h 127.0.0.1 -u campus_user -p'Ga$9vL!2QxR#8tPm' -D campus_db < "${SQL_FILE}"
echo "Schema updates applied to campus_db via campus_user."
