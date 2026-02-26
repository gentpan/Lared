<?php

if (!defined('ABSPATH')) {
    exit;
}

function lared_sanitize_memos_url(?string $value): string
{
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }

    return esc_url_raw($value);
}

function lared_sanitize_memos_token(?string $value): string
{
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }

    $value = preg_replace('/\s+/', '', $value);
    return is_string($value) ? sanitize_text_field($value) : '';
}

function lared_sanitize_memos_page_size($value): int
{
    $size = (int) $value;
    if ($size <= 0) {
        return 20;
    }

    return max(1, min(100, $size));
}

function lared_get_memos_site_url(): string
{
    return lared_sanitize_memos_url((string) get_option('lared_memos_site_url', ''));
}

function lared_get_memos_api_url(): string
{
    $saved = lared_sanitize_memos_url((string) get_option('lared_memos_api_url', ''));
    if ('' !== $saved) {
        return $saved;
    }

    $site_url = lared_get_memos_site_url();
    if ('' === $site_url) {
        return '';
    }

    return untrailingslashit($site_url) . '/api/v1/memos';
}

function lared_get_memos_api_token(): string
{
    return lared_sanitize_memos_token((string) get_option('lared_memos_api_token', ''));
}

function lared_get_memos_page_size(): int
{
    return lared_sanitize_memos_page_size((int) get_option('lared_memos_page_size', 20));
}

function lared_memos_parse_timestamp($value): int
{
    if (is_numeric($value)) {
        $ts = (int) $value;
        if ($ts > 1000000000000) {
            $ts = (int) floor($ts / 1000);
        }
        return max(0, $ts);
    }

    if (is_string($value) && '' !== trim($value)) {
        $ts = strtotime($value);
        return false === $ts ? 0 : max(0, (int) $ts);
    }

    return 0;
}

/**
 * @return string[]
 */
function lared_memos_extract_keywords(array $memo, string $content): array
{
    $keywords = [];

    $raw_tags = $memo['tags'] ?? null;
    if (is_array($raw_tags)) {
        foreach ($raw_tags as $tag) {
            $tag_text = sanitize_text_field((string) $tag);
            if ('' !== $tag_text) {
                $keywords[] = $tag_text;
            }
        }
    }

    if (preg_match_all('/[#＃]([\p{L}\p{N}_\-]{1,32})/u', $content, $matches) && !empty($matches[1])) {
        foreach ($matches[1] as $match) {
            $tag_text = sanitize_text_field((string) $match);
            if ('' !== $tag_text) {
                $keywords[] = $tag_text;
            }
        }
    }

    $keywords = array_values(array_unique($keywords));
    if (count($keywords) > 6) {
        $keywords = array_slice($keywords, 0, 6);
    }

    return $keywords;
}

function lared_memos_strip_hashtag_markers(string $content): string
{
    if ('' === trim($content)) {
        return $content;
    }

    $normalized = preg_replace('/[#＃]([\p{L}\p{N}_\-]{1,32})/u', '$1', $content);

    return is_string($normalized) ? $normalized : $content;
}

/**
 * 处理 Memos 内容中的图片
 * 将 Markdown 图片语法和普通图片 URL 转换为带 view-image 支持的 HTML
 * 
 * @param string $content 原始内容
 * @return string 处理后的 HTML
 */
function lared_memos_process_images(string $content): string
{
    if ('' === trim($content)) {
        return $content;
    }

    $site_url = lared_get_memos_site_url();
    
    // 匹配 Markdown 图片语法: ![alt](url)
    $content = preg_replace_callback(
        '/!\[([^\]]*)\]\(([^)]+)\)/',
        function ($matches) use ($site_url) {
            $alt = esc_attr($matches[1]);
            $url = esc_url($matches[2]);
            
            // 处理相对路径
            if ($site_url && strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
                $url = untrailingslashit($site_url) . '/' . ltrim($url, '/');
            }
            
            return '<img class="lazyload" data-src="' . $url . '" alt="' . $alt . '" />';
        },
        $content
    );
    
    // 匹配独立的图片 URL（行首或空格后的图片链接）
    $content = preg_replace_callback(
        '/(?:^|\s)(https?:\/\/[^\s<>"]+\.(?:jpg|jpeg|png|gif|webp|avif|svg))(?:\s|$)/i',
        function ($matches) use ($site_url) {
            $url = esc_url($matches[1]);
            
            return ' <img class="lazyload" data-src="' . $url . '" alt="" /> ';
        },
        $content
    );
    
    // 匹配 Memos 资源链接格式 /o/r/123
    $content = preg_replace_callback(
        '/(?:^|\s)(\/o\/r\/\d+(?:\/[^\s<>"]*)?)(?:\s|$)/',
        function ($matches) use ($site_url) {
            $path = $matches[1];
            $url = $site_url ? untrailingslashit($site_url) . $path : $path;
            
            return ' <img class="lazyload" data-src="' . esc_url($url) . '" alt="" /> ';
        },
        $content
    );

    return $content;
}

