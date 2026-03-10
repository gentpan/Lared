<?php

/**
 * 图片处理模块
 * - 文章特色图片获取
 * - 分类图标
 * - FontAwesome 类提取
 * - API 图片获取
 * - 懒加载（lazysizes）
 * - 图片加载动画包装
 *
 * @package Lared
 */

if (!defined('ABSPATH')) {
    exit;
}

function lared_get_default_landscape_image_api(): string
{
    return 'https://img.et/1920/1080?type=landscape&format=avif';
}

function lared_get_landscape_image_url(int $post_id, string $api_url = ''): string
{
    $base_url = trim($api_url);
    if ('' === $base_url) {
        $base_url = lared_get_default_landscape_image_api();
    }

    $image_url = add_query_arg('r', (string) $post_id, $base_url);
    return esc_url_raw($image_url);
}

function lared_get_post_image_url(int $post_id, string $size = 'large'): string
{
    // 静态缓存 get_option 结果，避免循环内重复查询
    static $api_url = null;
    static $default_image = null;
    if (null === $api_url) {
        $api_url = (string) get_option('lared_featured_image_api', '');
    }
    if (null === $default_image) {
        $default_image = (string) get_option('lared_default_featured_image', '');
    }

    // 1. 优先使用文章特色图片
    if (has_post_thumbnail($post_id)) {
        $image_url = get_the_post_thumbnail_url($post_id, $size);
        if ($image_url) {
            return $image_url;
        }
    }

    // 2. 使用 API 获取图片（如果设置了 API）
    if ('' !== $api_url) {
        $api_image = lared_get_image_from_api($api_url, $post_id);
        if ('' !== $api_image) {
            return $api_image;
        }
    }

    // 3. 从文章内容提取图片
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

    // 4. 使用主题设置的默认图片
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
    if ($cat_id <= 0) {
        return '';
    }

    // 静态缓存：同一次请求中相同分类只查询一次
    static $icon_map = [];

    if (isset($icon_map[$cat_id])) {
        return $icon_map[$cat_id];
    }

    $icon_map[$cat_id] = '';

    // 从分类描述中提取 FontAwesome <i> 标签
    $term = get_term($cat_id);
    if ($term && !is_wp_error($term) && '' !== trim((string) $term->description)) {
        $desc = trim((string) $term->description);
        if (preg_match('/<i\s[^>]*class="([^"]*fa-[^"]*)"[^>]*>\s*<\/i>/i', $desc, $m)) {
            $icon_map[$cat_id] = '<i class="' . esc_attr($m[1]) . '" aria-hidden="true"></i>';
        }
    }

    return $icon_map[$cat_id];
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
            if (str_starts_with($cls, $prefix)) {
                $matched[] = $cls;
                break;
            }
        }
    }

    return implode(' ', $matched);
}

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
    if (str_contains($api_url, 'img.et/')) {
        return lared_get_landscape_image_url($post_id, $api_url);
    }

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

    $content_type = strtolower((string) wp_remote_retrieve_header($response, 'content-type'));
    if (str_starts_with($content_type, 'image/')) {
        $image_url = esc_url_raw($api_url);
        set_transient($cache_key, $image_url, DAY_IN_SECONDS);
        return $image_url;
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

// ====== 图片懒加载（lazysizes） ======

function lared_add_lazyload_to_images(string $content): string
{
    if (!get_option('lared_enable_lazyload', true)) {
        return $content;
    }

    return preg_replace_callback('/<img([^>]+)>/i', static function ($matches) {
        $img_tag = $matches[0];
        $attributes = $matches[1];

        // 跳过已有 lazyload 类的图片
        if (preg_match('/class=["\'][^"\']*lazyload/i', $attributes)) {
            return $img_tag;
        }

        // 跳过 emoji 和头像
        if (preg_match('/class=["\'][^"\']*(?:emoji|avatar)/i', $attributes)) {
            return $img_tag;
        }

        // 移除原生 loading="lazy"
        $img_tag = preg_replace('/\s*loading=["\']lazy["\']\s*/i', ' ', $img_tag);

        // src → data-src
        if (preg_match('/\ssrc\s*=\s*["\']([^"\']+)["\']/i', $img_tag, $src_match)) {
            $img_tag = str_replace($src_match[0], ' data-src="' . $src_match[1] . '"', $img_tag);
        }

        // srcset → data-srcset
        if (preg_match('/\ssrcset\s*=\s*["\']([^"\']+)["\']/i', $img_tag, $srcset_match)) {
            $img_tag = str_replace($srcset_match[0], ' data-srcset="' . $srcset_match[1] . '"', $img_tag);
        }

        // 添加 lazyload 类
        if (preg_match('/class=["\']([^"\']*)["\']/', $img_tag)) {
            $img_tag = preg_replace('/class=["\']([^"\']*)["\']/', 'class="$1 lazyload"', $img_tag);
        } else {
            $img_tag = str_replace('<img', '<img class="lazyload"', $img_tag);
        }

        return $img_tag;
    }, $content);
}
add_filter('the_content', 'lared_add_lazyload_to_images', 20);
add_filter('post_thumbnail_html', 'lared_add_lazyload_to_images', 20);

function lared_get_lazyload_attrs(): string
{
    if (!get_option('lared_enable_lazyload', true)) {
        return '';
    }
    return ' class="lazyload"';
}

// ====== 图片加载动画包装 ======

function lared_wrap_images_with_loader(string $content): string
{
    if (is_admin() || (!is_single() && !is_page())) {
        return $content;
    }

    // ── 第一步：保护 lared-grid-* 区块，不做 loading-wrapper 包装 ──
    $grid_placeholders = [];
    $content = preg_replace_callback(
        '/<div\s+class="lared-grid-[234]"[^>]*>.*?<\/div>/si',
        static function (array $m) use (&$grid_placeholders): string {
            $key = '<!--LARED_GRID_' . count($grid_placeholders) . '-->';
            $grid_placeholders[$key] = $m[0];
            return $key;
        },
        $content
    );

    // ── 第二步：对非 grid 区域的 img 做 loading-wrapper 包装 ──
    $content = preg_replace_callback(
        '/<img([^>]+)>/i',
        static function (array $matches): string {
            $img_tag = $matches[0];
            $attributes = $matches[1];

            if (preg_match('/class=["\'][^"\']*img-loading-target/i', $attributes)) {
                return $img_tag;
            }

            // 跳过 emoji 和头像
            if (preg_match('/class=["\']([^"\']*(emoji|avatar))/i', $attributes)) {
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

            // 添加 img-loading-target 类
            if (preg_match('/class=["\']([^"\']*)["\']/i', $attributes)) {
                $img_tag = preg_replace('/class=["\']([^"\']*)["\']/i', 'class="$1 img-loading-target"', $img_tag);
            } else {
                $img_tag = str_replace('<img', '<img class="img-loading-target"', $img_tag);
            }

            $wrapper = '<figure class="img-loading-wrapper"' . $aspect_style . '>';
            $wrapper .= '<div class="img-loading-spinner">';
            $wrapper .= '<div class="spinner-circle"></div>';
            $wrapper .= '</div>';
            $wrapper .= $img_tag;
            $wrapper .= '</figure>';

            return $wrapper;
        },
        $content
    );

    // ── 第三步：还原 grid 区块 ──
    foreach ($grid_placeholders as $key => $original) {
        $content = str_replace($key, $original, $content);
    }

    return $content;
}
add_filter('the_content', 'lared_wrap_images_with_loader', 25);

/* =====================================================================
   mshots — 外链悬浮截图预览
   使用 https://s0.wp.com/mshots/v1/ 服务
   适用：友情链接页面外链 + 文章/页面正文外链
   ===================================================================== */

/**
 * 生成 mshots 截图 URL
 *
 * @param string $url 目标网址
 */
function lared_get_mshot_url(string $url): string
{
    return 'https://s0.wp.com/mshots/v1/' . $url;
}

/**
 * 判断是否为外部 URL（与当前站点 host 不同即为外部）
 */
function lared_is_external_url(string $url): bool
{
    $home_host = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
    $link_host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));

    if ('' === $link_host) {
        return false;
    }

    return $link_host !== $home_host;
}

