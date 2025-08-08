#!/bin/bash

# WordPress サイト完全エクスポートスクリプト
# データベースとファイルの両方をエクスポート（prod/localモード対応）

# ============================================
# デフォルト設定
# ============================================
# 動作モード（prod: 本番向けURL置換, local: URL置換なし）
MODE="prod"

# データベース設定（セキュリティ対応：パスワードはmy.cnfファイル使用を推奨）
DB_CONTAINER="kei-portfolio-db"
DB_NAME="kei_portfolio_dev"
DB_USER="wp_user"
DB_PASSWORD="wp_password"  # 本番環境ではmy.cnfファイルを使用してください

# データベース認証用my.cnfファイルパス（セキュリティ強化）
MYSQL_CONFIG_FILE="${HOME}/.my.cnf"
USE_MYSQL_CONFIG=${USE_MYSQL_CONFIG:-false}

# ディレクトリ設定（環境変数でオーバーライド可能）
EXPORT_DIR="${EXPORT_DIR:-./site-exports}"
THEME_SOURCE_DIR="${THEME_SOURCE_DIR:-./themes/kei-portfolio}"
THEME_UPLOAD_DIR="${THEME_UPLOAD_DIR:-./themes/kei-portfolio-upload}"

# URL設定（CLIオプションまたは環境変数で指定）
SOURCE_URL="${SOURCE_URL:-http://localhost:8090}"
DEST_URL="${DEST_URL:-https://kei-aokiki.dev}"

DATE=$(date +"%Y%m%d_%H%M%S")

# WordPressコンテナ名（wp search-replaceコマンド用）
WP_CONTAINER="${WP_CONTAINER:-kei-portfolio-wordpress}"

# 色付き出力用の変数
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================
# 関数定義
# ============================================

# エラーハンドリング（セキュリティ対応：フルパス情報を相対パスに変換）
error_exit() {
    local msg="${1//$PWD/.}"
    echo -e "${RED}エラー: $msg${NC}" >&2
    exit 1
}

# 成功メッセージ
success_msg() {
    echo -e "${GREEN}✓ $1${NC}"
}

# 警告メッセージ
warning_msg() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# 情報メッセージ
info_msg() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# ヘルプメッセージ表示
show_help() {
    cat << EOF
WordPress サイト完全エクスポートスクリプト (セキュリティ強化版)

使用方法:
    $0 [オプション]

オプション:
    --mode=MODE           動作モード (prod|local) [デフォルト: prod]
                         prod: 本番向けURL置換を実行
                         local: URL置換なし（ローカル用）
                         
    --source-url=URL      置換元URL [デフォルト: http://localhost:8090]
    --dest-url=URL        置換先URL [デフォルト: https://kei-aokiki.dev]
    --export-dir=PATH     エクスポート先ディレクトリ [デフォルト: ./site-exports]
    --theme-source=PATH   テーマソースディレクトリ [デフォルト: ./themes/kei-portfolio]
    --theme-upload=PATH   テーマアップロードディレクトリ [デフォルト: ./themes/kei-portfolio-upload]
    
    -h, --help           このヘルプメッセージを表示
    -v, --version        バージョン情報を表示

使用例:
    # 基本的な使用（本番向け）
    $0
    
    # ローカル環境向けエクスポート
    $0 --mode=local
    
    # カスタムURL指定
    $0 --mode=prod --source-url="http://localhost:8090" --dest-url="https://example.com"
    
    # カスタムディレクトリ指定
    $0 --export-dir="./exports" --theme-source="./my-theme"
    
    # my.cnfファイルを使用したセキュアなデータベースアクセス
    USE_MYSQL_CONFIG=true $0

環境変数:
    EXPORT_DIR            エクスポート先ディレクトリ
    THEME_SOURCE_DIR      テーマソースディレクトリ  
    THEME_UPLOAD_DIR      テーマアップロードディレクトリ
    SOURCE_URL            置換元URL
    DEST_URL              置換先URL
    WP_CONTAINER          WordPressコンテナ名
    USE_MYSQL_CONFIG      my.cnfファイル使用フラグ (true|false)
    MYSQL_CONFIG_FILE     my.cnfファイルパス [デフォルト: ~/.my.cnf]

セキュリティ機能:
    - エラーメッセージからフルパス情報を除去
    - my.cnfファイルを使用した安全なデータベース認証
    - macOS/Linux両対応のsed構文

EOF
}

# バージョン情報表示
show_version() {
    echo "WordPress Export Script v2.1.0 (セキュリティ強化版)"
    echo "セキュリティ強化アップデート - 2025年8月"
}

# CLIオプション解析
parse_options() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --mode=*)
                MODE="${1#*=}"
                if [[ "$MODE" != "prod" && "$MODE" != "local" ]]; then
                    error_exit "無効なモード: $MODE (prod または local を指定してください)"
                fi
                shift
                ;;
            --source-url=*)
                SOURCE_URL="${1#*=}"
                shift
                ;;
            --dest-url=*)
                DEST_URL="${1#*=}"
                shift
                ;;
            --export-dir=*)
                EXPORT_DIR="${1#*=}"
                shift
                ;;
            --theme-source=*)
                THEME_SOURCE_DIR="${1#*=}"
                shift
                ;;
            --theme-upload=*)
                THEME_UPLOAD_DIR="${1#*=}"
                shift
                ;;
            -h|--help)
                show_help
                exit 0
                ;;
            -v|--version)
                show_version
                exit 0
                ;;
            *)
                error_exit "不明なオプション: $1 (--help でヘルプを表示)"
                ;;
        esac
    done
}

