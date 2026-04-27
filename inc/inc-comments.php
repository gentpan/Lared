<?php

if (!defined("ABSPATH")) {
    exit;
}


/**
 * 评论系统模块
 *
 * 包含：
 * - User-Agent 解析（浏览器名 + 版本 + 操作系统名 + 版本）
 * - IP 地理位置（ip.bluecdn.com，数据存入 comment_meta）
 * - 评论提交时自动保存到 comment_meta
 * - 前端渲染辅助函数
 * - 评论等级系统（12 级）
 * - Client Hints 真实平台版本获取
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================================================================
//  Part 1 — User-Agent 解析
// ================================================================

/**
 * 从 UA 字符串解析浏览器名称和版本
 */
function lared_parse_browser(string $ua): array
{
    $browsers = [
        'Edge'    => '/Edg(?:e|A|iOS)?\/(\d+[\.\d]*)/',
        'Opera'   => '/(?:OPR|Opera)\/(\d+[\.\d]*)/',
        'Firefox' => '/Firefox\/(\d+[\.\d]*)/',
        'Chrome'  => '/Chrome\/(\d+[\.\d]*)/',
        'Safari'  => '/Version\/(\d+[\.\d]*).*Safari/',
    ];

    foreach ($browsers as $name => $pattern) {
        if (preg_match($pattern, $ua, $m)) {
            return [
                'name'    => $name,
                'version' => $m[1],
                'icon'    => lared_get_ua_icon_slug($name, 'browser'),
            ];
        }
    }

    return ['name' => '', 'version' => '', 'icon' => 'browser'];
}

/**
 * 从 UA 字符串解析操作系统名称和版本
 *
 * 注意：现代浏览器 UA 中 macOS 版本已冻结为 10_15_7。
 * 通过 User-Agent Client Hints（Sec-CH-UA-Platform-Version）可获取真实版本，
 * 但仅 Chromium 系浏览器（Chrome/Edge/Opera）支持，Safari/Firefox 不支持。
 */
function lared_parse_os(string $ua): array
{
    // Windows 版本映射（NT 10.0 需通过 Client Hints 区分 10/11）
    $win_map = [
        '6.3' => '8.1',
        '6.2' => '8',
        '6.1' => '7',
        '6.0' => 'Vista',
        '5.1' => 'XP',
    ];

    if (preg_match('/Windows NT (\d+\.\d+)/', $ua, $m)) {
        $nt_ver = $m[1];
        if ('10.0' === $nt_ver) {
            // 通过 Client Hints 区分 Windows 10 / 11
            // Windows 11 对应 platformVersion major >= 13
            $pv = lared_get_real_platform_version();
            $ver = ('' !== $pv && (int) $pv >= 13) ? '11' : '10';
        } else {
            $ver = $win_map[$nt_ver] ?? $nt_ver;
        }
        return ['name' => 'Windows', 'version' => $ver, 'icon' => 'fa-brands fa-windows'];
    }

    if (preg_match('/Mac OS X/', $ua)) {
        // 检测 iPhone / iPad
        if (preg_match('/iPhone|iPad|iPod/', $ua)) {
            $ver = '';
            if (preg_match('/OS (\d+[_.\d]*)/', $ua, $m)) {
                $ver = str_replace('_', '.', $m[1]);
            }
            return ['name' => 'iOS', 'version' => $ver, 'icon' => 'fa-brands fa-apple'];
        }
        // macOS：UA 版本冻结，尝试从 Client Hints 获取真实版本
        $real_ver = lared_get_real_platform_version();
        return ['name' => 'macOS', 'version' => $real_ver, 'icon' => 'fa-brands fa-apple'];
    }

    if (preg_match('/Android (\d+[\.\d]*)/', $ua, $m)) {
        return ['name' => 'Android', 'version' => $m[1], 'icon' => 'fa-brands fa-android'];
    }

    // Linux 发行版（具体发行版放在泛 Linux 之前）
    if (preg_match('/Ubuntu/', $ua)) {
        return ['name' => 'Ubuntu', 'version' => '', 'icon' => 'fa-brands fa-ubuntu'];
    }
    if (preg_match('/Debian/', $ua)) {
        return ['name' => 'Debian', 'version' => '', 'icon' => 'fa-brands fa-linux'];
    }
    if (preg_match('/CentOS/', $ua)) {
        return ['name' => 'CentOS', 'version' => '', 'icon' => 'fa-brands fa-centos'];
    }
    if (preg_match('/Arch/', $ua)) {
        return ['name' => 'Arch Linux', 'version' => '', 'icon' => 'fa-brands fa-linux'];
    }
    if (preg_match('/Fedora/', $ua)) {
        return ['name' => 'Fedora', 'version' => '', 'icon' => 'fa-brands fa-fedora'];
    }

    if (preg_match('/Linux/', $ua)) {
        return ['name' => 'Linux', 'version' => '', 'icon' => 'fa-brands fa-linux'];
    }

    return ['name' => '', 'version' => '', 'icon' => 'fa-solid fa-desktop'];
}

/**
 * 获取真实的平台版本号（通过 Client Hints 或 JS 隐藏字段）
 *
 * 优先级：
 * 1. 评论表单提交的隐藏字段 _platform_version（JS 端通过 navigator.userAgentData 获取）
 * 2. HTTP 请求头 Sec-CH-UA-Platform-Version（需服务端发送 Accept-CH 头）
 * 3. 均不可用时返回空字符串
 *
 * 仅 Chromium 系浏览器支持，Safari/Firefox 返回空。
 */
function lared_get_real_platform_version(): string
{
    // 1. 优先从评论表单的 JS 隐藏字段读取
    if (!empty($_POST['_platform_version'])) {
        $ver = sanitize_text_field(wp_unslash($_POST['_platform_version']));
        if (preg_match('/^(\d+)/', $ver, $m)) {
            return $m[1]; // 只取主版本号，如 "26.0.0" → "26"
        }
    }

    // 2. 从 Client Hints 请求头读取
    $ch_ver = $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] ?? '';
    if ('' !== $ch_ver) {
        $ch_ver = trim($ch_ver, '" ');
        if (preg_match('/^(\d+)/', $ch_ver, $m)) {
            return $m[1];
        }
    }

    return '';
}

/**
 * 浏览器名称映射到 FontAwesome 图标类
 */