/**
 * 将 Memos 内容转换为 HTML（支持图片）
 * 
 * @param string $content 原始内容
 * @return string HTML 内容
 */
function lared_memos_content_to_html(string $content): string
{
    if ('' === trim($content)) {
        return '';
    }
    
    // 先处理图片
    $content = lared_memos_process_images($content);
    
    // 移除标签标记 #tag
    $content = lared_memos_strip_hashtag_markers($content);
    
    // 转换为段落
    return wpautop($content);
}

function lared_memos_is_list_array(array $value): bool
{
    if (function_exists('array_is_list')) {
        return array_is_list($value);
    }

    $index = 0;
    foreach ($value as $key => $_) {
        if ($key !== $index) {
            return false;
        }
        $index++;
    }

    return true;
}

/**
 * @return array<string, mixed>
 */
function lared_get_memos_stream(array $args = []): array
{
    $defaults = [
        'cache_ttl' => 300,
        'page_size' => lared_get_memos_page_size(),
        'force_refresh' => false,
    ];

    $args = wp_parse_args($args, $defaults);
    $cache_ttl = max(60, (int) $args['cache_ttl']);
    $page_size = max(1, min(100, (int) $args['page_size']));
    $force_refresh = !empty($args['force_refresh']);

    $site_url = lared_get_memos_site_url();
    $api_url = lared_get_memos_api_url();
    $token = lared_get_memos_api_token();

    if ('' === $api_url) {
        return [
            'items' => [],
            'stats' => [
                'count' => 0,
                'latest_timestamp' => 0,
            ],
            'errors' => [__('尚未配置 Memos API 地址。', 'lared')],
        ];
    }

    $request_url = $api_url;
    $request_url = add_query_arg([
        'pageSize' => $page_size,
    ], $request_url);

    $cache_key = 'lared_memos_' . md5($request_url . '|' . $token . '|' . $site_url . '|v2_hashtag_fix');

    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $headers = [
        'Accept' => 'application/json',
    ];

    if ('' !== $token) {
        $headers['Authorization'] = 'Bearer ' . $token;
        $headers['X-Api-Key'] = $token;
    }

    $response = wp_remote_get($request_url, [
        'timeout' => 12,
        'redirection' => 3,
        'headers' => $headers,
    ]);

    if (is_wp_error($response)) {
        return [
            'items' => [],
            'stats' => [
                'count' => 0,
                'latest_timestamp' => 0,
            ],
            'errors' => [$response->get_error_message()],
        ];
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = (string) wp_remote_retrieve_body($response);

    if ($code < 200 || $code >= 300 || '' === trim($body)) {
        return [
            'items' => [],
            'stats' => [
                'count' => 0,
                'latest_timestamp' => 0,
            ],
            'errors' => [sprintf(__('Memos API 请求失败（HTTP %d）。', 'lared'), $code)],
        ];
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        return [
            'items' => [],
            'stats' => [
                'count' => 0,
                'latest_timestamp' => 0,
            ],
            'errors' => [__('Memos API 返回了无效 JSON。', 'lared')],
        ];
    }

    $raw_items = [];
    if (isset($decoded['memos']) && is_array($decoded['memos'])) {
        $raw_items = $decoded['memos'];
    } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
        $raw_items = $decoded['data'];
    } elseif (lared_memos_is_list_array($decoded)) {
        $raw_items = $decoded;
    }

    $items = [];
    foreach ($raw_items as $memo) {
        if (!is_array($memo)) {
            continue;
        }

        $content = trim((string) ($memo['content'] ?? $memo['displayContent'] ?? ''));
        if ('' === $content) {
            continue;
        }

        $name = (string) ($memo['name'] ?? '');
        $uid = (string) ($memo['uid'] ?? $memo['id'] ?? '');
        $memo_id = '' !== $uid ? $uid : ('' !== $name ? $name : md5($content));

        $created = lared_memos_parse_timestamp($memo['createTime'] ?? ($memo['createdTs'] ?? ($memo['createdAt'] ?? 0)));
        $updated = lared_memos_parse_timestamp($memo['updateTime'] ?? ($memo['updatedTs'] ?? ($memo['updatedAt'] ?? 0)));

        $memo_url = '';
        if ('' !== $site_url) {
            $site = untrailingslashit($site_url);
            if ('' !== $name) {
                $memo_url = $site . '/' . ltrim($name, '/');
            } elseif ('' !== $uid) {
                $memo_url = $site . '/m/' . rawurlencode($uid);
            }
        }

        $content_html = lared_memos_content_to_html($content);
        $display_content = lared_memos_strip_hashtag_markers($content);
        $title = wp_trim_words(wp_strip_all_tags($display_content), 12, '…');
        $keywords = lared_memos_extract_keywords($memo, $content);

        $items[] = [
            'id' => $memo_id,
            'title' => '' !== $title ? $title : __('Memos 动态', 'lared'),
            'content_html' => wp_kses_post($content_html),
            'excerpt' => wp_strip_all_tags($display_content),  // 完整内容，不裁剪
            'keywords' => $keywords,
            'url' => '' !== $memo_url ? esc_url_raw($memo_url) : '',
            'visibility' => sanitize_text_field((string) ($memo['visibility'] ?? 'PUBLIC')),
            'created_timestamp' => $created,
            'updated_timestamp' => $updated,
        ];
    }

    usort(
        $items,
        static function (array $left, array $right): int {
            return ($right['created_timestamp'] ?? 0) <=> ($left['created_timestamp'] ?? 0);
        }
    );

    $latest_timestamp = !empty($items) ? (int) ($items[0]['created_timestamp'] ?? 0) : 0;

    $result = [
        'items' => $items,
        'stats' => [
            'count' => count($items),
            'latest_timestamp' => $latest_timestamp,
        ],
        'errors' => [],
    ];

    set_transient($cache_key, $result, $cache_ttl);

    return $result;
}

