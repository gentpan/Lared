<?php
/**
 * Lared 主题设置页面
 *
 * 包含：后台设置页面注册、全部 Tab 渲染、Sanitize 回调、统计代码输出
 *
 * @package Lared
 */

if (!defined('ABSPATH')) {
    exit;
}

// ====================================================================
// Sanitize 回调
// ====================================================================

function lared_sanitize_analytics_code(?string $value): string
{
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }

    $sanitized = wp_kses($value, [
        'script' => [
            'src'              => true,
            'async'            => true,
            'defer'            => true,
            'id'               => true,
            'type'             => true,
            'charset'          => true,
            'crossorigin'      => true,
            'data-website-id'  => true,
            'data-host-url'    => true,
            'data-domains'     => true,
            'data-tag'         => true,
            'data-auto-track'  => true,
            'data-do-not-track'=> true,
            'data-cache'       => true,
        ],
    ]);

    return is_string($sanitized) ? trim($sanitized) : '';
}

// 保留旧名称作为别名以兼容迁移代码
function lared_sanitize_umami_script(?string $value): string
{
    return lared_sanitize_analytics_code($value);
}

function lared_sanitize_ten_year_start_date(?string $value): string
{
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
    if (!$date instanceof DateTimeImmutable) {
        return '';
    }

    return $date->format('Y-m-d');
}

function lared_sanitize_image_url(?string $value): string
{
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }
    return esc_url_raw($value);
}

function lared_sanitize_image_api_url(?string $value): string
{
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }
    return esc_url_raw($value);
}

function lared_sanitize_image_animation(?string $value): string
{
    $value = (string) $value;
    $allowed = ['none', 'fade', 'blur', 'pixelate', 'expand', 'blinds', 'slide', 'rotate'];
    return in_array($value, $allowed, true) ? $value : 'none';
}

function lared_sanitize_analytics_provider(?string $value): string
{
    $value = (string) $value;
    $allowed = ['none', 'google', '51la', 'umami', 'custom'];
    return in_array($value, $allowed, true) ? $value : 'none';
}

function lared_sanitize_music_float_visible($value): string
{
    return '1' === (string) $value ? '1' : '0';
}

// ====================================================================
// 后台设置页面注册
// ====================================================================

function lared_add_theme_settings_page(): void
{
    add_theme_page(
        __('主题设置', 'lared'),
        __('主题设置', 'lared'),
        'manage_options',
        'lared-theme-settings',
        'lared_render_theme_settings_page'
    );
}
add_action('admin_menu', 'lared_add_theme_settings_page');

function lared_register_theme_settings(): void
{
    // 音乐相关设置已迁移到 Xplayer 插件

    // 首页音乐播放器
    register_setting('lared_settings_general', 'lared_music_playlist', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '',
    ]);

    // 内页播放器显示开关
    register_setting('lared_settings_general', 'lared_music_float_visible', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_music_float_visible',
        'default' => '1',
    ]);

    // ── 基础设置 Tab ──
    register_setting('lared_settings_general', 'lared_memos_site_url', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_memos_url',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_memos_api_url', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_memos_url',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_memos_api_token', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_memos_token',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_memos_page_size', [
        'type' => 'integer',
        'sanitize_callback' => 'lared_sanitize_memos_page_size',
        'default' => 20,
    ]);

    // ── 统计代码设置 ──
    register_setting('lared_settings_general', 'lared_analytics_provider', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_analytics_provider',
        'default' => 'none',
    ]);

    register_setting('lared_settings_general', 'lared_ga_measurement_id', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_51la_site_id', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_umami_script_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_umami_website_id', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_analytics_custom_code', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_analytics_code',
        'default' => '',
    ]);

    // 保留旧选项注册以兼容迁移
    register_setting('lared_settings_general', 'lared_umami_script', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_umami_script',
        'default' => '',
    ]);

    register_setting('lared_settings_general', 'lared_ten_year_start_date', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_ten_year_start_date',
        'default' => '',
    ]);

    // ── 图片设置 Tab ──
    register_setting('lared_settings_image', 'lared_default_featured_image', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_image_url',
        'default' => '',
    ]);

    register_setting('lared_settings_image', 'lared_featured_image_api', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_image_api_url',
        'default' => '',
    ]);

    register_setting('lared_settings_image', 'lared_enable_lazyload', [
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => true,
    ]);

    register_setting('lared_settings_image', 'lared_image_load_animation', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_image_animation',
        'default' => 'none',
    ]);

    // WebP 自动转换设置
    register_setting('lared_settings_image', 'lared_webp_auto_convert', [
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ]);
    register_setting('lared_settings_image', 'lared_webp_quality', [
        'type' => 'integer',
        'sanitize_callback' => static function ($val) { return max(1, min(100, (int) $val)); },
        'default' => 85,
    ]);

    // ── AI 摘要 Tab ──
    register_setting('lared_settings_ai', 'lared_ai_provider', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'deepseek',
    ]);
    register_setting('lared_settings_ai', 'lared_ai_api_key', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    register_setting('lared_settings_ai', 'lared_ai_model', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    register_setting('lared_settings_ai', 'lared_ai_custom_endpoint', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ]);
    // 自定义服务商字段
    register_setting('lared_settings_ai', 'lared_ai_custom_name', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    register_setting('lared_settings_ai', 'lared_ai_custom_api_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ]);
    register_setting('lared_settings_ai', 'lared_ai_custom_models', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    register_setting('lared_settings_ai', 'lared_ai_prompt', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '请为以下文章内容生成一段简洁的中文摘要，不超过 150 字，直接输出摘要内容，不要加任何前缀。',
    ]);
}
add_action('admin_init', 'lared_register_theme_settings');

/**
 * 保存设置后重定向回正确的 Tab
 */
function lared_redirect_after_save(string $location): string
{
    // 仅在 options.php 保存后重定向时生效
    if (false === strpos($location, 'settings-updated')) {
        return $location;
    }

    $referer = wp_get_referer();
    if (false === $referer || false === strpos($referer, 'lared-theme-settings')) {
        return $location;
    }

    // 从 referer 中提取 tab 参数
    $parsed = wp_parse_url($referer);
    $query  = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $query);
    }
    $tab = isset($query['tab']) ? sanitize_key($query['tab']) : 'general';

    return add_query_arg(
        ['page' => 'lared-theme-settings', 'tab' => $tab, 'settings-updated' => 'true'],
        admin_url('themes.php')
    );
}
add_filter('wp_redirect', 'lared_redirect_after_save');

// ====================================================================
// 主设置页面渲染
// ====================================================================

function lared_render_theme_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $active_tab = isset($_GET['tab']) ? sanitize_key((string) $_GET['tab']) : 'general';
    $tabs = [
        'general' => __('基础设置', 'lared'),
        'about'   => __('关于页面', 'lared'),
        'image'   => __('图片设置', 'lared'),
        'rss'     => __('RSS 缓存', 'lared'),
        'ai'      => __('AI 摘要', 'lared'),
        'email'   => __('邮件设置', 'lared'),
        'data'    => __('数据维护', 'lared'),
    ];

    $rss_cache_status = isset($_GET['lared_rss_cache']) ? sanitize_key((string) $_GET['lared_rss_cache']) : '';
    $rss_cache_removed = isset($_GET['lared_rss_removed']) ? max(0, (int) $_GET['lared_rss_removed']) : 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('主题设置', 'lared'); ?></h1>

        <?php if (isset($_GET['settings-updated']) && 'true' === $_GET['settings-updated']) : ?>
            <div id="lared-toast" style="position:fixed;top:42px;right:20px;z-index:99999;background:#00a32a;color:#fff;padding:10px 20px;font-size:14px;line-height:1.5;box-shadow:0 4px 16px rgba(0,0,0,.15);opacity:0;transform:translateX(40px);transition:opacity .3s,transform .3s;">
                ✓ <?php esc_html_e('设置已保存', 'lared'); ?>
            </div>
            <script>
            (function(){
                var t = document.getElementById('lared-toast');
                if (!t) return;
                // 清除 URL 中的 settings-updated 参数
                if (window.history && window.history.replaceState) {
                    var url = new URL(window.location);
                    url.searchParams.delete('settings-updated');
                    window.history.replaceState({}, '', url);
                }
                requestAnimationFrame(function(){
                    t.style.opacity = '1';
                    t.style.transform = 'translateX(0)';
                });
                setTimeout(function(){
                    t.style.opacity = '0';
                    t.style.transform = 'translateX(40px)';
                    setTimeout(function(){ t.remove(); }, 350);
                }, 3000);
            })();
            </script>
        <?php endif; ?>

        <?php if ('cleared' === $rss_cache_status) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html(sprintf(__('RSS 缓存已刷新，清理 %d 个缓存文件。', 'lared'), $rss_cache_removed)); ?></p></div>
        <?php elseif ('failed' === $rss_cache_status) : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('RSS 缓存刷新失败，请检查 data/rss 目录权限。', 'lared'); ?></p></div>
        <?php endif; ?>

        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab_label) : ?>
                <a href="<?php echo esc_url(add_query_arg(['page' => 'lared-theme-settings', 'tab' => $tab_key], admin_url('themes.php'))); ?>"
                   class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab_label); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php
        switch ($active_tab) {
            case 'about':
                lared_render_tab_about();
                break;
            case 'image':
                lared_render_tab_image();
                break;
            case 'rss':
                lared_render_tab_rss();
                break;
            case 'ai':
                lared_render_tab_ai();
                break;
            case 'email':
                lared_render_tab_email();
                break;
            case 'data':
                lared_render_tab_data();
                break;
            default:
                lared_render_tab_general();
                break;
        }
        ?>
    </div>
    <?php
}

// ====================================================================
// Tab: 基础设置
// ====================================================================

