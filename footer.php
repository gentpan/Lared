<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
</div><!-- /data-barba="container" -->
</div><!-- /data-barba="wrapper" -->
<footer class="site-footer">
    <div class="site-footer-inner">
        <p class="site-footer-copy">© <?php echo esc_html(gmdate('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
        <div class="site-footer-icons" aria-label="Footer social links">
            <a class="site-footer-icon-link" href="https://github.com/gentpan/Lared" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                <i class="fa-brands fa-github" aria-hidden="true"></i>
            </a>
            <a class="site-footer-icon-link" href="https://xifeng.net" target="_blank" rel="noopener noreferrer" aria-label="西风" title="西风">
                <i class="fa-brands fa-red-river" aria-hidden="true"></i>
            </a>
            <a class="site-footer-icon-link" href="https://wordpress.org" target="_blank" rel="noopener noreferrer" aria-label="WordPress">
                <i class="fa-brands fa-wordpress" aria-hidden="true"></i>
            </a>
            <a class="site-footer-icon-link" href="https://tailwindcss.com" target="_blank" rel="noopener noreferrer" aria-label="Tailwind CSS">
                <i class="fa-brands fa-tailwind-css" aria-hidden="true"></i>
            </a>
            <button type="button" class="site-footer-icon-link rss-btn" aria-label="RSS Feed" data-rss-copy data-feed-url="<?php echo esc_url(get_feed_link()); ?>">
                <i class="fa-solid fa-rss" aria-hidden="true"></i>
            </button>
            <?php if (is_user_logged_in()) : ?>
                <?php $current_user = wp_get_current_user(); ?>
                <a href="<?php echo esc_url(admin_url()); ?>" class="site-footer-icon-link footer-user-avatar" title="<?php echo esc_attr($current_user->display_name); ?>">
                    <?php echo get_avatar($current_user->ID, 30, '', '', ['class' => 'h-full w-full object-cover']); ?>
                </a>
            <?php else : ?>
                <div class="footer-login-wrapper relative">
                    <button type="button" class="site-footer-icon-link footer-login-btn" aria-label="Login" data-login-toggle>
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </button>
                    <div class="footer-login-dropdown hidden absolute right-0 bottom-full mb-2 w-[280px] rounded-lg border border-[#d9d9d9] bg-white p-5 shadow-lg" data-login-dropdown>
                        <h4 class="mb-4 text-[16px] font-semibold text-[var(--color-title)]"><?php esc_html_e('管理员登录', 'lared'); ?></h4>
                        <form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
                            <div class="mb-3">
                                <label for="footer-login-user" class="mb-1 block text-[12px] font-medium text-[var(--color-body)]"><?php esc_html_e('用户名', 'lared'); ?></label>
                                <input type="text" id="footer-login-user" name="log" class="w-full rounded border border-[#d9d9d9] px-3 py-2 text-[13px] text-[#202020] outline-none focus:border-[var(--color-accent)]" required />
                            </div>
                            <div class="mb-4">
                                <label for="footer-login-pass" class="mb-1 block text-[12px] font-medium text-[var(--color-body)]"><?php esc_html_e('密码', 'lared'); ?></label>
                                <input type="password" id="footer-login-pass" name="pwd" class="w-full rounded border border-[#d9d9d9] px-3 py-2 text-[13px] text-[#202020] outline-none focus:border-[var(--color-accent)]" required />
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-1.5 text-[12px] text-[var(--color-body)]">
                                    <input type="checkbox" name="rememberme" value="forever" />
                                    <?php esc_html_e('记住我', 'lared'); ?>
                                </label>
                            </div>
                            <button type="submit" class="mt-3 w-full rounded bg-[var(--color-accent)] py-2 text-[14px] font-medium text-white hover:opacity-90 transition-opacity">
                                <?php esc_html_e('登录', 'lared'); ?>
                            </button>
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url()); ?>" />
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>
<div id="lared-aplayer" aria-label="Music Player"></div>
<button class="back-to-top" type="button" aria-label="Back to top" data-back-to-top>
    <i class="fa-regular fa-arrow-up" aria-hidden="true"></i>
</button>

<!-- Search Modal -->
<div class="search-modal" data-search-modal aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e('Search', 'lared'); ?>">
    <div class="search-modal-overlay" data-search-close></div>
    <div class="search-modal-container">
        <div class="search-modal-header">
            <form role="search" method="get" class="search-modal-form" action="<?php echo esc_url(home_url('/')); ?>">
                <i class="fa-solid fa-magnifying-glass search-modal-icon" aria-hidden="true"></i>
                <label class="sr-only" for="search-modal-input"><?php esc_html_e('Search for:', 'lared'); ?></label>
                <input id="search-modal-input" type="search" name="s" placeholder="<?php esc_attr_e('搜索文章...', 'lared'); ?>" class="search-modal-input" autocomplete="off" />
                <kbd class="search-modal-esc">ESC</kbd>
            </form>
        </div>
        <div class="search-modal-body" data-search-results>
            <div class="search-modal-hint">
                <p><?php esc_html_e('输入关键词搜索文章', 'lared'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
