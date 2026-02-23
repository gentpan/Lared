<?php
/*
Template Name: 关于页面
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $ten_year = function_exists('pan_get_ten_year_progress_data')
            ? pan_get_ten_year_progress_data()
            : [
                'start_date' => '',
                'end_date' => '',
                'progress_percent' => 0.0,
                'remaining_days' => 0,
                'is_started' => false,
            ];
        ?>

        <main class="main-shell about-main mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
            <section class="listing-head border-b border-[#d9d9d9]">
                <div class="listing-head-inner">
                    <span class="listing-head-accent" aria-hidden="true"></span>
                    <div class="listing-head-main">
                        <div class="listing-head-title-row">
                            <h1 class="listing-head-title"><?php echo esc_html(get_the_title()); ?></h1>
                            <p class="listing-head-side-stat">
                                <?php
                                printf(
                                    /* translators: %s: modified date */
                                    esc_html__('更新于 %s', 'pan'),
                                    esc_html(get_the_modified_date('Y-m-d'))
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-hero" aria-label="<?php esc_attr_e('关于页面头图', 'pan'); ?>">
                <?php
                $about_cover_image_html = pan_get_post_image_html(
                    (int) get_the_ID(),
                    'full',
                    ['class' => 'about-hero-image']
                );
                ?>
                <?php if ('' !== $about_cover_image_html) : ?>
                    <?php echo $about_cover_image_html; ?>
                <?php else : ?>
                    <div class="about-hero-fallback" aria-hidden="true"></div>
                <?php endif; ?>
            </section>

            <section class="about-content">
                <div class="about-content-inner">
                    <?php if ('' !== (string) ($ten_year['start_date'] ?? '')) : ?>
                        <section class="about-decade-card" aria-label="<?php esc_attr_e('博客十年之约', 'pan'); ?>">
                            <div class="about-decade-head">
                                <h2 class="about-decade-title">
                                    <i class="fa-regular fa-feather-pointed" aria-hidden="true"></i>
                                    <span><?php esc_html_e('博客十年之约', 'pan'); ?></span>
                                </h2>
                                <span class="about-decade-remaining">
                                    <?php
                                    printf(
                                        esc_html__('剩余 %d 天', 'pan'),
                                        (int) ($ten_year['remaining_days'] ?? 0)
                                    );
                                    ?>
                                </span>
                            </div>

                            <div class="about-decade-progress-wrap">
                                <div class="about-decade-progress-track">
                                    <span
                                        class="about-decade-progress-fill"
                                        style="width: <?php echo esc_attr(number_format((float) ($ten_year['progress_percent'] ?? 0), 2, '.', '')); ?>%;"
                                    ></span>
                                </div>
                                <span class="about-decade-progress-label"><?php echo esc_html(number_format((float) ($ten_year['progress_percent'] ?? 0), 1)); ?>%</span>
                            </div>

                            <div class="about-decade-dates">
                                <span><?php echo esc_html((string) ($ten_year['start_date'] ?? '')); ?></span>
                                <span><?php echo esc_html((string) ($ten_year['end_date'] ?? '')); ?></span>
                            </div>
                        </section>
                    <?php endif; ?>

                    <article class="about-article page-content prose prose-neutral max-w-none min-w-0 text-[var(--color-body)]">
                        <?php the_content(); ?>
                    </article>
                </div>
            </section>
        </main>

        <?php
    endwhile;
endif;

get_footer();
