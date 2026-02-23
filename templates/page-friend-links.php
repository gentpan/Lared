<?php
/*
Template Name: 友情链接
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $bookmarks = get_bookmarks([
            'orderby'        => 'name',
            'order'          => 'ASC',
            'hide_invisible' => 1,
        ]);
        $bookmark_count = is_array($bookmarks) ? count($bookmarks) : 0;
        ?>

        <main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
            <section class="listing-head border-b border-[#d9d9d9]">
                <div class="listing-head-inner">
                    <span class="listing-head-accent" aria-hidden="true"></span>
                    <div class="listing-head-main">
                        <div class="listing-head-title-row">
                            <h1 class="listing-head-title"><?php the_title(); ?></h1>
                            <p class="listing-head-side-stat"><?php printf(esc_html__('%d 个链接', 'lared'), $bookmark_count); ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="listing-content friend-links-content">
                <?php if ('' !== trim((string) get_the_content())) : ?>
                    <article class="friend-links-intro page-content prose prose-neutral max-w-none">
                        <?php the_content(); ?>
                    </article>
                <?php endif; ?>

                <?php if (!empty($bookmarks)) : ?>
                    <div class="friend-links-grid">
                        <?php foreach ($bookmarks as $bookmark) : ?>
                            <?php
                            $site_url  = (string) $bookmark->link_url;
                            $site_name = (string) $bookmark->link_name;
                            $site_desc = (string) $bookmark->link_description;
                            $host      = wp_parse_url($site_url, PHP_URL_HOST);
                            ?>
                            <article class="friend-link-card">
                                <a class="friend-link-card-link" href="<?php echo esc_url($site_url); ?>" target="_blank" rel="noopener noreferrer">
                                    <div class="friend-link-card-head">
                                        <h2 class="friend-link-card-title"><?php echo esc_html($site_name); ?></h2>
                                        <span class="friend-link-card-icon" aria-hidden="true"><i class="fa-sharp fa-thin fa-square-arrow-up-right"></i></span>
                                    </div>
                                    <?php if ('' !== trim($site_desc)) : ?>
                                        <p class="friend-link-card-desc"><?php echo esc_html($site_desc); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($host)) : ?>
                                        <p class="friend-link-card-host"><?php echo esc_html($host); ?></p>
                                    <?php endif; ?>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="listing-empty">
                        <p><?php esc_html_e('暂未配置友情链接。', 'lared'); ?></p>
                        <p class="listing-empty-note"><?php esc_html_e('可在后台 Links/链接 管理中添加站点后自动展示。', 'lared'); ?></p>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <?php
    endwhile;
endif;

get_footer();
