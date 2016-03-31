<?php

/** Test mode Y/N */
$testing = false ? "_testing" : "";
define('DEBUGGING', 'f');


// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'xgipubli_sc2016');

/** MySQL database username */
define('DB_USER', 'xgipubli_sc2016');

/** MySQL database password */
define('DB_PASSWORD', '2EKGDdppWcFeq6K');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');



/*************************************/
/** No need to edit below this line **/
/*************************************/


/**  Setup Error Reporting */
ini_set('error_reporting', E_ALL|E_STRICT);
ini_set('display_errors', 1);


?>