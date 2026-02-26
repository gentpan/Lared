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
