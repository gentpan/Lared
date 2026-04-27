<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $wp_query;

$archive_title = get_the_archive_title();
$archive_desc  = get_the_archive_description();
$archive_found_posts = (int) $wp_query->found_posts;
?>

<main class="main-shell mx-auto w-full max-w-[1400px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
    <section class="listing-head border-b border-[#d9d9d9]">
        <div class="listing-head-inner">
            <span class="listing-head-accent" aria-hidden="true"></span>
            <div class="listing-head-main">
                <div class="listing-head-title-row">
                    <h1 class="listing-head-title"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i><?php echo esc_html(wp_strip_all_tags((string) $archive_title)); ?></h1>
                    <p class="listing-head-side-stat"><?php printf(esc_html__('%d 篇文章', 'lared'), $archive_found_posts); ?></p>
                </div>
                <?php if ('' !== trim((string) $archive_desc)) : ?>
                    <div class="listing-head-desc"><?php echo wp_kses_post($archive_desc); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="listing-content">
        <?php if (have_posts()) : ?>
            <div class="archive-timeline">
                <ul class="archive-post-list">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        $post_id    = (int) get_the_ID();
                        $p_comments = (int) get_comments_number($post_id);
                        $p_views    = function_exists('lared_get_post_views') ? lared_get_post_views($post_id) : 0;
                        $p_cat_icon = '';
                        $category   = get_the_category();
                        if (!empty($category) && isset($category[0]->term_id)) {
                            $p_cat_icon = lared_get_category_icon_html((int) $category[0]->term_id);
                        }
                        ?>
                        <li class="archive-post-item">
                            <time class="archive-post-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('m/d')); ?></time>
                            <?php if ('' !== $p_cat_icon) : ?>
                                <span class="archive-post-cat-icon" aria-hidden="true"><?php echo wp_kses_post($p_cat_icon); ?></span>
                            <?php endif; ?>
                            <a class="archive-post-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <span class="archive-post-stats">
                                <span class="archive-post-stat" title="<?php esc_attr_e('评论', 'lared'); ?>"><i class="fa-regular fa-comment" aria-hidden="true"></i><?php echo esc_html((string) $p_comments); ?></span>
                                <span class="archive-post-stat" title="<?php esc_attr_e('浏览', 'lared'); ?>"><i class="fa-regular fa-eye" aria-hidden="true"></i><?php echo esc_html((string) $p_views); ?></span>
                            </span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="lared-pagination">
                <?php
                the_posts_pagination([
                    'mid_size'           => 2,
                    'prev_text'          => '&larr; ' . __('上一页', 'lared'),
                    'next_text'          => __('下一页', 'lared') . ' &rarr;',
                    'before_page_number' => '',
                    'screen_reader_text' => __('文章分页', 'lared'),
                ]);
                ?>
            </div>
        <?php else : ?>
            <div class="listing-empty">
                <p><?php esc_html_e('这个归档下暂时没有内容。', 'lared'); ?></p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php get_footer();