/**
 * 获取 Memos 统计数据（带每日缓存）
 * 用于热力图、日历、关键词统计
 * 
 * @return array<string, mixed>
 */
function lared_get_memos_stats(): array
{
    $cache_key = 'lared_memos_stats_v2';
    $cached = get_transient($cache_key);
    
    if (is_array($cached) && !empty($cached['items'])) {
        // 检查缓存是否是今天的
        $cached_date = $cached['cached_date'] ?? '';
        if ($cached_date === wp_date('Y-m-d')) {
            return $cached;
        }
    }
    
    // 获取所有 memos（不分页）
    $api_url = lared_get_memos_api_url();
    $token = lared_get_memos_api_token();
    $site_url = lared_get_memos_site_url();
    
    if ('' === $api_url) {
        return [
            'items' => [],
            'keyword_counts' => [],
            'daily_counts' => [],
            'cached_date' => wp_date('Y-m-d'),
        ];
    }
    
    // 请求大量数据用于统计
    $request_url = add_query_arg([
        'pageSize' => 1000,  // 获取足够多的数据
    ], $api_url);
    
    $headers = [
        'Accept' => 'application/json',
    ];
    
    if ('' !== $token) {
        $headers['Authorization'] = 'Bearer ' . $token;
        $headers['X-Api-Key'] = $token;
    }
    
    $response = wp_remote_get($request_url, [
        'timeout' => 30,
        'redirection' => 3,
        'headers' => $headers,
    ]);
    
    if (is_wp_error($response)) {
        return [
            'items' => [],
            'keyword_counts' => [],
            'daily_counts' => [],
            'cached_date' => wp_date('Y-m-d'),
        ];
    }
    
    $code = (int) wp_remote_retrieve_response_code($response);
    $body = (string) wp_remote_retrieve_body($response);
    
    if ($code < 200 || $code >= 300 || '' === trim($body)) {
        return [
            'items' => [],
            'keyword_counts' => [],
            'daily_counts' => [],
            'cached_date' => wp_date('Y-m-d'),
        ];
    }
    
    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        return [
            'items' => [],
            'keyword_counts' => [],
            'daily_counts' => [],
            'cached_date' => wp_date('Y-m-d'),
        ];
    }
    
    $raw_items = [];
    if (isset($decoded['memos']) && is_array($decoded['memos'])) {
        $raw_items = $decoded['memos'];
    } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
        $raw_items = $decoded['data'];
    } elseif (lared_memos_is_list_array($decoded)) {
        $raw_items = $decoded;
    }
    
    $items = [];
    $keyword_counts = [];
    $daily_counts = [];
    
    foreach ($raw_items as $memo) {
        if (!is_array($memo)) {
            continue;
        }
        
        $content = trim((string) ($memo['content'] ?? $memo['displayContent'] ?? ''));
        if ('' === $content) {
            continue;
        }
        
        $created = lared_memos_parse_timestamp($memo['createTime'] ?? ($memo['createdTs'] ?? ($memo['createdAt'] ?? 0)));
        $date_key = $created > 0 ? wp_date('Y-m-d', $created) : '';
        
        // 统计每日数量
        if ('' !== $date_key) {
            if (!isset($daily_counts[$date_key])) {
                $daily_counts[$date_key] = 0;
            }
            $daily_counts[$date_key]++;
        }
        
        // 提取关键词并统计
        $keywords = lared_memos_extract_keywords($memo, $content);
        foreach ($keywords as $keyword) {
            if (!isset($keyword_counts[$keyword])) {
                $keyword_counts[$keyword] = 0;
            }
            $keyword_counts[$keyword]++;
        }
        
        // 处理内容 HTML
        $content_html = lared_memos_content_to_html($content);
        
        $updated = lared_memos_parse_timestamp($memo['updateTime'] ?? ($memo['updatedTs'] ?? ($memo['updatedAt'] ?? 0)));
        
        $items[] = [
            'content' => $content,
            'content_html' => wp_kses_post($content_html),
            'keywords' => $keywords,
            'created_timestamp' => $created,
            'updated_timestamp' => $updated,
            'date' => $date_key,
        ];
    }
    
    // 按数量排序关键词
    arsort($keyword_counts);
    
    $result = [
        'items' => $items,
        'keyword_counts' => $keyword_counts,
        'daily_counts' => $daily_counts,
        'cached_date' => wp_date('Y-m-d'),
    ];
    
    // 缓存24小时
    set_transient($cache_key, $result, DAY_IN_SECONDS);
    
    return $result;
}

