#!/bin/bash

# グループ2テスト実行スクリプト
# テスト実装とデータ投入関連のテスト

set -e

# カラー設定
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ログファイル設定
LOG_DIR="$(dirname "$0")/tests/logs"
LOG_FILE="$LOG_DIR/group2-test-$(date +%Y%m%d-%H%M%S).log"

# ログディレクトリ作成
mkdir -p "$LOG_DIR"

echo -e "${BLUE}=== グループ2: テスト実装とデータ投入関連テスト ===${NC}"
echo "実行時刻: $(date)"
echo "ログファイル: $LOG_FILE"
echo ""

# ログ開始
{
    echo "=== Group 2 Test Execution Log ==="
    echo "Start Time: $(date)"
    echo "Working Directory: $(pwd)"
    echo ""
} > "$LOG_FILE"

# PHPUnit設定確認
echo -e "${YELLOW}PHPUnit設定確認中...${NC}"
if [ ! -f "phpunit.xml" ]; then
    echo -e "${RED}エラー: phpunit.xml が見つかりません${NC}"
    exit 1
fi

# テスト対象ファイル確認
echo -e "${YELLOW}テスト対象ファイル確認中...${NC}"
TEST_FILES=(
    "tests/PageCreatorTest.php"
    "tests/SampleDataTest.php" 
    "tests/NavigationTest.php"
)

for file in "${TEST_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo -e "${RED}警告: $file が見つかりません${NC}" | tee -a "$LOG_FILE"
    else
        echo -e "${GREEN}✓ $file${NC}"
    fi
done
echo ""

# Docker環境チェック
echo -e "${YELLOW}Docker環境チェック中...${NC}"
if command -v docker &> /dev/null && command -v docker-compose &> /dev/null; then
    if docker-compose ps | grep -q "Up"; then
        echo -e "${GREEN}✓ Docker環境が稼働中です${NC}"
        DOCKER_ENV=true
    else
        echo -e "${YELLOW}! Docker環境が停止中です。ローカル環境でテストを実行します${NC}" | tee -a "$LOG_FILE"
        DOCKER_ENV=false
    fi
else
    echo -e "${YELLOW}! Docker/docker-composeが見つかりません。ローカル環境でテストを実行します${NC}" | tee -a "$LOG_FILE"
    DOCKER_ENV=false
fi
echo ""

# テスト実行関数
run_test_suite() {
    local suite_name="$1"
    local files="$2"
    local description="$3"
    
    echo -e "${BLUE}--- $description ---${NC}"
    echo -e "${BLUE}--- $description ---${NC}" >> "$LOG_FILE"
    
    if [ "$DOCKER_ENV" = true ]; then
        # Docker環境での実行
        echo "Docker環境でテスト実行中..." | tee -a "$LOG_FILE"
        if docker-compose exec -T wordpress vendor/bin/phpunit \
            --configuration /var/www/html/wp-content/themes/kei-portfolio/phpunit.xml \
            --testsuite="$suite_name" \
            --testdox \
            --colors=always \
            --log-junit="coverage/junit-$suite_name.xml" \
            2>&1 | tee -a "$LOG_FILE"; then
            echo -e "${GREEN}✅ $description: 成功${NC}" | tee -a "$LOG_FILE"
            return 0
        else
            echo -e "${RED}❌ $description: 失敗${NC}" | tee -a "$LOG_FILE"
            return 1
        fi
    else
        # ローカル環境での実行
        echo "ローカル環境でテスト実行中..." | tee -a "$LOG_FILE"
        if [ -f "vendor/bin/phpunit" ]; then
            if vendor/bin/phpunit \
                --configuration phpunit.xml \
                $files \
                --testdox \
                --colors=always \
                --log-junit="coverage/junit-$suite_name.xml" \
                2>&1 | tee -a "$LOG_FILE"; then
                echo -e "${GREEN}✅ $description: 成功${NC}" | tee -a "$LOG_FILE"
                return 0
            else
                echo -e "${RED}❌ $description: 失敗${NC}" | tee -a "$LOG_FILE"
                return 1
            fi
        else
            echo -e "${RED}エラー: PHPUnitが見つかりません${NC}" | tee -a "$LOG_FILE"
            return 1
        fi
    fi
}

# テスト実行カウンタ
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# テスト1: 固定ページ作成テスト
echo -e "${BLUE}=== テスト1: 固定ページ作成機能 ===${NC}"
if run_test_suite "page-creator" "tests/PageCreatorTest.php" "固定ページ作成機能テスト"; then
    ((PASSED_TESTS++))
else
    ((FAILED_TESTS++))
fi
((TOTAL_TESTS++))
echo ""

