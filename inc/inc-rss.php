<?php

if (!defined('ABSPATH')) {
    exit;
}

function lared_extract_bookmark_avatar_url($value): string
{
    $raw = trim((string) $value);
    if ('' === $raw) {
        return '';
    }

    if (preg_match('/<img[^>]+src=("|\')([^"\']+)("|\')/i', $raw, $matches)) {
        $raw = trim((string) ($matches[2] ?? ''));
    }

    return esc_url_raw($raw);
}

function lared_get_rss_cache_dir(): string
{
    return trailingslashit(get_template_directory() . '/data/rss');
}

function lared_ensure_rss_cache_dir(): bool
{
    $cache_dir = lared_get_rss_cache_dir();
    if (is_dir($cache_dir)) {
        return true;
    }

    return wp_mkdir_p($cache_dir);
}

function lared_get_rss_cache_file_path(string $cache_key): string
{
    $safe_key = preg_replace('/[^a-z0-9_\-]/i', '', $cache_key);
    if (!is_string($safe_key) || '' === $safe_key) {
        $safe_key = md5($cache_key);
    }

    return lared_get_rss_cache_dir() . $safe_key . '.json';
}

/**
 * @return array<string, mixed>|null
 */
function lared_get_rss_cache(string $cache_key): ?array
{
    $cache_file = lared_get_rss_cache_file_path($cache_key);
    if (!is_readable($cache_file)) {
        return null;
    }

    $raw = file_get_contents($cache_file);
    if (!is_string($raw) || '' === trim($raw)) {
        return null;
    }

    $payload = json_decode($raw, true);
    if (!is_array($payload) || !isset($payload['expires_at'], $payload['data'])) {
        return null;
    }

    $expires_at = (int) $payload['expires_at'];
    if ($expires_at > 0 && time() > $expires_at) {
        @unlink($cache_file);
        return null;
    }

    return is_array($payload['data']) ? $payload['data'] : null;
}

function lared_set_rss_cache(string $cache_key, array $data, int $ttl): bool
{
    if (!lared_ensure_rss_cache_dir()) {
        return false;
    }

    $cache_file = lared_get_rss_cache_file_path($cache_key);
    $now = time();

    $payload = [
        'created_at' => $now,
        'expires_at' => $now + max(60, $ttl),
        'data' => $data,
    ];

    $json = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || '' === $json) {
        return false;
    }

    return false !== file_put_contents($cache_file, $json, LOCK_EX);
}

/**
 * @return array{removed:int, errors:int, dir:string}
 */
function lared_clear_rss_cache_files(): array
{
    $cache_dir = lared_get_rss_cache_dir();
    if (!is_dir($cache_dir)) {
        return [
            'removed' => 0,
            'errors' => 0,
            'dir' => $cache_dir,
        ];
    }

    $files = glob($cache_dir . '*.json');
    if (!is_array($files)) {
        return [
            'removed' => 0,
            'errors' => 1,
            'dir' => $cache_dir,
        ];
    }

    $removed = 0;
    $errors = 0;

    foreach ($files as $file) {
        if (!is_string($file) || '' === $file || !is_file($file)) {
            continue;
        }

        if (@unlink($file)) {
            $removed++;
        } else {
            $errors++;
        }
    }

    return [
        'removed' => $removed,
        'errors' => $errors,
        'dir' => $cache_dir,
    ];
}

/**
 * 从友情链接构建可用 RSS 源列表。
 *
 * @return array<int, array<string, mixed>>
 */
function lared_get_friend_rss_sources(): array
{
    $bookmarks = get_bookmarks([
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_invisible' => 1,
    ]);

    if (empty($bookmarks)) {
        return [];
    }

    $sources = [];

    foreach ($bookmarks as $bookmark) {
        $site_url = isset($bookmark->link_url) ? esc_url_raw((string) $bookmark->link_url) : '';
        if ('' === $site_url) {
            continue;
        }

        $feed_candidates = lared_get_bookmark_feed_candidates($bookmark);
        if (empty($feed_candidates)) {
            continue;
        }

        $site_name = isset($bookmark->link_name) ? sanitize_text_field((string) $bookmark->link_name) : '';
        $site_desc = isset($bookmark->link_description) ? sanitize_text_field((string) $bookmark->link_description) : '';
        $site_avatar = lared_extract_bookmark_avatar_url($bookmark->link_image ?? '');
        $host = (string) wp_parse_url($site_url, PHP_URL_HOST);

        $sources[] = [
            'bookmark_id' => isset($bookmark->link_id) ? (int) $bookmark->link_id : 0,
            'name' => '' !== $site_name ? $site_name : $host,
            'description' => $site_desc,
            'avatar' => $site_avatar,
            'site_url' => $site_url,
            'host' => $host,
            'feed_url' => $feed_candidates[0],
            'feed_candidates' => $feed_candidates,
        ];
    }

    return $sources;
}

