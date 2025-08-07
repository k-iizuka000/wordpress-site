# portfolio.json データ反映設計書

## 1. 概要

本設計書は、`portfolio.json`のデータをWordPressサイトの3つの主要画面（ホーム画面、自己紹介画面、スキル一覧画面）に反映するための実装設計を定義します。

## 2. データマッピング設計

### 2.1 ホーム画面（front-page.php）

#### 表示項目
| JSONデータ | 表示位置 | 表示内容 |
|-----------|---------|---------|
| summary.totalExperience | Hero Section | 「10年以上の開発経験」として表示 |
| summary.highlights[0] | About Preview | 最も重要な強みとして表示 |
| skills（抜粋） | Skills Preview | 主要技術を6-8個表示 |
| projects（最新3件） | Projects Preview | 最新プロジェクト3件のカード表示 |
| inProgress（全件） | 進行中プロジェクト | 進行中の全プロジェクトを表示 |

#### 実装方法
```php
// functions.phpにデータ取得関数を追加
function get_portfolio_data() {
    $json_file = get_template_directory() . '/data/portfolio.json';
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        return json_decode($json_data, true);
    }
    return null;
}
```

### 2.2 自己紹介画面（page-about.php）

#### 表示項目
| JSONデータ | 表示位置 | 表示内容 |
|-----------|---------|---------|
| about.title | ページタイトル | 「About Me」として表示 |
| about.description | メインコンテンツ | 自己紹介文を段落分けして表示 |
| summary.totalExperience | 経験年数セクション | 「10年以上」の経験として強調表示 |
| summary.highlights | 強みセクション | 箇条書きで全項目表示 |
| summary.coreTechnologies | 技術スタック | 表形式で技術名、経験年数、レベルを表示 |
| projects（最新5件） | キャリアハイライト | タイムライン形式で表示 |

#### テンプレート構成
```
template-parts/about/
├── hero.php          # タイトルとメイン紹介文
├── experience.php    # 経験年数と強み
├── tech-stack.php    # コア技術の表
└── career.php        # キャリアハイライト
```

### 2.3 スキル一覧画面（page-skills.php）

#### 表示項目
| JSONデータ | 表示位置 | 表示内容 |
|-----------|---------|---------|
| skills.frontend | フロントエンド | カード形式でスキル一覧表示 |
| skills.backend | バックエンド | カード形式でスキル一覧表示 |
| skills.other | その他技術 | カード形式でスキル一覧表示 |
| summary.coreTechnologies | 習熟度詳細 | プログレスバーで習熟度表示 |

#### スキルレベル変換ロジック
```php
function convert_skill_level($level) {
    $levels = [
        'エキスパート' => 90,
        '上級' => 75,
        '中級' => 60,
        '初級' => 40
    ];
    return $levels[$level] ?? 50;
}
```

## 3. データの抜け漏れチェック

### 3.1 使用されるデータ
✅ **完全に使用される項目：**
- about.title
- about.description
- skills.frontend（全項目）
- skills.backend（全項目）
- skills.other（全項目）
- summary.totalExperience
- summary.highlights（全項目）
- summary.coreTechnologies（全項目）
- inProgress（全項目）

### 3.2 部分的に使用される項目
⚠️ **部分使用の項目：**
- projects：最新3-5件のみホーム・自己紹介で使用
  - **対応策：** ポートフォリオページで全件表示

### 3.3 未使用項目の確認
❌ **現在の設計で未使用：**
- projects.github（GitHubリンク）
  - **対応策：** 各プロジェクト詳細ページで表示
- projects.technologies（詳細な技術情報）
  - **対応策：** プロジェクト詳細ページで表示

## 4. 実装詳細設計

### 4.1 データ取得レイヤー

```php
// inc/portfolio-data.php
class Portfolio_Data {
    private static $instance = null;
    private $data = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_data();
    }
    
    private function load_data() {
        $json_file = get_template_directory() . '/data/portfolio.json';
        if (file_exists($json_file)) {
            $json_data = file_get_contents($json_file);
            $this->data = json_decode($json_data, true);
        }
    }
    
    public function get_about() {
        return $this->data['about'] ?? null;
    }
    
    public function get_skills() {
        return $this->data['skills'] ?? null;
    }
    
    public function get_summary() {
        return $this->data['summary'] ?? null;
    }
    
    public function get_projects($limit = null) {
        $projects = $this->data['projects'] ?? [];
        if ($limit) {
            return array_slice($projects, 0, $limit);
        }
        return $projects;
    }
    
    public function get_in_progress() {
        return $this->data['inProgress'] ?? [];
    }
}
```

