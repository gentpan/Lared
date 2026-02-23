<?php
/**
 * 代码运行器功能
 * 支持 HTML/CSS/JS 实时预览
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 代码运行器短代码
 * 使用方式:
 * [code_runner html="<h1>Hello</h1>" css="h1{color:red;}" js="console.log('hi');" height="300" show_code="yes"]
 * 
 * 或在内容中:
 * [code_runner height="400" show_code="yes"]
 * <html>
 *   <h1>标题</h1>
 * </html>
 * <css>
 *   h1 { color: red; }
 * </css>
 * <js>
 *   document.querySelector('h1').onclick = () => alert('点击');
 * </js>
 * [/code_runner]
 */
function lared_code_runner_shortcode($atts, $content = null) {
    // 默认参数
    $atts = shortcode_atts([
        'html' => '',
        'css' => '',
        'js' => '',
        'height' => '300',
        'show_code' => 'yes',
        'title' => '代码预览',
    ], $atts, 'code_runner');

    $height = intval($atts['height']);
    $show_code = $atts['show_code'] === 'yes';
    $title = sanitize_text_field($atts['title']);

    // 解析内容中的 html/css/js 标签
    $html_code = $atts['html'];
    $css_code = $atts['css'];
    $js_code = $atts['js'];

    if (!empty($content)) {
        // 解析 <html> 标签
        if (preg_match('/<html>(.*?)<\/html>/s', $content, $matches)) {
            $html_code = trim($matches[1]);
        }
        // 解析 <css> 标签
        if (preg_match('/<css>(.*?)<\/css>/s', $content, $matches)) {
            $css_code = trim($matches[1]);
        }
        // 解析 <js> 标签
        if (preg_match('/<js>(.*?)<\/js>/s', $content, $matches)) {
            $js_code = trim($matches[1]);
        }
    }

    // 生成唯一的 iframe ID
    $iframe_id = 'code-runner-' . wp_rand(1000, 9999);

    // 构建完整的 HTML 文档
    $full_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* 基础重置 */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 20px;
            background: #fff;
        }
        /* 用户自定义 CSS */
        ' . wp_strip_all_tags($css_code) . '
    </style>
</head>
<body>
    ' . $html_code . '
    <script>
        // 错误处理
        window.onerror = function(msg, url, line) {
            console.error("Error: " + msg + " at line " + line);
            return false;
        };
        
        // 用户自定义 JS
        try {
            ' . wp_strip_all_tags($js_code) . '
        } catch (e) {
            console.error(e);
        }
    </script>
