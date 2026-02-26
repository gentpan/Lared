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

<main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
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
            <div class="listing-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $post_id     = (int) get_the_ID();
                    $image_url   = lared_get_post_image_url($post_id, 'large');
                    $category    = get_the_category();
                    $cat_name    = !empty($category) ? $category[0]->name : __('未分类', 'lared');
                    $cat_icon    = (!empty($category) && isset($category[0]->term_id)) ? lared_get_category_icon_html((int) $category[0]->term_id) : '';
                    $excerpt_raw = get_the_excerpt();
                    ?>
                    <article class="listing-card">
                        <a class="listing-card-link" href="<?php the_permalink(); ?>">
                            <div class="listing-card-image-wrap">
                                <?php if ('' !== $image_url) : ?>
                                    <img
                                        class="listing-card-image lazyload"
                                        data-src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php the_title_attribute(); ?>"
                                    />
                                <?php else : ?>
                                    <span class="listing-card-image-fallback" aria-hidden="true"></span>
                                <?php endif; ?>
                            </div>

                            <div class="listing-card-body">
                                <div class="listing-card-meta-top">
                                    <span class="listing-card-category">
                                        <?php if ('' !== $cat_icon) : ?>
                                            <span class="listing-card-category-icon" aria-hidden="true"><?php echo wp_kses_post($cat_icon); ?></span>
                                        <?php endif; ?>
                                        <span><?php echo esc_html($cat_name); ?></span>
                                    </span>
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y-m-d')); ?></time>
                                </div>

                                <h2 class="listing-card-title"><?php the_title(); ?></h2>

                                <?php if ('' !== trim((string) $excerpt_raw)) : ?>
                                    <p class="listing-card-excerpt"><?php echo esc_html(wp_trim_words($excerpt_raw, 24, '...')); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
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
