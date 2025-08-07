#!/bin/bash

# ==========================================================================
# WordPress テーマ kei-portfolio テスト実行スクリプト
# ==========================================================================

set -euo pipefail

# スクリプトのディレクトリを取得
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="/var/www/html/wp-content/themes/kei-portfolio"
WP_TESTS_DIR="/tmp/wordpress-tests-lib"
WP_CORE_DIR="/tmp/wordpress/"

# 色付き出力用の定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ヘルプメッセージ
show_help() {
    cat << EOF
WordPress テーマ kei-portfolio テスト実行スクリプト

使用方法: $0 [オプション] [テストタイプ]

テストタイプ:
    all         すべてのテストを実行
    php         PHPUnitテストのみ実行
    js          Jestテストのみ実行
    lint        リンターのみ実行
    coverage    カバレッジレポート生成
    performance パフォーマンステスト
    a11y        アクセシビリティテスト
    ajax        AJAX/API通信テスト

オプション:
    -h, --help      このヘルプメッセージを表示
    -v, --verbose   詳細出力
    -c, --clean     テスト前にクリーンアップを実行
    --setup         WordPress テストスイートをセットアップ

例:
    $0 all                    # すべてのテストを実行
    $0 php --verbose          # PHPテストを詳細出力で実行
    $0 --setup                # テストスイートのセットアップのみ
EOF
}

# ログ出力関数
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# WordPress テストスイートのセットアップ
setup_wp_test_suite() {
    log_info "WordPress テストスイートのセットアップを開始..."

    # 環境変数のチェック
    : ${WP_TESTS_DB_HOST:?環境変数 WP_TESTS_DB_HOST が設定されていません}
    : ${WP_TESTS_DB_NAME:?環境変数 WP_TESTS_DB_NAME が設定されていません}
    : ${WP_TESTS_DB_USER:?環境変数 WP_TESTS_DB_USER が設定されていません}
    : ${WP_TESTS_DB_PASSWORD:?環境変数 WP_TESTS_DB_PASSWORD が設定されていません}

    # データベースが利用可能になるまで待機
    log_info "データベースの接続を待機中..."
    until mariadb -h"${WP_TESTS_DB_HOST}" -u"${WP_TESTS_DB_USER}" -p"${WP_TESTS_DB_PASSWORD}" -e "SELECT 1;" >/dev/null 2>&1; do
        log_info "データベース接続待機中..."
        sleep 3
    done
    log_success "データベース接続確認完了"

    # WordPress テストライブラリのダウンロード
    if [ ! -d "$WP_TESTS_DIR" ]; then
        log_info "WordPress テストライブラリをダウンロード中..."
        git clone --depth=1 --quiet https://github.com/WordPress/wordpress-develop.git /tmp/wordpress-develop
        cp -r /tmp/wordpress-develop/tests/phpunit/includes "$WP_TESTS_DIR"
        rm -rf /tmp/wordpress-develop
    fi

    # WordPress コアのダウンロード
    if [ ! -d "$WP_CORE_DIR" ]; then
        log_info "WordPress コアをダウンロード中..."
        wp core download --path="$WP_CORE_DIR" --quiet
    fi

    # テスト用データベースの作成
    log_info "テスト用データベースを作成中..."
    mariadb -h"${WP_TESTS_DB_HOST}" -u"${WP_TESTS_DB_USER}" -p"${WP_TESTS_DB_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS \`${WP_TESTS_DB_NAME}\`;" || true

    # wp-tests-config.php の作成
    if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
        log_info "wp-tests-config.php を作成中..."
        cat > "$WP_TESTS_DIR/wp-tests-config.php" << EOF
<?php
define('DB_NAME', '${WP_TESTS_DB_NAME}');
define('DB_USER', '${WP_TESTS_DB_USER}');
define('DB_PASSWORD', '${WP_TESTS_DB_PASSWORD}');
define('DB_HOST', '${WP_TESTS_DB_HOST}');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('WP_TESTS_DOMAIN', '${WP_TESTS_DOMAIN:-localhost}');
define('WP_TESTS_EMAIL', '${WP_TESTS_EMAIL:-admin@example.org}');
define('WP_TESTS_TITLE', '${WP_TESTS_TITLE:-Test Blog}');

define('WP_PHP_BINARY', 'php');
define('WPLANG', '');
define('WP_DEBUG', true);

\$table_prefix = 'wptests_';

if ( ! defined( 'WP_TESTS_FORCE_KNOWN_BUGS' ) ) {
    define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
}

define('WP_TESTS_CONFIG_FILE_PATH', __FILE__);

require_once dirname(__FILE__) . '/includes/functions.php';

tests_add_filter('muplugins_loaded', function() {
    require dirname(__FILE__) . '/../../wp-content/themes/kei-portfolio/functions.php';
});

require dirname(__FILE__) . '/includes/bootstrap.php';
EOF
    fi

    log_success "WordPress テストスイートのセットアップが完了しました"
}

