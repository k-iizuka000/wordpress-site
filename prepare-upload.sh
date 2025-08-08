#!/bin/bash

# WordPressテーマアップロード用ファイル整理スクリプト
# 必要なファイルのみをアップロード用ディレクトリにコピー

# 設定
SOURCE_DIR="/Users/kei/work/wordpress-site/themes/kei-portfolio"
DEST_DIR="/Users/kei/work/wordpress-site/themes/kei-portfolio-upload"

# 色付き出力用の変数
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}===================================${NC}"
echo -e "${GREEN}WordPressテーマアップロード準備開始${NC}"
echo -e "${GREEN}===================================${NC}"

# 出力先ディレクトリが存在する場合は削除
if [ -d "$DEST_DIR" ]; then
    echo -e "${YELLOW}既存の出力ディレクトリを削除中...${NC}"
    rm -rf "$DEST_DIR"
fi

# 出力先ディレクトリを作成
echo "出力先ディレクトリを作成中..."
mkdir -p "$DEST_DIR"

# rsyncで必要なファイルのみコピー（除外リストを使用）
echo "必要なファイルをコピー中..."
rsync -av \
    --exclude='node_modules/' \
    --exclude='vendor/' \
    --exclude='tests/' \
    --exclude='coverage/' \
    --exclude='learning/' \
    --exclude='data/' \
    --exclude='tasks/' \
    --exclude='.git/' \
    --exclude='.idea/' \
    --exclude='.sass-cache/' \
    --exclude='package.json' \
    --exclude='package-lock.json' \
    --exclude='composer.json' \
    --exclude='composer.lock' \
    --exclude='composer.phar' \
    --exclude='composer-setup.php' \
    --exclude='phpunit.xml*' \
    --exclude='jest.config.js' \
    --exclude='.phpunit.result.cache' \
    --exclude='*test*.php' \
    --exclude='*test*.sh' \
    --exclude='run-group*.sh' \
    --exclude='setup-test-env.sh' \
    --exclude='.babelrc' \
    --exclude='webpack.config.js' \
    --exclude='.eslintrc*' \
    --exclude='.gitignore' \
    --exclude='.DS_Store' \
    --exclude='README*.md' \
    --exclude='*.log' \
    --exclude='backup_*.tar.gz' \
    --exclude='create-pages-cli.php' \
    --exclude='phpunit-simple.xml' \
    --exclude='phpunit-syntax.xml' \
    --exclude='simple-bootstrap.php' \
    --exclude='simple-test-runner.php' \
    --exclude='test-portfolio-data.php' \
    "$SOURCE_DIR/" "$DEST_DIR/"

# 結果を確認
echo ""
echo -e "${GREEN}===================================${NC}"
echo -e "${GREEN}コピー完了！${NC}"
echo -e "${GREEN}===================================${NC}"

# ディレクトリサイズを表示
echo ""
echo "ディレクトリサイズ比較:"
echo -n "元のディレクトリ: "
du -sh "$SOURCE_DIR" | cut -f1
echo -n "アップロード用: "
du -sh "$DEST_DIR" | cut -f1

# 主要ファイルの存在確認
echo ""
echo "主要ファイルの確認:"
required_files=(
    "style.css"
    "index.php"
    "functions.php"
    "header.php"
    "footer.php"
)

all_exists=true
for file in "${required_files[@]}"; do
    if [ -f "$DEST_DIR/$file" ]; then
        echo -e "  ✓ $file"
    else
        echo -e "  ✗ $file ${YELLOW}(見つかりません)${NC}"
        all_exists=false
    fi
done

# 重要ディレクトリの確認
echo ""
echo "重要ディレクトリの確認:"
required_dirs=(
    "template-parts"
    "inc"
    "assets"
)

for dir in "${required_dirs[@]}"; do
    if [ -d "$DEST_DIR/$dir" ]; then
        echo -e "  ✓ $dir/"
    else
        echo -e "  ✗ $dir/ ${YELLOW}(見つかりません)${NC}"
    fi
done

echo ""
if [ "$all_exists" = true ]; then
    echo -e "${GREEN}アップロード準備が完了しました！${NC}"
    echo "出力先: $DEST_DIR"
else
    echo -e "${YELLOW}警告: 一部の必須ファイルが見つかりません${NC}"
    echo "出力先: $DEST_DIR"
fi