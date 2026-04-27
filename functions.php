<?php

if (!defined('ABSPATH')) {
    exit;
}

// ====== CDN 配置常量 ======
// 可在 wp-config.php 中使用 define() 覆盖这些值
if (!defined('LARED_CDN_FONTAWESOME')) {
    define('LARED_CDN_FONTAWESOME', 'https://icons.bluecdn.com/fontawesome-pro/css/all.css');
}
if (!defined('LARED_CDN_STATIC')) {
    define('LARED_CDN_STATIC', 'https://jsd.bluecdn.com/npm');
}
// =========================

// 内容与展示模块
require_once get_template_directory() . '/inc/inc-image.php';
require_once get_template_directory() . '/inc/inc-hero.php';
require_once get_template_directory() . '/inc/inc-comments.php';

// 数据与扩展模块
require_once get_template_directory() . '/inc/inc-rss.php';
require_once get_template_directory() . '/inc/inc-memos.php';
require_once get_template_directory() . '/inc/inc-ai-summary.php';
require_once get_template_directory() . '/inc/inc-email.php';

// 后台设置模块
require_once get_template_directory() . '/inc/inc-theme-settings.php';

/* ===========================================
   Inlined from inc/inc-editor.php
   TinyMCE 编辑器「排版指南」按钮与样式
   =========================================== */

// 注册 TinyMCE 外部插件
function lared_mce_external_plugins(array $plugins): array
{
    $url = get_template_directory_uri() . '/assets/js/editor-admin.min.js';
    $plugins['laredThemeGuide']  = $url;
    return $plugins;
}
add_filter('mce_external_plugins', 'lared_mce_external_plugins');

// 将按钮添加到工具栏
function lared_mce_buttons(array $buttons): array
{
    $buttons[] = 'lared_theme_guide';
    return $buttons;
}
add_filter('mce_buttons', 'lared_mce_buttons');

// 编辑页面注入排版指南模态窗 CSS
function lared_editor_theme_guide_css(): void
{
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['post', 'page'], true)) {
        return;
    }
    echo '<style id="lared-guide-modal-css">