/**
 * 清除 Memos 缓存
 */
function lared_clear_memos_cache(): void
{
    global $wpdb;
    
    // 清除所有 memos 相关的 transient
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_lared_memos%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_lared_memos%'");
}

/**
 * 获取 Memos 热力图数据
 * 
 * @param int $days 天数
 * @return array
 */
function lared_get_memos_heatmap_data(int $days = 60): array
{
    // 从 JSON 缓存获取数据
    $cache = lared_get_memos_json_cache();
    $items = $cache['items'] ?? [];
    
    // 计算每日数量
    $daily_counts = [];
    foreach ($items as $item) {
        $created = (int) ($item['created_timestamp'] ?? 0);
        if ($created > 0) {
            $date = wp_date('Y-m-d', $created);
            $daily_counts[$date] = ($daily_counts[$date] ?? 0) + 1;
        }
    }
    
    $cells = [];
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $cell_ts = strtotime('-' . $i . ' days', current_time('timestamp'));
        if (false === $cell_ts) {
            continue;
        }
        
        $cell_date = wp_date('Y-m-d', $cell_ts);
        $cell_count = (int) ($daily_counts[$cell_date] ?? 0);
        
        // 5级深度
        $cell_level = 0;
        if ($cell_count === 1) {
            $cell_level = 4;
        } elseif ($cell_count >= 2) {
            $cell_level = 5;
        }
        
        $cells[] = [
            'date'  => $cell_date,
            'count' => $cell_count,
            'level' => $cell_level,
        ];
    }
    
    return $cells;
}

/**
 * 获取 Memos 关键词云数据
 * 
 * @param int $limit 数量限制
 * @return array
 */
function lared_get_memos_keywords(int $limit = 20): array
{
    // 从 JSON 缓存获取数据
    $cache = lared_get_memos_json_cache();
    $items = $cache['items'] ?? [];
    
    // 计算关键词使用次数
    $keyword_counts = [];
    foreach ($items as $item) {
        foreach ($item['keywords'] ?? [] as $kw) {
            $keyword_counts[$kw] = ($keyword_counts[$kw] ?? 0) + 1;
        }
    }
    
    // 按使用次数排序
    arsort($keyword_counts);
    
    $keywords = [];
    $count = 0;
    
    foreach ($keyword_counts as $keyword => $count_num) {
        if ($count >= $limit) {
            break;
        }
        $keywords[] = [
            'name' => $keyword,
            'count' => $count_num,
        ];
        $count++;
    }
    
    return $keywords;
}

/**
 * 获取 Memos 日历数据（某月）
 * 
 * @param int $year 年份
 * @param int $month 月份
 * @return array
 */
function lared_get_memos_calendar_data(int $year, int $month): array
{
    // 从 JSON 缓存获取数据
    $cache = lared_get_memos_json_cache();
    $items = $cache['items'] ?? [];
    
    // 计算每日数量
    $daily_counts = [];
    foreach ($items as $item) {
        $created = (int) ($item['created_timestamp'] ?? 0);
        if ($created > 0) {
            $date = wp_date('Y-m-d', $created);
            $daily_counts[$date] = ($daily_counts[$date] ?? 0) + 1;
        }
    }
    
    $days = [];
    $days_in_month = (int) wp_date('t', strtotime("$year-$month-01"));
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $count = $daily_counts[$date] ?? 0;
        
        $days[] = [
            'date' => $date,
            'count' => $count,
            'has_content' => $count > 0,
        ];
    }
    
    return $days;
}

