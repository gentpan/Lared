<?php
/**
 * Hero 区域逻辑
 * - 文章排序类型定义
 * - 获取 Hero 轮播数据
 * - AJAX 随机文章接口
 *
 * @package Lared
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hero 区域文章排序类型定义（最新/热门/热评/随机）
 * 被 lared_hero_fetch_four_articles 和 lared_hero_random_article 共同使用
 */
function lared_get_hero_type_defs(): array
{
    return [
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
}

/**
 * 为指定查询条件获取 4 种排序文章（最新/热门/热评/随机），每种取 1 篇。
 *
 * @param array $base_args WP_Query 基础参数（可含 tax_query / p 等），无需填写排序/条数。
 * @return array 最多 4 个元素，每个含 post_id/title/image/permalink/type_key/type_label/type_icon。
 */
function lared_hero_fetch_four_articles(array $base_args): array
{
    $type_defs = lared_get_hero_type_defs();

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
            $image = 'https://img.et/1200/600?r=' . wp_rand(1, 999999);
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

/**
 * AJAX: Hero 区域随机获取一篇文章（从 4 种排序方式中随机选一种）
 */
function lared_hero_random_article(): void
{
    // 公开只读接口，不强制 nonce（避免用户登录状态变化导致 403）

    $taxonomy   = sanitize_text_field(wp_unslash($_POST['taxonomy'] ?? ''));
    $term_id    = (int) ($_POST['term_id'] ?? 0);
    $exclude_id = (int) ($_POST['exclude_id'] ?? 0);

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

    // 排除当前正在显示的文章
    if ($exclude_id > 0) {
        $base_args['post__not_in'] = [$exclude_id];
    }

    // 四种排序方式
    $type_defs = lared_get_hero_type_defs();

    // 随机选一种排序方式
    $type = $type_defs[wp_rand(0, count($type_defs) - 1)];

    // 取多条再随机选一条，避免确定性排序总返回同一篇
    $query = array_merge($base_args, [
        'posts_per_page'      => 8,
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
        'orderby'             => $type['orderby'],
        'order'               => $type['order'],
    ]);
    if (isset($type['meta_key'])) {
        $query['meta_key'] = $type['meta_key'];
    }

    $posts = get_posts($query);

    if (empty($posts)) {
        // 兜底：全站随机（不排除）
        $posts = get_posts([
            'posts_per_page' => 5,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
        ]);
    }

    if (empty($posts)) {
        wp_send_json_error('no_posts');
    }

    // 从结果中随机选一篇
    $chosen = $posts[wp_rand(0, count($posts) - 1)];
    $pid    = (int) $chosen->ID;
    $image  = lared_get_post_image_url($pid, 'large');
    if ('' === $image) {
        $image = 'https://img.et/1200/600?r=' . wp_rand(1, 999999);
    }

    wp_send_json_success([
        'post_id'    => $pid,
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
