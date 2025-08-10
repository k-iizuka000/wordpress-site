<?php
/**
 * REST API 互換レイヤー（恒久策）
 * - /wp-json/ 経路を避け、index.php?rest_route= 形式に強制
 * - キャッシュ抑止ヘッダーを付与
 * - 管理画面では admin-ajax.php を使うプロキシを提供（WAF回避）
 *
 * 共有ホスティングでのWAFや中間装置が /wp-json/ を誤検知する場合の恒久回避策。
 */

// 直接アクセス防止
if (!defined('ABSPATH')) { exit; }

// フラグ: 必要に応じて define('KEI_FORCE_REST_QUERY', true); で無効化/有効化
if (!defined('KEI_FORCE_REST_QUERY')) {
    // 既定は無効（管理画面以外への影響を避ける）
    define('KEI_FORCE_REST_QUERY', false);
}

/**
 * REST URL を index.php?rest_route= 形式に変換
 */
add_filter('rest_url', function ($url, $path, $blog_id, $scheme) {
    if (!KEI_FORCE_REST_QUERY) {
        return $url;
    }
    // 管理画面のみで変換（フロントは影響させない）
    if (!is_admin()) {
        return $url;
    }

    // 既にクエリ形式なら何もしない
    if (strpos($url, 'rest_route=') !== false) {
        return $url;
    }

    $parts = wp_parse_url($url);
    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        return $url;
    }

    $pathPart = isset($parts['path']) ? $parts['path'] : '';
    $pos = strpos($pathPart, '/wp-json');
    if ($pos === false) {
        return $url; // 想定外の形式は変換しない
    }

    // /wp-json/ 以降をルートとして抽出
    $route = '';
    if (preg_match('#/wp-json(?:/)?(.*)$#', $pathPart, $m)) {
        $route = isset($m[1]) ? $m[1] : '';
    }
    $route = '/' . ltrim($route, '/');

    // 既存クエリを維持しつつ rest_route を付与
    $queryArray = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $queryArray);
    }
    $queryArray['rest_route'] = $route;

    $base = $parts['scheme'] . '://' . $parts['host']
          . (isset($parts['port']) ? ':' . $parts['port'] : '')
          . '/index.php';

    $newUrl = $base . '?' . http_build_query($queryArray);
    return $newUrl;
}, 20, 4);

/**
 * REST 応答のキャッシュ抑止
 */
add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
    // 明示的なキャッシュ抑止ヘッダー
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    return $served; // WordPressの標準送出を継続
}, 10, 4);

/**
 * サイトヘルス用: 現在のRESTベース確認
 */
add_filter('site_status_tests', function ($tests) {
    $tests['direct']['kei_rest_route_mode'] = [
        'label' => __('Kei Portfolio: REST 互換レイヤー', 'kei-portfolio'),
        'test'  => function () {
            $enabled = KEI_FORCE_REST_QUERY ? 'enabled' : 'disabled';
            return [
                'label'       => __('REST互換レイヤー: ' . $enabled, 'kei-portfolio'),
                'status'      => 'good',
                'badge'       => [ 'label' => 'Kei', 'color' => 'blue' ],
                'description' => sprintf(
                    __('REST URL を query形式に強制しています: %s', 'kei-portfolio'),
                    KEI_FORCE_REST_QUERY ? 'ON' : 'OFF'
                ),
                'actions'     => '',
                'test'        => 'kei_rest_route_mode',
            ];
        }
    ];
    return $tests;
});

/**
 * 管理画面用: apiFetch を admin-ajax 経由に切り替えるミドルウェア
 */