# テスト2: サンプルデータ投入テスト
echo -e "${BLUE}=== テスト2: サンプルデータ投入機能 ===${NC}"
if run_test_suite "sample-data" "tests/SampleDataTest.php" "サンプルデータ投入機能テスト"; then
    ((PASSED_TESTS++))
else
    ((FAILED_TESTS++))
fi
((TOTAL_TESTS++))
echo ""

# テスト3: ナビゲーションメニューテスト
echo -e "${BLUE}=== テスト3: ナビゲーションメニュー機能 ===${NC}"
if run_test_suite "navigation" "tests/NavigationTest.php" "ナビゲーションメニュー機能テスト"; then
    ((PASSED_TESTS++))
else
    ((FAILED_TESTS++))
fi
((TOTAL_TESTS++))
echo ""

# 統合テスト（全て一緒に実行）
echo -e "${BLUE}=== 統合テスト: グループ2全体 ===${NC}"
if run_test_suite "group2-integration" "${TEST_FILES[*]}" "グループ2統合テスト"; then
    ((PASSED_TESTS++))
else
    ((FAILED_TESTS++))
fi
((TOTAL_TESTS++))
echo ""

# カバレッジレポート生成（可能な場合）
echo -e "${YELLOW}カバレッジレポート生成中...${NC}"
if [ -d "coverage" ]; then
    if [ "$DOCKER_ENV" = true ]; then
        docker-compose exec -T wordpress vendor/bin/phpunit \
            --configuration /var/www/html/wp-content/themes/kei-portfolio/phpunit.xml \
            --testsuite="group2-integration" \
            --coverage-html coverage/html-group2 \
            --coverage-clover coverage/clover-group2.xml \
            2>/dev/null || echo -e "${YELLOW}カバレッジレポート生成をスキップしました${NC}"
    else
        vendor/bin/phpunit \
            --configuration phpunit.xml \
            "${TEST_FILES[@]}" \
            --coverage-html coverage/html-group2 \
            --coverage-clover coverage/clover-group2.xml \
            2>/dev/null || echo -e "${YELLOW}カバレッジレポート生成をスキップしました${NC}"
    fi
fi

# 結果サマリー
echo ""
echo -e "${BLUE}=== テスト実行結果サマリー ===${NC}"
echo "総テスト数: $TOTAL_TESTS"
echo -e "成功: ${GREEN}$PASSED_TESTS${NC}"
echo -e "失敗: ${RED}$FAILED_TESTS${NC}"
echo "実行時刻: $(date)"

# ログに結果を記録
{
    echo ""
    echo "=== Test Execution Summary ==="
    echo "Total Tests: $TOTAL_TESTS"
    echo "Passed: $PASSED_TESTS"
    echo "Failed: $FAILED_TESTS"
    echo "End Time: $(date)"
} >> "$LOG_FILE"

# レポートファイル生成
REPORT_FILE="tests/reports/group2-test-report-$(date +%Y%m%d-%H%M%S).md"
mkdir -p "tests/reports"

cat > "$REPORT_FILE" << EOF
# グループ2テスト実行レポート

## 実行概要
- 実行日時: $(date)
- テスト環境: $([ "$DOCKER_ENV" = true ] && echo "Docker" || echo "Local")
- 総テスト数: $TOTAL_TESTS
- 成功数: $PASSED_TESTS
- 失敗数: $FAILED_TESTS

## テスト対象
1. 固定ページ作成機能テスト (PageCreatorTest.php)
2. サンプルデータ投入機能テスト (SampleDataTest.php)
3. ナビゲーションメニュー機能テスト (NavigationTest.php)

## 実行結果
$([ $FAILED_TESTS -eq 0 ] && echo "✅ 全てのテストが成功しました" || echo "❌ $FAILED_TESTS個のテストが失敗しました")

## ログファイル
- 詳細ログ: $LOG_FILE
- レポート: $REPORT_FILE

## カバレッジレポート
$([ -d "coverage/html-group2" ] && echo "- HTML: coverage/html-group2/index.html" || echo "- カバレッジレポートは生成されませんでした")
$([ -f "coverage/clover-group2.xml" ] && echo "- XML: coverage/clover-group2.xml" || echo "")

## 実行コマンド
\`\`\`bash
$0
\`\`\`
EOF

echo ""
echo -e "${BLUE}レポートファイル: $REPORT_FILE${NC}"
echo -e "${BLUE}詳細ログ: $LOG_FILE${NC}"

# 終了コード設定
if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}✅ 全てのテストが成功しました！${NC}"
    exit 0
else
    echo -e "${RED}❌ $FAILED_TESTS個のテストが失敗しました${NC}"
    exit 1
fi