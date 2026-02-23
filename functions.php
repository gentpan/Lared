<?php

if (!defined('ABSPATH')) {
    exit;
}

// ====== CDN 配置常量 ======
// 可在 wp-config.php 中使用 define() 覆盖这些值
if (!defined('PAN_CDN_FONTS')) {
    define('PAN_CDN_FONTS', 'https://fonts.bluecdn.com/css2?family=Noto+Sans+SC:wght@400;500;700;900&display=swap');
}
if (!defined('PAN_CDN_FONTAWESOME')) {
    define('PAN_CDN_FONTAWESOME', 'https://icons.bluecdn.com/fontawesome-pro/css/all.css');
}
if (!defined('PAN_CDN_STATIC')) {
    define('PAN_CDN_STATIC', 'https://static.bluecdn.com/npm');
}
// =========================

require_once get_template_directory() . '/inc/inc-rss.php';
require_once get_template_directory() . '/inc/inc-memos.php';
require_once get_template_directory() . '/inc/inc-code-runner.php';
require_once get_template_directory() . '/inc/inc-download-button.php';
// require_once get_template_directory() . '/inc/inc-comment-levels.php';

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

function lared_primary_menu_fallback(): void
{
    echo '<ul class="nav"><li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('首页', 'lared') . '</a></li></ul>';
}

function lared_disable_page_comments(): void
{
    remove_post_type_support('page', 'comments');
    remove_post_type_support('page', 'trackbacks');
}
add_action('init', 'lared_disable_page_comments');

function lared_force_page_comments_closed(bool $open, int $post_id): bool
{
    if ('page' === get_post_type($post_id)) {
        return false;
    }

    return $open;
}
add_filter('comments_open', 'lared_force_page_comments_closed', 10, 2);
add_filter('pings_open', 'lared_force_page_comments_closed', 10, 2);

// ====== APlayer 播放列表管理 ======

function lared_normalize_aplayer_playlist(array $playlist): array
{
    $normalized = [];

    foreach ($playlist as $track) {
        if (!is_array($track)) {
            continue;
        }

        $name = isset($track['name']) ? sanitize_text_field((string) $track['name']) : '';
        $artist = isset($track['artist']) ? sanitize_text_field((string) $track['artist']) : '';
        $url = isset($track['url']) ? esc_url_raw((string) $track['url']) : '';
        $cover = isset($track['cover']) ? esc_url_raw((string) $track['cover']) : '';
        $lrc = isset($track['lrc']) ? esc_url_raw((string) $track['lrc']) : '';

        if ('' === $url) {
            continue;
        }

        $normalized[] = [
            'name' => '' !== $name ? $name : __('Unknown Title', 'lared'),
            'artist' => '' !== $artist ? $artist : __('Unknown Artist', 'lared'),
            'url' => $url,
            'cover' => $cover,
            'lrc' => $lrc,
        ];
    }

    return $normalized;
}

function lared_get_aplayer_playlist(): array
{
    $default_playlist = [[
        'name' => 'Lared Radio',
        'artist' => get_bloginfo('name'),
        'url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
        'cover' => '',
        'lrc' => '',
    ]];

    $raw_json = (string) get_option('lared_aplayer_playlist_json', '');
    if ('' === trim($raw_json)) {
        return $default_playlist;
    }

    $decoded = json_decode($raw_json, true);
    if (!is_array($decoded)) {
        return $default_playlist;
    }

    $normalized = lared_normalize_aplayer_playlist($decoded);
    return !empty($normalized) ? $normalized : $default_playlist;
}

function lared_sanitize_aplayer_playlist_json(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }

    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        return '';
    }

    $normalized = lared_normalize_aplayer_playlist($decoded);
    if (empty($normalized)) {
        return '';
    }

    return (string) wp_json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

// ====== 音乐歌单 URL 管理 ======

function lared_sanitize_music_playlist_urls(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }

    $lines = preg_split('/\r\n|\r|\n/', $value);
    if (!is_array($lines)) {
        return '';
    }

    $urls = [];
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ('' === $line) {
            continue;
        }

        $url = esc_url_raw($line);
        if ('' !== $url) {
            $urls[] = $url;
            continue;
        }

        if (preg_match('/^[a-zA-Z0-9]+$/', $line)) {
            $urls[] = $line;
        }
    }

    if (empty($urls)) {
        return '';
    }

    return implode("\n", array_values(array_unique($urls)));
}

function lared_get_music_playlist_urls(): array
{
    $raw = (string) get_option('lared_music_playlist_urls', '');
    if ('' === trim($raw)) {
        return [];
    }

    $lines = preg_split('/\r\n|\r|\n/', $raw);
    if (!is_array($lines)) {
        return [];
    }

    $urls = [];
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ('' === $line) {
            continue;
        }

        $url = esc_url_raw($line);
        if ('' !== $url) {
            $urls[] = $url;
            continue;
        }

        if (preg_match('/^[a-zA-Z0-9]+$/', $line)) {
            $urls[] = $line;
        }
    }

    return array_values(array_unique($urls));
}

function lared_parse_music_playlist_url(string $url): ?array
{
    $url = trim($url);
    if ('' === $url) {
        return null;
    }

    if (!str_contains($url, '://') && preg_match('/^[a-zA-Z0-9]+$/', $url)) {
        $server = ctype_digit($url) ? 'netease' : 'tencent';

        return [
            'server' => $server,
            'type' => 'playlist',
            'id' => $url,
            'url' => $url,
        ];
    }

    $parts = wp_parse_url($url);
    if (!is_array($parts)) {
        return null;
    }

    $host = strtolower((string) ($parts['host'] ?? ''));
    $path = (string) ($parts['path'] ?? '');
    $query = [];

    if (!empty($parts['query'])) {
        parse_str((string) $parts['query'], $query);
    }

    $fragment = (string) ($parts['fragment'] ?? '');
    if ('' !== $fragment) {
        if (str_contains($fragment, '?')) {
            [$fragment_path, $fragment_query] = explode('?', $fragment, 2);
            if ('' !== trim($fragment_path)) {
                $path = trim($fragment_path);
            }

            if ('' !== trim($fragment_query)) {
                $fragment_query_data = [];
                parse_str($fragment_query, $fragment_query_data);
                if (is_array($fragment_query_data)) {
                    $query = array_merge($query, $fragment_query_data);
                }
            }
        } elseif (str_starts_with(trim($fragment), '/')) {
            $path = trim($fragment);
        }
    }

    $playlist_id = '';
    $server = '';

    if (str_contains($host, 'music.163.com')) {
        $server = 'netease';
        $playlist_id = isset($query['id']) ? (string) $query['id'] : '';

        if ('' === $playlist_id && preg_match('#/playlist/(\d+)#', $path, $matches)) {
            $playlist_id = (string) $matches[1];
        }
    } elseif (str_contains($host, 'y.qq.com') || str_contains($host, 'qq.com')) {
        $server = 'tencent';
        $playlist_id = isset($query['id']) ? (string) $query['id'] : '';

        if ('' === $playlist_id && preg_match('#/playlist/([a-zA-Z0-9]+)#', $path, $matches)) {
            $playlist_id = (string) $matches[1];
        }
    }

    $playlist_id = trim($playlist_id);
    if ('' === $server || '' === $playlist_id) {
        return null;
    }

    if (!preg_match('/^[a-zA-Z0-9]+$/', $playlist_id)) {
        return null;
    }

    return [
        'server' => $server,
        'type' => 'playlist',
        'id' => $playlist_id,
        'url' => $url,
    ];
}