function lared_get_ua_icon_slug(string $name, string $type): string
{
    $map = [
        // Browser
        'Chrome'     => 'fa-brands fa-chrome',
        'Firefox'    => 'fa-brands fa-firefox-browser',
        'Safari'     => 'fa-brands fa-safari',
        'Edge'       => 'fa-brands fa-edge',
        'Opera'      => 'fa-brands fa-opera',
        // OS
        'Windows'    => 'fa-brands fa-windows',
        'macOS'      => 'fa-brands fa-apple',
        'iOS'        => 'fa-brands fa-apple',
        'Android'    => 'fa-brands fa-android',
        'Ubuntu'     => 'fa-brands fa-ubuntu',
        'Linux'      => 'fa-brands fa-linux',
        'Debian'     => 'fa-brands fa-linux',
        'Arch Linux' => 'fa-brands fa-linux',
        'CentOS'     => 'fa-brands fa-centos',
        'Fedora'     => 'fa-brands fa-fedora',
    ];

    return $map[$name] ?? ($type === 'browser' ? 'fa-solid fa-globe' : 'fa-solid fa-desktop');
}

/**
 * 获取 UA 图标的 FontAwesome <i> 标签
 */
function lared_ua_icon_html(string $fa_class): string
{
    return '<i class="' . esc_attr($fa_class) . ' fa-fw comment-ua-icon" aria-hidden="true"></i>';
}

/**
 * 将旧版 SVG 图标 slug 转换为 FontAwesome 类名
 * 兼容已存入 comment_meta 的老数据
 */
function lared_migrate_icon_slug(string $icon, string $type): string
{
    // 已经是 FontAwesome 格式（包含 'fa-'），无需转换
    if (str_contains($icon, 'fa-')) {
        return $icon;
    }

    $legacy_map = [
        // OS
        'windows' => 'fa-brands fa-windows',
        'macos'   => 'fa-brands fa-apple',
        'ios'     => 'fa-brands fa-apple',
        'android' => 'fa-brands fa-android',
        'ubuntu'  => 'fa-brands fa-ubuntu',
        'linux'   => 'fa-brands fa-linux',
        'system'  => 'fa-solid fa-desktop',
        // Browser
        'chrome'  => 'fa-brands fa-chrome',
        'firefox' => 'fa-brands fa-firefox-browser',
        'safari'  => 'fa-brands fa-safari',
        'edge'    => 'fa-brands fa-edge',
        'opera'   => 'fa-brands fa-opera',
        'browser' => 'fa-solid fa-globe',
    ];

    return $legacy_map[$icon] ?? ($type === 'browser' ? 'fa-solid fa-globe' : 'fa-solid fa-desktop');
}

// ================================================================
//  Part 2 — IP 地理位置（ip.bluecdn.com）
// ================================================================

/**
 * 通过 ip.bluecdn.com 获取 IP 地理信息（中文）
 * 使用静态内存缓存避免同一请求内对相同 IP 重复调用 API
 * 数据通过 comment_meta 持久化，同一 IP 只在首次查询时调 API
 * 返回 ['country' => 'cn', 'country_name' => '中国', 'city' => '南京', 'region' => '江苏']
 */
function lared_get_ip_geo(string $ip): array
{
    static $mem_cache = [];
    $empty = ['country' => '', 'country_name' => '', 'city' => '', 'region' => ''];

    if ('' === $ip || '127.0.0.1' === $ip || '::1' === $ip) {
        return $empty;
    }

    // 同一请求内的内存缓存（避免同页面多条相同 IP 评论重复调 API）
    if (isset($mem_cache[$ip])) {
        return $mem_cache[$ip];
    }

    // 调用 API
    $api_url = 'https://ip.bluecdn.com/geoip/' . rawurlencode($ip);
    $response = wp_remote_get($api_url, [
        'timeout' => 5,
        'headers' => [
            'Accept'          => 'application/json',
            'X-Forwarded-For' => $ip,
            'X-Real-IP'       => $ip,
        ],
    ]);

    $result = $empty;

    if (!is_wp_error($response) && 200 === (int) wp_remote_retrieve_response_code($response)) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        $country_name = trim((string) ($data['country'] ?? $data['country_name'] ?? $data['countryName'] ?? ''));
        $country_code = strtolower(trim((string) ($data['countryCode'] ?? $data['country_code'] ?? '')));
        $city = trim((string) ($data['city'] ?? $data['cityName'] ?? ''));
        $region = trim((string) ($data['regionName'] ?? $data['region'] ?? $data['province'] ?? ''));

        if ('' !== $country_name || '' !== $country_code) {
            $result = [
                'country'      => $country_code,
                'country_name' => $country_name,
                'city'         => $city,
                'region'       => $region,
            ];
        }
    }

    $mem_cache[$ip] = $result;
    return $result;
}

// ================================================================
//  Part 3 — 评论提交时自动保存元数据
// ================================================================

/**
 * 评论插入后自动保存 UA + 地理位置到 comment_meta（单条 _comment_info JSON）
 */
function lared_save_comment_meta(int $comment_id, $comment): void
{
    if (!($comment instanceof WP_Comment)) {
        $comment = get_comment($comment_id);
    }
    if (!$comment) {
        return;
    }

    $ua = (string) $comment->comment_agent;
    $ip = (string) $comment->comment_author_IP;

    $info = [];

    $browser = lared_parse_browser($ua);
    if ('' !== $browser['name']) {
        $info['browser']     = $browser['name'];
        $info['browser_ver'] = $browser['version'];
    }

    $os = lared_parse_os($ua);
    if ('' !== $os['name']) {
        $info['os']     = $os['name'];
        $info['os_ver'] = $os['version'];
    }

    $geo = lared_get_ip_geo($ip);
    if ('' !== $geo['country']) {
        $info['geo'] = [
            'country'      => $geo['country'],
            'country_name' => $geo['country_name'],
            'city'         => $geo['city'],
            'region'       => $geo['region'],
        ];
    }

    if (!empty($info)) {
        update_comment_meta($comment_id, '_comment_info', $info);
    }
}
add_action('comment_post', 'lared_save_comment_meta', 10, 2);

// ================================================================
//  Part 3.5 — 反垃圾评论（替代 Akismet）
// ================================================================

/**
 * 轻量反垃圾评论检查
 *
 * 白名单：已登录用户 / 友链域名评论者 → 跳过检查
 * 规则 1：纯英文（无中日韩字符）→ spam
 * 规则 2：评论含 2+ 个链接 → spam
 * 规则 3：内容过短（< 4 字符）→ spam
 * 规则 4：垃圾关键词黑名单 → spam
 */