/**
 * AJAX 处理：获取日历数据
 */
function lared_ajax_get_memos_calendar(): void
{
    // 验证 nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'lared_memos_filter_nonce')) {
        wp_send_json_error(['message' => __('安全验证失败', 'lared')]);
        return;
    }
    
    $year = (int) ($_POST['year'] ?? 0);
    $month = (int) ($_POST['month'] ?? 0);
    
    if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
        wp_send_json_error(['message' => __('日期无效', 'lared')]);
        return;
    }
    
    $days = lared_get_memos_calendar_data($year, $month);
    
    wp_send_json_success([
        'days' => $days,
        'year' => $year,
        'month' => $month,
    ]);
}
add_action('wp_ajax_lared_get_memos_calendar', 'lared_ajax_get_memos_calendar');
add_action('wp_ajax_nopriv_lared_get_memos_calendar', 'lared_ajax_get_memos_calendar');

/**
 * 获取 Memos JSON 缓存文件路径
 */
function lared_get_memos_cache_dir(): string
{
    return get_template_directory() . '/data';
}

function lared_get_memos_json_cache_file(): string
{
    return lared_get_memos_cache_dir() . '/memos-data.json';
}

/**
 * 从 JSON 文件获取 Memos 缓存（供前端使用）
 * 
 * @return array
 */
function lared_get_memos_json_cache(): array
{
    $cache_file = lared_get_memos_json_cache_file();
    
    if (!file_exists($cache_file)) {
        // 如果缓存文件不存在，先创建一次
        lared_update_memos_json_cache();
    }
    
    if (file_exists($cache_file)) {
        $content = file_get_contents($cache_file);
        $data = json_decode($content, true);
        if (is_array($data) && !empty($data['items'])) {
            return $data;
        }
    }
    
    return [
        'items' => [],
        'stats' => [
            'count' => 0,
            'latest_timestamp' => 0,
            'cached_at' => '',
        ],
    ];
}

/**
 * 更新 Memos JSON 缓存文件
 * 从 API 获取最新数据并保存到 JSON 文件
 */
