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

        $ten_year = function_exists('lared_get_ten_year_progress_data')
            ? lared_get_ten_year_progress_data()
            : [
                'start_date' => '',
                'end_date' => '',
                'progress_percent' => 0.0,
                'remaining_days' => 0,
                'is_started' => false,
            ];

        $about_sidebar = function_exists('lared_get_about_sidebar_data')
            ? lared_get_about_sidebar_data()
            : ['hobbies' => [], 'plans' => [], 'tags' => []];
?>

        <main class="main-shell about-main mx-auto w-full max-w-[1400px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] flex flex-col">
            <section class="listing-head border-b border-[#d9d9d9]">
                <div class="listing-head-inner">
                    <span class="listing-head-accent" aria-hidden="true"></span>
                    <div class="listing-head-main">
                        <div class="listing-head-title-row">
                            <h1 class="listing-head-title"><i class="fa-sharp fa-thin fa-face-grin-hearts" aria-hidden="true"></i><?php echo esc_html(wp_specialchars_decode(get_the_title(), ENT_QUOTES)); ?></h1>
                            <?php
                            $about_social_links = function_exists('lared_get_about_social_links') ? lared_get_about_social_links() : [];
                            if (!empty($about_social_links)) : ?>
                                <div class="about-social-links">
                                    <?php foreach ($about_social_links as $social) : ?>
                                        <a class="about-social-link" href="<?php echo esc_url($social['url']); ?>" target="_blank" rel="<?php echo 'mastodon' === $social['key'] ? 'me noopener noreferrer' : 'noopener noreferrer'; ?>" aria-label="<?php echo esc_attr($social['label']); ?>" title="<?php echo esc_attr($social['label']); ?>">
                                            <i class="<?php echo esc_attr($social['icon']); ?>" aria-hidden="true"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-hero" aria-label="<?php esc_attr_e('关于页面头图', 'lared'); ?>">
                <?php
                $about_cover_image_html = lared_get_post_image_html(
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
                    <div class="about-col-left">
                        <article class="about-article page-content prose prose-neutral max-w-none min-w-0 text-[var(--color-body)]">
                            <?php the_content(); ?>
                        </article>
                    </div>

                    <?php
                    $has_sidebar = !empty($about_sidebar['hobbies']) || !empty($about_sidebar['plans']) || !empty($about_sidebar['tags']);
                    ?>
                    <?php if ($has_sidebar) : ?>
                        <aside class="about-col-right">
                            <?php if (!empty($about_sidebar['hobbies'])) : ?>
                                <div class="about-sidebar-block">
                                    <h3 class="about-sidebar-title">
                                        <i class="fa-sharp fa-thin fa-heart" aria-hidden="true"></i>
                                        <?php esc_html_e('爱好', 'lared'); ?>
                                    </h3>
                                    <ul class="about-sidebar-list">
                                        <?php foreach ($about_sidebar['hobbies'] as $hobby) : ?>
                                            <li><?php echo esc_html($hobby); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($about_sidebar['plans'])) : ?>
                                <div class="about-sidebar-block">
                                    <h3 class="about-sidebar-title">
                                        <i class="fa-sharp fa-thin fa-list-check" aria-hidden="true"></i>
                                        <?php esc_html_e('计划', 'lared'); ?>
                                    </h3>
                                    <ul class="about-sidebar-list about-sidebar-plans">
                                        <?php foreach ($about_sidebar['plans'] as $plan) : ?>
                                            <li><?php echo esc_html($plan); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($about_sidebar['tags'])) : ?>
                                <div class="about-sidebar-block">
                                    <h3 class="about-sidebar-title">
                                        <i class="fa-sharp fa-thin fa-tags" aria-hidden="true"></i>
                                        <?php esc_html_e('关键词', 'lared'); ?>
                                    </h3>
                                    <div class="about-sidebar-tags">
                                        <?php foreach ($about_sidebar['tags'] as $tag) : ?>
                                            <span class="about-sidebar-tag"><?php echo esc_html($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </aside>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ('' !== (string) ($ten_year['start_date'] ?? '')) : ?>
                <section class="about-decade-bottom" aria-label="<?php esc_attr_e('博客十年之约', 'lared'); ?>">
                    <div class="about-decade-head">
                        <h2 class="about-decade-title">
                            <i class="fa-regular fa-feather-pointed" aria-hidden="true"></i>
                            <span><?php esc_html_e('博客十年之约', 'lared'); ?></span>
                            <a class="about-decade-join" href="https://www.foreverblog.cn/" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('申请加入', 'lared'); ?>
                                <i class="fa-sharp fa-thin fa-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        </h2>
                        <span class="about-decade-remaining">
                            <?php
                            printf(
                                esc_html__('剩余 %d 天', 'lared'),
                                (int) ($ten_year['remaining_days'] ?? 0)
                            );
                            ?>
                        </span>
                    </div>

                    <div class="about-decade-progress-wrap">
                        <div class="about-decade-progress-track">
                            <span
                                class="about-decade-progress-fill"
                                style="width: <?php echo esc_attr(number_format((float) ($ten_year['progress_percent'] ?? 0), 2, '.', '')); ?>%;"></span>
                        </div>
                        <span class="about-decade-progress-label"><?php echo esc_html(number_format((float) ($ten_year['progress_percent'] ?? 0), 1)); ?>%</span>
                    </div>

                    <div class="about-decade-dates">
                        <span><?php echo esc_html((string) ($ten_year['start_date'] ?? '')); ?></span>
                        <span><?php echo esc_html((string) ($ten_year['end_date'] ?? '')); ?></span>
                    </div>
                </section>
            <?php endif; ?>
        </main>

<?php
    endwhile;
endif;

get_footer();