function lared_antispam_check(array $commentdata): array
{
    // 白名单：已登录用户
    if (!empty($commentdata['user_ID']) && (int) $commentdata['user_ID'] > 0) {
        return $commentdata;
    }

    // 白名单：友链域名评论者
    $author_url = $commentdata['comment_author_url'] ?? '';
    if ('' !== $author_url && lared_is_friend_link($author_url)) {
        return $commentdata;
    }

    $content = trim($commentdata['comment_content'] ?? '');

    // 规则 3：内容过短
    if (mb_strlen($content) < 4) {
        wp_die(
            '评论内容过短，请至少输入 4 个字符。',
            '评论被拦截',
            ['response' => 403, 'back_link' => true]
        );
    }

    // 规则 1：无中日韩字符 → spam（纯英文/拉丁文）
    if (!preg_match('/[\x{4e00}-\x{9fff}\x{3040}-\x{309f}\x{30a0}-\x{30ff}\x{ac00}-\x{d7af}]/u', $content)) {
        wp_die(
            '本站仅接受包含中文的评论，纯英文评论已被拦截。',
            '评论被拦截',
            ['response' => 403, 'back_link' => true]
        );
    }

    // 规则 2：含 2+ 个链接（http/https URL）
    $link_count = preg_match_all('#https?://#i', $content);
    if ($link_count >= 2) {
        wp_die(
            '评论中包含过多链接，疑似垃圾评论。',
            '评论被拦截',
            ['response' => 403, 'back_link' => true]
        );
    }

    // 规则 4：垃圾关键词黑名单
    $blacklist = [
        'poker', 'casino', 'viagra', 'cialis', 'slots', 'gambling',
        'porn', 'sex', 'xxx', 'nude', 'dating', 'hookup',
        'buy cheap', 'free money', 'click here', 'make money',
        'seo service', 'link building', 'backlink',
        'crypto', 'bitcoin', 'nft', 'airdrop',
        'payday loan', 'insurance quote',
    ];
    $content_lower = strtolower($content . ' ' . ($commentdata['comment_author'] ?? '') . ' ' . $author_url);
    foreach ($blacklist as $keyword) {
        if (str_contains($content_lower, $keyword)) {
            wp_die(
                '评论包含垃圾关键词，已被拦截。',
                '评论被拦截',
                ['response' => 403, 'back_link' => true]
            );
        }
    }

    return $commentdata;
}
add_filter('preprocess_comment', 'lared_antispam_check', 1);

// ================================================================
//  Part 4 — 前端渲染函数
// ================================================================

/**
 * 渲染评论的 UA + 地理位置信息条
 * 优先读 _comment_info（新 JSON 格式），兼容旧 10 字段格式
 */
function lared_render_comment_ua_geo(WP_Comment $comment): string
{
    $comment_id = (int) $comment->comment_ID;

    // 优先读新格式 _comment_info
    $info = get_comment_meta($comment_id, '_comment_info', true);

    if (is_array($info) && !empty($info)) {
        // 新格式
        $browser_name    = (string) ($info['browser'] ?? '');
        $browser_version = (string) ($info['browser_ver'] ?? '');
        $os_name         = (string) ($info['os'] ?? '');
        $os_version      = (string) ($info['os_ver'] ?? '');
        $geo             = $info['geo'] ?? [];
        $geo_country     = (string) ($geo['country'] ?? '');
        $geo_country_name = (string) ($geo['country_name'] ?? '');
        $geo_city        = (string) ($geo['city'] ?? '');
        $geo_region      = (string) ($geo['region'] ?? '');
    } else {
        // 兼容旧格式 10 字段
        $browser_name    = (string) get_comment_meta($comment_id, '_browser_name', true);
        $browser_version = (string) get_comment_meta($comment_id, '_browser_version', true);
        $os_name         = (string) get_comment_meta($comment_id, '_os_name', true);
        $os_version      = (string) get_comment_meta($comment_id, '_os_version', true);
        $geo_country     = (string) get_comment_meta($comment_id, '_geo_country', true);
        $geo_country_name = (string) get_comment_meta($comment_id, '_geo_country_name', true);
        $geo_city        = (string) get_comment_meta($comment_id, '_geo_city', true);
        $geo_region      = (string) get_comment_meta($comment_id, '_geo_region', true);
    }

    // 如果没有任何 meta，从 UA 实时解析并保存为新格式
    if ('' === $browser_name && '' === $os_name) {
        $ua = (string) $comment->comment_agent;
        if ('' !== $ua) {
            $browser = lared_parse_browser($ua);
            $browser_name    = $browser['name'];
            $browser_version = $browser['version'];
            $os = lared_parse_os($ua);
            $os_name    = $os['name'];
            $os_version = $os['version'];

            $new_info = [];
            if ('' !== $browser_name) {
                $new_info['browser']     = $browser_name;
                $new_info['browser_ver'] = $browser_version;
            }
            if ('' !== $os_name) {
                $new_info['os']     = $os_name;
                $new_info['os_ver'] = $os_version;
            }
            if (!empty($new_info)) {
                update_comment_meta($comment_id, '_comment_info', $new_info);
            }
        }
    }

    // 地理位置：如果为空，调用 API 获取并保存
    if ('' === $geo_country) {
        $ip = (string) $comment->comment_author_IP;
        if ('' !== $ip && '127.0.0.1' !== $ip && '::1' !== $ip) {
            $geo = lared_get_ip_geo($ip);
            $geo_country      = $geo['country'];
            $geo_country_name = $geo['country_name'];
            $geo_city         = $geo['city'];
            $geo_region       = $geo['region'];

            if ('' !== $geo_country) {
                // 读取或初始化 _comment_info，追加 geo
                $existing = get_comment_meta($comment_id, '_comment_info', true);
                if (!is_array($existing)) {
                    $existing = [];
                }
                $existing['geo'] = [
                    'country'      => $geo_country,
                    'country_name' => $geo_country_name,
                    'city'         => $geo_city,
                    'region'       => $geo_region,
                ];
                update_comment_meta($comment_id, '_comment_info', $existing);
            }
        }
    }

    // 无任何数据就不输出
    if ('' === $browser_name && '' === $os_name && '' === $geo_country) {
        return '';
    }

    $parts = [];

    // 地理位置 + 国旗
    // 中国 IP：国旗 + 省份 - 城市；国外 IP：国旗 + 国家名
    if ('' !== $geo_country) {
        if ('cn' === $geo_country) {
            // 中国：只显示城市
            if ('' !== $geo_city) {
                $display = $geo_city;
            } elseif ('' !== $geo_region) {
                $display = $geo_region;
            } else {
                $display = $geo_country_name;
            }
        } else {
            // 国外：只显示国家名
            $display = $geo_country_name;
        }

        // 完整地址 tooltip：国家 省份 城市（有什么显示什么）
        $full_parts = [];
        if ('' !== $geo_country_name) $full_parts[] = $geo_country_name;
        if ('' !== $geo_region && $geo_region !== $geo_country_name) $full_parts[] = $geo_region;
        if ('' !== $geo_city && $geo_city !== $geo_region) $full_parts[] = $geo_city;
        $full_address = implode(' ', $full_parts);

        $parts[] = '<span class="comment-meta-geo">' 
            . '<span class="fi fi-' . esc_attr($geo_country) . '"></span>'
            . '<span class="comment-meta-geo-text">' . esc_html($display) . '</span>'
            . ('' !== $full_address ? '<span class="comment-tooltip">' . esc_html($full_address) . '</span>' : '')
            . '</span>';
    }

    // 操作系统（icon 从 name 推断）
    if ('' !== $os_name) {
        $os_fa = lared_get_ua_icon_slug($os_name, 'os');
        $os_display = $os_name . ('' !== $os_version ? ' ' . $os_version : '');
        $parts[] = '<span class="comment-meta-os">'
            . lared_ua_icon_html($os_fa)
            . '<span class="comment-meta-os-text">' . esc_html($os_display) . '</span>'
            . '</span>';
    }

    // 浏览器（icon 从 name 推断）
    if ('' !== $browser_name) {
        $browser_fa = lared_get_ua_icon_slug($browser_name, 'browser');
        $browser_display = $browser_name . ('' !== $browser_version ? ' ' . $browser_version : '');
        $parts[] = '<span class="comment-meta-browser">'
            . lared_ua_icon_html($browser_fa)
            . '<span class="comment-meta-browser-text">' . esc_html($browser_display) . '</span>'
            . '</span>';
    }

    if (empty($parts)) {
        return '';
    }

    return '<span class="comment-ua-geo">' . implode('', $parts) . '</span>';
}

