#!/bin/bash
# グループ1（PHP構文チェック）テスト実行スクリプト
# 
# このスクリプトは完全に独立して実行可能で、外部依存はありません。
# テスト設計書のグループ1要件に基づいて実装されています。
#
# 使用方法:
#   ./run-group1-tests.sh
#
# 環境変数:
#   SHOW_PHPCS_WARNINGS=1  # PHP_CodeSnifferの警告を表示
#   VERBOSE=1              # 詳細出力

set -e

# 設定
THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TESTS_DIR="${THEME_DIR}/tests"
TEMP_DIR="/tmp/kei-portfolio-tests-$$"

# 色付き出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# クリーンアップ関数
cleanup() {
    if [[ -d "$TEMP_DIR" ]]; then
        rm -rf "$TEMP_DIR"
    fi
}
trap cleanup EXIT

# テスト開始
log_info "グループ1: PHP構文チェック関連テスト開始"
log_info "テーマディレクトリ: $THEME_DIR"
log_info "実行時間目安: 30秒"
echo

# 1.1 PHP構文チェック（全PHPファイル）
log_info "1.1 PHP構文チェック（全PHPファイル）実行中..."
ERROR_COUNT=0
TOTAL_FILES=0

find "$THEME_DIR" -name "*.php" \
    -not -path "*/vendor/*" \
    -not -path "*/node_modules/*" \
    -not -path "*/tests/*" | while read -r file; do
    
    TOTAL_FILES=$((TOTAL_FILES + 1))
    
    if [[ "$VERBOSE" == "1" ]]; then
        echo "  チェック中: $(basename "$file")"
    fi
    
    if ! php -l "$file" > /dev/null 2>&1; then
        log_error "構文エラー: $file"
        php -l "$file"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
done

if [[ $ERROR_COUNT -eq 0 ]]; then
    log_success "PHP構文チェック完了 - エラーなし ($TOTAL_FILES ファイル)"
else
    log_error "PHP構文チェック失敗 - $ERROR_COUNT 個のエラー"
    exit 1
fi
echo

# 1.2 重要ファイルの存在確認
log_info "1.2 重要ファイルの存在確認..."
CRITICAL_FILES=(
    "functions.php"
    "index.php"
    "style.css"
    "header.php"
    "footer.php"
    "front-page.php"
    "404.php"
    "archive-project.php"
    "single-project.php"
    "page-templates/page-about.php"
    "page-templates/page-portfolio.php"
    "page-templates/page-skills.php"
    "page-templates/page-contact.php"
    "inc/setup.php"
    "inc/enqueue.php"
    "inc/customizer.php"
    "inc/widgets.php"
    "inc/post-types.php"
    "inc/ajax-handlers.php"
    "inc/optimizations.php"
)

MISSING_FILES=()

for file in "${CRITICAL_FILES[@]}"; do
    if [[ ! -f "$THEME_DIR/$file" ]]; then
        MISSING_FILES+=("$file")
        log_error "重要ファイルが見つかりません: $file"
    elif [[ "$VERBOSE" == "1" ]]; then
        echo "  ✓ $file"
    fi
done

if [[ ${#MISSING_FILES[@]} -eq 0 ]]; then
    log_success "重要ファイル確認完了 - 全て存在 (${#CRITICAL_FILES[@]} ファイル)"
else
    log_error "重要ファイル確認失敗 - ${#MISSING_FILES[@]} 個のファイルが見つかりません"
    exit 1
fi
echo

# 1.3 テンプレートパーツの確認
log_info "1.3 テンプレートパーツの確認..."
TEMPLATE_PARTS=(
    "template-parts/about/hero.php"
    "template-parts/about/engineer-history.php"
    "template-parts/about/personality-section.php"
    "template-parts/about/cycling-passion.php"
    "template-parts/portfolio/portfolio-hero.php"
    "template-parts/portfolio/featured-projects.php"
    "template-parts/portfolio/all-projects.php"
    "template-parts/portfolio/technical-approach.php"
    "template-parts/skills/skills-hero.php"
    "template-parts/skills/programming-languages.php"
    "template-parts/skills/frameworks-tools.php"
    "template-parts/skills/specialized-skills.php"
    "template-parts/skills/learning-approach.php"
    "template-parts/contact/hero.php"
    "template-parts/contact/contact-form.php"
    "template-parts/contact/contact-info.php"
)

MISSING_TEMPLATES=()

for template in "${TEMPLATE_PARTS[@]}"; do
    if [[ ! -f "$THEME_DIR/$template" ]]; then
        MISSING_TEMPLATES+=("$template")
        log_warning "テンプレートパーツが見つかりません: $template"
    elif [[ "$VERBOSE" == "1" ]]; then
        echo "  ✓ $template"
    fi
done

if [[ ${#MISSING_TEMPLATES[@]} -eq 0 ]]; then
    log_success "テンプレートパーツ確認完了 - 全て存在 (${#TEMPLATE_PARTS[@]} ファイル)"
else
    log_warning "テンプレートパーツ確認 - ${#MISSING_TEMPLATES[@]} 個のファイルが見つかりません（警告のみ）"
fi
echo

# 1.4 基本的なWordPressコーディング規約チェック
log_info "1.4 基本的なWordPressコーディング規約チェック..."

# デバッグコードの検出
log_info "  デバッグコードの検出..."
DEBUG_FOUND=0

find "$THEME_DIR" -name "*.php" \
    -not -path "*/vendor/*" \
    -not -path "*/node_modules/*" \
    -not -path "*/tests/*" | while read -r file; do
    
    if grep -q -E "(var_dump|print_r|debug_backtrace|debug_print_backtrace|var_export)\s*\(" "$file"; then
        log_warning "デバッグコードが見つかりました: $file"
        if [[ "$VERBOSE" == "1" ]]; then
            grep -n -E "(var_dump|print_r|debug_backtrace|debug_print_backtrace|var_export)\s*\(" "$file"
        fi
        DEBUG_FOUND=1
    fi
done

if [[ $DEBUG_FOUND -eq 0 ]]; then
    log_success "デバッグコード検出 - 問題なし"
else
    log_warning "デバッグコード検出 - 本番環境では削除を推奨"
fi

# 非推奨関数の検出
log_info "  非推奨関数の検出..."
DEPRECATED_FOUND=0

DEPRECATED_FUNCTIONS=(
    "get_theme_data"
    "get_themes"
    "get_current_theme"
    "clean_pre"
    "add_custom_background"
    "add_custom_image_header"
    "wp_convert_bytes_to_hr"
    "mysql_escape_string"
    "wp_specialchars"
    "attribute_escape"
    "clean_url"
)

find "$THEME_DIR" -name "*.php" \
    -not -path "*/vendor/*" \
    -not -path "*/node_modules/*" \
    -not -path "*/tests/*" | while read -r file; do
    
    for func in "${DEPRECATED_FUNCTIONS[@]}"; do
        if grep -q "${func}\s*(" "$file"; then
            log_error "非推奨関数が見つかりました: $func in $file"
            DEPRECATED_FOUND=1
        fi
    done
done

if [[ $DEPRECATED_FOUND -eq 0 ]]; then
    log_success "非推奨関数検出 - 問題なし"
else
    log_error "非推奨関数検出 - 修正が必要です"
fi

echo

# テスト完了
END_TIME=$(date +%s)
log_success "グループ1: PHP構文チェック関連テスト完了"
log_info "独立性: 完全独立（外部依存なし）"
log_info "並列実行: 対応済み"
log_info "実行完了"

exit 0