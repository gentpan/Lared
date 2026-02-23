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

        $categories = get_the_category();
        $first_cat = !empty($categories) ? $categories[0] : null;
        $category_label = $first_cat ? $first_cat->name : __('Uncategorized', 'lared');
        $category_icon_html = $first_cat ? lared_get_category_icon_html((int) $first_cat->term_id) : '';

        $tags = get_the_tags();
        $author_id = (int) get_the_author_meta('ID');
        $author_url = $author_id > 0 ? get_author_posts_url($author_id) : '';
        $previous_post = get_previous_post();
        $next_post = get_next_post();

        $article_content = apply_filters('the_content', (string) get_post_field('post_content', get_the_ID()));
        $single_toc_items = [];
        $single_used_ids = [];
        $single_heading_index = 0;

        $article_content = preg_replace_callback(
            '/<h([23])([^>]*)>(.*?)<\/h\1>/is',
            static function (array $matches) use (&$single_toc_items, &$single_used_ids, &$single_heading_index): string {
                $level = (int) $matches[1];
                $attributes = (string) $matches[2];
                $inner_html = (string) $matches[3];
                $heading_text = trim(wp_strip_all_tags($inner_html));

                if ('' === $heading_text) {
                    return $matches[0];
                }

                $single_heading_index++;
                $base_id = sanitize_title($heading_text);
                if ('' === $base_id) {
                    $base_id = 'section-' . $single_heading_index;
                }
                $base_id = 'single-' . get_the_ID() . '-' . $base_id;

                $heading_id = $base_id;
                $suffix = 2;
                while (in_array($heading_id, $single_used_ids, true)) {
                    $heading_id = $base_id . '-' . $suffix;
                    $suffix++;
                }
                $single_used_ids[] = $heading_id;

                $attributes = preg_replace('/\sid=("|\')(.*?)\1/i', '', $attributes);
                $attributes .= ' id="' . esc_attr($heading_id) . '"';

                $single_toc_items[] = [
                    'id' => $heading_id,
                    'label' => $heading_text,
                    'level' => $level,
                ];

                return '<h' . $level . $attributes . '>' . $inner_html . '</h' . $level . '>';
            },
            $article_content
        );

        $comment_count = (int) get_comments_number();
        $post_views = function_exists('lared_get_post_views') ? lared_get_post_views(get_the_ID()) : 0;
        $post_timestamp = (int) get_post_time('U', true, get_the_ID());
        $post_month_short = lared_date_en('M', $post_timestamp);
        $post_day_number  = lared_date_en('j', $post_timestamp);
        $post_time_full   = lared_date_en('Y/m/d H:i', $post_timestamp);
        ?>

        <main class="single-page-square main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#fff] pb-[90px] max-[900px]:pb-16" data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>">
            <section class="w-full pt-3 max-[900px]:pt-2" aria-label="Article Banner">
                <div class="single-top-banner">
                    <img
                        class="single-top-banner__image"
                        src="<?php
                        $banner_image = lared_get_post_image_url(get_the_ID(), 'large');
                        if ('' === $banner_image) {
                            $banner_image = 'https://picsum.photos/1600/520?random=' . wp_rand(1, 999999);
                        }
                        echo esc_url($banner_image);
                        ?>"
                        alt="<?php esc_attr_e('文章 Banner 占位图', 'lared'); ?>"
                        loading="lazy"
                        decoding="async"
                    >

                    <div class="single-top-banner__meta">
                        <div class="single-top-banner__meta-inner">
                            <div class="single-top-banner__meta-main">
                                <span class="home-article-time" tabindex="0" aria-label="<?php echo esc_attr($post_time_full); ?>">
                                    <span class="home-article-time-month"><?php echo esc_html($post_month_short); ?></span>
                                    <span class="home-article-time-day"><?php echo esc_html($post_day_number); ?></span>
                                    <span class="home-article-time-tooltip"><?php echo esc_html($post_time_full); ?></span>
                                </span>

                                <span class="single-top-banner__title-box"><?php the_title(); ?></span>
                            </div>

                            <div class="single-top-banner__meta-side" aria-label="Article Stats">
                                <div class="single-top-banner__cat-group">
                                    <span class="single-top-banner__cat-box" aria-label="<?php esc_attr_e('文章分类', 'lared'); ?>" tabindex="0">
                                        <span class="single-top-banner__cat-box-icon" aria-hidden="true">
                                            <?php if ('' !== $category_icon_html) : ?>
                                                <?php echo $category_icon_html; ?>
                                            <?php else : ?>
                                                <i class="fa-solid fa-folder"></i>
                                            <?php endif; ?>
                                        </span>
                                        <span class="single-top-banner__cat-box-name"><?php echo esc_html($category_label); ?></span>
                                        <span class="single-top-banner__tooltip"><?php esc_html_e('文章分类', 'lared'); ?></span>
                                    </span>
                                </div>

                                <div class="single-top-banner__stat-box single-top-banner__stat-box--comment" tabindex="0" aria-label="<?php esc_attr_e('评论数量', 'lared'); ?>">
                                    <span class="single-top-banner__stat-number"><?php echo esc_html($comment_count); ?></span>
                                    <span class="single-top-banner__stat-label"><?php esc_html_e('评论', 'lared'); ?></span>
                                    <span class="single-top-banner__tooltip"><?php esc_html_e('评论数量', 'lared'); ?></span>
                                </div>

                                <div class="single-top-banner__stat-box single-top-banner__stat-box--heat" tabindex="0" aria-label="<?php esc_attr_e('文章热度', 'lared'); ?>">
                                    <span class="single-top-banner__stat-number"><?php echo esc_html($post_views); ?></span>
                                    <span class="single-top-banner__stat-label"><?php esc_html_e('热度', 'lared'); ?></span>
                                    <span class="single-top-banner__tooltip"><?php esc_html_e('阅读数量', 'lared'); ?></span>
                                </div>

                                <div class="single-top-banner__stat-box single-top-banner__stat-box--reading" tabindex="0" aria-label="<?php esc_attr_e('阅读时间', 'lared'); ?>">
                                    <span class="single-top-banner__stat-number"><?php echo esc_html($reading_minutes); ?></span>
                                    <span class="single-top-banner__stat-label"><?php esc_html_e('分钟', 'lared'); ?></span>
                                    <span class="single-top-banner__tooltip"><?php esc_html_e('阅读时间', 'lared'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-[1280px]">
                <div class="single-content-wrap box-border pt-7 pb-10 max-[900px]:pt-[22px] max-[900px]:pb-7">
                    <?php if (!empty($single_toc_items)) : ?>
                        <aside class="single-side-toc" aria-label="<?php esc_attr_e('文章目录', 'lared'); ?>">
                            <nav class="single-side-toc__nav" aria-label="<?php esc_attr_e('文章目录导航', 'lared'); ?>">
                                <?php foreach ($single_toc_items as $index => $toc_item) : ?>
                                    <a
                                        class="single-side-toc__item level-<?php echo (int) $toc_item['level']; ?> <?php echo 0 === $index ? 'is-active' : ''; ?>"
                                        href="#<?php echo esc_attr((string) $toc_item['id']); ?>"
                                        data-single-toc-link
                                    >
                                        <?php echo esc_html((string) $toc_item['label']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        </aside>
                    <?php endif; ?>

                    <article class="single-article-content page-content prose prose-neutral max-w-none min-w-0 mt-[6px] text-[var(--color-body)] [&_hr]:hidden">
                        <?php echo wp_kses_post($article_content); ?>
                    </article>

                    <div class="single-footer-meta" aria-label="Article Footer Meta">
                        <div class="single-footer-meta__left">
                            <span class="single-footer-meta__item">
                                <strong><?php esc_html_e('作者', 'lared'); ?></strong>
                                <?php if ('' !== $author_url) : ?>
                                    <a class="single-footer-meta__author-link" href="<?php echo esc_url($author_url); ?>"><?php echo esc_html(get_the_author()); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html(get_the_author()); ?>
                                <?php endif; ?>
                                <?php esc_html_e('本文采用', 'lared'); ?>
                                <a class="single-footer-meta__license-link" href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-NC-SA 4.0</a>
                                <?php esc_html_e('许可协议，转载请注明来源。', 'lared'); ?>
                            </span>
                        </div>

                        <div class="single-footer-meta__right">
                            <span class="single-footer-meta__keywords">
                                <?php if (!empty($tags)) : ?>
                                    <?php foreach ($tags as $tag) : ?>
                                        <a class="single-footer-meta__tag" href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"><span class="single-footer-meta__tag-text"><?php echo esc_html($tag->name); ?></span></a>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <span class="single-footer-meta__tag is-empty"><?php esc_html_e('None', 'lared'); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <nav class="single-post-nav" aria-label="Post Navigation">
                        <div class="single-post-nav__item single-post-nav__item--prev">
                            <span class="single-post-nav__label"><?php esc_html_e('上一篇', 'lared'); ?></span>
                            <?php if ($previous_post instanceof WP_Post) : ?>
                                <a class="single-post-nav__link" href="<?php echo esc_url(get_permalink($previous_post->ID)); ?>"><?php echo esc_html(get_the_title($previous_post->ID)); ?></a>
                            <?php else : ?>
                                <span class="single-post-nav__empty"><?php esc_html_e('暂无上一篇', 'lared'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="single-post-nav__item single-post-nav__item--next">
                            <span class="single-post-nav__label"><?php esc_html_e('下一篇', 'lared'); ?></span>
                            <?php if ($next_post instanceof WP_Post) : ?>
                                <a class="single-post-nav__link" href="<?php echo esc_url(get_permalink($next_post->ID)); ?>"><?php echo esc_html(get_the_title($next_post->ID)); ?></a>
                            <?php else : ?>
                                <span class="single-post-nav__empty"><?php esc_html_e('暂无下一篇', 'lared'); ?></span>
                            <?php endif; ?>
                        </div>
                    </nav>
                </div>
            </section>

            <?php comments_template(); ?>
        </main>

        <?php
    endwhile;
endif;

get_footer();
