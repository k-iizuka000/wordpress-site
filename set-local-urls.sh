#!/bin/bash
set -e

# デフォルト値の設定
DEFAULT_URL="http://localhost:8090"
NEW_URL="${1:-$DEFAULT_URL}"

# WP-CLIの存在確認
if ! command -v wp &> /dev/null; then
    echo "エラー: WP-CLIがインストールされていません" >&2
    exit 1
fi

# WordPressディレクトリの確認
if [ ! -f wp-config.php ]; then
    echo "エラー: WordPressのルートディレクトリで実行してください" >&2
    exit 1
fi

echo "==================================="
echo "WordPress URLローカル環境設定ツール"
echo "==================================="
echo ""
echo "【現在の設定】"
echo "  サイトURL: $(wp option get siteurl 2>/dev/null || echo '取得失敗')"
echo "  ホームURL: $(wp option get home 2>/dev/null || echo '取得失敗')"
echo ""
echo "【更新処理】"
echo "  新しいURL: $NEW_URL"

# URL更新の実行
if wp option update siteurl "$NEW_URL" && \
   wp option update home "$NEW_URL"; then
    echo ""
    echo "【更新後の設定】"
    echo "  サイトURL: $(wp option get siteurl)"
    echo "  ホームURL: $(wp option get home)"
    echo ""
    echo "✅ 設定が完了しました"
else
    echo "❌ URLの更新に失敗しました" >&2
    exit 1
fi