// ================================================================
//  Part 4.5 — 友链互动检测
// ================================================================

/**
 * 获取所有友情链接的域名列表（带静态缓存）
 */
function lared_get_friend_link_domains(): array
{
    static $domains = null;
    if ($domains !== null) {
        return $domains;
    }

    $domains = [];
    $bookmarks = get_bookmarks(['hide_invisible' => 1]);
    if (is_array($bookmarks)) {
        foreach ($bookmarks as $bookmark) {
            $url = (string) $bookmark->link_url;
            if ('' !== $url) {
                $host = wp_parse_url($url, PHP_URL_HOST);
                if ($host) {
                    // 去掉 www. 前缀统一比较
                    $domains[] = preg_replace('/^www\./i', '', strtolower($host));
                }
            }
        }
    }

    return $domains;
}

/**
 * 检查评论者网址是否在友情链接中
 */
function lared_is_friend_link(string $comment_url): bool
{
    if ('' === $comment_url) {
        return false;
    }

    $host = wp_parse_url($comment_url, PHP_URL_HOST);
    if (!$host) {
        return false;
    }

    $host = preg_replace('/^www\./i', '', strtolower($host));
    $friend_domains = lared_get_friend_link_domains();

    return in_array($host, $friend_domains, true);
}

// ================================================================
//  Part 5 — Client Hints：让浏览器发送真实平台版本
// ================================================================

/**
 * 发送 Accept-CH 响应头，让 Chromium 浏览器在后续请求中附带真实平台版本
 * Safari/Firefox 会忽略此头，不影响兼容性
 */
function lared_send_client_hints_header(): void
{
    if (!headers_sent()) {
        header('Accept-CH: Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version');
    }
}
add_action('send_headers', 'lared_send_client_hints_header');

/**
 * 在评论表单末尾注入 JS：通过 navigator.userAgentData 获取真实平台版本
 * 将版本号写入隐藏字段 _platform_version，随评论表单一起提交
 * 仅 Chromium 系浏览器（Chrome/Edge/Opera）支持此 API
 */
function lared_comment_form_platform_js(): void
{
    ?>
    <script>
    (function() {
        if (!navigator.userAgentData) return;
        navigator.userAgentData.getHighEntropyValues(['platform', 'platformVersion']).then(function(ua) {
            if (!ua.platformVersion) return;
            var forms = document.querySelectorAll('.comment-form, #commentform');
            forms.forEach(function(form) {
                if (form.querySelector('input[name="_platform_version"]')) return;
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_platform_version';
                input.value = ua.platformVersion;
                form.appendChild(input);
            });
        });
    })();
    </script>
    <?php
}
add_action('comment_form_after', 'lared_comment_form_platform_js');

// ================================================================
//  Part 6 — 评论等级系统（12 级）
// ================================================================

/**
 * 等级划分（修仙体系 13 级）：
 * Lv1-Lv3:   入门 (1-10)
 * Lv4-Lv6:   进阶 (11-60)
 * Lv7-Lv9:   高阶 (61-200)
 * Lv10-Lv13:  巅峰 (201+)
 */

/**
 * 获取评论等级阈值配置
 */
function lared_get_comment_level_thresholds(): array
{
    return [
        1  => ['name' => '凝气', 'min' => 1,   'max' => 2,   'color' => '#9ca3af', 'bg' => '#f3f4f6', 'icon' => 'fa-solid fa-wind'],
        2  => ['name' => '筑基', 'min' => 3,   'max' => 5,   'color' => '#22c55e', 'bg' => '#dcfce7', 'icon' => 'fa-solid fa-seedling'],
        3  => ['name' => '结丹', 'min' => 6,   'max' => 10,  'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-solid fa-circle-dot'],
        4  => ['name' => '元婴', 'min' => 11,  'max' => 20,  'color' => '#14b8a6', 'bg' => '#ccfbf1', 'icon' => 'fa-solid fa-baby'],
        5  => ['name' => '化神', 'min' => 21,  'max' => 35,  'color' => '#06b6d4', 'bg' => '#cffafe', 'icon' => 'fa-solid fa-hat-wizard'],
        6  => ['name' => '婴变', 'min' => 36,  'max' => 60,  'color' => '#0ea5e9', 'bg' => '#e0f2fe', 'icon' => 'fa-solid fa-burst'],
        7  => ['name' => '问鼎', 'min' => 61,  'max' => 100, 'color' => '#3b82f6', 'bg' => '#dbeafe', 'icon' => 'fa-solid fa-landmark'],
        8  => ['name' => '阴虚', 'min' => 101, 'max' => 150, 'color' => '#6366f1', 'bg' => '#e0e7ff', 'icon' => 'fa-solid fa-moon'],
        9  => ['name' => '阳实', 'min' => 151, 'max' => 200, 'color' => '#8b5cf6', 'bg' => '#ede9fe', 'icon' => 'fa-solid fa-sun'],
        10 => ['name' => '窥涅', 'min' => 201, 'max' => 350, 'color' => '#a855f7', 'bg' => '#f3e8ff', 'icon' => 'fa-solid fa-eye'],
        11 => ['name' => '净涅', 'min' => 351, 'max' => 500, 'color' => '#d946ef', 'bg' => '#fae8ff', 'icon' => 'fa-solid fa-fire'],
        12 => ['name' => '碎涅', 'min' => 501, 'max' => 800, 'color' => '#ec4899', 'bg' => '#fce7f3', 'icon' => 'fa-solid fa-meteor'],
        13 => ['name' => '飞升', 'min' => 801, 'max' => null, 'color' => '#f43f5e', 'bg' => '#ffe4e6', 'icon' => 'fa-solid fa-dragon'],
    ];
}

