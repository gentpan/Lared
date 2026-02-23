<?php
/**
 * 评论等级系统 - 12级
 * 
 * 等级划分：
 * Lv1-Lv3: 新手阶段 (1-20评论)
 * Lv4-Lv6: 成长阶段 (21-100评论)  
 * Lv7-Lv9: 资深阶段 (101-500评论)
 * Lv10-Lv12: 大师阶段 (501+评论)
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 获取评论等级阈值配置
 * 
 * @return array
 */
function pan_get_comment_level_thresholds(): array
{
    return [
        1  => ['name' => '见习', 'min' => 1,   'max' => 2,   'color' => '#9ca3af', 'bg' => '#f3f4f6', 'icon' => 'fa-solid fa-seedling'],
        2  => ['name' => '新手', 'min' => 3,   'max' => 5,   'color' => '#22c55e', 'bg' => '#dcfce7', 'icon' => 'fa-solid fa-leaf'],
        3  => ['name' => '学徒', 'min' => 6,   'max' => 10,  'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-solid fa-spa'],
        4  => ['name' => '助理', 'min' => 11,  'max' => 20,  'color' => '#14b8a6', 'bg' => '#ccfbf1', 'icon' => 'fa-solid fa-feather'],
        5  => ['name' => '专员', 'min' => 21,  'max' => 35,  'color' => '#06b6d4', 'bg' => '#cffafe', 'icon' => 'fa-solid fa-pen-nib'],
        6  => ['name' => '骨干', 'min' => 36,  'max' => 60,  'color' => '#0ea5e9', 'bg' => '#e0f2fe', 'icon' => 'fa-solid fa-pen-fancy'],
        7  => ['name' => '资深', 'min' => 61,  'max' => 100, 'color' => '#3b82f6', 'bg' => '#dbeafe', 'icon' => 'fa-solid fa-pen-to-square'],
        8  => ['name' => '专家', 'min' => 101, 'max' => 200, 'color' => '#6366f1', 'bg' => '#e0e7ff', 'icon' => 'fa-solid fa-medal'],
        9  => ['name' => '顾问', 'min' => 201, 'max' => 350, 'color' => '#8b5cf6', 'bg' => '#ede9fe', 'icon' => 'fa-solid fa-crown'],
        10 => ['name' => '大师', 'min' => 351, 'max' => 500, 'color' => '#a855f7', 'bg' => '#f3e8ff', 'icon' => 'fa-solid fa-gem'],
        11 => ['name' => '宗师', 'min' => 501, 'max' => 800, 'color' => '#d946ef', 'bg' => '#fae8ff', 'icon' => 'fa-solid fa-star'],
        12 => ['name' => '传说', 'min' => 801, 'max' => null,'color' => '#f43f5e', 'bg' => '#ffe4e6', 'icon' => 'fa-solid fa-trophy'],
    ];
}

/**
 * 根据评论数计算等级
 * 
 * @param int $count
 * @return int
 */
function pan_calculate_level(int $count): int
{
    if ($count < 1) {
        return 0;
    }

    $thresholds = pan_get_comment_level_thresholds();

    foreach ($thresholds as $level => $config) {
        if ($count >= $config['min'] && ($config['max'] === null || $count <= $config['max'])) {
            return $level;
        }
    }

    return 12; // 超过最高等级阈值时返回12级
}

/**
 * 获取等级配置信息
 * 
 * @param int $level 等级1-12
 * @return array|null
 */
function pan_get_level_config(int $level): ?array
{
    if ($level < 1 || $level > 12) {
        return null;
    }

    $thresholds = pan_get_comment_level_thresholds();
    return $thresholds[$level] ?? null;
}

/**
 * 获取用户的评论统计数据
 * 
 * @param string $email
 * @return array
 */
function pan_get_user_comment_stats(string $email): array
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
    $level_info = pan_get_cached_level($email);
    
    if ($level_info === null) {
        // 缓存不存在或过期，重新计算
        $level_info = pan_update_and_cache_level($email);
    }

    $current_level = $level_info['level'];
    $count = $level_info['count'];
    
    $config = pan_get_level_config($current_level);
    
    // 计算进度
    $progress = 0;
    $next_level = null;
    
    if ($current_level < 12) {
        $next_level_config = pan_get_level_config($current_level + 1);
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
        ],
        'progress' => $progress,
        'next_level' => $next_level,
    ];
}

/**
 * 从缓存获取等级信息
 * 
 * @param string $email
 * @return array|null
 */