function lared_update_memos_json_cache(): void
{
    $api_url = lared_get_memos_api_url();
    $token = lared_get_memos_api_token();
    
    if ('' === $api_url) {
        return;
    }
    
    // 获取足够多的数据用于首页显示
    $request_url = add_query_arg([
        'pageSize' => 20,
    ], $api_url);
    
    $headers = [
        'Accept' => 'application/json',
    ];
    
    if ('' !== $token) {
        $headers['Authorization'] = 'Bearer ' . $token;
        $headers['X-Api-Key'] = $token;
    }
    
    $response = wp_remote_get($request_url, [
        'timeout' => 30,
        'redirection' => 3,
        'headers' => $headers,
    ]);
    
    if (is_wp_error($response)) {
        return;
    }
    
    $code = (int) wp_remote_retrieve_response_code($response);
    $body = (string) wp_remote_retrieve_body($response);
    
    if ($code < 200 || $code >= 300 || '' === trim($body)) {
        return;
    }
    
    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        return;
    }
    
    $raw_items = [];
    if (isset($decoded['memos']) && is_array($decoded['memos'])) {
        $raw_items = $decoded['memos'];
    } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
        $raw_items = $decoded['data'];
    } elseif (lared_memos_is_list_array($decoded)) {
        $raw_items = $decoded;
    }
    
    $items = [];
    foreach ($raw_items as $memo) {
        if (!is_array($memo)) {
            continue;
        }
        
        $content = trim((string) ($memo['content'] ?? $memo['displayContent'] ?? ''));
        if ('' === $content) {
            continue;
        }
        
        $created = lared_memos_parse_timestamp($memo['createTime'] ?? ($memo['createdTs'] ?? ($memo['createdAt'] ?? 0)));
        $updated = lared_memos_parse_timestamp($memo['updateTime'] ?? ($memo['updatedTs'] ?? ($memo['updatedAt'] ?? 0)));
        
        // 处理内容
        $content_html = lared_memos_content_to_html($content);
        $display_content = lared_memos_strip_hashtag_markers($content);
        
        $items[] = [
            'content' => $content,
            'content_html' => $content_html,
            'excerpt' => wp_strip_all_tags($display_content),
            'keywords' => lared_memos_extract_keywords($memo, $content),
            'created_timestamp' => $created,
            'updated_timestamp' => $updated,
        ];
    }
    
    // 按时间排序
    usort($items, static function (array $left, array $right): int {
        return ($right['created_timestamp'] ?? 0) <=> ($left['created_timestamp'] ?? 0);
    });
    
    $latest_timestamp = !empty($items) ? (int) ($items[0]['created_timestamp'] ?? 0) : 0;
    
    $cache_data = [
        'items' => $items,
        'stats' => [
            'count' => count($items),
            'latest_timestamp' => $latest_timestamp,
            'cached_at' => gmdate('Y-m-d H:i:s'),
        ],
    ];
    
    $cache_file = lared_get_memos_json_cache_file();
    file_put_contents($cache_file, json_encode($cache_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

/**
 * 获取首页 Memos 数据（只从 JSON 缓存读取）
 * 
 * @param int $limit 获取条数
 * @return array
 */
function lared_get_home_memos(int $limit = 1): array
{
    $cache = lared_get_memos_json_cache();
    $items = $cache['items'] ?? [];
    
    if ($limit > 0 && count($items) > $limit) {
        $items = array_slice($items, 0, $limit);
    }
    
    return [
        'items' => $items,
        'stats' => $cache['stats'] ?? [
            'count' => count($items),
            'latest_timestamp' => 0,
        ],
    ];
}

/**
 * 设置 Memos 缓存自动刷新计划任务（每日 12:00）
 */
function lared_schedule_memos_cache_refresh(): void
{
    // 清除废弃的旧钩子（仅一次性清理）
    if (wp_next_scheduled('lared_memos_daily_cache_refresh')) {
        wp_clear_scheduled_hook('lared_memos_daily_cache_refresh');
    }

    if (wp_next_scheduled('lared_memos_json_cache_refresh')) {
        return;
    }

    $now = current_time('timestamp');
    $today_noon = strtotime(date('Y-m-d 12:00:00', $now));
    $next_run = ($today_noon <= $now) ? strtotime('+1 day', $today_noon) : $today_noon;

    wp_schedule_event($next_run, 'daily', 'lared_memos_json_cache_refresh');
}
add_action('wp', 'lared_schedule_memos_cache_refresh');

/**
 * 每日 12:00 刷新 Memos JSON 缓存
 */
function lared_refresh_memos_cache_daily(): void
{
    // 更新 JSON 缓存文件
    lared_update_memos_json_cache();
    
    // 同时清除旧的 transient 缓存
    lared_clear_memos_cache();
}
add_action('lared_memos_json_cache_refresh', 'lared_refresh_memos_cache_daily');

// 兼容旧钩子
add_action('lared_memos_daily_cache_refresh', 'lared_refresh_memos_cache_daily');

/**
 * 主题切换时清除计划任务
 */
function lared_clear_memos_cache_schedule(): void
{
    wp_clear_scheduled_hook('lared_memos_daily_cache_refresh');
    wp_clear_scheduled_hook('lared_memos_json_cache_refresh');
}
add_action('switch_theme', 'lared_clear_memos_cache_schedule');

/**
 * 主题激活时初始化 Memos 缓存
 */
function lared_activate_memos_cache(): void
{
    // 创建缓存目录并获取初始数据
    lared_update_memos_json_cache();
    
    // 设置每日 12:00 的定时任务
    lared_schedule_memos_cache_refresh();
}
add_action('after_switch_theme', 'lared_activate_memos_cache');

/**
 * 手动刷新 Memos 缓存（供管理员使用）
 * 
 * @return array
 */
function lared_manual_refresh_memos_cache(): array
{
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return [
            'success' => false,
            'message' => __('权限不足', 'lared'),
        ];
    }
    
    lared_update_memos_json_cache();
    
    $cache_file = lared_get_memos_json_cache_file();
    $cache_data = lared_get_memos_json_cache();
    
    return [
        'success' => true,
        'message' => __('缓存已更新', 'lared'),
        'cached_at' => $cache_data['stats']['cached_at'] ?? '',
        'item_count' => $cache_data['stats']['count'] ?? 0,
    ];
}

/**
 * 发布 Memos 动态
 * 
 * @param string $content 内容
 * @param array $tags 标签数组
 * @param string $visibility 可见性 (PUBLIC/PRIVATE/PROTECTED)
 * @return array
 */
function lared_publish_memo(string $content, array $tags = [], string $visibility = 'PUBLIC'): array
{
    $api_url = lared_get_memos_api_url();
    $token = lared_get_memos_api_token();
    
    if ('' === $api_url || '' === $token) {
        return [
            'success' => false,
            'message' => __('Memos API 未配置', 'lared'),
        ];
    }
    
    if ('' === trim($content)) {
        return [
            'success' => false,
            'message' => __('内容不能为空', 'lared'),
        ];
    }
    
    // 构建请求数据
    $data = [
        'content' => $content,
        'visibility' => $visibility,
    ];
    
    if (!empty($tags)) {
        $data['tags'] = array_values(array_filter(array_map('trim', $tags)));
    }
    
    $response = wp_remote_post($api_url, [
        'timeout' => 30,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'X-Api-Key' => $token,
        ],
        'body' => wp_json_encode($data),
    ]);
    
    if (is_wp_error($response)) {
        return [
            'success' => false,
            'message' => $response->get_error_message(),
        ];
    }
    
    $code = (int) wp_remote_retrieve_response_code($response);
    $body = (string) wp_remote_retrieve_body($response);
    
    if ($code < 200 || $code >= 300) {
        $error_data = json_decode($body, true);
        $error_message = $error_data['message'] ?? sprintf(__('发布失败 (HTTP %d)', 'lared'), $code);
        return [
            'success' => false,
            'message' => $error_message,
        ];
    }
    
    // 清除旧缓存，更新 JSON 缓存，让新内容立即显示
    lared_clear_memos_cache();
    lared_update_memos_json_cache();
    
    return [
        'success' => true,
        'message' => __('发布成功', 'lared'),
        'data' => json_decode($body, true),
    ];
}

