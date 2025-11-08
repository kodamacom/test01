<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TaskFlow - チーム全員が使いやすいTODO管理SaaS</title>
  <meta name="description" content="TaskFlowはシンプルで強力なTODO管理ツール。チームのタスクを一元管理し、生産性を最大化します。">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Tailwind Config -->
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#3B82F6',
            accent: '#22C55E'
          },
          borderRadius: {
            xl: '12px'
          },
          boxShadow: {
            sm: '0 1px 2px rgba(0,0,0,.05)',
            md: '0 4px 10px rgba(0,0,0,.1)'
          }
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Inter', sans-serif; }
    .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
    .accordion-content.active { max-height: 500px; }
  </style>
</head>

<body class="bg-slate-50">

  <!-- ========================================
       Navbar (固定ヘッダー)
  ======================================== -->
  <nav class="bg-white border-b border-slate-200 sticky top-0 z-50" role="navigation" aria-label="メインナビゲーション">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">

        <!-- ロゴ -->
        <div class="flex items-center">
          <a href="#" class="flex items-center space-x-2" aria-label="TaskFlowホーム">
            <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
            <span class="text-xl font-bold text-slate-900">TaskFlow</span>
          </a>
        </div>

        <!-- ナビゲーションリンク -->
        <div class="hidden md:flex items-center space-x-8">
          <a href="#benefits" class="text-slate-600 hover:text-slate-900 font-medium transition">機能</a>
          <a href="#pricing" class="text-slate-600 hover:text-slate-900 font-medium transition">料金</a>
          <a href="#testimonials" class="text-slate-600 hover:text-slate-900 font-medium transition">導入事例</a>
          <a href="#faq" class="text-slate-600 hover:text-slate-900 font-medium transition">FAQ</a>
        </div>

        <!-- CTAボタン -->
        <div class="flex items-center space-x-4">
          <a href="#" class="hidden sm:block text-slate-600 hover:text-slate-900 font-medium transition">ログイン</a>
          <a href="#cta" class="bg-primary text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-blue-600 transition shadow-sm" aria-label="無料で始める">
            無料で始める
          </a>
        </div>

      </div>
    </div>
  </nav>

  <!-- ========================================
       Hero Section
  ======================================== -->
  <section class="bg-gradient-to-b from-white to-slate-50 py-20 lg:py-28" aria-labelledby="hero-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-2 gap-12 items-center">

        <!-- 左側：テキスト -->
        <div class="space-y-8">

          <!-- バッジ -->
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-primary/10 text-primary text-sm font-semibold" role="status">
            <span class="w-2 h-2 bg-accent rounded-full mr-2" aria-hidden="true"></span>
            5,000社以上が導入中
          </div>

          <!-- メイン見出し -->
          <h1 id="hero-heading" class="text-4xl lg:text-6xl font-bold text-slate-900 leading-tight">
            チーム全員が使いやすい<br>
            <span class="text-primary">TODO管理SaaS</span>
          </h1>

          <!-- サブ見出し -->
          <p class="text-lg lg:text-xl text-slate-600 leading-relaxed">
            TaskFlowはシンプルで強力なタスク管理ツール。<br class="hidden sm:block">
            チームの生産性を最大化し、プロジェクトを成功に導きます。
          </p>

          <!-- CTA ボタン群 -->
          <div class="flex flex-col sm:flex-row gap-4">
            <a href="#cta" class="bg-primary text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-600 transition shadow-md text-center" aria-label="14日間無料トライアル">
              14日間無料トライアル
            </a>
            <a href="#demo" class="bg-white text-slate-700 px-8 py-4 rounded-lg font-semibold hover:bg-slate-50 transition border border-slate-200 shadow-sm text-center" aria-label="デモを見る">
              デモを見る
            </a>
          </div>

          <!-- 社会的証明 -->
          <div class="flex items-center space-x-6 text-sm text-slate-600">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              <span class="font-semibold">4.9/5</span>
            </div>
            <span>・</span>
            <span>10,000+ レビュー</span>
          </div>

        </div>

        <!-- 右側：メディア -->
        <div class="relative">
          <div class="bg-white p-6 rounded-2xl shadow-2xl border border-slate-200">
            <!-- ダッシュボードのモックアップ -->
            <div class="space-y-4">
              <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">今日のタスク</h3>
                <span class="text-sm text-slate-600">5件</span>
              </div>

              <!-- タスクアイテム例 -->
              <div class="space-y-3">
                <div class="flex items-center space-x-3 p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition">
                  <div class="w-5 h-5 rounded border-2 border-primary"></div>
                  <span class="flex-1 text-slate-700">プロジェクト企画書作成</span>
                  <span class="text-xs text-slate-500">高</span>
                </div>

                <div class="flex items-center space-x-3 p-3 rounded-lg bg-accent/10">
                  <div class="w-5 h-5 rounded border-2 border-accent bg-accent flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="flex-1 text-slate-500 line-through">チームミーティング</span>
                  <span class="text-xs text-slate-500">中</span>
                </div>

                <div class="flex items-center space-x-3 p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition">
                  <div class="w-5 h-5 rounded border-2 border-primary"></div>
                  <span class="flex-1 text-slate-700">クライアント提案資料</span>
                  <span class="text-xs text-slate-500">高</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 装飾要素 -->
          <div class="absolute -top-4 -right-4 w-24 h-24 bg-accent/20 rounded-full blur-2xl" aria-hidden="true"></div>
          <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-primary/20 rounded-full blur-2xl" aria-hidden="true"></div>
        </div>

      </div>
    </div>
  </section>

  <!-- ========================================
       Partners Section (導入企業)
  ======================================== -->
  <section class="py-12 bg-white border-y border-slate-200" aria-labelledby="partners-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 id="partners-heading" class="text-center text-sm font-semibold text-slate-500 mb-8">
        5,000社以上の企業が導入
      </h2>

      <div class="flex flex-wrap justify-center items-center gap-8 md:gap-12 opacity-70 grayscale">
        <!-- ロゴプレースホルダー -->
        <div class="text-2xl font-bold text-slate-400">Company A</div>
        <div class="text-2xl font-bold text-slate-400">Company B</div>
        <div class="text-2xl font-bold text-slate-400">Company C</div>
        <div class="text-2xl font-bold text-slate-400">Company D</div>
        <div class="text-2xl font-bold text-slate-400">Company E</div>
      </div>
    </div>
  </section>

  <!-- ========================================
       Benefits Section (機能・利点)
  ======================================== -->
  <section id="benefits" class="py-20 lg:py-28 bg-slate-50" aria-labelledby="benefits-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- セクション見出し -->
      <div class="text-center mb-16">
        <h2 id="benefits-heading" class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
          生産性を最大化する機能
        </h2>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
          TaskFlowは、チームのタスク管理に必要なすべての機能を提供します
        </p>
      </div>

      <!-- 3列カードグリッド -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- カード1: 時間短縮 -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-primary mb-2">時間短縮</h3>
          <p class="text-slate-600">従来比70%の作業時間削減。直感的なUIでタスク管理を効率化します。</p>
        </div>

        <!-- カード2: リアルタイム同期 -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-accent mb-2">リアルタイム同期</h3>
          <p class="text-slate-600">チーム全員の進捗をリアルタイムで共有。常に最新の状態を確認できます。</p>
        </div>

        <!-- カード3: 柔軟なカスタマイズ -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-primary mb-2">柔軟なカスタマイズ</h3>
          <p class="text-slate-600">プロジェクトに合わせて自由にカスタマイズ。あなたのワークフローに最適化します。</p>
        </div>

        <!-- カード4: 高度な分析 -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-accent mb-2">高度な分析</h3>
          <p class="text-slate-600">詳細なレポートとダッシュボードで、チームのパフォーマンスを可視化します。</p>
        </div>

        <!-- カード5: セキュリティ -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-primary mb-2">エンタープライズ級セキュリティ</h3>
          <p class="text-slate-600">ISO 27001準拠。大切なデータを安全に保護します。</p>
        </div>

        <!-- カード6: 簡単な統合 -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-accent mb-2">簡単な統合</h3>
          <p class="text-slate-600">Slack、Google Workspace、Microsoft 365など、主要ツールと連携可能。</p>
        </div>

      </div>
    </div>
  </section>

  <!-- ========================================
       How It Works Section (使い方3ステップ)
  ======================================== -->
  <section class="py-20 lg:py-28 bg-white" aria-labelledby="howitworks-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- セクション見出し -->
      <div class="text-center mb-16">
        <h2 id="howitworks-heading" class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
          3ステップで始められる
        </h2>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
          TaskFlowは簡単セットアップで、すぐに使い始められます
        </p>
      </div>

      <!-- ステップ -->
      <div class="flex flex-col md:flex-row gap-8 md:gap-12">

        <!-- ステップ1 -->
        <div class="flex-1 text-center md:text-left" role="article">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-primary text-white rounded-full text-2xl font-bold mb-4">
            1
          </div>
          <h3 class="text-xl font-semibold text-slate-900 mb-2">アカウント作成</h3>
          <p class="text-slate-600">
            メールアドレスだけで30秒で登録完了。クレジットカード不要で14日間無料トライアル。
          </p>
        </div>

        <!-- ステップ2 -->
        <div class="flex-1 text-center md:text-left" role="article">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-primary text-white rounded-full text-2xl font-bold mb-4">
            2
          </div>
          <h3 class="text-xl font-semibold text-slate-900 mb-2">チームを招待</h3>
          <p class="text-slate-600">
            メンバーを招待し、プロジェクトを作成。権限設定も柔軟に対応できます。
          </p>
        </div>

        <!-- ステップ3 -->
        <div class="flex-1 text-center md:text-left" role="article">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-accent text-white rounded-full text-2xl font-bold mb-4">
            3
          </div>
          <h3 class="text-xl font-semibold text-slate-900 mb-2">タスク管理を開始</h3>
          <p class="text-slate-600">
            直感的なUIでタスクを追加・管理。チーム全員で生産性を向上させましょう。
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- ========================================
       Pricing Section (料金プラン)
  ======================================== -->
  <section id="pricing" class="py-20 lg:py-28 bg-slate-50" aria-labelledby="pricing-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- セクション見出し -->
      <div class="text-center mb-16">
        <h2 id="pricing-heading" class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
          シンプルで明瞭な料金プラン
        </h2>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
          チームの規模に合わせて最適なプランをお選びください
        </p>
      </div>

      <!-- 料金カード3列 -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">

        <!-- プラン1: Starter -->
        <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="mb-6">
            <h3 class="text-xl font-semibold text-slate-900 mb-2">Starter</h3>
            <p class="text-slate-600 text-sm mb-4">小規模チーム向け</p>
            <div class="flex items-baseline">
              <span class="text-4xl font-bold text-slate-900">¥980</span>
              <span class="text-slate-600 ml-2">/月</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">ユーザーあたり</p>
          </div>

          <ul class="space-y-3 mb-8" role="list">
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">最大10ユーザー</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">基本タスク管理</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">5GBストレージ</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">メールサポート</span>
            </li>
          </ul>

          <a href="#cta" class="block w-full text-center bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition border border-primary" aria-label="Starterプランを選択">
            プランを選択
          </a>
        </div>

        <!-- プラン2: Pro (強調) -->
        <div class="bg-white p-8 rounded-xl shadow-lg border-2 border-primary relative hover:shadow-xl transition" role="article">

          <!-- 人気バッジ -->
          <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
            <span class="bg-primary text-white px-4 py-1.5 rounded-full text-sm font-semibold">人気No.1</span>
          </div>

          <div class="mb-6">
            <h3 class="text-xl font-semibold text-slate-900 mb-2">Pro</h3>
            <p class="text-slate-600 text-sm mb-4">成長中のチーム向け</p>
            <div class="flex items-baseline">
              <span class="text-4xl font-bold text-primary">¥2,980</span>
              <span class="text-slate-600 ml-2">/月</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">ユーザーあたり</p>
          </div>

          <ul class="space-y-3 mb-8" role="list">
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">無制限ユーザー</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">高度なタスク管理</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">100GBストレージ</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">優先サポート</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">API連携</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">詳細レポート</span>
            </li>
          </ul>

          <a href="#cta" class="block w-full text-center bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-600 transition shadow-md" aria-label="Proプランを選択">
            プランを選択
          </a>
        </div>

        <!-- プラン3: Enterprise -->
        <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition" role="article">
          <div class="mb-6">
            <h3 class="text-xl font-semibold text-slate-900 mb-2">Enterprise</h3>
            <p class="text-slate-600 text-sm mb-4">大規模組織向け</p>
            <div class="flex items-baseline">
              <span class="text-4xl font-bold text-slate-900">お問い合わせ</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">カスタムプラン</p>
          </div>

          <ul class="space-y-3 mb-8" role="list">
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">Proの全機能</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">無制限ストレージ</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">専任サポート</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">SSO/SAML認証</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">カスタム統合</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-slate-600">SLA保証</span>
            </li>
          </ul>

          <a href="#contact" class="block w-full text-center bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition border border-primary" aria-label="Enterpriseプランを問い合わせ">
            お問い合わせ
          </a>
        </div>

      </div>

      <!-- 追加情報 -->
      <p class="text-center text-slate-600 mt-8">
        全プラン14日間無料トライアル付き。クレジットカード不要。
      </p>
    </div>
  </section>

  <!-- ========================================
       Testimonials Section (お客様の声)
  ======================================== -->
  <section id="testimonials" class="py-20 lg:py-28 bg-white" aria-labelledby="testimonials-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- セクション見出し -->
      <div class="text-center mb-16">
        <h2 id="testimonials-heading" class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
          お客様の声
        </h2>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
          5,000社以上の企業がTaskFlowで生産性を向上させています
        </p>
      </div>

      <!-- 証言カードグリッド -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- 証言1 -->
        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200" role="article">
          <div class="flex items-center mb-4">
            <!-- アバター -->
            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold mr-3">
              田中
            </div>
            <div>
              <div class="font-semibold text-slate-900">田中 太郎</div>
              <div class="text-sm text-slate-600">株式会社A / プロジェクトマネージャー</div>
            </div>
          </div>

          <!-- 星評価 -->
          <div class="flex mb-3" aria-label="5つ星評価">
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>

          <p class="text-slate-700">
            「TaskFlowを導入してから、チームの生産性が劇的に向上しました。タスクの見える化により、進捗管理が格段に楽になりました。」
          </p>
        </div>

        <!-- 証言2 -->
        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200" role="article">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center text-white font-bold mr-3">
              佐藤
            </div>
            <div>
              <div class="font-semibold text-slate-900">佐藤 花子</div>
              <div class="text-sm text-slate-600">株式会社B / 開発チームリーダー</div>
            </div>
          </div>

          <div class="flex mb-3" aria-label="5つ星評価">
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>

          <p class="text-slate-700">
            「UIが直感的で、チーム全員がすぐに使いこなせました。Slackとの連携も素晴らしく、ワークフローが大幅に改善されました。」
          </p>
        </div>

        <!-- 証言3 -->
        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200" role="article">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold mr-3">
              鈴木
            </div>
            <div>
              <div class="font-semibold text-slate-900">鈴木 一郎</div>
              <div class="text-sm text-slate-600">株式会社C / CEO</div>
            </div>
          </div>

          <div class="flex mb-3" aria-label="5つ星評価">
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>

          <p class="text-slate-700">
            「コストパフォーマンスが最高です。複数のツールを使っていたのをTaskFlow一本に統合でき、コストも時間も大幅に削減できました。」
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- ========================================
       FAQ Section (よくある質問)
  ======================================== -->
  <section id="faq" class="py-20 lg:py-28 bg-slate-50" aria-labelledby="faq-heading">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- セクション見出し -->
      <div class="text-center mb-16">
        <h2 id="faq-heading" class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
          よくある質問
        </h2>
        <p class="text-lg text-slate-600">
          TaskFlowについてのよくある質問にお答えします
        </p>
      </div>

      <!-- アコーディオン -->
      <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-200" role="region" aria-label="よくある質問">

        <!-- FAQ1 -->
        <div class="accordion-item">
          <button class="accordion-trigger w-full text-left px-6 py-5 flex justify-between items-center hover:bg-slate-50 transition" aria-expanded="false" aria-controls="faq1">
            <span class="font-semibold text-slate-900">無料トライアルにクレジットカードは必要ですか？</span>
            <svg class="w-5 h-5 text-slate-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div id="faq1" class="accordion-content">
            <div class="px-6 pb-5 text-slate-600">
              いいえ、必要ありません。14日間の無料トライアルはクレジットカード情報の入力なしで開始できます。トライアル期間終了後も、自動的に課金されることはありません。
            </div>
          </div>
        </div>

        <!-- FAQ2 -->
        <div class="accordion-item">
          <button class="accordion-trigger w-full text-left px-6 py-5 flex justify-between items-center hover:bg-slate-50 transition" aria-expanded="false" aria-controls="faq2">
            <span class="font-semibold text-slate-900">途中でプランを変更できますか？</span>
            <svg class="w-5 h-5 text-slate-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div id="faq2" class="accordion-content">
            <div class="px-6 pb-5 text-slate-600">
              はい、いつでもプラン変更が可能です。アップグレードの場合は即座に反映され、ダウングレードの場合は次回更新日から適用されます。差額は日割り計算されます。
            </div>
          </div>
        </div>

        <!-- FAQ3 -->
        <div class="accordion-item">
          <button class="accordion-trigger w-full text-left px-6 py-5 flex justify-between items-center hover:bg-slate-50 transition" aria-expanded="false" aria-controls="faq3">
            <span class="font-semibold text-slate-900">データのバックアップは取られていますか？</span>
            <svg class="w-5 h-5 text-slate-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div id="faq3" class="accordion-content">
            <div class="px-6 pb-5 text-slate-600">
              はい、すべてのデータは毎日自動的にバックアップされ、複数のデータセンターに保存されています。99.9%のアップタイム保証があり、万が一の際も安心です。
            </div>
          </div>
        </div>

        <!-- FAQ4 -->
        <div class="accordion-item">
          <button class="accordion-trigger w-full text-left px-6 py-5 flex justify-between items-center hover:bg-slate-50 transition" aria-expanded="false" aria-controls="faq4">
            <span class="font-semibold text-slate-900">他のツールからデータを移行できますか？</span>
            <svg class="w-5 h-5 text-slate-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div id="faq4" class="accordion-content">
            <div class="px-6 pb-5 text-slate-600">
              はい、主要なプロジェクト管理ツール（Asana、Trello、Jiraなど）からのデータ移行をサポートしています。CSVインポート機能もあり、簡単に既存データを移行できます。
            </div>
          </div>
        </div>

        <!-- FAQ5 -->
        <div class="accordion-item">
          <button class="accordion-trigger w-full text-left px-6 py-5 flex justify-between items-center hover:bg-slate-50 transition" aria-expanded="false" aria-controls="faq5">
            <span class="font-semibold text-slate-900">返金ポリシーについて教えてください</span>
            <svg class="w-5 h-5 text-slate-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div id="faq5" class="accordion-content">
            <div class="px-6 pb-5 text-slate-600">
              年次プランをご契約の場合、30日以内であれば全額返金いたします。月次プランの場合は、残期間に応じて日割り計算で返金されます。
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ========================================
       CTA Section (最終行動喚起)
  ======================================== -->
  <section id="cta" class="py-20 lg:py-28 bg-gradient-to-r from-primary/10 to-accent/10" aria-labelledby="cta-heading">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

      <h2 id="cta-heading" class="text-3xl lg:text-5xl font-bold text-slate-900 mb-6">
        今すぐTaskFlowで<br class="sm:hidden">生産性を向上させましょう
      </h2>

      <p class="text-lg lg:text-xl text-slate-700 mb-10 max-w-2xl mx-auto">
        14日間の無料トライアルを今すぐ開始。クレジットカード不要で、すべての機能をお試しいただけます。
      </p>

      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="#signup" class="bg-primary text-white px-10 py-4 rounded-lg font-semibold hover:bg-blue-600 transition shadow-lg text-lg" aria-label="無料で始める">
          無料で始める
        </a>
        <a href="#demo" class="bg-white text-slate-700 px-10 py-4 rounded-lg font-semibold hover:bg-slate-50 transition border border-slate-300 shadow-sm text-lg" aria-label="デモを予約">
          デモを予約
        </a>
      </div>

      <p class="text-sm text-slate-600 mt-6">
        5,000社以上が導入 ・ 4.9/5 (10,000+ レビュー) ・ ISO 27001準拠
      </p>

    </div>
  </section>

  <!-- ========================================
       Footer (フッター)
  ======================================== -->
  <footer class="bg-slate-900 text-slate-300 py-16" role="contentinfo">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">

        <!-- ロゴ・会社情報 -->
        <div class="md:col-span-1">
          <div class="flex items-center space-x-2 mb-4">
            <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
            <span class="text-xl font-bold text-white">TaskFlow</span>
          </div>
          <p class="text-sm mb-4">
            チーム全員が使いやすいTODO管理SaaS。生産性を最大化します。
          </p>

          <!-- SNS -->
          <div class="flex space-x-4">
            <a href="#" class="text-slate-400 hover:text-white transition" aria-label="Twitter">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
              </svg>
            </a>
            <a href="#" class="text-slate-400 hover:text-white transition" aria-label="Facebook">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
              </svg>
            </a>
            <a href="#" class="text-slate-400 hover:text-white transition" aria-label="LinkedIn">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- プロダクト -->
        <div>
          <h3 class="text-white font-semibold mb-4">プロダクト</h3>
          <ul class="space-y-2" role="list">
            <li><a href="#" class="hover:text-white transition">機能</a></li>
            <li><a href="#" class="hover:text-white transition">料金</a></li>
            <li><a href="#" class="hover:text-white transition">セキュリティ</a></li>
            <li><a href="#" class="hover:text-white transition">統合</a></li>
            <li><a href="#" class="hover:text-white transition">API</a></li>
          </ul>
        </div>

        <!-- 会社 -->
        <div>
          <h3 class="text-white font-semibold mb-4">会社</h3>
          <ul class="space-y-2" role="list">
            <li><a href="#" class="hover:text-white transition">会社概要</a></li>
            <li><a href="#" class="hover:text-white transition">採用情報</a></li>
            <li><a href="#" class="hover:text-white transition">ブログ</a></li>
            <li><a href="#" class="hover:text-white transition">プレスキット</a></li>
            <li><a href="#" class="hover:text-white transition">お問い合わせ</a></li>
          </ul>
        </div>

        <!-- ニュースレター -->
        <div>
          <h3 class="text-white font-semibold mb-4">ニュースレター</h3>
          <p class="text-sm mb-4">最新情報をお届けします</p>
          <form class="space-y-2" action="#" method="post" aria-label="ニュースレター登録">
            <input type="email" placeholder="メールアドレス" class="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary" aria-label="メールアドレス" required>
            <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-600 transition" aria-label="登録する">
              登録する
            </button>
          </form>
        </div>

      </div>

      <!-- 区切り線 -->
      <div class="border-t border-slate-700 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">

          <p class="text-sm text-slate-400">
            &copy; 2025 TaskFlow, Inc. All rights reserved.
          </p>

          <div class="flex space-x-6 text-sm">
            <a href="#" class="hover:text-white transition">プライバシーポリシー</a>
            <a href="#" class="hover:text-white transition">利用規約</a>
            <a href="#" class="hover:text-white transition">特定商取引法</a>
          </div>

        </div>
      </div>

    </div>
  </footer>

  <!-- ========================================
       JavaScript (アコーディオン機能)
  ======================================== -->
  <script>
    // アコーディオン機能
    document.querySelectorAll('.accordion-trigger').forEach(trigger => {
      trigger.addEventListener('click', function() {
        const content = this.nextElementSibling;
        const icon = this.querySelector('svg');
        const isExpanded = this.getAttribute('aria-expanded') === 'true';

        // すべてのアコーディオンを閉じる
        document.querySelectorAll('.accordion-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.accordion-trigger').forEach(t => {
          t.setAttribute('aria-expanded', 'false');
          t.querySelector('svg').classList.remove('rotate-180');
        });

        // クリックされた項目をトグル
        if (!isExpanded) {
          content.classList.add('active');
          this.setAttribute('aria-expanded', 'true');
          icon.classList.add('rotate-180');
        }
      });
    });

    // スムーズスクロール
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== '#signup' && href !== '#contact' && href !== '#demo') {
          e.preventDefault();
          const target = document.querySelector(href);
          if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }
      });
    });
  </script>

</body>
</html>
