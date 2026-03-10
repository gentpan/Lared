<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?> data-img-animation="<?php echo esc_attr(get_option('lared_image_load_animation', 'none')); ?>">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>

</head>

<body <?php body_class('theme-body'); ?>>
    <?php wp_body_open(); ?>
    <header class="fixed inset-x-0 top-0 z-[60] border-b border-[#d9d9d9] bg-[#ffffff]">
        <div class="mx-auto flex h-16 w-full max-w-[1400px] items-center justify-between pl-0 pr-0 box-border">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title-link flex items-center gap-[8px] text-[31px] font-medium leading-none tracking-[0] text-[var(--color-accent)] no-underline" style="font-family: -apple-system, BlinkMacSystemFont, 'PingFang SC', 'Microsoft YaHei', 'Helvetica Neue', sans-serif;">
                <?php
                $site_logo_url  = (string) get_option('lared_site_logo_url', '');
                $site_logo_icon = (string) get_option('lared_site_logo_icon', '');
                if ('' !== $site_logo_url) :
                ?>
                    <img class="site-logo-img" src="<?php echo esc_url($site_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                <?php elseif ('' !== $site_logo_icon) : ?>
                    <i class="site-logo-icon <?php echo esc_attr($site_logo_icon); ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php bloginfo('name'); ?>
            </a>

            <nav class="nav-wrap" aria-label="Primary Navigation">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'nav',
                    'fallback_cb' => 'lared_primary_menu_fallback',
                ]);
                ?>
            </nav>

            <div class="flex items-center gap-[10px]">
                <span class="header-loading" data-header-loading aria-hidden="true"></span>

                <form role="search" method="get" class="header-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <i class="fa-solid fa-magnifying-glass search-icon" aria-hidden="true"></i>
                    <label class="sr-only" for="header-search-input"><?php esc_html_e('Search for:', 'lared'); ?></label>
                    <input id="header-search-input" type="search" name="s" placeholder="<?php esc_attr_e('搜索文章...', 'lared'); ?>" autocomplete="off" />
                    <button type="submit" class="header-search-submit" aria-label="<?php esc_attr_e('Search', 'lared'); ?>" data-search-kbd-btn>
                        <kbd data-search-kbd></kbd>
                    </button>
                </form>

                <!-- 移动端搜索按钮 -->
                <button type="button" class="mobile-search-btn" data-mobile-search-btn aria-label="<?php esc_attr_e('搜索', 'lared'); ?>">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </button>

                <!-- 移动端 TOC 按钮（仅文章页显示） -->
                <button type="button" class="mobile-toc-header-btn" data-mobile-toc-btn aria-label="<?php esc_attr_e('目录', 'lared'); ?>">
                    <i class="fa-solid fa-list-ul" aria-hidden="true"></i>
                </button>

                <!-- 移动端汉堡菜单按钮 -->
                <button type="button" class="mobile-menu-btn" data-mobile-menu-btn aria-label="<?php esc_attr_e('菜单', 'lared'); ?>" aria-expanded="false">
                    <span class="mobile-menu-btn__line"></span>
                    <span class="mobile-menu-btn__line"></span>
                    <span class="mobile-menu-btn__line"></span>
                </button>

            </div>
        </div>
    </header>
    <?php
    if (is_front_page()) {
        $barba_namespace = 'home';
    } elseif (is_single()) {
        $barba_namespace = 'single';
    } elseif (is_page()) {
        $barba_namespace = 'page';
    } else {
        $barba_namespace = 'archive';
    }
    ?>
    <div data-barba="wrapper">
        <div data-barba="container" data-barba-namespace="<?php echo esc_attr($barba_namespace); ?>">
            <?php // PJAX 不会重新执行 wp_footer 中的脚本，将 nonce 放在容器内以便每次导航自动刷新 ?>
            <script id="lared-pjax-nonce" type="application/json"><?php echo wp_json_encode([
                'commentSubmitNonce' => wp_create_nonce('lared_comment_submit'),
                'commentEditNonce'   => wp_create_nonce('lared_comment_edit'),
                'loginNonce'         => wp_create_nonce('lared_login_nonce'),
                'isLoggedIn'         => is_user_logged_in(),
            ]); ?></script>