/**
 * AJAX 处理：发布 Memos
 */
function lared_ajax_publish_memo(): void
{
    // 检查权限
    if (!is_user_logged_in() || !current_user_can('publish_posts')) {
        wp_send_json_error(['message' => __('权限不足', 'lared')]);
        return;
    }
    
    // 检查 nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'lared_memos_publish_nonce')) {
        wp_send_json_error(['message' => __('安全验证失败', 'lared')]);
        return;
    }
    
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    $tags = isset($_POST['tags']) ? array_map('sanitize_text_field', (array) $_POST['tags']) : [];
    $visibility = sanitize_text_field($_POST['visibility'] ?? 'PUBLIC');
    
    $result = lared_publish_memo($content, $tags, $visibility);
    
    if ($result['success']) {
        wp_send_json_success(['message' => $result['message']]);
    } else {
        wp_send_json_error(['message' => $result['message']]);
    }
}
add_action('wp_ajax_lared_publish_memo', 'lared_ajax_publish_memo');

/**
 * AJAX 处理：手动刷新 Memos 缓存
 */
function lared_ajax_refresh_memos_cache(): void
{
    $result = lared_manual_refresh_memos_cache();
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_lared_refresh_memos_cache', 'lared_ajax_refresh_memos_cache');

/**
 * 获取 Memos 缓存状态
 * 
 * @return array
 */
function lared_get_memos_cache_status(): array
{
    $cache_file = lared_get_memos_json_cache_file();
    $cache_data = lared_get_memos_json_cache();
    
    $file_exists = file_exists($cache_file);
    $file_size = $file_exists ? filesize($cache_file) : 0;
    $file_mtime = $file_exists ? filemtime($cache_file) : 0;
    
    return [
        'file_exists' => $file_exists,
        'file_path' => $cache_file,
        'file_size' => $file_size,
        'file_size_formatted' => size_format($file_size),
        'last_updated' => $file_mtime > 0 ? wp_date('Y-m-d H:i:s', $file_mtime) : '',
        'next_scheduled' => wp_next_scheduled('lared_memos_json_cache_refresh'),
        'item_count' => $cache_data['stats']['count'] ?? 0,
        'cached_at' => $cache_data['stats']['cached_at'] ?? '',
    ];
}

/**
 * 从 JSON 缓存获取可用的 Memos 关键词列表
 * 
 * @param int $limit 数量限制
 * @return array
 */
function lared_get_memos_keyword_suggestions(int $limit = 20): array
{
    $cache = lared_get_memos_json_cache();
    $items = $cache['items'] ?? [];
    
    // 计算关键词使用次数
    $keyword_counts = [];
    foreach ($items as $item) {
        foreach ($item['keywords'] ?? [] as $kw) {
            $keyword_counts[$kw] = ($keyword_counts[$kw] ?? 0) + 1;
        }
    }
    
    // 按使用次数排序，取前 N 个
    arsort($keyword_counts);
    
    return array_slice(array_keys($keyword_counts), 0, $limit);
}

/**
 * 按日期获取 Memos（从 JSON 缓存）
 * 
 * @param string $date 日期 (Y-m-d)
 * @return array
 */
function lared_get_memos_by_date(string $date): array
{
    $cache = lared_get_memos_json_cache();
    $items = $cache['items'] ?? [];
    
    $filtered = [];
    foreach ($items as $item) {
        $created = (int) ($item['created_timestamp'] ?? 0);
        if ($created > 0) {
            $item_date = wp_date('Y-m-d', $created);
            if ($item_date === $date) {
                $filtered[] = $item;
            }
        }
    }
    
    return $filtered;
}

/**
 * 按关键词获取 Memos（从 JSON 缓存）
 * 
 * @param string $keyword 关键词
 * @return array
 */
function lared_get_memos_by_keyword(string $keyword): array
{
    $cache = lared_get_memos_json_cache();
    $items = $cache['items'] ?? [];
    
    $filtered = [];
    foreach ($items as $item) {
        if (in_array($keyword, $item['keywords'] ?? [], true)) {
            $filtered[] = $item;
        }
    }
    
    return $filtered;
}

/**
 * AJAX 处理：按日期获取 Memos
 */
function lared_ajax_get_memos_by_date(): void
{
    // 验证 nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'lared_memos_filter_nonce')) {
        wp_send_json_error(['message' => __('安全验证失败', 'lared')]);
        return;
    }
    
    $date = sanitize_text_field($_POST['date'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        wp_send_json_error(['message' => __('日期格式无效', 'lared')]);
        return;
    }
    
    $items = lared_get_memos_by_date($date);
    
    // 渲染 HTML
    ob_start();
    if (!empty($items)) {
        foreach ($items as $item) {
            lared_render_memo_card($item);
        }
    } else {
        echo '<div class="memos-empty-day">' . esc_html__('该日期暂无说说', 'lared') . '</div>';
    }
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'count' => count($items),
        'date' => $date,
    ]);
}
add_action('wp_ajax_lared_get_memos_by_date', 'lared_ajax_get_memos_by_date');
add_action('wp_ajax_nopriv_lared_get_memos_by_date', 'lared_ajax_get_memos_by_date');