</body>
</html>';

    // 编码 iframe 内容
    $data_uri = 'data:text/html;charset=utf-8,' . rawurlencode($full_html);

    // 开始输出
    $output = '<div class="lared-code-runner">';
    
    // 标题栏
    $output .= '<div class="cr-header">';
    $output .= '<span class="cr-title"><i class="fa-solid fa-code"></i> ' . esc_html($title) . '</span>';
    $output .= '<span class="cr-badges">';
    if ($html_code) $output .= '<span class="cr-badge cr-html">HTML</span>';
    if ($css_code) $output .= '<span class="cr-badge cr-css">CSS</span>';
    if ($js_code) $output .= '<span class="cr-badge cr-js">JS</span>';
    $output .= '</span>';
    $output .= '</div>';

    // 预览区域
    $output .= '<div class="cr-preview">';
    $output .= '<iframe id="' . esc_attr($iframe_id) . '" src="' . esc_attr($data_uri) . '" style="width:100%;height:' . $height . 'px;border:none;" sandbox="allow-scripts allow-same-origin"></iframe>';
    $output .= '</div>';

    // 代码展示区域
    if ($show_code && ($html_code || $css_code || $js_code)) {
        $output .= '<div class="cr-code-section">';
        $output .= '<div class="cr-tabs">';
        
        $tab_index = 0;
        if ($html_code) {
            $output .= '<button class="cr-tab' . ($tab_index === 0 ? ' active' : '') . '" data-tab="html-' . $iframe_id . '">HTML</button>';
            $tab_index++;
        }
        if ($css_code) {
            $output .= '<button class="cr-tab' . ($tab_index === 0 ? ' active' : '') . '" data-tab="css-' . $iframe_id . '">CSS</button>';
            $tab_index++;
        }
        if ($js_code) {
            $output .= '<button class="cr-tab' . ($tab_index === 0 ? ' active' : '') . '" data-tab="js-' . $iframe_id . '">JavaScript</button>';
        }
        
        $output .= '</div>';
        
        $output .= '<div class="cr-code-panels">';
        
        if ($html_code) {
            $output .= '<div class="cr-panel active" id="html-' . $iframe_id . '">';
            $output .= '<pre><code class="language-html">' . esc_html($html_code) . '</code></pre>';
            $output .= '</div>';
        }
        if ($css_code) {
            $output .= '<div class="cr-panel" id="css-' . $iframe_id . '">';
            $output .= '<pre><code class="language-css">' . esc_html($css_code) . '</code></pre>';
            $output .= '</div>';
        }
        if ($js_code) {
            $output .= '<div class="cr-panel" id="js-' . $iframe_id . '">';
            $output .= '<pre><code class="language-javascript">' . esc_html($js_code) . '</code></pre>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('code_runner', 'lared_code_runner_shortcode');

/**
 * 代码运行器样式
 */
function lared_code_runner_styles() {
    echo '<style>
/* 代码运行器样式 */
.lared-code-runner {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    margin: 20px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* 头部 */
.cr-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.cr-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.cr-title i {
    color: var(--color-accent, #f53004);
    margin-right: 6px;
}

.cr-badges {
    display: flex;
    gap: 6px;
}

.cr-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.cr-badge.cr-html { background: #fee2e2; color: #dc2626; }
.cr-badge.cr-css { background: #dbeafe; color: #2563eb; }
.cr-badge.cr-js { background: #fef3c7; color: #d97706; }

/* 预览区域 */
.cr-preview {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
}

.cr-preview iframe {
    display: block;
    background: #fff;
}

/* 代码区域 */
.cr-code-section {
    background: #1f2937;
}

.cr-tabs {
    display: flex;
    border-bottom: 1px solid #374151;
}

.cr-tab {
    padding: 10px 16px;
    background: transparent;
    border: none;
    color: #9ca3af;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.cr-tab:hover {
    color: #fff;
    background: #374151;
}

.cr-tab.active {
    color: #fff;
    background: #374151;
    border-bottom: 2px solid var(--color-accent, #f53004);
}

.cr-code-panels {
    position: relative;
}

.cr-panel {
    display: none;
    max-height: 300px;
    overflow: auto;
}

.cr-panel.active {
    display: block;
}

.cr-panel pre {
    margin: 0;
    padding: 16px;
    background: #1f2937;
}

.cr-panel code {
    font-family: var(--font-code, monospace);
    font-size: 13px;
    line-height: 1.6;
}

/* PrismJS 适配 */
.cr-panel pre[class*="language-"] {
    background: #1f2937 !important;
    margin: 0;
    padding: 16px;
}
</style>';
}
add_action('wp_head', 'lared_code_runner_styles');

/**
 * 代码运行器 JavaScript
 */
function lared_code_runner_scripts() {
    echo '<script>
(function() {
    // Tab 切换功能
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("cr-tab")) {
            var tab = e.target;
            var tabId = tab.getAttribute("data-tab");
            var container = tab.closest(".cr-code-section");
            
            // 切换 tab 按钮状态
            container.querySelectorAll(".cr-tab").forEach(function(t) {
                t.classList.remove("active");
            });
            tab.classList.add("active");
            
            // 切换面板
            container.querySelectorAll(".cr-panel").forEach(function(p) {
                p.classList.remove("active");
            });
            document.getElementById(tabId).classList.add("active");
        }
    });
})();
</script>';
}
add_action('wp_footer', 'lared_code_runner_scripts', 99);