/**
 * @param object $bookmark
 * @return string[]
 */
function lared_get_bookmark_feed_candidates(object $bookmark): array
{
    $candidates = [];

    $bookmark_rss = '';
    if (isset($bookmark->link_rss)) {
        $bookmark_rss = esc_url_raw((string) $bookmark->link_rss);
    }

    // 没有显式填写 RSS 地址的友链，直接跳过（不猜测、不报失败）
    if ('' === $bookmark_rss) {
        return [];
    }

    $candidates[] = $bookmark_rss;

    // 有显式 RSS 地址时，添加备用候选 URL 作为 fallback
    $site_url = isset($bookmark->link_url) ? esc_url_raw((string) $bookmark->link_url) : '';
    if ('' !== $site_url) {
        $base = trailingslashit($site_url);

        $candidates[] = $base . 'feed/';
        $candidates[] = $base . 'rss/';
        $candidates[] = $base . 'feed';
        $candidates[] = $base . 'rss.xml';
        $candidates[] = $base . 'atom.xml';
    }

    $candidates = array_values(array_filter(array_unique($candidates), static function ($url): bool {
        return '' !== trim((string) $url);
    }));

    return $candidates;
}

/**
 * @param array<string, mixed> $source
 * @return array<string, mixed>
 */
function lared_fetch_source_feed(array $source): array
{
    if (!function_exists('fetch_feed')) {
        require_once ABSPATH . WPINC . '/feed.php';
    }

    $candidates = [];
    if (!empty($source['feed_candidates']) && is_array($source['feed_candidates'])) {
        $candidates = $source['feed_candidates'];
    } elseif (!empty($source['feed_url'])) {
        $candidates = [(string) $source['feed_url']];
    }

    $last_error = '';

    foreach ($candidates as $candidate) {
        $feed_url = esc_url_raw((string) $candidate);
        if ('' === $feed_url) {
            continue;
        }

        $feed = fetch_feed($feed_url);
        if (is_wp_error($feed)) {
            $last_error = $feed->get_error_message();
            continue;
        }

        if (!$feed instanceof SimplePie) {
            $last_error = 'SimplePie 对象无效';
            continue;
        }

        if ((int) $feed->get_item_quantity(1) <= 0) {
            $last_error = '该 Feed 无可用条目';
            continue;
        }

        return [
            'feed' => $feed,
            'feed_url' => $feed_url,
            'error' => '',
        ];
    }

    return [
        'feed' => null,
        'feed_url' => '',
        'error' => '' !== $last_error ? $last_error : __('RSS 暂不可用', 'lared'),
    ];
}

/**
 * ===========================================
 * 友联动态 JSON 文件缓存系统（每日4次更新）
 * ===========================================
 */

/**
 * 获取友联动态 JSON 缓存目录
 */
function lared_get_feed_cache_dir(): string
{
    return get_template_directory() . '/data';
}

/**
 * 获取友联动态 JSON 缓存文件路径
 */
function lared_get_feed_json_cache_file(): string
{
    return lared_get_feed_cache_dir() . '/feed-data.json';
}

/**
 * 更新友联动态 JSON 缓存
 * 从所有 RSS 源获取数据并保存到 JSON 文件
 */
