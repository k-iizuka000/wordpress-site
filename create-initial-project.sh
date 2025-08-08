#!/bin/bash
set -euo pipefail

# WordPress初期データ投入スクリプト
# Projects投稿が存在しない場合のみ1件作成（idempotent）

# 設定
WP_PATH="${WP_PATH:-/var/www/html}"

# エラーハンドリング関数
error_exit() {
    echo "エラー: $1" >&2
    exit 1
}

# ログ関数
log_info() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# メイン処理開始
log_info "=== WordPress初期データ投入開始 ==="

# WP-CLIの存在確認
if ! command -v wp &> /dev/null; then
    error_exit "WP-CLIがインストールされていません"
fi

log_info "WP-CLIの存在を確認しました"

# WordPressの存在確認
if [ ! -f "$WP_PATH/wp-config.php" ]; then
    error_exit "指定されたパス ($WP_PATH) にWordPressが見つかりません"
fi

log_info "WordPress環境を確認しました (パス: $WP_PATH)"

# プロジェクト数の確認（より確実なcount方式）
log_info "既存プロジェクトを確認中..."
PROJECT_COUNT=$(wp post list --path="$WP_PATH" --post_type=project --post_status=publish --format=count 2>/dev/null || echo "0")

if [ "$PROJECT_COUNT" -eq 0 ]; then
    log_info "プロジェクトが存在しないため、サンプルプロジェクトを作成します"
    
    POST_ID=$(wp post create \
        --path="$WP_PATH" \
        --post_type=project \
        --post_status=publish \
        --post_title="Sample Project" \
        --post_content="Auto seeded sample project for local testing." \
        --porcelain) || error_exit "プロジェクトの作成に失敗しました"
    
    log_info "✓ サンプルプロジェクトを作成しました (ID: $POST_ID)"
else
    log_info "ℹ️ 既存プロジェクト（${PROJECT_COUNT}件）が存在するためスキップしました"
fi

log_info "=== 処理完了 ==="