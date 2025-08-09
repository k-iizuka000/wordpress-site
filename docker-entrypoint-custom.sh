#!/bin/bash
# =========================================================================
# カスタムDockerエントリーポイント - 403エラー対策
# =========================================================================

echo "Starting custom Docker entrypoint for Kei Portfolio..."

# テーマディレクトリの存在確認
THEME_DIR="/var/www/html/wp-content/themes/kei-portfolio"

if [ -d "$THEME_DIR" ]; then
    echo "Setting permissions for theme directory..."
    
    # www-dataユーザーに所有権を設定
    chown -R www-data:www-data "$THEME_DIR" 2>/dev/null || echo "Warning: Could not set ownership for theme directory"
    
    # ディレクトリとファイルのパーミッション設定
    find "$THEME_DIR" -type d -exec chmod 755 {} \; 2>/dev/null || echo "Warning: Could not set directory permissions"
    find "$THEME_DIR" -type f -exec chmod 644 {} \; 2>/dev/null || echo "Warning: Could not set file permissions"
    
    # .htaccessファイルの確認と作成
    if [ ! -f "$THEME_DIR/.htaccess" ]; then
        echo "Creating .htaccess file for theme directory..."
        cat > "$THEME_DIR/.htaccess" <<'EOF'
# Allow direct access to theme assets
<FilesMatch "\.(css|js|jpg|jpeg|png|gif|svg|webp|woff|woff2|ttf|eot|ico)(\?.*)?$">
    Order Allow,Deny
    Allow from all
    <IfModule mod_headers.c>
        Header set Access-Control-Allow-Origin "*"
        Header set Cache-Control "public, max-age=31536000"
    </IfModule>
</FilesMatch>

# MIME type設定
<IfModule mod_mime.c>
    AddType text/css .css
    AddType application/javascript .js
</IfModule>

# PHPファイルのアクセス制限
<FilesMatch "\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
EOF
        chmod 644 "$THEME_DIR/.htaccess" 2>/dev/null || echo "Warning: Could not set .htaccess permissions"
    fi
    
    # assetsディレクトリの特別な処理
    if [ -d "$THEME_DIR/assets" ]; then
        echo "Setting special permissions for assets directory..."
        chmod -R 755 "$THEME_DIR/assets" 2>/dev/null || echo "Warning: Could not set assets directory permissions"
        find "$THEME_DIR/assets" -type f -exec chmod 644 {} \; 2>/dev/null || echo "Warning: Could not set assets file permissions"
    fi
    
    echo "Theme directory permissions set successfully."
else
    echo "Warning: Theme directory not found"
fi

# WordPressのアップロードディレクトリ権限設定
UPLOADS_DIR="/var/www/html/wp-content/uploads"
if [ ! -d "$UPLOADS_DIR" ]; then
    echo "Creating uploads directory..."
    mkdir -p "$UPLOADS_DIR" 2>/dev/null || echo "Warning: Could not create uploads directory"
fi
chown -R www-data:www-data "$UPLOADS_DIR" 2>/dev/null || echo "Warning: Could not set uploads directory ownership"
chmod -R 755 "$UPLOADS_DIR" 2>/dev/null || echo "Warning: Could not set uploads directory permissions"

# デバッグログファイルの準備
DEBUG_LOG="/var/www/html/wp-content/debug.log"
if [ ! -f "$DEBUG_LOG" ]; then
    touch "$DEBUG_LOG" 2>/dev/null || echo "Warning: Could not create debug log file"
    chown www-data:www-data "$DEBUG_LOG" 2>/dev/null
    chmod 666 "$DEBUG_LOG" 2>/dev/null
fi

# 403エラーログファイルの準備
ERROR_LOG="/var/www/html/wp-content/debug-asset-403.log"
if [ ! -f "$ERROR_LOG" ]; then
    touch "$ERROR_LOG" 2>/dev/null || echo "Warning: Could not create error log file"
    chown www-data:www-data "$ERROR_LOG" 2>/dev/null
    chmod 666 "$ERROR_LOG" 2>/dev/null
fi

# E2Eテストモードログファイルの準備
TEST_LOG="/var/www/html/wp-content/e2e-test-mode.log"
if [ ! -f "$TEST_LOG" ]; then
    touch "$TEST_LOG" 2>/dev/null || echo "Warning: Could not create test mode log file"
    chown www-data:www-data "$TEST_LOG" 2>/dev/null
    chmod 666 "$TEST_LOG" 2>/dev/null
fi

# Apache設定の確認
echo "Checking Apache configuration..."
apache2ctl configtest

# E2Eテスト環境変数の設定（必要に応じて）
if [ "${E2E_TEST_MODE}" = "true" ]; then
    echo "E2E Test Mode is enabled"
    export E2E_TEST_MODE=true
fi

echo "Custom entrypoint setup complete."

# 元のWordPressエントリーポイントを実行
exec docker-entrypoint.sh apache2-foreground