function lared_get_music_playlist_sources(): array
{
    $urls = lared_get_music_playlist_urls();
    if (empty($urls)) {
        return [];
    }

    $sources = [];
    foreach ($urls as $url) {
        $parsed = lared_parse_music_playlist_url((string) $url);
        if (!is_array($parsed)) {
            continue;
        }

        $source_key = $parsed['server'] . '|' . $parsed['id'];
        $sources[$source_key] = $parsed;
    }

    return array_values($sources);
}

// ====== Meting API 管理 ======

function lared_sanitize_meting_api_template(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }

    $value = esc_url_raw($value);
    if ('' === $value || !str_contains($value, ':server') || !str_contains($value, ':type') || !str_contains($value, ':id')) {
        return '';
    }

    return $value;
}

function lared_get_meting_api_template(): string
{
    $saved = (string) get_option('lared_music_meting_api_template', '');
    $saved = lared_sanitize_meting_api_template($saved);
    if ('' !== $saved) {
        return $saved;
    }

    $local = lared_get_local_meting_api_template();
    if ('' !== $local) {
        return $local;
    }

    return lared_get_external_meting_api_template();
}

function lared_get_external_meting_api_template(): string
{
    return 'https://api.injahow.cn/meting/?server=:server&type=:type&id=:id&r=:r';
}

function lared_get_local_meting_api_template(): string
{
    $local_entry = get_template_directory() . '/assets/music/meting-api-1.2.0/index.php';
    if (!file_exists($local_entry)) {
        return '';
    }

    return trailingslashit(get_template_directory_uri()) . 'assets/music/meting-api-1.2.0/index.php?server=:server&type=:type&id=:id&r=:r';
}

function lared_get_meting_api_fallback_template(?string $primary = null): string
{
    $primary_value = is_string($primary) ? trim($primary) : '';
    $local = lared_get_local_meting_api_template();
    $external = lared_get_external_meting_api_template();

    if ('' !== $local && $primary_value === $local) {
        return $external;
    }

    if ('' !== $local) {
        return $local;
    }

    return $external;
}

// ====== 各种 Sanitize 回调 ======

function lared_sanitize_umami_script(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }

    $sanitized = wp_kses($value, [
        'script' => [
            'src' => true,
            'async' => true,
            'defer' => true,
            'id' => true,
            'type' => true,
            'data-website-id' => true,
            'data-host-url' => true,
            'data-domains' => true,
            'data-tag' => true,
            'data-auto-track' => true,
            'data-do-not-track' => true,
            'data-cache' => true,
        ],
    ]);

    return is_string($sanitized) ? trim($sanitized) : '';
}

function lared_sanitize_ten_year_start_date(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
    if (!$date instanceof DateTimeImmutable) {
        return '';
    }

    return $date->format('Y-m-d');
}

function lared_sanitize_image_url(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }
    return esc_url_raw($value);
}

function lared_sanitize_image_api_url(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }
    return esc_url_raw($value);
}

function lared_sanitize_image_animation(string $value): string
{
    $allowed = ['none', 'fade', 'blur', 'pixelate'];
    return in_array($value, $allowed, true) ? $value : 'none';
}

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

function lared_assets(): void
{
    // CDN 配置（默认从常量，可在 wp-config.php 覆盖）
    $cdn_fonts = PAN_CDN_FONTS;
    $cdn_icons = PAN_CDN_FONTAWESOME;
    $cdn_static = PAN_CDN_STATIC;

    // 字体
    wp_enqueue_style(
        'lared-fonts',
        $cdn_fonts,
        [],
        null
    );

    // Tailwind CSS v4（本地编译版，包含主题所有工具类）
    wp_enqueue_style(
        'lared-tailwind',
        get_template_directory_uri() . '/assets/css/tailwind.css',
        [],
        (string) filemtime(get_template_directory() . '/assets/css/tailwind.css')
    );

    // 主题样式（使用文件修改时间作为版本号，避免浏览器缓存旧样式）
    wp_enqueue_style(
        'lared-style',
        get_stylesheet_uri(),
        ['lared-tailwind'],
        (string) filemtime(get_stylesheet_directory() . '/style.css')
    );

    // Font Awesome Pro
    wp_enqueue_style(
        'lared-fontawesome',
        $cdn_icons,
        [],
        null
    );

    // APlayer CSS
    wp_enqueue_style(
        'lared-aplayer',
        $cdn_static . '/aplayer@1.10.1/dist/APlayer.min.css',
        [],
        '1.10.1'
    );

    // PrismJS Dracula Theme (from prism-themes package)
    wp_enqueue_style(
        'lared-prism-theme',
        $cdn_static . '/prism-themes@1.9.0/themes/prism-dracula.min.css',
        [],
        '1.9.0'
    );

    // Theme JS（使用文件修改时间作为版本号，避免浏览器缓存旧脚本）
    wp_enqueue_script(
        'lared-theme',
        get_template_directory_uri() . '/assets/js/app.js',
        [],
        (string) filemtime(get_template_directory() . '/assets/js/app.js'),
        true
    );

    // PJAX
    wp_enqueue_script(
        'lared-pjax',
        get_template_directory_uri() . '/assets/js/pjax.min.js',
        [],
        '1.0.0',
        true
    );

    // APlayer JS
    wp_enqueue_script(
        'lared-aplayer',
        $cdn_static . '/aplayer@1.10.1/dist/APlayer.min.js',
        [],
        '1.10.1',
        true
    );

    // PrismJS Core
    wp_enqueue_script(
        'lared-prism-core',
        $cdn_static . '/prismjs@1.29.0/components/prism-core.min.js',
        [],
        '1.29.0',
        true
    );

    // PrismJS Autoloader
    wp_enqueue_script(
        'lared-prism-autoloader',
        $cdn_static . '/prismjs@1.29.0/plugins/autoloader/prism-autoloader.js',
        ['lared-prism-core'],
        '1.29.0',
        true
    );

    // ViewImage - lightweight image lightbox
    wp_enqueue_script(
        'lared-view-image',
        get_template_directory_uri() . '/assets/js/view-image.min.js',
        [],
        '1.0.0',
        true
    );

    // WordPress 内置回复脚本（moveForm）
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'lared_assets');

function lared_append_content_link_icon(string $content): string
{
    if (is_admin() || !is_single() && !is_page() && !is_home() && !is_front_page() || '' === trim($content)) {
        return $content;
    }

    $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';

    return preg_replace_callback(
        '/<a([^>]*?)href="([^"]*?)"([^>]*?)>(.*?)<\/a>/i',
        function ($matches) use ($link_icon) {
            $attrs_before = $matches[1];
            $url = $matches[2];
            $attrs_after = $matches[3];
            $text = $matches[4];

            if (
                str_contains(strtolower($url), 'javascript:') ||
                str_contains($url, '#') ||
                str_contains(strtolower($attrs_before . $attrs_after), 'no-arrow') ||
                str_contains(strtolower($attrs_before . $attrs_after), 'dl-button') ||
                str_contains(strtolower($text), '<img')
            ) {
                return $matches[0];
            }

            $all_attrs = $attrs_before . $attrs_after;
            if (!str_contains($all_attrs, 'target="_blank"')) {
                $attrs_after .= ' target="_blank"';
            }
            if (!str_contains($all_attrs, 'rel=')) {
                $attrs_after .= ' rel="noopener noreferrer"';
            }

            return '<a' . $attrs_before . 'href="' . $url . '"' . trim($attrs_after) . '>' . $text . ' <span class="lared-inline-link-icon">' . $link_icon . '</span></a>';
        },
        $content
    ) ?? $content;
}
add_filter('the_content', 'lared_append_content_link_icon', 20);

