<?php  // Moodle configuration file (all environment-specific values come from env vars)

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = getenv('PGHOST');
$CFG->dbname    = getenv('PGDATABASE');
$CFG->dbuser    = getenv('PGUSER');
$CFG->dbpass    = getenv('PGPASSWORD');
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => getenv('PGPORT') ?: 5432,
  'dbsocket' => 0,
);

$CFG->wwwroot   = getenv('MOODLE_WWWROOT') ?: 'https://ppe-moodle.250-hfx.xyz';
$CFG->dataroot  = getenv('MOODLE_DATAROOT') ?: '/var/www/moodledata';
$CFG->admin     = 'admin';
$CFG->directorypermissions = 02777;

// TLS is terminated by Cloudflare / Railway in front of the container.
$CFG->sslproxy  = true;

$CFG->passwordsaltmain = getenv('MOODLE_PASSWORD_SALT') ?: '';

require_once(__DIR__ . '/lib/setup.php');