/* ===== 主题排版指南模态窗 ===== */
.lared-guide-backdrop {
    position: fixed; inset: 0; z-index: 100100;
    display: flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
    opacity: 0; transition: opacity .2s ease;
}
.lared-guide-backdrop.is-visible { opacity: 1; }
.lared-guide-modal {
    position: relative; width: 92%; max-width: 720px; max-height: 85vh;
    display: flex; flex-direction: column;
    background: #fff; border: 1px solid #d9d9d9;
    box-shadow: 0 8px 32px rgba(0,0,0,.15);
    transform: translateY(12px) scale(.97); transition: transform .2s ease;
}
.lared-guide-backdrop.is-visible .lared-guide-modal { transform: translateY(0) scale(1); }
.lared-guide-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid #e5e5e5;
}
.lared-guide-header-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.lared-guide-title { font-size: 15px; font-weight: 600; color: #1d2327; }
.lared-guide-mode {
    display: inline-flex; align-items: center; font-size: 11px; padding: 2px 8px;
    border-radius: 3px; font-weight: 500;
}
.lared-guide-mode.is-text { background: #ecfdf5; color: #059669; }
.lared-guide-mode.is-visual { background: #eff6ff; color: #2563eb; }
.lared-guide-close {
    width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
    border: none; background: transparent; font-size: 22px; color: #999; cursor: pointer;
    border-radius: 0; padding: 0; line-height: 1; flex-shrink: 0;
}
.lared-guide-close:hover { color: #333; }
.lared-guide-notice {
    padding: 8px 20px; background: #fffbeb; border-bottom: 1px solid #e5e5e5;
    font-size: 12px; color: #92400e; line-height: 1.5;
}
.lared-guide-tabs {
    display: flex; gap: 0; border-bottom: 1px solid #e5e5e5; overflow-x: auto;
}
.lared-guide-tab {
    padding: 10px 18px; font-size: 13px; font-weight: 500; color: #666;
    background: transparent; border: none; border-bottom: 2px solid transparent;
    cursor: pointer; transition: color .15s, border-color .15s; white-space: nowrap;
}
.lared-guide-tab:hover { color: #333; }
.lared-guide-tab.is-active { color: #1d2327; border-bottom-color: #f53004; }
.lared-guide-body { flex: 1; overflow-y: auto; padding: 16px 20px; }
.lared-guide-card {
    margin-bottom: 16px; border: 1px solid #e8e8e8; background: #fafafa;
}
.lared-guide-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; background: #fff; border-bottom: 1px solid #e8e8e8;
}
.lared-guide-card-title { font-size: 14px; font-weight: 600; color: #1d2327; }
.lared-guide-card-actions { display: flex; gap: 6px; }
.lared-guide-copy-btn,
.lared-guide-insert-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 28px; padding: 0 12px; font-size: 12px; font-weight: 500;
    border: 1px solid #d9d9d9; cursor: pointer; transition: all .15s;
    background: #fff; color: #555; border-radius: 0;
}
.lared-guide-copy-btn:hover { background: #f5f5f5; border-color: #bbb; }
.lared-guide-insert-btn {
    background: #f53004; color: #fff; border-color: #f53004;
}
.lared-guide-insert-btn:hover { background: #d42a03; border-color: #d42a03; }
.lared-guide-card-desc {
    margin: 0; padding: 8px 14px 4px; font-size: 12px; color: #888; line-height: 1.5;
}
.lared-guide-code {
    margin: 0; padding: 10px 14px; background: #282a36; color: #f8f8f2;
    font-size: 12px; line-height: 1.6; overflow-x: auto; white-space: pre-wrap;
    word-break: break-all; max-height: 200px;
}
.lared-guide-code code {
    font-family: "SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace;
    color: inherit; background: none; padding: 0; font-size: inherit;
}
@media (max-width: 600px) {
    .lared-guide-modal { max-width: 100%; max-height: 100vh; }
}
</style>';
}
add_action('admin_head', 'lared_editor_theme_guide_css');

// 编辑页面加载排版指南脚本
function lared_editor_enqueue_guide_script(): void
{
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['post', 'page'], true)) {
        return;
    }
    wp_enqueue_script(
        'lared-editor-admin',
        get_template_directory_uri() . '/assets/js/editor-admin.min.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('admin_enqueue_scripts', 'lared_editor_enqueue_guide_script');

// 主题设置页面加载媒体上传器 + 后台样式
function lared_admin_enqueue_media(string $hook): void
{
    if ('appearance_page_lared-theme-settings' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style(
        'lared-admin',
        get_template_directory_uri() . '/assets/css/lared-admin.min.css',
        [],
        wp_get_theme()->get('Version')
    );
}
add_action('admin_enqueue_scripts', 'lared_admin_enqueue_media');

function lared_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');

    register_nav_menus([
        'primary'      => __('Primary Menu', 'lared'),
        'hero_sidebar' => __('Hero 侧边栏', 'lared'),
    ]);
}
add_action('after_setup_theme', 'lared_setup');

/**
 * 禁用 XML-RPC 和 Pingback，防止 wp-cron 中
 * preg_match() 收到 array 参数导致 Fatal Error。
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('pre_option_default_pingback_flag', '__return_zero');
add_action('init', function () {
    remove_action('do_pings', 'do_all_pingbacks', 10);
    remove_action('do_pings', 'do_all_trackbacks', 10);
});

/**
 * 主题激活时自动创建缓存目录并设置写入权限。
 * 目录：data（Memos / 表情 缓存）、data/rss（RSS 缓存）
 */
function lared_activate_create_cache_dirs(): void
{
    $dirs = [
        get_template_directory() . '/data',
        get_template_directory() . '/data/rss',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        // 确保 Web 服务器可写（0755 权限）
        if (is_dir($dir) && !wp_is_writable($dir)) {
            chmod($dir, 0755);
        }
    }
}
add_action('after_switch_theme', 'lared_activate_create_cache_dirs');

/**
 * 主题激活后显示引导通知，提示用户前往数据维护初始化数据
 */
function lared_activate_setup_notice(): void
{
    set_transient('lared_activation_notice', true, 60 * 5);
}
add_action('after_switch_theme', 'lared_activate_setup_notice');

function lared_show_activation_notice(): void
{
    if (!get_transient('lared_activation_notice')) {
        return;
    }

    $data_tab_url = add_query_arg(
        ['page' => 'lared-theme-settings', 'tab' => 'data'],
        admin_url('themes.php')
    );

    echo '<div class="notice notice-info is-dismissible" style="border-left-color:#f53004;">'
        . '<p><strong>Lared 主题已激活！</strong> 请前往 '
        . '<a href="' . esc_url($data_tab_url) . '"><strong>主题设置 → 数据维护</strong></a>'
        . ' 执行以下操作以确保主题正常运行：</p>'
        . '<ol style="margin:4px 0 8px 20px;">'
        . '<li><strong>评论等级缓存</strong> — 点击「扫描」→「重建缓存」</li>'
        . '<li><strong>评论 Meta 合并迁移</strong> — 如从旧版升级，点击「扫描」→「执行迁移」</li>'
        . '<li><strong>文章字数统计</strong> — 点击「扫描」→「更新」</li>'
        . '</ol></div>';

    delete_transient('lared_activation_notice');
}
add_action('admin_notices', 'lared_show_activation_notice');

function lared_primary_menu_fallback(): void
{
    echo '<ul class="nav"><li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('首页', 'lared') . '</a></li></ul>';
}

/**
 * 全局强制 Gravatar 请求尺寸为 128px，以便在高分屏下保持高清。
 * 实际显示大小仍由 CSS / HTML 属性控制。
 */
function lared_force_avatar_hd(array $args): array
{
    $args['size'] = 128;
    return $args;
}
add_filter('pre_get_avatar_data', 'lared_force_avatar_hd');

/**
 * 将 Gravatar 默认域名替换为 gravatar.bluecdn.com 加速镜像。
 * 同时覆盖 get_avatar() 和 get_avatar_url() 输出。
 */
function lared_gravatar_cdn(string $url): string
{
    return str_replace(
        ['www.gravatar.com', 'secure.gravatar.com', '0.gravatar.com', '1.gravatar.com', '2.gravatar.com'],
        'gravatar.bluecdn.com',
        $url
    );
}
add_filter('get_avatar_url', 'lared_gravatar_cdn', 10, 1);

// ====== 热力图文件缓存 — uploads/lared-cache/heatmap/ ======

/**
 * 热力图缓存目录
 */
function lared_get_heatmap_cache_dir(): string
{
    $upload_dir = wp_upload_dir();
    return $upload_dir['basedir'] . '/lared-cache/heatmap';
}

/**
 * 热力图缓存文件路径
 */
function lared_get_heatmap_cache_file(): string
{
    return lared_get_heatmap_cache_dir() . '/heatmap-data.json';
}

/**
 * 读取热力图缓存（60 天文章+说说计数）
 *
 * @return array|false 缓存数据数组，不存在或已过期返回 false
 */
function lared_get_heatmap_cache(): array|false
{
    $cache_file = lared_get_heatmap_cache_file();
    if (!file_exists($cache_file)) {
        return false;
    }

    $content = file_get_contents($cache_file);
    if (!is_string($content) || '' === $content) {
        return false;
    }

    $data = json_decode($content, true);
    if (!is_array($data) || empty($data['cells'])) {
        return false;
    }

    // 1 小时过期检查
    $cached_at = (int) ($data['cached_at'] ?? 0);
    if ((time() - $cached_at) > HOUR_IN_SECONDS) {
        return false;
    }

    return $data['cells'];
}

/**
 * 生成并保存热力图缓存
 *
 * @return array 热力图单元格数组
 */
function lared_build_heatmap_cache(): array
{
    $days_total = 60;
    $day_counts = [];
    $post_ids = get_posts([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'fields'              => 'ids',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'date_query'          => [
            [
                'after'     => ($days_total - 1) . ' days ago',
                'inclusive' => true,
            ],
        ],
    ]);
    foreach ($post_ids as $pid) {
        $day_key = get_the_date('Y-m-d', (int) $pid);
        if ('' === $day_key) {
            continue;
        }
        $day_counts[$day_key] = ($day_counts[$day_key] ?? 0) + 1;
    }

    // Memos 说说数据
    $memo_counts = [];
    if (function_exists('lared_get_memos_json_cache')) {
        $today_date = wp_date('Y-m-d');
        $start_ts = strtotime($today_date . ' -' . ($days_total - 1) . ' days');
        $memos = lared_get_memos_json_cache();
        if (!empty($memos['items']) && is_array($memos['items'])) {
            foreach ($memos['items'] as $memo) {
                $memo_ts = (int) ($memo['created_timestamp'] ?? 0);
                if ($memo_ts <= 0 || $memo_ts < $start_ts) {
                    continue;
                }
                $memo_day = wp_date('Y-m-d', $memo_ts);
                if ('' === $memo_day) {
                    continue;
                }
                $memo_counts[$memo_day] = ($memo_counts[$memo_day] ?? 0) + 1;
            }
        }
    }

    // 合并计数，计算热力等级
    $combined = [];
    $all_days = array_unique(array_merge(array_keys($day_counts), array_keys($memo_counts)));
    foreach ($all_days as $day) {
        $combined[$day] = ($day_counts[$day] ?? 0) + ($memo_counts[$day] ?? 0);
    }
    $max_count = !empty($combined) ? max($combined) : 0;

    $cells = [];
    $today_ts = strtotime(wp_date('Y-m-d') . ' 12:00:00');
    for ($i = $days_total - 1; $i >= 0; $i--) {
        $cell_ts = $today_ts - ($i * DAY_IN_SECONDS);
        $cell_date       = wp_date('Y-m-d', $cell_ts);
        $cell_post_count = (int) ($day_counts[$cell_date] ?? 0);
        $cell_memo_count = (int) ($memo_counts[$cell_date] ?? 0);
        $cell_total      = $cell_post_count + $cell_memo_count;
        $cell_level      = 0;
        if ($cell_total > 0 && $max_count > 0) {
            $cell_level = (int) ceil(($cell_total / $max_count) * 5);
            $cell_level = max(1, min(5, $cell_level));
        }
        $cells[] = [
            'date'       => $cell_date,
            'count'      => $cell_total,
            'post_count' => $cell_post_count,
            'memo_count' => $cell_memo_count,
            'level'      => $cell_level,
        ];
    }

    // 保存到文件
    $cache_dir = lared_get_heatmap_cache_dir();
    if (!is_dir($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }
    $payload = wp_json_encode([
        'cells'     => $cells,
        'cached_at' => time(),
    ], JSON_UNESCAPED_UNICODE);
    @file_put_contents(lared_get_heatmap_cache_file(), $payload);

    return $cells;
}

/**
 * 清除热力图缓存文件
 */
function lared_clear_heatmap_cache(): void
{
    $file = lared_get_heatmap_cache_file();
    if (file_exists($file)) {
        @unlink($file);
    }
}

// ====== Gravatar 本地缓存 — 减少外部请求 ======

/**
 * 检查是否禁用了所有本地缓存（后台「缓存管理」开关）
 */
function lared_is_cache_disabled(): bool
{
    return '1' === (string) get_option('lared_disable_cache', '0');
}

/**
 * 获取 Gravatar 缓存目录路径（uploads/lared-cache/gravatar/）
 */
function lared_get_gravatar_cache_dir(): string
{
    $upload_dir = wp_upload_dir();
    return $upload_dir['basedir'] . '/lared-cache/gravatar';
}

/**
 * 获取 Gravatar 缓存 URL 前缀
 */
function lared_get_gravatar_cache_url(): string
{
    $upload_dir = wp_upload_dir();
    return $upload_dir['baseurl'] . '/lared-cache/gravatar';
}

/**
 * 将 Gravatar URL 替换为本地缓存副本。
 * 如果本地缓存不存在或已过期（7 天），则从 CDN 下载。
 * 下载失败时回退到原始 CDN URL。
 *
 * @param string $url Gravatar URL（已经过 lared_gravatar_cdn 替换为 bluecdn）
 * @return string 本地缓存 URL 或原始 URL（回退）
 */
function lared_cache_gravatar(string $url): string
{
    // 缓存全局关闭：直接返回 (已经过 lared_gravatar_cdn 替换为 bluecdn) URL
    if (lared_is_cache_disabled()) {
        return $url;
    }
    // 仅处理 Gravatar URL
    if (false === strpos($url, 'gravatar') && false === strpos($url, '/avatar/')) {
        return $url;
    }

    // 从 URL 提取 hash 和参数
    $parsed = wp_parse_url($url);
    if (empty($parsed['path'])) {
        return $url;
    }

    // 路径格式: /avatar/HASH
    if (!preg_match('#/avatar/([a-f0-9]{32,64})#i', $parsed['path'], $matches)) {
        return $url;
    }
    $hash = strtolower($matches[1]);

    // 解析查询参数获取 size
    $query_params = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $query_params);
    }
    $size = isset($query_params['s']) ? (int) $query_params['s'] : 128;

    // 缓存文件路径
    $cache_dir  = lared_get_gravatar_cache_dir();
    $cache_file = $cache_dir . '/' . $hash . '_' . $size . '.jpg';
    $cache_url  = lared_get_gravatar_cache_url() . '/' . $hash . '_' . $size . '.jpg';

    // 检查缓存是否存在且未过期（30 天）
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 30 * DAY_IN_SECONDS) {
        return $cache_url;
    }

    // 确保缓存目录存在
    if (!is_dir($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }

    // 从 CDN 下载
    $response = wp_remote_get($url, [
        'timeout'   => 5,
        'sslverify' => false,
    ]);

    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
        // 下载失败：如果旧缓存存在则续用，否则回退 CDN
        return file_exists($cache_file) ? $cache_url : $url;
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        return file_exists($cache_file) ? $cache_url : $url;
    }

    // 写入缓存文件
    file_put_contents($cache_file, $body, LOCK_EX);

    return $cache_url;
}
add_filter('get_avatar_url', 'lared_cache_gravatar', 20, 1);

/**
 * 定期清理过期的 Gravatar 缓存文件（超过 30 天）
 */
function lared_cleanup_gravatar_cache(): void
{
    $cache_dir = lared_get_gravatar_cache_dir();
    if (!is_dir($cache_dir)) {
        return;
    }

    $files = glob($cache_dir . '/*.jpg');
    if (empty($files)) {
        return;
    }

    $expire = time() - 60 * DAY_IN_SECONDS;
    foreach ($files as $file) {
        if (filemtime($file) < $expire) {
            @unlink($file);
        }
    }
}
add_action('wp_scheduled_delete', 'lared_cleanup_gravatar_cache');

// ====== 友链图标本地缓存 — uploads/lared-cache/links-ico/ ======

/**
 * 获取友链图标缓存目录
 */
function lared_get_link_ico_cache_dir(): string
{
    $upload_dir = wp_upload_dir();
    return $upload_dir['basedir'] . '/lared-cache/links-ico';
}

function lared_get_link_ico_cache_url(): string
{
    $upload_dir = wp_upload_dir();
    return $upload_dir['baseurl'] . '/lared-cache/links-ico';
}

/**
 * 获取友链图标（带本地缓存）
 *
 * 尝试从站点直接获取 favicon，成功则缓存到本地并返回本地 URL。
 * 失败或获取到的是文字占位图则返回空字符串（由模板显示字母 fallback）。
 *
 * @param string $host 域名（如 example.com）
 * @return string 本地缓存 URL 或空字符串
 */
function lared_get_cached_link_icon(string $host): string
{
    if ('' === $host) {
        return '';
    }

    // 缓存全局关闭：直接返回 favicon.im 远端 URL
    if (lared_is_cache_disabled()) {
        return 'https://favicon.im/' . urlencode($host) . '?larger=true';
    }

    $safe_name  = preg_replace('/[^a-z0-9.\-]/i', '_', $host);
    $cache_dir  = lared_get_link_ico_cache_dir();
    $cache_file = $cache_dir . '/' . $safe_name . '.ico';
    $miss_file  = $cache_dir . '/' . $safe_name . '.miss';
    $cache_url  = lared_get_link_ico_cache_url() . '/' . $safe_name . '.ico';

    // 已缓存的成功图标（30 天有效）
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 30 * DAY_IN_SECONDS) {
        return $cache_url;
    }

    // 已标记为失败（30 天内不重试）
    if (file_exists($miss_file) && (time() - filemtime($miss_file)) < 30 * DAY_IN_SECONDS) {
        return '';
    }

    // 确保目录存在
    if (!is_dir($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }

    // 从 favicon.im 获取（larger=true 拿大尺寸版本）
    $ico_url  = 'https://favicon.im/' . urlencode($host) . '?larger=true';
    $response = wp_remote_get($ico_url, [
        'timeout'   => 5,
        'sslverify' => false,
    ]);

    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
        @file_put_contents($miss_file, '', LOCK_EX);
        return '';
    }

    $body         = wp_remote_retrieve_body($response);
    $content_type = wp_remote_retrieve_header($response, 'content-type');

    // favicon.im 对无 favicon 的站点返回 image/svg+xml 占位 (~257 bytes)
    $is_placeholder = false;
    if (false !== stripos($content_type, 'svg')) {
        $is_placeholder = true;
    }
    if (strlen($body) < 500) {
        $is_placeholder = true;
    }

    if ($is_placeholder || empty($body)) {
        @file_put_contents($miss_file, '', LOCK_EX);
        return '';
    }

    // 写入缓存
    file_put_contents($cache_file, $body, LOCK_EX);
    // 清除旧的 miss 标记
    if (file_exists($miss_file)) {
        @unlink($miss_file);
    }

    return $cache_url;
}

/**
 * 定期清理过期的友链图标缓存
 */
function lared_cleanup_link_ico_cache(): void
{
    $cache_dir = lared_get_link_ico_cache_dir();
    if (!is_dir($cache_dir)) {
        return;
    }

    $expire = time() - 90 * DAY_IN_SECONDS;
    foreach (glob($cache_dir . '/*') as $file) {
        if (is_file($file) && filemtime($file) < $expire) {
            @unlink($file);
        }
    }
}
add_action('wp_scheduled_delete', 'lared_cleanup_link_ico_cache');

/**
 * 始终输出英文日期（不受 WordPress 语言设置影响）
 * 使用站点时区，格式字符同 PHP date()
 */
function lared_date_en(string $format, int $utc_timestamp): string
{
    return (new DateTimeImmutable('@' . $utc_timestamp))
        ->setTimezone(wp_timezone())
        ->format($format);
}

function lared_minify_css_contents(string $css): string
{
    $banner = '';
    $offset = 0;

    while (preg_match('/\A\s*\/\*.*?\*\//s', substr($css, $offset), $match)) {
        $banner .= trim($match[0]) . "\n";
        $offset += strpos(substr($css, $offset), $match[0]) + strlen($match[0]);
    }

    $body = substr($css, $offset);
    $body = preg_replace('/\/\*[^!].*?\*\//s', '', $body) ?? $body;
    $body = preg_replace('/\s+/', ' ', $body) ?? $body;
    $body = preg_replace('/\s*([{};,>])\s*/', '$1', $body) ?? $body;
    $body = str_replace(';}', '}', trim($body));

    return trim($banner) !== '' ? trim($banner) . "\n" . $body . "\n" : $body . "\n";
}

function lared_refresh_min_css_if_needed(): void
{
    $main_css_path = get_template_directory() . '/assets/css/lared-main.css';
    $main_min_css_path = get_template_directory() . '/assets/css/lared-main.min.css';

    if (! file_exists($main_css_path)) {
        return;
    }

    $source_mtime = (int) filemtime($main_css_path);
    $target_mtime = file_exists($main_min_css_path) ? (int) filemtime($main_min_css_path) : 0;

    if ($target_mtime >= $source_mtime) {
        return;
    }

    $css = file_get_contents($main_css_path);
    if (! is_string($css) || $css === '') {
        return;
    }

    $minified = lared_minify_css_contents($css);
    @file_put_contents($main_min_css_path, $minified);
}

function lared_after_switch_theme_refresh_assets(): void
{
    lared_refresh_min_css_if_needed();
}
add_action('after_switch_theme', 'lared_after_switch_theme_refresh_assets');

function lared_after_theme_update_refresh_assets($upgrader, array $options): void
{
    if (($options['action'] ?? '') !== 'update' || ($options['type'] ?? '') !== 'theme') {
        return;
    }

    $themes = $options['themes'] ?? [];
    if (is_array($themes) && in_array(get_template(), $themes, true)) {
        lared_refresh_min_css_if_needed();
    }
}
add_action('upgrader_process_complete', 'lared_after_theme_update_refresh_assets', 10, 2);

// ====== Preconnect 资源提示 — 加速 CDN 域名连接 ======
function lared_resource_hints(array $urls, string $relation_type): array
{
    if ('preconnect' === $relation_type) {
        $urls[] = ['href' => 'https://icons.bluecdn.com', 'crossorigin' => true];
        $urls[] = ['href' => 'https://static.bluecdn.com', 'crossorigin' => true];
        $urls[] = ['href' => 'https://flagcdn.io', 'crossorigin' => true];
    }
    return $urls;
}
add_filter('wp_resource_hints', 'lared_resource_hints', 10, 2);

function lared_assets(): void
{
    // CDN 配置（默认从常量，可在 wp-config.php 覆盖）
    $cdn_icons = LARED_CDN_FONTAWESOME;
    $cdn_static = LARED_CDN_STATIC;

    lared_refresh_min_css_if_needed();

    $style_rel_path = '/assets/css/lared-main.min.css';
    $style_abs_path = get_template_directory() . $style_rel_path;
    if (! file_exists($style_abs_path)) {
        $style_rel_path = '/assets/css/lared-main.css';
        $style_abs_path = get_template_directory() . $style_rel_path;
    }

    // Plyr — 仅在文章页或有音乐播放器配置时加载（提前判断，用于主题样式依赖）
    $need_plyr = is_single() || ('' !== trim((string) get_option('lared_music_playlist', '')));

    // 主题样式（默认优先 lared-main.min.css，不存在时回退 lared-main.css）
    wp_enqueue_style(
        'lared-style',
        get_template_directory_uri() . $style_rel_path,
        $need_plyr ? ['lared-plyr'] : [],
        (string) filemtime($style_abs_path)
    );

    // Font Awesome Pro
    wp_enqueue_style(
        'lared-fontawesome',
        $cdn_icons,
        [],
        null
    );

    // Google Fonts 已移除 — 全部使用系统字体

    // Font Awesome Sharp 变体（补充 all.css 缺失的 sharp 字体定义）
    $fa_sharp_base = str_replace('/css/all.css', '', $cdn_icons);
    foreach (['sharp-thin', 'sharp-light', 'sharp-solid'] as $sharp_variant) {
        wp_enqueue_style(
            'lared-fa-' . $sharp_variant,
            $fa_sharp_base . '/css/' . $sharp_variant . '.css',
            ['lared-fontawesome'],
            null
        );
    }

    // Flag Icons CSS — 仅在首页/文章页/页面加载（用于访客来源国旗和评论区 UA 检测）
    if (is_front_page() || is_single() || is_page()) {
        wp_enqueue_style(
            'lared-flag-icons',
            'https://flagcdn.io/css/flag-icons.min.css',
            [],
            null
        );
    }

    // Plyr CSS + JS（$need_plyr 已在上方判断）
    if ($need_plyr) {
        wp_enqueue_style(
            'lared-plyr',
            $cdn_static . '/plyr@3.7.8/dist/plyr.css',
            [],
            '3.7.8'
        );
        wp_enqueue_script(
            'lared-plyr',
            $cdn_static . '/plyr@3.7.8/dist/plyr.min.js',
            [],
            '3.7.8',
            true
        );
    }

    // PrismJS — 仅在文章页加载（代码高亮）
    if (is_single()) {
        wp_enqueue_style(
            'lared-prism-theme',
            $cdn_static . '/prism-themes@1.9.0/themes/prism-dracula.min.css',
            [],
            '1.9.0'
        );
        wp_enqueue_style(
            'lared-prism-line-numbers',
            $cdn_static . '/prismjs@1.29.0/plugins/line-numbers/prism-line-numbers.min.css',
            ['lared-prism-theme'],
            '1.29.0'
        );
        wp_enqueue_script(
            'lared-prism-core',
            $cdn_static . '/prismjs@1.29.0/components/prism-core.min.js',
            [],
            '1.29.0',
            true
        );
        wp_enqueue_script(
            'lared-prism-autoloader',
            $cdn_static . '/prismjs@1.29.0/plugins/autoloader/prism-autoloader.js',
            ['lared-prism-core'],
            '1.29.0',
            true
        );
        wp_enqueue_script(
            'lared-prism-line-numbers',
            $cdn_static . '/prismjs@1.29.0/plugins/line-numbers/prism-line-numbers.min.js',
            ['lared-prism-core'],
            '1.29.0',
            true
        );
    }

    // Theme JS — merged bundle: lazysizes + Pjax + ViewImage + Theme main
    // min 文件存在即优先使用，确保生产环境始终加载压缩版。
    $js_min_file = get_template_directory() . '/assets/js/lared-app.min.js';
    $js_file = file_exists($js_min_file)
        ? '/assets/js/lared-app.min.js'
        : '/assets/js/lared-app.js';

    $theme_deps = [];
    if ($need_plyr) {
        $theme_deps[] = 'lared-plyr';
    }
    if (is_single()) {
        $theme_deps[] = 'lared-prism-autoloader';
        $theme_deps[] = 'lared-prism-line-numbers';
    }
    wp_enqueue_script(
        'lared-theme',
        get_template_directory_uri() . $js_file,
        $theme_deps,
        (string) filemtime(get_template_directory() . $js_file),
        ['strategy' => 'defer', 'in_footer' => false]  // <head> + defer：不阻塞渲染，lazysizes 仍尽早初始化
    );

    // WordPress 内置回复脚本（moveForm）— 因使用 PJAX，需始终加载，
    // 否则从非 singular 页面导航到文章页时 window.addComment 不存在
    wp_enqueue_script('comment-reply');
}
add_action('wp_enqueue_scripts', 'lared_assets');

// ====================================================================
// 主题配色方案 — 根据后台选项输出 inline CSS 覆盖 :root 变量
// ====================================================================

/**
 * 返回所有配色方案的定义
 */
function lared_get_color_schemes(): array
{
    return [
        'red' => [
            'accent'     => '#f53004',
            'hover'      => '#d42a03',
            'accent-rgb' => '245,48,4',
            'heat-1'     => '#fdd9d2',
            'heat-2'     => '#fbb0a2',
            'heat-3'     => '#f98470',
            'heat-4'     => '#f75a3a',
            'heat-5'     => '#f53004',
        ],
        'blue' => [
            'accent'     => '#2563eb',
            'hover'      => '#1d4ed8',
            'accent-rgb' => '37,99,235',
            'heat-1'     => '#dbeafe',
            'heat-2'     => '#93c5fd',
            'heat-3'     => '#3b82f6',
            'heat-4'     => '#2563eb',
            'heat-5'     => '#1d4ed8',
        ],
        'green' => [
            'accent'     => '#16a34a',
            'hover'      => '#15803d',
            'accent-rgb' => '22,163,74',
            'heat-1'     => '#dcfce7',
            'heat-2'     => '#86efac',
            'heat-3'     => '#4ade80',
            'heat-4'     => '#22c55e',
            'heat-5'     => '#16a34a',
        ],
        'pink' => [
            'accent'     => '#e8437f',
            'hover'      => '#d6336c',
            'accent-rgb' => '232,67,127',
            'heat-1'     => '#fce7f3',
            'heat-2'     => '#f9a8d4',
            'heat-3'     => '#f472b6',
            'heat-4'     => '#ec4899',
            'heat-5'     => '#e8437f',
        ],
        'black' => [
            'accent'     => '#2d2d2d',
            'hover'      => '#1a1a1a',
            'accent-rgb' => '45,45,45',
            'heat-1'     => '#e5e5e5',
            'heat-2'     => '#bfbfbf',
            'heat-3'     => '#8c8c8c',
            'heat-4'     => '#595959',
            'heat-5'     => '#2d2d2d',
        ],
    ];
}

/**
 * 输出主题配色 inline CSS — 附加到 lared-style 之后，确保层叠顺序正确覆盖 :root 变量
 */
function lared_output_color_scheme_css(): void
{
    $scheme_key = (string) get_option('lared_color_scheme', 'red');
    $schemes    = lared_get_color_schemes();

    // 默认红色不需要额外输出（已在 CSS 文件中定义）
    if ('red' === $scheme_key || !isset($schemes[$scheme_key])) {
        return;
    }

    $s = $schemes[$scheme_key];
    $css  = ":root {";
    $css .= "--color-accent:{$s['accent']};";
    $css .= "--color-accent-hover:{$s['hover']};";
    $css .= "--color-accent-rgb:{$s['accent-rgb']};";
    $css .= "--color-heat-1:{$s['heat-1']};";
    $css .= "--color-heat-2:{$s['heat-2']};";
    $css .= "--color-heat-3:{$s['heat-3']};";
    $css .= "--color-heat-4:{$s['heat-4']};";
    $css .= "--color-heat-5:{$s['heat-5']};";
    $css .= "}";

    wp_add_inline_style('lared-style', $css);
}
add_action('wp_enqueue_scripts', 'lared_output_color_scheme_css', 20);

/**
 * 为 PrismJS Core 脚本添加 data-manual 属性，
 * 禁止 Prism 在 DOMContentLoaded 自动高亮（由主题 JS 手动控制时机）。
 */
function lared_prism_script_manual(string $tag, string $handle): string
{
    if ('lared-prism-core' === $handle) {
        $tag = str_replace(' src=', ' data-manual src=', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'lared_prism_script_manual', 10, 2);

/**
 * 统一处理文章内容中的链接：
 * 1. 外部链接自动添加 target="_blank" 和 rel="noopener noreferrer"
 * 2. 在文章/页面/首页中为非排除链接追加箭头图标
 */
function lared_process_content_links(string $content): string
{
    if (is_admin() || '' === trim($content)) {
        return $content;
    }

    $home_url  = home_url();
    $add_icon  = is_single() || is_page() || is_home() || is_front_page();
    $link_icon = '<i class="fa-sharp fa-thin fa-square-arrow-up-right"></i>';

    $content = preg_replace_callback(
        '/<a([^>]*?)href="([^"]*?)"([^>]*?)>(.*?)<\/a>/i',
        function ($matches) use ($home_url, $add_icon, $link_icon) {
            $before = $matches[1];
            $url    = $matches[2];
            $after  = $matches[3];
            $text   = $matches[4];

            $all_attrs  = $before . $after;
            $url_lower  = strtolower($url);

            // 跳过特殊链接
            $skip_icon = str_contains($url_lower, 'javascript:')
                || str_contains($url, '#')
                || str_contains(strtolower($all_attrs), 'no-arrow')
                || str_contains(strtolower($all_attrs), 'dl-button')
                || str_contains(strtolower($text), '<img');

            // 判断是否为外部链接
            $is_external = !str_starts_with($url, $home_url)
                && !preg_match('/^(\/|#|javascript:)/i', $url);

            // 外部链接添加 target 和 rel
            if ($is_external) {
                if (!str_contains($all_attrs, 'target=')) {
                    $after .= ' target="_blank"';
                }
                if (!str_contains($all_attrs, 'rel=')) {
                    $after .= ' rel="noopener noreferrer"';
                }
            }

            // 追加图标（符合条件时）
            if ($add_icon && !$skip_icon) {
                return '<a' . $before . 'href="' . $url . '"' . trim($after) . '>' . $text . ' <span class="lared-inline-link-icon">' . $link_icon . '</span></a>';
            }

            return '<a' . $before . 'href="' . $url . '"' . $after . '>' . $text . '</a>';
        },
        $content
    ) ?? $content;

    return $content;
}
add_filter('the_content', 'lared_process_content_links', 20);

function lared_archive_per_page(): int
{
    return 18;
}
add_filter('lared_archive_posts_per_page', 'lared_archive_per_page');

/**
 * 获取所有管理员用户 ID（带缓存）
 */
function lared_get_admin_user_ids(): array
{
    static $ids = null;
    if (null === $ids) {
        $admins = get_users(['role' => 'administrator', 'fields' => 'ID']);
        $ids = array_map('intval', $admins);
    }
    return $ids;
}

/**
 * 获取客户端真实 IP
 * 支持 Cloudflare（CF-Connecting-IP）、Nginx 反代（X-Real-IP）、直连（REMOTE_ADDR）
 * 可通过 lared_client_ip_headers filter 自定义检测顺序
 */
function lared_get_client_ip(): string
{
    /**
     * 按优先级排列的 IP header 列表
     * CF-Connecting-IP: Cloudflare 会覆写此 header，直连时不存在，不可伪造
     * X-Real-IP: Nginx proxy_set_header 设置，由服务端控制，不可伪造
     * REMOTE_ADDR: 兜底，始终可靠
     *
     * 注意：不使用 HTTP_CLIENT_IP / HTTP_X_FORWARDED_FOR，因为客户端可随意伪造
     *
     * @param string[] $headers header 名称列表（$_SERVER key）
     */
    $headers = apply_filters('lared_client_ip_headers', [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ]);

    foreach ($headers as $header) {
        $value = $_SERVER[$header] ?? '';
        if ('' !== $value) {
            $ip = sanitize_text_field(wp_unslash($value));
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '';
}

/**
 * AJAX 登录
 */
function lared_ajax_login(): void
{
    check_ajax_referer('lared_login_nonce', 'nonce');

    $username   = sanitize_text_field(wp_unslash($_POST['log'] ?? ''));
    $password   = $_POST['pwd'] ?? '';
    $remember   = !empty($_POST['rememberme']);

    if ('' === $username || '' === $password) {
        wp_send_json_error(['message' => __('请填写用户名和密码', 'lared')]);
    }

    // 暴力破解防护：同一 IP 5 分钟内失败 5 次后锁定
    $ip          = lared_get_client_ip();
    $fail_key    = 'lared_login_fail_' . md5($ip);
    $fail_count  = (int) get_transient($fail_key);
    if ($fail_count >= 5) {
        wp_send_json_error(['message' => __('登录尝试过多，请 5 分钟后再试', 'lared')]);
    }

    $creds = [
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember,
    ];

    $user = wp_signon($creds, is_ssl());

    if (is_wp_error($user)) {
        set_transient($fail_key, $fail_count + 1, 5 * MINUTE_IN_SECONDS);
        wp_send_json_error(['message' => __('用户名或密码错误', 'lared')]);
    }

    wp_set_current_user($user->ID);

    wp_send_json_success([
        'message'            => __('登录成功', 'lared'),
        'avatar'             => get_avatar_url($user->ID, ['size' => 60]),
        'name'               => $user->display_name,
        'admin_url'          => admin_url(),
        'logout_url'         => wp_logout_url(home_url()),
        'commentSubmitNonce' => wp_create_nonce('lared_comment_submit'),
        'commentEditNonce'   => wp_create_nonce('lared_comment_edit'),
        'nonce'              => wp_create_nonce('lared_ajax_nonce'),
        'memosFilterNonce'   => wp_create_nonce('lared_memos_filter_nonce'),
        'memosPublishNonce'  => wp_create_nonce('lared_memos_publish_nonce'),
        'friendLinkNonce'    => wp_create_nonce('lared_friend_link_nonce'),
        'levelNonce'         => wp_create_nonce('lared_level_nonce'),
    ]);
}
add_action('wp_ajax_nopriv_lared_ajax_login', 'lared_ajax_login');
add_action('wp_ajax_lared_ajax_login', 'lared_ajax_login');

/**
 * AJAX 评论懒加载 — 滚动到评论区附近时才加载完整评论 HTML
 */
function lared_ajax_load_comments(): void
{
    $post_id = (int) ($_GET['post_id'] ?? 0);
    $post_obj = get_post($post_id);

    if (!$post_obj || 'publish' !== $post_obj->post_status) {
        wp_send_json_error('Invalid post');
    }

    // 设置全局 $post + $withcomments 供 comments_template() 使用
    // comments_template() 内部检查 is_single()||is_page()||$withcomments
    // AJAX 上下文中 is_single() 为 false，必须设置 $withcomments
    global $post, $withcomments;
    $post = $post_obj;
    setup_postdata($post);
    $withcomments = true;

    // 移除 xMojipick 表情选择器的 HTML 输出（主页面已加载，AJAX 无需重复渲染 2MB+ 的 SVG）
    global $wp_filter;
    $xmoji_hooks = ['comment_form_after_fields', 'comment_form_logged_in_after', 'comment_form_top', 'comment_form_after'];
    foreach ($xmoji_hooks as $hook) {
        if (empty($wp_filter[$hook])) continue;
        foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $key => $cb) {
                if (is_array($cb['function']) && is_object($cb['function'][0]) && $cb['function'][0] instanceof \xMojipick_Comment) {
                    unset($wp_filter[$hook]->callbacks[$priority][$key]);
                }
            }
        }
    }

    ob_start();
    comments_template();
    $html = ob_get_clean();

    wp_reset_postdata();
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_lared_load_comments', 'lared_ajax_load_comments');
add_action('wp_ajax_nopriv_lared_load_comments', 'lared_ajax_load_comments');

/**
 * AJAX 搜索 - 实时搜索文章
 */
function lared_ajax_search(): void
{
    // 公开只读接口，不强制 nonce

    $keyword = sanitize_text_field(wp_unslash($_POST['keyword'] ?? ''));
    if ('' === $keyword || mb_strlen($keyword) < 2) {
        wp_send_json_success(['html' => '']);
        return;
    }

    $results = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        's'              => $keyword,
        'posts_per_page' => 8,
        'no_found_rows'  => true,
    ]);

    if (empty($results)) {
        wp_send_json_success(['html' => '<div class="search-modal-empty">没有找到相关文章</div>']);
        return;
    }

    $html = '<ul class="search-modal-results">';
    foreach ($results as $post) {
        $title   = get_the_title($post->ID);
        $excerpt = wp_strip_all_tags(get_the_excerpt($post));
        if ('' === $excerpt) {
            $excerpt = wp_trim_words(wp_strip_all_tags($post->post_content), 30, '…');
        }
        $link = get_permalink($post->ID);

        // 高亮关键词
        $esc_keyword   = preg_quote($keyword, '/');
        $title_marked  = preg_replace('/(' . $esc_keyword . ')/iu', '<mark>$1</mark>', esc_html($title));
        $excerpt_marked = preg_replace('/(' . $esc_keyword . ')/iu', '<mark>$1</mark>', esc_html($excerpt));

        $html .= '<li>';
        $html .= '<a href="' . esc_url($link) . '" class="search-modal-result-item">';
        $html .= '<div class="search-modal-result-title">' . $title_marked . '</div>';
        $html .= '<div class="search-modal-result-excerpt">' . $excerpt_marked . '</div>';
        $html .= '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';

    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_lared_ajax_search', 'lared_ajax_search');
add_action('wp_ajax_nopriv_lared_ajax_search', 'lared_ajax_search');

/**
 * 搜索结果只显示文章，排除页面等其他 post type。
 */
function lared_search_only_posts(\WP_Query $query): void
{
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        $query->set('post_type', 'post');
    }
}
add_action('pre_get_posts', 'lared_search_only_posts');

function lared_localize_script(): void
{
    // 获取当前 Gravatar CDN 域名（兼容 wp-starter-kit 插件配置）
    $avatar_host = 'gravatar.bluecdn.com';
    $sk_options = get_option('wp_starter_kit_options');
    if (!empty($sk_options['cdn_url']) && $sk_options['cdn_url'] !== 'custom') {
        $avatar_host = $sk_options['cdn_url'];
    } elseif (!empty($sk_options['cdn_url']) && $sk_options['cdn_url'] === 'custom' && !empty($sk_options['custom_cdn_url'])) {
        $avatar_host = $sk_options['custom_cdn_url'];
    }

    wp_localize_script('lared-theme', 'LaredAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lared_ajax_nonce'),
        'loginNonce' => wp_create_nonce('lared_login_nonce'),
        'logoutUrl' => wp_logout_url(home_url()),
        'adminUrl' => admin_url(),
        'isLoggedIn' => is_user_logged_in(),
        'memosFilterNonce' => wp_create_nonce('lared_memos_filter_nonce'),
        'memosPublishNonce' => wp_create_nonce('lared_memos_publish_nonce'),
        'commentSubmitNonce' => wp_create_nonce('lared_comment_submit'),
        'commentEditNonce' => wp_create_nonce('lared_comment_edit'),
        'friendLinkNonce' => wp_create_nonce('lared_friend_link_nonce'),
        'levelNonce' => wp_create_nonce('lared_level_nonce'),
        'themeUrl' => get_template_directory_uri(),
        'avatarBaseUrl' => 'https://' . $avatar_host . '/avatar/',
        'homeUrl' => trailingslashit(home_url()),
    ]);
}
add_action('wp_enqueue_scripts', 'lared_localize_script', 20);

function lared_get_site_running_days_from_first_post(): int
{
    $cached = get_transient('lared_site_running_days');
    if (false !== $cached) {
        return (int) $cached;
    }

    $first_post = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => 'date',
        'order' => 'ASC',
        'no_found_rows' => true,
    ]);

    if (empty($first_post)) {
        return 0;
    }

    $first_date      = get_the_date('Y-m-d', $first_post[0]->ID);
    $first_timestamp = strtotime((string) $first_date);
    if (false === $first_timestamp) {
        return 0;
    }
    $now_timestamp = current_datetime()->getTimestamp();
    $days = (int) round(($now_timestamp - $first_timestamp) / DAY_IN_SECONDS);
    $days = max(0, $days);

    // 缓存 12 小时，每天自然过期后重新计算
    set_transient('lared_site_running_days', $days, 12 * HOUR_IN_SECONDS);

    return $days;
}

function lared_remove_latex_backslashes(string $content): string
{
    if (is_admin() || '' === trim($content)) {
        return $content;
    }

    // 快速检测：内容中没有反斜杠则无需处理
    if (!str_contains($content, '\\')) {
        return $content;
    }

    $patterns = [
        '/\\\\begin\{([^}]+)\}/s' => '\\begin{$1}',
        '/\\\\end\{([^}]+)\}/s' => '\\end{$1}',
        '/\\\\([a-zA-Z]+)/s' => '\\$1',
        '/\\\\\[/s' => '\\[',
        '/\\\\\]/s' => '\\]',
        '/\\\\\(/s' => '\\(',
        '/\\\\\)/s' => '\\)',
        '/\\\\,/s' => '\\,',
        '/\\\\;/s' => '\\;',
        '/\\\\:/s' => '\\:',
        '/\\\\!/s' => '\\!',
        '/\\\\（/s' => '\\（',
        '/\\\\）/s' => '\\）',
        '/\\\\｛/s' => '\\｛',
        '/\\\\｝/s' => '\\｝',
    ];

    $result = preg_replace(array_keys($patterns), array_values($patterns), $content);

    return null !== $result ? $result : $content;
}
add_filter('the_content', 'lared_remove_latex_backslashes', 5);

function lared_remove_title_backslashes(string $title): string
{
    if (is_admin()) {
        return $title;
    }

    $title = str_replace('\\', '', $title);

    return $title;
}
add_filter('the_title', 'lared_remove_title_backslashes', 10);
add_filter('single_post_title', 'lared_remove_title_backslashes', 10);

function lared_migrate_template_paths(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $done = (string) get_option('lared_template_path_migrated_v4', '');
    if ('yes' === $done) {
        return;
    }

    $template_map = [
        'template-archive-page.php' => 'templates/page-archive.php',
        'template-friend-links.php' => 'templates/page-friend-links.php',
        'template-subscriptions.php' => 'templates/page-feed.php',
        'templates/page-subscriptions.php' => 'templates/page-feed.php',
        'template-memos-page.php' => 'templates/page-memos.php',
        'page-about.php' => 'templates/page-about-main.php',
        'pages.php' => 'default',
    ];

    foreach ($template_map as $old => $new) {
        $page_ids = get_posts([
            'post_type' => 'page',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => '_wp_page_template',
            'meta_value' => $old,
            'no_found_rows' => true,
        ]);

        if (empty($page_ids)) {
            continue;
        }

        foreach ($page_ids as $page_id) {
            if ('default' === $new) {
                delete_post_meta($page_id, '_wp_page_template');
            } else {
                update_post_meta($page_id, '_wp_page_template', $new);
            }
        }
    }

    update_option('lared_template_path_migrated_v4', 'yes', true);
}
add_action('admin_init', 'lared_migrate_template_paths');

/**
 * 一次性迁移：将旧 pan_* 数据库选项复制到新 lared_* 键名
 * 仅在新键不存在时迁移，迁移完成后打上标记不再重复执行
 */
function lared_migrate_option_prefix(): void
{
    if ('done' === get_option('lared_options_migrated_from_pan', '')) {
        return;
    }

    $option_map = [
        'pan_aplayer_playlist_json'    => 'lared_aplayer_playlist_json',
        'pan_music_playlist_urls'      => 'lared_music_playlist_urls',
        'pan_music_meting_api_template' => 'lared_music_meting_api_template',
        'pan_memos_site_url'           => 'lared_memos_site_url',
        'pan_memos_api_url'            => 'lared_memos_api_url',
        'pan_memos_api_token'          => 'lared_memos_api_token',
        'pan_memos_page_size'          => 'lared_memos_page_size',
        'pan_umami_script'             => 'lared_umami_script',
        'pan_ten_year_start_date'      => 'lared_ten_year_start_date',
        'pan_default_featured_image'   => 'lared_default_featured_image',
        'pan_featured_image_api'       => 'lared_featured_image_api',
        'pan_enable_lazyload'          => 'lared_enable_lazyload',
        'pan_image_load_animation'     => 'lared_image_load_animation',
        'pan_template_path_migrated_v4' => 'lared_template_path_migrated_v4',
    ];

    foreach ($option_map as $old_key => $new_key) {
        $old_val = get_option($old_key);
        if (false !== $old_val && false === get_option($new_key)) {
            update_option($new_key, $old_val, true);
        }
    }

    update_option('lared_options_migrated_from_pan', 'done', true);
}
add_action('admin_init', 'lared_migrate_option_prefix');

/**
 * 博客十年之约进度数据
 * 开始日期通过 "外观 > Lared 设置" 中的 lared_ten_year_start_date 选项配置
 *
 * @return array{start_date:string,end_date:string,progress_percent:float,remaining_days:int,is_started:bool}
 */
function lared_get_ten_year_progress_data(): array
{
    $empty = [
        'start_date'       => '',
        'end_date'         => '',
        'progress_percent' => 0.0,
        'remaining_days'   => 0,
        'is_started'       => false,
    ];

    $start_raw = (string) get_option('lared_ten_year_start_date', '');

    // 后台未设置时，fallback 到第一篇文章的发布日期
    if ('' === $start_raw) {
        $first_post = get_posts([
            'numberposts'      => 1,
            'orderby'          => 'date',
            'order'            => 'ASC',
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'suppress_filters' => true,
        ]);
        if (!empty($first_post)) {
            $start_raw = get_the_date('Y-m-d', $first_post[0]);
        }
        if ('' === $start_raw) {
            return $empty;
        }
    }

    $tz    = wp_timezone();
    $start = DateTimeImmutable::createFromFormat('Y-m-d', $start_raw, $tz);
    if (false === $start) {
        return $empty;
    }

    $end  = $start->modify('+10 years');
    $now  = new DateTimeImmutable('now', $tz);

    $total_seconds     = $end->getTimestamp() - $start->getTimestamp();
    $elapsed_seconds   = $now->getTimestamp() - $start->getTimestamp();
    $remaining_seconds = $end->getTimestamp() - $now->getTimestamp();

    $progress_percent = $total_seconds > 0
        ? min(100.0, max(0.0, ($elapsed_seconds / $total_seconds) * 100.0))
        : 0.0;

    $remaining_days = $remaining_seconds > 0
        ? (int) ceil($remaining_seconds / DAY_IN_SECONDS)
        : 0;

    return [
        'start_date'       => $start->format('Y-m-d'),
        'end_date'         => $end->format('Y-m-d'),
        'progress_percent' => (float) round($progress_percent, 2),
        'remaining_days'   => $remaining_days,
        'is_started'       => $elapsed_seconds >= 0,
    ];
}

/**
 * 读取文章浏览量（post meta: post_views）
 */
function lared_get_post_views(int $post_id): int
{
    return (int) get_post_meta($post_id, 'post_views', true);
}

/**
 * 获取全站总浏览量（首页访问 + 全部文章浏览）
 * 结果缓存 1 小时，文章或首页访问量更新时自动失效
 */
function lared_get_total_views(): int
{
    $cached = get_transient('lared_total_views_cache');
    if (false !== $cached) {
        return (int) $cached;
    }

    global $wpdb;
    $home_views = (int) get_option('lared_home_views', 0);
    $page_views = (int) $wpdb->get_var(
        "SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = 'post_views' AND p.post_status = 'publish' AND p.post_type = 'post'"
    );
    $total = $home_views + $page_views;

    set_transient('lared_total_views_cache', $total, HOUR_IN_SECONDS);
    return $total;
}

/**
 * AJAX 自增文章浏览量
 * 由前端 JS 在单篇文章页触发（兼容 PJAX 导航）
 * 每次请求直接自增，无服务端节流
 * 使用 $wpdb 原子自增，避免竞态条件
 */
function lared_track_post_views_ajax(): void
{
    $post_id = (int) ($_POST['post_id'] ?? 0);
    if ($post_id < 1 || 'publish' !== get_post_status($post_id)) {
        wp_send_json_error(['message' => 'Invalid post']);
        return;
    }

    // 原子自增
    global $wpdb;
    if (metadata_exists('post', $post_id, 'post_views')) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = 'post_views' LIMIT 1",
            $post_id
        ));
    } else {
        add_post_meta($post_id, 'post_views', 1, true);
    }

    // 清理对象缓存，并使全站总浏览量缓存失效
    wp_cache_delete($post_id, 'post_meta');
    delete_transient('lared_total_views_cache');

    wp_send_json_success(['views' => lared_get_post_views($post_id)]);
}
add_action('wp_ajax_lared_track_views', 'lared_track_post_views_ajax');
add_action('wp_ajax_nopriv_lared_track_views', 'lared_track_post_views_ajax');