### 4.2 テンプレートでの使用例

#### ホーム画面での実装
```php
<?php
$portfolio = Portfolio_Data::get_instance();
$summary = $portfolio->get_summary();
$recent_projects = $portfolio->get_projects(3);
$in_progress = $portfolio->get_in_progress();
?>

<!-- Hero Section -->
<section class="hero">
    <h1>自動化で<span>未来</span>を創るエンジニア</h1>
    <p class="experience"><?php echo esc_html($summary['totalExperience']); ?>の開発経験</p>
</section>

<!-- Projects Preview -->
<section class="projects-preview">
    <?php foreach ($recent_projects as $project): ?>
        <div class="project-card">
            <h3><?php echo esc_html($project['title']); ?></h3>
            <p><?php echo esc_html($project['description']); ?></p>
        </div>
    <?php endforeach; ?>
</section>
```

### 4.3 キャッシュ戦略

```php
// Transient APIを使用したキャッシュ
function get_cached_portfolio_data() {
    $cache_key = 'portfolio_json_data';
    $cached = get_transient($cache_key);
    
    if (false === $cached) {
        $json_file = get_template_directory() . '/data/portfolio.json';
        if (file_exists($json_file)) {
            $json_data = file_get_contents($json_file);
            $cached = json_decode($json_data, true);
            set_transient($cache_key, $cached, HOUR_IN_SECONDS);
        }
    }
    
    return $cached;
}
```

## 5. データ更新フロー

### 5.1 管理画面からの更新（将来実装）
1. カスタム設定ページを作成
2. JSON形式でのインポート/エクスポート機能
3. 個別項目の編集機能

### 5.2 ファイル直接更新
1. `/data/portfolio.json`を直接編集
2. キャッシュクリア（Transient削除）
3. サイトに反映

## 6. テスト項目

### 6.1 データ表示テスト
- [ ] ホーム画面に経験年数が表示される
- [ ] ホーム画面に最新プロジェクト3件が表示される
- [ ] ホーム画面に進行中プロジェクトが全件表示される
- [ ] 自己紹介画面に全ての自己紹介文が表示される
- [ ] 自己紹介画面にコア技術が表形式で表示される
- [ ] スキル一覧画面に全スキルが3カテゴリで表示される

### 6.2 エラーハンドリングテスト
- [ ] JSONファイルが存在しない場合のフォールバック
- [ ] JSONパースエラー時の処理
- [ ] データ項目が欠損している場合の処理

### 6.3 パフォーマンステスト
- [ ] キャッシュが正しく動作する
- [ ] 大量データでもページロードが高速

## 7. セキュリティ考慮事項

1. **エスケープ処理**
   - 全ての出力で`esc_html()`、`esc_url()`を使用
   
2. **ファイルアクセス制限**
   - JSONファイルは直接アクセスできない場所に配置
   
3. **データ検証**
   - JSONデータの構造を検証してから使用

## 8. 実装優先順位

1. **Phase 1（必須）**
   - Portfolio_Dataクラスの実装
   - ホーム画面への基本データ反映
   
2. **Phase 2（重要）**
   - 自己紹介画面の完全実装
   - スキル一覧画面の完全実装
   
3. **Phase 3（推奨）**
   - キャッシュ機能の実装
   - エラーハンドリングの強化
   
4. **Phase 4（オプション）**
   - 管理画面からの編集機能
   - データのバックアップ/リストア機能

## 9. 実装チェックリスト

### 必須実装項目
- [ ] Portfolio_Dataクラスの作成
- [ ] functions.phpへの読み込み追加
- [ ] front-page.phpの更新
- [ ] page-about.phpの更新
- [ ] page-skills.phpの更新
- [ ] 各template-partsファイルの更新

### データ反映確認
- [ ] about（自己紹介）データの全項目反映
- [ ] skills（スキル）データの全項目反映
- [ ] summary（サマリー）データの全項目反映
- [ ] projects（プロジェクト）データの適切な表示
- [ ] inProgress（進行中）データの全項目反映

## 10. 今後の拡張案

1. **プロジェクト詳細ページ**
   - 各プロジェクトの詳細情報表示
   - GitHub連携
   - デモサイトリンク
   
2. **スキルの視覚化**
   - チャート表示
   - アニメーション効果
   - インタラクティブ要素
   
3. **多言語対応**
   - 英語版portfolio.jsonの追加
   - 言語切り替え機能