function lared_update_feed_json_cache(): void
{
    $sources = lared_get_friend_rss_sources();
    
    if (empty($sources)) {
        return;
    }
    
    $items = [];
    $errors = [];
    $active_sources = 0;
    
    // 获取足够多的数据用于分页加载（默认18 + 自动加载9 + 更多按钮加载 = 建议保存50-100条）
    $items_per_source = 10;
    
    foreach ($sources as $source) {
        $feed_result = lared_fetch_source_feed($source);
        
        if (!empty($feed_result['error'])) {
            $errors[] = [
                'site' => $source['name'],
                'message' => (string) $feed_result['error'],
            ];
            continue;
        }
        
        $feed = $feed_result['feed'] ?? null;
        if (!$feed instanceof SimplePie) {
            continue;
        }
        
        $feed_items = $feed->get_items(0, $items_per_source);
        if (empty($feed_items)) {
            continue;
        }
        
        $active_sources++;
        
        foreach ($feed_items as $feed_item) {
            if (!$feed_item instanceof SimplePie_Item) {
                continue;
            }
            
            $item_url = (string) $feed_item->get_permalink();
            if ('' === trim($item_url)) {
                continue;
            }
            
            $item_title = wp_strip_all_tags((string) $feed_item->get_title());
            $item_desc = (string) $feed_item->get_description();
            if ('' === trim($item_desc)) {
                $item_desc = (string) $feed_item->get_content();
            }
            
            $items[] = [
                'title' => '' !== trim($item_title) ? $item_title : __('Untitled', 'lared'),
                'url' => esc_url_raw($item_url),
                'excerpt' => wp_trim_words(wp_strip_all_tags($item_desc), 36, '…'),
                'published_timestamp' => max(0, (int) $feed_item->get_date('U')),
                'published_human' => (string) $feed_item->get_date(get_option('date_format') . ' ' . get_option('time_format')),
                'site_name' => (string) $source['name'],
                'site_avatar' => (string) ($source['avatar'] ?? ''),
                'site_url' => (string) $source['site_url'],
                'site_host' => (string) $source['host'],
            ];
        }
    }
    
    // 按发布时间排序
    usort($items, static function (array $left, array $right): int {
        return ($right['published_timestamp'] ?? 0) <=> ($left['published_timestamp'] ?? 0);
    });
    
    $latest_timestamp = !empty($items) ? (int) $items[0]['published_timestamp'] : 0;
    
    $cache_data = [
        'items' => $items,
        'stats' => [
            'source_count' => count($sources),
            'active_source_count' => $active_sources,
            'item_count' => count($items),
            'latest_timestamp' => $latest_timestamp,
            'cached_at' => gmdate('Y-m-d H:i:s'),
        ],
        'errors' => $errors,
    ];
    
    $cache_file = lared_get_feed_json_cache_file();
    file_put_contents($cache_file, json_encode($cache_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

/**
 * 从 JSON 缓存获取友联动态
 * 
 * @return array
 */
function lared_get_feed_json_cache(): array
{
    $cache_file = lared_get_feed_json_cache_file();
    
    if (!file_exists($cache_file)) {
        lared_update_feed_json_cache();
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
            'source_count' => 0,
            'active_source_count' => 0,
            'item_count' => 0,
            'latest_timestamp' => 0,
            'cached_at' => '',
        ],
        'errors' => [],
    ];
}

/**
 * 获取分页的友联动态数据（前端使用）
 * 
 * @param int $offset 起始位置
 * @param int $limit 获取数量
 * @return array
 */
function lared_get_feed_items_paged(int $offset = 0, int $limit = 18): array
{
    $cache = lared_get_feed_json_cache();
    $all_items = $cache['items'] ?? [];
    $total = count($all_items);
    
    $items = array_slice($all_items, $offset, $limit);
    
    return [
        'items' => $items,
        'total' => $total,
        'offset' => $offset,
        'limit' => $limit,
        'has_more' => ($offset + $limit) < $total,
        'stats' => $cache['stats'] ?? [],
    ];
}

/**
 * 设置友联动态缓存定时任务（每 6 小时）
 */
function lared_schedule_feed_cache_refresh(): void
{
    if (wp_next_scheduled('lared_feed_json_cache_refresh')) {
        return;
    }

    $now = current_time('timestamp');
    $hours = [0, 6, 12, 18];
    $next_run = null;

    foreach ($hours as $hour) {
        $candidate = strtotime(date('Y-m-d', $now) . ' ' . sprintf('%02d:00:00', $hour));
        if ($candidate > $now) {
            $next_run = $candidate;
            break;
        }
    }

    if ($next_run === null) {
        $next_run = strtotime('+1 day', strtotime(date('Y-m-d', $now) . ' 00:00:00'));
    }

    wp_schedule_event($next_run, 'lared_four_times_daily', 'lared_feed_json_cache_refresh');
}
add_action('wp', 'lared_schedule_feed_cache_refresh');

/**
 * 注册每日4次的计划任务间隔
 */
function lared_add_four_times_daily_schedule($schedules): array
{
    $schedules['lared_four_times_daily'] = [
        'interval' => 6 * HOUR_IN_SECONDS, // 6小时 = 每日4次
        'display' => __('每日4次', 'lared'),
    ];
    return $schedules;
}
add_filter('cron_schedules', 'lared_add_four_times_daily_schedule');

/**
 * 定时刷新友联动态 JSON 缓存
 */
function lared_refresh_feed_cache_scheduled(): void
{
    lared_update_feed_json_cache();
}
add_action('lared_feed_json_cache_refresh', 'lared_refresh_feed_cache_scheduled');

/**
 * 主题切换时清除计划任务
 */
function lared_clear_feed_cache_schedule(): void
{
    wp_clear_scheduled_hook('lared_feed_json_cache_refresh');
}
add_action('switch_theme', 'lared_clear_feed_cache_schedule');

/**
 * 主题激活时初始化友联动态缓存
 */
function lared_activate_feed_cache(): void
{
    lared_update_feed_json_cache();
    lared_schedule_feed_cache_refresh();
}
add_action('after_switch_theme', 'lared_activate_feed_cache');

/**
 * AJAX 处理：获取分页友联动态
 */
function lared_ajax_get_feed_items(): void
{
    check_ajax_referer('lared_feed_nonce', 'nonce');
    
    $offset = (int) ($_POST['offset'] ?? 0);
    $limit = (int) ($_POST['limit'] ?? 18);
    
    // 限制最大获取数量
    $limit = max(1, min(50, $limit));
    
    $result = lared_get_feed_items_paged($offset, $limit);
    
    // 渲染 HTML
    ob_start();
    if (!empty($result['items'])) {
        foreach ($result['items'] as $item) {
            lared_render_feed_card($item);
        }
    }
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'total' => $result['total'],
        'offset' => $result['offset'],
        'limit' => $result['limit'],
        'has_more' => $result['has_more'],
        'cached_at' => $result['stats']['cached_at'] ?? '',
    ]);
}
add_action('wp_ajax_lared_get_feed_items', 'lared_ajax_get_feed_items');
add_action('wp_ajax_nopriv_lared_get_feed_items', 'lared_ajax_get_feed_items');

