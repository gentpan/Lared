<?php

if (!defined('ABSPATH')) {
    exit;
}

// ====== CDN 配置常量 ======
// 可在 wp-config.php 中使用 define() 覆盖这些值
if (!defined('PAN_CDN_FONTAWESOME')) {
    define('PAN_CDN_FONTAWESOME', 'https://icons.bluecdn.com/fontawesome-pro/css/all.css');
}
if (!defined('PAN_CDN_STATIC')) {
    define('PAN_CDN_STATIC', 'https://static.bluecdn.com/npm');
}
// =========================

require_once get_template_directory() . '/inc/inc-image.php';
require_once get_template_directory() . '/inc/inc-hero.php';
require_once get_template_directory() . '/inc/inc-editor.php';
require_once get_template_directory() . '/inc/inc-download.php';
require_once get_template_directory() . '/inc/inc-rss.php';
require_once get_template_directory() . '/inc/inc-memos.php';
require_once get_template_directory() . '/inc/inc-code-runner.php';
require_once get_template_directory() . '/inc/inc-ai-summary.php';
require_once get_template_directory() . '/inc/inc-comments.php';
require_once get_template_directory() . '/inc/inc-email.php';
require_once get_template_directory() . '/inc/inc-about.php';
require_once get_template_directory() . '/inc/inc-theme-settings.php';

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
 * 主题激活时自动重建所有评论者等级缓存
 */
function lared_activate_rebuild_levels(): void
{
    if (function_exists('lared_refresh_all_commenter_levels')) {
        $result = lared_refresh_all_commenter_levels();
        error_log(sprintf(
            '[Lared] 主题激活：已重建 %d 位评论者等级（%d 个错误）',
            $result['updated'],
            $result['errors']
        ));
    }
}
add_action('after_switch_theme', 'lared_activate_rebuild_levels');

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
    $cdn_icons = PAN_CDN_FONTAWESOME;
    $cdn_static = PAN_CDN_STATIC;

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
        get_template_directory_uri() . '/assets/css/main.css',
        ['lared-tailwind', 'lared-plyr'],
        (string) filemtime(get_template_directory() . '/assets/css/main.css')
    );

    // Font Awesome Pro
    wp_enqueue_style(
        'lared-fontawesome',
        $cdn_icons,
        [],
        null
    );

    // Flag Icons CSS
    wp_enqueue_style(
        'lared-flag-icons',
        'https://flagcdn.io/css/flag-icons.min.css',
        [],
        null
    );

    // Plyr CSS（依赖 tailwind，确保在其之后加载，避免被重置覆盖）
    wp_enqueue_style(
        'lared-plyr',
        $cdn_static . '/plyr@3.7.8/dist/plyr.css',
        ['lared-tailwind'],
        '3.7.8'
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
        ['lared-plyr', 'lared-prism-autoloader'],
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

    // Plyr JS
    wp_enqueue_script(
        'lared-plyr',
        $cdn_static . '/plyr@3.7.8/dist/plyr.min.js',
        [],
        '3.7.8',
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

    // lazysizes - 高性能图片懒加载
    wp_enqueue_script(
        'lared-lazysizes',
        get_template_directory_uri() . '/assets/js/lazysizes.min.js',
        [],
        '5.3.2',
        false  // 在 head 加载，尽早接管懒加载
    );

    // ViewImage - lightweight image lightbox
    wp_enqueue_script(
        'lared-view-image',
        get_template_directory_uri() . '/assets/js/view-image.min.js',
        [],
        '1.0.0',
        true
    );

    // WordPress 内置回复脚本（moveForm）— 因使用 PJAX，需始终加载，
    // 否则从非 singular 页面导航到文章页时 window.addComment 不存在
    wp_enqueue_script('comment-reply');
}
add_action('wp_enqueue_scripts', 'lared_assets');

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

function lared_count_unique_approved_commenters(int $post_id): int
{
    global $wpdb;

    // 使用 SQL 直接统计唯一评论者，避免加载所有评论对象
    // 优先按 user_id 去重，其次按 email，最后按 author name
    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM (
            SELECT CASE
                WHEN user_id > 0 THEN CONCAT('user:', user_id)
                WHEN comment_author_email != '' THEN CONCAT('email:', LOWER(TRIM(comment_author_email)))
                ELSE CONCAT('name:', LOWER(TRIM(comment_author)))
            END AS commenter_key
            FROM {$wpdb->comments}
            WHERE comment_post_ID = %d
              AND comment_approved = '1'
              AND comment_type = 'comment'
            GROUP BY commenter_key
        ) AS unique_commenters",
        $post_id
    ));

    return $count;
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
        "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_author_email = %s AND comment_content = %s AND comment_date_gmt > %s LIMIT 1",
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

    $creds = [
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember,
    ];

    $user = wp_signon($creds, is_ssl());

    if (is_wp_error($user)) {
        wp_send_json_error(['message' => __('用户名或密码错误', 'lared')]);
    }

    wp_set_current_user($user->ID);

    wp_send_json_success([
        'message'    => __('登录成功', 'lared'),
        'avatar'     => get_avatar_url($user->ID, ['size' => 60]),
        'name'       => $user->display_name,
        'admin_url'  => admin_url(),
        'logout_url' => wp_logout_url(home_url()),
    ]);
}
add_action('wp_ajax_nopriv_lared_ajax_login', 'lared_ajax_login');
add_action('wp_ajax_lared_ajax_login', 'lared_ajax_login');

