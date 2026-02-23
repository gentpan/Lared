<?php
/*
Template Name: 存档页面
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$archive_posts = get_posts([
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'numberposts'            => -1,
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'ignore_sticky_posts'    => true,
    'no_found_rows'          => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => true,
]);

$archive_tree = [];
$archive_total_chars = 0;
$archive_total_comments = 0;
$heatmap_counts = [];

foreach ($archive_posts as $archive_post) {
    if (!$archive_post instanceof WP_Post) {
        continue;
    }

    $post_id = (int) $archive_post->ID;
    $timestamp = (int) get_post_time('U', false, $post_id);
    if ($timestamp <= 0) {
        continue;
    }

    $year_key = wp_date('Y', $timestamp);
    $month_key = wp_date('m', $timestamp);

    if (!isset($archive_tree[$year_key])) {
        $archive_tree[$year_key] = [
            'count' => 0,
            'months' => [],
        ];
    }

    if (!isset($archive_tree[$year_key]['months'][$month_key])) {
        $archive_tree[$year_key]['months'][$month_key] = [
            'label' => wp_date('n月', $timestamp),
            'count' => 0,
            'posts' => [],
        ];
    }

    $p_categories = wp_get_post_categories($post_id, ['fields' => 'all']);
    $p_first_cat  = !empty($p_categories) ? $p_categories[0] : null;
    $p_cat_icon   = ($p_first_cat && $p_first_cat->term_id > 0)
        ? lared_get_category_icon_html((int) $p_first_cat->term_id)
        : '';
    $p_comments   = max(0, (int) $archive_post->comment_count);
    $p_views      = function_exists('lared_get_post_views') ? lared_get_post_views($post_id) : 0;

    $archive_tree[$year_key]['months'][$month_key]['posts'][] = [
        'id'       => $post_id,
        'title'    => get_the_title($post_id),
        'url'      => get_permalink($post_id),
        'day'      => wp_date('m-d', $timestamp),
        'datetime' => wp_date('c', $timestamp),
        'cat_icon' => $p_cat_icon,
        'comments' => $p_comments,
        'views'    => $p_views,
    ];

    $archive_tree[$year_key]['months'][$month_key]['count']++;
    $archive_tree[$year_key]['count']++;

    $day_key = wp_date('Y-m-d', $timestamp);
    if (!isset($heatmap_counts[$day_key])) {
        $heatmap_counts[$day_key] = 0;
    }
    $heatmap_counts[$day_key]++;

    $content_text = wp_strip_all_tags((string) $archive_post->post_content);
    $content_text = preg_replace('/\s+/u', '', $content_text) ?? $content_text;

    $archive_total_chars += function_exists('mb_strlen')
        ? mb_strlen($content_text, 'UTF-8')
        : strlen($content_text);

    $archive_total_comments += max(0, (int) $archive_post->comment_count);
}

$archive_total_posts = count($archive_posts);
$site_running_days = function_exists('lared_get_site_running_days_from_first_post')
    ? lared_get_site_running_days_from_first_post()
    : 0;

$today_ts = strtotime(wp_date('Y-m-d', current_time('timestamp')) . ' 00:00:00');
$range_start_ts = $today_ts - (364 * DAY_IN_SECONDS);
$start_weekday = (int) wp_date('w', $range_start_ts);
$aligned_start_ts = $range_start_ts - ($start_weekday * DAY_IN_SECONDS);
$end_weekday = (int) wp_date('w', $today_ts);
$aligned_end_ts = $today_ts + ((6 - $end_weekday) * DAY_IN_SECONDS);

$heatmap_weeks = [];
$month_labels = [];
$max_day_count = 0;
$week_index = -1;

for ($ts = $aligned_start_ts; $ts <= $aligned_end_ts; $ts += DAY_IN_SECONDS) {
    $weekday = (int) wp_date('w', $ts);
    if (0 === $weekday) {
        $week_index++;
        $heatmap_weeks[$week_index] = array_fill(0, 7, null);

        $month_key = wp_date('Y-m', $ts);
        if (!isset($month_labels[$month_key])) {
            $month_labels[$month_key] = [
                'label' => wp_date('M', $ts),
                'week_index' => $week_index,
            ];
        }
    }

    $ymd = wp_date('Y-m-d', $ts);
    $in_range = $ts >= $range_start_ts && $ts <= $today_ts;
    $count = $in_range ? (int) ($heatmap_counts[$ymd] ?? 0) : -1;

    if ($count > $max_day_count) {
        $max_day_count = $count;
    }

    $heatmap_weeks[$week_index][$weekday] = [
        'date' => $ymd,
        'count' => $count,
        'in_range' => $in_range,
    ];
}

$heatmap_month_labels = array_values($month_labels);
?>

<main class="main-shell year-month-archive-page mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
    <section class="listing-head border-b border-[#d9d9d9]">
        <div class="listing-head-inner">
            <span class="listing-head-accent" aria-hidden="true"></span>
            <div class="listing-head-main">
                <div class="listing-head-title-row archive-head-title-row">
                    <h1 class="listing-head-title"><?php echo esc_html(get_the_title()); ?></h1>

                    <div class="archive-head-stats" aria-label="归档统计">
                        <span class="archive-head-stat">
                            <b><?php echo esc_html(number_format_i18n($archive_total_posts)); ?></b>
                            <em><?php esc_html_e('篇文章', 'lared'); ?></em>
                        </span>
                        <span class="archive-head-stat">
                            <b><?php echo esc_html(number_format_i18n($site_running_days)); ?></b>
                            <em><?php esc_html_e('天', 'lared'); ?></em>
                        </span>
                        <span class="archive-head-stat">
                            <b><?php echo esc_html(number_format_i18n($archive_total_chars)); ?></b>
                            <em><?php esc_html_e('字', 'lared'); ?></em>
                        </span>
                        <span class="archive-head-stat">
                            <b><?php echo esc_html(number_format_i18n($archive_total_comments)); ?></b>
                            <em><?php esc_html_e('条评论', 'lared'); ?></em>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="listing-content">
        <section class="archive-heatmap" aria-label="<?php esc_attr_e('近一年更新热力图', 'lared'); ?>">
            <div class="archive-heatmap-head">
                <h2 class="archive-heatmap-title"><?php esc_html_e('近一年更新热力图', 'lared'); ?></h2>
                <p class="archive-heatmap-subtitle"><?php esc_html_e('每个方块代表一天，颜色越深表示当天发文越多。', 'lared'); ?></p>
            </div>

            <div class="archive-heatmap-wrap">
                <div class="archive-heatmap-months" aria-hidden="true">
                    <?php foreach ($heatmap_month_labels as $month_label) : ?>
                        <span style="grid-column: <?php echo esc_attr((string) ((int) $month_label['week_index'] + 1)); ?>">
                            <?php echo esc_html((string) $month_label['label']); ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="archive-heatmap-grid">
                    <?php foreach ($heatmap_weeks as $week) : ?>
                        <?php foreach ($week as $day_data) : ?>
                            <?php
                            $count = (int) ($day_data['count'] ?? -1);
                            $date_text = (string) ($day_data['date'] ?? '');

                            // 5级深度：1篇=4级，2篇以上=5级
                            if ($count < 0) {
                                $level = -1;
                            } elseif (0 === $count) {
                                $level = 0;
                            } elseif (1 === $count) {
                                $level = 4;  // 1篇显示第4级深度
                            } else {
                                $level = 5;  // 2篇以上显示第5级深度
                            }

                            $title = $count < 0
                                ? ''
                                : sprintf(
                                    /* translators: 1: post count, 2: date */
                                    __('%1$d 篇文章 · %2$s', 'lared'),
                                    max(0, $count),
                                    $date_text
                                );
                            ?>
                            <span
                                class="archive-heatmap-cell level-<?php echo esc_attr((string) $level); ?>"
                                <?php if ('' !== $title) : ?>
                                    title="<?php echo esc_attr($title); ?>"
                                    aria-label="<?php echo esc_attr($title); ?>"
                                <?php else : ?>
                                    aria-hidden="true"
                                <?php endif; ?>
                            ></span>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="archive-heatmap-legend" aria-hidden="true">
                <span><?php esc_html_e('少', 'lared'); ?></span>
                <i class="archive-heatmap-cell level-0"></i>
                <i class="archive-heatmap-cell level-1"></i>
                <i class="archive-heatmap-cell level-2"></i>
                <i class="archive-heatmap-cell level-3"></i>
                <i class="archive-heatmap-cell level-4"></i>
                <i class="archive-heatmap-cell level-5"></i>
                <span><?php esc_html_e('多', 'lared'); ?></span>
            </div>
        </section>

        <?php if (!empty($archive_tree)) : ?>
            <div class="archive-timeline">
                <?php foreach ($archive_tree as $year => $year_data) : ?>
                    <section class="archive-year-group">
                        <header class="archive-year-head">
                            <h2 class="archive-year-title"><?php echo esc_html((string) $year); ?></h2>
                            <span class="archive-year-count"><?php echo esc_html(sprintf(_n('%d 篇', '%d 篇', (int) $year_data['count'], 'lared'), (int) $year_data['count'])); ?></span>
                        </header>

                        <?php if (!empty($year_data['months']) && is_array($year_data['months'])) : ?>
                            <?php foreach ($year_data['months'] as $month_data) : ?>
                                <section class="archive-month-group">
                                    <header class="archive-month-head">
                                        <h3 class="archive-month-title"><?php echo esc_html((string) ($month_data['label'] ?? '')); ?></h3>
                                        <span class="archive-month-count"><?php echo esc_html(sprintf(_n('%d 篇', '%d 篇', (int) ($month_data['count'] ?? 0), 'lared'), (int) ($month_data['count'] ?? 0))); ?></span>
                                    </header>

                                    <ul class="archive-post-list">
                                        <?php foreach (($month_data['posts'] ?? []) as $month_post) : ?>
                                            <li class="archive-post-item">
                                                <time class="archive-post-date" datetime="<?php echo esc_attr((string) ($month_post['datetime'] ?? '')); ?>"><?php echo esc_html((string) ($month_post['day'] ?? '')); ?></time>
                                                <?php if ('' !== ($month_post['cat_icon'] ?? '')) : ?>
                                                    <span class="archive-post-cat-icon" aria-hidden="true"><?php echo wp_kses_post($month_post['cat_icon']); ?></span>
                                                <?php endif; ?>
                                                <a class="archive-post-link" href="<?php echo esc_url((string) ($month_post['url'] ?? '')); ?>"><?php echo esc_html((string) ($month_post['title'] ?? '')); ?></a>
                                                <span class="archive-post-stats">
                                                    <span class="archive-post-stat" title="<?php esc_attr_e('评论', 'lared'); ?>"><i class="fa-regular fa-comment" aria-hidden="true"></i><?php echo esc_html((string) ($month_post['comments'] ?? 0)); ?></span>
                                                    <span class="archive-post-stat" title="<?php esc_attr_e('浏览', 'lared'); ?>"><i class="fa-regular fa-eye" aria-hidden="true"></i><?php echo esc_html((string) ($month_post['views'] ?? 0)); ?></span>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="listing-empty">
                <p><?php esc_html_e('暂无文章。', 'lared'); ?></p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php
get_footer();
