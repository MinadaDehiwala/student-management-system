#!/usr/bin/env bash
set -euo pipefail

if pgrep -f "php -S localhost:8000" >/dev/null 2>&1; then
  pkill -f "php -S localhost:8000"
  echo "Stopped PHP dev server on localhost:8000"
else
  echo "No PHP dev server process found for localhost:8000"
fi
