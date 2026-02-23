<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$search_query = get_search_query();
$found_posts  = (int) $wp_query->found_posts;
$search_title = sprintf(__('搜索：%s', 'pan'), $search_query);
?>

<main class="main-shell keyword-archive-page mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
    <section class="listing-head border-b border-[#d9d9d9]">
        <div class="listing-head-inner">
            <span class="listing-head-accent" aria-hidden="true"></span>
            <div class="listing-head-main">
                <div class="listing-head-title-row">
                    <h1 class="listing-head-title"><?php echo esc_html($search_title); ?></h1>
                    <p class="listing-head-side-stat"><?php printf(esc_html__('共找到 %d 条结果', 'pan'), $found_posts); ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="listing-content">
        <?php if (have_posts()) : ?>
            <div class="search-result-list">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $post_id      = (int) get_the_ID();
                    $post_type    = get_post_type($post_id);
                    $post_type_obj = get_post_type_object($post_type);
                    $post_type_label = ($post_type_obj && isset($post_type_obj->labels->singular_name))
                        ? $post_type_obj->labels->singular_name
                        : strtoupper((string) $post_type);

                    $excerpt = get_the_excerpt();
                    if ('' === trim((string) $excerpt)) {
                        $excerpt = wp_strip_all_tags((string) get_post_field('post_content', $post_id));
                    }
                    ?>
                    <article class="search-result-item">
                        <a class="search-result-link" href="<?php the_permalink(); ?>">
                            <div class="search-result-meta">
                                <span class="search-result-type"><?php echo esc_html($post_type_label); ?></span>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y-m-d')); ?></time>
                            </div>
                            <h2 class="search-result-title"><?php the_title(); ?></h2>
                            <p class="search-result-excerpt"><?php echo esc_html(wp_trim_words((string) $excerpt, 36, '...')); ?></p>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="pan-pagination">
                <?php
                the_posts_pagination([
                    'mid_size'           => 2,
                    'prev_text'          => '&larr; ' . __('上一页', 'pan'),
                    'next_text'          => __('下一页', 'pan') . ' &rarr;',
                    'before_page_number' => '',
                    'screen_reader_text' => __('搜索结果分页', 'pan'),
                ]);
                ?>
            </div>
        <?php else : ?>
            <div class="listing-empty">
                <p><?php esc_html_e('没有找到匹配内容，请尝试其他关键词。', 'pan'); ?></p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php get_footer();