/**
 * 内容过滤器：为文章 / 页面正文中的外链添加 data-mshot-url
 */
function lared_mshots_content_filter(string $content): string
{
    if (is_admin() || wp_doing_ajax()) {
        return $content;
    }

    if (!is_singular()) {
        return $content;
    }

    return preg_replace_callback(
        '/<a\s([^>]*href=["\']([^"\']+)["\'][^>]*)>/i',
        static function (array $m): string {
            $tag  = $m[0];
            $href = $m[2];

            if (!preg_match('#^https?://#i', $href) || !lared_is_external_url($href)) {
                return $tag;
            }

            if (str_contains($tag, 'data-no-mshot')) {
                return $tag;
            }

            if (str_contains($tag, 'data-mshot-url')) {
                return $tag;
            }

            $mshot = lared_get_mshot_url($href);

            return str_replace('<a ', '<a data-mshot-url="' . esc_attr($mshot) . '" ', $tag);
        },
        $content
    ) ?? $content;
}
add_filter('the_content', 'lared_mshots_content_filter', 99);

/**
 * 输出 mshots 悬浮预览所需 CSS
 */
function lared_mshots_head_css(): void
{
    if (is_admin()) {
        return;
    }
?>
    <style id="lared-mshots-css">
        .mshot-preview {
            position: fixed;
            z-index: 99999;
            pointer-events: none;
            opacity: 0;
            transform: translateY(8px) scale(.97);
            transition: opacity .25s ease, transform .25s ease;
            background: #fff;
            border: 1px solid #d9d9d9;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .18);
            overflow: hidden;
            padding: 5px 5px 0;
            border-radius: 6px
        }

        .mshot-preview.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1)
        }

        .mshot-preview-imgbox {
            position: relative;
            width: 320px;
            height: 240px;
            border-radius: 4px;
            overflow: hidden;
            background: #f3f3f3
        }

        .mshot-preview-img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top left;
            opacity: 0;
            transition: opacity .3s ease
        }

        .mshot-preview-img.is-loaded {
            opacity: 1
        }

        .mshot-preview-spinner {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity .3s ease
        }

        .mshot-preview-spinner.is-hidden {
            opacity: 0;
            pointer-events: none
        }

        .mshot-preview-spinner::after {
            content: '';
            width: 28px;
            height: 28px;
            border: 2.5px solid #e0e0e0;
            border-top-color: #999;
            border-radius: 50%;
            animation: mshot-spin .7s linear infinite
        }

        @keyframes mshot-spin {
            to {
                transform: rotate(360deg)
            }
        }

        .mshot-preview-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 4px;
            max-width: 320px
        }

        .mshot-preview-bar i {
            font-size: 10px;
            color: #b0b0b0;
            flex-shrink: 0
        }

        .mshot-preview-url {
            font-size: 11px;
            color: #999;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.3;
            font-family: var(--font-mono, ui-monospace, SFMono-Regular, Menlo, monospace)
        }

        @media(hover:none) {
            .mshot-preview {
                display: none !important
            }
        }
    </style>
