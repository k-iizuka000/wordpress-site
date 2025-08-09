#!/bin/bash

# WordPress テーマ kei-portfolio
# グループ4: ページテンプレート後半のテスト実行スクリプト

set -e

# スクリプトのディレクトリを取得
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$SCRIPT_DIR"

# カラー出力の設定
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ヘッダー出力
echo -e "${BLUE}=======================================${NC}"
echo -e "${BLUE}  WordPress テーマ kei-portfolio${NC}"
echo -e "${BLUE}  グループ4: ページテンプレート後半${NC}"
echo -e "${BLUE}  テスト実行スクリプト${NC}"
echo -e "${BLUE}=======================================${NC}"
echo ""

# 実行時刻の表示
echo -e "${YELLOW}実行開始時刻: $(date)${NC}"
echo ""

# 環境確認
echo -e "${BLUE}=== 環境確認 ===${NC}"
echo "テーマディレクトリ: $THEME_DIR"
echo "PHP バージョン: $(php --version | head -n1)"

# Composer確認
if [ -f "$THEME_DIR/composer.json" ] && command -v composer >/dev/null 2>&1; then
    echo "Composer: 利用可能"
else
    echo -e "${YELLOW}Composer: 利用不可 (composer.jsonが見つからないか、composerがインストールされていません)${NC}"
fi

# PHPUnit確認
if [ -f "$THEME_DIR/vendor/bin/phpunit" ]; then
    PHPUNIT_CMD="$THEME_DIR/vendor/bin/phpunit"
    echo "PHPUnit: ベンダー版を使用"
elif command -v phpunit >/dev/null 2>&1; then
    PHPUNIT_CMD="phpunit"
    echo "PHPUnit: グローバル版を使用"
else
    echo -e "${RED}エラー: PHPUnitが見つかりません${NC}"
    echo "以下のいずれかの方法でPHPUnitをインストールしてください:"
    echo "1. composer install (推奨)"
    echo "2. グローバルインストール: composer global require phpunit/phpunit"
    exit 1
fi

echo ""

# テストディレクトリの確認
if [ ! -d "$THEME_DIR/tests" ]; then
    echo -e "${RED}エラー: testsディレクトリが見つかりません: $THEME_DIR/tests${NC}"
    exit 1
fi

# テストファイルの存在確認
echo -e "${BLUE}=== テストファイル確認 ===${NC}"
TEST_FILES=(
    "tests/bootstrap.php"
    "tests/test-helpers.php"
    "tests/TemplatePartsTest.php"
    "tests/PageTemplatesTest2.php"
)

