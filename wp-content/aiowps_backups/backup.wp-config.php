<?php
/** 
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information by
 * visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress_4');

/** MySQL database username */
define('DB_USER', 'wordpress_1');

/** MySQL database password */
define('DB_PASSWORD', '#Vf8X2iz4P');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link http://api.wordpress.org/secret-key/1.1/ WordPress.org secret-key service}
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'f!6CZglT0nyT#C0@2oWpmiKUYpycgGI%NPjnohC2nS5zAfQ4Mr*@UtHWBIyt0NrM');
define('SECURE_AUTH_KEY',  'uU7gCDg6VTWwC)!IvZ2k%bYXY879UFKGuGHRQLU)HkPtwr6FK#Y43xm01qgkkrVV');
define('LOGGED_IN_KEY',    'cOY#nYtz&YBzeDCmluYL3OES93PAH0aVMjkhF&BDByAeMB0c@uI!72LUrKkphAa(');
define('NONCE_KEY',        'KZ(K!1!uOwrSyXmkiR0r@)6CVVyROP&!bkFWxzOuIgp@^0CJ&V7dHHUekmuvc12f');
define('AUTH_SALT',        'Y)dAdCzA8JXedvsHG3u&4zrcS*F39jXm&ZT9VqjWgu0Z(6Tl55ow(@)HhCcSTtk#');
define('SECURE_AUTH_SALT', '0bCbpCieT!OhL4!gmhOaComs($@rj^*KiZRU2!wH2gTMIi75PcS8SwUuTUdABe#1');
define('LOGGED_IN_SALT',   'u10MlS1^(1nai2^1mK&oFz3OZyY&eAAeClyRhxWsegGxrU8lNaCQu$afzfSK$Ig1');
define('NONCE_SALT',       'O*3$*v4uDCiNWf1L)seJi5rDk*RcgrijTXvJgUblTqQS0DMHdSUTNLHKwiE*2Q62');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', 'es_ES');

define ('FS_METHOD', 'direct');

define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

//--- disable auto upgrade
define( 'AUTOMATIC_UPDATER_DISABLED', true );



?>