/**
 * AJAX: 获取所有 RSS 源列表（仅管理员）
 */
function lared_ajax_get_feed_sources(): void
{
    if (!check_ajax_referer('lared_feed_refresh_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce 验证失败，请刷新页面后重试']);
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足']);
        return;
    }

    $sources = lared_get_friend_rss_sources();

    $list = [];
    foreach ($sources as $i => $source) {
        $list[] = [
            'index'    => $i,
            'name'     => (string) ($source['name'] ?? ''),
            'site_url' => (string) ($source['site_url'] ?? ''),
            'feed_url' => (string) ($source['feed_url'] ?? ''),
        ];
    }

    wp_send_json_success([
        'sources' => $list,
        'total'   => count($list),
    ]);
}
add_action('wp_ajax_lared_get_feed_sources', 'lared_ajax_get_feed_sources');

/**
 * AJAX: 拉取单个 RSS 源数据（仅管理员）
 */
function lared_ajax_fetch_single_feed(): void
{
    if (!check_ajax_referer('lared_feed_refresh_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce 验证失败，请刷新页面后重试']);
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足']);
        return;
    }

    $index = (int) ($_POST['source_index'] ?? -1);
    $sources = lared_get_friend_rss_sources();

    if ($index < 0 || $index >= count($sources)) {
        wp_send_json_error(['message' => '无效的源索引']);
        return;
    }

    $source = $sources[$index];
    $items_per_source = 10;

    $feed_result = lared_fetch_source_feed($source);

    if (!empty($feed_result['error'])) {
        wp_send_json_success([
            'status'  => 'error',
            'name'    => (string) $source['name'],
            'message' => (string) $feed_result['error'],
            'items'   => [],
        ]);
        return;
    }

    $feed = $feed_result['feed'] ?? null;
    if (!$feed instanceof SimplePie) {
        wp_send_json_success([
            'status'  => 'error',
            'name'    => (string) $source['name'],
            'message' => 'Feed 对象不可用',
            'items'   => [],
        ]);
        return;
    }

    $feed_items = $feed->get_items(0, $items_per_source);
    $collected = [];

    foreach ($feed_items as $feed_item) {
        if (!$feed_item instanceof SimplePie_Item) {
            continue;
        }

        $item_url = (string) $feed_item->get_permalink();
        if ('' === trim($item_url)) {
            continue;
        }

        $item_title = wp_strip_all_tags((string) $feed_item->get_title());
        $item_desc = (string) $feed_item->get_description();
        if ('' === trim($item_desc)) {
            $item_desc = (string) $feed_item->get_content();
        }

        $collected[] = [
            'title'               => '' !== trim($item_title) ? $item_title : __('Untitled', 'lared'),
            'url'                 => esc_url_raw($item_url),
            'excerpt'             => wp_trim_words(wp_strip_all_tags($item_desc), 36, '…'),
            'published_timestamp' => max(0, (int) $feed_item->get_date('U')),
            'published_human'     => (string) $feed_item->get_date(get_option('date_format') . ' ' . get_option('time_format')),
            'site_name'           => (string) $source['name'],
            'site_avatar'         => (string) ($source['avatar'] ?? ''),
            'site_url'            => (string) $source['site_url'],
            'site_host'           => (string) $source['host'],
        ];
    }

    wp_send_json_success([
        'status'  => 'ok',
        'name'    => (string) $source['name'],
        'message' => '',
        'items'   => $collected,
    ]);
}
add_action('wp_ajax_lared_fetch_single_feed', 'lared_ajax_fetch_single_feed');