/**
 * 清理内联 code 标签中的反引号
 * 处理 Gutenberg/编辑器自动添加的反引号
 */
function lared_clean_code_backticks(string $content): string
{
    if (is_admin() || '' === trim($content)) {
        return $content;
    }

    $original = $content;

    // 匹配 code 标签及其内容
    $content = preg_replace_callback(
        '/<code([^>]*)>([^<]*)<\/code>/i',
        function ($matches) {
            $attrs = $matches[1];
            $inner = $matches[2];

            // 检查是否包含反引号（包括 HTML 实体）
            $hasLeadingBacktick  = (bool) preg_match('/^(&#96;|&#x60;|&grave;|`|\'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+/u', $inner);
            $hasTrailingBacktick = (bool) preg_match('/(&#96;|&#x60;|&grave;|`|\'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+$/u', $inner);

            if ($hasLeadingBacktick || $hasTrailingBacktick) {
                // 去除开头和结尾的反引号及其 HTML 实体
                $cleaned = preg_replace('/^(&#96;|&#x60;|&grave;|`|\'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+/u', '', $inner);
                $cleaned = preg_replace('/(&#96;|&#x60;|&grave;|`|\'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+$/u', '', (string) $cleaned);

                return '<code' . $attrs . '>' . $cleaned . '</code>';
            }

            return '<code' . $attrs . '>' . $inner . '</code>';
        },
        $content
    );

    if (null === $content) {
        return $original;
    }

    return $content;
}
add_filter('the_content', 'lared_clean_code_backticks', 999);
add_filter('the_excerpt', 'lared_clean_code_backticks', 999);

