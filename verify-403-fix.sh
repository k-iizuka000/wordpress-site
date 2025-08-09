#!/bin/bash
# =========================================================================
# 403エラー修正の検証スクリプト
# =========================================================================

echo "============================================"
echo "403 Error Fix Verification Script"
echo "============================================"
echo ""

# 色付き出力の定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# テスト対象のURL
BASE_URL="http://localhost:8090"
THEME_URL="$BASE_URL/wp-content/themes/kei-portfolio"

# 結果カウンター
PASSED=0
FAILED=0

# テスト関数
test_url() {
    local url=$1
    local description=$2
    
    echo -n "Testing: $description ... "
    
    # HTTPステータスコードを取得
    status_code=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$status_code" = "200" ] || [ "$status_code" = "304" ]; then
        echo -e "${GREEN}PASSED${NC} (Status: $status_code)"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}FAILED${NC} (Status: $status_code)"
        ((FAILED++))
        
        # 詳細なエラー情報を取得
        echo "  Headers:"
        curl -I "$url" 2>/dev/null | head -n 5 | sed 's/^/    /'
        return 1
    fi
}

# MIMEタイプチェック関数
check_mime_type() {
    local url=$1
    local expected_mime=$2
    local description=$3
    
    echo -n "Checking MIME type: $description ... "
    
    # Content-Typeヘッダーを取得
    content_type=$(curl -s -I "$url" | grep -i "^content-type:" | cut -d' ' -f2 | tr -d '\r\n')
    
    if [[ "$content_type" == *"$expected_mime"* ]]; then
        echo -e "${GREEN}PASSED${NC} (MIME: $content_type)"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}FAILED${NC} (Expected: $expected_mime, Got: $content_type)"
        ((FAILED++))
        return 1
    fi
}

echo "1. Checking Docker container status..."
echo "----------------------------------------"
docker ps | grep kei-portfolio-dev
echo ""

echo "2. Testing static asset access..."
echo "----------------------------------------"

# CSSファイルのテスト
test_url "$THEME_URL/style.css" "Main stylesheet"
check_mime_type "$THEME_URL/style.css" "text/css" "style.css MIME type"

# JavaScriptファイルのテスト
test_url "$THEME_URL/assets/js/main.js" "Main JavaScript"
check_mime_type "$THEME_URL/assets/js/main.js" "application/javascript" "main.js MIME type"

test_url "$THEME_URL/assets/js/navigation.js" "Navigation JavaScript"
check_mime_type "$THEME_URL/assets/js/navigation.js" "application/javascript" "navigation.js MIME type"

echo ""
echo "3. Testing with query parameters..."
echo "----------------------------------------"

# クエリパラメータ付きURLのテスト
test_url "$THEME_URL/style.css?ver=1.0.0" "Stylesheet with version parameter"
test_url "$THEME_URL/assets/js/main.js?ver=1.0.0" "JavaScript with version parameter"

echo ""
echo "4. Testing REST API endpoints..."
echo "----------------------------------------"

# REST APIのテスト
test_url "$BASE_URL/wp-json/" "REST API root"
test_url "$BASE_URL/wp-json/wp/v2/posts" "Posts endpoint"

echo ""
echo "5. Checking .htaccess file..."
echo "----------------------------------------"

# .htaccessファイルの存在確認
docker exec kei-portfolio-dev test -f /var/www/html/wp-content/themes/kei-portfolio/.htaccess
if [ $? -eq 0 ]; then
    echo -e "${GREEN}.htaccess file exists${NC}"
    ((PASSED++))
    
    # .htaccessの内容を一部表示
    echo "  First 10 lines of .htaccess:"
    docker exec kei-portfolio-dev head -n 10 /var/www/html/wp-content/themes/kei-portfolio/.htaccess | sed 's/^/    /'
else
    echo -e "${RED}.htaccess file not found${NC}"
    ((FAILED++))
fi

echo ""
echo "6. Checking file permissions..."
echo "----------------------------------------"

# パーミッションチェック
echo "Theme directory permissions:"
docker exec kei-portfolio-dev ls -la /var/www/html/wp-content/themes/ | grep kei-portfolio | head -n 5

echo ""
echo "Assets directory permissions:"
docker exec kei-portfolio-dev ls -la /var/www/html/wp-content/themes/kei-portfolio/assets/ | head -n 5

echo ""
echo "7. Checking error logs..."
echo "----------------------------------------"

# エラーログの確認
echo "Apache error log (last 5 lines):"
docker exec kei-portfolio-dev tail -n 5 /var/log/apache2/error.log 2>/dev/null || echo "  No errors found or log not accessible"

echo ""
echo "Debug log (last 5 lines):"
docker exec kei-portfolio-dev tail -n 5 /var/www/html/wp-content/debug.log 2>/dev/null || echo "  No debug log found"

echo ""
echo "Asset 403 log (last 5 lines):"
docker exec kei-portfolio-dev tail -n 5 /var/www/html/wp-content/debug-asset-403.log 2>/dev/null || echo "  No 403 error log found"

echo ""
echo "============================================"
echo "Test Summary"
echo "============================================"
echo -e "Passed: ${GREEN}$PASSED${NC}"
echo -e "Failed: ${RED}$FAILED${NC}"

if [ $FAILED -eq 0 ]; then
    echo -e "\n${GREEN}All tests passed! The 403 error fix appears to be working.${NC}"
    exit 0
else
    echo -e "\n${YELLOW}Some tests failed. Please review the errors above.${NC}"
    exit 1
fi