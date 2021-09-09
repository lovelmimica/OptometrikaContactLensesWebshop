<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'optometrika_webshop' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Tgr8g6>%wzDIqnXpKl_r.i<DR)+DGQ;Fp7wC2VOOg._D[0h^Om8NY,+jf`QG#@pn' );
define( 'SECURE_AUTH_KEY',  'M1/AhEs/JouwAzdurl8`N~vH{lR5t.c0snKrZZvkfVu=#i[|W>Wg47nu{d`)kAT-' );
define( 'LOGGED_IN_KEY',    '.?J*.+hus[ro5uF1o9l$i_0!T;3P0xtq1V`8&Od+f^qn&H`,N jB^u(09QsHM_4{' );
define( 'NONCE_KEY',        ';bhUu$&|Nc[ 5U8EME?97:r5WZ2Q#y/K3[)My+:](<Yv,(mvc?Jr{fgXUG8h~:yb' );
define( 'AUTH_SALT',        ';X}r]K3ODPjcg5vdOr{SmPe*:_H&!czG9Y yLFr~dD4_O0%x|aa)wN5fgL>z6&$U' );
define( 'SECURE_AUTH_SALT', 'By4!+P.g<.v|c @PI}]_9|Y264LL@D_%S<nNpEsk]h1q}P9MWgg%Fr4%*a2vM16x' );
define( 'LOGGED_IN_SALT',   'OrS- (xwf20e6(Kfs1w%1 Un.[9DMUBQH%1cg0f!zk6g;Ez<b4L>_z%:^IY{c=,C' );
define( 'NONCE_SALT',       'X; ]RDfxnb;-sjP[O5c(i1Rf }n2jBt?T1396Z|%2<O47_BI{AFW7zIC;|9A|jXY' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
