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
// require_once get_template_directory() . '/inc/inc-comment-levels.php';

function pan_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');

    register_nav_menus([
        'primary'      => __('Primary Menu', 'pan'),
        'hero_sidebar' => __('Hero 侧边栏', 'pan'),
    ]);
}
add_action('after_setup_theme', 'pan_setup');

function pan_primary_menu_fallback(): void
{
    echo '<ul class="nav"><li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('首页', 'pan') . '</a></li></ul>';
}

function pan_disable_page_comments(): void
{
    remove_post_type_support('page', 'comments');
    remove_post_type_support('page', 'trackbacks');
}
add_action('init', 'pan_disable_page_comments');

function pan_force_page_comments_closed(bool $open, int $post_id): bool
{
    if ('page' === get_post_type($post_id)) {
        return false;
    }

    return $open;
}
add_filter('comments_open', 'pan_force_page_comments_closed', 10, 2);
add_filter('pings_open', 'pan_force_page_comments_closed', 10, 2);

// ====== APlayer 播放列表管理 ======

function pan_normalize_aplayer_playlist(array $playlist): array
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
            'name' => '' !== $name ? $name : __('Unknown Title', 'pan'),
            'artist' => '' !== $artist ? $artist : __('Unknown Artist', 'pan'),
            'url' => $url,
            'cover' => $cover,
            'lrc' => $lrc,
        ];
    }

    return $normalized;
}

function pan_get_aplayer_playlist(): array
{
    $default_playlist = [[
        'name' => 'Pan Radio',
        'artist' => get_bloginfo('name'),
        'url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
        'cover' => '',
        'lrc' => '',
    ]];

    $raw_json = (string) get_option('pan_aplayer_playlist_json', '');
    if ('' === trim($raw_json)) {
        return $default_playlist;
    }

    $decoded = json_decode($raw_json, true);
    if (!is_array($decoded)) {
        return $default_playlist;
    }

    $normalized = pan_normalize_aplayer_playlist($decoded);
    return !empty($normalized) ? $normalized : $default_playlist;
}

function pan_sanitize_aplayer_playlist_json(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }

    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        return '';
    }

    $normalized = pan_normalize_aplayer_playlist($decoded);
    if (empty($normalized)) {
        return '';
    }

    return (string) wp_json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

// ====== 音乐歌单 URL 管理 ======

function pan_sanitize_music_playlist_urls(string $value): string
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

function pan_get_music_playlist_urls(): array
{
    $raw = (string) get_option('pan_music_playlist_urls', '');
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

function pan_parse_music_playlist_url(string $url): ?array
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

function pan_get_music_playlist_sources(): array
{
    $urls = pan_get_music_playlist_urls();
    if (empty($urls)) {
        return [];
    }

    $sources = [];
    foreach ($urls as $url) {
        $parsed = pan_parse_music_playlist_url((string) $url);
        if (!is_array($parsed)) {
            continue;
        }

        $source_key = $parsed['server'] . '|' . $parsed['id'];
        $sources[$source_key] = $parsed;
    }

    return array_values($sources);
}

// ====== Meting API 管理 ======

function pan_sanitize_meting_api_template(string $value): string
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

function pan_get_meting_api_template(): string
{
    $saved = (string) get_option('pan_music_meting_api_template', '');
    $saved = pan_sanitize_meting_api_template($saved);
    if ('' !== $saved) {
        return $saved;
    }

    $local = pan_get_local_meting_api_template();
    if ('' !== $local) {
        return $local;
    }

    return pan_get_external_meting_api_template();
}

function pan_get_external_meting_api_template(): string
{
    return 'https://api.injahow.cn/meting/?server=:server&type=:type&id=:id&r=:r';
}

function pan_get_local_meting_api_template(): string
{
    $local_entry = get_template_directory() . '/assets/music/meting-api-1.2.0/index.php';
    if (!file_exists($local_entry)) {
        return '';
    }

    return trailingslashit(get_template_directory_uri()) . 'assets/music/meting-api-1.2.0/index.php?server=:server&type=:type&id=:id&r=:r';
}

function pan_get_meting_api_fallback_template(?string $primary = null): string
{
    $primary_value = is_string($primary) ? trim($primary) : '';
    $local = pan_get_local_meting_api_template();
    $external = pan_get_external_meting_api_template();

    if ('' !== $local && $primary_value === $local) {
        return $external;
    }

    if ('' !== $local) {
        return $local;
    }

    return $external;
}

// ====== 各种 Sanitize 回调 ======

function pan_sanitize_umami_script(string $value): string
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

function pan_sanitize_ten_year_start_date(string $value): string
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

function pan_sanitize_image_url(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }
    return esc_url_raw($value);
}

