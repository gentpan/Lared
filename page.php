<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $content_plain = wp_strip_all_tags((string) get_post_field('post_content', get_the_ID()));
        $cjk_count     = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', $content_plain, $_cjk_m);
        $latin_words   = str_word_count((string) preg_replace('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', ' ', $content_plain));
        $reading_minutes = max(1, (int) ceil((int) $cjk_count / 350 + $latin_words / 200));
        $is_xalbum_page = has_shortcode((string) get_post_field('post_content', get_the_ID()), 'xalbum');
        $content_box_class = $is_xalbum_page
            ? 'box-border'
            : 'box-border border-x border-b border-[#d9d9d9] px-8 pt-7 max-[900px]:px-[18px] max-[900px]:pt-[22px]';
        ?>

        <main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
            <section class="listing-head border-b border-[#d9d9d9]">
                <div class="listing-head-inner">
                    <span class="listing-head-accent" aria-hidden="true"></span>
                    <div class="listing-head-main">
                        <div class="listing-head-title-row">
                            <h1 class="listing-head-title"><i class="<?php echo $is_xalbum_page ? 'fa-sharp fa-thin fa-image' : 'fa-sharp fa-thin fa-bookmark'; ?>" aria-hidden="true"></i><?php echo esc_html(get_the_title()); ?></h1>
                            <?php if ($is_xalbum_page && function_exists('lared_get_album_data')) :
                                $album_data = lared_get_album_data();
                                $album_total = count($album_data['images'] ?? []);
                            ?>
                                <p class="listing-head-side-stat">
                                    <?php printf(esc_html__('共 %d 张图片', 'lared'), $album_total); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <?php
            $page_cover_image_html = lared_get_post_image_html(
                (int) get_the_ID(),
                'full',
                ['class' => 'block h-auto w-full']
            );
            ?>
            <?php if ('' !== $page_cover_image_html) : ?>
            <section class="listing-content">
                <div class="overflow-hidden border border-[#d9d9d9] bg-[#f3f3f3]">
                    <?php echo wp_kses_post($page_cover_image_html); ?>
                </div>
            </section>
            <?php endif; ?>

            <section class="listing-content">
                <div class="<?php echo esc_attr($content_box_class); ?>">
                    <article class="page-content prose prose-neutral max-w-none min-w-0 text-[var(--color-body)]">
                        <?php the_content(); ?>
                    </article>
                </div>
            </section>
        </main>

        <?php
    endwhile;
endif;

get_footer();