/**
 * 根据评论数计算等级
 */
function lared_calculate_level(int $count): int
{
    if ($count < 1) {
        return 0;
    }

    $thresholds = lared_get_comment_level_thresholds();

    foreach ($thresholds as $level => $config) {
        if ($count >= $config['min'] && ($config['max'] === null || $count <= $config['max'])) {
            return $level;
        }
    }

    return 13;
}

/**
 * 获取等级配置信息
 */
function lared_get_level_config(int $level): ?array
{
    if ($level < 1 || $level > 13) {
        return null;
    }

    $thresholds = lared_get_comment_level_thresholds();
    return $thresholds[$level] ?? null;
}

/**
 * 获取用户的评论统计数据
 */
function lared_get_user_comment_stats(string $email): array
{
    if (empty($email)) {
        return [
            'count' => 0,
            'level' => ['id' => 0, 'name' => '游客', 'config' => null],
            'progress' => 0,
            'next_level' => null,
        ];
    }

    // 先从缓存获取等级信息
    $level_info = lared_get_cached_level($email);

    if ($level_info === null) {
        // 缓存不存在或过期，重新计算
        $level_info = lared_update_and_cache_level($email);
    }

    $current_level = $level_info['level'];
    $count = $level_info['count'];

    $config = lared_get_level_config($current_level);

    // 计算进度
    $progress = 0;
    $next_level = null;

    if ($current_level < 13) {
        $next_level_config = lared_get_level_config($current_level + 1);
        if ($next_level_config) {
            $next_level = [
                'id' => $current_level + 1,
                'name' => $next_level_config['name'],
                'required' => $next_level_config['min'],
            ];

            $current_min = $config['min'];
            $next_min = $next_level_config['min'];
            $range = $next_min - $current_min;

            if ($range > 0) {
                $progress = min(100, max(0, round((($count - $current_min) / $range) * 100)));
            }
        }
    } else {
        $progress = 100; // 满级
    }

    return [
        'count' => $count,
        'level' => [
            'id' => $current_level,
            'name' => $config['name'] ?? '游客',
            'config' => $config,
            'count' => $count,
        ],
        'progress' => $progress,
        'next_level' => $next_level,
    ];
}

// ================================================================
//  Part 7 — 等级缓存
// ================================================================

/**
 * 从缓存获取等级信息
 */
function lared_get_cached_level(string $email): ?array
{
    $cache_key = 'lared_commenter_levels_v2';
    $levels = get_option($cache_key, []);
    $email_key = sanitize_email($email);

    if (!isset($levels[$email_key])) {
        return null;
    }

    $level_data = $levels[$email_key];

    // 检查缓存是否过期（24小时）
    if (!isset($level_data['updated_at']) || $level_data['updated_at'] < (time() - DAY_IN_SECONDS)) {
        return null;
    }

    return $level_data;
}

/**
 * 更新并缓存用户等级
 */
function lared_update_and_cache_level(string $email): array
{
    global $wpdb;

    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_author_email = %s AND comment_approved = '1'",
        $email
    ));

    $level = lared_calculate_level($count);

    $result = [
        'level' => $level,
        'count' => $count,
        'updated_at' => time(),
    ];

    // 更新缓存
    $cache_key = 'lared_commenter_levels_v2';
    $levels = get_option($cache_key, []);
    $levels[sanitize_email($email)] = $result;
    update_option($cache_key, $levels, false);

    return $result;
}

/**
 * 清除用户等级缓存
 */
function lared_clear_level_cache(string $email): void
{
    $cache_key = 'lared_commenter_levels_v2';
    $levels = get_option($cache_key, []);
    $email_key = sanitize_email($email);

    if (isset($levels[$email_key])) {
        unset($levels[$email_key]);
        update_option($cache_key, $levels, false);
    }
}

/**
 * 评论提交后更新等级缓存
 */
function lared_update_level_on_comment($comment_id, $comment_approved): void
{
    $comment = get_comment($comment_id);
    if (!$comment || empty($comment->comment_author_email)) {
        return;
    }

    // 清除缓存，下次访问时重新计算
    lared_clear_level_cache($comment->comment_author_email);
}
add_action('comment_post', 'lared_update_level_on_comment', 10, 2);
add_action('wp_set_comment_status', function ($comment_id, $status) {
    $comment = get_comment($comment_id);
    if ($comment && !empty($comment->comment_author_email)) {
        lared_clear_level_cache($comment->comment_author_email);
    }
}, 10, 2);

// ================================================================
//  Part 8 — 等级徽章渲染
// ================================================================

/**
 * 获取等级徽章 HTML（简洁版）
 */
function lared_get_level_badge_simple(array $level, string $size = 'small'): string
{
    if (empty($level['id']) || $level['id'] < 1) {
        return '';
    }

    $config = $level['config'] ?? null;
    if (!$config) {
        $config = lared_get_level_config($level['id']);
    }

    if (!$config) {
        return '';
    }

    $color = esc_attr($config['color']);
    $lv    = (int) $level['id'];
    $count = (int) ($level['count'] ?? 0);
    $name  = esc_html($config['name']);

    return '<span class="lared-level-badge" style="color:' . $color . ';border-color:' . $color . '">'
        . $name
        . '<span class="comment-tooltip">' . esc_html(sprintf('Lv%d · 评论 %d 条', $lv, $count)) . '</span>'
        . '</span>';
}

/**
 * 获取完整的等级徽章（带进度条）
 */