function lared_add_target_blank_to_content_links(string $content): string
{
    if (is_admin() || '' === trim($content)) {
        return $content;
    }

    $home_url = home_url();
    $content = preg_replace_callback(
        '/<a([^>]+)href="([^"]+)"([^>]*)>([^<]*)<\/a>/i',
        function ($matches) use ($home_url) {
            $before = $matches[1];
            $url = $matches[2];
            $after = $matches[3];
            $text = $matches[4];

            $is_external = !str_starts_with($url, $home_url) && !preg_match('/^(\/|#|javascript:)/i', $url);

            if ($is_external) {
                if (!str_contains($before . $after, 'target=')) {
                    $after .= ' target="_blank"';
                }
                if (!str_contains($before . $after, 'rel=')) {
                    $after .= ' rel="noopener noreferrer"';
                }
            }

            return '<a' . $before . 'href="' . $url . '"' . $after . '>' . $text . '</a>';
        },
        $content
    ) ?? $content;

    return $content;
}
add_filter('the_content', 'lared_add_target_blank_to_content_links', 999);

function lared_archive_per_page(): int
{
    return 18;
}
add_filter('lared_archive_posts_per_page', 'lared_archive_per_page');

function lared_load_aplayer_config(): void
{
    $shared_music_base_path = trailingslashit(get_template_directory_uri() . '/assets/music');

    $config = [
        'playlist' => lared_get_aplayer_playlist(),
        'musicBasePath' => $shared_music_base_path,
        'defaultCover' => $shared_music_base_path . 'img/lizhi.jpg',
        'autoplay' => false,
        'loop' => 'all',
        'order' => 'list',
        'volume' => 0.7,
    ];

    $config = apply_filters('lared_aplayer_config', $config);

    wp_localize_script('lared-theme', 'LaredAPlayerConfig', $config);
}
add_action('wp_enqueue_scripts', 'lared_load_aplayer_config', 20);

/**
 * 为指定查询条件获取 4 种排序文章（最新/热门/热评/随机），每种取 1 篇。
 *
 * @param array $base_args WP_Query 基础参数（可含 tax_query / p 等），无需填写排序/条数。
 * @return array 最多 4 个元素，每个含 post_id/title/image/permalink/type_key/type_label/type_icon。
 */
function lared_hero_fetch_four_articles(array $base_args): array
{
    $type_defs = [
        [
            'key'     => 'latest',
            'label'   => __('最新文章', 'lared'),
            'icon'    => 'fa-solid fa-clock',
            'orderby' => 'date',
            'order'   => 'DESC',
        ],
        [
            'key'      => 'popular',
            'label'    => __('热门文章', 'lared'),
            'icon'     => 'fa-solid fa-fire',
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
            'meta_key' => 'post_views',
        ],
        [
            'key'     => 'comment',
            'label'   => __('热评文章', 'lared'),
            'icon'    => 'fa-solid fa-comments',
            'orderby' => 'comment_count',
            'order'   => 'DESC',
        ],
        [
            'key'     => 'random',
            'label'   => __('随机文章', 'lared'),
            'icon'    => 'fa-solid fa-shuffle',
            'orderby' => 'rand',
            'order'   => 'DESC',
        ],
    ];

    $articles = [];

    foreach ($type_defs as $type) {
        $query = array_merge($base_args, [
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => $type['orderby'],
            'order'          => $type['order'],
        ]);
        if (isset($type['meta_key'])) {
            $query['meta_key'] = $type['meta_key'];
        }

        $posts = get_posts($query);
        if (empty($posts)) {
            continue;
        }

        $pid   = (int) $posts[0]->ID;
        $image = lared_get_post_image_url($pid, 'large');
        if ('' === $image) {
            $image = 'https://picsum.photos/1600/800?random=' . wp_rand(100000, 999999);
        }

        $articles[] = [
            'post_id'    => $pid,
            'title'      => get_the_title($pid),
            'image'      => $image,
            'permalink'  => get_permalink($pid),
            'type_key'   => $type['key'],
            'type_label' => $type['label'],
            'type_icon'  => $type['icon'],
        ];
    }

    return $articles;
}

function lared_get_hero_items(): array
{
    $items = [];

    // ── 第一行：固定"全部"（全站 4 种排序各取一篇）──
    $all_articles = lared_hero_fetch_four_articles([]);
    if (!empty($all_articles)) {
        $all_start     = wp_rand(0, count($all_articles) - 1);
        $all_start_art = $all_articles[$all_start];
        $items[] = [
            'post_id'    => $all_start_art['post_id'],
            'item_url'   => home_url('/'),
            'cat_label'  => __('全部', 'lared'),
            'icon'       => 'fa-regular fa-grid-2',
            'icon_html'  => '<i class="fa-regular fa-grid-2" aria-hidden="true"></i>',
            'count'      => (int) wp_count_posts('post')->publish,
            'type_key'   => $all_start_art['type_key'],
            'type_label' => $all_start_art['type_label'],
            'type_icon'  => $all_start_art['type_icon'],
            'taxonomy'   => '',
            'term_id'    => 0,
            'articles'   => $all_articles,
            'start'      => $all_start,
        ];
    }

    // ── 从 hero_sidebar 菜单读取顶级项（最多 4 个）──
    $locations  = get_nav_menu_locations();
    $menu_items = [];

    if (!empty($locations['hero_sidebar'])) {
        $nav_menu_items = wp_get_nav_menu_items((int) $locations['hero_sidebar']);
        if (is_array($nav_menu_items)) {
            foreach ($nav_menu_items as $nav_item) {
                if ((int) $nav_item->menu_item_parent === 0) {
                    $menu_items[] = $nav_item;
                    if (count($menu_items) >= 4) {
                        break;
                    }
                }
            }
        }
    }

    foreach ($menu_items as $nav_item) {
        $item_url       = esc_url($nav_item->url);
        $item_count     = 0;
        $item_icon_html = '';
        $base_args      = [];

        // 优先从菜单项 CSS 类提取 FA icon
        $nav_fa = lared_extract_fa_classes((array) $nav_item->classes);
        if ('' !== $nav_fa) {
            $item_icon_html = '<i class="' . esc_attr($nav_fa) . '" aria-hidden="true"></i>';
        }

        if (in_array($nav_item->object, ['category', 'post_tag'], true)) {
            $taxonomy = 'category' === $nav_item->object ? 'category' : 'post_tag';
            $term_id  = (int) $nav_item->object_id;
            $term_obj = get_term($term_id, $taxonomy);
            if ($term_obj instanceof WP_Term) {
                $item_count = (int) $term_obj->count;
            }
            // 若菜单 CSS 类没有 icon，降级到 lared_get_category_icon_html
            if ('' === $item_icon_html) {
                $item_icon_html = lared_get_category_icon_html($term_id);
            }
            $base_args = [
                'tax_query' => [[
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ]],
            ];
        } else {
            $resolved = url_to_postid($nav_item->url);
            if ($resolved > 0) {
                $base_args = ['p' => $resolved];
            }
        }

        $articles = lared_hero_fetch_four_articles($base_args);

        // 兜底：若该分类/链接下无结果，降级为全站查询
        if (empty($articles)) {
            $articles = lared_hero_fetch_four_articles([]);
        }

        if (empty($articles)) {
            continue;
        }

        $start     = wp_rand(0, count($articles) - 1);
        $start_art = $articles[$start];

        $hero_taxonomy = '';
        $hero_term_id  = 0;
        if (in_array($nav_item->object, ['category', 'post_tag'], true)) {
            $hero_taxonomy = 'category' === $nav_item->object ? 'category' : 'post_tag';
            $hero_term_id  = (int) $nav_item->object_id;
        }

        $items[] = [
            'post_id'    => $start_art['post_id'],
            'item_url'   => $item_url,
            'cat_label'  => $nav_item->title,
            'icon'       => '',
            'icon_html'  => $item_icon_html,
            'count'      => $item_count,
            'type_key'   => $start_art['type_key'],
            'type_label' => $start_art['type_label'],
            'type_icon'  => $start_art['type_icon'],
            'taxonomy'   => $hero_taxonomy,
            'term_id'    => $hero_term_id,
            'articles'   => $articles,
            'start'      => $start,
        ];
    }

    return $items;
}

