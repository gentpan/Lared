<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
</div><!-- /data-barba="container" -->
</div><!-- /data-barba="wrapper" -->
<?php
// 浮动音乐播放器（非首页显示，竖排贴左边）
$lared_float_music_raw = trim((string) get_option('lared_music_playlist', ''));
if ('' !== $lared_float_music_raw) :
    $lared_float_lines = array_filter(array_map('trim', explode("\n", $lared_float_music_raw)));
    $lared_float_tracks = [];
    foreach ($lared_float_lines as $fl) {
        $fp = array_map('trim', explode('|', $fl, 3));
        if (count($fp) >= 2 && '' !== $fp[1]) {
            $lared_float_tracks[] = [
                'name' => $fp[0],
                'url'  => $fp[1],
                'lrc'  => isset($fp[2]) ? $fp[2] : '',
            ];
        }
    }
    if (!empty($lared_float_tracks)) :
        $lared_float_visible = '1' === (string) get_option('lared_music_float_visible', '1') ? '1' : '0';
?>
<div class="lared-music-float" id="lared-music-float" data-tracks="<?php echo esc_attr(wp_json_encode($lared_float_tracks)); ?>" data-float-visible="<?php echo esc_attr($lared_float_visible); ?>">
    <span class="lared-music-float-name" data-music="name"><?php echo esc_html($lared_float_tracks[0]['name']); ?></span>
    <div class="lared-music-float-controls" data-music="controls">
        <button type="button" class="lared-music-float-btn" data-music="prev" title="<?php esc_attr_e('上一首', 'lared'); ?>">
            <i class="fa-solid fa-backward-step" aria-hidden="true"></i>
        </button>
        <button type="button" class="lared-music-float-btn" data-music="toggle" title="<?php esc_attr_e('播放/暂停', 'lared'); ?>">
            <i class="fa-solid fa-play" aria-hidden="true"></i>
        </button>
        <button type="button" class="lared-music-float-btn" data-music="next" title="<?php esc_attr_e('下一首', 'lared'); ?>">
            <i class="fa-solid fa-forward-step" aria-hidden="true"></i>
        </button>
    </div>
    <div class="lared-music-float-progress-row">
        <span class="lared-music-float-time" data-music="time-current">0:00</span>
        <div class="lared-music-float-progress" data-music="progress">
            <div class="lared-music-float-progress-fill" data-music="progress-fill"></div>
            <div class="lared-music-float-progress-dot" data-music="progress-dot"></div>
        </div>
        <span class="lared-music-float-time" data-music="time-duration">0:00</span>
    </div>
</div>
<?php endif; endif; ?>
<footer class="site-footer">
    <div class="site-footer-inner">
        <p class="site-footer-copy">© <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
        <?php
        global $wpdb;
        $home_views = (int) get_option('lared_home_views', 0);
        $page_views = (int) $wpdb->get_var(
            "SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = 'post_views' AND p.post_status = 'publish' AND p.post_type = 'post'"
        );
        $total_views = $home_views + $page_views;
        $last_visitor = get_option('lared_last_visitor', []);
        $visitor_location = '';
        $visitor_flag = '';
        if (!empty($last_visitor['city'])) {
            $visitor_location = $last_visitor['city'];
        } elseif (!empty($last_visitor['regionName'])) {
            $visitor_location = $last_visitor['regionName'];
        } elseif (!empty($last_visitor['country'])) {
            $visitor_location = $last_visitor['country'];
        }
        if (!empty($last_visitor['countryCode'])) {
            $visitor_flag = strtolower($last_visitor['countryCode']);
        }
        ?>
        <div class="footer-visitor-info">
            <span class="footer-visitor-stat">
                <i class="fa-sharp fa-light fa-eye" aria-hidden="true"></i>
                <?php esc_html_e('总浏览量', 'lared'); ?>
                <span class="footer-visitor-value" data-total-views><?php echo esc_html(lared_format_number($total_views)); ?></span>
            </span>
            <?php if ($visitor_location) : ?>
                <span class="footer-visitor-stat">
                    <i class="fa-sharp fa-light fa-location-dot" aria-hidden="true"></i>
                    <?php esc_html_e('最近访客来自', 'lared'); ?>
                    <?php if ($visitor_flag) : ?>
                        <span class="fi fi-<?php echo esc_attr($visitor_flag); ?> footer-visitor-flag"></span>
                    <?php endif; ?>
                    <span class="footer-visitor-value"><?php echo esc_html($visitor_location); ?></span>
                </span>
            <?php endif; ?>
        </div>
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
                <span class="rss-tooltip"><?php esc_html_e('点击复制订阅地址', 'lared'); ?></span>
            </button>
            <?php if (is_user_logged_in()) : ?>
                <?php $current_user = wp_get_current_user(); ?>
                <div class="footer-avatar-wrapper">
                    <a href="<?php echo esc_url(admin_url()); ?>" class="site-footer-icon-link footer-user-avatar" title="<?php echo esc_attr($current_user->display_name); ?>">
                        <?php echo get_avatar($current_user->ID, 30, '', '', ['class' => 'h-full w-full object-cover']); ?>
                    </a>
                    <div class="footer-avatar-menu">
                        <a href="<?php echo esc_url(admin_url()); ?>" class="footer-avatar-menu-item">
                            <i class="fa-solid fa-gauge" aria-hidden="true"></i>
                            <?php esc_html_e('仪表盘', 'lared'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="footer-avatar-menu-item">
                            <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
                            <?php esc_html_e('个人资料', 'lared'); ?>
                        </a>
                        <div class="footer-avatar-menu-divider"></div>
                        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="footer-avatar-menu-item footer-avatar-menu-logout" data-no-pjax>
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            <?php esc_html_e('退出登录', 'lared'); ?>
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="footer-login-wrapper relative">
                    <button type="button" class="site-footer-icon-link footer-login-btn" aria-label="Login" data-login-toggle>
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </button>
                    <div class="footer-login-dropdown" data-login-dropdown>
                        <h4 class="footer-login-title"><i class="fa-solid fa-lock" aria-hidden="true"></i> <?php esc_html_e('管理员登录', 'lared'); ?></h4>
                        <form data-login-form>
                            <div class="footer-login-field">
                                <label for="footer-login-user"><?php esc_html_e('用户名', 'lared'); ?></label>
                                <input type="text" id="footer-login-user" name="log" autocomplete="username" required />
                            </div>
                            <div class="footer-login-field">
                                <label for="footer-login-pass"><?php esc_html_e('密码', 'lared'); ?></label>
                                <input type="password" id="footer-login-pass" name="pwd" autocomplete="current-password" required />
                            </div>
                            <div class="footer-login-remember">
                                <label><input type="checkbox" name="rememberme" value="forever" /> <?php esc_html_e('记住我', 'lared'); ?></label>
                            </div>
                            <div class="footer-login-error" data-login-error></div>
                            <button type="submit" class="footer-login-submit" data-login-submit>
                                <span class="footer-login-submit-text"><?php esc_html_e('登录', 'lared'); ?></span>
                                <span class="footer-login-submit-loading" style="display:none"><i class="fa-solid fa-spinner fa-spin"></i> <?php esc_html_e('登录中...', 'lared'); ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>
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
