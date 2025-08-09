<?php
/**
 * 本番環境用 WordPress設定ファイル（例）
 * 本番環境では、この設定を wp-config.php として使用してください。
 */

// ** Database settings ** //
define( 'DB_NAME', 'your_production_database' );
define( 'DB_USER', 'your_production_user' );
define( 'DB_PASSWORD', 'your_production_password' );
define( 'DB_HOST', 'your_production_host' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**
 * Authentication unique keys and salts.
 * これらは必ず本番環境用の値に変更してください
 * https://api.wordpress.org/secret-key/1.1/salt/
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/**
 * WordPress database table prefix.
 */
$table_prefix = 'wp_';

/**
 * 本番環境設定（セキュリティ重視）
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', true );  // エラーログは記録
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );
define( 'WP_ENVIRONMENT_TYPE', 'production' );

/**
 * 環境固有の設定（本番環境）
 */
// テストファイルのアクセス拒否
define( 'KEI_PORTFOLIO_TEST_ACCESS', false );
// 緊急修正パッチの無効化
define( 'KEI_PORTFOLIO_EMERGENCY_FIXES', false );
// エラーログのレベルを制限
define( 'KEI_PORTFOLIO_LOG_LEVEL', 'error' );
// セキュリティ制限を厳格化
define( 'KEI_PORTFOLIO_STRICT_SECURITY', true );

/**
 * セキュリティ強化設定
 */
// ファイル編集を禁止
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', true );
// 自動更新の制御
define( 'AUTOMATIC_UPDATER_DISABLED', false );
define( 'WP_AUTO_UPDATE_CORE', true );  // セキュリティアップデートは許可

/**
 * パフォーマンス最適化
 */
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_CACHE', true );
// リビジョン数を制限
define( 'WP_POST_REVISIONS', 5 );
// ゴミ箱の自動削除（30日）
define( 'EMPTY_TRASH_DAYS', 30 );

/**
 * SSL設定
 */
if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) {
    define( 'FORCE_SSL_ADMIN', true );
}

/**
 * ログファイルの場所設定
 */
if (!defined('KEI_PORTFOLIO_DEBUG_LOG')) {
    define('KEI_PORTFOLIO_DEBUG_LOG', WP_CONTENT_DIR . '/logs/error.log');
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';