function lared_count_unique_approved_commenters(int $post_id): int
{
    $comments = get_comments([
        'post_id' => $post_id,
        'status' => 'approve',
        'type' => 'comment',
    ]);

    $unique = [];
    foreach ($comments as $comment) {
        $key = '';
        if ((int) $comment->user_id > 0) {
            $key = 'user:' . $comment->user_id;
        } elseif ('' !== trim((string) $comment->comment_author_email)) {
            $key = 'email:' . strtolower(trim($comment->comment_author_email));
        } else {
            $key = 'name:' . strtolower(trim((string) $comment->comment_author));
        }
        $unique[$key] = true;
    }

    return count($unique);
}

function lared_ajax_submit_comment(): void
{
    check_ajax_referer('lared_comment_submit', 'nonce');

    $comment_post_id = (int) ($_POST['comment_post_ID'] ?? 0);
    $comment_content = sanitize_textarea_field($_POST['comment'] ?? '');
    $comment_parent = (int) ($_POST['comment_parent'] ?? 0);

    $user = wp_get_current_user();
    $user_id = $user->ID;

    // 已登录用户从账号获取信息，游客从表单获取
    if ($user_id > 0) {
        $comment_author = $user->display_name;
        $comment_author_email = $user->user_email;
        $comment_author_url = $user->user_url;
    } else {
        $comment_author = sanitize_text_field($_POST['author'] ?? '');
        $comment_author_email = sanitize_email($_POST['email'] ?? '');
        $comment_author_url = esc_url_raw($_POST['url'] ?? '');
    }

    if (!$comment_post_id || '' === $comment_content) {
        wp_send_json_error(['message' => __('请填写评论内容。', 'lared')]);
        return;
    }

    if (0 === $user_id && ('' === $comment_author || '' === $comment_author_email)) {
        wp_send_json_error(['message' => __('请填写昵称和邮箱。', 'lared')]);
        return;
    }

    if (0 === $user_id && !is_email($comment_author_email)) {
        wp_send_json_error(['message' => __('请输入有效的邮箱地址。', 'lared')]);
        return;
    }

    $comment_data = [
        'comment_post_ID' => $comment_post_id,
        'comment_author' => $comment_author,
        'comment_author_email' => $comment_author_email,
        'comment_author_url' => $comment_author_url,
        'comment_content' => $comment_content,
        'comment_parent' => $comment_parent,
        'user_id' => $user_id,
    ];

    // 防重复提交：同一作者 + 同一内容 60 秒内不允许重复
    global $wpdb;
    $recent_dup = $wpdb->get_var($wpdb->prepare(
        "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_author_email = %s AND comment_content = %s AND comment_date > %s LIMIT 1",
        $comment_post_id,
        $comment_author_email,
        $comment_content,
        gmdate('Y-m-d H:i:s', time() - 60)
    ));
    if ($recent_dup) {
        wp_send_json_error(['message' => __('请勿重复提交评论。', 'lared')]);
        return;
    }

    $comment_id = wp_new_comment($comment_data, true);

    if (is_wp_error($comment_id)) {
        wp_send_json_error(['message' => $comment_id->get_error_message()]);
        return;
    }

    $comment = get_comment($comment_id);
    $is_approved = (1 === (int) $comment->comment_approved);

    // 为匿名用户强制设置 cookie，确保下次访问能识别为回头访客
    if (0 === $user_id && $comment) {
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));
        $expire = time() + 30 * DAY_IN_SECONDS;
        $path   = defined('COOKIEPATH') ? COOKIEPATH : '/';
        $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
        setcookie('comment_author_' . COOKIEHASH, $comment_author, $expire, $path, $domain, $secure, true);
        setcookie('comment_author_email_' . COOKIEHASH, $comment_author_email, $expire, $path, $domain, $secure, true);
        if (!empty($comment_author_url)) {
            setcookie('comment_author_url_' . COOKIEHASH, $comment_author_url, $expire, $path, $domain, $secure, true);
        }
    }

    $comment_html = '';

    if ($is_approved) {
        ob_start();
        wp_list_comments([
            'style' => 'ol',
            'short_ping' => true,
            'avatar_size' => 44,
            'callback' => 'lared_custom_comment_callback',
        ], [$comment]);
        $comment_html = (string) ob_get_clean();
    }

    $post_id = (int) $comment->comment_post_ID;

    wp_send_json_success([
        'approved' => $is_approved,
        'pending' => !$is_approved,
        'message' => $is_approved
            ? __('评论发布成功。', 'lared')
            : __('评论已提交，审核通过后显示。', 'lared'),
        'html' => $comment_html,
        'parent' => (int) $comment->comment_parent,
        'commentId' => (int) $comment->comment_ID,
        'commentTotal' => (int) get_comments_number($post_id),
        'commenterCount' => lared_count_unique_approved_commenters($post_id),
        'commenterName' => $comment_author,
    ]);
}
add_action('wp_ajax_lared_submit_comment', 'lared_ajax_submit_comment');
add_action('wp_ajax_nopriv_lared_submit_comment', 'lared_ajax_submit_comment');

/**
 * AJAX 编辑评论（60 秒内允许前端编辑自己的评论）
 */
function lared_ajax_edit_comment(): void
{
    check_ajax_referer('lared_comment_edit', 'nonce');

    $comment_id = (int) ($_POST['comment_id'] ?? 0);
    $new_content = sanitize_textarea_field($_POST['comment'] ?? '');

    if (!$comment_id || '' === $new_content) {
        wp_send_json_error(['message' => __('请填写评论内容。', 'lared')]);
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment) {
        wp_send_json_error(['message' => __('评论不存在。', 'lared')]);
        return;
    }

    // 验证身份：通过 IP + User Agent + 作者信息匹配
    $user = wp_get_current_user();
    $is_owner = false;

    if ($user->ID > 0 && (int) $comment->user_id === $user->ID) {
        $is_owner = true;
    } elseif (0 === $user->ID) {
        // 游客：比对 IP + 作者邮箱
        $visitor_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($comment->comment_author_IP === $visitor_ip
            && strtolower(trim($comment->comment_author_email)) === strtolower(trim(sanitize_email($_POST['author_email'] ?? '')))
        ) {
            $is_owner = true;
        }
    }

    if (!$is_owner) {
        wp_send_json_error(['message' => __('无权编辑此评论。', 'lared')]);
        return;
    }

    // 检查是否在 60 秒内
    $comment_time = strtotime($comment->comment_date_gmt);
    if ((time() - $comment_time) > 60) {
        wp_send_json_error(['message' => __('编辑时间已过期（60 秒）。', 'lared')]);
        return;
    }

    // 更新评论内容
    $result = wp_update_comment([
        'comment_ID' => $comment_id,
        'comment_content' => $new_content,
    ]);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
        return;
    }

    // 重新获取更新后的评论并渲染 HTML
    $updated_comment = get_comment($comment_id);
    ob_start();
    wp_list_comments([
        'style' => 'ol',
        'short_ping' => true,
        'avatar_size' => 44,
        'callback' => 'lared_custom_comment_callback',
    ], [$updated_comment]);
    $comment_html = (string) ob_get_clean();

    wp_send_json_success([
        'message' => __('评论已更新。', 'lared'),
        'html' => $comment_html,
        'commentId' => $comment_id,
    ]);
}
add_action('wp_ajax_lared_edit_comment', 'lared_ajax_edit_comment');
add_action('wp_ajax_nopriv_lared_edit_comment', 'lared_ajax_edit_comment');

