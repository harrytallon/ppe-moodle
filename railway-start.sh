#!/bin/sh
# Install the Moodle database from the CLI on first boot (the web installer is too slow
# for Cloudflare's 100s limit), then hand over to FrankenPHP.
set -e
cd /app
mkdir -p /var/www/moodledata
chown www-data:www-data /var/www/moodledata

installed=$(php -r '
  $d = new PDO("pgsql:host=".getenv("PGHOST").";port=".(getenv("PGPORT") ?: 5432).";dbname=".getenv("PGDATABASE"), getenv("PGUSER"), getenv("PGPASSWORD"));
  echo $d->query("SELECT to_regclass(\x27public.mdl_config\x27)")->fetchColumn() ? "yes" : "no";
' 2>/dev/null || echo error)

if [ "$installed" = "no" ] && [ -n "$MOODLE_ADMIN_PASSWORD" ]; then
  echo "Installing Moodle database..."
  su -s /bin/sh www-data -c "php admin/cli/install_database.php --lang=en --adminuser=admin \
    --adminpass='$MOODLE_ADMIN_PASSWORD' --adminemail='${MOODLE_ADMIN_EMAIL:-admin@example.com}' \
    --fullname='${MOODLE_FULLNAME:-PPE Moodle}' --shortname='${MOODLE_SHORTNAME:-PPE}' --agree-license" \
    || echo "Moodle CLI install failed; starting server anyway"
elif [ "$installed" = "yes" ]; then
  # Pick up new or updated plugins (e.g. qtype_threeshort) without clicking through the admin UI.
  echo "Running Moodle upgrade check..."
  su -s /bin/sh www-data -c "php admin/cli/upgrade.php --non-interactive --allow-unstable" \
    || echo "Moodle upgrade failed; starting server anyway"
else
  echo "Moodle database check: installed=$installed"
fi

# Moodle needs cron every minute (adhoc tasks, scheduled tasks, email).
if [ "$installed" != "error" ]; then
  (
    sleep 30
    while true; do
      su -s /bin/sh www-data -c "php /app/admin/cli/cron.php" >/tmp/moodle-cron.log 2>&1 || true
      sleep 60
    done
  ) &
fi

exec docker-php-entrypoint "$@"