<?php
}
add_action('wp_head', 'lared_mshots_head_css', 90);

/**
 * 输出 mshots 悬浮预览 JS（事件委托，兼容 Pjax 动态内容）
 */
function lared_mshots_footer_js(): void
{
    if (is_admin()) {
        return;
    }
?>
    <script id="lared-mshots-js">
        (function() {
            function mshotInit() {
                if (document.getElementById('lared-mshot-preview')) return;
                if (!document.body) {
                    document.addEventListener('DOMContentLoaded', mshotInit);
                    return;
                }
                var box = document.createElement('div');
                box.id = 'lared-mshot-preview';
                box.className = 'mshot-preview';
                box.innerHTML =
                    '<div class="mshot-preview-imgbox">' +
                    '<div class="mshot-preview-spinner"></div>' +
                    '<img class="mshot-preview-img" alt="" referrerpolicy="no-referrer" />' +
                    '</div>' +
                    '<div class="mshot-preview-bar">' +
                    '<i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>' +
                    '<span class="mshot-preview-url"></span>' +
                    '</div>';
                document.body.appendChild(box);

                var imgBox = box.querySelector('.mshot-preview-imgbox');
                var spinner = box.querySelector('.mshot-preview-spinner');
                var img = box.querySelector('.mshot-preview-img');
                var urlEl = box.querySelector('.mshot-preview-url');
                var showT = null,
                    hideT = null,
                    curEl = null;

                function pos(e) {
                    var bw = 336,
                        bh = 296;
                    var x = e.clientX + 16,
                        y = e.clientY + 16;
                    if (x + bw > window.innerWidth) x = e.clientX - bw - 8;
                    if (y + bh > window.innerHeight) y = e.clientY - bh - 8;
                    if (x < 2) x = 2;
                    if (y < 2) y = 2;
                    box.style.left = x + 'px';
                    box.style.top = y + 'px';
                }

                function show(el) {
                    var u = el.getAttribute('data-mshot-url');
                    if (!u) return;
                    curEl = el;
                    urlEl.textContent = (el.getAttribute('href') || '').replace(/^https?:\/\//, '');

                    /* 重置状态：显示 spinner，隐藏图片 */
                    img.classList.remove('is-loaded');
                    spinner.classList.remove('is-hidden');
                    img.removeAttribute('src');

                    img.onload = function() {
                        img.classList.add('is-loaded');
                        spinner.classList.add('is-hidden');
                        img.removeAttribute('data-retry');
                    };
                    img.onerror = function() {
                        /* 首次失败 1.5s 后重试一次（mshots 可能还在生成） */
                        if (!img.getAttribute('data-retry')) {
                            img.setAttribute('data-retry', '1');
                            setTimeout(function() {
                                if (curEl === el) img.src = u;
                            }, 1500);
                        }
                    };

                    img.src = u;
                    box.classList.add('is-visible');
                }

                function hide() {
                    box.classList.remove('is-visible');
                    curEl = null;
                    setTimeout(function() {
                        if (!curEl) {
                            img.removeAttribute('src');
                            img.removeAttribute('data-retry');
                            img.classList.remove('is-loaded');
                            spinner.classList.remove('is-hidden');
                        }
                    }, 300);
                }

                function getTarget(e) {
                    return e.target ? e.target.closest('[data-mshot-url]') : null;
                }

                document.addEventListener('mouseover', function(e) {
                    var el = getTarget(e);
                    if (!el) return;
                    clearTimeout(hideT);
                    if (el === curEl && box.classList.contains('is-visible')) {
                        pos(e);
                        return;
                    }
                    pos(e);
                    clearTimeout(showT);
                    showT = setTimeout(function() {
                        show(el);
                    }, 200);
                }, true);

                document.addEventListener('mouseout', function(e) {
                    var el = getTarget(e);
                    if (!el) return;
                    var related = e.relatedTarget;
                    if (related && related.closest && related.closest('[data-mshot-url]') === el) return;
                    clearTimeout(showT);
                    hideT = setTimeout(hide, 150);
                }, true);

                document.addEventListener('mousemove', function(e) {
                    if (curEl || box.classList.contains('is-visible')) pos(e);
                });
            }
            mshotInit();
        })();
    </script>
<?php
}
add_action('wp_footer', 'lared_mshots_footer_js', 90);
