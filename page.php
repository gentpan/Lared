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
        ?>

        <main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
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

            <section class="listing-content">
                <div class="overflow-hidden border border-[#d9d9d9] bg-[#f3f3f3]">
                    <?php
                    $page_cover_image_html = pan_get_post_image_html(
                        (int) get_the_ID(),
                        'full',
                        ['class' => 'block h-auto w-full']
                    );
                    ?>
                    <?php if ('' !== $page_cover_image_html) : ?>
                        <?php echo wp_kses_post($page_cover_image_html); ?>
                    <?php else : ?>
                        <div class="w-full aspect-[16/7] bg-[var(--color-accent)]" aria-hidden="true"></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="listing-content">
                <div class="box-border border-x border-b border-[#d9d9d9] px-8 pt-7 pb-10 max-[900px]:px-[18px] max-[900px]:pt-[22px] max-[900px]:pb-7">
                    <div class="grid grid-cols-[minmax(0,1fr)_240px] gap-[34px] max-[900px]:grid-cols-1 max-[900px]:gap-5">
                        <article class="page-content prose prose-neutral max-w-none min-w-0 text-[var(--color-body)]">
                            <?php the_content(); ?>
                        </article>

                        <aside class="flex flex-col gap-[14px] border-l border-[#d9d9d9] pl-5 max-[900px]:border-l-0 max-[900px]:border-t max-[900px]:border-[#d9d9d9] max-[900px]:pt-[14px] max-[900px]:pl-0" aria-label="Page Meta">
                            <div class="grid gap-[3px] text-[13px] text-[#666]">
                                <span>更新时间</span>
                                <strong class="text-[14px] font-medium text-[#222]"><?php echo esc_html(get_the_modified_date('F j, Y')); ?></strong>
                            </div>
                            <div class="grid gap-[3px] text-[13px] text-[#666]">
                                <span>作者</span>
                                <strong class="text-[14px] font-medium text-[#222]"><?php echo esc_html(get_the_author()); ?></strong>
                            </div>
                            <div class="grid gap-[3px] text-[13px] text-[#666]">
                                <span>阅读时长</span>
                                <strong class="text-[14px] font-medium text-[#222]"><?php echo esc_html($reading_minutes); ?> min read</strong>
                            </div>
                            <div class="grid gap-[3px] text-[13px] text-[#666]">
                                <span>分享</span>
                                <a class="text-[14px] font-medium text-[#222] no-underline hover:text-[var(--color-accent)]" href="<?php echo esc_url(get_permalink()); ?>">Copy Link ↗</a>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </main>

        <?php
    endwhile;
endif;

get_footer();
