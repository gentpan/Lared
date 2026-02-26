<?php
/**
 * TinyMCE 编辑器「排版指南」按钮与样式
 *
 * @package Lared
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ===========================================
   TinyMCE「排版指南」按钮
   =========================================== */

// 注册 TinyMCE 外部插件
function lared_mce_external_plugins(array $plugins): array
{
    $url = get_template_directory_uri() . '/assets/js/editor-admin.js';
    $plugins['laredThemeGuide']  = $url;
    return $plugins;
}
add_filter('mce_external_plugins', 'lared_mce_external_plugins');

// 将按钮添加到工具栏
function lared_mce_buttons(array $buttons): array
{
    $buttons[] = 'lared_theme_guide';
    return $buttons;
}
add_filter('mce_buttons', 'lared_mce_buttons');

// 编辑页面注入排版指南模态窗 CSS
function lared_editor_theme_guide_css(): void
{
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['post', 'page'], true)) {
        return;
    }
    echo '<style id="lared-guide-modal-css">
/* ===== 主题排版指南模态窗 ===== */
.lared-guide-backdrop {
    position: fixed; inset: 0; z-index: 100100;
    display: flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
    opacity: 0; transition: opacity .2s ease;
}
.lared-guide-backdrop.is-visible { opacity: 1; }
.lared-guide-modal {
    position: relative; width: 92%; max-width: 720px; max-height: 85vh;
    display: flex; flex-direction: column;
    background: #fff; border: 1px solid #d9d9d9;
    box-shadow: 0 8px 32px rgba(0,0,0,.15);
    transform: translateY(12px) scale(.97); transition: transform .2s ease;
}
.lared-guide-backdrop.is-visible .lared-guide-modal { transform: translateY(0) scale(1); }
.lared-guide-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid #e5e5e5;
}
.lared-guide-header-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.lared-guide-title { font-size: 15px; font-weight: 600; color: #1d2327; }
.lared-guide-mode {
    display: inline-flex; align-items: center; font-size: 11px; padding: 2px 8px;
    border-radius: 3px; font-weight: 500;
}
.lared-guide-mode.is-text { background: #ecfdf5; color: #059669; }
.lared-guide-mode.is-visual { background: #eff6ff; color: #2563eb; }
.lared-guide-close {
    width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
    border: none; background: transparent; font-size: 22px; color: #999; cursor: pointer;
    border-radius: 0; padding: 0; line-height: 1; flex-shrink: 0;
}
.lared-guide-close:hover { color: #333; }
.lared-guide-notice {
    padding: 8px 20px; background: #fffbeb; border-bottom: 1px solid #e5e5e5;
    font-size: 12px; color: #92400e; line-height: 1.5;
}
.lared-guide-tabs {
    display: flex; gap: 0; border-bottom: 1px solid #e5e5e5; overflow-x: auto;
}
.lared-guide-tab {
    padding: 10px 18px; font-size: 13px; font-weight: 500; color: #666;
    background: transparent; border: none; border-bottom: 2px solid transparent;
    cursor: pointer; transition: color .15s, border-color .15s; white-space: nowrap;
}
.lared-guide-tab:hover { color: #333; }
.lared-guide-tab.is-active { color: #1d2327; border-bottom-color: #f53004; }
.lared-guide-body { flex: 1; overflow-y: auto; padding: 16px 20px; }
.lared-guide-card {
    margin-bottom: 16px; border: 1px solid #e8e8e8; background: #fafafa;
}
.lared-guide-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; background: #fff; border-bottom: 1px solid #e8e8e8;
}
.lared-guide-card-title { font-size: 14px; font-weight: 600; color: #1d2327; }
.lared-guide-card-actions { display: flex; gap: 6px; }
.lared-guide-copy-btn,
.lared-guide-insert-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 28px; padding: 0 12px; font-size: 12px; font-weight: 500;
    border: 1px solid #d9d9d9; cursor: pointer; transition: all .15s;
    background: #fff; color: #555; border-radius: 0;
}
.lared-guide-copy-btn:hover { background: #f5f5f5; border-color: #bbb; }
.lared-guide-insert-btn {
    background: #f53004; color: #fff; border-color: #f53004;
}
.lared-guide-insert-btn:hover { background: #d42a03; border-color: #d42a03; }
.lared-guide-card-desc {
    margin: 0; padding: 8px 14px 4px; font-size: 12px; color: #888; line-height: 1.5;
}
.lared-guide-code {
    margin: 0; padding: 10px 14px; background: #282a36; color: #f8f8f2;
    font-size: 12px; line-height: 1.6; overflow-x: auto; white-space: pre-wrap;
    word-break: break-all; max-height: 200px;
}
.lared-guide-code code {
    font-family: "SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace;
    color: inherit; background: none; padding: 0; font-size: inherit;
}
@media (max-width: 600px) {
    .lared-guide-modal { max-width: 100%; max-height: 100vh; }
}
</style>';
}
add_action('admin_head', 'lared_editor_theme_guide_css');

// 编辑页面加载排版指南脚本
function lared_editor_enqueue_guide_script(): void
{
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['post', 'page'], true)) {
        return;
    }
    wp_enqueue_script(
        'lared-editor-admin',
        get_template_directory_uri() . '/assets/js/editor-admin.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('admin_enqueue_scripts', 'lared_editor_enqueue_guide_script');
