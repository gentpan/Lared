<?php
/**
 * 代码运行器 v5
 *
 * 功能：
 *   - [code_runner] shortcode 保护 HTML 代码不被 WordPress 破坏
 *   - 输出标准 Prism 代码块，JS 自动在 HTML 代码块上加运行按钮
 *   - 兼容旧格式 {{html}}...{{/html}} 标记
 *
 * 用法：
 *   [code_runner title="示例" height="400"]
 *   完整的 HTML 代码（可含 <style> <script>）
 *   [/code_runner]
 *
 * 架构：
 *   1. 在优先级 6 的 the_content 过滤器中，用 strpos 提取代码内容，
 *      存入全局数组 $GLOBALS['_lared_cr_store']，shortcode 只保留短 ID
 *   2. shortcode 处理时从全局数组取回完整代码，完全绕过 PCRE
 */

if (!defined('ABSPATH')) exit;

// 全局存储，key = 短 ID，value = 原始代码
$GLOBALS['_lared_cr_store'] = [];

/* ================================================================
 *  1. 内容保护 — 优先级 6
 *     在 wptexturize(8) / wpautop(10) / do_shortcode(11) 之前
 *     用 strpos 提取代码，存入全局数组，shortcode 属性只存短 ID
 *     完全不依赖任何正则 — 大段代码也不会失败
 * ================================================================ */
function lared_cr_protect_content($content) {
    if (strpos($content, '[code_runner') === false) return $content;

    $output = '';
    $pos    = 0;
    $len    = strlen($content);

    while ($pos < $len) {
        $start = strpos($content, '[code_runner', $pos);
        if ($start === false) {
            $output .= substr($content, $pos);
            break;
        }

        // 保留 shortcode 之前的内容
        $output .= substr($content, $pos, $start - $pos);

        // 找开标签结束的 ]
        $tagEnd = strpos($content, ']', $start + 12);
        if ($tagEnd === false) {
            $output .= substr($content, $start);
            break;
        }

        // 提取属性部分（[code_runner ... ] 中间的部分）
        $attrs = substr($content, $start + 12, $tagEnd - $start - 12);

        // 自闭合标签？ [code_runner .../]
        if (substr(trim($attrs), -1) === '/') {
            $output .= substr($content, $start, $tagEnd - $start + 1);
            $pos = $tagEnd + 1;
            continue;
        }

        // 找 [/code_runner]
        $closeTag = strpos($content, '[/code_runner]', $tagEnd + 1);
        if ($closeTag === false) {
            $output .= substr($content, $start);
            break;
        }

        // 提取代码内容
        $inner = substr($content, $tagEnd + 1, $closeTag - $tagEnd - 1);

        // 生成唯一短 ID，存入全局数组
        $id = 'cr_' . md5($inner . microtime(true) . mt_rand());
        $GLOBALS['_lared_cr_store'][$id] = $inner;

        // shortcode 只保留短 ID（几十字符），WordPress 解析毫无压力
        $output .= '[code_runner' . $attrs . ' _crid="' . $id . '"][/code_runner]';

        $pos = $closeTag + 14;
    }

    return $output;
}
add_filter('the_content', 'lared_cr_protect_content', 6);

add_filter('no_texturize_shortcodes', function ($list) {
    $list[] = 'code_runner';
    return $list;
});

/* ================================================================
 *  2. 短代码 — 从全局数组取回代码，输出 Prism 代码块
 * ================================================================ */
function lared_code_runner_shortcode($atts, $content = null) {
    $atts = shortcode_atts([
        'title'  => '代码预览',
        'height' => '400',
        '_crid'  => '',
    ], $atts, 'code_runner');

    $title  = sanitize_text_field($atts['title']);
    $height = max(200, intval($atts['height']));

    $code = '';

    /* ── 方式 A：从全局数组取回（正常路径） ── */
    $crid = $atts['_crid'];
    if ($crid !== '' && isset($GLOBALS['_lared_cr_store'][$crid])) {
        $raw = trim($GLOBALS['_lared_cr_store'][$crid]);

        // 兼容旧格式：{{html}}...{{/html}} 标记
        $html = $css = $js = '';
        if (strpos($raw, '{{html}}') !== false) {
            $s = strpos($raw, '{{html}}');
            $e = strpos($raw, '{{/html}}');
            if ($s !== false && $e !== false) {
                $html = trim(substr($raw, $s + 8, $e - $s - 8));
            }
        }
        if (strpos($raw, '{{css}}') !== false) {
            $s = strpos($raw, '{{css}}');
            $e = strpos($raw, '{{/css}}');
            if ($s !== false && $e !== false) {
                $css = trim(substr($raw, $s + 7, $e - $s - 7));
            }
        }
        if (strpos($raw, '{{js}}') !== false) {
            $s = strpos($raw, '{{js}}');
            $e = strpos($raw, '{{/js}}');
            if ($s !== false && $e !== false) {
                $js = trim(substr($raw, $s + 6, $e - $s - 6));
            }
        }

        if ($html !== '') {
            $code = $html;
            if ($css !== '' && stripos($html, '</style>') === false) {
                $code = '<style>' . "\n" . $css . "\n" . '</style>' . "\n" . $code;
            }
            if ($js !== '' && stripos($html, '</script>') === false) {
                $code .= "\n" . '<script>' . "\n" . $js . "\n" . '</script>';
            }
        } else {
            $code = $raw;
        }
    }

    /* ── 方式 B：回退到 $content ── */
    if ($code === '' && $content !== null && trim($content) !== '') {
        $code = trim($content);
        $code = preg_replace('/<br\s*\/?>\s*\n?/i', "\n", $code);
        $code = preg_replace('/<\/?p>/i', '', $code);
    }

    if ($code === '') return '';

    /* 输出标准 Prism 代码块 */
    return '<pre class="lared-prism-pre" data-cr-runnable="1"'
         . ' data-cr-title="' . esc_attr($title) . '"'
         . ' data-cr-height="' . $height . '">'
         . '<code class="language-markup">' . esc_html($code) . '</code>'
         . '</pre>';
}
add_shortcode('code_runner', 'lared_code_runner_shortcode');

