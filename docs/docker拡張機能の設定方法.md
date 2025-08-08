  理由

  1. Docker デーモン: Docker Desktop が Docker デーモン（エンジン）を提供
  2. VSCode 拡張機能: Docker 拡張機能は Docker デーモンと通信するため、Docker Desktop が必要
  3. コンテナ実行: コンテナを起動・管理するには Docker エンジンが必須

  動作の仕組み

  VSCode Docker拡張機能
      ↓
  Docker CLI (docker, docker-compose)
      ↓
  Docker デーモン (Docker Desktop が提供)
      ↓
  コンテナ

  使用時の流れ

  1. Mac 起動時: Docker Desktop を起動（自動起動設定も可能）
  2. VSCode で作業: Docker Desktop が起動していれば拡張機能が動作
  3. 作業終了時: Docker Desktop を終了してもOK（コンテナも停止）

  自動起動設定

  Docker Desktop → Settings → General → 「Start Docker Desktop when you log in」をオンにすると Mac 起動時に自動起動します。