# クリーンアップ関数
cleanup() {
    log_info "クリーンアップを実行中..."
    
    cd "$THEME_DIR" || exit 1
    
    # Composer キャッシュクリア
    if command -v composer >/dev/null 2>&1; then
        composer clear-cache 2>/dev/null || true
    fi
    
    # npm キャッシュクリア
    if command -v npm >/dev/null 2>&1; then
        npm cache clean --force 2>/dev/null || true
    fi
    
    # 古いカバレッジファイルの削除
    rm -rf coverage/ reports/ .nyc_output/ || true
    
    log_success "クリーンアップ完了"
}

# PHPテスト実行
run_php_tests() {
    log_info "PHPUnit テストを実行中..."
    
    cd "$THEME_DIR" || exit 1
    
    # Composer 依存関係のインストール
    if [ -f composer.json ]; then
        log_info "Composer 依存関係をインストール中..."
        composer install --no-interaction --prefer-dist --optimize-autoloader
    fi
    
    # PHPUnit 実行
    if [ -f vendor/bin/phpunit ]; then
        ./vendor/bin/phpunit --configuration phpunit.xml "${VERBOSE:+--verbose}"
    else
        phpunit --configuration phpunit.xml "${VERBOSE:+--verbose}"
    fi
    
    log_success "PHPUnit テスト完了"
}

# JavaScriptテスト実行
run_js_tests() {
    log_info "Jest テストを実行中..."
    
    cd "$THEME_DIR" || exit 1
    
    # npm 依存関係のインストール
    if [ -f package.json ]; then
        log_info "npm 依存関係をインストール中..."
        npm ci --silent
    fi
    
    # Jest 実行
    npm run test "${VERBOSE:+-- --verbose}"
    
    log_success "Jest テスト完了"
}

# リンター実行
run_linting() {
    log_info "リンター実行中..."
    
    cd "$THEME_DIR" || exit 1
    
    # PHP CodeSniffer
    log_info "PHP CodeSniffer 実行中..."
    if [ -f composer.json ]; then
        composer run lint:php || LINT_ERRORS=$((${LINT_ERRORS:-0} + 1))
    fi
    
    # ESLint
    log_info "ESLint 実行中..."
    if [ -f package.json ]; then
        npm run lint:js-check || LINT_ERRORS=$((${LINT_ERRORS:-0} + 1))
    fi
    
    # Stylelint
    log_info "Stylelint 実行中..."
    if [ -f package.json ]; then
        npm run lint:css-check || LINT_ERRORS=$((${LINT_ERRORS:-0} + 1))
    fi
    
    if [ "${LINT_ERRORS:-0}" -eq 0 ]; then
        log_success "リンター実行完了 - エラーなし"
    else
        log_error "リンター実行完了 - ${LINT_ERRORS} 個のエラーが見つかりました"
        return 1
    fi
}