// ====== 分类描述中保留 FA 图标 ======

/**
 * 允许分类描述中保留 Font Awesome <i> 图标的 class 属性。
 */
function lared_allow_fa_icons_in_term_description(): void
{
    remove_filter('pre_term_description', 'wp_filter_kses');
    add_filter('pre_term_description', 'lared_sanitize_term_description');
}
add_action('init', 'lared_allow_fa_icons_in_term_description');

function lared_sanitize_term_description(string $description): string
{
    return wp_kses($description, [
        'i'      => ['class' => true, 'aria-hidden' => true, 'style' => true],
        'span'   => ['class' => true, 'style' => true],
        'strong' => [],
        'em'     => [],
        'br'     => [],
        'p'      => [],
        'a'      => ['href' => true, 'title' => true, 'target' => true, 'rel' => true],
    ]);
}

// ====== 辅助函数 ======

function lared_get_first_post_date_ymd(): string
{
    $first_post_ids = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => 'date',
        'order' => 'ASC',
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);

    if (empty($first_post_ids)) {
        return '';
    }

    $first_post_id = (int) $first_post_ids[0];
    if ($first_post_id <= 0) {
        return '';
    }

    return (string) get_the_date('Y-m-d', $first_post_id);
}

function lared_get_ten_year_start_date_ymd(): string
{
    $saved = lared_sanitize_ten_year_start_date((string) get_option('lared_ten_year_start_date', ''));
    if ('' !== $saved) {
        return $saved;
    }

    return lared_get_first_post_date_ymd();
}

