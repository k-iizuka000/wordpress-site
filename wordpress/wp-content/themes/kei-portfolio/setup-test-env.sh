#!/bin/bash

# テスト環境セットアップスクリプト
# kei-portfolioテーマのPHPUnit/Jest テスト環境をセットアップします
#
# 使用法:
#   ./setup-test-env.sh [--docker|--local] [--skip-composer] [--skip-npm]
#
# オプション:
#   --docker         Docker環境向けの設定
#   --local          ローカル環境向けの設定（デフォルト）
#   --skip-composer  Composer依存関係のインストールをスキップ
#   --skip-npm       npm依存関係のインストールをスキップ
#   --help           このヘルプメッセージを表示

set -euo pipefail

# カラー定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 設定値
DOCKER_MODE=false
SKIP_COMPOSER=false
SKIP_NPM=false
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$SCRIPT_DIR"

# ヘルプメッセージ
show_help() {
    cat << EOF
テスト環境セットアップスクリプト

使用法:
    $0 [オプション]

オプション:
    --docker         Docker環境向けの設定
    --local          ローカル環境向けの設定（デフォルト）
    --skip-composer  Composer依存関係のインストールをスキップ
    --skip-npm       npm依存関係のインストールをスキップ
    --help           このヘルプメッセージを表示

例:
    $0 --docker                # Docker環境用にセットアップ
    $0 --local --skip-npm      # ローカル環境でComposerのみインストール
    $0                         # デフォルト（ローカル環境）でセットアップ
EOF
}

# 引数解析
while [[ $# -gt 0 ]]; do
    case $1 in
        --docker)
            DOCKER_MODE=true
            shift
            ;;
        --local)
            DOCKER_MODE=false
            shift
            ;;
        --skip-composer)
            SKIP_COMPOSER=true
            shift
            ;;
        --skip-npm)
            SKIP_NPM=true
            shift
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            echo -e "${RED}エラー: 不明なオプション $1${NC}"
            show_help
            exit 1
            ;;
    esac
done

# ログ関数
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