# カバレッジレポート生成
run_coverage() {
    log_info "カバレッジレポート生成中..."
    
    cd "$THEME_DIR" || exit 1
    
    # PHP カバレッジ
    log_info "PHP カバレッジ生成中..."
    composer run test:coverage || true
    
    # JavaScript カバレッジ
    log_info "JavaScript カバレッジ生成中..."
    npm run test:coverage || true
    
    log_success "カバレッジレポート生成完了"
    log_info "カバレッジレポート: coverage/html/index.html"
}

# パフォーマンステスト実行
run_performance_tests() {
    log_info "パフォーマンステスト実行中..."
    
    cd "$THEME_DIR" || exit 1
    
    # Lighthouse実行
    if command -v lighthouse >/dev/null 2>&1; then
        log_info "Lighthouse 実行中..."
        npm run performance || true
    else
        log_warning "Lighthouse がインストールされていません"
    fi
    
    log_success "パフォーマンステスト完了"
}

# アクセシビリティテスト実行
run_accessibility_tests() {
    log_info "アクセシビリティテスト実行中..."
    
    cd "$THEME_DIR" || exit 1
    
    # axe-core実行
    log_info "axe-core 実行中..."
    npm run test:a11y || true
    
    # pa11y実行
    log_info "pa11y 実行中..."
    npm run test:pa11y || true
    
    log_success "アクセシビリティテスト完了"
}

# AJAX/APIテスト実行
run_ajax_tests() {
    log_info "AJAX/API通信テスト実行中..."
    
    cd "$THEME_DIR" || exit 1
    
    # Composer 依存関係のインストール
    if [ -f composer.json ]; then
        log_info "Composer 依存関係をインストール中..."
        composer install --no-interaction --prefer-dist --optimize-autoloader
    fi
    
    # PHPUnit でAJAX関連テストのみ実行
    log_info "AJAX通信テスト実行中..."
    if [ -f vendor/bin/phpunit ]; then
        ./vendor/bin/phpunit --configuration phpunit.xml --testsuite group9-ajax-api "${VERBOSE:+--verbose}"
    else
        phpunit --configuration phpunit.xml --testsuite group9-ajax-api "${VERBOSE:+--verbose}"
    fi
    
    log_success "AJAX/API通信テスト完了"
}

# すべてのテスト実行
run_all_tests() {
    log_info "すべてのテストを実行中..."
    
    run_php_tests
    run_js_tests
    run_linting
    run_coverage
    
    log_success "すべてのテスト完了"
}

# メイン処理
main() {
    local CLEAN=false
    local SETUP_ONLY=false
    local TEST_TYPE="all"
    
    # 引数解析
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            -v|--verbose)
                VERBOSE=true
                shift
                ;;
            -c|--clean)
                CLEAN=true
                shift
                ;;
            --setup)
                SETUP_ONLY=true
                shift
                ;;
            all|php|js|lint|coverage|performance|a11y|ajax)
                TEST_TYPE=$1
                shift
                ;;
            *)
                log_error "不明なオプション: $1"
                show_help
                exit 1
                ;;
        esac
    done
    
    # クリーンアップ実行
    if [ "$CLEAN" = true ]; then
        cleanup
    fi
    
    # WordPress テストスイートのセットアップ
    if [ "$TEST_TYPE" != "js" ] && [ "$TEST_TYPE" != "lint" ]; then
        setup_wp_test_suite
    fi
    
    # セットアップのみの場合は終了
    if [ "$SETUP_ONLY" = true ]; then
        exit 0
    fi
    
    # テスト実行
    case $TEST_TYPE in
        all)
            run_all_tests
            ;;
        php)
            run_php_tests
            ;;
        js)
            run_js_tests
            ;;
        lint)
            run_linting
            ;;
        coverage)
            run_coverage
            ;;
        performance)
            run_performance_tests
            ;;
        a11y)
            run_accessibility_tests
            ;;
        ajax)
            run_ajax_tests
            ;;
        *)
            log_error "不明なテストタイプ: $TEST_TYPE"
            show_help
            exit 1
            ;;
    esac
    
    log_success "テスト実行完了!"
}

# スクリプト実行
main "$@"