/**
 * AJAX: Hero 区域随机获取一篇文章（从 4 种排序方式中随机选一种）
 */
function lared_hero_random_article(): void
{
    check_ajax_referer('lared_ajax_nonce', 'nonce');

    $taxonomy = sanitize_text_field(wp_unslash($_POST['taxonomy'] ?? ''));
    $term_id  = (int) ($_POST['term_id'] ?? 0);

    $base_args = [];
    if ('' !== $taxonomy && $term_id > 0) {
        $base_args = [
            'tax_query' => [[
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $term_id,
            ]],
        ];
    }

    // 四种排序方式
    $type_defs = [
        [
            'key'     => 'latest',
            'label'   => __('最新文章', 'lared'),
            'icon'    => 'fa-solid fa-clock',
            'orderby' => 'date',
            'order'   => 'DESC',
        ],
        [
            'key'      => 'popular',
            'label'    => __('热门文章', 'lared'),
            'icon'     => 'fa-solid fa-fire',
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
            'meta_key' => 'post_views',
        ],
        [
            'key'     => 'comment',
            'label'   => __('热评文章', 'lared'),
            'icon'    => 'fa-solid fa-comments',
            'orderby' => 'comment_count',
            'order'   => 'DESC',
        ],
        [
            'key'     => 'random',
            'label'   => __('随机文章', 'lared'),
            'icon'    => 'fa-solid fa-shuffle',
            'orderby' => 'rand',
            'order'   => 'DESC',
        ],
    ];

    // 随机选一种排序方式
    $type = $type_defs[wp_rand(0, count($type_defs) - 1)];

    $query = array_merge($base_args, [
        'posts_per_page' => 1,
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'no_found_rows'  => true,
        'orderby'        => $type['orderby'],
        'order'          => $type['order'],
    ]);
    if (isset($type['meta_key'])) {
        $query['meta_key'] = $type['meta_key'];
    }

    $posts = get_posts($query);

    if (empty($posts)) {
        // 兜底：全站随机
        $posts = get_posts([
            'posts_per_page' => 1,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
        ]);
    }

    if (empty($posts)) {
        wp_send_json_error('no_posts');
    }

    $pid   = (int) $posts[0]->ID;
    $image = lared_get_post_image_url($pid, 'large');
    if ('' === $image) {
        $image = 'https://picsum.photos/1600/800?random=' . wp_rand(100000, 999999);
    }

    wp_send_json_success([
        'title'      => get_the_title($pid),
        'image'      => $image,
        'permalink'  => get_permalink($pid),
        'type_key'   => $type['key'],
        'type_label' => $type['label'],
        'type_icon'  => $type['icon'],
    ]);
}
add_action('wp_ajax_lared_hero_random_article', 'lared_hero_random_article');
add_action('wp_ajax_nopriv_lared_hero_random_article', 'lared_hero_random_article');

/**
 * AJAX 搜索 - 实时搜索文章
 */
function lared_ajax_search(): void
{
    check_ajax_referer('lared_ajax_nonce', 'nonce');

    $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
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
    $avatar_host = 'secure.gravatar.com';
    $sk_options = get_option('wp_starter_kit_options');
    if (!empty($sk_options['cdn_url']) && $sk_options['cdn_url'] !== 'custom') {
        $avatar_host = $sk_options['cdn_url'];
    } elseif (!empty($sk_options['cdn_url']) && $sk_options['cdn_url'] === 'custom' && !empty($sk_options['custom_cdn_url'])) {
        $avatar_host = $sk_options['custom_cdn_url'];
    }

    wp_localize_script('lared-theme', 'LaredAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lared_ajax_nonce'),
        'memosFilterNonce' => wp_create_nonce('lared_memos_filter_nonce'),
        'memosPublishNonce' => wp_create_nonce('lared_memos_publish_nonce'),
        'commentSubmitNonce' => wp_create_nonce('lared_comment_submit'),
        'commentEditNonce' => wp_create_nonce('lared_comment_edit'),
        'levelNonce' => wp_create_nonce('lared_level_nonce'),
        'themeUrl' => get_template_directory_uri(),
        'avatarBaseUrl' => 'https://' . $avatar_host . '/avatar/',
    ]);
}
add_action('wp_enqueue_scripts', 'lared_localize_script', 20);

function lared_get_site_running_days_from_first_post(): int
{
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

    return max(0, $days);
}