function pan_sanitize_image_api_url(string $value): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }
    return esc_url_raw($value);
}

function pan_sanitize_image_animation(string $value): string
{
    $allowed = ['none', 'fade', 'blur', 'pixelate'];
    return in_array($value, $allowed, true) ? $value : 'none';
}

function pan_assets(): void
{
    // CDN 配置（默认从常量，可在 wp-config.php 覆盖）
    $cdn_fonts = PAN_CDN_FONTS;
    $cdn_icons = PAN_CDN_FONTAWESOME;
    $cdn_static = PAN_CDN_STATIC;

    // 字体
    wp_enqueue_style(
        'pan-fonts',
        $cdn_fonts,
        [],
        null
    );

    // Tailwind CSS v4（本地编译版，包含主题所有工具类）
    wp_enqueue_style(
        'pan-tailwind',
        get_template_directory_uri() . '/assets/css/tailwind.css',
        [],
        wp_get_theme()->get('Version')
    );

    // 主题样式
    wp_enqueue_style(
        'pan-style',
        get_stylesheet_uri(),
        ['pan-tailwind'],
        wp_get_theme()->get('Version')
    );

    // Font Awesome Pro
    wp_enqueue_style(
        'pan-fontawesome',
        $cdn_icons,
        [],
        null
    );

    // APlayer CSS
    wp_enqueue_style(
        'pan-aplayer',
        $cdn_static . '/aplayer@1.10.1/dist/APlayer.min.css',
        [],
        '1.10.1'
    );

    // PrismJS Dracula Theme
    wp_enqueue_style(
        'pan-prism-theme',
        $cdn_static . '/prismjs@1.29.0/themes/prism-dracula.min.css',
        [],
        '1.29.0'
    );

    // Theme JS
    wp_enqueue_script(
        'pan-theme',
        get_template_directory_uri() . '/assets/js/app.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );

    // PJAX
    wp_enqueue_script(
        'pan-pjax',
        get_template_directory_uri() . '/assets/js/pjax.min.js',
        [],
        '1.0.0',
        true
    );

    // APlayer JS
    wp_enqueue_script(
        'pan-aplayer',
        $cdn_static . '/aplayer@1.10.1/dist/APlayer.min.js',
        [],
        '1.10.1',
        true
    );

    // PrismJS Core
    wp_enqueue_script(
        'pan-prism-core',
        $cdn_static . '/prismjs@1.29.0/components/prism-core.min.js',
        [],
        '1.29.0',
        true
    );

    // PrismJS Autoloader
    wp_enqueue_script(
        'pan-prism-autoloader',
        $cdn_static . '/prismjs@1.29.0/plugins/autoloader/prism-autoloader.js',
        ['pan-prism-core'],
        '1.29.0',
        true
    );

    // ViewImage - lightweight image lightbox
    wp_enqueue_script(
        'pan-view-image',
        get_template_directory_uri() . '/assets/js/view-image.min.js',
        [],
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'pan_assets');

function pan_append_content_link_icon(string $content): string
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

            return '<a' . $attrs_before . 'href="' . $url . '"' . trim($attrs_after) . '>' . $text . ' <span class="external-link-icon">' . $link_icon . '</span></a>';
        },
        $content
    ) ?? $content;
}
add_filter('the_content', 'pan_append_content_link_icon', 20);

function pan_add_target_blank_to_content_links(string $content): string
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
add_filter('the_content', 'pan_add_target_blank_to_content_links', 999);

function pan_archive_per_page(): int
{
    return 18;
}
add_filter('pan_archive_posts_per_page', 'pan_archive_per_page');

function pan_load_aplayer_config(): void
{
    $shared_music_base_path = trailingslashit(get_template_directory_uri() . '/assets/music');

    $config = [
        'playlist' => pan_get_aplayer_playlist(),
        'musicBasePath' => $shared_music_base_path,
        'defaultCover' => $shared_music_base_path . 'img/lizhi.jpg',
        'autoplay' => false,
        'loop' => 'all',
        'order' => 'list',
        'volume' => 0.7,
    ];

    $config = apply_filters('pan_aplayer_config', $config);

    wp_localize_script('pan-theme', 'PanAPlayerConfig', $config);
}
add_action('wp_enqueue_scripts', 'pan_load_aplayer_config', 20);

