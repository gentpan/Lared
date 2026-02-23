<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?> data-img-animation="<?php echo esc_attr(get_option('pan_image_load_animation', 'none')); ?>">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('theme-body'); ?>>
<?php wp_body_open(); ?>
<header class="fixed inset-x-0 top-0 z-[60] border-b border-[#d9d9d9] bg-[#ffffff]">
    <div class="mx-auto flex h-16 w-full max-w-[1280px] items-center justify-between px-[18px] box-border">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="text-[31px] font-medium leading-none tracking-[0] text-[var(--color-accent)] no-underline" style="font-family: 'Nano Sans', 'Noto Sans SC', sans-serif;"><?php bloginfo('name'); ?></a>

        <nav class="nav-wrap" aria-label="Primary Navigation">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'nav',
                'fallback_cb' => 'pan_primary_menu_fallback',
            ]);
            ?>
        </nav>

        <div class="flex items-center gap-[10px]">
            <span class="header-loading" data-header-loading aria-hidden="true"></span>
            
            <button type="button" class="header-search-trigger" data-search-open aria-label="<?php esc_attr_e('Search', 'pan'); ?>">
                <i class="fa-solid fa-magnifying-glass header-search-trigger-icon" aria-hidden="true"></i>
                <span class="header-search-trigger-text"><?php esc_html_e('Search docs', 'pan'); ?></span>
                <kbd class="header-search-trigger-kbd" data-search-kbd></kbd>
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