// ====== 搜索结果一页全展示（上限 200） ======

function lared_set_unlimited_search_results(WP_Query $query): void
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_search()) {
        $query->set('posts_per_page', 200);
        $query->set('nopaging', false);
    }
}
add_action('pre_get_posts', 'lared_set_unlimited_search_results');

// ====== 文章字数统计 ======

/**
 * 获取文章字数（中文按字符计、英文按单词计）
 * 使用 _word_count meta 缓存，避免每次重新计算
 */
function lared_get_word_count(int $post_id): int
{
    $cached = get_post_meta($post_id, '_word_count', true);
    if ('' !== $cached && false !== $cached) {
        return (int) $cached;
    }

    $count = lared_calculate_word_count($post_id);
    if ($count > 0) {
        update_post_meta($post_id, '_word_count', $count);
    }

    return $count;
}

/**
 * 实际计算文章字数
 * 中文：按字符数统计
 * 英文/数字：按空格分隔的单词数统计
 */
function lared_calculate_word_count(int $post_id): int
{
    $content = get_post_field('post_content', $post_id);
    if (empty($content)) {
        return 0;
    }

    // 去除 HTML 标签和短代码
    $content = wp_strip_all_tags(strip_shortcodes($content));
    // 去除多余空白
    $content = preg_replace('/\s+/u', ' ', trim($content));

    if ('' === $content) {
        return 0;
    }

    // 统计中文字符数
    $chinese_count = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', $content);

    // 去掉中文后统计英文单词数
    $without_chinese = preg_replace('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', ' ', $content);
    $without_chinese = preg_replace('/\s+/', ' ', trim($without_chinese));
    $english_count = ('' !== $without_chinese) ? count(array_filter(explode(' ', $without_chinese))) : 0;

    return $chinese_count + $english_count;
}

/**
 * 文章保存时自动更新字数统计
 */
function lared_update_word_count_on_save(int $post_id): void
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if ('publish' !== get_post_status($post_id)) {
        return;
    }

    $count = lared_calculate_word_count($post_id);
    update_post_meta($post_id, '_word_count', $count);
}
add_action('save_post', 'lared_update_word_count_on_save');

// ====== 缓存失效 — 文章变动时清除首页 transient + 文件缓存 ======
function lared_clear_homepage_cache($post_id, $post = null): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if ($post && 'post' !== $post->post_type) {
        return;
    }

    delete_transient('lared_hero_items');
    delete_transient('lared_popular_posts');
    delete_transient('lared_random_sidebar_posts');
    delete_transient('lared_latest_comments');
    delete_transient('lared_popular_tags');
    delete_transient('lared_site_stats');
    delete_transient('lared_total_views_cache');
    delete_transient('lared_latest_sidebar_posts');
    lared_clear_heatmap_cache();
}
add_action('save_post', 'lared_clear_homepage_cache', 20, 2);
add_action('deleted_post', 'lared_clear_homepage_cache', 10, 2);

// 评论变动时清除评论缓存
function lared_clear_comment_cache(): void
{
    delete_transient('lared_latest_comments');
}
add_action('comment_post', 'lared_clear_comment_cache');
add_action('edit_comment', 'lared_clear_comment_cache');
add_action('transition_comment_status', 'lared_clear_comment_cache');

// 分类/标签编辑时清除 hero 缓存（分类描述中的图标可能变化）
function lared_clear_term_cache(): void
{
    delete_transient('lared_hero_items');
}
add_action('edited_term', 'lared_clear_term_cache');
add_action('created_term', 'lared_clear_term_cache');
add_action('delete_term', 'lared_clear_term_cache');

/**
 * 格式化字数显示（如 1,234 字 / 约 5 分钟阅读）
 */
function lared_format_word_count(int $post_id): string
{
    $count = lared_get_word_count($post_id);
    return number_format($count) . ' 字';
}

/**
 * 获取预估阅读时间（分钟）
 * 中文平均阅读速度约 400-500 字/分钟，取 400
 */