/**
 * AJAX 搜索 - 实时搜索文章
 */
function lared_ajax_search(): void
{
    // 公开只读接口，不强制 nonce

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
 * AJAX 自增文章浏览量
 * 由前端 JS 在单篇文章页触发（兼容 PJAX 导航）
 * 使用 transient 节流（同一 IP 对同一篇文章 30 秒内只计一次）
 * 使用 $wpdb 原子自增，避免竞态条件
 */
function lared_track_post_views_ajax(): void
{
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if ($post_id < 1 || 'publish' !== get_post_status($post_id)) {
        wp_send_json_error(['message' => 'Invalid post']);
        return;
    }

    // 节流：同一 IP + 同一文章 30 秒内不重复计数
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $throttle_key = 'lared_pv_' . md5($ip . '_' . $post_id);
    if (get_transient($throttle_key)) {
        $current = lared_get_post_views($post_id);
        wp_send_json_success(['tracked' => false, 'views' => $current]);
        return;
    }
    set_transient($throttle_key, 1, 30);

    // 原子自增
    global $wpdb;
    $meta_exists = metadata_exists('post', $post_id, 'post_views');
    if ($meta_exists) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = 'post_views' LIMIT 1",
            $post_id
        ));
    } else {
        add_post_meta($post_id, 'post_views', 1, true);
    }

    // 清理对象缓存
    wp_cache_delete($post_id, 'post_meta');
    $current = lared_get_post_views($post_id);

    wp_send_json_success(['tracked' => true, 'views' => $current]);
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

// ====== 评论表情渲染 ======

/**
 * 将评论中的表情代码（如 :daxiao:）替换为 <img> 标签
 */
function lared_render_comment_emojis(string $text): string
{
    static $emoji_map = null;

    if (null === $emoji_map) {
        $json_path = get_template_directory() . '/data/bilibili-emojis.json';
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
            . '">';
        $text = str_replace($code, $img, $text);
    }

    return $text;
}
add_filter('comment_text', 'lared_render_comment_emojis', 20);

/**
 * 后台评论列表：限制表情图片尺寸（后台不加载主题 CSS）
 */
function lared_admin_comment_emoji_css(): void
{
    $screen = get_current_screen();
    if (!$screen || 'edit-comments' !== $screen->id) {
        return;
    }
    echo '<style>
        .comment .lared-emoji,
        .wp-list-table .lared-emoji {
            display: inline-block;
            width: 1.6em;
            height: 1.6em;
            vertical-align: text-bottom;
            margin: 0 1px;
        }
    </style>';
}
add_action('admin_head', 'lared_admin_comment_emoji_css');


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
                    <span class="comment-author-wrap">
                        <span class="comment-author-name vcard"><?php comment_author_link($comment); ?></span><?php if (user_can($comment->user_id, 'manage_options')) : ?><span class="lared-admin-badge"><i class="fa-sharp fa-solid fa-crown"></i><span class="comment-tooltip"><?php esc_html_e('博主', 'lared'); ?></span></span><?php endif; ?><?php
                        // 友链互动徽章
                        $comment_author_url = (string) $comment->comment_author_url;
                        if (!user_can($comment->user_id, 'manage_options') && lared_is_friend_link($comment_author_url)) : ?><span class="lared-friend-badge"><i class="fa-sharp fa-thin fa-circle-star"></i><span class="comment-tooltip"><?php esc_html_e('友链互动', 'lared'); ?></span></span><?php endif; ?><?php
                        // 评论等级徽章（昵称后面）
                        $comment_email = (string) $comment->comment_author_email;
                        if ('' !== $comment_email && !user_can($comment->user_id, 'manage_options')) {
                            $comment_stats = lared_get_user_comment_stats($comment_email);
                            echo lared_get_level_badge_simple($comment_stats['level']);
                        }
                        ?>
                    </span>
                    <span class="comment-metadata">
                        <?php
                        $comment_timestamp = get_comment_date('U', $comment);
                        $exact_time = get_comment_date('Y/m/d H:i', $comment);
                        $relative_time = sprintf(__('%s前', 'lared'), human_time_diff($comment_timestamp, current_time('timestamp')));
                        ?>
                        <time datetime="<?php comment_time('c'); ?>">
                            <i class="fa-regular fa-clock fa-fw comment-ua-icon" aria-hidden="true"></i><?php echo esc_html($relative_time); ?>
                            <span class="comment-tooltip"><?php echo esc_html($exact_time); ?></span>
                        </time>
                    </span>
                    <?php echo lared_render_comment_ua_geo($comment); ?>
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
 * 格式化大数字显示（如 12345 → "1.2万"）
 */
