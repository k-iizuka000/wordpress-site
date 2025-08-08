#!/bin/bash

# WordPressデータベースエクスポートスクリプト
# ローカル環境のデータベースを本番環境用にエクスポート

# 設定
DB_CONTAINER="kei-portfolio-db"
DB_NAME="kei_portfolio_dev"
DB_USER="wp_user"
DB_PASSWORD="wp_password"
EXPORT_DIR="/Users/kei/work/wordpress-site/database-exports"
DATE=$(date +"%Y%m%d_%H%M%S")

# 色付き出力用の変数
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}===================================${NC}"
echo -e "${GREEN}WordPressデータベースエクスポート開始${NC}"
echo -e "${GREEN}===================================${NC}"

# エクスポートディレクトリを作成
if [ ! -d "$EXPORT_DIR" ]; then
    echo "エクスポートディレクトリを作成中..."
    mkdir -p "$EXPORT_DIR"
fi

# エクスポートタイプを選択
echo ""
echo "エクスポートタイプを選択してください："
echo "1) 全データ（構造＋データ）"
echo "2) 構造のみ"
echo "3) データのみ"
echo "4) 本番環境用（URLを置換）"
echo -n "選択 [1-4]: "
read export_type

case $export_type in
    1)
        EXPORT_OPTIONS=""
        FILENAME="full_backup_${DATE}.sql"
        ;;
    2)
        EXPORT_OPTIONS="--no-data"
        FILENAME="structure_only_${DATE}.sql"
        ;;
    3)
        EXPORT_OPTIONS="--no-create-info"
        FILENAME="data_only_${DATE}.sql"
        ;;
    4)
        EXPORT_OPTIONS=""
        FILENAME="production_ready_${DATE}.sql"
        ;;
    *)
        echo -e "${RED}無効な選択です${NC}"
        exit 1
        ;;
esac

EXPORT_FILE="$EXPORT_DIR/$FILENAME"

# データベースをエクスポート
echo ""
echo "データベースをエクスポート中..."
docker exec $DB_CONTAINER mysqldump \
    -u$DB_USER \
    -p$DB_PASSWORD \
    $DB_NAME \
    $EXPORT_OPTIONS \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --default-character-set=utf8mb4 \
    --routines \
    --triggers \
    --events > "$EXPORT_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ エクスポート成功${NC}"
    
    # 本番環境用の場合、URLを置換
    if [ "$export_type" = "4" ]; then
        echo ""
        echo "本番環境用にURLを置換中..."
        
        # バックアップを作成
        cp "$EXPORT_FILE" "${EXPORT_FILE}.backup"
        
        # URLを置換（localhost:8090 → kei-aokiki.dev）
        sed -i '' 's|http://localhost:8090|https://kei-aokiki.dev|g' "$EXPORT_FILE"
        
        # テーブルプレフィックスを置換（必要に応じて）
        echo -n "テーブルプレフィックスを変更しますか？ (wp_dev_ → wp_) [y/n]: "
        read change_prefix
        if [ "$change_prefix" = "y" ]; then
            sed -i '' 's/wp_dev_/wp_/g' "$EXPORT_FILE"
            echo -e "${GREEN}✓ テーブルプレフィックスを変更しました${NC}"
        fi
        
        echo -e "${GREEN}✓ 本番環境用の置換完了${NC}"
    fi
    
    # ファイルサイズを表示
    FILE_SIZE=$(du -h "$EXPORT_FILE" | cut -f1)
    echo ""
    echo -e "${GREEN}===================================${NC}"
    echo -e "${GREEN}エクスポート完了！${NC}"
    echo -e "${GREEN}===================================${NC}"
    echo ""
    echo "ファイル: $EXPORT_FILE"
    echo "サイズ: $FILE_SIZE"
    
    # 圧縮オプション
    echo ""
    echo -n "ファイルを圧縮しますか？ [y/n]: "
    read compress
    if [ "$compress" = "y" ]; then
        echo "圧縮中..."
        gzip -c "$EXPORT_FILE" > "${EXPORT_FILE}.gz"
        COMPRESSED_SIZE=$(du -h "${EXPORT_FILE}.gz" | cut -f1)
        echo -e "${GREEN}✓ 圧縮完了${NC}"
        echo "圧縮ファイル: ${EXPORT_FILE}.gz"
        echo "圧縮後サイズ: $COMPRESSED_SIZE"
    fi
    
    # 統計情報を表示
    echo ""
    echo "データベース統計："
    echo -n "  テーブル数: "
    docker exec $DB_CONTAINER mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" -s -N
    echo -n "  投稿数: "
    docker exec $DB_CONTAINER mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME -e "SELECT COUNT(*) FROM wp_dev_posts;" -s -N
    echo -n "  プロジェクト数: "
    docker exec $DB_CONTAINER mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME -e "SELECT COUNT(*) FROM wp_dev_posts WHERE post_type='project' AND post_status='publish';" -s -N
    
else
    echo -e "${RED}✗ エクスポート失敗${NC}"
    exit 1
fi

echo ""
echo "次のステップ："
echo "1. 本番環境のphpMyAdminにアクセス"
echo "2. 既存データのバックアップを作成"
echo "3. エクスポートしたファイルをインポート"
echo ""
echo -e "${YELLOW}注意: 本番環境にインポートする前に必ずバックアップを作成してください${NC}"