function lared_get_reading_time(int $post_id): int
{
    $count = lared_get_word_count($post_id);
    return max(1, (int) ceil($count / 400));
}

/**
 * 格式化大数字显示（如 43100 → "4.3万"，8000 → "8,000"）
 */
function lared_format_number(int $num): string
{
    if ($num >= 100_000_000) {
        return round($num / 100_000_000, 1) . '亿';
    }
    if ($num >= 10_000) {
        return round($num / 10_000, 1) . '万';
    }
    return number_format($num);
}

/**
 * AJAX 自增首页访问量（存储在 wp_options: lared_home_views）
 * 每次请求直接自增，无服务端节流
 * 使用 $wpdb 原子自增
 */
function lared_track_home_views_ajax(): void
{
    global $wpdb;
    $wpdb->query(
        "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = 'lared_home_views'"
    );
    wp_cache_delete('lared_home_views', 'options');
    delete_transient('lared_total_views_cache');

    wp_send_json_success(['views' => (int) get_option('lared_home_views', 0)]);
}
add_action('wp_ajax_lared_track_home_views', 'lared_track_home_views_ajax');
add_action('wp_ajax_nopriv_lared_track_home_views', 'lared_track_home_views_ajax');

/**
 * AJAX 记录最近访客地理位置
 * 通过 ip.bluecdn.com 获取 IP 地理信息，存储到 wp_options: lared_last_visitor
 * 使用 transient 节流：同一 IP 5 分钟内仅查询一次外部 API
 */
function lared_track_visitor_ajax(): void
{
    $ip = lared_get_client_ip();

    // 跳过本地 / 私有 IP
    if (
        empty($ip)
        || $ip === '127.0.0.1'
        || $ip === '::1'
        || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
    ) {
        wp_send_json_success(['skipped' => true]);
        return;
    }

    // Transient 节流：同一 IP 5 分钟内直接返回缓存数据，不调用外部 API
    $throttle_key = 'lared_vis_' . md5($ip);
    $cached = get_transient($throttle_key);
    if (is_array($cached)) {
        wp_send_json_success($cached);
        return;
    }

    $last = get_option('lared_last_visitor', []);

    // 相同 IP 不重复调用 API
    if (!empty($last['ip']) && $last['ip'] === $ip) {
        $last['timestamp'] = time();
        update_option('lared_last_visitor', $last, false);
        set_transient($throttle_key, $last, 300);
        wp_send_json_success($last);
        return;
    }

    // 调用 ip.bluecdn.com（超时缩短到 3 秒）
    $api_url = 'https://ip.bluecdn.com/geoip/' . rawurlencode($ip);
    $response = wp_remote_get($api_url, [
        'timeout' => 3,
        'headers' => [
            'Accept'          => 'application/json',
            'X-Forwarded-For' => $ip,
            'X-Real-IP'       => $ip,
        ],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'API request failed']);
        return;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body)) {
        wp_send_json_error(['message' => 'Invalid API response']);
        return;
    }
    if (isset($body['data']) && is_array($body['data'])) {
        $body = $body['data'];
    }

    $country = trim((string) ($body['country'] ?? $body['country_name'] ?? $body['countryName'] ?? ''));
    $country_code = strtolower(trim((string) ($body['countryCode'] ?? $body['country_code'] ?? '')));
    $region_name = trim((string) ($body['regionName'] ?? $body['region'] ?? $body['province'] ?? ''));
    $city = trim((string) ($body['city'] ?? $body['cityName'] ?? ''));

    if ($country === '' && $country_code === '') {
        wp_send_json_error(['message' => 'Invalid API response']);
        return;
    }

    $visitor_data = [
        'ip'          => $ip,
        'country'     => $country,
        'countryCode' => $country_code,
        'regionName'  => $region_name,
        'city'        => $city,
        'timestamp'   => time(),
    ];

    update_option('lared_last_visitor', $visitor_data, false);
    set_transient($throttle_key, $visitor_data, 300);
    wp_send_json_success($visitor_data);
}
add_action('wp_ajax_lared_track_visitor', 'lared_track_visitor_ajax');
add_action('wp_ajax_nopriv_lared_track_visitor', 'lared_track_visitor_ajax');

// ── 友链申请 AJAX ──
function lared_ajax_apply_friend_link()
{
    if (! wp_verify_nonce($_POST['nonce'] ?? '', 'lared_friend_link_nonce')) {
        wp_send_json_error(['message' => '安全验证失败，请刷新页面重试']);
    }

    $name   = sanitize_text_field($_POST['flink_name'] ?? '');
    $url    = esc_url_raw($_POST['flink_url'] ?? '');
    $desc   = sanitize_text_field($_POST['flink_desc'] ?? '');
    $feed   = esc_url_raw($_POST['flink_feed'] ?? '');
    $avatar = esc_url_raw($_POST['flink_avatar'] ?? '');

    if (empty($name) || empty($url)) {
        wp_send_json_error(['message' => '站点名称和网址为必填项']);
    }

    // 检查是否已存在相同 URL 的链接
    $existing = get_bookmarks(['search' => $url]);
    foreach ($existing as $link) {
        if (trailingslashit($link->link_url) === trailingslashit($url)) {
            wp_send_json_error(['message' => '该站点已在友链列表中']);
        }
    }

    $linkdata = [
        'link_name'        => $name,
        'link_url'         => $url,
        'link_description' => $desc,
        'link_rss'         => $feed,
        'link_image'       => $avatar,
        'link_visible'     => 'N', // 隐藏，待审核
    ];

    $result = wp_insert_link($linkdata, true);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => '提交失败：' . $result->get_error_message()]);
    }

    wp_send_json_success(['message' => '提交成功！待站长审核后将显示在友链列表中。']);
}
add_action('wp_ajax_lared_apply_friend_link', 'lared_ajax_apply_friend_link');
add_action('wp_ajax_nopriv_lared_apply_friend_link', 'lared_ajax_apply_friend_link');

/*
 * ================================================================
 *  Inlined from inc/inc-download.php
 * ================================================================
 */

function lared_download_button_shortcode(array $atts): string
{
    $atts = shortcode_atts([
        'dl_url'     => '',
        'dl_name'    => '未知文件',
        'dl_text'    => '立即下载',
        'dl_size'    => '',
        'dl_format'  => '',
        'dl_version' => '',
        'dl_note'    => '',
    ], $atts, 'download_button');

    $url = esc_url(trim($atts['dl_url']));
    if ('' === $url) {
        return '<div class="download-error">错误：请提供下载链接</div>';
    }

    $name    = esc_html(trim($atts['dl_name']));
    $text    = esc_html(trim($atts['dl_text']));
    $size    = esc_html(trim($atts['dl_size']));
    $format  = esc_html(trim($atts['dl_format']));
    $version = esc_html(trim($atts['dl_version']));
    $note    = trim($atts['dl_note']);

    $dl_key  = md5($url);
    $counts  = (array) get_option('lared_dl_counts', []);
    $count   = (int) ($counts[$dl_key] ?? 0);

    ob_start();
?>
    <div class="lared-download-box">
        <div class="dl-header">
            <div class="dl-icon">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div class="dl-info">
                <h4 class="dl-name"><?php echo esc_html($name); ?></h4>
                <?php if ('' !== $format || '' !== $version || '' !== $size) : ?>
                    <div class="dl-badges">
                        <?php if ('' !== $format) : ?>
                            <span class="dl-badge dl-format"><?php echo esc_html($format); ?></span>
                        <?php endif; ?>
                        <?php if ('' !== $version) : ?>
                            <span class="dl-badge dl-version"><?php echo esc_html($version); ?></span>
                        <?php endif; ?>
                        <?php if ('' !== $size) : ?>
                            <span class="dl-badge dl-size"><?php echo esc_html($size); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="dl-count" title="<?php esc_attr_e('下载次数', 'lared'); ?>">
                <i class="fa-solid fa-download"></i>
                <span class="dl-count-number" data-dl-count="<?php echo esc_attr($dl_key); ?>"><?php echo esc_html(number_format($count)); ?></span>
            </div>
        </div>
        <?php if ('' !== $note) : ?>
            <div class="dl-note">
                <i class="fa-solid fa-circle-info"></i>
                <span><?php echo nl2br(esc_html($note)); ?></span>
            </div>
        <?php endif; ?>
        <a class="dl-button no-arrow" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" data-no-mshot="1"
            data-dl-track="<?php echo esc_attr($dl_key); ?>">
            <span class="dl-btn-icon"><i class="fa-solid fa-download"></i></span>
            <span class="dl-btn-text"><?php echo esc_html($text); ?></span>
        </a>
    </div>
<?php
    return (string) ob_get_clean();
}
add_shortcode('download_button', 'lared_download_button_shortcode');

function lared_ajax_track_download(): void
{
    $dl_key = sanitize_key((string) ($_POST['dl_key'] ?? ''));
    if ('' === $dl_key || !preg_match('/^[a-f0-9]{32}$/', $dl_key)) {
        wp_send_json_error(['message' => 'Invalid key']);
        return;
    }

    $counts = (array) get_option('lared_dl_counts', []);
    $counts[$dl_key] = (int) ($counts[$dl_key] ?? 0) + 1;
    update_option('lared_dl_counts', $counts, false);

    wp_send_json_success(['count' => $counts[$dl_key]]);
}
add_action('wp_ajax_lared_track_download', 'lared_ajax_track_download');
add_action('wp_ajax_nopriv_lared_track_download', 'lared_ajax_track_download');

function lared_download_tracking_script(): void
{
    if (is_admin()) {
        return;
    }
?>
    <script>
        (function() {
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('[data-dl-track]');
                if (!btn) return;
                var key = btn.getAttribute('data-dl-track');
                if (!key) return;
                var fd = new FormData();
                fd.append('action', 'lared_track_download');
                fd.append('dl_key', key);
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(d) {
                        if (d.success && d.data.count) {
                            var els = document.querySelectorAll('[data-dl-count="' + key + '"]');
                            els.forEach(function(el) {
                                el.textContent = d.data.count.toLocaleString();
                            });
                        }
                    })
                    .catch(function() {});
            });
        })();
    </script>
<?php
}
add_action('wp_footer', 'lared_download_tracking_script', 99);

/*
 * ================================================================
 *  Inlined from inc/inc-code-runner.php
 * ================================================================
 */

$GLOBALS['_lared_cr_store'] = [];

function lared_cr_protect_content($content)
{
    if (!str_contains($content, '[code_runner')) return $content;

    $output = '';
    $pos    = 0;
    $len    = strlen($content);

    while ($pos < $len) {
        $start = strpos($content, '[code_runner', $pos);
        if ($start === false) {
            $output .= substr($content, $pos);
            break;
        }

        $output .= substr($content, $pos, $start - $pos);

        $tagEnd = strpos($content, ']', $start + 12);
        if ($tagEnd === false) {
            $output .= substr($content, $start);
            break;
        }

        $attrs = substr($content, $start + 12, $tagEnd - $start - 12);

        if (substr(trim($attrs), -1) === '/') {
            $output .= substr($content, $start, $tagEnd - $start + 1);
            $pos = $tagEnd + 1;
            continue;
        }

        $closeTag = strpos($content, '[/code_runner]', $tagEnd + 1);
        if ($closeTag === false) {
            $output .= substr($content, $start);
            break;
        }

        $inner = substr($content, $tagEnd + 1, $closeTag - $tagEnd - 1);

        $id = 'cr_' . md5($inner . microtime(true) . mt_rand());
        $GLOBALS['_lared_cr_store'][$id] = $inner;

        $output .= '[code_runner' . $attrs . ' _crid="' . $id . '"][/code_runner]';

        $pos = $closeTag + 14;
    }

    return $output;
}
add_filter('the_content', 'lared_cr_protect_content', 6);

add_filter('no_texturize_shortcodes', function ($list) {
    $list[] = 'code_runner';
    return $list;
});

