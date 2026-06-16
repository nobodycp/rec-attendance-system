#!/bin/sh
PORT="${PORT:-80}"
curl -fsS "http://127.0.0.1:${PORT}/ping.php" || exit 1