# 必要なコマンドの存在確認
check_requirements() {
    log_info "必要なコマンドの確認中..."
    
    local missing_commands=()
    
    if [[ "$SKIP_COMPOSER" == "false" ]]; then
        if ! command -v composer &> /dev/null && ! [[ -f "$THEME_DIR/composer.phar" ]]; then
            missing_commands+=("composer")
        fi
    fi
    
    if [[ "$SKIP_NPM" == "false" ]]; then
        if ! command -v npm &> /dev/null; then
            missing_commands+=("npm")
        fi
    fi
    
    if [[ ${#missing_commands[@]} -gt 0 ]]; then
        log_error "以下のコマンドが見つかりません: ${missing_commands[*]}"
        log_info "Composerがない場合は --skip-composer オプションを使用してください"
        log_info "npmがない場合は --skip-npm オプションを使用してください"
        exit 1
    fi
    
    log_success "必要なコマンドが確認できました"
}

# Composerの存在確認とインストール
setup_composer() {
    if [[ "$SKIP_COMPOSER" == "true" ]]; then
        log_info "Composerのセットアップをスキップしました"
        return
    fi
    
    log_info "Composerの設定中..."
    
    # Composerのインストール確認
    if ! command -v composer &> /dev/null; then
        if [[ ! -f "$THEME_DIR/composer.phar" ]]; then
            log_info "Composerをダウンロードしています..."
            curl -sS https://getcomposer.org/installer | php -- --install-dir="$THEME_DIR"
            
            if [[ ! -f "$THEME_DIR/composer.phar" ]]; then
                log_error "Composerのダウンロードに失敗しました"
                exit 1
            fi
        fi
        COMPOSER_CMD="php $THEME_DIR/composer.phar"
    else
        COMPOSER_CMD="composer"
    fi
    
    log_success "Composer設定完了: $COMPOSER_CMD"
}

# PHP依存関係のインストール
install_php_dependencies() {
    if [[ "$SKIP_COMPOSER" == "true" ]]; then
        log_info "PHP依存関係のインストールをスキップしました"
        return
    fi
    
    log_info "PHP依存関係をインストール中..."
    
    cd "$THEME_DIR"
    
    # composer.jsonの存在確認
    if [[ ! -f "composer.json" ]]; then
        log_error "composer.jsonが見つかりません: $THEME_DIR"
        exit 1
    fi
    
    # Composer install実行
    log_info "composer installを実行中..."
    $COMPOSER_CMD install --no-interaction --prefer-dist
    
    # WordPressコーディング規約の設定
    if [[ -f "vendor/bin/phpcs" ]]; then
        log_info "WordPressコーディング規約を設定中..."
        ./vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs
        log_success "WordPressコーディング規約設定完了"
    fi
    
    log_success "PHP依存関係のインストール完了"
}

# Node.js依存関係のインストール
install_node_dependencies() {
    if [[ "$SKIP_NPM" == "true" ]]; then
        log_info "Node.js依存関係のインストールをスキップしました"
        return
    fi
    
    log_info "Node.js依存関係をインストール中..."
    
    cd "$THEME_DIR"
    
    # package.jsonの存在確認
    if [[ ! -f "package.json" ]]; then
        log_error "package.jsonが見つかりません: $THEME_DIR"
        exit 1
    fi
    
    # Node.jsバージョン確認
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node --version | sed 's/v//')
        REQUIRED_VERSION="18.0.0"
        
        if ! printf '%s\n%s\n' "$REQUIRED_VERSION" "$NODE_VERSION" | sort -V -C; then
            log_warning "Node.js v$NODE_VERSION が検出されました。v$REQUIRED_VERSION 以上を推奨します"
        fi
    fi
    
    # npm install実行
    log_info "npm installを実行中..."
    npm install
    
    log_success "Node.js依存関係のインストール完了"
}

# WordPress Test Suiteのセットアップ
setup_wordpress_test_suite() {
    log_info "WordPress Test Suiteのセットアップ中..."
    
    # 環境変数の設定確認
    if [[ "$DOCKER_MODE" == "true" ]]; then
        # Docker環境の場合
        export WP_TESTS_DIR="/tmp/wordpress-tests-lib"
        export WP_TESTS_DB_HOST="db-test"
        export WP_TESTS_DB_NAME="wp_test_suite"
        export WP_TESTS_DB_USER="wp_test_user"
        export WP_TESTS_DB_PASSWORD="wp_test_password"
        
        log_info "Docker環境用の設定を適用しました"
        log_info "WP_TESTS_DIR: $WP_TESTS_DIR"
        log_info "WP_TESTS_DB_HOST: $WP_TESTS_DB_HOST"
    else
        # ローカル環境の場合
        WP_TESTS_DIR="${WP_TESTS_DIR:-/tmp/wordpress-tests-lib}"
        export WP_TESTS_DIR
        
        log_info "ローカル環境用の設定を適用しました"
        log_info "WP_TESTS_DIR: $WP_TESTS_DIR"
        log_info "WordPress Test Suiteが必要な場合は、以下のコマンドでインストールできます:"
        log_info "  bash bin/install-wp-tests.sh wp_test_suite root '' localhost latest"
    fi
    
    # テストディレクトリの作成
    mkdir -p "$THEME_DIR/coverage/html"
    mkdir -p "$THEME_DIR/coverage/js"
    mkdir -p "$THEME_DIR/tests/js"
    
    log_success "WordPress Test Suite設定完了"
}

# テスト設定の検証
verify_test_setup() {
    log_info "テスト設定の検証中..."
    
    local errors=()
    
    # PHPUnit設定確認
    if [[ ! -f "$THEME_DIR/phpunit.xml" ]]; then
        errors+=("phpunit.xmlが見つかりません")
    fi
    
    # Jest設定確認
    if [[ ! -f "$THEME_DIR/jest.config.js" ]]; then
        errors+=("jest.config.jsが見つかりません")
    fi
    
    # テストbootstrap確認
    if [[ ! -f "$THEME_DIR/tests/bootstrap.php" ]]; then
        errors+=("tests/bootstrap.phpが見つかりません")
    fi
    
    # テストディレクトリ確認
    if [[ ! -d "$THEME_DIR/tests" ]]; then
        errors+=("testsディレクトリが見つかりません")
    fi
    
    # Composer依存関係確認（スキップしていない場合）
    if [[ "$SKIP_COMPOSER" == "false" ]] && [[ ! -d "$THEME_DIR/vendor" ]]; then
        errors+=("vendorディレクトリが見つかりません - Composerのインストールが必要です")
    fi
    
    # npm依存関係確認（スキップしていない場合）
    if [[ "$SKIP_NPM" == "false" ]] && [[ ! -d "$THEME_DIR/node_modules" ]]; then
        errors+=("node_modulesディレクトリが見つかりません - npmのインストールが必要です")
    fi
    
    if [[ ${#errors[@]} -gt 0 ]]; then
        log_error "テスト設定に以下の問題があります:"
        for error in "${errors[@]}"; do
            log_error "  - $error"
        done
        exit 1
    fi
    
    log_success "テスト設定の検証完了"
}

# テスト実行例の表示
show_test_examples() {
    log_info "テスト実行例:"
    echo ""
    
    if [[ "$SKIP_COMPOSER" == "false" ]]; then
        echo -e "${GREEN}PHP テスト:${NC}"
        echo "  # 構文チェックのみ（独立実行可能）"
        echo "  composer test:syntax"
        echo ""
        echo "  # WordPressコーディング規約チェック"
        echo "  composer test:standards"
        echo ""
        echo "  # 全PHPテスト実行（WordPress Test Suite必要）"
        echo "  composer test:all"
        echo ""
        echo "  # 特定のテストグループ実行"
        echo "  composer test:group1    # PHP構文チェック"
        echo "  composer test:group10   # セキュリティ・パフォーマンス"
        echo ""
        echo "  # カバレッジレポート生成"
        echo "  composer test:coverage"
        echo ""
    fi
    
    if [[ "$SKIP_NPM" == "false" ]]; then
        echo -e "${GREEN}JavaScript テスト:${NC}"
        echo "  # 全Jestテスト実行"
        echo "  npm test"
        echo ""
        echo "  # カバレッジレポート付きテスト"
        echo "  npm run test:coverage"
        echo ""
        echo "  # 特定のテストファイル実行"
        echo "  npm run test:main             # main.js テスト"
        echo "  npm run test:contact-form     # コンタクトフォーム テスト"
        echo ""
        echo "  # 継続的テスト実行"
        echo "  npm run test:watch"
        echo ""
    fi
    
    echo -e "${GREEN}コードチェック:${NC}"
    if [[ "$SKIP_COMPOSER" == "false" ]]; then
        echo "  composer lint           # PHP構文・規約チェック"
        echo "  composer analyze        # 静的解析（PHPStan）"
    fi
    if [[ "$SKIP_NPM" == "false" ]]; then
        echo "  npm run lint            # JavaScript/CSS lint"
        echo "  npm run format          # コードフォーマット"
    fi
}

# メイン実行部分
main() {
    log_info "kei-portfolio テーマ テスト環境セットアップを開始します"
    log_info "テーマディレクトリ: $THEME_DIR"
    log_info "Docker モード: $DOCKER_MODE"
    
    # 前提条件チェック
    check_requirements
    
    # Composerセットアップ
    setup_composer
    
    # 依存関係インストール
    install_php_dependencies
    install_node_dependencies
    
    # WordPress Test Suite設定
    setup_wordpress_test_suite
    
    # 設定検証
    verify_test_setup
    
    # 完了メッセージ
    echo ""
    log_success "テスト環境のセットアップが完了しました！"
    echo ""
    
    # テスト実行例の表示
    show_test_examples
    
    echo ""
    log_info "詳細なテスト実行方法については README-TESTING.md を参照してください"
    
    if [[ "$DOCKER_MODE" == "true" ]]; then
        echo ""
        log_info "Docker環境では、コンテナ内でテストを実行してください:"
        log_info "  docker-compose exec wordpress bash"
        log_info "  cd /var/www/html/wp-content/themes/kei-portfolio"
    fi
}

# スクリプト実行
main "$@"