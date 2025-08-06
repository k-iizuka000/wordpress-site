<?php // WordPress Theme File ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>田中健太 - フリーランスエンジニア</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1E90FF',
            secondary: '#32CD32'
          },
          borderRadius: {
            'none': '0px',
            'sm': '4px',
            DEFAULT: '8px',
            'md': '12px',
            'lg': '16px',
            'xl': '20px',
            '2xl': '24px',
            '3xl': '32px',
            'full': '9999px',
            'button': '8px'
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.css">
</head>
<body class="bg-white text-gray-800">
  <nav class="fixed top-0 w-full bg-white/90 backdrop-blur-md z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-4">
      <div class="flex justify-between items-center">
        <div class="font-['Pacifico'] text-2xl text-primary">Kenta Tanaka</div>
        <div class="hidden md:flex space-x-8">
          <a href="#home" class="text-gray-600 hover:text-primary transition-colors">ホーム</a>
          <a href="#about" class="text-gray-600 hover:text-primary transition-colors">自己紹介</a>
          <a href="#portfolio" class="text-gray-600 hover:text-primary transition-colors">制作実績</a>
          <a href="#skills" class="text-gray-600 hover:text-primary transition-colors">スキル</a>
          <a href="#contact" class="text-gray-600 hover:text-primary transition-colors">お問い合わせ</a>
        </div>
        <button id="mobile-menu-btn" class="md:hidden w-8 h-8 flex items-center justify-center">
          <i class="ri-menu-line text-xl"></i>
        </button>
      </div>
    </div>
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
      <div class="px-6 py-4 space-y-3">
        <a href="#home" class="block text-gray-600 hover:text-primary transition-colors">ホーム</a>
        <a href="#about" class="block text-gray-600 hover:text-primary transition-colors">自己紹介</a>
        <a href="#portfolio" class="block text-gray-600 hover:text-primary transition-colors">制作実績</a>
        <a href="#skills" class="block text-gray-600 hover:text-primary transition-colors">スキル</a>
        <a href="#contact" class="block text-gray-600 hover:text-primary transition-colors">お問い合わせ</a>
      </div>
    </div>
  </nav>
  <section id="home" class="min-h-screen hero-bg relative flex items-center justify-center">
    <div class="absolute inset-0 gradient-overlay"></div>
    <div class="relative z-10 text-center text-white px-6 max-w-4xl mx-auto">
      <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
        小さなアイデアを<br>
        <span class="text-yellow-300">価値あるプロダクト</span>へ
      </h1>
      <p class="text-xl md:text-2xl mb-8 opacity-90">
        フリーランスエンジニア 田中健太<br>
        運用まで回るプロダクト開発のスペシャリスト
      </p>
      <button class="bg-white text-primary px-8 py-4 !rounded-button text-lg font-semibold hover:bg-gray-50 transition-colors whitespace-nowrap">
        ポートフォリオを見る
      </button>
    </div>
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 scroll-indicator">
      <div class="w-6 h-6 flex items-center justify-center text-white">
        <i class="ri-arrow-down-line text-2xl"></i>
      </div>
    </div>
  </section>
  <section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold mb-4">はじめまして</h2>
        <p class="text-xl text-gray-600">明るく、ポジティブに、価値あるプロダクトを作り続けています</p>
      </div>
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div class="text-center">
          <div class="w-64 h-64 mx-auto mb-8 rounded-full overflow-hidden">
            <img src="https://readdy.ai/api/search-image?query=Professional%20portrait%20of%20a%20cheerful%20Japanese%20male%20engineer%20in%20his%2030s%2C%20wearing%20casual%20business%20attire%2C%20bright%20smile%2C%20confident%20expression%2C%20clean%20white%20background%2C%20modern%20professional%20photography%20style%2C%20natural%20lighting%2C%20approachable%20and%20trustworthy%20appearance&width=400&height=400&seq=profile001&orientation=squarish" alt="田中健太" class="w-full h-full object-cover object-top">
          </div>
          <h3 class="text-2xl font-bold mb-2">田中健太</h3>
          <p class="text-gray-600 mb-4">フリーランスエンジニア</p>
          <div class="flex justify-center space-x-4">
            <div class="w-8 h-8 flex items-center justify-center text-primary">
              <i class="ri-github-fill text-2xl"></i>
            </div>
            <div class="w-8 h-8 flex items-center justify-center text-primary">
              <i class="ri-linkedin-fill text-2xl"></i>
            </div>
            <div class="w-8 h-8 flex items-center justify-center text-primary">
              <i class="ri-twitter-fill text-2xl"></i>
            </div>
          </div>
        </div>
        <div>
          <div class="space-y-8">
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-full">
                <i class="ri-lightbulb-line text-2xl text-primary"></i>
              </div>
              <div>
                <h4 class="text-xl font-semibold mb-2">アイデアを形に</h4>
                <p class="text-gray-600">小さなアイデアから始まり、ユーザーに価値を提供する完成されたプロダクトまで一貫して開発します。</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 flex items-center justify-center bg-secondary/10 rounded-full">
                <i class="ri-settings-3-line text-2xl text-secondary"></i>
              </div>
              <div>
                <h4 class="text-xl font-semibold mb-2">運用まで考慮</h4>
                <p class="text-gray-600">開発だけでなく、保守性、拡張性、運用コストまで考慮したプロダクト設計を心がけています。</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 flex items-center justify-center bg-yellow-100 rounded-full">
                <i class="ri-bike-line text-2xl text-yellow-600"></i>
              </div>
              <div>
                <h4 class="text-xl font-semibold mb-2">チャレンジ精神</h4>
                <p class="text-gray-600">ロードバイクで培った持続力と挑戦する気持ちを、プロダクト開発にも活かしています。</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="py-20">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
          <div class="w-16 h-16 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-6">
            <i class="ri-user-line text-3xl text-primary"></i>
          </div>
          <h3 class="text-xl font-semibold mb-4">自己紹介</h3>
          <p class="text-gray-600 mb-6">エンジニアとしての経歴とロードバイクへの情熱をご紹介</p>
          <button class="bg-primary text-white px-6 py-3 !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
            詳しく見る
          </button>
        </div>
<div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
<div class="w-16 h-16 flex items-center justify-center bg-secondary/10 rounded-full mx-auto mb-6">
<i class="ri-code-box-line text-3xl text-secondary"></i>
</div>
<h3 class="text-xl font-semibold mb-4">制作実績</h3>
<p class="text-gray-600 mb-6">これまでに開発したプロダクトとツールの紹介</p>
<a href="https://readdy.ai/home/e5ffdb93-4fd9-45d7-ba60-e5099c6b38aa/f7acfea6-0679-4ccc-b45d-ad24763ab736" data-readdy="true">
<button class="bg-secondary text-white px-6 py-3 !rounded-button hover:bg-secondary/90 transition-colors whitespace-nowrap">
作品を見る
</button>
</a>
</div>
<div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
<div class="w-16 h-16 flex items-center justify-center bg-yellow-100 rounded-full mx-auto mb-6">
<i class="ri-tools-line text-3xl text-yellow-600"></i>
</div>
<h3 class="text-xl font-semibold mb-4">スキル</h3>
<p class="text-gray-600 mb-6">対応可能な技術スタックとツールの一覧</p>
<button class="bg-yellow-600 text-white px-6 py-3 !rounded-button hover:bg-yellow-700 transition-colors whitespace-nowrap">
スキルを見る
</button>
</div>
<div class="bg-white p-8 rounded-xl shadow-lg card-hover text-center">
<div class="w-16 h-16 flex items-center justify-center bg-purple-100 rounded-full mx-auto mb-6">
<i class="ri-mail-line text-3xl text-purple-600"></i>
</div>
<h3 class="text-xl font-semibold mb-4">お問い合わせ</h3>
<p class="text-gray-600 mb-6">プロジェクトのご相談やお仕事のご依頼はこちら</p>
<button class="bg-purple-600 text-white px-6 py-3 !rounded-button hover:bg-purple-700 transition-colors whitespace-nowrap">
相談する
</button>
</div>
</div>
</div>
</section>
<section id="about" class="py-20 bg-gray-50">
<div class="max-w-6xl mx-auto px-6">
<div class="text-center mb-16">
<h2 class="text-4xl font-bold mb-4">自己紹介</h2>
<p class="text-xl text-gray-600">エンジニアとしての歩みと、ロードバイクから学んだこと</p>
</div>
<div class="grid lg:grid-cols-2 gap-16">
<div>
<div class="bg-white p-8 rounded-xl shadow-lg mb-8">
<h3 class="text-2xl font-semibold mb-6">プロフィール</h3>
<div class="space-y-4">
<div class="flex items-center space-x-3">
<div class="w-6 h-6 flex items-center justify-center text-primary">
<i class="ri-user-line"></i>
</div>
<span>田中健太（32歳）</span>
</div>
<div class="flex items-center space-x-3">
<div class="w-6 h-6 flex items-center justify-center text-primary">
<i class="ri-map-pin-line"></i>
</div>
<span>東京都在住</span>
</div>
<div class="flex items-center space-x-3">
<div class="w-6 h-6 flex items-center justify-center text-primary">
<i class="ri-briefcase-line"></i>
</div>
<span>フリーランスエンジニア（3年目）</span>
</div>
<div class="flex items-center space-x-3">
<div class="w-6 h-6 flex items-center justify-center text-secondary">
<i class="ri-bike-line"></i>
</div>
<span>ロードバイク歴 8年</span>
</div>
</div>
</div>
<div class="bg-white p-8 rounded-xl shadow-lg">
<h3 class="text-2xl font-semibold mb-6">ロードバイクへの情熱</h3>
<div class="mb-6">
<img src="https://readdy.ai/api/search-image?query=Road%20cyclist%20in%20professional%20gear%20riding%20through%20beautiful%20mountain%20scenery%20during%20golden%20hour%2C%20dynamic%20cycling%20action%20shot%2C%20scenic%20landscape%20with%20rolling%20hills%2C%20vibrant%20blue%20and%20green%20natural%20colors%2C%20inspirational%20outdoor%20sports%20photography%2C%20clear%20blue%20sky%20background&width=600&height=400&seq=cycling001&orientation=landscape" alt="ロードバイク" class="w-full h-48 object-cover object-top rounded-lg">
</div>
<p class="text-gray-600 mb-4">
週末は愛車と共に都内から郊外まで、様々なルートを走っています。ロードバイクから学んだ「継続する力」「目標に向かって努力する姿勢」「チームワークの大切さ」は、エンジニアとしての仕事にも大いに活かされています。
</p>
<p class="text-gray-600">
特に長距離ライドで培った集中力と持続力は、複雑なプロダクト開発において非常に重要な要素となっています。
</p>
</div>
</div>
<div>
<div class="bg-white p-8 rounded-xl shadow-lg">
<h3 class="text-2xl font-semibold mb-8">エンジニアとしての歩み</h3>
<div class="space-y-8">
<div class="flex items-start space-x-4">
<div class="w-12 h-12 flex items-center justify-center bg-primary rounded-full text-white font-semibold">
2018
</div>
<div>
<h4 class="text-lg font-semibold mb-2">Web 制作会社入社</h4>
<p class="text-gray-600">HTML/CSS/JavaScript を中心とした Web サイト制作からキャリアをスタート。基礎的なプログラミングスキルを習得。</p>
</div>
</div>
<div class="flex items-start space-x-4">
<div class="w-12 h-12 flex items-center justify-center bg-primary rounded-full text-white font-semibold">
2020
</div>
<div>
<h4 class="text-lg font-semibold mb-2">SaaS 企業へ転職</h4>
<p class="text-gray-600">React/Node.js を使った Web アプリケーション開発に従事。チーム開発とアジャイル開発手法を経験。</p>
</div>
</div>
<div class="flex items-start space-x-4">
<div class="w-12 h-12 flex items-center justify-center bg-secondary rounded-full text-white font-semibold">
2022
</div>
<div>
<h4 class="text-lg font-semibold mb-2">フリーランス独立</h4>
<p class="text-gray-600">より多様なプロジェクトに携わりたいという思いから独立。スタートアップから大企業まで幅広いクライアントと協業。</p>
</div>
</div>
<div class="flex items-start space-x-4">
<div class="w-12 h-12 flex items-center justify-center bg-yellow-600 rounded-full text-white font-semibold">
現在
</div>
<div>
<h4 class="text-lg font-semibold mb-2">プロダクト開発支援</h4>
<p class="text-gray-600">アイデア段階から運用まで、一貫したプロダクト開発支援を提供。技術選定から保守性まで考慮した設計を得意としています。</p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<section id="portfolio" class="py-20">
<div class="max-w-6xl mx-auto px-6">
<div class="text-center mb-16">
<h2 class="text-4xl font-bold mb-4">制作実績</h2>
<p class="text-xl text-gray-600">これまでに開発したプロダクトとツールをご紹介します</p>
</div>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
<div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
<div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 relative">
<img src="https://readdy.ai/api/search-image?query=Modern%20e-commerce%20web%20application%20interface%20displayed%20on%20laptop%20screen%2C%20clean%20minimalist%20design%20with%20blue%20and%20white%20color%20scheme%2C%20product%20catalog%20layout%2C%20shopping%20cart%20functionality%2C%20professional%20business%20application%20screenshot%2C%20high-tech%20digital%20commerce%20platform&width=400&height=300&seq=ecommerce001&orientation=landscape" alt="E-commerce Platform" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<h3 class="text-xl font-semibold mb-3">E-commerce プラットフォーム</h3>
<p class="text-gray-600 mb-4">中小企業向けの EC サイト構築プラットフォーム。直感的な管理画面と高いカスタマイズ性を実現。</p>
<div class="flex flex-wrap gap-2 mb-4">
<span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">React</span>
<span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Node.js</span>
<span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">PostgreSQL</span>
</div>
<button class="text-primary hover:text-primary/80 font-semibold">
詳細を見る →
</button>
</div>
</div>
<div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
<div class="h-48 bg-gradient-to-br from-green-400 to-green-600 relative">
<img src="https://readdy.ai/api/search-image?query=Task%20management%20application%20dashboard%20on%20computer%20screen%2C%20organized%20project%20boards%20with%20colorful%20cards%2C%20team%20collaboration%20interface%2C%20productivity%20software%20design%2C%20clean%20modern%20UI%20with%20green%20accent%20colors%2C%20professional%20project%20management%20tool&width=400&height=300&seq=taskapp001&orientation=landscape" alt="Task Management App" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<h3 class="text-xl font-semibold mb-3">タスク管理アプリ</h3>
<p class="text-gray-600 mb-4">チーム向けのタスク管理ツール。リアルタイム同期とガントチャート機能を搭載。</p>
<div class="flex flex-wrap gap-2 mb-4">
<span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">Vue.js</span>
<span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">Firebase</span>
<span class="px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">TypeScript</span>
</div>
<button class="text-primary hover:text-primary/80 font-semibold">
詳細を見る →
</button>
</div>
</div>
<div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
<div class="h-48 bg-gradient-to-br from-purple-400 to-purple-600 relative">
<img src="https://readdy.ai/api/search-image?query=Data%20analytics%20dashboard%20with%20colorful%20charts%20and%20graphs%20on%20computer%20monitor%2C%20business%20intelligence%20visualization%2C%20real-time%20metrics%20display%2C%20modern%20analytics%20interface%20with%20purple%20theme%2C%20professional%20data%20visualization%20tool&width=400&height=300&seq=analytics001&orientation=landscape" alt="Analytics Dashboard" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<h3 class="text-xl font-semibold mb-3">データ分析ダッシュボード</h3>
<p class="text-gray-600 mb-4">マーケティングデータを可視化するダッシュボード。リアルタイムでの KPI 監視が可能。</p>
<div class="flex flex-wrap gap-2 mb-4">
<span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm rounded-full">Python</span>
<span class="px-3 py-1 bg-blue-100 text-blue-800 text-blue-800 text-sm rounded-full">Django</span>
<span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded-full">Chart.js</span>
</div>
<button class="text-primary hover:text-primary/80 font-semibold">
詳細を見る →
</button>
</div>
</div>
<div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
<div class="h-48 bg-gradient-to-br from-yellow-400 to-orange-500 relative">
<img src="https://readdy.ai/api/search-image?query=Mobile%20learning%20application%20interface%20on%20smartphone%20screen%2C%20educational%20app%20with%20interactive%20lessons%2C%20bright%20colorful%20design%20with%20yellow%20and%20orange%20gradients%2C%20student-friendly%20UI%20design%2C%20modern%20e-learning%20mobile%20platform&width=400&height=300&seq=learning001&orientation=landscape" alt="Learning Platform" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<h3 class="text-xl font-semibold mb-3">オンライン学習プラットフォーム</h3>
<p class="text-gray-600 mb-4">プログラミング学習者向けのオンラインプラットフォーム。進捗管理と質問機能を実装。</p>
<div class="flex flex-wrap gap-2 mb-4">
<span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">Next.js</span>
<span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Supabase</span>
<span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">Tailwind</span>
</div>
<button class="text-primary hover:text-primary/80 font-semibold">
詳細を見る →
</button>
</div>
</div>
<div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
<div class="h-48 bg-gradient-to-br from-red-400 to-pink-500 relative">
<img src="https://readdy.ai/api/search-image?query=Restaurant%20reservation%20system%20interface%20on%20tablet%20device%2C%20booking%20calendar%20with%20time%20slots%2C%20elegant%20restaurant%20management%20software%2C%20red%20and%20pink%20color%20scheme%2C%20hospitality%20industry%20application%20design&width=400&height=300&seq=restaurant001&orientation=landscape" alt="Reservation System" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<h3 class="text-xl font-semibold mb-3">予約管理システム</h3>
<p class="text-gray-600 mb-4">レストラン向けの予約管理システム。顧客管理と売上分析機能も搭載。</p>
<div class="flex flex-wrap gap-2 mb-4">
<span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">Laravel</span>
<span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm rounded-full">MySQL</span>
<span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Vue.js</span>
</div>
<button class="text-primary hover:text-primary/80 font-semibold">
詳細を見る →
</button>
</div>
</div>
<div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
<div class="h-48 bg-gradient-to-br from-indigo-400 to-blue-500 relative">
<img src="https://readdy.ai/api/search-image?query=Fitness%20tracking%20mobile%20application%20on%20smartphone%2C%20workout%20progress%20charts%20and%20exercise%20routines%2C%20health%20and%20wellness%20app%20interface%2C%20indigo%20and%20blue%20color%20theme%2C%20modern%20fitness%20technology%20design&width=400&height=300&seq=fitness001&orientation=landscape" alt="Fitness App" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<h3 class="text-xl font-semibold mb-3">フィットネス追跡アプリ</h3>
<p class="text-gray-600 mb-4">個人向けのフィットネス記録アプリ。運動データの可視化とモチベーション機能を実装。</p>
<div class="flex flex-wrap gap-2 mb-4">
<span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">React Native</span>
<span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">Firebase</span>
<span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">Redux</span>
</div>
<button class="text-primary hover:text-primary/80 font-semibold">
詳細を見る →
</button>
</div>
</div>
</div>
</div>
</section>
<section id="skills" class="py-20 bg-gray-50">
<div class="max-w-6xl mx-auto px-6">
<div class="text-center mb-16">
<h2 class="text-4xl font-bold mb-4">スキル</h2>
<p class="text-xl text-gray-600">対応可能な技術スタックとツール</p>
</div>
<div class="grid lg:grid-cols-2 gap-12">
<div class="space-y-8">
<div class="bg-white p-8 rounded-xl shadow-lg">
<h3 class="text-2xl font-semibold mb-6 flex items-center">
<div class="w-8 h-8 flex items-center justify-center bg-blue-100 rounded-full mr-3">
<i class="ri-window-line text-blue-600"></i>
</div>
フロントエンド
</h3>
<div class="space-y-4">
<div class="flex items-center justify-between">
<span class="font-medium">React / Next.js</span>
<span class="text-sm text-gray-500">5年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-primary h-2 rounded-full" style="width: 90%"></div>
</div>
<div class="flex items-center justify-between">
<span class="font-medium">Vue.js / Nuxt.js</span>
<span class="text-sm text-gray-500">4年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-secondary h-2 rounded-full" style="width: 85%"></div>
</div>
<div class="flex items-center justify-between">
<span class="font-medium">TypeScript</span>
<span class="text-sm text-gray-500">3年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-yellow-500 h-2 rounded-full" style="width: 80%"></div>
</div>
</div>
</div>
<div class="bg-white p-8 rounded-xl shadow-lg">
<h3 class="text-2xl font-semibold mb-6 flex items-center">
<div class="w-8 h-8 flex items-center justify-center bg-green-100 rounded-full mr-3">
<i class="ri-server-line text-green-600"></i>
</div>
バックエンド
</h3>
<div class="space-y-4">
<div class="flex items-center justify-between">
<span class="font-medium">Node.js / Express</span>
<span class="text-sm text-gray-500">4年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-primary h-2 rounded-full" style="width: 85%"></div>
</div>
<div class="flex items-center justify-between">
<span class="font-medium">Python / Django</span>
<span class="text-sm text-gray-500">3年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-secondary h-2 rounded-full" style="width: 75%"></div>
</div>
<div class="flex items-center justify-between">
<span class="font-medium">PHP / Laravel</span>
<span class="text-sm text-gray-500">3年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-purple-500 h-2 rounded-full" style="width: 70%"></div>
</div>
</div>
</div>
</div>
<div class="space-y-8">
<div class="bg-white p-8 rounded-xl shadow-lg">
<h3 class="text-2xl font-semibold mb-6 flex items-center">
<div class="w-8 h-8 flex items-center justify-center bg-purple-100 rounded-full mr-3">
<i class="ri-database-2-line text-purple-600"></i>
</div>
データベース
</h3>
<div class="space-y-4">
<div class="flex items-center justify-between">
<span class="font-medium">PostgreSQL</span>
<span class="text-sm text-gray-500">4年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-primary h-2 rounded-full" style="width: 80%"></div>
</div>
<div class="flex items-center justify-between">
<span class="font-medium">MySQL</span>
<span class="text-sm text-gray-500">5年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-secondary h-2 rounded-full" style="width: 85%"></div>
</div>
<div class="flex items-center justify-between">
<span class="font-medium">MongoDB</span>
<span class="text-sm text-gray-500">2年</span>
</div>
<div class="w-full bg-gray-200 rounded-full h-2">
<div class="bg-yellow-500 h-2 rounded-full" style="width: 65%"></div>
</div>
</div>
</div>
<div class="bg-white p-8 rounded-xl shadow-lg">
<h3 class="text-2xl font-semibold mb-6 flex items-center">
<div class="w-8 h-8 flex items-center justify-center bg-orange-100 rounded-full mr-3">
<i class="ri-tools-line text-orange-600"></i>
</div>
その他ツール
</h3>
<div class="grid grid-cols-2 gap-4">
<div class="flex items-center space-x-2">
<div class="w-6 h-6 flex items-center justify-center text-gray-600">
<i class="ri-git-branch-line"></i>
</div>
<span>Git / GitHub</span>
</div>
<div class="flex items-center space-x-2">
<div class="w-6 h-6 flex items-center justify-center text-blue-600">
<i class="ri-ship-line"></i>
</div>
<span>Docker</span>
</div>
<div class="flex items-center space-x-2">
<div class="w-6 h-6 flex items-center justify-center text-orange-600">
<i class="ri-cloud-line"></i>
</div>
<span>AWS</span>
</div>
<div class="flex items-center space-x-2">
<div class="w-6 h-6 flex items-center justify-center text-yellow-600">
<i class="ri-fire-line"></i>
</div>
<span>Firebase</span>
</div>
<div class="flex items-center space-x-2">
<div class="w-6 h-6 flex items-center justify-center text-green-600">
<i class="ri-settings-3-line"></i>
</div>
<span>CI/CD</span>
</div>
<div class="flex items-center space-x-2">
<div class="w-6 h-6 flex items-center justify-center text-purple-600">
<i class="ri-test-tube-line"></i>
</div>
<span>Jest / Cypress</span>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<section id="contact" class="py-20">
<div class="max-w-4xl mx-auto px-6">
<div class="text-center mb-16">
<h2 class="text-4xl font-bold mb-4">お問い合わせ</h2>
<p class="text-xl text-gray-600">プロジェクトのご相談やお仕事のご依頼をお待ちしています</p>
</div>
<div class="grid lg:grid-cols-2 gap-12">
<div>
<div class="bg-white p-8 rounded-xl shadow-lg">
<h3 class="text-2xl font-semibold mb-6">お気軽にご相談ください</h3>
<div class="space-y-6">
<div class="flex items-start space-x-4">
<div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-full">
<i class="ri-lightbulb-line text-2xl text-primary"></i>
</div>
<div>
<h4 class="font-semibold mb-2">アイデア相談</h4>
<p class="text-gray-600">「こんなものを作りたい」という段階からお気軽にご相談ください。技術的な実現可能性から運用面まで、トータルでサポートします。</p>
</div>
</div>
<div class="flex items-start space-x-4">
<div class="w-12 h-12 flex items-center justify-center bg-secondary/10 rounded-full">
<i class="ri-code-box-line text-2xl text-secondary"></i>
</div>
<div>
<h4 class="font-semibold mb-2">開発支援</h4>
<p class="text-gray-600">既存プロジェクトの開発支援や、技術的な課題解決もお任せください。短期間でのスポット対応も可能です。</p>
</div>
</div>
<div class="flex items-start space-x-4">
<div class="w-12 h-12 flex items-center justify-center bg-yellow-100 rounded-full">
<i class="ri-question-line text-2xl text-yellow-600"></i>
</div>
<div>
<h4 class="font-semibold mb-2">技術相談</h4>
<p class="text-gray-600">技術選定や設計についてのご相談も承ります。プロジェクトの規模や要件に最適な技術スタックをご提案します。</p>
</div>
</div>
</div>
<div class="mt-8 pt-8 border-t border-gray-200">
<h4 class="font-semibold mb-4">SNS でもつながりましょう</h4>
<div class="flex space-x-4">
<a href="#" class="w-12 h-12 flex items-center justify-center bg-gray-800 text-white rounded-full hover:bg-gray-700 transition-colors">
<i class="ri-github-fill text-xl"></i>
</a>
<a href="#" class="w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
<i class="ri-linkedin-fill text-xl"></i>
</a>
<a href="#" class="w-12 h-12 flex items-center justify-center bg-blue-400 text-white rounded-full hover:bg-blue-500 transition-colors">
<i class="ri-twitter-fill text-xl"></i>
</a>
</div>
</div>
</div>
</div>
<div>
<form class="bg-white p-8 rounded-xl shadow-lg">
<div class="space-y-6">
<div>
<label class="block text-sm font-semibold text-gray-700 mb-2">お名前 *</label>
<input type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-colors">
</div>
<div>
<label class="block text-sm font-semibold text-gray-700 mb-2">メールアドレス *</label>
<input type="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-colors">
</div>
<div>
<label class="block text-sm font-semibold text-gray-700 mb-2">件名 *</label>
<input type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-colors">
</div>
<div>
<label class="block text-sm font-semibold text-gray-700 mb-2">メッセージ *</label>
<textarea rows="6" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-colors resize-none"></textarea>
</div>
<button type="submit" class="w-full bg-primary text-white py-4 !rounded-button font-semibold hover:bg-primary/90 transition-colors whitespace-nowrap">
メッセージを送信
</button>
</div>
</form>
</div>
</div>
</div>
</section>
  <footer class="bg-gray-900 text-white py-12">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid md:grid-cols-3 gap-8">
        <div>
          <div class="font-['Pacifico'] text-2xl text-primary mb-4">Kenta Tanaka</div>
          <p class="text-gray-400 mb-4">
            小さなアイデアを価値あるプロダクトに育て上げるフリーランスエンジニア
          </p>
          <div class="flex space-x-4">
            <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
              <i class="ri-github-fill text-xl"></i>
            </a>
            <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
              <i class="ri-linkedin-fill text-xl"></i>
            </a>
            <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
              <i class="ri-twitter-fill text-xl"></i>
            </a>
          </div>
        </div>
<div>
<h4 class="text-lg font-semibold mb-4">サイトマップ</h4>
<ul class="space-y-2 text-gray-400">
<li><a href="#home" class="hover:text-white transition-colors">ホーム</a></li>
<li><a href="#about" class="hover:text-white transition-colors">自己紹介</a></li>
<li><a href="#portfolio" class="hover:text-white transition-colors">制作実績</a></li>
<li><a href="#skills" class="hover:text-white transition-colors">スキル</a></li>
<li><a href="#contact" class="hover:text-white transition-colors">お問い合わせ</a></li>
</ul>
</div>
<div>
<h4 class="text-lg font-semibold mb-4">お問い合わせ</h4>
<div class="space-y-3 text-gray-400">
<div class="flex items-center space-x-3">
<div class="w-5 h-5 flex items-center justify-center">
<i class="ri-mail-line"></i>
</div>
<span>contact@kenta-tanaka.dev</span>
</div>
<div class="flex items-center space-x-3">
<div class="w-5 h-5 flex items-center justify-center">
<i class="ri-map-pin-line"></i>
</div>
<span>東京都</span>
</div>
</div>
</div>
</div>
      <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
        <p>&copy; 2025 Kenta Tanaka. All rights reserved.</p>
      </div>
    </div>
  </footer>
  <script src="<?php echo get_template_directory_uri(); ?>/script.js"></script>
</body>
</html>