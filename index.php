<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#fff]">
    <?php
    // Hero：四种文章类型（最新 / 热门 / 热评 / 随机）
    $hero_items = pan_get_hero_items();
    $hero_first = !empty($hero_items) ? $hero_items[0] : null;
    ?>

    <?php if ($hero_first !== null) : ?>
        <?php
        $hf_post_id   = $hero_first['post_id'];
        $hf_image_url = pan_get_post_image_url($hf_post_id, 'large');
        if ('' === $hf_image_url) {
            $hf_image_url = 'https://picsum.photos/1600/800?random=' . wp_rand(100000, 999999);
        }
        ?>
        <section class="w-full pb-0">
            <div class="hero-shell grid h-[400px] grid-cols-[260px_minmax(0,1fr)] max-[1024px]:h-auto max-[1024px]:grid-cols-1" style="--hero-item-count: <?php echo max(1, count($hero_items)); ?>;">

                <!-- 左侧：红色分类导航 -->
                <aside class="hero-sidebar order-1 flex h-full flex-col overflow-hidden bg-[#1f1f1f] max-[1024px]:order-2">
                    <?php foreach ($hero_items as $hi_index => $hi_item) : ?>
                        <?php
                        $hi_post_id   = $hi_item['post_id'];
                        $hi_image = pan_get_post_image_url($hi_post_id, 'large');
                        if ('' === $hi_image) {
                            $hi_image = 'https://picsum.photos/1600/800?random=' . wp_rand(100000, 999999);
                        }
                        $hi_active    = 0 === $hi_index;
                        ?>
                        <button
                            class="relative flex flex-1 w-full items-center gap-3 overflow-hidden px-5 text-white transition-colors <?php echo $hi_active ? 'is-hero-active' : ''; ?>"
                            type="button"
                            data-hero-item
                            data-hero-taxonomy="<?php echo esc_attr($hi_item['taxonomy'] ?? ''); ?>"
                            data-hero-term-id="<?php echo esc_attr((string) ($hi_item['term_id'] ?? 0)); ?>"
                            data-hero-title="<?php echo esc_attr(get_the_title($hi_post_id)); ?>"
                            data-hero-link="<?php echo esc_url($hi_item['item_url']); ?>"
                            data-hero-image="<?php echo esc_url($hi_image); ?>"
                            data-hero-badge-key="<?php echo esc_attr($hi_item['type_key']); ?>"
                            data-hero-badge-icon="<?php echo esc_attr($hi_item['type_icon']); ?>"
                            data-hero-badge="<?php echo esc_attr($hi_item['type_label']); ?>"
                            aria-pressed="<?php echo $hi_active ? 'true' : 'false'; ?>"
                        >
                            <span class="hero-item-label relative z-10 min-w-0 flex-1 truncate text-left"><?php echo esc_html($hi_item['cat_label']); ?><?php if ($hi_item['count'] > 0) : ?><span class="hero-item-count">(<?php echo esc_html((string) $hi_item['count']); ?>)</span><?php endif; ?></span>
                            <?php if ('' !== $hi_item['icon_html']) : ?>
                                <span class="hero-item-bg-icon" aria-hidden="true">
                                    <?php echo wp_kses_post($hi_item['icon_html']); ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </aside>

                <!-- 右侧：Banner 图 + 标题 + 文章类型 -->
                <article class="order-2 relative h-full overflow-hidden bg-[#10131b] max-[1024px]:order-1 max-[1024px]:min-h-[340px]">
                    <img
                        class="absolute inset-0 h-full w-full object-cover object-center transition-opacity duration-300 <?php echo '' === $hf_image_url ? 'opacity-0' : 'opacity-100'; ?>"
                        src="<?php echo esc_url($hf_image_url); ?>"
                        alt="<?php echo esc_attr(get_the_title($hf_post_id)); ?>"
                        data-hero-main-image
                    />
                    <div class="absolute inset-0 h-full w-full bg-[linear-gradient(135deg,#1f2433,#0c0f17)] <?php echo '' !== $hf_image_url ? 'hidden' : ''; ?>" aria-hidden="true" data-hero-main-fallback></div>
                    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(5,8,14,0.08)_0%,rgba(5,8,14,0.65)_75%,rgba(5,8,14,0.85)_100%)]"></div>

                    <div class="absolute inset-x-0 bottom-0 z-10 text-right text-white max-[900px]:text-right">
                        <div class="hero-title-frost w-full">
                            <div class="hero-title-row">
                                <h2 class="hero-main-title-wrap m-0 text-[40px] font-semibold leading-[1.06]">
                                    <a class="text-inherit no-underline" href="<?php echo esc_url($hero_first['item_url']); ?>" data-hero-main-link>
                                        <span class="hero-main-title" data-hero-main-title><?php echo esc_html(get_the_title($hf_post_id)); ?></span>
                                    </a>
                                </h2>
                                <span class="hero-type-badge" data-hero-main-badge data-hero-main-badge-key="<?php echo esc_attr($hero_first['type_key']); ?>"><?php echo esc_html($hero_first['type_label']); ?></span>
                            </div>
                        </div>
                    </div>
                </article>

            </div>
        </section>
    <?php endif; ?>

    <?php
    $heatmap_days_total = 60;
    $heatmap_day_counts = [];
    $heatmap_post_ids = get_posts([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'fields'              => 'ids',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'date_query'          => [
            [
                'after'     => ($heatmap_days_total - 1) . ' days ago',
                'inclusive' => true,
            ],
        ],
    ]);
    foreach ($heatmap_post_ids as $heatmap_post_id) {
        $day_key = get_the_date('Y-m-d', (int) $heatmap_post_id);
        if ('' === $day_key) {
            continue;
        }
        if (!isset($heatmap_day_counts[$day_key])) {
            $heatmap_day_counts[$day_key] = 0;
        }
        $heatmap_day_counts[$day_key]++;
    }
    $heatmap_max_count = !empty($heatmap_day_counts) ? max($heatmap_day_counts) : 0;
    $home_heatmap_cells = [];
    for ($i = $heatmap_days_total - 1; $i >= 0; $i--) {
        $cell_ts = strtotime('-' . $i . ' days', current_time('timestamp'));
        if (false === $cell_ts) {
            continue;
        }
        $cell_date = wp_date('Y-m-d', $cell_ts);
        $cell_count = (int) ($heatmap_day_counts[$cell_date] ?? 0);
        $cell_level = 0;
        if ($cell_count > 0 && $heatmap_max_count > 0) {
            $cell_level = (int) ceil(($cell_count / $heatmap_max_count) * 4);
            $cell_level = max(1, min(4, $cell_level));
        }
        $home_heatmap_cells[] = [
            'date' => $cell_date,
            'count' => $cell_count,
            'level' => $cell_level,
        ];
    }

    $latest_memo_item = null;
    $latest_memo_stream = function_exists('pan_get_memos_stream')
        ? pan_get_memos_stream([
            'cache_ttl' => 300,
            'page_size' => 1,
        ])
        : ['items' => []];
    if (!empty($latest_memo_stream['items']) && is_array($latest_memo_stream['items'])) {
        $latest_memo_item = $latest_memo_stream['items'][0];
    }

    $memos_page_url = '';
    $memos_page_ids = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'templates/page-memos.php',
        'no_found_rows'  => true,
    ]);
    if (empty($memos_page_ids)) {
        $memos_page_ids = get_posts([
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'template-memos-page.php',
            'no_found_rows'  => true,
        ]);
    }
    if (!empty($memos_page_ids)) {
        $memos_page_url = get_permalink((int) $memos_page_ids[0]) ?: '';
    }
    if ('' === $memos_page_url) {
        $memos_page_url = home_url('/memos/');
    }

    $posts_per_page = (int) get_option('posts_per_page');
    if ($posts_per_page <= 0) {
        $posts_per_page = 10;
    }

    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

    $featured_query = new WP_Query([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $posts_per_page,
        'paged'               => $paged,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
    ]);

    $featured_posts = $featured_query->posts;
    // 热门文章：整站文章按总浏览量排序
    $popular_posts = get_posts([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 10,
        'orderby'             => [
            'meta_value_num' => 'DESC',
            'date' => 'DESC',
        ],
        'meta_key'            => 'post_views',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ]);

    // 最新文章
    $latest_sidebar_posts = get_posts([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 10,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ]);

    // 随机文章
    $random_sidebar_posts = get_posts([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 10,
        'orderby'             => 'rand',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ]);
    $latest_comments = get_comments([
        'status'      => 'approve',
        'number'      => 15,
        'type'        => 'comment',
        'post_status' => 'publish',
    ]);
    $popular_tags = get_tags([
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 16,
        'hide_empty' => true,
    ]);
    $site_days = function_exists('pan_get_site_running_days_from_first_post')
        ? pan_get_site_running_days_from_first_post()
        : 0;
    $post_count = (int) wp_count_posts('post')->publish;
    $category_count = (int) wp_count_terms([
        'taxonomy'   => 'category',
        'hide_empty' => true,
    ]);
    $comment_totals = wp_count_comments();
    $approved_comment_count = isset($comment_totals->approved) ? (int) $comment_totals->approved : 0;
    ?>

    <?php if (is_array($latest_memo_item) && !empty($latest_memo_item)) : ?>
        <?php
        $home_memo_excerpt = trim((string) ($latest_memo_item['excerpt'] ?? ''));
        $home_memo_time = (int) ($latest_memo_item['created_timestamp'] ?? 0);
        $home_memo_time_text = $home_memo_time > 0 ? wp_date('Y/m/d H:i', $home_memo_time) : '';
        ?>
        <section class="home-memo-strip" aria-label="<?php esc_attr_e('最新 Memos', 'pan'); ?>">
            <a class="home-memo-strip-link" href="<?php echo esc_url($memos_page_url); ?>">
                <span class="home-memo-strip-bird-track" aria-hidden="true">
                    <span class="home-memo-strip-icon">
                        <i class="fa-solid fa-dove"></i>
                    </span>
                </span>
                <span class="home-memo-strip-main">
                    <span class="home-memo-strip-content"><?php echo esc_html($home_memo_excerpt); ?></span>
                    <?php if ('' !== $home_memo_time_text) : ?>
                        <time class="home-memo-strip-time"><?php echo esc_html($home_memo_time_text); ?></time>
                    <?php endif; ?>
                </span>
            </a>
        </section>
    <?php endif; ?>

    <?php if (!empty($featured_posts)) : ?>
        <div class="home-main-layout">
            <aside class="home-main-sidebar" aria-label="<?php esc_attr_e('首页侧边栏', 'pan'); ?>">
                <div class="home-main-sidebar-inner">
                    <section class="home-main-sidebar-block home-main-sidebar-block--welcome">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-house-chimney" aria-hidden="true"></i><span><?php esc_html_e('欢迎板块', 'pan'); ?></span></h3>
                        </div>
                        <div class="home-main-sidebar-block-body">
                            <p><?php echo esc_html(get_bloginfo('description') ?: __('欢迎来到我的博客，记录技术与日常。', 'pan')); ?></p>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block home-main-sidebar-block--heatmap home-main-sidebar-block--no-title">
                        <div class="home-main-sidebar-block-body">
                            <div class="home-mini-heatmap" aria-label="<?php esc_attr_e('近60天更新热力图', 'pan'); ?>">
                                <?php foreach ($home_heatmap_cells as $heatmap_cell) : ?>
                                    <span
                                        class="home-mini-heatmap-cell tone-green level-<?php echo esc_attr((string) $heatmap_cell['level']); ?>"
                                        title="<?php echo esc_attr((string) $heatmap_cell['date'] . ' · ' . (int) $heatmap_cell['count'] . ' 篇'); ?>"
                                    ></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block home-main-sidebar-block--popular home-main-sidebar-block--no-title">
                        <div class="home-sidebar-tabs">
                            <button type="button" class="home-sidebar-tab is-active" data-tab="latest"><?php esc_html_e('最新日志', 'pan'); ?></button>
                            <button type="button" class="home-sidebar-tab" data-tab="popular"><?php esc_html_e('热评日志', 'pan'); ?></button>
                            <button type="button" class="home-sidebar-tab" data-tab="random"><?php esc_html_e('随机日志', 'pan'); ?></button>
                        </div>
                        <div class="home-sidebar-tab-content">
                            <div class="home-sidebar-tab-panel is-active" data-panel="latest">
                                <?php if (!empty($latest_sidebar_posts)) : ?>
                                    <ul class="home-main-sidebar-list home-main-sidebar-list-popular">
                                        <?php foreach ($latest_sidebar_posts as $sidebar_post) : ?>
                                            <li class="home-main-popular-item">
                                                <a href="<?php echo esc_url(get_permalink($sidebar_post->ID)); ?>">
                                                    <span class="home-main-popular-title"><?php echo esc_html(get_the_title($sidebar_post->ID)); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p><?php esc_html_e('暂无文章。', 'pan'); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="home-sidebar-tab-panel" data-panel="popular">
                                <?php if (!empty($popular_posts)) : ?>
                                    <ul class="home-main-sidebar-list home-main-sidebar-list-popular">
                                        <?php foreach ($popular_posts as $popular_post) : ?>
                                            <li class="home-main-popular-item">
                                                <a href="<?php echo esc_url(get_permalink($popular_post->ID)); ?>">
                                                    <span class="home-main-popular-title"><?php echo esc_html(get_the_title($popular_post->ID)); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p><?php esc_html_e('暂无热门文章。', 'pan'); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="home-sidebar-tab-panel" data-panel="random">
                                <?php if (!empty($random_sidebar_posts)) : ?>
                                    <ul class="home-main-sidebar-list home-main-sidebar-list-popular">
                                        <?php foreach ($random_sidebar_posts as $random_post) : ?>
                                            <li class="home-main-popular-item">
                                                <a href="<?php echo esc_url(get_permalink($random_post->ID)); ?>">
                                                    <span class="home-main-popular-title"><?php echo esc_html(get_the_title($random_post->ID)); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p><?php esc_html_e('暂无文章。', 'pan'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block home-main-sidebar-block--comments">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-comments" aria-hidden="true"></i><span><?php esc_html_e('最新评论', 'pan'); ?></span></h3>
                        </div>
                        <div class="home-main-sidebar-block-body">
                            <?php if (!empty($latest_comments)) : ?>
                                <ul class="home-main-sidebar-list home-main-sidebar-list-comments">
                                    <?php foreach ($latest_comments as $latest_comment) : ?>
                                        <?php
                                        $comment_author = get_comment_author($latest_comment);
                                        $comment_post_title = get_the_title((int) $latest_comment->comment_post_ID);
                                        $comment_excerpt = wp_trim_words(wp_strip_all_tags($latest_comment->comment_content), 18, '…');
                                        $comment_time = get_comment_date('Y/m/d H:i', $latest_comment);
                                        $comment_visitor_url = trim((string) get_comment_author_url($latest_comment));
                                        $comment_target_url = '' !== $comment_visitor_url
                                            ? $comment_visitor_url
                                            : get_comment_link($latest_comment);
                                        ?>
                                        <li>
                                            <a
                                                class="home-main-comment-link"
                                                href="<?php echo esc_url($comment_target_url); ?>"
                                            >
                                                <span class="home-main-comment-avatar">
                                                    <?php echo wp_kses_post(get_avatar($latest_comment, 32, '', '', ['class' => 'home-main-comment-avatar-img'])); ?>
                                                </span>
                                                <span class="home-main-comment-tooltip" role="tooltip">
                                                    <span class="home-main-comment-tooltip-head">
                                                        <strong class="home-main-comment-tooltip-author"><?php echo esc_html($comment_author ?: __('匿名', 'pan')); ?></strong>
                                                        <time class="home-main-comment-tooltip-time"><?php echo esc_html($comment_time); ?></time>
                                                    </span>
                                                    <span class="home-main-comment-tooltip-content"><?php echo esc_html($comment_excerpt); ?></span>
                                                    <span class="home-main-comment-tooltip-post"><?php echo esc_html($comment_post_title); ?></span>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p><?php esc_html_e('暂无评论。', 'pan'); ?></p>
                            <?php endif; ?>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-tags" aria-hidden="true"></i><span><?php esc_html_e('关键词', 'pan'); ?></span></h3>
                        </div>
                        <div class="home-main-sidebar-block-body">
                            <?php if (!empty($popular_tags)) : ?>
                                <div class="home-main-sidebar-tags">
                                    <?php 
                                    $tag_colors = ['red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'pink'];
                                    $color_index = 0;
                                    foreach ($popular_tags as $tag) : 
                                        $color_class = $tag_colors[$color_index % count($tag_colors)];
                                        $color_index++;
                                    ?>
                                        <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="tag-<?php echo esc_attr($color_class); ?>"><?php echo esc_html($tag->name); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <p><?php esc_html_e('暂无关键词。', 'pan'); ?></p>
                            <?php endif; ?>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-chart-column" aria-hidden="true"></i><span><?php esc_html_e('统计信息', 'pan'); ?></span></h3>
                        </div>
                        <div class="home-main-sidebar-block-body">
                            <dl class="home-main-sidebar-stats">
                                <div>
                                    <dt><?php esc_html_e('文章', 'pan'); ?></dt>
                                    <dd><?php echo esc_html((string) $post_count); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('评论', 'pan'); ?></dt>
                                    <dd><?php echo esc_html((string) $approved_comment_count); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('分类', 'pan'); ?></dt>
                                    <dd><?php echo esc_html((string) $category_count); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('建站天数', 'pan'); ?></dt>
                                    <dd><?php echo esc_html((string) $site_days); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </section>
                </div>
            </aside>

            <div class="home-main-feed">
        <?php foreach ($featured_posts as $post_index => $featured_post) : ?>
            <?php
            $post_id = (int) $featured_post->ID;

            // 获取完整分类对象，用于提取图标
            $post_categories  = wp_get_post_categories($post_id, ['fields' => 'all']);
            $first_category   = !empty($post_categories) ? $post_categories[0] : null;
            $category_label   = $first_category ? $first_category->name : __('未分类', 'pan');
            $category_icon_html = ($first_category && $first_category->term_id > 0)
                ? pan_get_category_icon_html((int) $first_category->term_id)
                : '';

            $post_timestamp = (int) get_post_time('U', true, $post_id);
            if ($post_timestamp <= 0) {
                $post_timestamp = time();
            }
            $post_month_short = wp_date('M', $post_timestamp);
            $post_day_number  = wp_date('j', $post_timestamp);
            $post_time_full   = wp_date('Y/m/d H:i', $post_timestamp);
            $article_image_url = pan_get_post_image_url($post_id, 'large');
            if ('' === $article_image_url) {
                $article_image_url = 'https://picsum.photos/seed/pan-post-' . $post_id . '/1600/900';
            }
            $article_excerpt_raw = has_excerpt($post_id)
                ? get_the_excerpt($post_id)
                : wp_strip_all_tags((string) $featured_post->post_content);
            $article_excerpt_text = trim(wp_strip_all_tags((string) $article_excerpt_raw));
            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                $article_excerpt = mb_strlen($article_excerpt_text, 'UTF-8') > 150
                    ? mb_substr($article_excerpt_text, 0, 150, 'UTF-8') . '…'
                    : $article_excerpt_text;
            } else {
                $article_excerpt = strlen($article_excerpt_text) > 150
                    ? substr($article_excerpt_text, 0, 150) . '…'
                    : $article_excerpt_text;
            }
            ?>

            <section class="home-article <?php echo 0 === $post_index ? 'is-first' : ''; ?>" id="home-article-<?php echo (int) $post_id; ?>">
                <article class="home-article-content">
                    <div class="home-article-head">
                        <span class="home-article-time" tabindex="0" aria-label="<?php echo esc_attr($post_time_full); ?>">
                            <span class="home-article-time-month"><?php echo esc_html($post_month_short); ?></span>
                            <span class="home-article-time-day"><?php echo esc_html($post_day_number); ?></span>
                            <span class="home-article-time-tooltip"><?php echo esc_html($post_time_full); ?></span>
                        </span>

                        <h2>
                            <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                                <?php echo esc_html(get_the_title($post_id)); ?>
                            </a>
                        </h2>

                        <span class="home-article-head-label">
                            <?php if ('' !== $category_icon_html) : ?>
                                <span class="category-icon" aria-hidden="true"><?php echo $category_icon_html; ?></span>
                            <?php endif; ?>
                            <span><?php echo esc_html($category_label); ?></span>
                        </span>
                    </div>

                    <div class="home-article-featured">
                        <img
                            class="home-article-featured-image"
                            src="<?php echo esc_url($article_image_url); ?>"
                            alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                            loading="lazy"
                            decoding="async"
                        />
                    </div>

                    <div class="home-article-body-wrap">
                        <div class="home-article-body page-content prose prose-neutral max-w-none">
                            <p><?php echo esc_html($article_excerpt); ?></p>
                        </div>
                    </div>
                </article>
            </section>
        <?php endforeach; ?>

        <?php
        $pagination_links = paginate_links([
            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format'    => '?paged=%#%',
            'total'     => (int) $featured_query->max_num_pages,
            'current'   => $paged,
            'mid_size'  => 2,
            'prev_text' => '&larr; ' . __('上一页', 'pan'),
            'next_text' => __('下一页', 'pan') . ' &rarr;',
            'type'      => 'array',
        ]);
        ?>

        <?php if (!empty($pagination_links)) : ?>
            <div class="pan-pagination pan-pagination--home">
                <div class="nav-links">
                    <?php foreach ($pagination_links as $pagination_link) : ?>
                        <?php echo wp_kses_post($pagination_link); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php
wp_reset_postdata();
get_footer();