function kei_portfolio_inject_apifetch_proxy_script() {
    if (!function_exists('wp_add_inline_script')) return;
    // 確実に読み込む
    wp_enqueue_script('wp-api-fetch');
    $inline = <<<JS
    (function(){
      if (!window.wp || !wp.apiFetch) return;
      var ajaxUrl = (typeof window.ajaxurl !== 'undefined') ? window.ajaxurl : null;
      if (!ajaxUrl) return;
      var routePattern = /^\/?wp\/(v2|block-patterns)\//;
      wp.apiFetch.use(function(options, next){
        try {
          var method = (options && options.method ? options.method : 'GET').toUpperCase();
          // 既に完全URLがある場合
          if (options && options.url && options.url.indexOf('rest_route=') !== -1) {
            var u = new URL(options.url, window.location.origin);
            var restRoute = u.searchParams.get('rest_route') || '';
            if (restRoute) {
              var proxy = new URL(ajaxUrl, window.location.origin);
              proxy.searchParams.set('action','kei_rest_proxy');
              proxy.searchParams.set('route', restRoute.replace(/^\/+/, ''));
              // 既存クエリも転送
              u.searchParams.forEach(function(val, key){
                if (key !== 'rest_route') proxy.searchParams.set(key, val);
              });
              options.url = proxy.toString();
              // Ajax判定ヘッダー付与
              options.headers = options.headers || {};
              try {
                var h1 = new Headers(options.headers);
                h1.set('X-Requested-With','XMLHttpRequest');
                h1.set('X-KEIPORTFOLIO_REQUEST','1');
                h1.set('X-HTTP-Method-Override', method);
                options.headers = h1;
              } catch(e) {
                options.headers['X-Requested-With'] = 'XMLHttpRequest';
                options.headers['X-KEIPORTFOLIO_REQUEST'] = '1';
                options.headers['X-HTTP-Method-Override'] = method;
              }
              delete options.path;
              return next(options);
            }
          }
          // path 指定がある場合
          if (options && typeof options.path === 'string' && routePattern.test(options.path)){
            var proxyUrl = new URL(ajaxUrl, window.location.origin);
            proxyUrl.searchParams.set('action','kei_rest_proxy');
            proxyUrl.searchParams.set('route', options.path.replace(/^\/+/, ''));
            options.url = proxyUrl.toString();
            // Ajax判定ヘッダー付与
            options.headers = options.headers || {};
            try {
              var h2 = new Headers(options.headers);
              h2.set('X-Requested-With','XMLHttpRequest');
              h2.set('X-KEIPORTFOLIO_REQUEST','1');
              h2.set('X-HTTP-Method-Override', method);
              options.headers = h2;
            } catch(e) {
              options.headers['X-Requested-With'] = 'XMLHttpRequest';
              options.headers['X-KEIPORTFOLIO_REQUEST'] = '1';
              options.headers['X-HTTP-Method-Override'] = method;
            }
            delete options.path;
          }
        } catch(e) {}
        return next(options);
      });
    })();
    JS;
    wp_add_inline_script('wp-api-fetch', $inline, 'after');

    // window.fetch のフォールバック・プロキシ（wp.apiFetchを使わない呼び出し対策）
    $inline2 = <<<JS
    (function(){
      try{
        var ajaxUrl = (typeof window.ajaxurl !== 'undefined') ? window.ajaxurl : null;
        if (!ajaxUrl || !window.fetch) return;
        var origFetch = window.fetch;
        function toURL(input){
          try { return new URL(input, window.location.origin); } catch(e) { return null; }
        }
        function buildProxy(u){
          var restRoute = '';
          if (u.pathname.indexOf('/wp-json') === 0){
            restRoute = u.pathname.replace('/wp-json','');
          }
          if (!restRoute && u.searchParams && u.searchParams.get('rest_route')){
            restRoute = u.searchParams.get('rest_route');
          }
          if (!restRoute) return null;
          var proxy = new URL(ajaxUrl, window.location.origin);
          proxy.searchParams.set('action','kei_rest_proxy');
          proxy.searchParams.set('route', String(restRoute).replace(/^\/+/, ''));
          u.searchParams.forEach(function(val,key){ if (key!=='rest_route') proxy.searchParams.set(key,val); });
          return proxy;
        }
        window.fetch = function(input, init){
          var u = (typeof input === 'string') ? toURL(input) : (input && input.url ? toURL(input.url) : null);
          var proxy = u ? buildProxy(u) : null;
          if (proxy){
            var method = (init && init.method) ? init.method : (input && input.method) ? input.method : 'GET';
            var newInit = Object.assign({}, init||{});
            newInit.method = method;
            // ヘッダー調整
            newInit.headers = newInit.headers || {};
            try {
              var h = new Headers(newInit.headers);
              h.set('X-HTTP-Method-Override', method);
              h.set('X-Requested-With','XMLHttpRequest');
              h.set('X-KEIPORTFOLIO_REQUEST','1');
              newInit.headers = h;
            } catch(e) {}
            return origFetch(proxy.toString(), newInit);
          }
          return origFetch(input, init);
        };
      }catch(e){}
    })();
    JS;
    wp_add_inline_script('wp-api-fetch', $inline2, 'after');
}