function pan_get_cached_level(string $email): ?array
{
    $cache_key = 'pan_commenter_levels_v2';
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
 * 
 * @param string $email
 * @return array
 */
function pan_update_and_cache_level(string $email): array
{
    global $wpdb;
    
    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_author_email = %s AND comment_approved = '1'",
        $email
    ));
    
    $level = pan_calculate_level($count);
    
    $result = [
        'level' => $level,
        'count' => $count,
        'updated_at' => time(),
    ];
    
    // 更新缓存
    $cache_key = 'pan_commenter_levels_v2';
    $levels = get_option($cache_key, []);
    $levels[sanitize_email($email)] = $result;
    update_option($cache_key, $levels, false);
    
    return $result;
}

/**
 * 清除用户等级缓存
 * 
 * @param string $email
 */
function pan_clear_level_cache(string $email): void
{
    $cache_key = 'pan_commenter_levels_v2';
    $levels = get_option($cache_key, []);
    $email_key = sanitize_email($email);
    
    if (isset($levels[$email_key])) {
        unset($levels[$email_key]);
        update_option($cache_key, $levels, false);
    }
}

/**
 * 评论提交后更新等级缓存
 * 
 * @param int $comment_id
 * @param int|string $comment_approved
 */
function pan_update_level_on_comment($comment_id, $comment_approved): void
{
    $comment = get_comment($comment_id);
    if (!$comment || empty($comment->comment_author_email)) {
        return;
    }
    
    // 清除缓存，下次访问时重新计算
    pan_clear_level_cache($comment->comment_author_email);
}
add_action('comment_post', 'pan_update_level_on_comment', 10, 2);
add_action('wp_set_comment_status', function($comment_id, $status) {
    $comment = get_comment($comment_id);
    if ($comment && !empty($comment->comment_author_email)) {
        pan_clear_level_cache($comment->comment_author_email);
    }
}, 10, 2);

/**
 * 获取等级徽章HTML
 * 
 * @param array $level
 * @param string $size small|medium|large
 * @return string
 */
function pan_get_level_badge_simple(array $level, string $size = 'small'): string
{
    if (empty($level['id']) || $level['id'] < 1) {
        return '';
    }

    $config = $level['config'] ?? null;
    if (!$config) {
        $config = pan_get_level_config($level['id']);
    }

    if (!$config) {
        return '';
    }

    $size_class = match ($size) {
        'large' => 'badge-large',
        'medium' => 'badge-medium',
        default => 'badge-small',
    };

    $style = sprintf(
        'color: %s; background: %s; border-color: %s;',
        esc_attr($config['color']),
        esc_attr($config['bg']),
        esc_attr($config['color'])
    );

    $icon = '';
    if (!empty($config['icon'])) {
        $icon = sprintf('<i class="%s"></i> ', esc_attr($config['icon']));
    }

    return sprintf(
        '<span class="pan-level-badge %s" style="%s" title="%s">%sLv%d %s</span>',
        esc_attr($size_class),
        $style,
        esc_attr(sprintf('评论数: %d', $level['count'] ?? 0)),
        $icon,
        $level['id'],
        esc_html($config['name'])
    );
}

/**
 * 获取完整的等级徽章（带进度条）
 * 
 * @param array $stats
 * @return string
 */
function pan_get_level_badge_full(array $stats): string
{
    $level = $stats['level'] ?? ['id' => 0];
    
    if (empty($level['id'])) {
        return '<div class="pan-level-badge-full guest">' .
               '<span class="level-text">登录后发表评论获取等级</span>' .
               '</div>';
    }

    $config = $level['config'];
    $progress = $stats['progress'] ?? 0;
    $count = $stats['count'] ?? 0;
    $next = $stats['next_level'];

    $html = '<div class="pan-level-badge-full">';
    
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

/**
 * AJAX: 获取评论者等级信息
 */
function pan_ajax_get_commenter_level(): void
{
    check_ajax_referer('pan_level_nonce', 'nonce');
    
    $email = sanitize_email($_POST['email'] ?? '');
    
    if (empty($email)) {
        wp_send_json_error(['message' => 'Invalid email']);
        return;
    }
    
    $stats = pan_get_user_comment_stats($email);
    
    wp_send_json_success([
        'stats' => $stats,
        'badge_simple' => pan_get_level_badge_simple($stats['level']),
        'badge_full' => pan_get_level_badge_full($stats),
    ]);
}
add_action('wp_ajax_pan_get_commenter_level', 'pan_ajax_get_commenter_level');
add_action('wp_ajax_nopriv_pan_get_commenter_level', 'pan_ajax_get_commenter_level');

/**
 * 批量刷新所有评论者等级（用于初始化或修复数据）
 * 
 * @return array
 */
function pan_refresh_all_commenter_levels(): array
{
    global $wpdb;
    
    $updated = 0;
    $errors = 0;
    $batch_size = 100;
    $offset = 0;
    
    // 清除旧缓存
    delete_option('pan_commenter_levels_v2');
    
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
                pan_update_and_cache_level($email);
                $updated++;
            } catch (Exception $e) {
                $errors++;
                error_log('Pan Level Update Error: ' . $e->getMessage());
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