function lared_code_runner_shortcode($atts, $content = null)
{
    $atts = shortcode_atts([
        'title'  => '代码预览',
        'height' => '400',
        '_crid'  => '',
    ], $atts, 'code_runner');

    $title  = sanitize_text_field($atts['title']);
    $height = max(200, intval($atts['height']));

    $code = '';

    $crid = $atts['_crid'];
    if ($crid !== '' && isset($GLOBALS['_lared_cr_store'][$crid])) {
        $raw = trim($GLOBALS['_lared_cr_store'][$crid]);

        $html = $css = $js = '';
        if (str_contains($raw, '{{html}}')) {
            $s = strpos($raw, '{{html}}');
            $e = strpos($raw, '{{/html}}');
            if ($s !== false && $e !== false) {
                $html = trim(substr($raw, $s + 8, $e - $s - 8));
            }
        }
        if (str_contains($raw, '{{css}}')) {
            $s = strpos($raw, '{{css}}');
            $e = strpos($raw, '{{/css}}');
            if ($s !== false && $e !== false) {
                $css = trim(substr($raw, $s + 7, $e - $s - 7));
            }
        }
        if (str_contains($raw, '{{js}}')) {
            $s = strpos($raw, '{{js}}');
            $e = strpos($raw, '{{/js}}');
            if ($s !== false && $e !== false) {
                $js = trim(substr($raw, $s + 6, $e - $s - 6));
            }
        }

        if ($html !== '') {
            $code = $html;
            if ($css !== '' && stripos($html, '</style>') === false) {
                $code = '<style>' . "\n" . $css . "\n" . '</style>' . "\n" . $code;
            }
            if ($js !== '' && stripos($html, '</script>') === false) {
                $code .= "\n" . '<script>' . "\n" . $js . "\n" . '</script>';
            }
        } else {
            $code = $raw;
        }
    }

    if ($code === '' && $content !== null && trim($content) !== '') {
        $code = trim($content);
        $code = preg_replace('/<br\s*\/?>\s*\n?/i', "\n", $code);
        $code = preg_replace('/<\/?p>/i', '', $code);
    }

    if ($code === '') return '';

    return '<pre class="lared-prism-pre" data-cr-runnable="1"'
        . ' data-cr-title="' . esc_attr($title) . '"'
        . ' data-cr-height="' . $height . '">'
        . '<code class="language-markup">' . esc_html($code) . '</code>'
        . '</pre>';
}
add_shortcode('code_runner', 'lared_code_runner_shortcode');

function lared_protect_code_blocks($content)
{
    if (!str_contains($content, '<code')) return $content;

    $output = '';
    $pos    = 0;
    $len    = strlen($content);

    while ($pos < $len) {
        $preStart = strpos($content, '<pre', $pos);
        if ($preStart === false) {
            $output .= substr($content, $pos);
            break;
        }

        $output .= substr($content, $pos, $preStart - $pos);

        $preTagEnd = strpos($content, '>', $preStart + 4);
        if ($preTagEnd === false) {
            $output .= substr($content, $preStart);
            break;
        }

        $preTag = substr($content, $preStart, $preTagEnd - $preStart + 1);

        $preClose = strpos($content, '</pre>', $preTagEnd + 1);
        if ($preClose === false) {
            $output .= substr($content, $preStart);
            break;
        }

        $innerFull = substr($content, $preTagEnd + 1, $preClose - $preTagEnd - 1);

        if (str_contains($innerFull, '<code')) {
            $codeStart = strpos($innerFull, '<code');
            $codeTagEnd = strpos($innerFull, '>', $codeStart + 5);

            if ($codeTagEnd !== false) {
                $codeTag = substr($innerFull, $codeStart, $codeTagEnd - $codeStart + 1);
                $codeClose = strrpos($innerFull, '</code>');

                if ($codeClose !== false && $codeClose > $codeTagEnd) {
                    $codeInner = substr($innerFull, $codeTagEnd + 1, $codeClose - $codeTagEnd - 1);

                    if (str_contains($codeInner, '<')) {
                        $codeInner = htmlspecialchars($codeInner, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
                    }

                    $beforeCode = substr($innerFull, 0, $codeStart);
                    $afterCode  = substr($innerFull, $codeClose + 7);

                    $output .= $preTag . $beforeCode . $codeTag . $codeInner . '</code>' . $afterCode . '</pre>';
                    $pos = $preClose + 6;
                    continue;
                }
            }
        }

        $output .= $preTag . $innerFull . '</pre>';
        $pos = $preClose + 6;
    }

    return $output;
}
add_filter('the_content', 'lared_protect_code_blocks', 7);

function lared_allow_code_data_attributes($tags, $context)
{
    if ($context !== 'post') return $tags;

    if (isset($tags['pre'])) {
        $tags['pre']['data-cr-runnable'] = true;
        $tags['pre']['data-cr-title']    = true;
        $tags['pre']['data-cr-height']   = true;
        $tags['pre']['data-lared-copy-ready'] = true;
    }

    if (isset($tags['code'])) {
        $tags['code']['data-language'] = true;
    }

    // 允许 img 标签的 data-src 属性（lazyload 图片需要）
    if (!isset($tags['img'])) {
        $tags['img'] = [];
    }
    $tags['img']['data-src'] = true;
    $tags['img']['class']    = true;
    $tags['img']['alt']      = true;
    $tags['img']['src']      = true;
    $tags['img']['loading']  = true;

    return $tags;
}
add_filter('wp_kses_allowed_html', 'lared_allow_code_data_attributes', 10, 2);

/*
 * ================================================================
 *  Inlined from inc/inc-about.php
 * ================================================================
 */

function lared_register_about_settings(): void
{
    register_setting('lared_settings_about', 'lared_about_hobbies', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ]);

    register_setting('lared_settings_about', 'lared_about_plans', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ]);

    register_setting('lared_settings_about', 'lared_about_tags', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ]);

    register_setting('lared_settings_about', 'lared_about_social_links', [
        'type'              => 'string',
        'sanitize_callback' => 'lared_sanitize_social_links',
        'default'           => '{}',
    ]);
}
add_action('admin_init', 'lared_register_about_settings');

/**
 * 社交链接 — 清洗回调
 */
function lared_sanitize_social_links(?string $value): string
{
    $value = trim((string) $value);
    if ('' === $value) {
        return '{}';
    }
    $data = json_decode($value, true);
    if (!is_array($data)) {
        return '{}';
    }

    $allowed_keys = array_keys(lared_get_social_platforms());
    $clean = [];

    // 固定平台
    foreach ($data as $key => $url) {
        if (in_array($key, $allowed_keys, true)) {
            $url = trim((string) $url);
            $clean[$key] = '' !== $url ? esc_url_raw($url) : '';
        }
    }

    // 自定义链接
    if (isset($data['_custom']) && is_array($data['_custom'])) {
        $custom_clean = [];
        foreach ($data['_custom'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = trim((string) ($item['name'] ?? ''));
            $icon = trim((string) ($item['icon'] ?? ''));
            $url  = trim((string) ($item['url'] ?? ''));
            if ('' !== $name && '' !== $url) {
                $custom_clean[] = [
                    'name' => sanitize_text_field($name),
                    'icon' => sanitize_text_field($icon),
                    'url'  => esc_url_raw($url),
                ];
            }
        }
        if (!empty($custom_clean)) {
            $clean['_custom'] = $custom_clean;
        }
    }

    return (string) wp_json_encode($clean);
}

/**
 * 支持的社交平台定义（key => [label, icon_class]）
 */
function lared_get_social_platforms(): array
{
    return [
        'x_twitter' => ['X / Twitter', 'fa-brands fa-x-twitter'],
        'github'    => ['GitHub',       'fa-brands fa-square-github'],
        'mastodon'  => ['Mastodon',     'fa-brands fa-mastodon'],
    ];
}

/**
 * 获取已配置的社交链接（仅返回非空项）
 */
function lared_get_about_social_links(): array
{
    $raw = (string) get_option('lared_about_social_links', '{}');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return [];
    }
    $platforms = lared_get_social_platforms();
    $result = [];

    // 固定平台
    foreach ($data as $key => $url) {
        if ('_custom' === $key) {
            continue;
        }
        $url = trim((string) $url);
        if ('' !== $url && isset($platforms[$key])) {
            $result[] = [
                'key'   => $key,
                'url'   => $url,
                'label' => $platforms[$key][0],
                'icon'  => $platforms[$key][1],
            ];
        }
    }

    // 自定义链接
    if (isset($data['_custom']) && is_array($data['_custom'])) {
        foreach ($data['_custom'] as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $url  = trim((string) ($item['url'] ?? ''));
            $icon = trim((string) ($item['icon'] ?? ''));
            if ('' !== $name && '' !== $url) {
                $result[] = [
                    'key'   => 'custom_' . sanitize_title($name),
                    'url'   => $url,
                    'label' => $name,
                    'icon'  => '' !== $icon ? $icon : 'fa-solid fa-link',
                ];
            }
        }
    }

    return $result;
}

function lared_get_about_sidebar_data(): array
{
    $parse = static function (string $raw, string $sep = ','): array {
        if ('' === $raw) {
            return [];
        }
        $items = ',' === $sep
            ? explode(',', $raw)
            : explode("\n", $raw);
        return array_values(array_filter(array_map('trim', $items), static fn($v) => '' !== $v));
    };

    return [
        'hobbies' => $parse((string) get_option('lared_about_hobbies', ''), ','),
        'plans'   => $parse((string) get_option('lared_about_plans', ''), "\n"),
        'tags'    => $parse((string) get_option('lared_about_tags', ''), ','),
    ];
}

