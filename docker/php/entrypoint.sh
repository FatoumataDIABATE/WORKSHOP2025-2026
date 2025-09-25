#!/usr/bin/env bash
set -e
php bin/console doctrine:database:create --if-not-exists --no-interaction || true
php bin/console doctrine:migrations:migrate --no-interaction || true
if [ "${APP_ENV}" = "prod" ]; then php bin/console cache:warmup --no-interaction || true; fi
exec "$@"
