<?php
define( 'WP_CACHE', true /* Modified by NitroPack */ );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u145483p138673_9' );

/** Database username */
define( 'DB_USER', 'u145483p138673_9' );

/** Database password */
define( 'DB_PASSWORD', 'D*FnK(#(MV27m]1nCQ[07@&5' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'bDpzZWB6O0AtW8cAdOuwP8IbWWSNCMLVZjIVDGYePQmCARMCyAENUtXGN7gi3S21');
define('SECURE_AUTH_KEY',  'N3A0xEcECg8Oqulye6fYMENbq9aBzZ9sTrnaUj0tSkIdZmcyOZjx0u8F3heioGHT');
define('LOGGED_IN_KEY',    'yrg6z9HcAE2V0B6iwtfnx7NeGB0aMA75Xu5nDaV3JekSbkZJETTee6MXnNlvWFIp');
define('NONCE_KEY',        'GpWe2N4EADFtWRlH7eHnKHLLOshZnK70kzTDNQdjRuQGI46GH5PggzitD6Yc62Wt');
define('AUTH_SALT',        'mR7bLVNmXLP5l6wPP7fXWRsqP0VQAcTeIiW6fGvUMN0gtGytQtKZXv03KBOwjUwx');
define('SECURE_AUTH_SALT', 'OmHv1sJj04tIqQqdmHDZVMcNWg0N40j1J5HBkhFAx49r6QuFwIJMMHC80BmzIHZ6');
define('LOGGED_IN_SALT',   'SSpBxVXIpIlisyuw2sADxqgnDyrUWWYxADE8h3rB8YUrm5KukwtnJXY5aue4rOKv');
define('NONCE_SALT',       '0UqcMoOOdF8PJwXH5L59v6bUDGP7G5gmBznIUxzGTxa6yOwbyGCKC5Q43AUzqVkL');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');
define('FS_CHMOD_DIR',0755);
define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */

// Enable WP_DEBUG mode
define('WP_DEBUG', true);

// Enable the display of errors and warnings
define('WP_DEBUG_DISPLAY', true);

// Log errors to a file
define('WP_DEBUG_LOG', true);

// Disable display of errors and warnings
// This is useful when you don't want to display errors on the front end but still want to log them
// define('WP_DEBUG_DISPLAY', false);

// Save queries for analysis
define('SAVEQUERIES', true);

// Script Debug
define('SCRIPT_DEBUG', true);

// Display deprecation notices
define('WP_DEBUG_DISPLAY', true);

// Display deprecation notices for plugin and theme developers
define('WP_DEPRECATED_DISPLAY', true);




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