// 管理画面全体
add_action('admin_enqueue_scripts', 'kei_portfolio_inject_apifetch_proxy_script');
// ブロックエディタ（iframe側含む）
add_action('enqueue_block_editor_assets', 'kei_portfolio_inject_apifetch_proxy_script');

/**
 * ブロック関連の不要通信を削減（パターン/ディレクトリ無効化）
 */
add_action('after_setup_theme', function(){
    remove_theme_support('core-block-patterns');
});
add_filter('should_load_remote_block_patterns', '__return_false');
add_filter('block_editor_settings_all', function($settings){
    if (is_array($settings)) {
        $settings['enableBlockDirectory'] = false;
    }
    return $settings;
}, 9);

/**
 * admin-ajax 経由のRESTプロキシ
 */
add_action('wp_ajax_kei_rest_proxy', function(){
    if (!is_user_logged_in()) {
        status_header(401);
        wp_send_json_error(array('code'=>'not_logged_in'), 401);
    }
    // 投稿操作が主用途のため基本権限をチェック
    if (!current_user_can('edit_posts')) {
        status_header(403);
        wp_send_json_error(array('code'=>'forbidden'), 403);
    }
    // ルートの取得と正規化（URLエンコード対応）
    $route_raw = isset($_GET['route']) ? wp_unslash($_GET['route']) : '';
    $route_raw = is_string($route_raw) ? $route_raw : '';
    $route_decoded = urldecode($route_raw);
    // rest_route= が含まれている形式にも対応
    if (strpos($route_decoded, 'rest_route=') !== false) {
        $q = [];
        parse_str(parse_url($route_decoded, PHP_URL_QUERY) ?: $route_decoded, $q);
        if (!empty($q['rest_route'])) {
            $route_decoded = $q['rest_route'];
        }
    }
    // 余計なプレフィックスを排除
    $route_decoded = preg_replace('#^/?index\.php\??#', '', $route_decoded);
    $route_decoded = ltrim($route_decoded, '/');

    // ルート内に含まれるクエリを抽出してリクエストパラメータへ移送
    $extra_params = [];
    $route_path = $route_decoded;
    $maybe_query = parse_url($route_decoded, PHP_URL_QUERY);
    if (!empty($maybe_query)) {
        parse_str($maybe_query, $extra_params);
        $rp = parse_url($route_decoded, PHP_URL_PATH);
        if (!empty($rp)) {
            $route_path = ltrim($rp, '/');
        }
    }

    $route = '/' . $route_path;
    if ($route === '') {
        status_header(400);
        wp_send_json_error(array('code'=>'bad_request','message'=>'Missing route'), 400);
    }
    $method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) : (isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET');
    if (!in_array($method, array('GET','POST','PUT','PATCH','DELETE'), true)) {
        $method = 'GET';
    }
    $request = new WP_REST_Request($method, $route);
    // クエリをセット（action, route 以外）
    foreach ($_GET as $k=>$v) {
        if ($k === 'action' || $k === 'route') continue;
        $request->set_param(sanitize_key($k), wp_unslash($v));
    }
    // ルート内から抽出したクエリもセット
    foreach ($extra_params as $k=>$v) {
        $request->set_param(sanitize_key($k), $v);
    }
    // JSONボディ対応
    $raw = file_get_contents('php://input');
    if ($raw) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            foreach ($json as $k=>$v) {
                $request->set_param($k, $v);
            }
        }
    }
    // 実行
    $response = rest_do_request($request);
    $server = rest_get_server();
    $status = (int) $response->get_status();
    // 応答ヘッダを転送（必要なもの）
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        foreach ((array)$response->get_headers() as $hk=>$hv) {
            // 敏感ヘッダは除外
            if (preg_match('/^link$|^x-wp-/i', $hk)) {
                header($hk . ': ' . $hv);
            }
        }
        // キャッシュ抑止
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        status_header($status);
    }
    $data = $server->response_to_data($response, false);
    echo wp_json_encode($data);
    wp_die();
});
