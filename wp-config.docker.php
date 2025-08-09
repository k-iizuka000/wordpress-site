<?php
/**
 * Docker開発環境用 WordPress設定ファイル
 */

/**
 * Docker環境変数取得関数
 */
function getenv_docker($name, $default) {
    $env = getenv($name);
    return $env !== false ? $env : $default;
}

// ** Database settings ** //
define( 'DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'kei_portfolio_dev') );
define( 'DB_USER', getenv_docker('WORDPRESS_DB_USER', 'wp_user') );
define( 'DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'wp_password') );
define( 'DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'db:3306') );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         'docker-dev-auth-key-change-me-in-production' );
define( 'SECURE_AUTH_KEY',  'docker-dev-secure-auth-key-change-me-in-production' );
define( 'LOGGED_IN_KEY',    'docker-dev-logged-in-key-change-me-in-production' );
define( 'NONCE_KEY',        'docker-dev-nonce-key-change-me-in-production' );
define( 'AUTH_SALT',        'docker-dev-auth-salt-change-me-in-production' );
define( 'SECURE_AUTH_SALT', 'docker-dev-secure-auth-salt-change-me-in-production' );
define( 'LOGGED_IN_SALT',   'docker-dev-logged-in-salt-change-me-in-production' );
define( 'NONCE_SALT',       'docker-dev-nonce-salt-change-me-in-production' );

/**
 * WordPress database table prefix.
 */
$table_prefix = 'wp_dev_';

/**
 * WordPress debugging mode (開発環境用).
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
define( 'WP_ENVIRONMENT_TYPE', 'development' );

/**
 * 環境固有の設定
 */
// テストファイルのアクセス許可（開発環境のみ）
define( 'KEI_PORTFOLIO_TEST_ACCESS', true );
// 緊急修正パッチの有効化（開発環境のみ）
define( 'KEI_PORTFOLIO_EMERGENCY_FIXES', true );
// デバッグログの詳細レベル
define( 'KEI_PORTFOLIO_LOG_LEVEL', 'debug' );
// セキュリティ制限の緩和（開発環境のみ）
define( 'KEI_PORTFOLIO_STRICT_SECURITY', false );

/**
 * メモリ制限
 */
define( 'WP_MEMORY_LIMIT', '256M' );

/**
 * 自動更新を無効化（開発環境）
 */
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );

/**
 * ファイル編集を許可（開発環境）
 */
define( 'DISALLOW_FILE_EDIT', false );
define( 'DISALLOW_FILE_MODS', false );

/**
 * キャッシュ無効化（開発環境）
 */
define( 'WP_CACHE', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';