function lared_get_level_badge_full(array $stats): string
{
    $level = $stats['level'] ?? ['id' => 0];

    if (empty($level['id'])) {
        return '<div class="lared-level-badge-full guest">' .
               '<span class="level-text">登录后发表评论获取等级</span>' .
               '</div>';
    }

    $config = $level['config'];
    $progress = $stats['progress'] ?? 0;
    $count = $stats['count'] ?? 0;
    $next = $stats['next_level'];

    $html = '<div class="lared-level-badge-full">';

    // 头部：图标 + 等级名
    $html .= '<div class="level-header" style="color: ' . esc_attr($config['color']) . '">';
    if (!empty($config['icon'])) {
        $html .= '<i class="' . esc_attr($config['icon']) . ' level-icon"></i>';
    }
    $html .= '<span class="level-name">' . esc_html($config['name']) . '</span>';
    $html .= '<span class="level-num">Lv' . $level['id'] . '</span>';
    $html .= '</div>';

    // 进度条
    $html .= '<div class="level-progress">';
    $html .= '<div class="progress-bar" style="width: ' . $progress . '%; background: ' . esc_attr($config['color']) . '"></div>';
    $html .= '</div>';

    // 底部：评论数 + 下一级信息
    $html .= '<div class="level-footer">';
    $html .= '<span class="comment-count">已评论 <strong>' . $count . '</strong> 条</span>';
    if ($next) {
        $html .= '<span class="next-level">还差 ' . ($next['required'] - $count) . ' 条升级</span>';
    } else {
        $html .= '<span class="max-level">已达最高等级</span>';
    }
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

// ================================================================
//  Part 9 — 等级 AJAX
// ================================================================

/**
 * AJAX: 获取评论者等级信息
 */
function lared_ajax_get_commenter_level(): void
{
    check_ajax_referer('lared_level_nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');

    if (empty($email)) {
        wp_send_json_error(['message' => 'Invalid email']);
        return;
    }

    $stats = lared_get_user_comment_stats($email);

    wp_send_json_success([
        'stats' => $stats,
        'badge_simple' => lared_get_level_badge_simple($stats['level']),
        'badge_full' => lared_get_level_badge_full($stats),
    ]);
}
add_action('wp_ajax_lared_get_commenter_level', 'lared_ajax_get_commenter_level');
add_action('wp_ajax_nopriv_lared_get_commenter_level', 'lared_ajax_get_commenter_level');

/**
 * 批量刷新所有评论者等级（用于初始化或修复数据）
 */
function lared_refresh_all_commenter_levels(): array
{
    global $wpdb;

    $updated = 0;
    $errors = 0;
    $batch_size = 100;
    $offset = 0;

    // 清除旧缓存
    delete_option('lared_commenter_levels_v2');

    do {
        $emails = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT comment_author_email FROM {$wpdb->comments} 
             WHERE comment_author_email != '' AND comment_approved = '1' 
             LIMIT %d OFFSET %d",
            $batch_size,
            $offset
        ));

        if (empty($emails)) {
            break;
        }

        foreach ($emails as $email) {
            try {
                lared_update_and_cache_level($email);
                $updated++;
            } catch (Exception $e) {
                $errors++;
                error_log('Lared Level Update Error: ' . $e->getMessage());
            }
        }

        $offset += $batch_size;

        // 避免数据库压力过大
        usleep(50000); // 50ms

    } while (count($emails) === $batch_size);

    return [
        'updated' => $updated,
        'errors' => $errors,
    ];
}

// ================================================================
//  Part 9 — 评论页面控制
// ================================================================

/**
 * 禁用 Page 类型页面的评论支持
 */
function lared_disable_page_comments(): void
{
    remove_post_type_support('page', 'comments');
    remove_post_type_support('page', 'trackbacks');
}
add_action('init', 'lared_disable_page_comments');

/**
 * 强制关闭 Page 类型的评论和 ping
 */
function lared_force_page_comments_closed(bool $open, int $post_id): bool
{
    if ('page' === get_post_type($post_id)) {
        return false;
    }

    return $open;
}
add_filter('comments_open', 'lared_force_page_comments_closed', 10, 2);
add_filter('pings_open', 'lared_force_page_comments_closed', 10, 2);

// ================================================================
//  Part 10 — 评论统计
// ================================================================

/**
 * 统计文章的唯一已审核评论者数量
 * 优先按 user_id 去重，其次按 email，最后按昵称
 */
function lared_count_unique_approved_commenters(int $post_id): int
{
    global $wpdb;

    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM (
            SELECT CASE
                WHEN user_id > 0 THEN CONCAT('user:', user_id)
                WHEN comment_author_email != '' THEN CONCAT('email:', LOWER(TRIM(comment_author_email)))
                ELSE CONCAT('name:', LOWER(TRIM(comment_author)))
            END AS commenter_key
            FROM {$wpdb->comments}
            WHERE comment_post_ID = %d
              AND comment_approved = '1'
              AND comment_type = 'comment'
            GROUP BY commenter_key
        ) AS unique_commenters",
        $post_id
    ));

    return $count;
}

// ================================================================
//  Part 11 — AJAX 评论提交 / 编辑
// ================================================================

/**
 * AJAX 提交评论
 */