function lared_render_tab_general(): void
{
    $memos_site_url    = (string) get_option('lared_memos_site_url', '');
    $memos_api_url     = (string) get_option('lared_memos_api_url', lared_get_memos_api_url());
    $memos_api_token   = (string) get_option('lared_memos_api_token', '');
    $memos_page_size   = (int) get_option('lared_memos_page_size', 20);
    $ten_year_start    = (string) get_option('lared_ten_year_start_date', '');
    $ten_year_default  = lared_get_first_post_date_ymd();
    $music_playlist    = (string) get_option('lared_music_playlist', '');
    $music_float_visible = '1' === (string) get_option('lared_music_float_visible', '1') ? '1' : '0';

    // 统计代码
    $analytics_provider    = (string) get_option('lared_analytics_provider', 'none');
    $ga_measurement_id     = (string) get_option('lared_ga_measurement_id', '');
    $la51_site_id          = (string) get_option('lared_51la_site_id', '');
    $umami_script_url      = (string) get_option('lared_umami_script_url', '');
    $umami_website_id      = (string) get_option('lared_umami_website_id', '');
    $analytics_custom_code = (string) get_option('lared_analytics_custom_code', '');

    // 自动迁移：如果旧的 lared_umami_script 有值且新的 analytics_provider 还是 none，自动迁移
    if ('none' === $analytics_provider && '' === $umami_script_url) {
        $old_umami = (string) get_option('lared_umami_script', '');
        if ('' !== $old_umami) {
            // 尝试从旧 script 标签中提取 src 和 data-website-id
            if (preg_match('/src=["\']([^"\']+)["\']/', $old_umami, $src_match)) {
                $umami_script_url = $src_match[1];
                update_option('lared_umami_script_url', $umami_script_url);
            }
            if (preg_match('/data-website-id=["\']([^"\']+)["\']/', $old_umami, $id_match)) {
                $umami_website_id = $id_match[1];
                update_option('lared_umami_website_id', $umami_website_id);
            }
            if ('' !== $umami_script_url && '' !== $umami_website_id) {
                $analytics_provider = 'umami';
                update_option('lared_analytics_provider', 'umami');
            }
        }
    }
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('lared_settings_general'); ?>

        <h2><?php esc_html_e('首页音乐播放器', 'lared'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_music_playlist"><?php esc_html_e('音乐列表', 'lared'); ?></label></th>
                <td>
                    <textarea id="lared_music_playlist" name="lared_music_playlist" rows="8" class="large-text code" placeholder="<?php esc_attr_e('每行一首歌，格式：歌曲名 | 音乐文件URL | 歌词文件URL（可选）', 'lared'); ?>"><?php echo esc_textarea($music_playlist); ?></textarea>
                    <p class="description"><?php esc_html_e('每行一首歌，使用 | 分隔。歌词文件为可选的 LRC 格式。例如：晨光 | /uploads/music/morning.mp3 | /uploads/music/morning.lrc', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('内页播放器', 'lared'); ?></th>
                <td>
                    <input type="hidden" name="lared_music_float_visible" value="0" />
                    <label><input type="checkbox" name="lared_music_float_visible" value="1" <?php checked($music_float_visible, '1'); ?> /> <?php esc_html_e('在内页显示浮动播放器和歌词', 'lared'); ?></label>
                    <p class="description"><?php esc_html_e('取消勾选后，内页不显示播放器和歌词面板，但音乐仍正常播放。', 'lared'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('基础设置', 'lared'); ?></h2>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_memos_site_url"><?php esc_html_e('Memos 站点地址', 'lared'); ?></label></th>
                <td>
                    <input id="lared_memos_site_url" name="lared_memos_site_url" type="url" class="large-text code" value="<?php echo esc_attr($memos_site_url); ?>" placeholder="https://memos.example.com" />
                    <p class="description"><?php esc_html_e('用于拼接动态详情链接。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_memos_api_url"><?php esc_html_e('Memos API 地址', 'lared'); ?></label></th>
                <td>
                    <input id="lared_memos_api_url" name="lared_memos_api_url" type="url" class="large-text code" value="<?php echo esc_attr($memos_api_url); ?>" placeholder="https://memos.example.com/api/v1/memos" />
                    <p class="description"><?php esc_html_e('例如：/api/v1/memos；若留空则按"站点地址 + /api/v1/memos"自动推导。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_memos_api_token"><?php esc_html_e('Memos API Token', 'lared'); ?></label></th>
                <td>
                    <input id="lared_memos_api_token" name="lared_memos_api_token" type="text" class="large-text code" value="<?php echo esc_attr($memos_api_token); ?>" placeholder="可选" />
                    <p class="description"><?php esc_html_e('私有实例可填写 Token，会自动附带 Authorization 与 X-Api-Key 头。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_memos_page_size"><?php esc_html_e('Memos 拉取数量', 'lared'); ?></label></th>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <input id="lared_memos_page_size" name="lared_memos_page_size" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr((string) $memos_page_size); ?>" />
                        <button type="button" id="lared-memos-refresh-btn" class="button button-secondary" style="white-space:nowrap;">
                            <span class="dashicons dashicons-update" style="vertical-align:middle;margin-right:2px;font-size:16px;width:16px;height:16px;line-height:16px;"></span><?php esc_html_e('手动拉取更新', 'lared'); ?>
                        </button>
                        <span id="lared-memos-refresh-status" style="font-size:13px;color:#666;"></span>
                    </div>
                    <?php
                    // 显示 Memos 缓存状态
                    $memos_cache = function_exists('lared_get_memos_json_cache') ? lared_get_memos_json_cache() : ['stats' => []];
                    $memos_cached_at = $memos_cache['stats']['cached_at'] ?? '';
                    $memos_count = $memos_cache['stats']['count'] ?? 0;
                    $memos_next = wp_next_scheduled('lared_memos_json_cache_refresh');
                    ?>
                    <p class="description"><?php esc_html_e('每次请求的最大条数，建议 20-50。', 'lared'); ?></p>
                    <p class="description" style="margin-top:4px;">
                        <?php if ($memos_cached_at) : ?>
                            <?php printf(esc_html__('上次更新：%s（UTC）· %d 条动态', 'lared'), esc_html($memos_cached_at), (int) $memos_count); ?>
                        <?php else : ?>
                            <?php esc_html_e('尚无缓存数据', 'lared'); ?>
                        <?php endif; ?>
                        <?php if ($memos_next) : ?>
                            · <?php printf(esc_html__('下次自动更新：%s', 'lared'), esc_html(get_date_from_gmt(gmdate('Y-m-d H:i:s', $memos_next), 'Y-m-d H:i'))); ?>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('网站统计', 'lared'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_analytics_provider"><?php esc_html_e('统计方案', 'lared'); ?></label></th>
                <td>
                    <select id="lared_analytics_provider" name="lared_analytics_provider" class="regular-text">
                        <option value="none" <?php selected($analytics_provider, 'none'); ?>><?php esc_html_e('不启用', 'lared'); ?></option>
                        <option value="google" <?php selected($analytics_provider, 'google'); ?>>Google Analytics (GA4)</option>
                        <option value="51la" <?php selected($analytics_provider, '51la'); ?>>51.LA</option>
                        <option value="umami" <?php selected($analytics_provider, 'umami'); ?>>Umami</option>
                        <option value="custom" <?php selected($analytics_provider, 'custom'); ?>><?php esc_html_e('自定义代码', 'lared'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('选择网站流量统计方案，保存后统计代码自动输出到前台。', 'lared'); ?></p>
                </td>
            </tr>

            <!-- Google Analytics -->
            <tr class="lared-analytics-row lared-analytics-google" style="<?php echo 'google' === $analytics_provider ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_ga_measurement_id"><?php esc_html_e('Measurement ID', 'lared'); ?></label></th>
                <td>
                    <input id="lared_ga_measurement_id" name="lared_ga_measurement_id" type="text" class="regular-text code" value="<?php echo esc_attr($ga_measurement_id); ?>" placeholder="G-XXXXXXXXXX" />
                    <p class="description"><?php esc_html_e('在 Google Analytics 4 中创建数据流后获取的衡量 ID，格式如 G-XXXXXXXXXX。', 'lared'); ?></p>
                </td>
            </tr>

            <!-- 51.LA -->
            <tr class="lared-analytics-row lared-analytics-51la" style="<?php echo '51la' === $analytics_provider ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_51la_site_id"><?php esc_html_e('站点 ID', 'lared'); ?></label></th>
                <td>
                    <input id="lared_51la_site_id" name="lared_51la_site_id" type="text" class="regular-text code" value="<?php echo esc_attr($la51_site_id); ?>" placeholder="如：CxxxxxxxxxxDwq" />
                    <p class="description"><?php esc_html_e('在 51.LA 控制台创建站点后获取的 ID（位于统计代码中的 id 参数）。', 'lared'); ?></p>
                </td>
            </tr>

            <!-- Umami -->
            <tr class="lared-analytics-row lared-analytics-umami" style="<?php echo 'umami' === $analytics_provider ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_umami_script_url"><?php esc_html_e('Script 地址', 'lared'); ?></label></th>
                <td>
                    <input id="lared_umami_script_url" name="lared_umami_script_url" type="url" class="large-text code" value="<?php echo esc_attr($umami_script_url); ?>" placeholder="https://umami.example.com/script.js" />
                    <p class="description"><?php esc_html_e('自建 Umami 实例的 script.js 地址。', 'lared'); ?></p>
                </td>
            </tr>
            <tr class="lared-analytics-row lared-analytics-umami" style="<?php echo 'umami' === $analytics_provider ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_umami_website_id"><?php esc_html_e('Website ID', 'lared'); ?></label></th>
                <td>
                    <input id="lared_umami_website_id" name="lared_umami_website_id" type="text" class="regular-text code" value="<?php echo esc_attr($umami_website_id); ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />
                    <p class="description"><?php esc_html_e('Umami 中对应站点的 Website ID（UUID 格式）。', 'lared'); ?></p>
                </td>
            </tr>

            <!-- 自定义代码 -->
            <tr class="lared-analytics-row lared-analytics-custom" style="<?php echo 'custom' === $analytics_provider ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_analytics_custom_code"><?php esc_html_e('自定义统计代码', 'lared'); ?></label></th>
                <td>
                    <textarea id="lared_analytics_custom_code" name="lared_analytics_custom_code" rows="6" class="large-text code" placeholder='<script src="..." defer></script>'><?php echo esc_textarea($analytics_custom_code); ?></textarea>
                    <p class="description"><?php esc_html_e('粘贴任意统计平台的 script 代码，保存后原样输出到前台 head 中。', 'lared'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('其他', 'lared'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_ten_year_start_date"><?php esc_html_e('博客十年起始日期', 'lared'); ?></label></th>
                <td>
                    <input id="lared_ten_year_start_date" name="lared_ten_year_start_date" type="date" class="regular-text" value="<?php echo esc_attr($ten_year_start); ?>" />
                    <p class="description">
                        <?php
                        printf(
                            esc_html__('留空将默认使用第一篇文章日期（%s）。', 'lared'),
                            '' !== $ten_year_default ? esc_html($ten_year_default) : esc_html__('暂无文章', 'lared')
                        );
                        ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <script>
    (function() {
        // 统计方案切换
        var providerSelect = document.getElementById('lared_analytics_provider');
        if (providerSelect) {
            providerSelect.addEventListener('change', function() {
                var val = this.value;
                document.querySelectorAll('.lared-analytics-row').forEach(function(row) {
                    row.style.display = 'none';
                });
                if (val && val !== 'none') {
                    document.querySelectorAll('.lared-analytics-' + val).forEach(function(row) {
                        row.style.display = '';
                    });
                }
            });
        }

        // Memos 手动拉取
        var btn = document.getElementById('lared-memos-refresh-btn');
        if (!btn) return;
        var statusEl = document.getElementById('lared-memos-refresh-status');
        var icon = btn.querySelector('.dashicons');
        btn.addEventListener('click', function() {
            btn.disabled = true;
            if (icon) icon.style.animation = 'rotation 1s linear infinite';
            statusEl.textContent = '<?php echo esc_js(__('正在拉取…', 'lared')); ?>';
            var fd = new FormData();
            fd.append('action', 'lared_refresh_memos_cache');
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                if (resp && resp.success) {
                    var d = resp.data || {};
                    statusEl.textContent = '<?php echo esc_js(__('更新成功', 'lared')); ?>' + (d.item_count ? '，' + d.item_count + ' <?php echo esc_js(__('条动态', 'lared')); ?>' : '') + (d.cached_at ? '（' + d.cached_at + '）' : '');
                    statusEl.style.color = '#00a32a';
                } else {
                    statusEl.textContent = '<?php echo esc_js(__('更新失败', 'lared')); ?>' + ((resp && resp.data && resp.data.message) ? '：' + resp.data.message : '');
                    statusEl.style.color = '#d63638';
                }
            })
            .catch(function() {
                statusEl.textContent = '<?php echo esc_js(__('网络错误', 'lared')); ?>';
                statusEl.style.color = '#d63638';
            })
            .finally(function() {
                btn.disabled = false;
                if (icon) icon.style.animation = '';
            });
        });
    })();
    </script>
    <style>@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>
    <?php
}

// ====================================================================
// Tab: 图片设置
// ====================================================================

function lared_render_tab_image(): void
{
    $default_featured_image = (string) get_option('lared_default_featured_image', '');
    $featured_image_api     = (string) get_option('lared_featured_image_api', '');
    $enable_lazyload        = (bool) get_option('lared_enable_lazyload', true);
    $image_load_animation   = (string) get_option('lared_image_load_animation', 'none');
    $webp_auto_convert      = (bool) get_option('lared_webp_auto_convert', false);
    $webp_quality           = (int) get_option('lared_webp_quality', 85);
    $gd_ok   = function_exists('imagecreatefromjpeg') && function_exists('imagewebp');
    $imk_ok  = extension_loaded('imagick');
    $can_webp = $gd_ok || $imk_ok;
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('lared_settings_image'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_default_featured_image"><?php esc_html_e('默认特色图片', 'lared'); ?></label></th>
                <td>
                    <input id="lared_default_featured_image" name="lared_default_featured_image" type="url" class="large-text code" value="<?php echo esc_attr($default_featured_image); ?>" placeholder="https://example.com/default-image.jpg" />
                    <p class="description"><?php esc_html_e('设置全站默认特色图片 URL，当文章没有特色图片且内容中无图片时使用。', 'lared'); ?></p>
                    <?php if ('' !== $default_featured_image) : ?>
                        <p class="description">
                            <img src="<?php echo esc_url($default_featured_image); ?>" alt="<?php esc_attr_e('默认特色图片预览', 'lared'); ?>" style="max-width: 200px; max-height: 120px; margin-top: 8px; border-radius: 4px;" />
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_featured_image_api"><?php esc_html_e('特色图片 API', 'lared'); ?></label></th>
                <td>
                    <input id="lared_featured_image_api" name="lared_featured_image_api" type="url" class="large-text code" value="<?php echo esc_attr($featured_image_api); ?>" placeholder="https://api.example.com/random-image" />
                    <p class="description"><?php esc_html_e('输入随机图片 API 地址，系统将从此 API 获取图片作为文章特色图。API 应返回图片 URL 或可直接访问的图片。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('图片懒加载', 'lared'); ?></th>
                <td>
                    <label for="lared_enable_lazyload">
                        <input id="lared_enable_lazyload" name="lared_enable_lazyload" type="checkbox" value="1" <?php checked($enable_lazyload); ?> />
                        <?php esc_html_e('启用图片懒加载', 'lared'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('开启后，页面中的图片将延迟加载，提升页面加载速度。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_image_load_animation"><?php esc_html_e('图片加载动画', 'lared'); ?></label></th>
                <td>
                    <select id="lared_image_load_animation" name="lared_image_load_animation" class="regular-text">
                        <option value="none" <?php selected($image_load_animation, 'none'); ?>><?php esc_html_e('无动画', 'lared'); ?></option>
                        <option value="fade" <?php selected($image_load_animation, 'fade'); ?>><?php esc_html_e('淡入效果', 'lared'); ?></option>
                        <option value="blur" <?php selected($image_load_animation, 'blur'); ?>><?php esc_html_e('模糊淡入', 'lared'); ?></option>
                        <option value="pixelate" <?php selected($image_load_animation, 'pixelate'); ?>><?php esc_html_e('像素化显现', 'lared'); ?></option>
                        <option value="expand" <?php selected($image_load_animation, 'expand'); ?>><?php esc_html_e('扩散效果', 'lared'); ?></option>
                        <option value="blinds" <?php selected($image_load_animation, 'blinds'); ?>><?php esc_html_e('百叶窗效果', 'lared'); ?></option>
                        <option value="slide" <?php selected($image_load_animation, 'slide'); ?>><?php esc_html_e('滑入效果', 'lared'); ?></option>
                        <option value="rotate" <?php selected($image_load_animation, 'rotate'); ?>><?php esc_html_e('旋转缩放', 'lared'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('选择图片加载完成后的展示动画。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('WebP 自动转换', 'lared'); ?></th>
                <td>
                    <label for="lared_webp_auto_convert">
                        <input id="lared_webp_auto_convert" name="lared_webp_auto_convert" type="checkbox" value="1" <?php checked($webp_auto_convert); ?> <?php disabled(!$can_webp); ?> />
                        <?php esc_html_e('上传图片时自动转换为 WebP 格式', 'lared'); ?>
                    </label>
                    <?php if (!$can_webp) : ?>
                        <p class="description" style="color:#d63638;"><?php esc_html_e('当前服务器未安装 GD（webp）或 Imagick 扩展，无法使用 WebP 转换。', 'lared'); ?></p>
                    <?php else : ?>
                        <p class="description">
                            <?php
                            printf(
                                esc_html__('启用后非 WebP 图片（JPG/PNG/BMP/GIF）将自动转换为 WebP。当前可用引擎：%s', 'lared'),
                                esc_html(implode('、', array_filter([$gd_ok ? 'GD' : '', $imk_ok ? 'Imagick' : ''])))
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_webp_quality"><?php esc_html_e('WebP 压缩质量', 'lared'); ?></label></th>
                <td>
                    <input id="lared_webp_quality" name="lared_webp_quality" type="number" min="1" max="100" step="1" class="small-text" value="<?php echo esc_attr((string) $webp_quality); ?>" />
                    <span>%</span>
                    <p class="description"><?php esc_html_e('1-100，值越高质量越好文件越大。推荐 75-90，默认 85。', 'lared'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    <?php
}

// ====================================================================
// Tab: RSS 缓存
// ====================================================================

function lared_render_tab_rss(): void
{
    ?>
    <h2><?php esc_html_e('RSS Cache', 'lared'); ?></h2>
    <p><?php echo esc_html(sprintf(__('缓存目录：%s', 'lared'), lared_get_rss_cache_dir())); ?></p>
    <?php
    // RSS 缓存状态
    $rss_cache_file = function_exists('lared_get_feed_json_cache_file') ? lared_get_feed_json_cache_file() : '';
    $rss_cached_at = '';
    if ('' !== $rss_cache_file && file_exists($rss_cache_file)) {
        $rss_data = json_decode((string) file_get_contents($rss_cache_file), true);
        $rss_cached_at = $rss_data['stats']['cached_at'] ?? '';
    }
    $rss_next = wp_next_scheduled('lared_feed_json_cache_refresh');
    ?>
    <p class="description" style="margin-bottom:12px;">
        <?php if ($rss_cached_at) : ?>
            <?php printf(esc_html__('上次更新：%s（UTC）', 'lared'), esc_html($rss_cached_at)); ?>
        <?php else : ?>
            <?php esc_html_e('尚无缓存数据', 'lared'); ?>
        <?php endif; ?>
        <?php if ($rss_next) : ?>
            · <?php printf(esc_html__('下次自动更新：%s', 'lared'), esc_html(get_date_from_gmt(gmdate('Y-m-d H:i:s', $rss_next), 'Y-m-d H:i'))); ?>
        <?php else : ?>
            · <span style="color:#d63638;"><?php esc_html_e('定时任务未注册（前台访问一次即可激活）', 'lared'); ?></span>
        <?php endif; ?>
    </p>
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0;">
            <input type="hidden" name="action" value="lared_clear_rss_cache" />
            <?php wp_nonce_field('lared_clear_rss_cache_action'); ?>
            <?php submit_button(__('一键清除缓存', 'lared'), 'secondary', 'submit', false); ?>
        </form>
        <button type="button" id="lared-refresh-all-feeds-btn" class="button button-primary">
            <?php esc_html_e('一键刷新全部订阅', 'lared'); ?>
        </button>
    </div>

    <!-- 刷新进度面板 -->
    <div id="lared-feed-refresh-panel" style="display:none;max-width:720px;margin-top:12px;">
        <div style="margin-bottom:8px;">
            <strong id="lared-feed-refresh-status"><?php esc_html_e('准备中…', 'lared'); ?></strong>
            <span id="lared-feed-refresh-counter" style="margin-left:8px;color:#666;"></span>
        </div>
        <div style="background:#e0e0e0;border-radius:4px;height:22px;overflow:hidden;margin-bottom:10px;">
            <div id="lared-feed-refresh-bar" style="background:#2271b1;height:100%;width:0%;transition:width .3s ease;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                <span id="lared-feed-refresh-percent" style="color:#fff;font-size:12px;font-weight:600;"></span>
            </div>
        </div>
        <div id="lared-feed-refresh-log" style="max-height:260px;overflow-y:auto;border:1px solid #ccc;border-radius:4px;padding:8px 12px;background:#f9f9f9;font-size:13px;line-height:1.7;"></div>
        <div id="lared-feed-refresh-errors" style="display:none;margin-top:10px;border:1px solid #d63638;border-radius:4px;padding:8px 12px;background:#fcf0f1;font-size:13px;line-height:1.7;">
            <strong style="color:#d63638;"><?php esc_html_e('以下订阅源拉取失败：', 'lared'); ?></strong>
            <ul id="lared-feed-refresh-error-list" style="margin:6px 0 0 18px;list-style:disc;color:#8b1a1e;"></ul>
        </div>
        <div id="lared-feed-refresh-summary" style="display:none;margin-top:10px;padding:10px 14px;border-radius:4px;background:#edfaef;border:1px solid #00a32a;font-size:13px;">
        </div>
    </div>

    <script>
    (function() {
        var btn = document.getElementById('lared-refresh-all-feeds-btn');
        var panel = document.getElementById('lared-feed-refresh-panel');
        var statusEl = document.getElementById('lared-feed-refresh-status');
        var counterEl = document.getElementById('lared-feed-refresh-counter');
        var bar = document.getElementById('lared-feed-refresh-bar');
        var percentEl = document.getElementById('lared-feed-refresh-percent');
        var logEl = document.getElementById('lared-feed-refresh-log');
        var errorsEl = document.getElementById('lared-feed-refresh-errors');
        var errorListEl = document.getElementById('lared-feed-refresh-error-list');
        var summaryEl = document.getElementById('lared-feed-refresh-summary');
        var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
        var nonce = '<?php echo wp_create_nonce('lared_feed_refresh_nonce'); ?>';

        if (!btn) return;

        function setProgress(current, total) {
            var pct = total > 0 ? Math.round((current / total) * 100) : 0;
            bar.style.width = pct + '%';
            percentEl.textContent = pct + '%';
            counterEl.textContent = current + ' / ' + total;
        }

        function appendLog(text, type) {
            var line = document.createElement('div');
            if (type === 'error') line.style.color = '#d63638';
            else if (type === 'success') line.style.color = '#00a32a';
            else line.style.color = '#50575e';
            line.textContent = text;
            logEl.appendChild(line);
            logEl.scrollTop = logEl.scrollHeight;
        }

        function postAjax(action, extraData) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', nonce);
            if (extraData) {
                for (var k in extraData) fd.append(k, extraData[k]);
            }
            return fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function(r) { return r.json(); });
        }

        btn.addEventListener('click', function() {
            btn.disabled = true;
            btn.textContent = '<?php echo esc_js(__('拉取中…', 'lared')); ?>';
            panel.style.display = 'block';
            logEl.innerHTML = '';
            errorListEl.innerHTML = '';
            errorsEl.style.display = 'none';
            summaryEl.style.display = 'none';
            setProgress(0, 0);
            statusEl.textContent = '<?php echo esc_js(__('正在获取订阅源列表…', 'lared')); ?>';

            postAjax('lared_get_feed_sources')
            .then(function(resp) {
                if (!resp || !resp.success) {
                    var errMsg = (resp && resp.data && resp.data.message) ? resp.data.message : '<?php echo esc_js(__('请求失败，请刷新页面后重试', 'lared')); ?>';
                    statusEl.textContent = errMsg;
                    appendLog(errMsg, 'error');
                    btn.disabled = false;
                    btn.textContent = '<?php echo esc_js(__('一键刷新全部订阅', 'lared')); ?>';
                    return;
                }

                var sources = (resp.data && resp.data.sources) ? resp.data.sources : [];
                if (!sources.length) {
                    statusEl.textContent = '<?php echo esc_js(__('未找到可用的订阅源', 'lared')); ?>';
                    appendLog('<?php echo esc_js(__('提示：请在 WordPress 后台 → 链接 中添加友情链接，并填写 RSS 地址（link_rss 字段）。', 'lared')); ?>', 'error');
                    btn.disabled = false;
                    btn.textContent = '<?php echo esc_js(__('一键刷新全部订阅', 'lared')); ?>';
                    return;
                }

                var total = sources.length;
                var done = 0;
                var allItems = [];
                var allErrors = [];
                var activeSources = 0;

                appendLog('<?php echo esc_js(__('找到 ', 'lared')); ?>' + total + '<?php echo esc_js(__(' 个订阅源，开始逐个拉取…', 'lared')); ?>', 'info');
                setProgress(0, total);

                function fetchNext(idx) {
                    if (idx >= total) {
                        statusEl.textContent = '<?php echo esc_js(__('正在保存缓存…', 'lared')); ?>';
                        appendLog('<?php echo esc_js(__('全部拉取完成，正在写入缓存…', 'lared')); ?>', 'info');

                        var saveData = {
                            items: allItems,
                            errors: allErrors,
                            source_count: total,
                            active_source_count: activeSources
                        };

                        postAjax('lared_save_feed_cache', { feed_data: JSON.stringify(saveData) })
                        .then(function(saveResp) {
                            if (saveResp && saveResp.success) {
                                var msg = '<?php echo esc_js(__('刷新完成！共拉取 ', 'lared')); ?>' + allItems.length + '<?php echo esc_js(__(' 条内容，来自 ', 'lared')); ?>' + activeSources + '<?php echo esc_js(__(' 个有效源', 'lared')); ?>';
                                if (allErrors.length > 0) msg += '<?php echo esc_js(__('，', 'lared')); ?>' + allErrors.length + '<?php echo esc_js(__(' 个源拉取失败已跳过', 'lared')); ?>';
                                msg += '<?php echo esc_js(__('。', 'lared')); ?>';
                                statusEl.textContent = '<?php echo esc_js(__('完成', 'lared')); ?>';
                                summaryEl.textContent = msg;
                                summaryEl.style.display = 'block';
                                appendLog(msg, 'success');
                            } else {
                                var saveErr = (saveResp && saveResp.data && saveResp.data.message) ? saveResp.data.message : '<?php echo esc_js(__('未知错误', 'lared')); ?>';
                                statusEl.textContent = '<?php echo esc_js(__('保存失败', 'lared')); ?>';
                                appendLog('<?php echo esc_js(__('保存缓存失败：', 'lared')); ?>' + saveErr, 'error');
                            }
                        })
                        .catch(function(err) {
                            statusEl.textContent = '<?php echo esc_js(__('保存出错', 'lared')); ?>';
                            appendLog('<?php echo esc_js(__('保存缓存时出错：', 'lared')); ?>' + err.message, 'error');
                        })
                        .finally(function() {
                            btn.disabled = false;
                            btn.textContent = '<?php echo esc_js(__('一键刷新全部订阅', 'lared')); ?>';
                            if (allErrors.length > 0) {
                                errorsEl.style.display = 'block';
                                allErrors.forEach(function(e) {
                                    var li = document.createElement('li');
                                    li.textContent = e.site + '：' + e.message;
                                    errorListEl.appendChild(li);
                                });
                            }
                        });
                        return;
                    }

                    var src = sources[idx];
                    statusEl.textContent = '<?php echo esc_js(__('正在拉取：', 'lared')); ?>' + src.name;
                    appendLog('[' + (idx + 1) + '/' + total + '] <?php echo esc_js(__('拉取', 'lared')); ?> ' + src.name + '…', 'info');

                    postAjax('lared_fetch_single_feed', { source_index: idx })
                    .then(function(feedResp) {
                        done++;
                        setProgress(done, total);
                        if (feedResp && feedResp.success && feedResp.data && feedResp.data.status === 'ok') {
                            var count = feedResp.data.items ? feedResp.data.items.length : 0;
                            appendLog('  ✓ ' + src.name + ' — ' + count + ' <?php echo esc_js(__('条内容', 'lared')); ?>', 'success');
                            if (feedResp.data.items) allItems = allItems.concat(feedResp.data.items);
                            activeSources++;
                        } else {
                            var errMsg = (feedResp && feedResp.data && feedResp.data.message) ? feedResp.data.message : '<?php echo esc_js(__('未知错误', 'lared')); ?>';
                            appendLog('  ✗ ' + src.name + ' — ' + errMsg + '<?php echo esc_js(__('（已跳过）', 'lared')); ?>', 'error');
                            allErrors.push({ site: src.name, message: errMsg });
                        }
                    })
                    .catch(function(err) {
                        done++;
                        setProgress(done, total);
                        appendLog('  ✗ ' + src.name + ' — ' + (err.message || '<?php echo esc_js(__('网络错误', 'lared')); ?>') + '<?php echo esc_js(__('（已跳过）', 'lared')); ?>', 'error');
                        allErrors.push({ site: src.name, message: err.message || '<?php echo esc_js(__('网络错误', 'lared')); ?>' });
                    })
                    .then(function() {
                        fetchNext(idx + 1);
                    });
                }

                fetchNext(0);
            })
            .catch(function(err) {
                var catchMsg = '<?php echo esc_js(__('获取源列表失败：', 'lared')); ?>' + (err.message || '<?php echo esc_js(__('网络错误或服务器无响应，请刷新页面后重试', 'lared')); ?>');
                statusEl.textContent = catchMsg;
                appendLog(catchMsg, 'error');
                btn.disabled = false;
                btn.textContent = '<?php echo esc_js(__('一键刷新全部订阅', 'lared')); ?>';
            });
        });
    })();
    </script>
    <?php
}

// ====================================================================
// Tab: AI 摘要
// ====================================================================

function lared_render_tab_ai(): void
{
    $providers = lared_ai_providers();
    $current_provider = lared_ai_get_provider();
    $current_model    = lared_ai_get_model();
    $api_key          = lared_ai_get_api_key();
    $custom_endpoint  = lared_ai_get_custom_endpoint();
    $prompt           = lared_ai_get_prompt();
    $ai_nonce         = wp_create_nonce('lared_ai_nonce');

    // 自定义服务商字段
    $custom_name     = (string) get_option('lared_ai_custom_name', '');
    $custom_api_url  = (string) get_option('lared_ai_custom_api_url', '');
    $custom_models   = (string) get_option('lared_ai_custom_models', '');
    $is_custom       = ('custom' === $current_provider);
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('lared_settings_ai'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_ai_provider"><?php esc_html_e('AI 供应商', 'lared'); ?></label></th>
                <td>
                    <select id="lared_ai_provider" name="lared_ai_provider" class="regular-text">
                        <?php foreach ($providers as $key => $p) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_provider, $key); ?>
                                data-models='<?php echo esc_attr(wp_json_encode($p['models'])); ?>'
                                data-default="<?php echo esc_attr($p['default']); ?>">
                                <?php echo esc_html($p['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- 自定义服务商配置 -->
            <tr id="lared-ai-custom-name-row" style="<?php echo $is_custom ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_ai_custom_name"><?php esc_html_e('服务商名称', 'lared'); ?></label></th>
                <td>
                    <input id="lared_ai_custom_name" name="lared_ai_custom_name" type="text" class="regular-text" value="<?php echo esc_attr($custom_name); ?>" placeholder="如：Claude、Gemini" />
                    <p class="description"><?php esc_html_e('自定义服务商的显示名称。', 'lared'); ?></p>
                </td>
            </tr>
            <tr id="lared-ai-custom-url-row" style="<?php echo $is_custom ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_ai_custom_api_url"><?php esc_html_e('API 地址', 'lared'); ?></label></th>
                <td>
                    <input id="lared_ai_custom_api_url" name="lared_ai_custom_api_url" type="url" class="large-text code" value="<?php echo esc_attr($custom_api_url); ?>" placeholder="https://api.example.com/v1/chat/completions" />
                    <p class="description"><?php esc_html_e('需兼容 OpenAI Chat Completions 格式的 API 地址。', 'lared'); ?></p>
                </td>
            </tr>
            <tr id="lared-ai-custom-models-row" style="<?php echo $is_custom ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="lared_ai_custom_models"><?php esc_html_e('可用模型', 'lared'); ?></label></th>
                <td>
                    <input id="lared_ai_custom_models" name="lared_ai_custom_models" type="text" class="large-text code" value="<?php echo esc_attr($custom_models); ?>" placeholder="model-a, model-b, model-c" />
                    <p class="description"><?php esc_html_e('用英文逗号分隔模型名称，第一个为默认模型。', 'lared'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="lared_ai_api_key"><?php esc_html_e('API Key', 'lared'); ?></label></th>
                <td>
                    <input id="lared_ai_api_key" name="lared_ai_api_key" type="password" class="large-text code" value="<?php echo esc_attr($api_key); ?>" autocomplete="off" />
                    <p class="description"><?php esc_html_e('填写所选供应商对应的 API Key。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_ai_model"><?php esc_html_e('模型', 'lared'); ?></label></th>
                <td>
                    <select id="lared_ai_model" name="lared_ai_model" class="regular-text">
                        <?php
                        $models = $providers[$current_provider]['models'] ?? [];
                        foreach ($models as $m) :
                        ?>
                            <option value="<?php echo esc_attr($m); ?>" <?php selected($current_model, $m); ?>><?php echo esc_html($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_ai_custom_endpoint"><?php esc_html_e('自定义 API 端点', 'lared'); ?></label></th>
                <td>
                    <input id="lared_ai_custom_endpoint" name="lared_ai_custom_endpoint" type="url" class="large-text code" value="<?php echo esc_attr($custom_endpoint); ?>" placeholder="留空则使用供应商默认地址" />
                    <p class="description"><?php esc_html_e('高级选项：填写后将覆盖供应商默认 API 地址，用于代理或第三方兼容接口。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_ai_prompt"><?php esc_html_e('系统提示词', 'lared'); ?></label></th>
                <td>
                    <textarea id="lared_ai_prompt" name="lared_ai_prompt" rows="3" class="large-text code"><?php echo esc_textarea($prompt); ?></textarea>
                    <p class="description"><?php esc_html_e('自定义 System Prompt，用于指导 AI 生成摘要的风格和长度。', 'lared'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <hr />

    <h2><?php esc_html_e('功能测试', 'lared'); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><?php esc_html_e('API 连通测试', 'lared'); ?></th>
            <td>
                <button type="button" id="lared-ai-test-btn" class="button button-secondary"><?php esc_html_e('测试连接', 'lared'); ?></button>
                <span id="lared-ai-test-result" style="margin-left:12px;"></span>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('生成摘要测试', 'lared'); ?></th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                    <input id="lared-ai-test-post-id" type="number" class="small-text" placeholder="文章 ID" min="1" />
                    <button type="button" id="lared-ai-generate-btn" class="button button-primary"><?php esc_html_e('生成摘要', 'lared'); ?></button>
                    <label style="margin-left:8px;">
                        <input id="lared-ai-save-check" type="checkbox" checked /> <?php esc_html_e('同时保存到文章', 'lared'); ?>
                    </label>
                </div>
                <div id="lared-ai-generate-result" style="display:none;max-width:720px;margin-top:8px;padding:12px 16px;border-radius:4px;background:#f9f9f9;border:1px solid #ddd;font-size:13px;line-height:1.7;"></div>
            </td>
        </tr>
    </table>

    <hr />

    <h2><?php esc_html_e('批量管理', 'lared'); ?></h2>

    <!-- 统计概览 -->
    <div id="lared-ai-stats" style="max-width:720px;margin-bottom:16px;padding:14px 18px;border-radius:4px;background:#f0f6fc;border:1px solid #c3d1e0;font-size:14px;line-height:1.8;">
        <span style="color:#666;"><?php esc_html_e('加载统计中…', 'lared'); ?></span>
    </div>

    <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;">
        <button type="button" id="lared-ai-batch-gen-btn" class="button button-primary"><?php esc_html_e('一键生成全部摘要', 'lared'); ?></button>
        <button type="button" id="lared-ai-batch-del-btn" class="button button-secondary" style="color:#d63638;border-color:#d63638;"><?php esc_html_e('一键删除全部摘要', 'lared'); ?></button>
        <button type="button" id="lared-ai-refresh-stats-btn" class="button button-secondary"><?php esc_html_e('刷新统计', 'lared'); ?></button>
    </div>

    <!-- 批量进度面板 -->
    <div id="lared-ai-batch-panel" style="display:none;max-width:720px;margin-bottom:16px;">
        <div style="margin-bottom:8px;">
            <strong id="lared-ai-batch-status"><?php esc_html_e('准备中…', 'lared'); ?></strong>
            <span id="lared-ai-batch-counter" style="margin-left:8px;color:#666;"></span>
            <button type="button" id="lared-ai-batch-stop-btn" class="button button-link-delete" style="margin-left:12px;display:none;"><?php esc_html_e('停止', 'lared'); ?></button>
        </div>
        <div style="background:#e0e0e0;border-radius:4px;height:22px;overflow:hidden;margin-bottom:10px;">
            <div id="lared-ai-batch-bar" style="background:#2271b1;height:100%;width:0%;transition:width .3s ease;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                <span id="lared-ai-batch-percent" style="color:#fff;font-size:12px;font-weight:600;"></span>
            </div>
        </div>
        <div id="lared-ai-batch-log" style="max-height:300px;overflow-y:auto;border:1px solid #ccc;border-radius:4px;padding:8px 12px;background:#f9f9f9;font-size:13px;line-height:1.7;"></div>
    </div>

    <!-- 文章列表 -->
    <div id="lared-ai-post-lists" style="display:none;max-width:720px;">
        <h3 style="cursor:pointer;user-select:none;" id="lared-ai-toggle-without">
            <span id="lared-ai-toggle-without-arrow">▶</span> <?php esc_html_e('未生成摘要的文章', 'lared'); ?>
            <span id="lared-ai-without-count" style="color:#d63638;font-weight:normal;font-size:13px;"></span>
        </h3>
        <div id="lared-ai-without-list" style="display:none;max-height:300px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;margin-bottom:16px;"></div>

        <h3 style="cursor:pointer;user-select:none;" id="lared-ai-toggle-with">
            <span id="lared-ai-toggle-with-arrow">▶</span> <?php esc_html_e('已生成摘要的文章', 'lared'); ?>
            <span id="lared-ai-with-count" style="color:#00a32a;font-weight:normal;font-size:13px;"></span>
        </h3>
        <div id="lared-ai-with-list" style="display:none;max-height:300px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;"></div>
    </div>

    <!-- 自定义确认弹窗 -->
    <div id="lared-ai-dialog-overlay" style="display:none;position:fixed;inset:0;z-index:100000;background:rgba(0,0,0,.45);backdrop-filter:blur(2px);transition:opacity .2s;">
        <div id="lared-ai-dialog" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:0;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:28px 32px 22px;min-width:380px;max-width:480px;text-align:center;animation:laredDialogIn .25s ease;">
            <div id="lared-ai-dialog-icon" style="margin-bottom:14px;font-size:36px;"></div>
            <div id="lared-ai-dialog-title" style="font-size:17px;font-weight:700;color:#1d2327;margin-bottom:8px;"></div>
            <div id="lared-ai-dialog-msg" style="font-size:14px;color:#50575e;line-height:1.7;margin-bottom:22px;"></div>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button type="button" id="lared-ai-dialog-cancel" class="button button-secondary" style="min-width:90px;height:36px;font-size:14px;border-radius:0;"><?php esc_html_e('取消', 'lared'); ?></button>
                <button type="button" id="lared-ai-dialog-ok" class="button button-primary" style="min-width:90px;height:36px;font-size:14px;border-radius:0;"><?php esc_html_e('确定', 'lared'); ?></button>
            </div>
        </div>
    </div>
    <style>
        @keyframes laredDialogIn {
            from { opacity: 0; transform: translate(-50%, -50%) scale(.92); }
            to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }
        #lared-ai-dialog-ok.is-danger {
            background: #d63638 !important;
            border-color: #d63638 !important;
            color: #fff !important;
        }
        #lared-ai-dialog-ok.is-danger:hover {
            background: #b32d2e !important;
            border-color: #b32d2e !important;
        }
    </style>

    <script>
    (function(){
        var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
        var nonce   = '<?php echo esc_attr($ai_nonce); ?>';
        var providerSelect = document.getElementById('lared_ai_provider');
        var modelSelect    = document.getElementById('lared_ai_model');

        // ── 自定义弹窗 ──
        var dialogOverlay = document.getElementById('lared-ai-dialog-overlay');
        var dialogIcon    = document.getElementById('lared-ai-dialog-icon');
        var dialogTitle   = document.getElementById('lared-ai-dialog-title');
        var dialogMsg     = document.getElementById('lared-ai-dialog-msg');
        var dialogOk      = document.getElementById('lared-ai-dialog-ok');
        var dialogCancel  = document.getElementById('lared-ai-dialog-cancel');
        var _dialogResolve = null;

        function laredConfirm(opts) {
            return new Promise(function(resolve) {
                _dialogResolve = resolve;
                dialogIcon.textContent = opts.icon || '⚠️';
                dialogTitle.textContent = opts.title || '确认';
                dialogMsg.innerHTML = opts.message || '';
                dialogOk.textContent = opts.okText || '确定';
                dialogCancel.textContent = opts.cancelText || '取消';
                dialogOk.className = 'button button-primary' + (opts.danger ? ' is-danger' : '');
                if (opts.alertOnly) { dialogCancel.style.display = 'none'; } else { dialogCancel.style.display = ''; }
                dialogOverlay.style.display = 'block';
            });
        }
        function closeDialog(val) {
            dialogOverlay.style.display = 'none';
            if (_dialogResolve) { _dialogResolve(val); _dialogResolve = null; }
        }
        dialogOk.addEventListener('click', function() { closeDialog(true); });
        dialogCancel.addEventListener('click', function() { closeDialog(false); });
        dialogOverlay.addEventListener('click', function(e) { if (e.target === dialogOverlay) closeDialog(false); });

        function post(action, extra) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', nonce);
            if (extra) { for (var k in extra) fd.append(k, extra[k]); }
            return fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function(r){ return r.json(); });
        }

        // 切换供应商时更新模型列表 & 自定义服务商字段显示
        if (providerSelect && modelSelect) {
            var customRows = [
                document.getElementById('lared-ai-custom-name-row'),
                document.getElementById('lared-ai-custom-url-row'),
                document.getElementById('lared-ai-custom-models-row')
            ];

            function toggleCustomRows(show) {
                customRows.forEach(function(row) {
                    if (row) row.style.display = show ? '' : 'none';
                });
            }

            providerSelect.addEventListener('change', function() {
                var opt = this.options[this.selectedIndex];
                var isCustom = (this.value === 'custom');
                toggleCustomRows(isCustom);

                var models = JSON.parse(opt.getAttribute('data-models') || '[]');
                var def = opt.getAttribute('data-default') || '';
                modelSelect.innerHTML = '';

                if (isCustom && models.length === 0) {
                    // 自定义服务商且尚无模型，从输入框读取
                    var customModelsInput = document.getElementById('lared_ai_custom_models');
                    if (customModelsInput && customModelsInput.value.trim()) {
                        models = customModelsInput.value.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                        def = models[0] || '';
                    }
                }

                if (models.length === 0) {
                    var placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = isCustom ? '请先填写可用模型' : '无可用模型';
                    modelSelect.appendChild(placeholder);
                } else {
                    models.forEach(function(m) {
                        var o = document.createElement('option');
                        o.value = m; o.textContent = m;
                        if (m === def) o.selected = true;
                        modelSelect.appendChild(o);
                    });
                }
            });

            // 自定义模型输入框变化时同步更新模型下拉
            var customModelsInput = document.getElementById('lared_ai_custom_models');
            if (customModelsInput) {
                customModelsInput.addEventListener('input', function() {
                    if (providerSelect.value !== 'custom') return;
                    var models = this.value.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                    modelSelect.innerHTML = '';
                    if (models.length === 0) {
                        var ph = document.createElement('option');
                        ph.value = ''; ph.textContent = '请先填写可用模型';
                        modelSelect.appendChild(ph);
                    } else {
                        models.forEach(function(m, i) {
                            var o = document.createElement('option');
                            o.value = m; o.textContent = m;
                            if (i === 0) o.selected = true;
                            modelSelect.appendChild(o);
                        });
                    }
                });
            }
        }

        // ── API 连通测试 ──
        var testBtn = document.getElementById('lared-ai-test-btn');
        var testResult = document.getElementById('lared-ai-test-result');
        if (testBtn) {
            testBtn.addEventListener('click', function() {
                testBtn.disabled = true;
                testBtn.textContent = '<?php echo esc_js(__('测试中…', 'lared')); ?>';
                testResult.textContent = '';
                testResult.style.color = '';
                post('lared_ai_test_connection')
                .then(function(d){
                    if (d.success) {
                        testResult.style.color = '#00a32a';
                        testResult.textContent = '✓ ' + d.data.message + ' — ' + d.data.reply;
                    } else {
                        testResult.style.color = '#d63638';
                        testResult.textContent = '✗ ' + (d.data && d.data.message ? d.data.message : '未知错误');
                    }
                })
                .catch(function(e){ testResult.style.color = '#d63638'; testResult.textContent = '✗ 网络错误: ' + e.message; })
                .finally(function(){ testBtn.disabled = false; testBtn.textContent = '<?php echo esc_js(__('测试连接', 'lared')); ?>'; });
            });
        }

        // ── 单篇生成测试 ──
        var genBtn = document.getElementById('lared-ai-generate-btn');
        var genResult = document.getElementById('lared-ai-generate-result');
        var postIdInput = document.getElementById('lared-ai-test-post-id');
        var saveCheck = document.getElementById('lared-ai-save-check');
        if (genBtn) {
            genBtn.addEventListener('click', function() {
                var postId = postIdInput ? postIdInput.value : '';
                if (!postId || parseInt(postId) < 1) { laredConfirm({ icon: 'ℹ️', title: '提示', message: '<?php echo esc_js(__('请输入有效的文章 ID', 'lared')); ?>', okText: '知道了', alertOnly: true }); return; }
                genBtn.disabled = true;
                genBtn.textContent = '<?php echo esc_js(__('生成中…', 'lared')); ?>';
                genResult.style.display = 'none';
                post('lared_ai_generate_summary', { post_id: postId, save: saveCheck && saveCheck.checked ? '1' : '0' })
                .then(function(d){
                    genResult.style.display = 'block';
                    if (d.success) {
                        genResult.style.borderColor = '#00a32a'; genResult.style.background = '#edfaef';
                        var html = '<strong>' + (d.data.title || '') + '</strong><br>' + d.data.summary;
                        html += '<br><small style="color:#666;">模型: ' + (d.data.model||'') + ' · Tokens: ' + (d.data.tokens||0);
                        if (d.data.saved) html += ' · 已保存';
                        html += '</small>';
                        genResult.innerHTML = html;
                    } else {
                        genResult.style.borderColor = '#d63638'; genResult.style.background = '#fcf0f1';
                        genResult.textContent = '✗ ' + (d.data && d.data.message ? d.data.message : '未知错误');
                    }
                })
                .catch(function(e){ genResult.style.display = 'block'; genResult.style.borderColor = '#d63638'; genResult.style.background = '#fcf0f1'; genResult.textContent = '✗ 网络错误: ' + e.message; })
                .finally(function(){ genBtn.disabled = false; genBtn.textContent = '<?php echo esc_js(__('生成摘要', 'lared')); ?>'; });
            });
        }

        // ── 统计 ──
        var statsEl       = document.getElementById('lared-ai-stats');
        var postListsEl   = document.getElementById('lared-ai-post-lists');
        var withoutListEl = document.getElementById('lared-ai-without-list');
        var withListEl    = document.getElementById('lared-ai-with-list');
        var withoutCountEl= document.getElementById('lared-ai-without-count');
        var withCountEl   = document.getElementById('lared-ai-with-count');
        var editBase      = '<?php echo esc_url(admin_url('post.php?action=edit&post=')); ?>';

        function renderPostTable(container, posts, showSummary) {
            if (!posts.length) { container.innerHTML = '<p style="padding:8px 12px;color:#666;margin:0;">无</p>'; return; }
            var html = '<table class="widefat striped" style="margin:0;"><thead><tr><th style="width:60px;">ID</th><th>标题</th>';
            if (showSummary) html += '<th>摘要预览</th>';
            html += '</tr></thead><tbody>';
            posts.forEach(function(p) {
                html += '<tr><td>' + p.id + '</td><td><a href="' + editBase + p.id + '" target="_blank">' + p.title + '</a></td>';
                if (showSummary) html += '<td style="color:#666;font-size:12px;">' + (p.summary || '') + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function loadStats() {
            statsEl.innerHTML = '<span style="color:#666;">加载统计中…</span>';
            post('lared_ai_get_stats')
            .then(function(d){
                if (!d.success) { statsEl.innerHTML = '<span style="color:#d63638;">加载失败</span>'; return; }
                var s = d.data;
                statsEl.innerHTML = '<strong>文章总数：</strong>' + s.total +
                    ' &nbsp;|&nbsp; <strong style="color:#00a32a;">已生成：</strong>' + s.with_summary +
                    ' &nbsp;|&nbsp; <strong style="color:#d63638;">未生成：</strong>' + s.without_summary;
                withoutCountEl.textContent = '(' + s.without_summary + ')';
                withCountEl.textContent = '(' + s.with_summary + ')';
                renderPostTable(withoutListEl, s.without, false);
                renderPostTable(withListEl, s.with, true);
                postListsEl.style.display = 'block';
            })
            .catch(function(){ statsEl.innerHTML = '<span style="color:#d63638;">网络错误</span>'; });
        }
        loadStats();

        // 折叠切换
        function toggle(headerId, arrowId, listId) {
            var h = document.getElementById(headerId);
            var a = document.getElementById(arrowId);
            var l = document.getElementById(listId);
            if (h) h.addEventListener('click', function() {
                var open = l.style.display !== 'none';
                l.style.display = open ? 'none' : 'block';
                a.textContent = open ? '▶' : '▼';
            });
        }
        toggle('lared-ai-toggle-without', 'lared-ai-toggle-without-arrow', 'lared-ai-without-list');
        toggle('lared-ai-toggle-with', 'lared-ai-toggle-with-arrow', 'lared-ai-with-list');

        document.getElementById('lared-ai-refresh-stats-btn').addEventListener('click', loadStats);

        // ── 批量生成 ──
        var batchPanel  = document.getElementById('lared-ai-batch-panel');
        var batchStatus = document.getElementById('lared-ai-batch-status');
        var batchCounter= document.getElementById('lared-ai-batch-counter');
        var batchBar    = document.getElementById('lared-ai-batch-bar');
        var batchPercent= document.getElementById('lared-ai-batch-percent');
        var batchLog    = document.getElementById('lared-ai-batch-log');
        var batchGenBtn = document.getElementById('lared-ai-batch-gen-btn');
        var batchDelBtn = document.getElementById('lared-ai-batch-del-btn');
        var batchStopBtn= document.getElementById('lared-ai-batch-stop-btn');
        var stopFlag    = false;

        function batchProgress(cur, total) {
            var pct = total > 0 ? Math.round((cur / total) * 100) : 0;
            batchBar.style.width = pct + '%';
            batchPercent.textContent = pct + '%';
            batchCounter.textContent = cur + ' / ' + total;
        }

        function batchAppend(text, type) {
            var d = document.createElement('div');
            d.style.color = type === 'error' ? '#d63638' : type === 'success' ? '#00a32a' : '#50575e';
            d.textContent = text;
            batchLog.appendChild(d);
            batchLog.scrollTop = batchLog.scrollHeight;
        }

        batchGenBtn.addEventListener('click', function() {
            laredConfirm({
                icon: '🤖',
                title: '一键生成全部摘要',
                message: '<?php echo esc_js(__('将为所有未生成摘要的文章调用 AI 生成，可能消耗大量 API 配额。<br>确定继续？', 'lared')); ?>',
                okText: '开始生成'
            }).then(function(ok) { if (!ok) return;
            stopFlag = false;
            batchGenBtn.disabled = true;
            batchDelBtn.disabled = true;
            batchStopBtn.style.display = 'inline-block';
            batchPanel.style.display = 'block';
            batchLog.innerHTML = '';
            batchProgress(0, 0);
            batchStatus.textContent = '<?php echo esc_js(__('正在获取待生成文章列表…', 'lared')); ?>';

            post('lared_ai_get_pending_ids')
            .then(function(resp) {
                if (!resp.success) { batchStatus.textContent = '获取失败'; batchGenBtn.disabled = false; batchDelBtn.disabled = false; batchStopBtn.style.display = 'none'; return; }
                var ids = resp.data.ids;
                if (!ids.length) {
                    batchStatus.textContent = '<?php echo esc_js(__('所有文章均已生成摘要', 'lared')); ?>';
                    batchAppend('无需生成。', 'success');
                    batchGenBtn.disabled = false; batchDelBtn.disabled = false; batchStopBtn.style.display = 'none';
                    return;
                }
                var total = ids.length, done = 0, ok = 0, fail = 0;
                batchAppend('找到 ' + total + ' 篇待生成文章，开始逐篇处理…', 'info');
                batchProgress(0, total);

                function next(i) {
                    if (stopFlag) {
                        batchStatus.textContent = '<?php echo esc_js(__('已停止', 'lared')); ?>';
                        batchAppend('用户手动停止，已完成 ' + done + '/' + total, 'info');
                        batchGenBtn.disabled = false; batchDelBtn.disabled = false; batchStopBtn.style.display = 'none';
                        loadStats();
                        return;
                    }
                    if (i >= total) {
                        batchStatus.textContent = '<?php echo esc_js(__('全部完成', 'lared')); ?>';
                        batchAppend('批量生成完成！成功 ' + ok + '，失败 ' + fail, ok > 0 ? 'success' : 'error');
                        batchGenBtn.disabled = false; batchDelBtn.disabled = false; batchStopBtn.style.display = 'none';
                        loadStats();
                        return;
                    }
                    batchStatus.textContent = '正在生成 [' + (i+1) + '/' + total + ']…';
                    post('lared_ai_generate_summary', { post_id: ids[i], save: '1' })
                    .then(function(d) {
                        done++;
                        batchProgress(done, total);
                        if (d.success) {
                            ok++;
                            batchAppend('  ✓ [' + ids[i] + '] ' + (d.data.title||'') + ' — ' + (d.data.tokens||0) + ' tokens', 'success');
                        } else {
                            fail++;
                            batchAppend('  ✗ [' + ids[i] + '] ' + (d.data && d.data.message ? d.data.message : '未知错误'), 'error');
                        }
                    })
                    .catch(function(e) {
                        done++; fail++;
                        batchProgress(done, total);
                        batchAppend('  ✗ [' + ids[i] + '] 网络错误: ' + e.message, 'error');
                    })
                    .then(function(){ next(i + 1); });
                }
                next(0);
            })
            .catch(function(e) {
                batchStatus.textContent = '网络错误';
                batchAppend('获取列表失败: ' + e.message, 'error');
                batchGenBtn.disabled = false; batchDelBtn.disabled = false; batchStopBtn.style.display = 'none';
            });
            }); // end laredConfirm
        });

        batchStopBtn.addEventListener('click', function() { stopFlag = true; batchStopBtn.disabled = true; batchStopBtn.textContent = '正在停止…'; });

        // ── 删除全部 ──
        batchDelBtn.addEventListener('click', function() {
            laredConfirm({
                icon: '🗑️',
                title: '删除全部摘要',
                message: '<?php echo esc_js(__('确定要删除所有文章的 AI 摘要吗？<br>此操作不可撤销！', 'lared')); ?>',
                okText: '确认删除',
                danger: true
            }).then(function(ok) { if (!ok) return;
            batchDelBtn.disabled = true;
            batchGenBtn.disabled = true;
            batchPanel.style.display = 'block';
            batchLog.innerHTML = '';
            batchStatus.textContent = '<?php echo esc_js(__('正在删除…', 'lared')); ?>';
            batchProgress(0, 0);

            post('lared_ai_delete_all')
            .then(function(d) {
                if (d.success) {
                    batchProgress(1, 1);
                    batchStatus.textContent = '<?php echo esc_js(__('删除完成', 'lared')); ?>';
                    batchAppend('已删除 ' + (d.data.deleted || 0) + ' 条摘要记录，并清空所有文章摘要字段', 'success');
                } else {
                    batchStatus.textContent = '删除失败';
                    batchAppend(d.data && d.data.message ? d.data.message : '未知错误', 'error');
                }
            })
            .catch(function(e) { batchStatus.textContent = '网络错误'; batchAppend(e.message, 'error'); })
            .finally(function() { batchDelBtn.disabled = false; batchGenBtn.disabled = false; loadStats(); });
            }); // end laredConfirm
        });
    })();
    </script>
    <?php
}

// ====================================================================
// RSS 缓存清除处理
// ====================================================================

function lared_handle_clear_rss_cache(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('无权限执行该操作。', 'lared'));
    }

    check_admin_referer('lared_clear_rss_cache_action');

    $result = function_exists('lared_clear_rss_cache_files')
        ? lared_clear_rss_cache_files()
        : ['removed' => 0, 'errors' => 1, 'dir' => ''];

    $status = ((int) ($result['errors'] ?? 1) === 0) ? 'cleared' : 'failed';
    $removed = max(0, (int) ($result['removed'] ?? 0));

    $redirect_url = add_query_arg([
        'page' => 'lared-theme-settings',
        'lared_rss_cache' => $status,
        'lared_rss_removed' => $removed,
    ], admin_url('themes.php'));

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('admin_post_lared_clear_rss_cache', 'lared_handle_clear_rss_cache');

// ====================================================================
// 前台统计代码输出
// ====================================================================

/**
 * 根据后台选择的统计方案输出对应的 script 代码到 <head>
 * 支持：Google Analytics (GA4), 51.LA, Umami, 自定义代码
 */
function lared_output_analytics_script(): void
{
    if (is_admin()) {
        return;
    }

    $provider = (string) get_option('lared_analytics_provider', 'none');

    // 向后兼容：如果新选项未设置，检查旧的 lared_umami_script
    if ('none' === $provider) {
        $old_umami = lared_sanitize_umami_script((string) get_option('lared_umami_script', ''));
        if ('' !== $old_umami) {
            echo $old_umami . "\n";
            return;
        }
        return;
    }

    switch ($provider) {
        case 'google':
            $measurement_id = sanitize_text_field((string) get_option('lared_ga_measurement_id', ''));
            if ('' === $measurement_id) {
                return;
            }
            echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($measurement_id) . '"></script>' . "\n";
            echo "<script>\nwindow.dataLayer = window.dataLayer || [];\nfunction gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', '" . esc_js($measurement_id) . "');\n</script>\n";
            break;

        case '51la':
            $site_id = sanitize_text_field((string) get_option('lared_51la_site_id', ''));
            if ('' === $site_id) {
                return;
            }
            echo '<script charset="UTF-8" id="LA_COLLECT" src="//sdk.51.la/js-sdk-pro.min.js?id=' . esc_attr($site_id) . '&ck=' . esc_attr($site_id) . '"></script>' . "\n";
            break;

        case 'umami':
            $script_url = esc_url((string) get_option('lared_umami_script_url', ''));
            $website_id = sanitize_text_field((string) get_option('lared_umami_website_id', ''));
            if ('' === $script_url || '' === $website_id) {
                return;
            }
            echo '<script defer src="' . $script_url . '" data-website-id="' . esc_attr($website_id) . '"></script>' . "\n";
            break;

        case 'custom':
            $custom_code = lared_sanitize_analytics_code((string) get_option('lared_analytics_custom_code', ''));
            if ('' === $custom_code) {
                return;
            }
            echo $custom_code . "\n";
            break;
    }
}
add_action('wp_head', 'lared_output_analytics_script', 99);

/* ====================================================================
   数据维护 Tab — 数据库检查 + 迁移工具
   ==================================================================== */

function lared_render_tab_data(): void
{
    $nonce = wp_create_nonce('lared_data_maintenance');
    ?>
    <div class="lared-data-maintenance" id="lared-data-maintenance" data-nonce="<?php echo esc_attr($nonce); ?>">
        <p class="description" style="margin:12px 0 20px;font-size:13px;color:#666;">
            检查并修复数据库中的数据一致性问题。执行操作前会自动扫描，请确认后再执行。
        </p>

        <!-- 1. 评论等级缓存 -->
        <div class="lared-dm-card" id="dm-comment-levels">
            <div class="lared-dm-card-header">
                <div>
                    <h3>评论等级缓存</h3>
                    <p class="description">重新计算所有评论者的等级和评论数量（基于数据库实际审核通过的评论数）</p>
                </div>
                <div class="lared-dm-card-actions">
                    <button type="button" class="button" onclick="laredDM.scan('comment_levels')">扫描</button>
                    <button type="button" class="button button-primary" onclick="laredDM.execute('comment_levels')" disabled>重建缓存</button>
                </div>
            </div>
            <div class="lared-dm-card-result" style="display:none;"></div>
        </div>

        <!-- 2. 文章浏览量 -->
        <div class="lared-dm-card" id="dm-post-views">
            <div class="lared-dm-card-header">
                <div>
                    <h3>文章浏览量 (post_views)</h3>
                    <p class="description">检查并初始化缺失的 post_views meta；可合并其他插件（如 WP-PostViews 的 views）的浏览量数据</p>
                </div>
                <div class="lared-dm-card-actions">
                    <button type="button" class="button" onclick="laredDM.scan('post_views')">扫描</button>
                    <button type="button" class="button button-primary" onclick="laredDM.execute('post_views')" disabled>同步</button>
                </div>
            </div>
            <div class="lared-dm-card-result" style="display:none;"></div>
        </div>

        <!-- 3. 评论计数同步 -->
        <div class="lared-dm-card" id="dm-comment-counts">
            <div class="lared-dm-card-header">
                <div>
                    <h3>评论计数同步</h3>
                    <p class="description">将 wp_posts.comment_count 与数据库中实际审核通过的评论数同步</p>
                </div>
                <div class="lared-dm-card-actions">
                    <button type="button" class="button" onclick="laredDM.scan('comment_counts')">扫描</button>
                    <button type="button" class="button button-primary" onclick="laredDM.execute('comment_counts')" disabled>同步</button>
                </div>
            </div>
            <div class="lared-dm-card-result" style="display:none;"></div>
        </div>

        <!-- 4. 文章字数统计 -->
        <div class="lared-dm-card" id="dm-word-count">
            <div class="lared-dm-card-header">
                <div>
                    <h3>文章字数统计 (_word_count)</h3>
                    <p class="description">统计所有已发布文章的字数（中文字符 + 英文单词），存入 _word_count meta</p>
                </div>
                <div class="lared-dm-card-actions">
                    <button type="button" class="button" onclick="laredDM.scan('word_count')">扫描</button>
                    <button type="button" class="button button-primary" onclick="laredDM.execute('word_count')" disabled>更新</button>
                </div>
            </div>
            <div class="lared-dm-card-result" style="display:none;"></div>
        </div>

        <!-- 5. 首页浏览量 -->
        <div class="lared-dm-card" id="dm-home-views">
            <div class="lared-dm-card-header">
                <div>
                    <h3>首页浏览量 (lared_home_views)</h3>
                    <p class="description">查看或重置首页浏览量计数器</p>
                </div>
                <div class="lared-dm-card-actions">
                    <button type="button" class="button" onclick="laredDM.scan('home_views')">查看</button>
                    <button type="button" class="button" onclick="if(confirm('确定要重置首页浏览量为 0 ？')) laredDM.execute('home_views_reset')" style="color:#b32d2e;">重置</button>
                </div>
            </div>
            <div class="lared-dm-card-result" style="display:none;"></div>
        </div>
    </div>

    <style>
    .lared-dm-card {
        background: #fff;
        border: 1px solid #d9d9d9;
        margin-bottom: 12px;
    }
    .lared-dm-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
    }
    .lared-dm-card-header h3 {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 600;
    }
    .lared-dm-card-header .description {
        margin: 0;
        font-size: 12px;
    }
    .lared-dm-card-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
        align-items: center;
        white-space: nowrap;
    }
    .lared-dm-card-result {
        padding: 14px 20px;
        border-top: 1px solid #eee;
        font-size: 13px;
        line-height: 1.6;
        background: #fafafa;
    }
    .lared-dm-card-result table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0;
    }
    .lared-dm-card-result th,
    .lared-dm-card-result td {
        text-align: left;
        padding: 6px 10px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
    }
    .lared-dm-card-result th {
        font-weight: 600;
        background: #f5f5f5;
    }
    .lared-dm-merge-row { display: flex; align-items: center; gap: 8px; margin: 4px 0; }
    .lared-dm-merge-row label { font-size: 13px; cursor: pointer; }
    .lared-dm-tag { display: inline-block; padding: 1px 8px; font-size: 11px; font-weight: 600; }
    .lared-dm-tag--ok { background: #d4edda; color: #155724; }
    .lared-dm-tag--warn { background: #fef3cd; color: #856404; }
    .lared-dm-tag--count { background: #e8f0fe; color: #1a56db; }
    .lared-dm-loading { color: #999; }
    .lared-dm-card-result code { background: #f0f0f0; padding: 1px 5px; font-size: 12px; }
    .lared-dm-card-result input[type="checkbox"] { margin-right: 4px; vertical-align: middle; }
    .lared-dm-loading::after {
        content: '';
        display: inline-block;
        width: 12px; height: 12px;
        border: 2px solid #ddd;
        border-top-color: #666;
        border-radius: 50%;
        animation: lared-loading-spin 1s linear infinite;
        margin-left: 6px;
        vertical-align: middle;
    }
    </style>

    <script>
    (function() {
        var nonce = document.getElementById('lared-data-maintenance').dataset.nonce;
        var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

        window.laredDM = {
            scan: function(type) {
                var card = this._getCard(type);
                var result = card.querySelector('.lared-dm-card-result');
                result.style.display = 'block';
                result.innerHTML = '<span class="lared-dm-loading">扫描中</span>';

                this._ajax('lared_dm_scan', { type: type }, function(data) {
                    result.innerHTML = data.html || '扫描完成';
                    // 启用/禁用执行按钮（根据 checkbox 勾选状态）
                    var execBtn = card.querySelector('.button-primary');
                    var updateBtn = function() {
                        if (!execBtn) return;
                        var anyChecked = card.querySelectorAll('.lared-dm-card-result input[type=checkbox]:checked').length > 0;
                        execBtn.disabled = !anyChecked;
                    };
                    // 初始状态
                    if (execBtn) {
                        execBtn.disabled = !data.can_execute;
                    }
                    // 监听 checkbox 变化
                    result.addEventListener('change', function(e) {
                        if (e.target.type === 'checkbox') updateBtn();
                    });
                }, function(msg) {
                    result.innerHTML = '<span style="color:#b32d2e;">扫描失败: ' + msg + '</span>';
                });
            },

            execute: function(type) {
                var card = this._getCard(type);
                var result = card.querySelector('.lared-dm-card-result');
                result.style.display = 'block';

                // 收集勾选项
                var checks = card.querySelectorAll('input[type=checkbox]:checked');
                var selected = [];
                checks.forEach(function(c) { selected.push(c.value); });

                result.innerHTML = '<span class="lared-dm-loading">执行中</span>';

                this._ajax('lared_dm_execute', { type: type, selected: selected }, function(data) {
                    result.innerHTML = data.html || '执行完成';
                    var execBtn = card.querySelector('.button-primary');
                    if (execBtn) execBtn.disabled = true;
                }, function(msg) {
                    result.innerHTML = '<span style="color:#b32d2e;">执行失败: ' + msg + '</span>';
                });
            },

            _getCard: function(type) {
                var map = {
                    comment_levels: 'dm-comment-levels',
                    post_views: 'dm-post-views',
                    comment_counts: 'dm-comment-counts',
                    word_count: 'dm-word-count',
                    home_views: 'dm-home-views',
                    home_views_reset: 'dm-home-views'
                };
                return document.getElementById(map[type] || ('dm-' + type));
            },

            _ajax: function(action, params, onSuccess, onError) {
                var fd = new FormData();
                fd.append('action', action);
                fd.append('nonce', nonce);
                for (var k in params) {
                    if (Array.isArray(params[k])) {
                        params[k].forEach(function(v) { fd.append(k + '[]', v); });
                    } else {
                        fd.append(k, params[k]);
                    }
                }

                fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(r) {
                        if (r.success) {
                            onSuccess(r.data);
                        } else {
                            onError(r.data && r.data.message ? r.data.message : '未知错误');
                        }
                    })
                    .catch(function(e) { onError(e.message || '网络错误'); });
            }
        };
    })();
    </script>
    <?php
}

/* ────────────────────────────────────────────
 *  AJAX: 数据扫描
 * ──────────────────────────────────────────── */
function lared_ajax_dm_scan(): void
{
    check_ajax_referer('lared_data_maintenance', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足']);
        return;
    }

    global $wpdb;
    $type = sanitize_key($_POST['type'] ?? '');

    switch ($type) {
        case 'comment_levels':
            $cached = get_option('lared_commenter_levels_v2', []);
            $cached_count = is_array($cached) ? count($cached) : 0;

            // 数据库中有评论的邮箱数
            $db_count = (int) $wpdb->get_var(
                "SELECT COUNT(DISTINCT comment_author_email) FROM {$wpdb->comments} WHERE comment_approved = '1' AND comment_author_email != ''"
            );

            $html = '<table><tr><th>项目</th><th>数值</th></tr>'
                . '<tr><td>缓存中的评论者数</td><td>' . $cached_count . '</td></tr>'
                . '<tr><td>数据库中有审核评论的评论者</td><td>' . $db_count . '</td></tr>'
                . '</table>';

            if ($cached_count !== $db_count) {
                $html .= '<p><span class="lared-dm-tag lared-dm-tag--warn">不一致</span> 缓存与数据库不匹配，建议重建缓存</p>';
            } else {
                $html .= '<p><span class="lared-dm-tag lared-dm-tag--ok">一致</span> 缓存数据与数据库吻合</p>';
            }

            wp_send_json_success(['html' => $html, 'can_execute' => true]);
            break;

        case 'post_views':
            // 统计 post_views 情况
            $total_posts = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
            );
            $has_views = (int) $wpdb->get_var(
                "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'post_views'
                 WHERE p.post_status = 'publish' AND p.post_type = 'post'"
            );
            $missing_views = $total_posts - $has_views;

            // 已排除的 key 列表
            $excluded_keys = get_option('lared_dm_excluded_view_keys', []);
            if (!is_array($excluded_keys)) {
                $excluded_keys = [];
            }

            // 动态扫描数据库：找出所有包含 view / views / count 的 meta_key（排除 post_views 本身和 WP 内部 key）
            $other_keys_raw = $wpdb->get_results(
                "SELECT pm.meta_key, COUNT(*) AS cnt, SUM(CAST(pm.meta_value AS UNSIGNED)) AS total_views
                 FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE p.post_status = 'publish' AND p.post_type = 'post'
                   AND pm.meta_key != 'post_views'
                   AND (
                       pm.meta_key LIKE '%view%'
                       OR pm.meta_key LIKE '%views%'
                       OR pm.meta_key LIKE '%count%'
                   )
                   AND pm.meta_key NOT LIKE '\_%edit%'
                   AND pm.meta_key NOT IN ('_edit_lock', '_edit_last', '_wp_page_template', 'comment_count', '_thumbnail_id')
                   AND pm.meta_value REGEXP '^[0-9]+$'
                 GROUP BY pm.meta_key
                 ORDER BY cnt DESC"
            );

            // 分离：排除的 vs 待处理的
            $other_keys = [];
            $excluded_found = [];
            foreach ($other_keys_raw as $row) {
                if (in_array($row->meta_key, $excluded_keys, true)) {
                    $excluded_found[] = $row;
                } else {
                    $other_keys[] = $row;
                }
            }

            // 概览表
            $html = '<table><tr><th>项目</th><th>数值</th></tr>'
                . '<tr><td>已发布文章总数</td><td>' . $total_posts . '</td></tr>'
                . '<tr><td>已有 post_views 的文章</td><td>' . $has_views . '</td></tr>'
                . '<tr><td>缺失 post_views 的文章</td><td>' . ($missing_views > 0 ? '<span class="lared-dm-tag lared-dm-tag--warn">' . $missing_views . '</span>' : '<span class="lared-dm-tag lared-dm-tag--ok">0</span>') . '</td></tr>'
                . '</table>';

            if ($missing_views > 0) {
                $html .= '<div class="lared-dm-merge-row"><label><input type="checkbox" value="init_missing" checked> 为缺失的 ' . $missing_views . ' 篇文章初始化 post_views = 0</label></div>';
            }

            // 可合并 key 列表（带概览统计 + checkbox + 排除按钮）
            $found_keys = [];
            if (!empty($other_keys)) {
                $html .= '<p style="margin:16px 0 6px;font-weight:600;">发现以下浏览量相关 meta key：</p>';
                $html .= '<table><tr><th style="width:40px;">合并</th><th>meta_key</th><th>文章数</th><th>总浏览量</th><th style="width:50px;">排除</th></tr>';
                foreach ($other_keys as $row) {
                    $found_keys[] = $row->meta_key;
                    $html .= '<tr>'
                        . '<td><input type="checkbox" value="merge_' . esc_attr($row->meta_key) . '"></td>'
                        . '<td><code>' . esc_html($row->meta_key) . '</code></td>'
                        . '<td>' . (int) $row->cnt . '</td>'
                        . '<td>' . number_format_i18n((int) $row->total_views) . '</td>'
                        . '<td><input type="checkbox" value="exclude_' . esc_attr($row->meta_key) . '"></td>'
                        . '</tr>';
                }
                $html .= '</table>';
                $html .= '<p class="description" style="margin:4px 0 0;">勾选「合并」将数据合并到 post_views 并删除源 key；勾选「排除」标记为非浏览量 key，下次扫描不再显示</p>';
            }

            // 已排除的 key 列表（可恢复）
            if (!empty($excluded_found)) {
                $html .= '<details style="margin:12px 0;"><summary style="cursor:pointer;color:#666;font-size:13px;">已排除的 key（' . count($excluded_found) . ' 个，点击展开可恢复）</summary>';
                $html .= '<table style="margin-top:6px;"><tr><th>meta_key</th><th>文章数</th><th>总浏览量</th><th style="width:50px;">恢复</th></tr>';
                foreach ($excluded_found as $row) {
                    $html .= '<tr>'
                        . '<td><code>' . esc_html($row->meta_key) . '</code></td>'
                        . '<td>' . (int) $row->cnt . '</td>'
                        . '<td>' . number_format_i18n((int) $row->total_views) . '</td>'
                        . '<td><input type="checkbox" value="unexclude_' . esc_attr($row->meta_key) . '"></td>'
                        . '</tr>';
                }
                $html .= '</table></details>';
            }

            // 逐篇文章对比明细（有差异的前 20 篇）
            if (!empty($found_keys)) {
                $key_selects = [];
                foreach ($found_keys as $fk) {
                    $safe = esc_sql($fk);
                    $alias = 'fk_' . preg_replace('/[^a-z0-9_]/', '', $fk);
                    $key_selects[] = "MAX(CASE WHEN pm2.meta_key = '{$safe}' THEN CAST(pm2.meta_value AS UNSIGNED) END) AS `{$alias}`";
                }
                $key_select_sql = implode(', ', $key_selects);

                $compare_rows = $wpdb->get_results(
                    "SELECT p.ID, p.post_title,
                            COALESCE(pv.meta_value, 0) + 0 AS current_views,
                            {$key_select_sql}
                     FROM {$wpdb->posts} p
                     LEFT JOIN {$wpdb->postmeta} pv ON p.ID = pv.post_id AND pv.meta_key = 'post_views'
                     LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key IN ('" . implode("','", array_map('esc_sql', $found_keys)) . "')
                     WHERE p.post_status = 'publish' AND p.post_type = 'post'
                     GROUP BY p.ID
                     HAVING " . implode(' OR ', array_map(function ($fk) {
                        $alias = 'fk_' . preg_replace('/[^a-z0-9_]/', '', $fk);
                        return "`{$alias}` IS NOT NULL AND `{$alias}` != current_views";
                    }, $found_keys)) . "
                     ORDER BY current_views DESC
                     LIMIT 20"
                );

                if (!empty($compare_rows)) {
                    $html .= '<p style="margin:16px 0 6px;font-weight:600;">数据对比（显示有差异的前 20 篇）：</p>';
                    $html .= '<div style="overflow-x:auto;"><table><tr><th>ID</th><th>标题</th><th>post_views</th>';
                    foreach ($found_keys as $fk) {
                        $html .= '<th><code>' . esc_html($fk) . '</code></th>';
                    }
                    $html .= '<th>差异</th></tr>';

                    foreach ($compare_rows as $row) {
                        $cur = (int) $row->current_views;
                        $max_other = 0;
                        $cells = '';
                        foreach ($found_keys as $fk) {
                            $alias = 'fk_' . preg_replace('/[^a-z0-9_]/', '', $fk);
                            $val = isset($row->$alias) ? (int) $row->$alias : '-';
                            if (is_int($val) && $val > $max_other) {
                                $max_other = $val;
                            }
                            $style = (is_int($val) && $val > $cur) ? ' style="color:#b32d2e;font-weight:600;"' : '';
                            $cells .= '<td' . $style . '>' . (is_int($val) ? number_format_i18n($val) : $val) . '</td>';
                        }
                        $diff = $max_other - $cur;
                        $diff_html = $diff > 0
                            ? '<span class="lared-dm-tag lared-dm-tag--warn">+' . number_format_i18n($diff) . '</span>'
                            : '<span class="lared-dm-tag lared-dm-tag--ok">—</span>';

                        $html .= '<tr><td>' . (int) $row->ID . '</td>'
                            . '<td>' . esc_html(mb_strimwidth($row->post_title, 0, 24, '…')) . '</td>'
                            . '<td><strong>' . number_format_i18n($cur) . '</strong></td>'
                            . $cells
                            . '<td>' . $diff_html . '</td></tr>';
                    }
                    $html .= '</table></div>';
                } else {
                    $html .= '<p style="margin:12px 0;"><span class="lared-dm-tag lared-dm-tag--ok">一致</span> post_views 与其他 key 的数据无差异</p>';
                }
            }

            $can_execute = ($missing_views > 0 || !empty($other_keys));

            if (!$can_execute && empty($found_keys)) {
                $html .= '<p><span class="lared-dm-tag lared-dm-tag--ok">正常</span> 所有文章均已有 post_views，未发现其他浏览量 key</p>';
            }

            wp_send_json_success(['html' => $html, 'can_execute' => $can_execute]);
            break;

        case 'comment_counts':
            // 找出 comment_count 与实际不匹配的文章
            $mismatched = $wpdb->get_results(
                "SELECT p.ID, p.post_title, p.comment_count AS stored_count,
                        COALESCE(real.cnt, 0) AS real_count
                 FROM {$wpdb->posts} p
                 LEFT JOIN (
                     SELECT comment_post_ID, COUNT(*) AS cnt
                     FROM {$wpdb->comments}
                     WHERE comment_approved = '1'
                     GROUP BY comment_post_ID
                 ) real ON p.ID = real.comment_post_ID
                 WHERE p.post_status = 'publish'
                   AND p.post_type = 'post'
                   AND p.comment_count != COALESCE(real.cnt, 0)
                 ORDER BY p.ID DESC
                 LIMIT 50"
            );

            $total_mismatch = (int) $wpdb->get_var(
                "SELECT COUNT(*)
                 FROM {$wpdb->posts} p
                 LEFT JOIN (
                     SELECT comment_post_ID, COUNT(*) AS cnt
                     FROM {$wpdb->comments}
                     WHERE comment_approved = '1'
                     GROUP BY comment_post_ID
                 ) real ON p.ID = real.comment_post_ID
                 WHERE p.post_status = 'publish'
                   AND p.post_type = 'post'
                   AND p.comment_count != COALESCE(real.cnt, 0)"
            );

            if ($total_mismatch === 0) {
                $html = '<p><span class="lared-dm-tag lared-dm-tag--ok">一致</span> 所有文章的 comment_count 与数据库一致</p>';
                wp_send_json_success(['html' => $html, 'can_execute' => false]);
                return;
            }

            $html = '<p><span class="lared-dm-tag lared-dm-tag--warn">发现 ' . $total_mismatch . ' 篇文章</span> 评论计数与数据库不一致</p>';
            $html .= '<table><tr><th>ID</th><th>标题</th><th>当前计数</th><th>实际数量</th></tr>';
            foreach (array_slice($mismatched, 0, 10) as $row) {
                $html .= '<tr><td>' . (int) $row->ID . '</td><td>' . esc_html(mb_strimwidth($row->post_title, 0, 30, '…')) . '</td>'
                    . '<td>' . (int) $row->stored_count . '</td><td>' . (int) $row->real_count . '</td></tr>';
            }
            $html .= '</table>';
            if ($total_mismatch > 10) {
                $html .= '<p style="color:#999;font-size:12px;">…还有 ' . ($total_mismatch - 10) . ' 篇</p>';
            }

            wp_send_json_success(['html' => $html, 'can_execute' => true]);
            break;

        case 'word_count':
            $total_posts = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
            );
            $has_wc = (int) $wpdb->get_var(
                "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_word_count'
                 WHERE p.post_status = 'publish' AND p.post_type = 'post'"
            );
            $missing_wc = $total_posts - $has_wc;

            $total_words = (int) $wpdb->get_var(
                "SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0)
                 FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE pm.meta_key = '_word_count'
                   AND p.post_status = 'publish' AND p.post_type = 'post'"
            );

            $html = '<table><tr><th>项目</th><th>数值</th></tr>'
                . '<tr><td>已发布文章总数</td><td>' . $total_posts . '</td></tr>'
                . '<tr><td>已有字数统计的文章</td><td>' . $has_wc . '</td></tr>'
                . '<tr><td>缺失字数统计的文章</td><td>' . ($missing_wc > 0 ? '<span class="lared-dm-tag lared-dm-tag--warn">' . $missing_wc . '</span>' : '<span class="lared-dm-tag lared-dm-tag--ok">0</span>') . '</td></tr>'
                . '<tr><td>全站总字数</td><td><strong>' . number_format_i18n($total_words) . '</strong></td></tr>'
                . '</table>';

            // 显示字数最多和最少的文章
            if ($has_wc > 0) {
                $top5 = $wpdb->get_results(
                    "SELECT p.ID, p.post_title, CAST(pm.meta_value AS UNSIGNED) AS wc
                     FROM {$wpdb->posts} p
                     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_word_count'
                     WHERE p.post_status = 'publish' AND p.post_type = 'post'
                     ORDER BY wc DESC LIMIT 5"
                );
                if (!empty($top5)) {
                    $html .= '<p style="margin:12px 0 6px;font-weight:600;">字数最多的 5 篇文章：</p>';
                    $html .= '<table><tr><th>ID</th><th>标题</th><th>字数</th></tr>';
                    foreach ($top5 as $row) {
                        $html .= '<tr><td>' . (int) $row->ID . '</td>'
                            . '<td>' . esc_html(mb_strimwidth($row->post_title, 0, 30, '…')) . '</td>'
                            . '<td>' . number_format_i18n((int) $row->wc) . '</td></tr>';
                    }
                    $html .= '</table>';
                }
            }

            $can_execute = true; // 始终允许重新统计
            wp_send_json_success(['html' => $html, 'can_execute' => $can_execute]);
            break;

        case 'home_views':
            $home_views = (int) get_option('lared_home_views', 0);
            $html = '<p>当前首页浏览量: <strong>' . number_format_i18n($home_views) . '</strong></p>';
            wp_send_json_success(['html' => $html, 'can_execute' => false]);
            break;

        default:
            wp_send_json_error(['message' => '未知类型']);
    }
}
add_action('wp_ajax_lared_dm_scan', 'lared_ajax_dm_scan');

/* ────────────────────────────────────────────
 *  AJAX: 数据执行
 * ──────────────────────────────────────────── */
function lared_ajax_dm_execute(): void
{
    check_ajax_referer('lared_data_maintenance', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足']);
        return;
    }

    global $wpdb;
    $type     = sanitize_key($_POST['type'] ?? '');
    $selected = isset($_POST['selected']) ? array_map('sanitize_key', (array) $_POST['selected']) : [];

    switch ($type) {
        case 'comment_levels':
            // 重建所有评论者等级缓存
            $emails = $wpdb->get_col(
                "SELECT DISTINCT comment_author_email FROM {$wpdb->comments} WHERE comment_approved = '1' AND comment_author_email != ''"
            );

            $levels = [];
            foreach ($emails as $email) {
                $count = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_author_email = %s AND comment_approved = '1'",
                    $email
                ));
                $level = function_exists('lared_calculate_level') ? lared_calculate_level($count) : 0;
                $levels[sanitize_email($email)] = [
                    'level'      => $level,
                    'count'      => $count,
                    'updated_at' => time(),
                ];
            }

            update_option('lared_commenter_levels_v2', $levels, false);

            $html = '<p><span class="lared-dm-tag lared-dm-tag--ok">完成</span> 已重建 <strong>' . count($levels) . '</strong> 位评论者的等级缓存</p>';
            wp_send_json_success(['html' => $html]);
            break;

        case 'post_views':
            $results = [];

            // 处理排除 / 恢复操作
            $excluded_keys = get_option('lared_dm_excluded_view_keys', []);
            if (!is_array($excluded_keys)) {
                $excluded_keys = [];
            }
            $exclude_changed = false;
            foreach ($selected as $sel) {
                if (0 === strpos($sel, 'exclude_')) {
                    $key_to_exclude = substr($sel, 8);
                    if (!in_array($key_to_exclude, $excluded_keys, true)) {
                        $excluded_keys[] = $key_to_exclude;
                        $exclude_changed = true;
                        $results[] = '已排除 <code>' . esc_html($key_to_exclude) . '</code>，下次扫描不再显示';
                    }
                } elseif (0 === strpos($sel, 'unexclude_')) {
                    $key_to_restore = substr($sel, 10);
                    $idx = array_search($key_to_restore, $excluded_keys, true);
                    if ($idx !== false) {
                        array_splice($excluded_keys, $idx, 1);
                        $exclude_changed = true;
                        $results[] = '已恢复 <code>' . esc_html($key_to_restore) . '</code>，下次扫描将重新显示';
                    }
                }
            }
            if ($exclude_changed) {
                update_option('lared_dm_excluded_view_keys', array_values($excluded_keys), false);
            }

            // 初始化缺失的 post_views
            if (in_array('init_missing', $selected, true)) {
                $missing_ids = $wpdb->get_col(
                    "SELECT p.ID FROM {$wpdb->posts} p
                     LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'post_views'
                     WHERE p.post_status = 'publish' AND p.post_type = 'post' AND pm.meta_id IS NULL"
                );
                foreach ($missing_ids as $pid) {
                    add_post_meta((int) $pid, 'post_views', 0, true);
                }
                $results[] = '初始化了 ' . count($missing_ids) . ' 篇文章的 post_views';
            }

            // 合并其他 meta key
            foreach ($selected as $sel) {
                if (0 !== strpos($sel, 'merge_')) {
                    continue;
                }
                $source_key = substr($sel, 6); // 去掉 merge_ 前缀

                // 安全检查：此 key 必须是数据库中实际存在的、值为纯数字的 postmeta key
                $key_exists = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value REGEXP '^[0-9]+$' LIMIT 1",
                    $source_key
                ));
                if (!$key_exists) {
                    continue;
                }

                // 获取源数据
                $source_data = $wpdb->get_results($wpdb->prepare(
                    "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
                    $source_key
                ));

                $merged = 0;
                foreach ($source_data as $row) {
                    $pid       = (int) $row->post_id;
                    $src_views = (int) $row->meta_value;
                    $cur_views = (int) get_post_meta($pid, 'post_views', true);

                    if ($src_views > $cur_views) {
                        update_post_meta($pid, 'post_views', $src_views);
                        $merged++;
                    }
                }

                // 合并完成后删除源 key
                $deleted = (int) $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
                    $source_key
                ));

                $results[] = '合并 <code>' . esc_html($source_key) . '</code>: ' . count($source_data) . ' 条记录，更新了 ' . $merged . ' 篇，已删除该 key（' . $deleted . ' 条）';
            }

            // 清理对象缓存
            wp_cache_flush();

            $html = '<p><span class="lared-dm-tag lared-dm-tag--ok">完成</span></p><ul style="margin:8px 0;list-style:disc;padding-left:20px;">';
            foreach ($results as $r) {
                $html .= '<li>' . $r . '</li>';
            }
            $html .= '</ul>';

            wp_send_json_success(['html' => $html]);
            break;

        case 'comment_counts':
            // 使用 WordPress 内置函数重新计算
            $updated = 0;
            $post_ids = $wpdb->get_col(
                "SELECT p.ID
                 FROM {$wpdb->posts} p
                 LEFT JOIN (
                     SELECT comment_post_ID, COUNT(*) AS cnt
                     FROM {$wpdb->comments}
                     WHERE comment_approved = '1'
                     GROUP BY comment_post_ID
                 ) real ON p.ID = real.comment_post_ID
                 WHERE p.post_status = 'publish'
                   AND p.post_type = 'post'
                   AND p.comment_count != COALESCE(real.cnt, 0)"
            );

            foreach ($post_ids as $pid) {
                wp_update_comment_count_now((int) $pid);
                $updated++;
            }

            $html = '<p><span class="lared-dm-tag lared-dm-tag--ok">完成</span> 已同步 <strong>' . $updated . '</strong> 篇文章的评论计数</p>';
            wp_send_json_success(['html' => $html]);
            break;

        case 'word_count':
            $posts = $wpdb->get_results(
                "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
            );

            $updated = 0;
            $total_words = 0;
            foreach ($posts as $post) {
                $content_plain = wp_strip_all_tags($post->post_content);
                $cjk_count = (int) preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', $content_plain);
                $latin_words = str_word_count((string) preg_replace('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', ' ', $content_plain));
                $word_count = $cjk_count + $latin_words;

                update_post_meta((int) $post->ID, '_word_count', $word_count);
                $total_words += $word_count;
                $updated++;
            }

            $html = '<p><span class="lared-dm-tag lared-dm-tag--ok">完成</span> 已统计 <strong>' . $updated . '</strong> 篇文章，全站总字数：<strong>' . number_format_i18n($total_words) . '</strong></p>';
            wp_send_json_success(['html' => $html]);
            break;

        case 'home_views_reset':
            update_option('lared_home_views', 0);
            wp_cache_delete('lared_home_views', 'options');
            $html = '<p><span class="lared-dm-tag lared-dm-tag--ok">完成</span> 首页浏览量已重置为 0</p>';
            wp_send_json_success(['html' => $html]);
            break;

        default:
            wp_send_json_error(['message' => '未知操作类型']);
    }
}
add_action('wp_ajax_lared_dm_execute', 'lared_ajax_dm_execute');