function lared_format_number(int $num): string
{
    if ($num >= 10000) {
        return round($num / 10000, 1) . '万';
    }
    return number_format($num);
}

/**
 * AJAX 自增首页访问量（存储在 wp_options: lared_home_views）
 * 使用 transient 节流（同一 IP 30 秒内只计一次）
 * 使用 $wpdb 原子自增
 */
function lared_track_home_views_ajax(): void
{
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $throttle_key = 'lared_hv_' . md5($ip);
    if (get_transient($throttle_key)) {
        $current = (int) get_option('lared_home_views', 0);
        wp_send_json_success(['tracked' => false, 'views' => $current]);
        return;
    }
    set_transient($throttle_key, 1, 30);

    global $wpdb;
    $wpdb->query(
        "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = 'lared_home_views'"
    );
    wp_cache_delete('lared_home_views', 'options');
    $current = (int) get_option('lared_home_views', 0);

    wp_send_json_success(['tracked' => true, 'views' => $current]);
}
add_action('wp_ajax_lared_track_home_views', 'lared_track_home_views_ajax');
add_action('wp_ajax_nopriv_lared_track_home_views', 'lared_track_home_views_ajax');

/**
 * AJAX 记录最近访客地理位置
 * 通过 ip.bluecdn.com 获取 IP 地理信息，存储到 wp_options: lared_last_visitor
 */
function lared_track_visitor_ajax(): void
{
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']));
        $ip = trim($ips[0]);
    } else {
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    }

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

    $last = get_option('lared_last_visitor', []);

    // 相同 IP 不重复调用 API
    if (!empty($last['ip']) && $last['ip'] === $ip) {
        $last['timestamp'] = time();
        update_option('lared_last_visitor', $last, false);
        wp_send_json_success($last);
        return;
    }

    // 调用 ip.bluecdn.com
    $api_url = 'https://ip.bluecdn.com/geoip/' . rawurlencode($ip);
    $response = wp_remote_get($api_url, [
        'timeout' => 5,
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
    wp_send_json_success($visitor_data);
}
add_action('wp_ajax_lared_track_visitor', 'lared_track_visitor_ajax');
add_action('wp_ajax_nopriv_lared_track_visitor', 'lared_track_visitor_ajax');

// ── 友链申请 AJAX ──
function lared_ajax_apply_friend_link() {
    if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'lared_friend_link_nonce' ) ) {
        wp_send_json_error( [ 'message' => '安全验证失败，请刷新页面重试' ] );
    }

    $name   = sanitize_text_field( $_POST['flink_name'] ?? '' );
    $url    = esc_url_raw( $_POST['flink_url'] ?? '' );
    $desc   = sanitize_text_field( $_POST['flink_desc'] ?? '' );
    $feed   = esc_url_raw( $_POST['flink_feed'] ?? '' );
    $avatar = esc_url_raw( $_POST['flink_avatar'] ?? '' );

    if ( empty( $name ) || empty( $url ) ) {
        wp_send_json_error( [ 'message' => '站点名称和网址为必填项' ] );
    }

    // 检查是否已存在相同 URL 的链接
    $existing = get_bookmarks( [ 'search' => $url ] );
    foreach ( $existing as $link ) {
        if ( trailingslashit( $link->link_url ) === trailingslashit( $url ) ) {
            wp_send_json_error( [ 'message' => '该站点已在友链列表中' ] );
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

    $result = wp_insert_link( $linkdata, true );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => '提交失败：' . $result->get_error_message() ] );
    }

    wp_send_json_success( [ 'message' => '提交成功！待站长审核后将显示在友链列表中。' ] );
}
add_action( 'wp_ajax_lared_apply_friend_link', 'lared_ajax_apply_friend_link' );
add_action( 'wp_ajax_nopriv_lared_apply_friend_link', 'lared_ajax_apply_friend_link' );