function lared_ajax_submit_comment(): void
{
    $nonce = sanitize_text_field(wp_unslash($_POST['nonce'] ?? ''));
    if (!wp_verify_nonce($nonce, 'lared_comment_submit')) {
        wp_send_json_error(['message' => __('安全验证失败，请刷新页面后重试。', 'lared')]);
        return;
    }

    $comment_post_id  = (int) ($_POST['comment_post_ID'] ?? 0);
    $comment_content  = sanitize_textarea_field(wp_unslash($_POST['comment'] ?? ''));
    $comment_parent   = (int) ($_POST['comment_parent'] ?? 0);

    $user    = wp_get_current_user();
    $user_id = $user->ID;

    // 已登录用户从账号获取信息，游客从表单获取
    if ($user_id > 0) {
        $comment_author       = $user->display_name;
        $comment_author_email = $user->user_email;
        $comment_author_url   = $user->user_url;
    } else {
        $comment_author       = sanitize_text_field(wp_unslash($_POST['author'] ?? ''));
        $comment_author_email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $comment_author_url   = esc_url_raw(wp_unslash($_POST['url'] ?? ''));
    }

    if (!$comment_post_id || '' === $comment_content) {
        wp_send_json_error(['message' => __('请填写评论内容。', 'lared')]);
        return;
    }

    if (0 === $user_id && ('' === $comment_author || '' === $comment_author_email)) {
        wp_send_json_error(['message' => __('请填写昵称和邮箱。', 'lared')]);
        return;
    }

    if (0 === $user_id && !is_email($comment_author_email)) {
        wp_send_json_error(['message' => __('请输入有效的邮箱地址。', 'lared')]);
        return;
    }

    $comment_data = [
        'comment_post_ID'      => $comment_post_id,
        'comment_author'       => $comment_author,
        'comment_author_email' => $comment_author_email,
        'comment_author_url'   => $comment_author_url,
        'comment_content'      => $comment_content,
        'comment_parent'       => $comment_parent,
        'user_id'              => $user_id,
    ];

    // 防重复提交：同一作者 + 同一内容 60 秒内不允许重复
    global $wpdb;
    $recent_dup = $wpdb->get_var($wpdb->prepare(
        "SELECT comment_ID FROM {$wpdb->comments}
         WHERE comment_post_ID = %d AND comment_author_email = %s AND comment_content = %s
           AND comment_date_gmt > %s LIMIT 1",
        $comment_post_id,
        $comment_author_email,
        $comment_content,
        gmdate('Y-m-d H:i:s', time() - 60)
    ));
    if ($recent_dup) {
        wp_send_json_error(['message' => __('请勿重复提交评论。', 'lared')]);
        return;
    }

    $comment_id = wp_new_comment($comment_data, true);

    if (is_wp_error($comment_id)) {
        wp_send_json_error(['message' => $comment_id->get_error_message()]);
        return;
    }

    $comment     = get_comment($comment_id);
    $is_approved = (1 === (int) $comment->comment_approved);

    // 为匿名用户强制设置 cookie，确保下次访问能识别为回头访客
    if (0 === $user_id && $comment) {
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));
        $expire = time() + 30 * DAY_IN_SECONDS;
        $path   = defined('COOKIEPATH')    ? COOKIEPATH    : '/';
        $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
        setcookie('comment_author_'       . COOKIEHASH, $comment_author,       $expire, $path, $domain, $secure, true);
        setcookie('comment_author_email_' . COOKIEHASH, $comment_author_email, $expire, $path, $domain, $secure, true);
        if (!empty($comment_author_url)) {
            setcookie('comment_author_url_' . COOKIEHASH, $comment_author_url, $expire, $path, $domain, $secure, true);
        }
    }

    $comment_html = '';
    if ($is_approved) {
        // 构建楼层号映射，确保新评论有正确的楼层号
        global $lared_floor_map;
        $all_toplevel = get_comments([
            'post_id' => (int) $comment->comment_post_ID,
            'status'  => 'approve',
            'type'    => 'comment',
            'parent'  => 0,
            'orderby' => 'comment_date_gmt',
            'order'   => 'ASC',
        ]);
        $lared_floor_map = [];
        foreach ($all_toplevel as $i => $tc) {
            $lared_floor_map[(int) $tc->comment_ID] = $i + 1;
        }

        ob_start();
        wp_list_comments([
            'style'       => 'ol',
            'short_ping'  => true,
            'avatar_size' => 44,
            'callback'    => 'lared_custom_comment_callback',
        ], [$comment]);
        $comment_html = (string) ob_get_clean();
    }

    $post_id = (int) $comment->comment_post_ID;

    wp_send_json_success([
        'approved'       => $is_approved,
        'pending'        => !$is_approved,
        'message'        => $is_approved
            ? __('评论发布成功。', 'lared')
            : __('评论已提交，审核通过后显示。', 'lared'),
        'html'           => $comment_html,
        'parent'         => (int) $comment->comment_parent,
        'commentId'      => (int) $comment->comment_ID,
        'commentTotal'   => (int) get_comments_number($post_id),
        'toplevelCount'  => (int) get_comments([
            'post_id' => $post_id,
            'status'  => 'approve',
            'type'    => 'comment',
            'parent'  => 0,
            'count'   => true,
        ]),
        'commenterCount' => lared_count_unique_approved_commenters($post_id),
        'commenterName'  => $comment_author,
    ]);
}
add_action('wp_ajax_lared_submit_comment', 'lared_ajax_submit_comment');
add_action('wp_ajax_nopriv_lared_submit_comment', 'lared_ajax_submit_comment');

/**
 * AJAX 编辑评论（60 秒内允许前端编辑自己的评论）
 */
function lared_ajax_edit_comment(): void
{
    check_ajax_referer('lared_comment_edit', 'nonce');

    $comment_id  = (int) ($_POST['comment_id'] ?? 0);
    $new_content = sanitize_textarea_field($_POST['comment'] ?? '');

    if (!$comment_id || '' === $new_content) {
        wp_send_json_error(['message' => __('请填写评论内容。', 'lared')]);
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment) {
        wp_send_json_error(['message' => __('评论不存在。', 'lared')]);
        return;
    }

    // 验证身份：已登录用户比对 user_id，游客比对 IP + 邮箱
    $user     = wp_get_current_user();
    $is_owner = false;

    if ($user->ID > 0 && (int) $comment->user_id === $user->ID) {
        $is_owner = true;
    } elseif (0 === $user->ID) {
        $visitor_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
        if (
            $comment->comment_author_IP === $visitor_ip
            && strtolower(trim($comment->comment_author_email)) === strtolower(trim(sanitize_email($_POST['author_email'] ?? '')))
        ) {
            $is_owner = true;
        }
    }

    if (!$is_owner) {
        wp_send_json_error(['message' => __('无权编辑此评论。', 'lared')]);
        return;
    }

    // 检查是否在 60 秒内
    if ((time() - strtotime($comment->comment_date_gmt)) > 60) {
        wp_send_json_error(['message' => __('编辑时间已过期（60 秒）。', 'lared')]);
        return;
    }

    $result = wp_update_comment([
        'comment_ID'      => $comment_id,
        'comment_content' => $new_content,
    ]);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
        return;
    }

    $updated_comment = get_comment($comment_id);

    // 构建楼层号映射，确保编辑后评论有正确的楼层号
    global $lared_floor_map;
    $all_toplevel = get_comments([
        'post_id' => (int) $updated_comment->comment_post_ID,
        'status'  => 'approve',
        'type'    => 'comment',
        'parent'  => 0,
        'orderby' => 'comment_date_gmt',
        'order'   => 'ASC',
    ]);
    $lared_floor_map = [];
    foreach ($all_toplevel as $i => $tc) {
        $lared_floor_map[(int) $tc->comment_ID] = $i + 1;
    }

    ob_start();
    wp_list_comments([
        'style'       => 'ol',
        'short_ping'  => true,
        'avatar_size' => 44,
        'callback'    => 'lared_custom_comment_callback',
    ], [$updated_comment]);
    $comment_html = (string) ob_get_clean();

    wp_send_json_success([
        'message'   => __('评论已更新。', 'lared'),
        'html'      => $comment_html,
        'commentId' => $comment_id,
    ]);
}
add_action('wp_ajax_lared_edit_comment', 'lared_ajax_edit_comment');
add_action('wp_ajax_nopriv_lared_edit_comment', 'lared_ajax_edit_comment');

