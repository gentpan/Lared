<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

/** @var WP_Term|null $queried_term */
$queried_term = get_queried_object();
$cat_id       = ($queried_term instanceof WP_Term) ? (int) $queried_term->term_id : 0;
$cat_name     = ($queried_term instanceof WP_Term) ? $queried_term->name : '';
$cat_count    = ($queried_term instanceof WP_Term) ? (int) $queried_term->count : 0;
$cat_icon_html = $cat_id > 0 ? pan_get_category_icon_html($cat_id) : '';
?>

<main class="main-shell category-archive-page mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
    <section class="listing-head category-archive-head border-b border-[#d9d9d9]">
        <div class="listing-head-inner">
            <span class="listing-head-accent" aria-hidden="true"></span>
            <div class="listing-head-main">
                <div class="listing-head-title-row">
                    <h1 class="listing-head-title category-archive-title">
                        <?php if ('' !== $cat_icon_html) : ?>
                            <span class="category-archive-title-icon" aria-hidden="true"><?php echo wp_kses_post($cat_icon_html); ?></span>
                        <?php endif; ?>
                        <span><?php echo esc_html($cat_name); ?></span>
                    </h1>

                    <p class="listing-head-side-stat">
                        <?php printf(esc_html__('%d 篇文章', 'pan'), $cat_count); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="listing-content category-archive-content">
        <?php if (have_posts()) : ?>
            <div class="listing-grid category-archive-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $p_id        = (int) get_the_ID();
                    $p_image_url = pan_get_post_image_url($p_id, 'large');
                    $p_comments  = (int) get_comments_number($p_id);
                    $p_date      = get_the_date('Y-m-d', $p_id);
                    ?>
                    <article class="listing-card category-archive-card">
                        <a class="listing-card-link category-archive-card-link" href="<?php the_permalink(); ?>">
                            <div class="listing-card-image-wrap category-archive-image-wrap">
                                <?php if ('' !== $p_image_url) : ?>
                                    <img
                                        class="listing-card-image category-archive-image"
                                        src="<?php echo esc_url($p_image_url); ?>"
                                        alt="<?php the_title_attribute(); ?>"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                <?php else : ?>
                                    <span class="listing-card-image-fallback" aria-hidden="true"></span>
                                <?php endif; ?>
                            </div>

                            <div class="category-archive-meta-row">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html($p_date); ?></time>
                                <span><?php printf(esc_html__('%d 条评论', 'pan'), $p_comments); ?></span>
                            </div>

                            <div class="listing-card-body category-archive-card-body">
                                <h2 class="listing-card-title"><?php the_title(); ?></h2>
                            </div>
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
                    'screen_reader_text' => __('文章分页', 'pan'),
                ]);
                ?>
            </div>
        <?php else : ?>
            <div class="listing-empty">
                <p><?php esc_html_e('该分类下暂无文章。', 'pan'); ?></p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php get_footer(); ?>