/**
 * AJAX 处理：按关键词获取 Memos
 */
function lared_ajax_get_memos_by_keyword(): void
{
    // 验证 nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'lared_memos_filter_nonce')) {
        wp_send_json_error(['message' => __('安全验证失败', 'lared')]);
        return;
    }
    
    $keyword = sanitize_text_field($_POST['keyword'] ?? '');
    if ('' === $keyword) {
        wp_send_json_error(['message' => __('关键词不能为空', 'lared')]);
        return;
    }
    
    $items = lared_get_memos_by_keyword($keyword);
    
    // 渲染 HTML
    ob_start();
    if (!empty($items)) {
        foreach ($items as $item) {
            lared_render_memo_card($item);
        }
    } else {
        echo '<div class="memos-empty-keyword">' . esc_html__('该关键词暂无说说', 'lared') . '</div>';
    }
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'count' => count($items),
        'keyword' => $keyword,
    ]);
}
add_action('wp_ajax_lared_get_memos_by_keyword', 'lared_ajax_get_memos_by_keyword');
add_action('wp_ajax_nopriv_lared_get_memos_by_keyword', 'lared_ajax_get_memos_by_keyword');

/**
 * 渲染单个 Memos 卡片
 * 
 * @param array $item Memos 数据
 */
function lared_render_memo_card(array $item): void
{
    $keywords = $item['keywords'] ?? [];
    $content_html = $item['content_html'] ?? '';
    $created_timestamp = (int) ($item['created_timestamp'] ?? 0);
    $updated_timestamp = (int) ($item['updated_timestamp'] ?? 0);
    $time_source = $updated_timestamp > 0 ? $updated_timestamp : $created_timestamp;
    $time_human = $time_source > 0
        ? sprintf(
            /* translators: %s: relative time */
            __('%s前', 'lared'),
            human_time_diff($time_source, current_time('timestamp'))
        )
        : '';
    ?>
    <article class="memos-card">
        <div class="memos-card-link" role="group">
            <!-- 第一行：关键词 + 日期 -->
            <div class="memos-card-header">
                <div class="memos-card-keywords">
                    <?php if (!empty($keywords)) : ?>
                        <?php foreach ($keywords as $keyword) : ?>
                            <span class="memos-card-keyword" data-keyword="<?php echo esc_attr($keyword); ?>">#<?php echo esc_html((string) $keyword); ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ('' !== $time_human && $time_source > 0) : ?>
                    <time class="memos-card-time" datetime="<?php echo esc_attr(wp_date('c', $time_source)); ?>"><?php echo esc_html($time_human); ?></time>
                <?php endif; ?>
            </div>
            <!-- 第二行：内容 -->
            <div class="memos-card-body" view-image><?php echo $content_html; ?></div>
        </div>
    </article>
    <?php
}