/**
 * AJAX: 保存合并后的 feed 缓存 JSON（仅管理员）
 */
function lared_ajax_save_feed_cache(): void
{
    if (!check_ajax_referer('lared_feed_refresh_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce 验证失败，请刷新页面后重试']);
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足']);
        return;
    }

    $raw = isset($_POST['feed_data']) ? wp_unslash($_POST['feed_data']) : '';
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        wp_send_json_error(['message' => '数据格式无效']);
        return;
    }

    // 允许全部拉取失败时保存空缓存（清除旧数据）
    if (empty($data['items'])) {
        $data['items'] = [];
    }

    // 按时间排序
    $items = $data['items'];
    usort($items, static function (array $l, array $r): int {
        return ($r['published_timestamp'] ?? 0) <=> ($l['published_timestamp'] ?? 0);
    });

    $latest_timestamp = !empty($items) ? (int) $items[0]['published_timestamp'] : 0;

    $cache_data = [
        'items'  => $items,
        'stats'  => [
            'source_count'        => (int) ($data['source_count'] ?? 0),
            'active_source_count' => (int) ($data['active_source_count'] ?? 0),
            'item_count'          => count($items),
            'latest_timestamp'    => $latest_timestamp,
            'cached_at'           => gmdate('Y-m-d H:i:s'),
        ],
        'errors' => $data['errors'] ?? [],
    ];

    $cache_file = lared_get_feed_json_cache_file();
    $result = file_put_contents($cache_file, json_encode($cache_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    if (false === $result) {
        wp_send_json_error(['message' => '写入缓存文件失败']);
        return;
    }

    wp_send_json_success([
        'message'    => '缓存已更新',
        'item_count' => count($items),
        'cached_at'  => $cache_data['stats']['cached_at'],
    ]);
}
add_action('wp_ajax_lared_save_feed_cache', 'lared_ajax_save_feed_cache');

/**
 * 渲染单个友联动态卡片
 * 
 * @param array $item
 */
function lared_render_feed_card(array $item): void
{
    $item_title = (string) ($item['title'] ?? '');
    $item_url = (string) ($item['url'] ?? '');
    $item_excerpt = (string) ($item['excerpt'] ?? '');
    $item_site_name = (string) ($item['site_name'] ?? '');
    $item_site_avatar = (string) ($item['site_avatar'] ?? '');
    $item_site_url = (string) ($item['site_url'] ?? '');
    $item_timestamp = (int) ($item['published_timestamp'] ?? 0);
    
    $time_text = '';
    if ($item_timestamp > 0) {
        $time_text = sprintf(__('%s前', 'lared'), human_time_diff($item_timestamp, current_time('timestamp')));
    }
    ?>
    <article class="rss-feed-card">
        <a class="rss-feed-card-link" href="<?php echo esc_url($item_url); ?>" target="_blank" rel="noopener noreferrer">
            <header class="rss-feed-card-head">
                <h2 class="rss-feed-card-title"><?php echo esc_html($item_title); ?></h2>
                <span class="rss-feed-card-icon" aria-hidden="true"><i class="fa-sharp fa-thin fa-square-arrow-up-right"></i></span>
            </header>
            <?php if ('' !== $item_excerpt) : ?>
                <p class="rss-feed-card-excerpt"><?php echo esc_html($item_excerpt); ?></p>
            <?php endif; ?>
            <footer class="rss-feed-card-meta">
                <?php if ('' !== $item_site_name) : ?>
                    <span class="rss-feed-card-site">
                        <?php if ('' !== $item_site_avatar) : ?>
                            <img class="rss-feed-card-site-avatar lazyload" data-src="<?php echo esc_url($item_site_avatar); ?>" alt="<?php echo esc_attr($item_site_name); ?>" />
                        <?php endif; ?>
                        <span><?php echo esc_html($item_site_name); ?></span>
                    </span>
                <?php endif; ?>
                <?php if ('' !== $time_text) : ?>
                    <time datetime="<?php echo esc_attr(wp_date('c', $item_timestamp)); ?>"><?php echo esc_html($time_text); ?></time>
                <?php elseif ('' !== $item['site_host'] ?? '') : ?>
                    <span><?php echo esc_html($item['site_host']); ?></span>
                <?php endif; ?>
            </footer>
        </a>
    </article>
    <?php
}
