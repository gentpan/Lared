<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="main-shell mx-auto w-full max-w-[1400px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#fff]">
    <?php
    // Hero：四种文章类型（最新 / 热门 / 热评 / 随机）
    $hero_items = lared_get_hero_items();
    $hero_first = !empty($hero_items) ? $hero_items[0] : null;
    ?>

    <?php if ($hero_first !== null) : ?>
        <?php
        $hf_start_art = ($hero_first['articles'] ?? [])[$hero_first['start'] ?? 0] ?? [];
        $hf_post_id   = $hf_start_art['post_id'] ?? ($hero_first['post_id'] ?? 0);
        $hf_image_url = $hf_start_art['image'] ?? '';
        ?>
        <section class="w-full pb-0">
            <div class="hero-shell" style="--hero-item-count: <?php echo max(1, count($hero_items)); ?>;">

                <!-- 左侧：红色分类导航 -->
                <aside class="hero-sidebar">
                    <?php foreach ($hero_items as $hi_index => $hi_item) : ?>
                        <?php
                        $hi_articles  = $hi_item['articles'] ?? [];
                        $hi_start     = $hi_item['start'] ?? 0;
                        $hi_start_art = $hi_articles[$hi_start] ?? [];
                        $hi_post_id   = $hi_start_art['post_id'] ?? ($hi_item['post_id'] ?? 0);
                        $hi_image     = $hi_start_art['image'] ?? '';
                        $hi_active    = 0 === $hi_index;
                        ?>
                        <button
                            class="relative flex flex-1 w-full items-center gap-3 overflow-hidden px-5 text-white transition-colors <?php echo $hi_active ? 'is-hero-active' : ''; ?>"
                            type="button"
                            data-hero-item
                            data-hero-taxonomy="<?php echo esc_attr($hi_item['taxonomy'] ?? ''); ?>"
                            data-hero-term-id="<?php echo esc_attr((string) ($hi_item['term_id'] ?? 0)); ?>"
                            data-hero-title="<?php echo esc_attr($hi_start_art['title'] ?? ''); ?>"
                            data-hero-link="<?php echo esc_url($hi_item['item_url']); ?>"
                            data-hero-image="<?php echo esc_url($hi_image); ?>"
                            data-hero-badge-key="<?php echo esc_attr($hi_item['type_key']); ?>"
                            data-hero-badge-icon="<?php echo esc_attr($hi_item['type_icon']); ?>"
                            data-hero-badge="<?php echo esc_attr($hi_item['type_label']); ?>"
                            data-hero-articles="<?php echo esc_attr(wp_json_encode($hi_articles)); ?>"
                            data-hero-start="<?php echo (int) $hi_start; ?>"
                            aria-pressed="<?php echo $hi_active ? 'true' : 'false'; ?>">
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
                <article class="hero-banner" data-hero-current-post-id="<?php echo (int) $hf_post_id; ?>">
                    <img
                        class="absolute inset-0 h-full w-full object-cover object-center"
                        src="<?php echo esc_url($hf_image_url); ?>"
                        alt="<?php echo esc_attr(html_entity_decode(get_the_title($hf_post_id), ENT_QUOTES, 'UTF-8')); ?>"
                        data-hero-main-image />
                    <div class="absolute inset-0 h-full w-full bg-[linear-gradient(135deg,#1f2433,#0c0f17)] <?php echo '' !== $hf_image_url ? 'hidden' : ''; ?>" aria-hidden="true" data-hero-main-fallback></div>
                    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(5,8,14,0.08)_0%,rgba(5,8,14,0.65)_75%,rgba(5,8,14,0.85)_100%)]"></div>

                    <div class="absolute inset-x-0 bottom-0 z-10 text-right text-white max-[900px]:text-right">
                        <div class="hero-title-frost w-full">
                            <div class="hero-title-row">
                                <h2 class="hero-main-title-wrap m-0 text-[40px] font-semibold leading-[1.06]">
                                    <a class="text-inherit no-underline" href="<?php echo esc_url($hero_first['item_url']); ?>" data-hero-main-link>
                                        <span class="hero-main-title" data-hero-main-title><?php echo esc_html(html_entity_decode(get_the_title($hf_post_id), ENT_QUOTES, 'UTF-8')); ?></span>
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
    // 热力图数据（文件缓存 uploads/lared-cache/heatmap/，1 小时过期）
    $home_heatmap_cells = lared_get_heatmap_cache();
    if (false === $home_heatmap_cells) {
        $home_heatmap_cells = lared_build_heatmap_cache();
    }

    $latest_memo_item = null;
    $latest_memo_stream = function_exists('lared_get_memos_json_cache')
        ? lared_get_memos_json_cache()
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
    // 热门文章：整站文章按总浏览量排序（1 小时缓存）
    $popular_posts = get_transient('lared_popular_posts');
    if (false === $popular_posts) {
        $popular_posts = get_posts([
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => 5,
            'orderby'             => [
                'meta_value_num' => 'DESC',
                'date' => 'DESC',
            ],
            'meta_key'            => 'post_views',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ]);
        set_transient('lared_popular_posts', $popular_posts, HOUR_IN_SECONDS);
    }

    // 最新文章（1 小时缓存）
    $latest_sidebar_posts = get_transient('lared_latest_sidebar_posts');
    if (false === $latest_sidebar_posts) {
        $latest_sidebar_posts = get_posts([
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => 5,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ]);
        set_transient('lared_latest_sidebar_posts', $latest_sidebar_posts, HOUR_IN_SECONDS);
    }

    // 随机文章（1 小时缓存 — 避免 ORDER BY RAND() 每次加载）
    $random_sidebar_posts = get_transient('lared_random_sidebar_posts');
    if (false === $random_sidebar_posts) {
        $random_sidebar_posts = get_posts([
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => 5,
            'orderby'             => 'rand',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ]);
        set_transient('lared_random_sidebar_posts', $random_sidebar_posts, HOUR_IN_SECONDS);
    }
    $home_sidebar_categories_visible = '1' === (string) get_option('lared_home_sidebar_categories_visible', '1');
    $home_sidebar_categories = [];
    if ($home_sidebar_categories_visible) {
        $home_sidebar_categories = get_terms([
            'taxonomy'   => 'category',
            'hide_empty' => true,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => 10,
        ]);
        if (is_wp_error($home_sidebar_categories) || !is_array($home_sidebar_categories)) {
            $home_sidebar_categories = [];
        }
    }
    // 最新评论（1 小时缓存）
    $latest_comments = get_transient('lared_latest_comments');
    if (false === $latest_comments) {
        $latest_comments = get_comments([
            'status'           => 'approve',
            'number'           => 5,
            'type'             => 'comment',
            'post_status'      => 'publish',
            'author__not_in'   => lared_get_admin_user_ids(),
        ]);
        set_transient('lared_latest_comments', $latest_comments, HOUR_IN_SECONDS);
    }

    // 热门标签（1 小时缓存）
    $popular_tags = get_transient('lared_popular_tags');
    if (false === $popular_tags) {
        $popular_tags = get_tags([
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => 16,
            'hide_empty' => true,
        ]);
        set_transient('lared_popular_tags', $popular_tags, HOUR_IN_SECONDS);
    }

    $site_days = function_exists('lared_get_site_running_days_from_first_post')
        ? lared_get_site_running_days_from_first_post()
        : 0;

    // 站点统计数据（1 小时缓存 — 合并多个计数查询）
    $site_stats = get_transient('lared_site_stats');
    if (false === $site_stats) {
        $comment_totals = wp_count_comments();
        $site_stats = [
            'post_count'             => (int) wp_count_posts('post')->publish,
            'approved_comment_count' => (int) ($comment_totals->approved ?? 0),
            'total_words'            => (int) $wpdb->get_var(
                "SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE pm.meta_key = '_word_count' AND p.post_status = 'publish' AND p.post_type = 'post'"
            ),
        ];
        set_transient('lared_site_stats', $site_stats, HOUR_IN_SECONDS);
    }
    $post_count = $site_stats['post_count'];
    $approved_comment_count = $site_stats['approved_comment_count'];
    $total_words = $site_stats['total_words'];
    ?>

    <?php
    // 音乐播放器数据
    $lared_music_raw    = trim((string) get_option('lared_music_playlist', ''));
    $lared_music_tracks = [];
    if ('' !== $lared_music_raw) {
        $lared_music_lines = array_filter(array_map('trim', explode("\n", $lared_music_raw)));
        foreach ($lared_music_lines as $line) {
            $parts = array_map('trim', explode('|', $line, 3));
            if (count($parts) >= 2 && '' !== $parts[1]) {
                $lared_music_tracks[] = [
                    'name' => $parts[0],
                    'url'  => $parts[1],
                    'lrc'  => $parts[2] ?? '',
                ];
            }
        }
    }
    $has_music = !empty($lared_music_tracks);

    // Memo 数据
    $has_memo = is_array($latest_memo_item) && !empty($latest_memo_item);
    if ($has_memo) {
        $home_memo_excerpt = trim((string) ($latest_memo_item['excerpt'] ?? ''));
        $home_memo_time = (int) ($latest_memo_item['created_timestamp'] ?? 0);
        $home_memo_time_text = $home_memo_time > 0
            ? sprintf(__('%s前', 'lared'), human_time_diff($home_memo_time, time()))
            : '';
    }
    ?>

    <?php if ($has_music || $has_memo) : ?>
        <section class="home-memo-strip" aria-label="<?php esc_attr_e('最新 Memos', 'lared'); ?>">
            <div class="home-memo-strip-link">
                <div class="home-memo-strip-bird-track">
                    <?php if ($has_music) : ?>
                        <div class="lared-music-player" id="lared-music-player" data-tracks="<?php echo esc_attr(wp_json_encode($lared_music_tracks)); ?>">
                            <div class="lared-music-controls" data-music="controls">
                                <button type="button" class="lared-music-btn" data-music="prev" title="<?php esc_attr_e('上一首', 'lared'); ?>">
                                    <i class="fa-solid fa-backward-step" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="lared-music-btn" data-music="toggle" title="<?php esc_attr_e('播放/暂停', 'lared'); ?>">
                                    <i class="fa-solid fa-play" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="lared-music-btn" data-music="next" title="<?php esc_attr_e('下一首', 'lared'); ?>">
                                    <i class="fa-solid fa-forward-step" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="lared-music-viz" data-music="viz">
                                <span class="lared-music-bar"></span>
                                <span class="lared-music-bar"></span>
                                <span class="lared-music-bar"></span>
                                <span class="lared-music-bar"></span>
                                <span class="lared-music-bar"></span>
                                <span class="lared-music-time" data-music="time">0:00</span>
                            </div>
                            <span class="lared-music-track-name" data-music="name"><?php echo esc_html($lared_music_tracks[0]['name']); ?></span>
                            <div class="lared-music-progress" data-music="progress">
                                <div class="lared-music-progress-fill" data-music="progress-fill"></div>
                                <div class="lared-music-progress-dot" data-music="progress-dot"></div>
                                <span class="lared-music-progress-tip" data-music="progress-tip"></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($has_memo) : ?>
                    <a class="home-memo-strip-main" href="<?php echo esc_url($memos_page_url); ?>">
                        <span class="home-memo-strip-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M23.643 4.937c-.835.37-1.732.62-2.675.733a4.67 4.67 0 0 0 2.048-2.578 9.3 9.3 0 0 1-2.958 1.13 4.66 4.66 0 0 0-7.938 4.25 13.229 13.229 0 0 1-9.602-4.868c-.4.69-.63 1.49-.63 2.342A4.66 4.66 0 0 0 3.96 9.824a4.647 4.647 0 0 1-2.11-.583v.06a4.66 4.66 0 0 0 3.737 4.568 4.692 4.692 0 0 1-2.104.08 4.661 4.661 0 0 0 4.352 3.234 9.348 9.348 0 0 1-5.786 1.995 9.5 9.5 0 0 1-1.112-.065 13.175 13.175 0 0 0 7.14 2.093c8.57 0 13.255-7.098 13.255-13.254 0-.2-.005-.402-.014-.602a9.47 9.47 0 0 0 2.323-2.41l.002-.003z" fill="#1DA1F2" />
                            </svg>
                        </span>
                        <span class="home-memo-strip-content"><?php echo esc_html($home_memo_excerpt); ?></span>
                        <?php if ('' !== $home_memo_time_text) : ?>
                            <time class="home-memo-strip-time"><?php echo esc_html($home_memo_time_text); ?></time>
                        <?php endif; ?>
                    </a>
                <?php else : ?>
                    <div class="home-memo-strip-main"></div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($featured_posts)) : ?>
        <div class="home-main-layout">
            <aside class="home-main-sidebar" aria-label="<?php esc_attr_e('首页侧边栏', 'lared'); ?>">
                <div class="home-main-sidebar-inner">
                    <section class="home-main-sidebar-block home-main-sidebar-block--welcome">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-house-chimney" aria-hidden="true"></i><span><?php esc_html_e('欢迎板块', 'lared'); ?></span></h3>
                        </div>
                        <div class="home-main-sidebar-block-body">
                            <p><?php echo esc_html(get_bloginfo('description') ?: __('欢迎来到我的博客，记录技术与日常。', 'lared')); ?></p>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block home-main-sidebar-block--heatmap home-main-sidebar-block--no-title">
                        <div class="home-main-sidebar-block-body">
                            <div class="home-mini-heatmap" aria-label="<?php esc_attr_e('近60天更新热力图', 'lared'); ?>">
                                <?php foreach ($home_heatmap_cells as $heatmap_cell) : ?>
                                    <?php
                                    $heatmap_title_parts = [$heatmap_cell['date']];
                                    if ($heatmap_cell['post_count'] > 0) {
                                        $heatmap_title_parts[] = $heatmap_cell['post_count'] . ' 篇文章';
                                    }
                                    if ($heatmap_cell['memo_count'] > 0) {
                                        $heatmap_title_parts[] = $heatmap_cell['memo_count'] . ' 条说说';
                                    }
                                    if ($heatmap_cell['post_count'] === 0 && $heatmap_cell['memo_count'] === 0) {
                                        $heatmap_title_parts[] = '无更新';
                                    }
                                    ?>
                                    <span
                                        class="home-mini-heatmap-cell tone-red level-<?php echo esc_attr((string) $heatmap_cell['level']); ?>"
                                        title="<?php echo esc_attr(implode(' · ', $heatmap_title_parts)); ?>"></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block home-main-sidebar-block--popular home-main-sidebar-block--no-title">
                        <div class="home-sidebar-tabs">
                            <button type="button" class="home-sidebar-tab is-active" data-tab="latest"><i class="fa-regular fa-clock" aria-hidden="true"></i> <?php esc_html_e('最新日志', 'lared'); ?></button>
                            <button type="button" class="home-sidebar-tab" data-tab="popular"><i class="fa-regular fa-fire-flame-curved" aria-hidden="true"></i> <?php esc_html_e('热评日志', 'lared'); ?></button>
                            <button type="button" class="home-sidebar-tab" data-tab="random"><i class="fa-regular fa-shuffle" aria-hidden="true"></i> <?php esc_html_e('随机日志', 'lared'); ?></button>
                        </div>
                        <div class="home-sidebar-tab-content">
                            <div class="home-sidebar-tab-panel is-active" data-panel="latest">
                                <?php if (!empty($latest_sidebar_posts)) : ?>
                                    <ul class="home-main-sidebar-list home-main-sidebar-list-popular">
                                        <?php foreach ($latest_sidebar_posts as $lsi => $sidebar_post) : ?>
                                            <li class="home-main-popular-item">
                                                <a href="<?php echo esc_url(get_permalink($sidebar_post->ID)); ?>">
                                                    <span class="home-main-popular-num"><i class="fa-sharp fa-light fa-square-<?php echo ($lsi + 1); ?>" aria-hidden="true"></i></span>
                                                    <span class="home-main-popular-title"><?php echo esc_html(html_entity_decode(get_the_title($sidebar_post->ID), ENT_QUOTES, 'UTF-8')); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p><?php esc_html_e('暂无文章。', 'lared'); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="home-sidebar-tab-panel" data-panel="popular">
                                <?php if (!empty($popular_posts)) : ?>
                                    <ul class="home-main-sidebar-list home-main-sidebar-list-popular">
                                        <?php foreach ($popular_posts as $ppi => $popular_post) : ?>
                                            <li class="home-main-popular-item">
                                                <a href="<?php echo esc_url(get_permalink($popular_post->ID)); ?>">
                                                    <span class="home-main-popular-num"><i class="fa-sharp fa-light fa-square-<?php echo ($ppi + 1); ?>" aria-hidden="true"></i></span>
                                                    <span class="home-main-popular-title"><?php echo esc_html(html_entity_decode(get_the_title($popular_post->ID), ENT_QUOTES, 'UTF-8')); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p><?php esc_html_e('暂无热门文章。', 'lared'); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="home-sidebar-tab-panel" data-panel="random">
                                <?php if (!empty($random_sidebar_posts)) : ?>
                                    <ul class="home-main-sidebar-list home-main-sidebar-list-popular">
                                        <?php foreach ($random_sidebar_posts as $rsi => $random_post) : ?>
                                            <li class="home-main-popular-item">
                                                <a href="<?php echo esc_url(get_permalink($random_post->ID)); ?>">
                                                    <span class="home-main-popular-num"><i class="fa-sharp fa-light fa-square-<?php echo ($rsi + 1); ?>" aria-hidden="true"></i></span>
                                                    <span class="home-main-popular-title"><?php echo esc_html(html_entity_decode(get_the_title($random_post->ID), ENT_QUOTES, 'UTF-8')); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p><?php esc_html_e('暂无文章。', 'lared'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block home-main-sidebar-block--comments">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-comments" aria-hidden="true"></i><span><?php esc_html_e('最新评论', 'lared'); ?></span></h3>
                        </div>
                        <div class="home-main-sidebar-block-body">
                            <?php if (!empty($latest_comments)) : ?>
                                <ul class="home-main-sidebar-list home-sidebar-comments">
                                    <?php foreach ($latest_comments as $latest_comment) : ?>
                                        <?php
                                        $comment_author = get_comment_author($latest_comment);
                                        $comment_excerpt = wp_trim_words(wp_strip_all_tags($latest_comment->comment_content), 20, '…');
                                        $comment_link = get_comment_link($latest_comment);
                                        $comment_ago = human_time_diff(get_comment_date('U', $latest_comment), current_time('timestamp')) . __('前', 'lared');
                                        ?>
                                        <li class="home-sidebar-comment-item">
                                            <a class="home-sidebar-comment-link" href="<?php echo esc_url($comment_link); ?>">
                                                <span class="home-sidebar-comment-avatar">
                                                    <?php echo wp_kses_post(get_avatar($latest_comment, 40, '', '', ['class' => 'home-sidebar-comment-avatar-img'])); ?>
                                                </span>
                                                <span class="home-sidebar-comment-body">
                                                    <span class="home-sidebar-comment-head">
                                                        <strong class="home-sidebar-comment-author"><?php echo esc_html($comment_author ?: __('匿名', 'lared')); ?></strong>
                                                        <time class="home-sidebar-comment-time"><?php echo esc_html($comment_ago); ?></time>
                                                    </span>
                                                    <span class="home-sidebar-comment-text"><?php echo esc_html($comment_excerpt); ?></span>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p><?php esc_html_e('暂无评论。', 'lared'); ?></p>
                            <?php endif; ?>
                        </div>
                    </section>
                    <section class="home-main-sidebar-block">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-tags" aria-hidden="true"></i><span><?php esc_html_e('关键词', 'lared'); ?></span></h3>
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
                                <p><?php esc_html_e('暂无关键词。', 'lared'); ?></p>
                            <?php endif; ?>
                        </div>
                    </section>
                    <?php if ($home_sidebar_categories_visible) : ?>
                        <section class="home-main-sidebar-block home-main-sidebar-block--categories">
                            <div class="home-main-sidebar-block-title">
                                <h3><i class="fa-solid fa-folder-tree" aria-hidden="true"></i><span><?php esc_html_e('文章分类', 'lared'); ?></span></h3>
                            </div>
                            <div class="home-main-sidebar-block-body">
                                <?php if (!empty($home_sidebar_categories)) : ?>
                                    <ul class="home-main-sidebar-list home-main-sidebar-list-categories">
                                        <?php foreach ($home_sidebar_categories as $home_sidebar_category) : ?>
                                            <?php
                                            $home_sidebar_category_link = get_term_link($home_sidebar_category);
                                            if (is_wp_error($home_sidebar_category_link)) {
                                                continue;
                                            }
                                            $home_sidebar_category_icon = lared_get_category_icon_html((int) $home_sidebar_category->term_id);
                                            ?>
                                            <li>
                                                <a href="<?php echo esc_url($home_sidebar_category_link); ?>">
                                                    <span class="home-main-category-link-main">
                                                        <span class="home-main-category-icon" aria-hidden="true"><?php echo '' !== $home_sidebar_category_icon ? wp_kses_post($home_sidebar_category_icon) : '<i class="fa-regular fa-folder-open"></i>'; ?></span>
                                                        <span class="home-main-category-name"><?php echo esc_html($home_sidebar_category->name); ?></span>
                                                    </span>
                                                    <em>(<?php echo esc_html(number_format_i18n((int) $home_sidebar_category->count)); ?>)</em>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p><?php esc_html_e('暂无分类。', 'lared'); ?></p>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    <section class="home-main-sidebar-block">
                        <div class="home-main-sidebar-block-title">
                            <h3><i class="fa-solid fa-chart-column" aria-hidden="true"></i><span><?php esc_html_e('统计信息', 'lared'); ?></span></h3>
                        </div>
                        <div class="home-main-sidebar-block-body">
                            <dl class="home-main-sidebar-stats">
                                <div>
                                    <dt><?php esc_html_e('文章', 'lared'); ?></dt>
                                    <dd><?php echo esc_html((string) $post_count); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('评论', 'lared'); ?></dt>
                                    <dd><?php echo esc_html((string) $approved_comment_count); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('建站天数', 'lared'); ?></dt>
                                    <dd><?php echo esc_html((string) $site_days); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('全部字数', 'lared'); ?></dt>
                                    <dd><?php echo esc_html(number_format_i18n($total_words)); ?></dd>
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
                    $category_label   = $first_category ? $first_category->name : __('未分类', 'lared');
                    $category_link    = $first_category ? get_category_link($first_category->term_id) : '';
                    $category_count   = $first_category ? (int) $first_category->count : 0;
                    $category_icon_html = ($first_category && $first_category->term_id > 0)
                        ? lared_get_category_icon_html((int) $first_category->term_id)
                        : '';

                    $post_timestamp = (int) get_post_time('U', true, $post_id);
                    if ($post_timestamp <= 0) {
                        $post_timestamp = time();
                    }
                    $post_month_short = lared_date_en('M', $post_timestamp);
                    $post_day_number  = lared_date_en('j', $post_timestamp);
                    $post_time_full   = lared_date_en('Y/m/d H:i', $post_timestamp);
                    $article_image_url = lared_get_post_image_url($post_id, 'large');
                    if ('' === $article_image_url) {
                        $article_image_url = lared_get_landscape_image_url($post_id);
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
                                        <?php echo esc_html(html_entity_decode(get_the_title($post_id), ENT_QUOTES, 'UTF-8')); ?>
                                    </a>
                                    <?php if (1 === $paged && 0 === $post_index && (time() - $post_timestamp) < 7 * DAY_IN_SECONDS) : ?>
                                        <span class="home-article-new-badge"><i class="fa-sharp fa-thin fa-rectangle-new" aria-hidden="true"></i></span>
                                    <?php endif; ?>
                                </h2>

                                <?php
                                $comment_count = (int) get_comments_number($post_id);
                                $post_views    = (int) get_post_meta($post_id, 'post_views', true);
                                if ($post_views > 0 || $comment_count > 0) : ?>
                                    <span class="home-article-stats-panel">
                                        <span class="home-article-stats-row">
                                            <i class="fa-solid fa-fire" aria-hidden="true"></i>
                                            <span class="home-article-stats-value"><?php echo esc_html(number_format_i18n($post_views)); ?></span>
                                        </span>
                                        <span class="home-article-stats-row">
                                            <i class="fa-regular fa-comment" aria-hidden="true"></i>
                                            <span class="home-article-stats-value"><?php echo esc_html(number_format_i18n($comment_count)); ?></span>
                                        </span>
                                    </span>
                                <?php endif; ?>

                                <a class="home-article-head-label" href="<?php echo esc_url($category_link); ?>" tabindex="0" aria-label="<?php echo esc_attr($category_label); ?>">
                                    <?php if ('' !== $category_icon_html) : ?>
                                        <span class="category-icon" aria-hidden="true"><?php echo $category_icon_html; ?></span>
                                    <?php endif; ?>
                                    <span><?php echo esc_html($category_label); ?></span>
                                    <span class="home-article-label-tooltip"><?php
                                                                                /* translators: %d: number of posts in category */
                                                                                echo esc_html(sprintf(__('%d 篇文章', 'lared'), $category_count));
                                                                                ?></span>
                                </a>
                            </div>

                            <div class="home-article-featured">
                                <img
                                    class="home-article-featured-image lazyload"
                                    data-src="<?php echo esc_url($article_image_url); ?>"
                                    alt="<?php echo esc_attr(html_entity_decode(get_the_title($post_id), ENT_QUOTES, 'UTF-8')); ?>" />
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
                    'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                    'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                    'type'      => 'array',
                ]);
                ?>

                <?php if (!empty($pagination_links)) : ?>
                    <div class="lared-pagination lared-pagination--home">
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