/**
 * 为指定查询条件获取 4 种排序文章（最新/热门/热评/随机），每种取 1 篇。
 *
 * @param array $base_args WP_Query 基础参数（可含 tax_query / p 等），无需填写排序/条数。
 * @return array 最多 4 个元素，每个含 post_id/title/image/permalink/type_key/type_label/type_icon。
 */
function pan_hero_fetch_four_articles(array $base_args): array
{
    $type_defs = [
        [
            'key'     => 'latest',
            'label'   => __('最新文章', 'pan'),
            'icon'    => 'fa-solid fa-clock',
            'orderby' => 'date',
            'order'   => 'DESC',
        ],
        [
            'key'      => 'popular',
            'label'    => __('热门文章', 'pan'),
            'icon'     => 'fa-solid fa-fire',
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
            'meta_key' => 'post_views',
        ],
        [
            'key'     => 'comment',
            'label'   => __('热评文章', 'pan'),
            'icon'    => 'fa-solid fa-comments',
            'orderby' => 'comment_count',
            'order'   => 'DESC',
        ],
        [
            'key'     => 'random',
            'label'   => __('随机文章', 'pan'),
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
        $image = pan_get_post_image_url($pid, 'large');
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

function pan_get_hero_items(): array
{
    $items = [];

    // ── 第一行：固定"全部"（全站 4 种排序各取一篇）──
    $all_articles = pan_hero_fetch_four_articles([]);
    if (!empty($all_articles)) {
        $all_start     = wp_rand(0, count($all_articles) - 1);
        $all_start_art = $all_articles[$all_start];
        $items[] = [
            'post_id'    => $all_start_art['post_id'],
            'item_url'   => home_url('/'),
            'cat_label'  => __('全部', 'pan'),
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
        $nav_fa = pan_extract_fa_classes((array) $nav_item->classes);
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
            // 若菜单 CSS 类没有 icon，降级到 pan_get_category_icon_html
            if ('' === $item_icon_html) {
                $item_icon_html = pan_get_category_icon_html($term_id);
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

        $articles = pan_hero_fetch_four_articles($base_args);

        // 兜底：若该分类/链接下无结果，降级为全站查询
        if (empty($articles)) {
            $articles = pan_hero_fetch_four_articles([]);
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

function pan_count_unique_approved_commenters(int $post_id): int
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

function pan_ajax_submit_comment(): void
{
    check_ajax_referer('pan_comment_submit', 'nonce');

    $comment_post_id = (int) ($_POST['comment_post_ID'] ?? 0);
    $comment_author = sanitize_text_field($_POST['author'] ?? '');
    $comment_author_email = sanitize_email($_POST['email'] ?? '');
    $comment_author_url = esc_url_raw($_POST['url'] ?? '');
    $comment_content = sanitize_textarea_field($_POST['comment'] ?? '');
    $comment_parent = (int) ($_POST['comment_parent'] ?? 0);

    if (!$comment_post_id || '' === $comment_content || '' === $comment_author || '' === $comment_author_email) {
        wp_send_json_error(['message' => __('请填写所有必填项。', 'pan')]);
        return;
    }

    if (!is_email($comment_author_email)) {
        wp_send_json_error(['message' => __('请输入有效的邮箱地址。', 'pan')]);
        return;
    }

    $user = wp_get_current_user();
    $user_id = $user->ID;

    $comment_data = [
        'comment_post_ID' => $comment_post_id,
        'comment_author' => $comment_author,
        'comment_author_email' => $comment_author_email,
        'comment_author_url' => $comment_author_url,
        'comment_content' => $comment_content,
        'comment_parent' => $comment_parent,
        'user_id' => $user_id,
        'comment_approved' => 0,
    ];

    $comment_id = wp_new_comment($comment_data, true);

    if (is_wp_error($comment_id)) {
        wp_send_json_error(['message' => $comment_id->get_error_message()]);
        return;
    }

    $comment = get_comment($comment_id);
    $is_approved = (1 === (int) $comment->comment_approved);
    $comment_html = '';

    if ($is_approved) {
        ob_start();
        wp_list_comments([
            'style' => 'ol',
            'short_ping' => true,
            'avatar_size' => 44,
            'callback' => 'pan_custom_comment_callback',
        ], [$comment]);
        $comment_html = (string) ob_get_clean();
    }

    $post_id = (int) $comment->comment_post_ID;

    wp_send_json_success([
        'approved' => $is_approved,
        'pending' => !$is_approved,
        'message' => $is_approved
            ? __('评论发布成功。', 'pan')
            : __('评论已提交，审核通过后显示。', 'pan'),
        'html' => $comment_html,
        'parent' => (int) $comment->comment_parent,
        'commentId' => (int) $comment->comment_ID,
        'commentTotal' => (int) get_comments_number($post_id),
        'commenterCount' => pan_count_unique_approved_commenters($post_id),
    ]);
}
add_action('wp_ajax_pan_submit_comment', 'pan_ajax_submit_comment');
add_action('wp_ajax_nopriv_pan_submit_comment', 'pan_ajax_submit_comment');

/**
 * AJAX: Hero 区域随机获取一篇文章（从 4 种排序方式中随机选一种）
 */
function pan_hero_random_article(): void
{
    check_ajax_referer('pan_ajax_nonce', 'nonce');

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
            'label'   => __('最新文章', 'pan'),
            'icon'    => 'fa-solid fa-clock',
            'orderby' => 'date',
            'order'   => 'DESC',
        ],
        [
            'key'      => 'popular',
            'label'    => __('热门文章', 'pan'),
            'icon'     => 'fa-solid fa-fire',
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
            'meta_key' => 'post_views',
        ],
        [
            'key'     => 'comment',
            'label'   => __('热评文章', 'pan'),
            'icon'    => 'fa-solid fa-comments',
            'orderby' => 'comment_count',
            'order'   => 'DESC',
        ],
        [
            'key'     => 'random',
            'label'   => __('随机文章', 'pan'),
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
    $image = pan_get_post_image_url($pid, 'large');
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
add_action('wp_ajax_pan_hero_random_article', 'pan_hero_random_article');
add_action('wp_ajax_nopriv_pan_hero_random_article', 'pan_hero_random_article');

/**
 * AJAX 搜索 - 实时搜索文章
 */
function pan_ajax_search(): void
{
    check_ajax_referer('pan_ajax_nonce', 'nonce');

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
add_action('wp_ajax_pan_ajax_search', 'pan_ajax_search');
add_action('wp_ajax_nopriv_pan_ajax_search', 'pan_ajax_search');

function pan_localize_script(): void
{
    wp_localize_script('pan-theme', 'PanAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pan_ajax_nonce'),
        'memosFilterNonce' => wp_create_nonce('pan_memos_filter_nonce'),
        'memosPublishNonce' => wp_create_nonce('pan_memos_publish_nonce'),
        'commentSubmitNonce' => wp_create_nonce('pan_comment_submit'),
        'levelNonce' => wp_create_nonce('pan_level_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'pan_localize_script', 20);

function pan_get_site_running_days_from_first_post(): int
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

function pan_remove_latex_backslashes(string $content): string
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
add_filter('the_content', 'pan_remove_latex_backslashes', 5);

function pan_remove_title_backslashes(string $title): string
{
    if (is_admin()) {
        return $title;
    }

    $title = str_replace('\\', '', $title);

    return $title;
}
add_filter('the_title', 'pan_remove_title_backslashes', 10);
add_filter('single_post_title', 'pan_remove_title_backslashes', 10);

function pan_migrate_template_paths(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $done = (string) get_option('pan_template_path_migrated_v4', '');
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

    update_option('pan_template_path_migrated_v4', 'yes', true);
}
add_action('admin_init', 'pan_migrate_template_paths');

/**
 * 博客十年之约进度数据
 * 开始日期通过 "外观 > Pan 设置" 中的 pan_ten_year_start_date 选项配置
 *
 * @return array{start_date:string,end_date:string,progress_percent:float,remaining_days:int,is_started:bool}
 */
function pan_get_ten_year_progress_data(): array
{
    $empty = [
        'start_date'       => '',
        'end_date'         => '',
        'progress_percent' => 0.0,
        'remaining_days'   => 0,
        'is_started'       => false,
    ];

    $start_raw = (string) get_option('pan_ten_year_start_date', '');
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

function pan_add_theme_settings_page(): void
{
    add_theme_page(
        __('主题设置', 'pan'),
        __('主题设置', 'pan'),
        'manage_options',
        'pan-theme-settings',
        'pan_render_theme_settings_page'
    );
}
add_action('admin_menu', 'pan_add_theme_settings_page');

function pan_register_theme_settings(): void
{
    register_setting('pan_theme_settings_group', 'pan_aplayer_playlist_json', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_aplayer_playlist_json',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_music_playlist_urls', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_music_playlist_urls',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_music_meting_api_template', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_meting_api_template',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_memos_site_url', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_memos_url',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_memos_api_url', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_memos_url',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_memos_api_token', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_memos_token',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_memos_page_size', [
        'type' => 'integer',
        'sanitize_callback' => 'pan_sanitize_memos_page_size',
        'default' => 20,
    ]);

    register_setting('pan_theme_settings_group', 'pan_umami_script', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_umami_script',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_ten_year_start_date', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_ten_year_start_date',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_default_featured_image', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_image_url',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_featured_image_api', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_image_api_url',
        'default' => '',
    ]);

    register_setting('pan_theme_settings_group', 'pan_enable_lazyload', [
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => true,
    ]);

    register_setting('pan_theme_settings_group', 'pan_image_load_animation', [
        'type' => 'string',
        'sanitize_callback' => 'pan_sanitize_image_animation',
        'default' => 'none',
    ]);
}
add_action('admin_init', 'pan_register_theme_settings');

function pan_render_theme_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $playlist_json = (string) get_option('pan_aplayer_playlist_json', '');
    $music_playlist_urls = (string) get_option('pan_music_playlist_urls', '');
    $meting_api_template = (string) get_option('pan_music_meting_api_template', pan_get_meting_api_template());
    $memos_site_url = (string) get_option('pan_memos_site_url', '');
    $memos_api_url = (string) get_option('pan_memos_api_url', pan_get_memos_api_url());
    $memos_api_token = (string) get_option('pan_memos_api_token', '');
    $memos_page_size = (int) get_option('pan_memos_page_size', 20);
    $umami_script = (string) get_option('pan_umami_script', '');
    $ten_year_start_date = (string) get_option('pan_ten_year_start_date', '');
    $ten_year_start_default = pan_get_first_post_date_ymd();
    $default_featured_image = (string) get_option('pan_default_featured_image', '');
    $featured_image_api = (string) get_option('pan_featured_image_api', '');
    $enable_lazyload = (bool) get_option('pan_enable_lazyload', true);
    $image_load_animation = (string) get_option('pan_image_load_animation', 'none');
    $rss_cache_status = isset($_GET['pan_rss_cache']) ? sanitize_key((string) $_GET['pan_rss_cache']) : '';
    $rss_cache_removed = isset($_GET['pan_rss_removed']) ? max(0, (int) $_GET['pan_rss_removed']) : 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('主题设置', 'pan'); ?></h1>
        <?php if ('cleared' === $rss_cache_status) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html(sprintf(__('RSS 缓存已刷新，清理 %d 个缓存文件。', 'pan'), $rss_cache_removed)); ?></p></div>
        <?php elseif ('failed' === $rss_cache_status) : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('RSS 缓存刷新失败，请检查 data/rss 目录权限。', 'pan'); ?></p></div>
        <?php endif; ?>
        <p><?php esc_html_e('配置 APlayer 播放列表（JSON 数组）。每一项支持 name、artist、url、cover、lrc。', 'pan'); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields('pan_theme_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="pan_aplayer_playlist_json"><?php esc_html_e('APlayer Playlist JSON', 'pan'); ?></label></th>
                    <td>
                        <textarea id="pan_aplayer_playlist_json" name="pan_aplayer_playlist_json" rows="16" class="large-text code"><?php echo esc_textarea($playlist_json); ?></textarea>
                        <p class="description"><?php esc_html_e('示例：[{"name":"Song A","artist":"Pan","url":"https://example.com/a.mp3","cover":"https://example.com/a.jpg"}]', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_music_playlist_urls"><?php esc_html_e('音乐页歌单地址', 'pan'); ?></label></th>
                    <td>
                        <textarea id="pan_music_playlist_urls" name="pan_music_playlist_urls" rows="6" class="large-text code" placeholder="https://music.163.com/#/playlist?id=xxxx&#10;https://y.qq.com/n/ryqq/playlist/xxxx"><?php echo esc_textarea($music_playlist_urls); ?></textarea>
                        <p class="description"><?php esc_html_e('用于"Music Page"独立页面。每行一个歌单地址，支持网易云与 QQ 音乐链接。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_music_meting_api_template"><?php esc_html_e('Meting API 模板', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_music_meting_api_template" name="pan_music_meting_api_template" type="text" class="large-text code" value="<?php echo esc_attr($meting_api_template); ?>" placeholder="<?php echo esc_attr(pan_get_local_meting_api_template() ?: pan_get_external_meting_api_template()); ?>" />
                        <p class="description"><?php esc_html_e('用于解析歌单到可播放链接。留空时自动优先使用主题内本地 meting-api（assets/music/meting-api-1.2.0），失败再走外部兜底。需包含 :server、:type、:id 占位符。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_memos_site_url"><?php esc_html_e('Memos 站点地址', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_memos_site_url" name="pan_memos_site_url" type="url" class="large-text code" value="<?php echo esc_attr($memos_site_url); ?>" placeholder="https://memos.example.com" />
                        <p class="description"><?php esc_html_e('用于拼接动态详情链接。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_memos_api_url"><?php esc_html_e('Memos API 地址', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_memos_api_url" name="pan_memos_api_url" type="url" class="large-text code" value="<?php echo esc_attr($memos_api_url); ?>" placeholder="https://memos.example.com/api/v1/memos" />
                        <p class="description"><?php esc_html_e('例如：/api/v1/memos；若留空则按"站点地址 + /api/v1/memos"自动推导。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_memos_api_token"><?php esc_html_e('Memos API Token', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_memos_api_token" name="pan_memos_api_token" type="text" class="large-text code" value="<?php echo esc_attr($memos_api_token); ?>" placeholder="可选" />
                        <p class="description"><?php esc_html_e('私有实例可填写 Token，会自动附带 Authorization 与 X-Api-Key 头。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_memos_page_size"><?php esc_html_e('Memos 拉取数量', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_memos_page_size" name="pan_memos_page_size" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr((string) $memos_page_size); ?>" />
                        <p class="description"><?php esc_html_e('每次请求的最大条数，建议 20-50。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_umami_script"><?php esc_html_e('Umami 统计代码', 'pan'); ?></label></th>
                    <td>
                        <textarea id="pan_umami_script" name="pan_umami_script" rows="5" class="large-text code" placeholder='<script defer src="https://umami.example.com/script.js" data-website-id="xxxx-xxxx-xxxx-xxxx"></script>'><?php echo esc_textarea($umami_script); ?></textarea>
                        <p class="description"><?php esc_html_e('粘贴 Umami 官方 script 代码，保存后会自动输出到前台 head。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_ten_year_start_date"><?php esc_html_e('博客十年起始日期', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_ten_year_start_date" name="pan_ten_year_start_date" type="date" class="regular-text" value="<?php echo esc_attr($ten_year_start_date); ?>" />
                        <p class="description">
                            <?php
                            printf(
                                esc_html__('留空将默认使用第一篇文章日期（%s）。', 'pan'),
                                '' !== $ten_year_start_default ? esc_html($ten_year_start_default) : esc_html__('暂无文章', 'pan')
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_default_featured_image"><?php esc_html_e('默认特色图片', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_default_featured_image" name="pan_default_featured_image" type="url" class="large-text code" value="<?php echo esc_attr($default_featured_image); ?>" placeholder="https://example.com/default-image.jpg" />
                        <p class="description"><?php esc_html_e('设置全站默认特色图片 URL，当文章没有特色图片且内容中无图片时使用。', 'pan'); ?></p>
                        <?php if ('' !== $default_featured_image) : ?>
                            <p class="description">
                                <img src="<?php echo esc_url($default_featured_image); ?>" alt="<?php esc_attr_e('默认特色图片预览', 'pan'); ?>" style="max-width: 200px; max-height: 120px; margin-top: 8px; border-radius: 4px;" />
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_featured_image_api"><?php esc_html_e('特色图片 API', 'pan'); ?></label></th>
                    <td>
                        <input id="pan_featured_image_api" name="pan_featured_image_api" type="url" class="large-text code" value="<?php echo esc_attr($featured_image_api); ?>" placeholder="https://api.example.com/random-image" />
                        <p class="description"><?php esc_html_e('输入随机图片 API 地址，系统将从此 API 获取图片作为文章特色图。API 应返回图片 URL 或可直接访问的图片。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('图片懒加载', 'pan'); ?></th>
                    <td>
                        <label for="pan_enable_lazyload">
                            <input id="pan_enable_lazyload" name="pan_enable_lazyload" type="checkbox" value="1" <?php checked($enable_lazyload); ?> />
                            <?php esc_html_e('启用图片懒加载', 'pan'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('开启后，页面中的图片将延迟加载，提升页面加载速度。', 'pan'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_image_load_animation"><?php esc_html_e('图片加载动画', 'pan'); ?></label></th>
                    <td>
                        <select id="pan_image_load_animation" name="pan_image_load_animation" class="regular-text">
                            <option value="none" <?php selected($image_load_animation, 'none'); ?>><?php esc_html_e('无动画', 'pan'); ?></option>
                            <option value="fade" <?php selected($image_load_animation, 'fade'); ?>><?php esc_html_e('淡入效果', 'pan'); ?></option>
                            <option value="blur" <?php selected($image_load_animation, 'blur'); ?>><?php esc_html_e('模糊淡入', 'pan'); ?></option>
                            <option value="pixelate" <?php selected($image_load_animation, 'pixelate'); ?>><?php esc_html_e('像素化显现', 'pan'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('选择图片加载时的动画效果。淡入效果柔和自然，像素化显现具有艺术感。', 'pan'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr />
        <h2><?php esc_html_e('RSS Cache', 'pan'); ?></h2>
        <p><?php echo esc_html(sprintf(__('缓存目录：%s', 'pan'), pan_get_rss_cache_dir())); ?></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="pan_clear_rss_cache" />
            <?php wp_nonce_field('pan_clear_rss_cache_action'); ?>
            <?php submit_button(__('手动刷新缓存', 'pan'), 'secondary', 'submit', false); ?>
        </form>
    </div>
    <?php
}

function pan_handle_clear_rss_cache(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('无权限执行该操作。', 'pan'));
    }

    check_admin_referer('pan_clear_rss_cache_action');

    $result = function_exists('pan_clear_rss_cache_files')
        ? pan_clear_rss_cache_files()
        : ['removed' => 0, 'errors' => 1, 'dir' => ''];

    $status = ((int) ($result['errors'] ?? 1) === 0) ? 'cleared' : 'failed';
    $removed = max(0, (int) ($result['removed'] ?? 0));

    $redirect_url = add_query_arg([
        'page' => 'pan-theme-settings',
        'pan_rss_cache' => $status,
        'pan_rss_removed' => $removed,
    ], admin_url('themes.php'));

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('admin_post_pan_clear_rss_cache', 'pan_handle_clear_rss_cache');

/**
 * 读取文章浏览量（post meta: post_views）
 */
function pan_get_post_views(int $post_id): int
{
    return (int) get_post_meta($post_id, 'post_views', true);
}

/**
 * AJAX 自增文章浏览量
 * 由前端 JS 在单篇文章页触发（兼容 PJAX 导航）
 */
function pan_track_post_views_ajax(): void
{
    check_ajax_referer('pan_ajax_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if ($post_id < 1 || 'publish' !== get_post_status($post_id)) {
        wp_send_json_error(['message' => 'Invalid post']);
        return;
    }

    $current = pan_get_post_views($post_id);
    update_post_meta($post_id, 'post_views', $current + 1);

    wp_send_json_success(['tracked' => true, 'views' => $current + 1]);
}
add_action('wp_ajax_pan_track_views', 'pan_track_post_views_ajax');
add_action('wp_ajax_nopriv_pan_track_views', 'pan_track_post_views_ajax');

function pan_get_post_image_url(int $post_id, string $size = 'large'): string
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
    $api_url = (string) get_option('pan_featured_image_api', '');
    if ('' !== $api_url) {
        $api_image = pan_get_image_from_api($api_url, $post_id);
        if ('' !== $api_image) {
            return $api_image;
        }
    }

    // 4. 使用主题设置的默认图片
    $default_image = (string) get_option('pan_default_featured_image', '');
    if ('' !== $default_image) {
        return $default_image;
    }

    return '';
}

function pan_get_post_image_html(int $post_id, string $size = 'large', array $attrs = []): string
{
    if (has_post_thumbnail($post_id)) {
        $attr_defaults = ['alt' => get_the_title($post_id)];
        $attr_merged   = array_merge($attr_defaults, $attrs);
        $html = get_the_post_thumbnail($post_id, $size, $attr_merged);
        if ($html) {
            return $html;
        }
    }

    $image_url = pan_get_post_image_url($post_id, $size);
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

function pan_get_category_icon_html(int $cat_id): string
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
                $fa_classes = pan_extract_fa_classes((array) $nav_item->classes);
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
function pan_extract_fa_classes(array $classes): string
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
function pan_clean_code_backticks(string $content): string
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
add_filter('the_content', 'pan_clean_code_backticks', 999);
add_filter('the_excerpt', 'pan_clean_code_backticks', 999);

/**
 * 自定义评论回调函数
 */
function pan_custom_comment_callback(WP_Comment $comment, array $args, int $depth): void
{
    $tag = ('div' === $args['style']) ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent', $comment); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <footer class="comment-meta">
                <div class="comment-author vcard">
                    <?php if (0 !== $args['avatar_size']) {
                        echo get_avatar($comment, $args['avatar_size']);
                    } ?>
                    <div class="comment-author-name">
                        <?php comment_author_link($comment); ?>
                    </div>
                </div>
                <div class="comment-metadata">
                    <a href="<?php echo esc_url(get_comment_link($comment, $args)); ?>">
                        <time datetime="<?php comment_time('c'); ?>">
                            <?php
                            printf(
                                /* translators: 1: Comment date, 2: Comment time. */
                                __('%1$s at %2$s', 'pan'),
                                get_comment_date('', $comment),
                                get_comment_time()
                            );
                            ?>
                        </time>
                    </a>
                    <?php edit_comment_link(__('Edit', 'pan'), '<span class="edit-link">', '</span>'); ?>
                </div>
            </footer>
            <div class="comment-content">
                <?php comment_text(); ?>
            </div>
            <?php if ('0' === $comment->comment_approved) : ?>
                <em class="comment-awaiting-moderation">
                    <?php echo esc_html__('Your comment is awaiting moderation.', 'pan'); ?>
                </em>
            <?php endif; ?>
            <?php
            comment_reply_link(
                array_merge($args, [
                    'add_below' => 'div-comment',
                    'depth' => $depth,
                    'max_depth' => $args['max_depth'],
                    'before' => '<div class="reply">',
                    'after' => '</div>',
                ])
            );
            ?>
        </article>
    <?php
}

// ====== 分类描述中保留 FA 图标 ======

/**
 * 允许分类描述中保留 Font Awesome <i> 图标的 class 属性。
 */
function pan_allow_fa_icons_in_term_description(): void
{
    remove_filter('pre_term_description', 'wp_filter_kses');
    add_filter('pre_term_description', 'pan_sanitize_term_description');
}
add_action('init', 'pan_allow_fa_icons_in_term_description');

function pan_sanitize_term_description(string $description): string
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

function pan_get_first_post_date_ymd(): string
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

function pan_get_ten_year_start_date_ymd(): string
{
    $saved = pan_sanitize_ten_year_start_date((string) get_option('pan_ten_year_start_date', ''));
    if ('' !== $saved) {
        return $saved;
    }

    return pan_get_first_post_date_ymd();
}

// ====== Umami 统计输出 ======

function pan_output_umami_script(): void
{
    if (is_admin()) {
        return;
    }

    $umami_script = pan_sanitize_umami_script((string) get_option('pan_umami_script', ''));
    if ('' === $umami_script) {
        return;
    }

    echo $umami_script . "\n";
}
add_action('wp_head', 'pan_output_umami_script', 99);

// ====== 搜索结果不分页 ======

function pan_set_unlimited_search_results(WP_Query $query): void
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_search()) {
        $query->set('posts_per_page', -1);
        $query->set('nopaging', true);
    }
}
add_action('pre_get_posts', 'pan_set_unlimited_search_results');

// ====== 图片 API 获取 ======

/**
 * 从 API 获取图片 URL
 *
 * @param string $api_url API 地址
 * @param int    $post_id 文章 ID（用于缓存）
 * @return string 图片 URL
 */
function pan_get_image_from_api(string $api_url, int $post_id): string
{
    $cache_key = 'pan_api_image_' . $post_id;
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

function pan_add_lazyload_to_images(string $content): string
{
    if (!get_option('pan_enable_lazyload', true)) {
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
add_filter('the_content', 'pan_add_lazyload_to_images', 20);
add_filter('post_thumbnail_html', 'pan_add_lazyload_to_images', 20);

function pan_get_lazyload_attrs(): string
{
    if (!get_option('pan_enable_lazyload', true)) {
        return '';
    }
    return ' loading="lazy" decoding="async"';
}

// ====== 图片加载动画包装 ======

function pan_wrap_images_with_loader(string $content): string
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
            $wrapper .= '<div class="spinner-circle" style="display:block;width:40px;height:40px;border:4px solid #e5e7eb;border-top-color:var(--color-accent,#f53004);border-radius:50%;animation:pan-loading-spin 1s linear infinite;box-sizing:border-box;"></div>';
            $wrapper .= '</div>';
            $wrapper .= $img_tag;
            $wrapper .= '</figure>';

            return $wrapper;
        },
        $content
    );
}
add_filter('the_content', 'pan_wrap_images_with_loader', 25);