// ================================================================
//  Part 12 — 旧评论表情兼容渲染
// ================================================================

/**
 * 使用 MojiPack 的 bilibili 表情包兼容渲染旧评论中的 :slug: 文本。
 *
 * 旧主题原本将评论中的 :daxiao: 这类纯文本代码替换成主题内置图片。
 * 现在输入交给 MojiPack 处理，但为了避免插件未启用或未接管时旧评论消失，
 * 这里保留一个仅用于显示旧评论的兼容层。
 */
function lared_render_legacy_comment_emojis(string $text): string
{
    if (strpos($text, ':') === false) {
        return $text;
    }

    // MojiPack 已启用时，直接交给插件自己的 comment_text 过滤器处理。
    if (class_exists('MojiPack') || class_exists('MojiPack_Comment')) {
        return $text;
    }

    static $emoji_map = null;

    if (null === $emoji_map) {
        $emoji_map = [];

        $pack_file = WP_PLUGIN_DIR . '/mojipack/assets/packs/pack-bilibili.json';
        if (!file_exists($pack_file)) {
            return $text;
        }

        $json = file_get_contents($pack_file);
        $data = is_string($json) ? json_decode($json, true) : null;
        $emojis = is_array($data) && isset($data['emojis']) && is_array($data['emojis']) ? $data['emojis'] : [];
        $base_url = content_url('plugins/mojipack/assets/packs/bilibili');

        foreach ($emojis as $slug => $emoji) {
            if (!is_array($emoji) || empty($emoji['file'])) {
                continue;
            }

            $code = ':' . (string) $slug . ':';
            $emoji_map[$code] = [
                'name' => (string) ($emoji['name'] ?? $slug),
                'url'  => trailingslashit($base_url) . ltrim((string) $emoji['file'], '/'),
            ];
        }
    }

    if (empty($emoji_map)) {
        return $text;
    }

    foreach ($emoji_map as $code => $emoji) {
        if (!str_contains($text, $code)) {
            continue;
        }

        $img = '<img class="lared-legacy-emoji"'
            . ' src="' . esc_url($emoji['url']) . '"'
            . ' alt="' . esc_attr($emoji['name']) . '"'
            . ' title="' . esc_attr($emoji['name']) . '"'
            . ' data-code="' . esc_attr($code) . '"'
            . ' width="28" height="28"'
            . ' style="display:inline-block;width:1.6em;height:1.6em;vertical-align:text-bottom;margin:0 1px;">';

        $text = str_replace($code, $img, $text);
    }

    return $text;
}
add_filter('comment_text', 'lared_render_legacy_comment_emojis', 19);

// ================================================================
//  Part 13 — 评论模板回调
// ================================================================

/**
 * 自定义评论列表单条回调
 */
function lared_custom_comment_callback(WP_Comment $comment, array $args, int $depth): void
{
    $tag = ('div' === $args['style']) ? 'div' : 'li';
    $is_toplevel = (1 === $depth);
    global $lared_floor_map;
    $floor_number = $lared_floor_map[(int) $comment->comment_ID] ?? 0;
    ?>
    <<?php echo esc_html($tag); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent', $comment); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">

            <?php if (0 !== $args['avatar_size']) :
                echo get_avatar($comment, $args['avatar_size']);
            endif; ?>

            <div class="comment-main">
                <div class="comment-header">

                    <span class="comment-author-wrap">
                        <span class="comment-author-name vcard"><?php comment_author_link($comment); ?></span>

                        <?php if (user_can($comment->user_id, 'manage_options')) : ?>
                            <span class="lared-admin-badge">
                                <i class="fa-sharp fa-solid fa-crown"></i>
                                <span class="comment-tooltip"><?php esc_html_e('博主', 'lared'); ?></span>
                            </span>
                        <?php endif; ?>

                        <?php
                        // 友链互动徽章
                        $comment_author_url = (string) $comment->comment_author_url;
                        if (!user_can($comment->user_id, 'manage_options') && lared_is_friend_link($comment_author_url)) :
                        ?>
                            <span class="lared-friend-badge">
                                <i class="fa-sharp fa-thin fa-circle-star"></i>
                                <span class="comment-tooltip"><?php esc_html_e('友链互动', 'lared'); ?></span>
                            </span>
                        <?php endif; ?>

                        <?php
                        // 评论等级徽章
                        $comment_email = (string) $comment->comment_author_email;
                        if ('' !== $comment_email && !user_can($comment->user_id, 'manage_options')) {
                            $comment_stats = lared_get_user_comment_stats($comment_email);
                            echo lared_get_level_badge_simple($comment_stats['level']);
                        }
                        ?>
                    </span>

                    <span class="comment-metadata">
                        <?php
                        $comment_timestamp = get_comment_date('U', $comment);
                        $exact_time        = get_comment_date('Y/m/d H:i', $comment);
                        $relative_time     = sprintf(__('%s前', 'lared'), human_time_diff($comment_timestamp, current_time('timestamp')));
                        ?>
                        <time datetime="<?php comment_time('c'); ?>">
                            <i class="fa-regular fa-clock fa-fw comment-ua-icon" aria-hidden="true"></i><?php echo esc_html($relative_time); ?>
                            <span class="comment-tooltip"><?php echo esc_html($exact_time); ?></span>
                        </time>
                    </span>

                    <?php echo lared_render_comment_ua_geo($comment); ?>

                    <?php if ($is_toplevel) : ?>
                        <span class="comment-floor"><?php echo esc_html($floor_number . '楼'); ?></span>
                    <?php endif; ?>

                    <?php
                    comment_reply_link(
                        array_merge($args, [
                            'add_below' => 'div-comment',
                            'depth'     => $depth,
                            'max_depth' => $args['max_depth'],
                            'before'    => '<span class="reply">',
                            'after'     => '</span>',
                            'reply_text' => '<i class="fa-sharp fa-solid fa-reply" style="font-size:14px"></i>',
                        ])
                    );
                    ?>
                </div>

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div>

                <?php if ('0' === $comment->comment_approved) : ?>
                    <div class="comment-footer">
                        <em class="comment-awaiting-moderation">
                            <?php esc_html_e('Your comment is awaiting moderation.', 'lared'); ?>
                        </em>
                    </div>
                <?php endif; ?>
            </div>

        </article>
    <?php
}
