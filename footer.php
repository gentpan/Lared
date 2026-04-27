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
                'lrc'  => $fp[2] ?? '',
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
<?php endif;
endif; ?>
<footer class="site-footer">
    <span class="site-footer-watermark" aria-hidden="true">
        <i class="fa-brands fa-wordpress-simple"></i>
    </span>
    <div class="site-footer-inner">
        <p class="site-footer-copy">© <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
        <?php
        $total_views = lared_get_total_views();
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
        <?php
        $blogscn_icon_rel = '/assets/images/blogscn.png';
        $blogscn_icon_abs = get_template_directory() . $blogscn_icon_rel;
        $blogscn_icon_uri = get_template_directory_uri() . $blogscn_icon_rel;
        if (file_exists($blogscn_icon_abs)) {
            $blogscn_icon_uri .= '?ver=' . (string) filemtime($blogscn_icon_abs);
        }

        $blogsclub_icon_rel = '/assets/images/blogsclub.svg';
        $blogsclub_icon_abs = get_template_directory() . $blogsclub_icon_rel;
        $blogsclub_icon_uri = get_template_directory_uri() . $blogsclub_icon_rel;
        if (file_exists($blogsclub_icon_abs)) {
            $blogsclub_icon_uri .= '?ver=' . (string) filemtime($blogsclub_icon_abs);
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
            <a class="site-footer-icon-link site-footer-icon-link--foreverblog" href="https://www.foreverblog.cn/go.html" target="_blank" rel="noopener noreferrer" aria-label="十年博客之约">
                <i class="fa-brands fa-blogger" aria-hidden="true"></i>
                <span class="site-footer-icon-tooltip">十年博客之约</span>
            </a>
            <a class="site-footer-icon-link site-footer-icon-link--travellings" href="https://www.travellings.cn/go.html" target="_blank" rel="noopener noreferrer" aria-label="开往-友链接力">
                <i class="fa-sharp fa-solid fa-train-subway" aria-hidden="true"></i>
                <span class="site-footer-icon-tooltip">开往-友链接力</span>
            </a>
            <a class="site-footer-icon-link site-footer-icon-link--gomami" href="https://gomami.io/aff.php?aff=364" target="_blank" rel="noopener noreferrer sponsored" aria-label="GoMami HKG Turin Air">
                <svg class="site-footer-icon-svg site-footer-icon-svg--gomami" width="30" height="31" viewBox="0 0 30 31" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M16.544 0.0124521C17.0158 0.0135121 17.383 0.417552 17.3332 0.880842L16.3259 10.2501C16.2711 10.7594 15.9684 11.2103 15.5147 11.4585L10.9472 13.9565C10.5062 14.1977 9.97546 14.2182 9.51676 14.0118L0.596452 9.9981C0.164562 9.8038 1.75834e-06 9.2816 0.243972 8.8796L3.00877 4.32364C3.27173 3.89031 3.87458 3.81124 4.24298 4.16174L10.7215 10.3256C11.0274 10.6166 11.49 10.6758 11.8608 10.4714C12.2399 10.2625 12.431 9.8292 12.3276 9.4128L10.232 0.972512C10.109 0.476922 10.4896 -0.00115789 11.0064 2.10665e-06L16.544 0.0124521Z" fill="currentColor" />
                    <path d="M0.105894 20.8691C-0.129076 20.465 0.0416742 19.949 0.472904 19.76L9.19345 15.9368C9.66745 15.729 10.2144 15.7624 10.6589 16.0263L15.1335 18.6833C15.5655 18.9399 15.8488 19.3834 15.8971 19.879L16.8373 29.5142C16.8828 29.9807 16.5071 30.3825 16.0325 30.3749L10.6545 30.2885C10.143 30.2803 9.77215 29.8043 9.89535 29.314L12.0619 20.6918C12.1642 20.2847 11.9848 19.8595 11.6201 19.6445C11.2474 19.4248 10.7719 19.4781 10.4584 19.7746L4.10388 25.7869C3.73076 26.1399 3.12115 26.0534 2.8638 25.611L0.105894 20.8691Z" fill="currentColor" />
                    <path d="M26.7637 5.94474C26.5333 5.53818 25.9972 5.41788 25.6115 5.68619L17.8112 11.1121C17.3872 11.4071 17.1354 11.8877 17.1365 12.3999L17.1476 17.5556C17.1487 18.0535 17.3887 18.5213 17.7944 18.8167L25.6844 24.5611C26.0664 24.8392 26.6085 24.7272 26.8455 24.3212L29.5321 19.7199C29.7876 19.2823 29.5644 18.7236 29.0751 18.5763L20.4702 15.9844C20.0639 15.862 19.7865 15.4917 19.787 15.0723C19.7875 14.6436 20.0779 14.268 20.4966 14.1547L28.9842 11.8578C29.4825 11.7229 29.7204 11.162 29.4681 10.7167L26.7637 5.94474Z" fill="currentColor" />
                </svg>
                <span class="site-footer-icon-tooltip">GoMami HKG.Turin.Air</span>
            </a>
            <button type="button" class="site-footer-icon-link rss-btn" aria-label="RSS Feed" data-rss-copy data-feed-url="<?php echo esc_url(get_feed_link()); ?>">
                <i class="fa-solid fa-rss" aria-hidden="true"></i>
                <span class="rss-tooltip"><?php esc_html_e('点击复制订阅地址', 'lared'); ?></span>
            </button>
            <?php if (is_user_logged_in()) : ?>
                <?php $current_user = wp_get_current_user(); ?>
                <div class="footer-avatar-wrapper">
                    <a href="<?php echo esc_url(admin_url()); ?>" class="site-footer-icon-link footer-user-avatar">
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
            <button class="back-to-top" type="button" aria-label="Back to top" data-back-to-top>
                <i class="fa-regular fa-arrow-up" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</footer>

<!-- Search Modal -->
<dialog class="search-modal" data-search-modal aria-label="<?php esc_attr_e('Search', 'lared'); ?>">
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
</dialog>

<?php wp_footer(); ?>
</body>

</html>