all_files_exist=true
for file in "${TEST_FILES[@]}"; do
    if [ -f "$THEME_DIR/$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file (見つかりません)"
        all_files_exist=false
    fi
done

if [ "$all_files_exist" = false ]; then
    echo -e "${RED}エラー: 必要なテストファイルが不足しています${NC}"
    exit 1
fi

echo ""

# 対象テンプレートファイルの確認
echo -e "${BLUE}=== 対象テンプレートファイル確認 ===${NC}"
TEMPLATE_FILES=(
    "page-templates/page-skills.php"
    "page-templates/page-contact.php"
    "single-project.php"
)

for file in "${TEMPLATE_FILES[@]}"; do
    if [ -f "$THEME_DIR/$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${YELLOW}⚠${NC} $file (見つかりません - テストはスキップされます)"
    fi
done

echo ""

# テンプレートパーツの確認
echo -e "${BLUE}=== テンプレートパーツ確認 ===${NC}"
TEMPLATE_PARTS=(
    "template-parts/skills/skills-hero.php"
    "template-parts/skills/programming-languages.php"
    "template-parts/contact/hero.php"
    "template-parts/contact/contact-form.php"
    "template-parts/contact/contact-info.php"
)

for part in "${TEMPLATE_PARTS[@]}"; do
    if [ -f "$THEME_DIR/$part" ]; then
        echo -e "${GREEN}✓${NC} $part"
    else
        echo -e "${YELLOW}⚠${NC} $part (見つかりません - 一部テストはスキップされます)"
    fi
done

echo ""

# PHPUnit設定ファイルの確認
echo -e "${BLUE}=== PHPUnit設定確認 ===${NC}"
if [ -f "$THEME_DIR/phpunit.xml" ]; then
    echo -e "${GREEN}✓${NC} phpunit.xml"
    PHPUNIT_CONFIG="--configuration $THEME_DIR/phpunit.xml"
elif [ -f "$THEME_DIR/phpunit.xml.dist" ]; then
    echo -e "${GREEN}✓${NC} phpunit.xml.dist"
    PHPUNIT_CONFIG="--configuration $THEME_DIR/phpunit.xml.dist"
else
    echo -e "${YELLOW}⚠${NC} PHPUnit設定ファイルなし (デフォルト設定を使用)"
    PHPUNIT_CONFIG=""
fi

echo ""

# テスト実行
echo -e "${BLUE}=== グループ4 テスト実行 ===${NC}"
echo "実行対象:"
echo "- テンプレートパーツの存在と構造確認"
echo "- Skills ページテンプレートテスト"
echo "- Contact ページテンプレートテスト"  
echo "- 単一プロジェクトページテスト"
echo ""

# カバレッジディレクトリの作成
COVERAGE_DIR="$THEME_DIR/coverage"
mkdir -p "$COVERAGE_DIR"

# テスト実行時のオプション
PHPUNIT_OPTIONS="--colors=always --verbose"

# テンプレートテストスイートの実行
echo -e "${YELLOW}テンプレートパーツテスト実行中...${NC}"
if cd "$THEME_DIR" && $PHPUNIT_CMD $PHPUNIT_CONFIG $PHPUNIT_OPTIONS tests/TemplatePartsTest.php; then
    echo -e "${GREEN}✓ テンプレートパーツテスト: 成功${NC}"
    TEMPLATE_PARTS_RESULT="success"
else
    echo -e "${RED}✗ テンプレートパーツテスト: 失敗${NC}"
    TEMPLATE_PARTS_RESULT="failed"
fi

echo ""

echo -e "${YELLOW}ページテンプレートテスト実行中...${NC}"
if cd "$THEME_DIR" && $PHPUNIT_CMD $PHPUNIT_CONFIG $PHPUNIT_OPTIONS tests/PageTemplatesTest2.php; then
    echo -e "${GREEN}✓ ページテンプレートテスト: 成功${NC}"
    PAGE_TEMPLATES_RESULT="success"
else
    echo -e "${RED}✗ ページテンプレートテスト: 失敗${NC}"
    PAGE_TEMPLATES_RESULT="failed"
fi

echo ""

# テンプレートスイート全体の実行（利用可能な場合）
if [ -n "$PHPUNIT_CONFIG" ]; then
    echo -e "${YELLOW}テンプレートスイート全体実行中...${NC}"
    if cd "$THEME_DIR" && $PHPUNIT_CMD $PHPUNIT_CONFIG $PHPUNIT_OPTIONS --testsuite templates; then
        echo -e "${GREEN}✓ テンプレートスイート: 成功${NC}"
        TEMPLATE_SUITE_RESULT="success"
    else
        echo -e "${RED}✗ テンプレートスイート: 失敗${NC}"
        TEMPLATE_SUITE_RESULT="failed"
    fi
fi

echo ""

# 結果サマリー
echo -e "${BLUE}=== テスト結果サマリー ===${NC}"
echo -e "テンプレートパーツテスト: $([ "$TEMPLATE_PARTS_RESULT" = "success" ] && echo -e "${GREEN}成功${NC}" || echo -e "${RED}失敗${NC}")"
echo -e "ページテンプレートテスト: $([ "$PAGE_TEMPLATES_RESULT" = "success" ] && echo -e "${GREEN}成功${NC}" || echo -e "${RED}失敗${NC}")"

if [ -n "$TEMPLATE_SUITE_RESULT" ]; then
    echo -e "テンプレートスイート全体: $([ "$TEMPLATE_SUITE_RESULT" = "success" ] && echo -e "${GREEN}成功${NC}" || echo -e "${RED}失敗${NC}")"
fi

# 終了コードの決定
if [ "$TEMPLATE_PARTS_RESULT" = "success" ] && [ "$PAGE_TEMPLATES_RESULT" = "success" ] && ([ -z "$TEMPLATE_SUITE_RESULT" ] || [ "$TEMPLATE_SUITE_RESULT" = "success" ]); then
    echo ""
    echo -e "${GREEN}🎉 グループ4: すべてのテストが成功しました！${NC}"
    echo ""
    
    # 成果の表示
    echo -e "${BLUE}=== 実装された機能 ===${NC}"
    echo "✓ スキルページテンプレートの構造確認"
    echo "✓ お問い合わせページテンプレートの構造確認"
    echo "✓ プロジェクト詳細ページテンプレートの確認"
    echo "✓ テンプレートパーツの存在確認"
    echo "✓ セキュリティ要素の確認"
    echo "✓ WordPress関数の適切な使用確認"
    
    exit 0
else
    echo ""
    echo -e "${RED}❌ グループ4: 一部のテストが失敗しました${NC}"
    echo ""
    echo -e "${YELLOW}対処方法:${NC}"
    echo "1. エラーメッセージを確認してください"
    echo "2. 対象テンプレートファイルが存在するか確認してください"
    echo "3. PHPの構文エラーがないか確認してください"
    echo "4. WordPressの関数が適切に使用されているか確認してください"
    
    exit 1
fi

echo ""
echo -e "${YELLOW}実行終了時刻: $(date)${NC}"