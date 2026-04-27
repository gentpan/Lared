<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

/** @var WP_Term|null $queried_term */
$queried_term = get_queried_object();
$tag_id       = ($queried_term instanceof WP_Term) ? (int) $queried_term->term_id : 0;
$tag_name     = ($queried_term instanceof WP_Term) ? $queried_term->name : '';
$tag_count    = ($queried_term instanceof WP_Term) ? (int) $queried_term->count : 0;

$tag_posts = get_posts([
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'numberposts'            => -1,
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'tag_id'                 => $tag_id,
    'ignore_sticky_posts'    => true,
    'no_found_rows'          => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => true,
]);

$tree = [];
foreach ($tag_posts as $p) {
    $pid = (int) $p->ID;
    $ts  = (int) get_post_time('U', true, $pid);
    if ($ts <= 0) continue;

    $year = wp_date('Y', $ts);
    if (!isset($tree[$year])) {
        $tree[$year] = ['count' => 0, 'posts' => []];
    }

    $p_cat_icon = '';
    $p_cats = wp_get_post_categories($pid, ['fields' => 'all']);
    if (!empty($p_cats) && isset($p_cats[0]->term_id)) {
        $p_cat_icon = lared_get_category_icon_html((int) $p_cats[0]->term_id);
    }

    $tree[$year]['posts'][] = [
        'id'       => $pid,
        'title'    => get_the_title($pid),
        'url'      => get_permalink($pid),
        'day'      => wp_date('m/d', $ts),
        'datetime' => wp_date('c', $ts),
        'cat_icon' => $p_cat_icon,
        'comments' => max(0, (int) $p->comment_count),
        'views'    => function_exists('lared_get_post_views') ? lared_get_post_views($pid) : 0,
    ];
    $tree[$year]['count']++;
}
?>

<main class="main-shell mx-auto w-full max-w-[1400px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
    <section class="listing-head border-b border-[#d9d9d9]">
        <div class="listing-head-inner">
            <span class="listing-head-accent" aria-hidden="true"></span>
            <div class="listing-head-main">
                <div class="listing-head-title-row">
                    <h1 class="listing-head-title">
                        <i class="fa-sharp fa-regular fa-hashtag" aria-hidden="true"></i>
                        <span><?php echo esc_html($tag_name); ?></span>
                    </h1>
                    <p class="listing-head-side-stat">
                        <?php printf(esc_html__('%d 篇文章', 'lared'), $tag_count); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="listing-content">
        <?php if (!empty($tree)) : ?>
            <div class="archive-timeline">
                <?php foreach ($tree as $year => $year_data) : ?>
                    <section class="archive-year-group">
                        <header class="archive-year-head">
                            <h2 class="archive-year-title"><?php echo esc_html((string) $year); ?></h2>
                            <span class="archive-year-count"><?php echo esc_html(sprintf(_n('%d 篇', '%d 篇', (int) $year_data['count'], 'lared'), (int) $year_data['count'])); ?></span>
                        </header>

                        <ul class="archive-post-list">
                            <?php foreach ($year_data['posts'] as $post_item) : ?>
                                <li class="archive-post-item">
                                    <time class="archive-post-date" datetime="<?php echo esc_attr((string) $post_item['datetime']); ?>"><?php echo esc_html((string) $post_item['day']); ?></time>
                                    <?php if ('' !== $post_item['cat_icon']) : ?>
                                        <span class="archive-post-cat-icon" aria-hidden="true"><?php echo wp_kses_post($post_item['cat_icon']); ?></span>
                                    <?php endif; ?>
                                    <a class="archive-post-link" href="<?php echo esc_url((string) $post_item['url']); ?>"><?php echo esc_html((string) $post_item['title']); ?></a>
                                    <span class="archive-post-stats">
                                        <span class="archive-post-stat" title="<?php esc_attr_e('评论', 'lared'); ?>"><i class="fa-regular fa-comment" aria-hidden="true"></i><?php echo esc_html((string) $post_item['comments']); ?></span>
                                        <span class="archive-post-stat" title="<?php esc_attr_e('浏览', 'lared'); ?>"><i class="fa-regular fa-eye" aria-hidden="true"></i><?php echo esc_html((string) $post_item['views']); ?></span>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="listing-empty">
                <p><?php esc_html_e('该标签下暂无文章。', 'lared'); ?></p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php get_footer(); ?>