function lared_remove_latex_backslashes(string $content): string
{
    if (is_admin() || '' === trim($content)) {
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
        'pan_music_meting_api_template'=> 'lared_music_meting_api_template',
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
        'pan_template_path_migrated_v4'=> 'lared_template_path_migrated_v4',
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
    if ('' === $start_raw) {
        return $empty;
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
    register_setting('lared_theme_settings_group', 'lared_aplayer_playlist_json', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_aplayer_playlist_json',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_music_playlist_urls', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_music_playlist_urls',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_music_meting_api_template', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_meting_api_template',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_memos_site_url', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_memos_url',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_memos_api_url', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_memos_url',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_memos_api_token', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_memos_token',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_memos_page_size', [
        'type' => 'integer',
        'sanitize_callback' => 'lared_sanitize_memos_page_size',
        'default' => 20,
    ]);

    register_setting('lared_theme_settings_group', 'lared_umami_script', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_umami_script',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_ten_year_start_date', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_ten_year_start_date',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_default_featured_image', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_image_url',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_featured_image_api', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_image_api_url',
        'default' => '',
    ]);

    register_setting('lared_theme_settings_group', 'lared_enable_lazyload', [
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => true,
    ]);

    register_setting('lared_theme_settings_group', 'lared_image_load_animation', [
        'type' => 'string',
        'sanitize_callback' => 'lared_sanitize_image_animation',
        'default' => 'none',
    ]);
}
add_action('admin_init', 'lared_register_theme_settings');

function lared_render_theme_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $playlist_json = (string) get_option('lared_aplayer_playlist_json', '');
    $music_playlist_urls = (string) get_option('lared_music_playlist_urls', '');
    $meting_api_template = (string) get_option('lared_music_meting_api_template', lared_get_meting_api_template());
    $memos_site_url = (string) get_option('lared_memos_site_url', '');
    $memos_api_url = (string) get_option('lared_memos_api_url', lared_get_memos_api_url());
    $memos_api_token = (string) get_option('lared_memos_api_token', '');
    $memos_page_size = (int) get_option('lared_memos_page_size', 20);
    $umami_script = (string) get_option('lared_umami_script', '');
    $ten_year_start_date = (string) get_option('lared_ten_year_start_date', '');
    $ten_year_start_default = lared_get_first_post_date_ymd();
    $default_featured_image = (string) get_option('lared_default_featured_image', '');
    $featured_image_api = (string) get_option('lared_featured_image_api', '');
    $enable_lazyload = (bool) get_option('lared_enable_lazyload', true);
    $image_load_animation = (string) get_option('lared_image_load_animation', 'none');
    $rss_cache_status = isset($_GET['lared_rss_cache']) ? sanitize_key((string) $_GET['lared_rss_cache']) : '';
    $rss_cache_removed = isset($_GET['lared_rss_removed']) ? max(0, (int) $_GET['lared_rss_removed']) : 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('主题设置', 'lared'); ?></h1>
        <?php if ('cleared' === $rss_cache_status) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html(sprintf(__('RSS 缓存已刷新，清理 %d 个缓存文件。', 'lared'), $rss_cache_removed)); ?></p></div>
        <?php elseif ('failed' === $rss_cache_status) : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('RSS 缓存刷新失败，请检查 data/rss 目录权限。', 'lared'); ?></p></div>
        <?php endif; ?>
        <p><?php esc_html_e('配置 APlayer 播放列表（JSON 数组）。每一项支持 name、artist、url、cover、lrc。', 'lared'); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields('lared_theme_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="lared_aplayer_playlist_json"><?php esc_html_e('APlayer Playlist JSON', 'lared'); ?></label></th>
                    <td>
                        <textarea id="lared_aplayer_playlist_json" name="lared_aplayer_playlist_json" rows="16" class="large-text code"><?php echo esc_textarea($playlist_json); ?></textarea>
                        <p class="description"><?php esc_html_e('示例：[{"name":"Song A","artist":"Pan","url":"https://example.com/a.mp3","cover":"https://example.com/a.jpg"}]', 'lared'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_music_playlist_urls"><?php esc_html_e('音乐页歌单地址', 'lared'); ?></label></th>
                    <td>
                        <textarea id="lared_music_playlist_urls" name="lared_music_playlist_urls" rows="6" class="large-text code" placeholder="https://music.163.com/#/playlist?id=xxxx&#10;https://y.qq.com/n/ryqq/playlist/xxxx"><?php echo esc_textarea($music_playlist_urls); ?></textarea>
                        <p class="description"><?php esc_html_e('用于"Music Page"独立页面。每行一个歌单地址，支持网易云与 QQ 音乐链接。', 'lared'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_music_meting_api_template"><?php esc_html_e('Meting API 模板', 'lared'); ?></label></th>
                    <td>
                        <input id="lared_music_meting_api_template" name="lared_music_meting_api_template" type="text" class="large-text code" value="<?php echo esc_attr($meting_api_template); ?>" placeholder="<?php echo esc_attr(lared_get_local_meting_api_template() ?: lared_get_external_meting_api_template()); ?>" />
                        <p class="description"><?php esc_html_e('用于解析歌单到可播放链接。留空时自动优先使用主题内本地 meting-api（assets/music/meting-api-1.2.0），失败再走外部兜底。需包含 :server、:type、:id 占位符。', 'lared'); ?></p>
                    </td>
                </tr>
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
                        <input id="lared_memos_page_size" name="lared_memos_page_size" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr((string) $memos_page_size); ?>" />
                        <p class="description"><?php esc_html_e('每次请求的最大条数，建议 20-50。', 'lared'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_umami_script"><?php esc_html_e('Umami 统计代码', 'lared'); ?></label></th>
                    <td>
                        <textarea id="lared_umami_script" name="lared_umami_script" rows="5" class="large-text code" placeholder='<script defer src="https://umami.example.com/script.js" data-website-id="xxxx-xxxx-xxxx-xxxx"></script>'><?php echo esc_textarea($umami_script); ?></textarea>
                        <p class="description"><?php esc_html_e('粘贴 Umami 官方 script 代码，保存后会自动输出到前台 head。', 'lared'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_ten_year_start_date"><?php esc_html_e('博客十年起始日期', 'lared'); ?></label></th>
                    <td>
                        <input id="lared_ten_year_start_date" name="lared_ten_year_start_date" type="date" class="regular-text" value="<?php echo esc_attr($ten_year_start_date); ?>" />
                        <p class="description">
                            <?php
                            printf(
                                esc_html__('留空将默认使用第一篇文章日期（%s）。', 'lared'),
                                '' !== $ten_year_start_default ? esc_html($ten_year_start_default) : esc_html__('暂无文章', 'lared')
                            );
                            ?>
                        </p>
                    </td>
                </tr>
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
                        </select>
                        <p class="description"><?php esc_html_e('选择图片加载时的动画效果。淡入效果柔和自然，像素化显现具有艺术感。', 'lared'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr />
        <h2><?php esc_html_e('RSS Cache', 'lared'); ?></h2>
        <p><?php echo esc_html(sprintf(__('缓存目录：%s', 'lared'), lared_get_rss_cache_dir())); ?></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="lared_clear_rss_cache" />
            <?php wp_nonce_field('lared_clear_rss_cache_action'); ?>
            <?php submit_button(__('手动刷新缓存', 'lared'), 'secondary', 'submit', false); ?>
        </form>
    </div>
    <?php
}

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

/**
 * 读取文章浏览量（post meta: post_views）
 */
function lared_get_post_views(int $post_id): int
{
    return (int) get_post_meta($post_id, 'post_views', true);
}

/**
 * AJAX 自增文章浏览量
 * 由前端 JS 在单篇文章页触发（兼容 PJAX 导航）
 */
function lared_track_post_views_ajax(): void
{
    check_ajax_referer('lared_ajax_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if ($post_id < 1 || 'publish' !== get_post_status($post_id)) {
        wp_send_json_error(['message' => 'Invalid post']);
        return;
    }

    $current = lared_get_post_views($post_id);
    update_post_meta($post_id, 'post_views', $current + 1);

    wp_send_json_success(['tracked' => true, 'views' => $current + 1]);
}
add_action('wp_ajax_lared_track_views', 'lared_track_post_views_ajax');
add_action('wp_ajax_nopriv_lared_track_views', 'lared_track_post_views_ajax');

function lared_get_post_image_url(int $post_id, string $size = 'large'): string
{
    // 1. 优先使用文章特色图片
    if (has_post_thumbnail($post_id)) {
        $image_url = get_the_post_thumbnail_url($post_id, $size);
        if ($image_url) {
            return $image_url;
        }
    }

    // 2. 从文章内容提取图片
    $content = (string) get_post_field('post_content', $post_id);
    if ('' !== trim($content)) {
        if (preg_match('/<img[^>]*>/i', $content, $img_tag_match)) {
            $img_tag = (string) $img_tag_match[0];

            if (preg_match('/wp-image-([0-9]+)/i', $img_tag, $id_match)) {
                $attachment_id = (int) $id_match[1];
                if ($attachment_id > 0) {
                    $image = wp_get_attachment_image_src($attachment_id, $size);
                    if (is_array($image) && isset($image[0]) && '' !== trim((string) $image[0])) {
                        return (string) $image[0];
                    }
                }
            }

            if (preg_match('/src=("|\')(.*?)\1/i', $img_tag, $src_match)) {
                $src = (string) $src_match[2];
                if ('' !== trim($src)) {
                    return $src;
                }
            }
        }
    }

    // 3. 使用 API 获取图片（如果设置了 API）
    $api_url = (string) get_option('lared_featured_image_api', '');
    if ('' !== $api_url) {
        $api_image = lared_get_image_from_api($api_url, $post_id);
        if ('' !== $api_image) {
            return $api_image;
        }
    }

    // 4. 使用主题设置的默认图片
    $default_image = (string) get_option('lared_default_featured_image', '');
    if ('' !== $default_image) {
        return $default_image;
    }

    return '';
}

function lared_get_post_image_html(int $post_id, string $size = 'large', array $attrs = []): string
{
    if (has_post_thumbnail($post_id)) {
        $attr_defaults = ['alt' => get_the_title($post_id)];
        $attr_merged   = array_merge($attr_defaults, $attrs);
        $html = get_the_post_thumbnail($post_id, $size, $attr_merged);
        if ($html) {
            return $html;
        }
    }

    $image_url = lared_get_post_image_url($post_id, $size);
    if ('' !== $image_url) {
        $attr_str = '';
        $merged = array_merge(['alt' => get_the_title($post_id)], $attrs);
        foreach ($merged as $k => $v) {
            $attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }
        return '<img src="' . esc_url($image_url) . '"' . $attr_str . '>';
    }

    return '';
}

function lared_get_category_icon_html(int $cat_id): string
{
    // cat_id = 0 表示"全部"，不对应任何分类，直接返回空
    if ($cat_id <= 0) {
        return '';
    }

    // ── 静态缓存：同一次请求中只扫描一次菜单 ──
    static $icon_map = null;

    if (null === $icon_map) {
        $icon_map = [];

        // 扫描所有已注册的导航菜单
        $locations = get_nav_menu_locations();
        foreach ($locations as $menu_id) {
            if (empty($menu_id)) {
                continue;
            }
            $nav_items = wp_get_nav_menu_items((int) $menu_id);
            if (!is_array($nav_items)) {
                continue;
            }
            foreach ($nav_items as $nav_item) {
                // 只处理分类和标签类型的菜单项
                if (!in_array($nav_item->object, ['category', 'post_tag'], true)) {
                    continue;
                }
                $tid = (int) $nav_item->object_id;
                if ($tid <= 0 || isset($icon_map[$tid])) {
                    continue;
                }
                // 从菜单项的 CSS 类中提取 FontAwesome 类名
                $fa_classes = lared_extract_fa_classes((array) $nav_item->classes);
                if ('' !== $fa_classes) {
                    $icon_map[$tid] = $fa_classes;
                }
            }
        }
    }

    if (empty($icon_map[$cat_id])) {
        return '';
    }

    return '<i class="' . esc_attr($icon_map[$cat_id]) . '" aria-hidden="true"></i>';
}

/**
 * 从 CSS 类名数组中提取 FontAwesome 图标类
 * 识别规则：fa-、fas、far、fal、fab、fad、fat 前缀
 */
function lared_extract_fa_classes(array $classes): string
{
    $fa_prefixes = ['fa-', 'fas', 'far', 'fal', 'fab', 'fad', 'fat', 'fa '];
    $matched = [];

    foreach ($classes as $cls) {
        $cls = trim((string) $cls);
        if ('' === $cls) {
            continue;
        }
        foreach ($fa_prefixes as $prefix) {
            if (0 === strpos($cls, $prefix)) {
                $matched[] = $cls;
                break;
            }
        }
    }

    return implode(' ', $matched);
}

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

// ====== 评论表情渲染 ======

/**
 * 将评论中的表情代码（如 :daxiao:）替换为 <img> 标签
 */
function lared_render_comment_emojis(string $text): string
{
    static $emoji_map = null;

    if (null === $emoji_map) {
        $json_path = get_template_directory() . '/assets/json/bilibili-emojis.json';
        if (file_exists($json_path)) {
            $json = (string) file_get_contents($json_path);
            $emoji_map = json_decode($json, true);
        }
        if (!is_array($emoji_map)) {
            $emoji_map = [];
        }
    }

    if (empty($emoji_map)) {
        return $text;
    }

    $theme_url = get_template_directory_uri();

    foreach ($emoji_map as $code => $emoji) {
        if (false === strpos($text, $code)) {
            continue;
        }
        $img = '<img class="lared-emoji" src="' . esc_url($theme_url . '/assets/images/bilibili/' . $emoji['file'])
            . '" alt="' . esc_attr($emoji['name'])
            . '" title="' . esc_attr($emoji['name'])
            . '" data-code="' . esc_attr($code)
            . '" loading="lazy">';
        $text = str_replace($code, $img, $text);
    }

    return $text;
}
add_filter('comment_text', 'lared_render_comment_emojis', 20);

/**
 * 自定义评论回调函数
 */
function lared_custom_comment_callback(WP_Comment $comment, array $args, int $depth): void
{
    $tag = ('div' === $args['style']) ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent', $comment); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <?php if (0 !== $args['avatar_size']) {
                echo get_avatar($comment, $args['avatar_size']);
            } ?>
            <div class="comment-main">
                <div class="comment-header">
                    <span class="comment-author-name vcard"><?php comment_author_link($comment); ?></span>
                    <?php if (user_can($comment->user_id, 'manage_options')) : ?>
                        <span class="lared-admin-badge" title="<?php esc_attr_e('博主', 'lared'); ?>"><i class="fa-sharp fa-solid fa-crown"></i></span>
                    <?php endif; ?>
                    <span class="comment-metadata">
                        <a href="<?php echo esc_url(get_comment_link($comment, $args)); ?>">
                            <time datetime="<?php comment_time('c'); ?>">
                                <?php echo esc_html(get_comment_date('', $comment) . ' ' . get_comment_time('H:i:s', true, true, $comment)); ?>
                            </time>
                        </a>
                    </span>
                    <?php
                    comment_reply_link(
                        array_merge($args, [
                            'add_below' => 'div-comment',
                            'depth' => $depth,
                            'max_depth' => $args['max_depth'],
                            'before' => '<span class="reply">',
                            'after' => '</span>',
                            'reply_text' => '<i class="fa-sharp fa-solid fa-reply" style="font-size:14px"></i>',
                        ])
                    );
                    ?>
                </div>
                <div class="comment-content">
                    <?php comment_text(); ?>
                </div>
                <?php if ('0' === $comment->comment_approved) : ?>
                    <div class="comment-footer">
                        <em class="comment-awaiting-moderation">
                            <?php echo esc_html__('Your comment is awaiting moderation.', 'lared'); ?>
                        </em>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php
}

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

// ====== Umami 统计输出 ======

function lared_output_umami_script(): void
{
    if (is_admin()) {
        return;
    }

    $umami_script = lared_sanitize_umami_script((string) get_option('lared_umami_script', ''));
    if ('' === $umami_script) {
        return;
    }

    echo $umami_script . "\n";
}
add_action('wp_head', 'lared_output_umami_script', 99);

// ====== 搜索结果不分页 ======

function lared_set_unlimited_search_results(WP_Query $query): void
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_search()) {
        $query->set('posts_per_page', -1);
        $query->set('nopaging', true);
    }
}
add_action('pre_get_posts', 'lared_set_unlimited_search_results');

// ====== 图片 API 获取 ======

/**
 * 从 API 获取图片 URL
 *
 * @param string $api_url API 地址
 * @param int    $post_id 文章 ID（用于缓存）
 * @return string 图片 URL
 */
function lared_get_image_from_api(string $api_url, int $post_id): string
{
    $cache_key = 'lared_api_image_' . $post_id;
    $cached_url = get_transient($cache_key);

    if (false !== $cached_url) {
        return is_string($cached_url) ? $cached_url : '';
    }

    $response = wp_remote_get($api_url, [
        'timeout' => 10,
        'sslverify' => false,
    ]);

    if (is_wp_error($response)) {
        return '';
    }

    $body = wp_remote_retrieve_body($response);
    if ('' === $body) {
        return '';
    }

    // 尝试解析 JSON
    $data = json_decode($body, true);
    if (is_array($data)) {
        $possible_keys = ['url', 'imgUrl', 'image', 'src', 'imageUrl', 'data', 'file'];
        foreach ($possible_keys as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && '' !== trim($data[$key])) {
                $image_url = esc_url_raw(trim($data[$key]));
                set_transient($cache_key, $image_url, DAY_IN_SECONDS);
                return $image_url;
            }
        }
    }

    // 如果返回的是纯 URL 文本
    $url = trim($body);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $image_url = esc_url_raw($url);
        set_transient($cache_key, $image_url, DAY_IN_SECONDS);
        return $image_url;
    }

    return '';
}

// ====== 图片懒加载 ======

function lared_add_lazyload_to_images(string $content): string
{
    if (!get_option('lared_enable_lazyload', true)) {
        return $content;
    }

    return preg_replace_callback('/<img([^>]+)>/i', static function ($matches) {
        $img_tag = $matches[0];
        $attributes = $matches[1];

        if (preg_match('/\sloading\s*=/i', $attributes)) {
            return $img_tag;
        }

        $lazy_attributes = ' loading="lazy" decoding="async"';

        return preg_replace('/\s*\/?>$/', $lazy_attributes . ' />', $img_tag);
    }, $content);
}
add_filter('the_content', 'lared_add_lazyload_to_images', 20);
add_filter('post_thumbnail_html', 'lared_add_lazyload_to_images', 20);

function lared_get_lazyload_attrs(): string
{
    if (!get_option('lared_enable_lazyload', true)) {
        return '';
    }
    return ' loading="lazy" decoding="async"';
}

// ====== 图片加载动画包装 ======

function lared_wrap_images_with_loader(string $content): string
{
    if (is_admin() || (!is_single() && !is_page())) {
        return $content;
    }

    return preg_replace_callback(
        '/<img([^>]+)>/i',
        static function (array $matches): string {
            $img_tag = $matches[0];
            $attributes = $matches[1];

            if (preg_match('/class=["\'][^"\']*img-loading-target/i', $attributes)) {
                return $img_tag;
            }

            if (preg_match('/class=["\'][^"\']*(emoji|avatar)/i', $attributes)) {
                return $img_tag;
            }

            $width = '';
            $height = '';
            if (preg_match('/width=["\'](\d+)["\']/i', $attributes, $w_match)) {
                $width = $w_match[1];
            }
            if (preg_match('/height=["\'](\d+)["\']/i', $attributes, $h_match)) {
                $height = $h_match[1];
            }

            $aspect_style = '';
            if ($width && $height && (int)$height > 0) {
                $aspect_style = ' style="aspect-ratio: ' . $width . '/' . $height . ';"';
            }

            if (preg_match('/class=["\']([^"\']*)["\']/i', $attributes)) {
                $img_tag = preg_replace('/class=["\']([^"\']*)["\']/i', 'class="$1 img-loading-target"', $img_tag);
            } else {
                $img_tag = str_replace('<img', '<img class="img-loading-target"', $img_tag);
            }

            $wrapper = '<figure class="img-loading-wrapper"' . $aspect_style . '>';
            $wrapper .= '<div class="img-loading-spinner">';
            $wrapper .= '<div class="spinner-circle" style="display:block;width:40px;height:40px;border:4px solid #e5e7eb;border-top-color:var(--color-accent,#f53004);border-radius:50%;animation:lared-loading-spin 1s linear infinite;box-sizing:border-box;"></div>';
            $wrapper .= '</div>';
            $wrapper .= $img_tag;
            $wrapper .= '</figure>';

            return $wrapper;
        },
        $content
    );
}
add_filter('the_content', 'lared_wrap_images_with_loader', 25);

/**
 * 一次性合并工具：将旧浏览量 key 合并到 post_views
 * 管理员登录后访问 ?lared_merge_views=1 执行合并
 * 合并完成后请删除此函数和 add_action
 *
 * 合并逻辑：取 post_views / post_views_count / pan_post_views / _count_views
 *           中的最大值写入 post_views，然后删除旧 key
 */
function lared_merge_views_keys(): void
{
    if (!isset($_GET['lared_merge_views']) || !current_user_can('manage_options')) {
        return;
    }

    global $wpdb;

    header('Content-Type: text/plain; charset=UTF-8');

    $old_keys = ['post_views_count', 'pan_post_views', '_count_views'];
    $target_key = 'post_views';

    echo "====================================\n";
    echo "  Lared 浏览量合并工具\n";
    echo "====================================\n\n";

    // 1. 收集所有涉及的 post_id
    $placeholders = implode(',', array_fill(0, count($old_keys) + 1, '%s'));
    $all_keys = array_merge([$target_key], $old_keys);

    $post_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta}
             WHERE meta_key IN ({$placeholders})",
            ...$all_keys
        )
    );

    echo "► 涉及文章数：" . count($post_ids) . "\n\n";

    $merged_count = 0;
    $deleted_count = 0;

    foreach ($post_ids as $pid) {
        $pid = (int) $pid;
        $title = get_the_title($pid) ?: "(ID {$pid})";
        $title_short = mb_substr($title, 0, 20, 'UTF-8');

        // 获取当前 post_views 值
        $current = (int) get_post_meta($pid, $target_key, true);

        // 获取所有旧 key 值，取最大值
        $max_old = 0;
        $old_values = [];
        foreach ($old_keys as $ok) {
            $val = (int) get_post_meta($pid, $ok, true);
            $old_values[$ok] = $val;
            if ($val > $max_old) {
                $max_old = $val;
            }
        }

        // 如果旧 key 最大值 > 当前 post_views，用旧值覆盖
        $final = max($current, $max_old);

        $changed = ($final !== $current);
        if ($changed) {
            update_post_meta($pid, $target_key, $final);
            $merged_count++;
        }

        // 删除旧 key
        $del_this = 0;
        foreach ($old_keys as $ok) {
            if ($old_values[$ok] > 0 || metadata_exists('post', $pid, $ok)) {
                delete_post_meta($pid, $ok);
                $del_this++;
                $deleted_count++;
            }
        }

        $status = $changed ? '✦ 已合并' : '  保持';
        printf(
            "%s  ID %-5d  %-22s  post_views: %d → %d  (删除旧key: %d)\n",
            $status, $pid, $title_short, $current, $final, $del_this
        );
    }

    echo "\n" . str_repeat('-', 60) . "\n";
    echo "► 合并完成\n";
    echo "  更新浏览量的文章数：{$merged_count}\n";
    echo "  删除旧 meta 记录数：{$deleted_count}\n";
    echo "\n► 提示：合并完成后，请从 functions.php 中删除 lared_merge_views_keys 函数\n";
    echo "====================================\n";
    exit;
}
add_action('init', 'lared_merge_views_keys');

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
