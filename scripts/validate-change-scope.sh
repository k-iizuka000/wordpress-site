#!/usr/bin/env bash
set -euo pipefail

# 禁止変更の検出スクリプト
# 用途: pre-commitやCIで、ステージ済み変更が禁止パスに触れていないか検査する
# 許可: 環境変数 ALLOW_TEST_MODS=1 が設定されている場合のみ、テスト関連の変更を許可

if [[ "${ALLOW_TEST_MODS:-}" == "1" ]]; then
  exit 0
fi

changed_files=$(git diff --cached --name-only || true)

violations=()
while IFS= read -r f; do
  [[ -z "$f" ]] && continue
  if [[ "$f" == run-tests.sh ]] || [[ "$f" == tests/* ]] ; then
    violations+=("$f")
  fi
  # テーマ直下のpackage.jsonでscripts.e2eを書き換える変更検出（粗め）
  if [[ "$f" == themes/*/package.json ]]; then
    if git diff --cached -- "$f" | grep -q '"e2e"'; then
      violations+=("$f (scripts.e2e)")
    fi
  fi
done <<< "$changed_files"

if (( ${#violations[@]} > 0 )); then
  echo "[ERROR] 禁止パスへの変更が検出されました（ALLOW_TEST_MODS=1 でのみ許可）:" >&2
  for v in "${violations[@]}"; do
    echo " - $v" >&2
  done
  exit 2
fi

exit 0