/* ================================================================
 *  3. 保护 <pre><code> 内的原始 HTML — 优先级 7
 *     在 code_runner 保护(6) 之后、在 wptexturize(8) / wpautop(10) 之前
 *     将 <pre><code> 中包含未转义 HTML 标签的内容做 htmlspecialchars
 *     这样 wp_kses_post() 不会把代码中的标签当真正的 HTML 剥离
 *     使用 $double_encode = false 避免双重编码已有的实体
 * ================================================================ */
function lared_protect_code_blocks($content) {
    if (strpos($content, '<code') === false) return $content;

    $output = '';
    $pos    = 0;
    $len    = strlen($content);

    while ($pos < $len) {
        // 找 <pre 开标签
        $preStart = strpos($content, '<pre', $pos);
        if ($preStart === false) {
            $output .= substr($content, $pos);
            break;
        }

        // 保留 <pre 之前的内容
        $output .= substr($content, $pos, $preStart - $pos);

        // 找 <pre> 的关闭 >
        $preTagEnd = strpos($content, '>', $preStart + 4);
        if ($preTagEnd === false) {
            $output .= substr($content, $preStart);
            break;
        }

        $preTag = substr($content, $preStart, $preTagEnd - $preStart + 1);

        // 找 </pre>
        $preClose = strpos($content, '</pre>', $preTagEnd + 1);
        if ($preClose === false) {
            $output .= substr($content, $preStart);
            break;
        }

        // <pre> 和 </pre> 之间的完整内容
        $innerFull = substr($content, $preTagEnd + 1, $preClose - $preTagEnd - 1);

        // 检查内部是否有 <code 标签
        if (strpos($innerFull, '<code') !== false) {
            $codeStart = strpos($innerFull, '<code');
            $codeTagEnd = strpos($innerFull, '>', $codeStart + 5);

            if ($codeTagEnd !== false) {
                $codeTag = substr($innerFull, $codeStart, $codeTagEnd - $codeStart + 1);

                // 找 </code>（用最后出现的位置）
                $codeClose = strrpos($innerFull, '</code>');

                if ($codeClose !== false && $codeClose > $codeTagEnd) {
                    $codeInner = substr($innerFull, $codeTagEnd + 1, $codeClose - $codeTagEnd - 1);

                    // 如果内容包含字面 < 字符，说明有未转义的 HTML
                    // 使用 htmlspecialchars + $double_encode=false：
                    //   - 转义所有字面 < > & "
                    //   - 不会双重编码已有的 &lt; &amp; 等实体
                    if (strpos($codeInner, '<') !== false) {
                        $codeInner = htmlspecialchars($codeInner, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
                    }

                    $beforeCode = substr($innerFull, 0, $codeStart);
                    $afterCode  = substr($innerFull, $codeClose + 7);

                    $output .= $preTag . $beforeCode . $codeTag . $codeInner . '</code>' . $afterCode . '</pre>';
                    $pos = $preClose + 6;
                    continue;
                }
            }
        }

        // 如果没有 <code> 标签，直接保留原样
        $output .= $preTag . $innerFull . '</pre>';
        $pos = $preClose + 6;
    }

    return $output;
}
add_filter('the_content', 'lared_protect_code_blocks', 7);

/* ================================================================
 *  4. 允许 <pre> 和 <code> 标签上的 data-* 属性
 *     防止 wp_kses_post() 剥离 code_runner 所需的 data 属性
 * ================================================================ */
function lared_allow_code_data_attributes($tags, $context) {
    if ($context !== 'post') return $tags;

    // pre 标签
    if (isset($tags['pre'])) {
        $tags['pre']['data-cr-runnable'] = true;
        $tags['pre']['data-cr-title']    = true;
        $tags['pre']['data-cr-height']   = true;
        $tags['pre']['data-lared-copy-ready'] = true;
    }

    // code 标签
    if (isset($tags['code'])) {
        $tags['code']['data-language'] = true;
    }

    return $tags;
}
add_filter('wp_kses_allowed_html', 'lared_allow_code_data_attributes', 10, 2);