# ディレクトリ作成
create_directory() {
    if [ ! -d "$1" ]; then
        mkdir -p "$1" || error_exit "ディレクトリ作成失敗: $1"
        success_msg "ディレクトリ作成: $1"
    fi
}

# ファイルコピー（上書き確認付き）
copy_file() {
    local source="$1"
    local dest="$2"
    
    if [ -f "$source" ]; then
        if [ -f "$dest" ]; then
            # ファイルが異なる場合のみコピー
            if ! cmp -s "$source" "$dest"; then
                cp "$source" "$dest" || error_exit "ファイルコピー失敗: $source → $dest"
                success_msg "更新: $(basename "$source")"
            else
                info_msg "スキップ（同一）: $(basename "$source")"
            fi
        else
            cp "$source" "$dest" || error_exit "ファイルコピー失敗: $source → $dest"
            success_msg "コピー: $(basename "$source")"
        fi
    else
        warning_msg "ファイルが見つかりません: $source"
    fi
}

# ============================================
# メイン処理
# ============================================

# CLIオプション解析
parse_options "$@"

# 設定値の表示
echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  WordPress サイト完全エクスポート              ${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
info_msg "動作モード: $MODE"
info_msg "エクスポート先: $EXPORT_DIR"
info_msg "テーマソース: $THEME_SOURCE_DIR"
info_msg "テーマアップロード: $THEME_UPLOAD_DIR"

if [[ "$MODE" == "prod" ]]; then
    info_msg "URL置換: $SOURCE_URL → $DEST_URL"
else
    info_msg "URL置換: なし（ローカルモード）"
fi
echo ""

# 設定値の検証
validate_configuration() {
    local errors=0
    
    # ディレクトリの存在確認
    if [[ ! -d "$THEME_SOURCE_DIR" ]]; then
        error_exit "テーマソースディレクトリが見つかりません: $(basename "$THEME_SOURCE_DIR")"
        ((errors++))
    fi
    
    if [[ "$MODE" == "prod" && "$SOURCE_URL" == "$DEST_URL" ]]; then
        error_exit "置換元URLと置換先URLが同じです"
        ((errors++))
    fi
    
    # DockerコンテナのチェックをDB処理前に実行
    if ! docker ps | grep -q "$DB_CONTAINER"; then
        error_exit "データベースコンテナが起動していません: $(basename "$DB_CONTAINER")"
        ((errors++))
    fi
    
    if [[ $errors -gt 0 ]]; then
        error_exit "設定エラーが $errors 個見つかりました。"
    fi
    
    success_msg "設定値の検証完了"
}

# 設定値の検証
validate_configuration

# エクスポートディレクトリを作成
create_directory "$EXPORT_DIR"
create_directory "$EXPORT_DIR/$DATE"

# ============================================
# 1. アップロードディレクトリのクリーンアップ
# ============================================
echo ""
echo -e "${BLUE}[1/3] アップロードディレクトリの準備${NC}"
echo "========================================"

# 不要なファイルを削除（開発用ファイル）
EXCLUDE_PATTERNS=(
    "node_modules"
    "vendor"
    "tests"
    "coverage"
    "*.log"
    "*.lock"
    "composer.json"
    "composer.lock"
    "package.json"
    "package-lock.json"
    "phpunit.xml*"
    "jest.config.js"
    ".git"
    ".gitignore"
    "*.sh"
    "README.md"
    ".babelrc"
    ".env*"
    ".DS_Store"
)

# 不要なファイル/ディレクトリを削除
info_msg "不要なファイルをクリーンアップ中..."
for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    if compgen -G "$THEME_UPLOAD_DIR/$pattern" > /dev/null; then
        rm -rf "$THEME_UPLOAD_DIR"/$pattern
        info_msg "削除: $pattern"
    fi
done

# ============================================
# 2. 必要なファイルをコピー
# ============================================
echo ""
echo -e "${BLUE}[2/3] 必要なファイルをアップロードディレクトリにコピー${NC}"
echo "========================================"

# PHPファイルをコピー（ソースからアップロードディレクトリへ）
info_msg "PHPファイルをコピー中..."
for file in "$THEME_SOURCE_DIR"/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        copy_file "$file" "$THEME_UPLOAD_DIR/$filename"
    fi
done

# テンプレートパーツをコピー
if [ -d "$THEME_SOURCE_DIR/template-parts" ]; then
    create_directory "$THEME_UPLOAD_DIR/template-parts"
    info_msg "template-partsをコピー中..."
    cp -r "$THEME_SOURCE_DIR/template-parts/"* "$THEME_UPLOAD_DIR/template-parts/" 2>/dev/null
    success_msg "template-partsコピー完了"
fi

# incディレクトリをコピー
if [ -d "$THEME_SOURCE_DIR/inc" ]; then
    create_directory "$THEME_UPLOAD_DIR/inc"
    info_msg "incディレクトリをコピー中..."
    cp -r "$THEME_SOURCE_DIR/inc/"*.php "$THEME_UPLOAD_DIR/inc/" 2>/dev/null
    success_msg "incディレクトリコピー完了"
fi

# assetsディレクトリをコピー（CSS、JS、画像）
if [ -d "$THEME_SOURCE_DIR/assets" ]; then
    create_directory "$THEME_UPLOAD_DIR/assets"
    info_msg "assetsディレクトリをコピー中..."
    cp -r "$THEME_SOURCE_DIR/assets/"* "$THEME_UPLOAD_DIR/assets/" 2>/dev/null
    success_msg "assetsディレクトリコピー完了"
fi

# data/portfolio.json をコピー
if [ -f "$THEME_SOURCE_DIR/data/portfolio.json" ]; then
    create_directory "$THEME_UPLOAD_DIR/data"
    copy_file "$THEME_SOURCE_DIR/data/portfolio.json" "$THEME_UPLOAD_DIR/data/portfolio.json"
else
    error_exit "portfolio.json が見つかりません（data/ディレクトリを確認してください）"
fi

# style.cssをコピー（必須）
if [ -f "$THEME_SOURCE_DIR/style.css" ]; then
    copy_file "$THEME_SOURCE_DIR/style.css" "$THEME_UPLOAD_DIR/style.css"
else
    error_exit "style.css が見つかりません（テーマルートディレクトリを確認してください）"
fi

# screenshot.pngをコピー（あれば）
if [ -f "$THEME_SOURCE_DIR/screenshot.png" ]; then
    copy_file "$THEME_SOURCE_DIR/screenshot.png" "$THEME_UPLOAD_DIR/screenshot.png"
fi

# .htaccess ファイルをコピー（相対パスで指定）
OLD_HTACCESS="./old/.htaccess"
if [ -f "$OLD_HTACCESS" ]; then
    info_msg ".htaccess-for-root ファイルをコピー中..."
    cp "$OLD_HTACCESS" "$THEME_UPLOAD_DIR/.htaccess-for-root"
    success_msg ".htaccess-for-root ファイルコピー完了（old/.htaccessから）"
else
    info_msg ".htaccess-for-root ファイルを作成中..."
    cat > "$THEME_UPLOAD_DIR/.htaccess-for-root" << 'HTACCESS_EOF'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Protect important files
<FilesMatch "^(wp-config\.php|\.htaccess)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# GZIP Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>

# Cache Control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresDefault "access plus 2 days"
</IfModule>
HTACCESS_EOF
    success_msg ".htaccess-for-root ファイル作成完了（基本版）"
fi

# ファイル数をカウント
TOTAL_FILES=$(find "$THEME_UPLOAD_DIR" -type f | wc -l)
TOTAL_SIZE=$(du -sh "$THEME_UPLOAD_DIR" | cut -f1)
success_msg "テーマファイル準備完了: $TOTAL_FILES ファイル ($TOTAL_SIZE)"

# ============================================
# 3. データベースのエクスポート
# ============================================
echo ""
echo -e "${BLUE}[3/3] データベースのエクスポート${NC}"
echo "========================================"

# データベースエクスポート（セキュリティ強化：my.cnf対応）
DB_EXPORT_FILE="$EXPORT_DIR/$DATE/database_${DATE}.sql"

# データベース認証の安全な実行
export_database() {
    if [[ "$USE_MYSQL_CONFIG" == "true" && -f "$MYSQL_CONFIG_FILE" ]]; then
        info_msg "my.cnfファイルを使用してデータベースをエクスポート中..."
        docker exec $DB_CONTAINER mysqldump \
            --defaults-file=/root/.my.cnf \
            $DB_NAME \
            --single-transaction \
            --quick \
            --lock-tables=false \
            --default-character-set=utf8mb4 \
            --routines \
            --triggers \
            --events > "$DB_EXPORT_FILE"
    else
        warning_msg "パスワード直接指定を使用（本番環境では非推奨）"
        docker exec $DB_CONTAINER mysqldump \
            -u$DB_USER \
            -p$DB_PASSWORD \
            $DB_NAME \
            --single-transaction \
            --quick \
            --lock-tables=false \
            --default-character-set=utf8mb4 \
            --routines \
            --triggers \
            --events > "$DB_EXPORT_FILE"
    fi
}

export_database

# URL置換処理関数
perform_url_replacement() {
    local db_file="$1"
    
    if [[ "$MODE" == "local" ]]; then
        info_msg "ローカルモードのため、URL置換をスキップします"
        return 0
    fi
    
    info_msg "本番環境用にURLを置換中..."
    cp "$db_file" "${db_file}.backup"
    
    # wp search-replaceコマンドが利用可能かチェック
    if docker exec "$WP_CONTAINER" wp --help &>/dev/null; then
        info_msg "wp search-replaceコマンドを使用してURL置換を実行中..."
        
        # WordPress CLIを使った安全なURL置換
        if docker exec "$WP_CONTAINER" wp search-replace "$SOURCE_URL" "$DEST_URL" --skip-columns=guid --dry-run; then
            # 実際の置換を実行
            docker exec "$WP_CONTAINER" wp search-replace "$SOURCE_URL" "$DEST_URL" --skip-columns=guid
            
            # データベースを再エクスポート（置換後）
            export_database_to_file "$db_file"
                
            success_msg "wp search-replaceによるURL置換完了"
        else
            warning_msg "wp search-replaceが失敗しました。sedによる置換にフォールバック"
            perform_sed_replacement "$db_file"
        fi
    else
        warning_msg "WordPress CLIが利用できません。sedによる置換を実行"
        perform_sed_replacement "$db_file"
    fi
}

# データベースを指定ファイルにエクスポート（共通関数）
export_database_to_file() {
    local target_file="$1"
    if [[ "$USE_MYSQL_CONFIG" == "true" && -f "$MYSQL_CONFIG_FILE" ]]; then
        docker exec $DB_CONTAINER mysqldump \
            --defaults-file=/root/.my.cnf \
            $DB_NAME \
            --single-transaction \
            --quick \
            --lock-tables=false \
            --default-character-set=utf8mb4 \
            --routines \
            --triggers \
            --events > "$target_file"
    else
        docker exec $DB_CONTAINER mysqldump \
            -u$DB_USER \
            -p$DB_PASSWORD \
            $DB_NAME \
            --single-transaction \
            --quick \
            --lock-tables=false \
            --default-character-set=utf8mb4 \
            --routines \
            --triggers \
            --events > "$target_file"
    fi
}

# sedによるフォールバック置換（macOS/Linux対応）
perform_sed_replacement() {
    local db_file="$1"
    info_msg "sedコマンドによるURL置換を実行中..."
    
    # OS判定によるsed構文の切り替え（セキュリティ対応）
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS用のsed
        sed -i '' "s|${SOURCE_URL//|/\\|}|${DEST_URL//|/\\|}|g" "$db_file"
    else
        # Linux用のsed
        sed -i "s|${SOURCE_URL//|/\\|}|${DEST_URL//|/\\|}|g" "$db_file"
    fi
    
    # シリアライズデータ内のURL長調整（基本的な対応）
    # Note: 完全なシリアライズデータ対応にはより複雑な処理が必要
    warning_msg "注意: シリアライズデータのURL長が変更された場合、手動での修正が必要な可能性があります"
    
    success_msg "sedによるURL置換完了"
}

if [ $? -eq 0 ]; then
    success_msg "データベースエクスポート完了"
    
    # URL置換処理
    perform_url_replacement "$DB_EXPORT_FILE"
    
    # ファイルサイズ表示
    DB_SIZE=$(du -h "$DB_EXPORT_FILE" | cut -f1)
    info_msg "データベースサイズ: $DB_SIZE"
else
    error_exit "データベースエクスポート失敗"
fi

# データベース圧縮
gzip -c "$DB_EXPORT_FILE" > "${DB_EXPORT_FILE}.gz"
if [ $? -eq 0 ]; then
    success_msg "データベース圧縮完了"
    DB_GZ_SIZE=$(du -h "${DB_EXPORT_FILE}.gz" | cut -f1)
    info_msg "圧縮後サイズ: $DB_GZ_SIZE"
fi

# ============================================
# 4. 移行チェックリストの生成
# ============================================
echo ""
echo -e "${BLUE}移行チェックリストの生成${NC}"
echo "========================================"

CHECKLIST_FILE="$EXPORT_DIR/$DATE/migration-checklist.md"

cat > "$CHECKLIST_FILE" << 'EOF'
# WordPress サイト移行チェックリスト

## エクスポート日時
EOF
echo "- $(date '+%Y年%m月%d日 %H:%M:%S')" >> "$CHECKLIST_FILE"

cat >> "$CHECKLIST_FILE" << EOF

## エクスポート設定
- **モード**: $MODE
- **置換設定**: $([[ "$MODE" == "prod" ]] && echo "$SOURCE_URL → $DEST_URL" || echo "URL置換なし")

## エクスポートファイル
1. **テーマファイル**: \`$THEME_UPLOAD_DIR\` ディレクトリに準備済み
   - .htaccess-for-root（WordPressルート用）
   - portfolio.json（データファイル）
   - テーマファイル一式
2. **データベース**: \`database_*.sql.gz\`
EOF

cat >> "$CHECKLIST_FILE" << 'EOF'

## 移行前の確認事項
- [ ] 本番環境のバックアップ作成
- [ ] メンテナンスモードの有効化
- [ ] 現在のテーマファイルのバックアップ
- [ ] 現在のデータベースのバックアップ

## .htaccess ファイル移行手順（テーマ展開後）
1. [ ] テーマディレクトリからWordPressルートへファイルをコピー
   ```bash
   cd /path/to/wordpress/
   cp wp-content/themes/kei-portfolio/.htaccess-for-root .htaccess
   ```
2. [ ] 既存の `.htaccess` がある場合はバックアップ
   ```bash
   mv .htaccess .htaccess.backup
   ```
3. [ ] パーミッションと所有者を設定
   ```bash
   chmod 644 .htaccess
   chown www-data:www-data .htaccess
   ```

## テーマファイル移行手順
1. [ ] 本番環境の `wp-content/themes/` にアクセス
2. [ ] 既存の `kei-portfolio` ディレクトリをバックアップ
   ```bash
   mv kei-portfolio kei-portfolio.backup
   ```
3. [ ] アップロードディレクトリの内容をコピー
   ```bash
   cp -r /path/to/themes/kei-portfolio-upload kei-portfolio
   ```
4. [ ] ファイルのパーミッション設定
   ```bash
   chmod -R 755 kei-portfolio
   chown -R www-data:www-data kei-portfolio
   ```
5. [ ] `data/portfolio.json` の存在確認
6. [ ] `.htaccess-for-root` の存在確認

## データベース移行手順
1. [ ] phpMyAdminまたはコマンドラインでアクセス
2. [ ] 既存データベースのバックアップ
3. [ ] データベースのインポート
   ```sql
   mysql -u [username] -p [database_name] < database_*.sql
   ```
4. [ ] テーブルプレフィックスの確認（wp_ または wp_dev_）

## 移行後の確認事項
- [ ] WordPress管理画面へのログイン確認
- [ ] テーマの有効化確認
- [ ] 固定ページの表示確認
  - [ ] トップページ
  - [ ] About
  - [ ] Skills
  - [ ] Portfolio
  - [ ] Contact
- [ ] portfolio.json データの反映確認
- [ ] JavaScriptエラーの確認（ブラウザコンソール）
- [ ] レスポンシブデザインの確認
- [ ] お問い合わせフォームの動作確認

## トラブルシューティング
### portfolio.json が読み込まれない場合
1. ファイルパスの確認: `/wp-content/themes/kei-portfolio/data/portfolio.json`
2. ファイルパーミッションの確認: 644以上
3. JSONフォーマットの検証

### スタイルが崩れる場合
1. CSSファイルの読み込み確認
2. キャッシュのクリア
3. テーマの再有効化

### 404エラーが発生する場合
1. **.htaccessファイルの確認**
   - WordPressルートに `.htaccess` が存在することを確認
   - パーミッションが644であることを確認
   - mod_rewriteが有効になっていることを確認
2. **パーマリンク設定の更新**
   - 管理画面 → 設定 → パーマリンク設定
   - 設定を保存（変更なしでも保存をクリック）
3. **Apacheモジュールの確認**
   ```bash
   # mod_rewriteが有効か確認
   apache2ctl -M | grep rewrite
   # または
   httpd -M | grep rewrite
   ```
4. **固定ページのスラッグ確認**
   - 各固定ページのスラッグが正しいか確認
5. **サーバー設定の確認**
   - AllowOverride All が設定されているか確認

## 重要な注意事項
- portfolio.json には個人情報が含まれている可能性があるため、適切に管理すること
EOF

if [[ "$MODE" == "prod" ]]; then
    cat >> "$CHECKLIST_FILE" << EOF
- 本番環境のURLは \`$DEST_URL\` に設定済み
EOF
else
    cat >> "$CHECKLIST_FILE" << 'EOF'
- ローカルモードのためURL置換は実行されていません
EOF
fi

cat >> "$CHECKLIST_FILE" << 'EOF'
- データベースのテーブルプレフィックスを確認すること
EOF

success_msg "チェックリスト作成完了: $CHECKLIST_FILE"

# ============================================
# 5. エクスポート結果のサマリー
# ============================================
echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  エクスポート完了！                            ${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo "エクスポート先: $EXPORT_DIR/$DATE/"
echo ""
echo "準備完了:"
echo "  1. テーマファイル: $(basename "$THEME_UPLOAD_DIR")/ ($TOTAL_SIZE)"
echo "     - $TOTAL_FILES ファイル準備済み"
echo "     - portfolio.jsonを含む"
echo "     - .htaccess-for-rootを含む（WordPressルート用）"
echo "  2. データベース: $(basename "$DB_EXPORT_FILE") ($DB_SIZE)"
if [[ "$MODE" == "prod" ]]; then
    echo "     - URL置換済み: $SOURCE_URL → $DEST_URL"
else
    echo "     - URL置換なし（ローカルモード）"
fi
echo "  3. データベース圧縮: $(basename "${DB_EXPORT_FILE}.gz") ($DB_GZ_SIZE)"
echo "  4. 移行チェックリスト: migration-checklist.md"
echo ""
echo -e "${YELLOW}次のステップ:${NC}"
echo "1. $(basename "$THEME_UPLOAD_DIR")/ ディレクトリ全体を本番環境に転送"
echo "2. $EXPORT_DIR/$DATE/ 内のデータベースファイルを本番環境に転送"
echo "3. migration-checklist.md に従って移行作業を実施"
echo "4. 移行後の動作確認を実施"
echo ""
if [[ "$MODE" == "prod" ]]; then
    echo -e "${YELLOW}⚠ 重要: 本番環境での作業前に必ずバックアップを作成してください${NC}"
else
    echo -e "${YELLOW}ℹ ローカルモードで実行されました。URL置換は行われていません。${NC}"
fi