function lared_render_tab_about(): void
{
    $hobbies = (string) get_option('lared_about_hobbies', '');
    $plans   = (string) get_option('lared_about_plans', '');
    $tags    = (string) get_option('lared_about_tags', '');
?>
    <form method="post" action="options.php">
        <?php settings_fields('lared_settings_about'); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_about_hobbies"><?php esc_html_e('爱好列表', 'lared'); ?></label></th>
                <td>
                    <input id="lared_about_hobbies" name="lared_about_hobbies" type="text" class="large-text" value="<?php echo esc_attr($hobbies); ?>" placeholder="摄影, 编程, 阅读, 旅行" />
                    <p class="description"><?php esc_html_e('用英文逗号分隔，例如：摄影, 编程, 阅读', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_about_plans"><?php esc_html_e('计划列表', 'lared'); ?></label></th>
                <td>
                    <textarea id="lared_about_plans" name="lared_about_plans" rows="5" class="large-text" placeholder="学习 Rust&#10;完成一次马拉松&#10;独立开发一个 App"><?php echo esc_textarea($plans); ?></textarea>
                    <p class="description"><?php esc_html_e('每行一条计划。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_about_tags"><?php esc_html_e('关键词 / 标签', 'lared'); ?></label></th>
                <td>
                    <input id="lared_about_tags" name="lared_about_tags" type="text" class="large-text" value="<?php echo esc_attr($tags); ?>" placeholder="WordPress, 极简主义, 开源, 独立博客" />
                    <p class="description"><?php esc_html_e('用英文逗号分隔，将在关于页面以标签云形式展示。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('社交链接', 'lared'); ?></th>
                <td>
                    <p class="description" style="margin-bottom:12px;"><?php esc_html_e('填写对应平台的个人主页链接，留空则不在前台显示。', 'lared'); ?></p>
                    <?php
                    $platforms = lared_get_social_platforms();
                    $saved_raw = (string) get_option('lared_about_social_links', '{}');
                    $saved = json_decode($saved_raw, true);
                    if (!is_array($saved)) {
                        $saved = [];
                    }
                    ?>
                    <input type="hidden" id="lared_about_social_links" name="lared_about_social_links" value="<?php echo esc_attr($saved_raw); ?>" />

                    <!-- 固定平台 -->
                    <?php foreach ($platforms as $pkey => $pinfo) :
                        $purl = (string) ($saved[$pkey] ?? '');
                    ?>
                        <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                            <span style="width:120px;flex-shrink:0;"><i class="<?php echo esc_attr($pinfo[1]); ?>" style="width:18px;text-align:center;margin-right:6px;"></i><?php echo esc_html($pinfo[0]); ?></span>
                            <input type="url" class="lared-social-url" data-platform="<?php echo esc_attr($pkey); ?>" value="<?php echo esc_attr($purl); ?>" placeholder="https://" style="flex:1;max-width:none !important;" />
                        </div>
                    <?php endforeach; ?>

                    <!-- 自定义链接 -->
                    <h4 style="margin:16px 0 8px;"><?php esc_html_e('自定义链接', 'lared'); ?></h4>
                    <style>
                        .lared-custom-row { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
                        .lared-custom-row input { max-width:none !important; width:auto !important; box-sizing:border-box; }
                        .lared-custom-row .lared-cs-name { flex:2; }
                        .lared-custom-row .lared-cs-icon { flex:3; }
                        .lared-custom-row .lared-cs-url  { flex:5; }
                        .lared-custom-row .lared-cs-remove { flex-shrink:0; background:none; border:none; color:#b32d2e; cursor:pointer; padding:4px 6px; opacity:.6; }
                        .lared-custom-row .lared-cs-remove:hover { opacity:1; }
                    </style>
                    <div id="lared-custom-social-body">
                        <?php
                        $custom_links = (isset($saved['_custom']) && is_array($saved['_custom'])) ? $saved['_custom'] : [];
                        foreach ($custom_links as $ci) :
                            $cname = esc_attr((string) ($ci['name'] ?? ''));
                            $cicon = esc_attr((string) ($ci['icon'] ?? ''));
                            $curl  = esc_attr((string) ($ci['url'] ?? ''));
                        ?>
                            <div class="lared-custom-row">
                                <input type="text" class="lared-cs-name" value="<?php echo $cname; ?>" placeholder="名称" />
                                <input type="text" class="lared-cs-icon" value="<?php echo $cicon; ?>" placeholder="fa-brands fa-telegram" />
                                <input type="url" class="lared-cs-url" value="<?php echo $curl; ?>" placeholder="https://" />
                                <button type="button" class="lared-cs-remove" title="删除"><span class="dashicons dashicons-trash" style="font-size:18px;width:18px;height:18px;line-height:18px;"></span></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin-top:4px;">
                        <button type="button" class="button" id="lared-cs-add">+ <?php esc_html_e('添加链接', 'lared'); ?></button>
                    </p>

                    <script>
                        (function() {
                            var hidden = document.getElementById('lared_about_social_links');
                            var tbody  = document.getElementById('lared-custom-social-body');

                            function sync() {
                                var obj = {};
                                // 固定平台
                                document.querySelectorAll('.lared-social-url').forEach(function(inp) {
                                    var v = inp.value.trim();
                                    if (v) obj[inp.getAttribute('data-platform')] = v;
                                });
                                // 自定义
                                var customs = [];
                                document.querySelectorAll('.lared-custom-row').forEach(function(row) {
                                    var n = row.querySelector('.lared-cs-name').value.trim();
                                    var i = row.querySelector('.lared-cs-icon').value.trim();
                                    var u = row.querySelector('.lared-cs-url').value.trim();
                                    if (n && u) customs.push({name: n, icon: i, url: u});
                                });
                                if (customs.length) obj._custom = customs;
                                hidden.value = JSON.stringify(obj);
                            }

                            function bindRow(row) {
                                row.querySelectorAll('input').forEach(function(inp) {
                                    inp.addEventListener('input', sync);
                                });
                                row.querySelector('.lared-cs-remove').addEventListener('click', function() {
                                    row.remove();
                                    sync();
                                });
                            }

                            // 绑定已有行
                            document.querySelectorAll('.lared-custom-row').forEach(bindRow);
                            document.querySelectorAll('.lared-social-url').forEach(function(inp) {
                                inp.addEventListener('input', sync);
                            });

                            // 添加按钮
                            document.getElementById('lared-cs-add').addEventListener('click', function() {
                                var row = document.createElement('div');
                                row.className = 'lared-custom-row';
                                row.innerHTML =
                                    '<input type="text" class="lared-cs-name" value="" placeholder="名称" />' +
                                    '<input type="text" class="lared-cs-icon" value="" placeholder="fa-brands fa-telegram" />' +
                                    '<input type="url" class="lared-cs-url" value="" placeholder="https://" />' +
                                    '<button type="button" class="lared-cs-remove" title="删除"><span class="dashicons dashicons-trash" style="font-size:18px;width:18px;height:18px;line-height:18px;"></span></button>';
                                tbody.appendChild(row);
                                bindRow(row);
                            });

                            sync();
                        })();
                    </script>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
<?php
}



// ==================== 文章图片自动排版（patched 2026-04-27 v3）====================

add_filter('the_content', 'lared_auto_photo_layout', 22);

function lared_auto_photo_layout($content) {
    if (
        strpos($content, '<img') === false
        && strpos($content, 'lared-grid-') === false
        && strpos($content, 'wp-block-gallery') === false
    ) {
        return $content;
    }

    $placeholders = [];
    $store = function ($html) use (&$placeholders) {
        $key = '<!--LARED_PHOTOS_' . count($placeholders) . '-->';
        $placeholders[$key] = $html;
        return $key;
    };

    // 1) 旧手动 .lared-grid-2/3/4
    $content = preg_replace_callback(
        '/<div\b[^>]*\bclass\s*=\s*["\'][^"\']*\blared-grid-[234]\b[^"\']*["\'][^>]*>([\s\S]*?)<\/div>/i',
        function ($m) use ($store) {
            preg_match_all('/<figure\b[^>]*>[\s\S]*?<\/figure>|<img\b[^>]*\/?>/i', $m[1], $items);
            if (empty($items[0])) return $m[0];
            $figures = array_map(function ($item) {
                if (stripos($item, '<figure') === 0) return $item;
                return '<figure>' . $item . '</figure>';
            }, $items[0]);
            return $store(lared_build_photo_layout($figures));
        },
        $content
    );

    // 2) wp-block-gallery
    $content = preg_replace_callback(
        '/<figure\b[^>]*\bwp-block-gallery\b[^>]*>([\s\S]*?)<\/figure>/i',
        function ($m) use ($store) {
            if (!preg_match_all('/<figure\b[^>]*>[\s\S]*?<\/figure>/i', $m[1], $figs)) {
                return $m[0];
            }
            return $store(lared_build_photo_layout($figs[0]));
        },
        $content
    );

    // 3) 散落的连续 <figure>（不在 wp-block-gallery 内）
    $content = preg_replace_callback(
        '/(?:<figure\b(?![^>]*\bwp-block-gallery\b)(?:\s[^>]*)?>(?=[\s\S]*?<img\b)[\s\S]*?<\/figure>\s*(?:<p>\s*<\/p>\s*)*){1,}/i',
        function ($m) use ($store) {
            preg_match_all('/<figure\b(?:\s[^>]*)?>[\s\S]*?<\/figure>/i', $m[0], $figs);
            $figs = array_values(array_filter($figs[0], function ($f) {
                return stripos($f, '<img') !== false;
            }));
            if (empty($figs)) return $m[0];
            return $store(lared_build_photo_layout($figs));
        },
        $content
    );

    // 4) 还原占位符
    if (!empty($placeholders)) {
        $content = strtr($content, $placeholders);
    }

    return $content;
}

function lared_split_photo_rows($n) {
    if ($n <= 0) return [];
    if ($n <= 4) return [$n];
    if ($n === 5) return [2, 3];
    if ($n === 6) return [3, 3];
    if ($n === 7) return [3, 4];
    return array_merge([4], lared_split_photo_rows($n - 4));
}

function lared_build_photo_layout($figures) {
    $count = count($figures);
    if ($count === 0) return '';

    if ($count === 1) {
        return '<div class="lared-photos" data-count="1">' . $figures[0] . '</div>';
    }

    $rows = lared_split_photo_rows($count);
    $html = '<div class="lared-photos" data-count="' . $count . '">';
    $idx = 0;
    foreach ($rows as $cols) {
        $slice = array_slice($figures, $idx, $cols);
        $html .= '<div class="lared-photos-row" data-cols="' . $cols . '">' . implode('', $slice) . '</div>';
        $idx += $cols;
    }
    $html .= '</div>';
    return $html;
}


// ==================== 缓存管理 AJAX（patched 2026-04-27）====================

function lared_clear_cache_dirs(): int
{
    $count = 0;
    foreach ([lared_get_link_ico_cache_dir(), lared_get_gravatar_cache_dir()] as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $files = glob($dir . '/*');
        foreach ((array) $files as $f) {
            if (is_file($f) && @unlink($f)) {
                $count++;
            }
        }
    }
    return $count;
}

function lared_ajax_clear_all_cache(): void
{
    check_ajax_referer('lared_cache_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足'], 403);
    }
    $deleted = lared_clear_cache_dirs();
    wp_send_json_success([
        'deleted' => $deleted,
        'message' => sprintf('已清空 %d 个缓存文件', $deleted),
    ]);
}
add_action('wp_ajax_lared_clear_all_cache', 'lared_ajax_clear_all_cache');

function lared_ajax_refresh_all_cache(): void
{
    check_ajax_referer('lared_cache_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足'], 403);
    }

    // 强制重新拉：先把缓存关掉绕过本地命中，循环 wp_remote_get 重新拉所有友链 favicon
    $deleted = lared_clear_cache_dirs();

    $warmed = 0;
    $bookmarks = get_bookmarks(['orderby' => 'name', 'hide_invisible' => 0]);
    foreach ($bookmarks as $b) {
        $host = wp_parse_url((string) ($b->link_url ?? ''), PHP_URL_HOST);
        if (!$host) {
            continue;
        }
        $url = lared_get_cached_link_icon($host);
        if ('' !== $url) {
            $warmed++;
        }
    }

    wp_send_json_success([
        'deleted' => $deleted,
        'warmed'  => $warmed,
        'message' => sprintf('已清空 %d 个缓存，预热 %d 个友链图标', $deleted, $warmed),
    ]);
}
add_action('wp_ajax_lared_refresh_all_cache', 'lared_ajax_refresh_all_cache');

// 注册「关闭缓存」开关
add_action('admin_init', function () {
    register_setting('lared_settings_cache', 'lared_disable_cache', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_toggle_option',
        'default' => '0',
    ]);
});

// ==================== .lared-photos 内剥掉 .img-loading-wrapper 壳（patched 2026-04-27）====================
//   主题的 wrap_images_with_loader 给所有 img 包 figure.img-loading-wrapper > div > spinner > img
//   壳，在 .lared-photos 内多余且引发 specificity 冲突。我们这一步把"figure>figure.wrapper>...<img>"
//   嵌套展平为"figure><img>"，外层 figure 保留以承载 figcaption。
add_filter('the_content', 'lared_strip_loader_in_photos', 30);
function lared_strip_loader_in_photos(string $content): string
{
    if (false === strpos($content, 'lared-photos')) {
        return $content;
    }
    return preg_replace_callback(
        '/(<figure\b[^>]*>\s*)<figure\b[^>]*\bimg-loading-wrapper\b[^>]*>([\s\S]*?)<\/figure>/i',
        function ($m) {
            if (preg_match('/<img\b[^>]*>/i', $m[2], $img)) {
                return $m[1] . $img[0];
            }
            return $m[0];
        },
        $content
    ) ?? $content;
}

// ==================== ViewImage 防重复打开 + 列表去重 patch（patched 2026-04-27）====================
//   主题 ViewImage 库的问题：
//   1. 不防止重复打开 → 叠加多个 overlay，关闭要点很多次
//   2. 不去重 images 列表 → 灯箱底部 1/N 计数虚高，左右切换出现重复同张图
//   都在 footer 注入 patch 修复，不动 lared-app.js
add_action('wp_footer', 'lared_view_image_patch', 100);
function lared_view_image_patch(): void
{
    if (!is_singular() && !is_home() && !is_front_page() && !is_archive()) {
        return;
    }
    ?>
<script>
(function () {
    function patch() {
        if (!window.ViewImage || window.ViewImage.__patched) return;

        // 1. wrap listener：已有 viewer 时拦住新打开
        var origListener = window.ViewImage.listener;
        window.ViewImage.listener = function (a) {
            if (document.querySelector('.view-image')) {
                a.stopPropagation();
                a.preventDefault();
                return;
            }
            return origListener.call(this, a);
        };

        // 2. wrap display：去重 images 列表（防止 1/N 计数虚高、切换重复图）
        var origDisplay = window.ViewImage.display;
        window.ViewImage.display = function (images, current) {
            var deduped = Array.from(new Set((images || []).filter(Boolean)));
            return origDisplay.call(this, deduped, current);
        };

        // 3. 兜底：document 级捕获 click，关闭按钮一次清掉所有 .view-image overlay
        document.addEventListener('click', function (a) {
            if (a.target.closest && a.target.closest('.view-image-close')) {
                document.querySelectorAll('.view-image').forEach(function (o) { o.remove(); });
            }
        }, true);

        window.ViewImage.__patched = true;
    }
    if (window.ViewImage) patch();
    else document.addEventListener('DOMContentLoaded', patch);
    document.addEventListener('pjax:complete', patch);
})();
</script>
    <?php
}
