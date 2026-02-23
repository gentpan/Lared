<?php
/*
Template Name: 备忘录动态
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        // 只从 JSON 缓存获取数据（每日12:00更新）
        $cache_data = function_exists('pan_get_memos_json_cache') 
            ? pan_get_memos_json_cache() 
            : ['items' => [], 'stats' => []];
        
        $items = $cache_data['items'] ?? [];
        $stats = $cache_data['stats'] ?? [];
        $errors = [];

        $item_count = (int) ($stats['count'] ?? 0);
        $latest_timestamp = (int) ($stats['latest_timestamp'] ?? 0);
        $latest_updated = $latest_timestamp > 0
            ? wp_date('Y-m-d H:i:s', $latest_timestamp)
            : __('暂无更新', 'pan');
        $cached_at = $stats['cached_at'] ?? '';

        // 从缓存数据计算侧边栏统计
        $daily_counts = [];
        $keyword_counts = [];
        
        foreach ($items as $item) {
            // 每日统计
            $created = (int) ($item['created_timestamp'] ?? 0);
            if ($created > 0) {
                $date_key = gmdate('Y-m-d', $created);
                $daily_counts[$date_key] = ($daily_counts[$date_key] ?? 0) + 1;
            }
            
            // 关键词统计
            $keywords = $item['keywords'] ?? [];
            foreach ($keywords as $kw) {
                $keyword_counts[$kw] = ($keyword_counts[$kw] ?? 0) + 1;
            }
        }
        
        // 热力图数据（60天）
        $memos_heatmap_cells = [];
        for ($i = 59; $i >= 0; $i--) {
            $cell_ts = strtotime('-' . $i . ' days', current_time('timestamp'));
            if (false === $cell_ts) continue;
            $cell_date = wp_date('Y-m-d', $cell_ts);
            $cell_count = (int) ($daily_counts[$cell_date] ?? 0);
            
            $cell_level = 0;
            if ($cell_count === 1) {
                $cell_level = 4;
            } elseif ($cell_count >= 2) {
                $cell_level = 5;
            }
            
            $memos_heatmap_cells[] = [
                'date' => $cell_date,
                'count' => $cell_count,
                'level' => $cell_level,
            ];
        }

        // 关键词数据（前16个）
        arsort($keyword_counts);
        $memos_keywords = [];
        $count = 0;
        foreach ($keyword_counts as $keyword => $count_num) {
            if ($count >= 16) break;
            $memos_keywords[] = [
                'name' => $keyword,
                'count' => $count_num,
            ];
            $count++;
        }

        // 日历数据（当前月）
        $current_year = gmdate('Y');
        $current_month = gmdate('m');
        $calendar_days = [];
        $days_in_month = gmdate('t', strtotime("$current_year-$current_month-01"));
        
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
            $count = $daily_counts[$date] ?? 0;
            $calendar_days[$day] = [
                'date' => $date,
                'count' => $count,
                'has_content' => $count > 0,
            ];
        }
        ?>

        <main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#fff] pb-[90px] max-[900px]:pb-16">
            <section class="listing-head border-b border-[#d9d9d9]">
                <div class="listing-head-inner">
                    <span class="listing-head-accent" aria-hidden="true"></span>
                    <div class="listing-head-main">
                        <div class="listing-head-title-row">
                            <h1 class="listing-head-title"><?php the_title(); ?></h1>
                            <p class="listing-head-side-stat">
                                <?php
                                printf(
                                    /* translators: 1: memos count, 2: latest updated */
                                    esc_html__('%1$d 条动态 · %2$s', 'pan'),
                                    $item_count,
                                    $latest_updated
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="memos-layout">
                <!-- 左侧：内容区域 -->
                <section class="memos-main-content">
                    <?php if (!empty($errors)) : ?>
                        <div class="memos-error" role="alert">
                            <?php foreach ($errors as $error) : ?>
                                <p><?php echo esc_html((string) $error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    // 管理员发布表单 - 从缓存数据获取关键词建议
                    if (is_user_logged_in() && current_user_can('publish_posts')) :
                        $keyword_suggestions = array_slice(array_keys($keyword_counts), 0, 8);
                    ?>
                    <!-- 发布表单 -->
                    <section class="memos-publish-box">
                        <div class="memos-publish-header">
                            <h3><i class="fa-solid fa-pen-to-square"></i> <?php esc_html_e('发布说说', 'pan'); ?></h3>
                        </div>
                        <form class="memos-publish-form" id="memos-publish-form" method="post">
                            <?php wp_nonce_field('pan_memos_publish_nonce', 'memos_publish_nonce'); ?>
                            <div class="memos-publish-textarea-wrap">
                                <textarea 
                                    name="memos_content" 
                                    id="memos-content"
                                    class="memos-publish-textarea"
                                    placeholder="<?php esc_attr_e('写下你的想法... 支持 #标签', 'pan'); ?>"
                                    rows="3"
                                    required
                                ></textarea>
                            </div>
                            <div class="memos-publish-tools">
                                <div class="memos-publish-tags">
                                    <span class="memos-publish-label"><i class="fa-solid fa-hashtag"></i> <?php esc_html_e('关键词', 'pan'); ?>:</span>
                                    <div class="memos-publish-tag-list" id="memos-tag-list">
                                        <?php foreach ($keyword_suggestions as $keyword) : ?>
                                            <span class="memos-publish-tag-btn" data-tag="<?php echo esc_attr($keyword); ?>">#<?php echo esc_html($keyword); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="memos-publish-actions">
                                    <select name="memos_visibility" class="memos-publish-visibility">
                                        <option value="PUBLIC"><?php esc_html_e('公开', 'pan'); ?></option>
                                        <option value="PRIVATE"><?php esc_html_e('私密', 'pan'); ?></option>
                                    </select>
                                    <button type="submit" class="memos-publish-submit">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        <?php esc_html_e('发布', 'pan'); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="memos-publish-status" id="memos-publish-status"></div>
                        </form>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($items)) : ?>
                        <div class="memos-grid">
                            <?php foreach ($items as $item) : ?>
                                <?php
                                $keywords = is_array($item['keywords'] ?? null) ? $item['keywords'] : [];
                                $content_html = (string) ($item['content_html'] ?? '');
                                $created_timestamp = (int) ($item['created_timestamp'] ?? 0);
                                $updated_timestamp = (int) ($item['updated_timestamp'] ?? 0);
                                $time_source = $updated_timestamp > 0 ? $updated_timestamp : $created_timestamp;
                                $time_human = $time_source > 0
                                    ? sprintf(
                                        /* translators: %s: relative time */
                                        __('%s前', 'pan'),
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
                                                <?php else : ?>
                                                    <span class="memos-card-keyword memos-card-keyword-empty"><?php esc_html_e('无关键词', 'pan'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ('' !== $time_human && $time_source > 0) : ?>
                                                <time class="memos-card-time" datetime="<?php echo esc_attr(gmdate('c', $time_source)); ?>"><?php echo esc_html($time_human); ?></time>
                                            <?php endif; ?>
                                        </div>

                                        <!-- 第二行：内容 -->
                                        <div class="memos-card-body" view-image><?php echo $content_html; ?></div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (empty($errors)) : ?>
                        <div class="listing-empty">
                            <p><?php esc_html_e('暂未获取到 Memos 内容。', 'pan'); ?></p>
                            <p class="listing-empty-note"><?php esc_html_e('请检查 Memos 站点地址、API 地址和 Token 配置。', 'pan'); ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- 右侧：侧边栏 -->
                <aside class="memos-sidebar" aria-label="<?php esc_attr_e('说说侧边栏', 'pan'); ?>">
                    <!-- 日历 -->
                    <section class="memos-sidebar-block memos-sidebar-block--calendar">
                        <div class="memos-sidebar-block-title memos-sidebar-block-title--calendar">
                            <h3><i class="fa-solid fa-calendar" aria-hidden="true"></i><span><?php esc_html_e('日历', 'pan'); ?></span></h3>
                            <div class="memos-calendar-nav">
                                <button type="button" class="memos-calendar-prev" data-action="prev-month"><i class="fa-solid fa-chevron-left"></i></button>
                                <span class="memos-calendar-title-text" id="memos-calendar-title"><?php echo esc_html($current_year); ?>-<?php echo esc_html($current_month); ?></span>
                                <button type="button" class="memos-calendar-next" data-action="next-month"><i class="fa-solid fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="memos-sidebar-block-body">
                            <div class="memos-calendar" id="memos-calendar" data-year="<?php echo esc_attr($current_year); ?>" data-month="<?php echo esc_attr($current_month); ?>">
                                <div class="memos-calendar-grid">
                                    <div class="memos-calendar-weekdays">
                                        <span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span>
                                    </div>
                                    <div class="memos-calendar-days" id="memos-calendar-days">
                                        <?php
                                        $first_day = strtotime("$current_year-$current_month-01");
                                        $days_in_month = gmdate('t', $first_day);
                                        $start_weekday = gmdate('w', $first_day);
                                        
                                        // 空白填充
                                        for ($i = 0; $i < $start_weekday; $i++) {
                                            echo '<span class="memos-calendar-day memos-calendar-day-empty"></span>';
                                        }
                                        
                                        // 日期
                                        $today = gmdate('Y-m-d');
                                        for ($day = 1; $day <= $days_in_month; $day++) {
                                            $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                                            $is_today = $date === $today ? ' is-today' : '';
                                            $has_memos = !empty($calendar_days[$day]['has_content']) ? ' has-memos' : '';
                                            $memo_count = $calendar_days[$day]['count'] ?? 0;
                                            $title_attr = $memo_count > 0 ? ' title="' . $memo_count . ' 条动态"' : '';
                                            $data_date_attr = ' data-date="' . esc_attr($date) . '"';
                                            echo '<span class="memos-calendar-day' . esc_attr($is_today . $has_memos) . '"' . $data_date_attr . $title_attr . '>' . (int) $day . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 热力图 -->
                    <section class="memos-sidebar-block memos-sidebar-block--heatmap">
                        <div class="memos-sidebar-block-title">
                            <h3><i class="fa-solid fa-fire" aria-hidden="true"></i><span><?php esc_html_e('更新热力图', 'pan'); ?></span></h3>
                        </div>
                        <div class="memos-sidebar-block-body">
                            <div class="memos-mini-heatmap" aria-label="<?php esc_attr_e('近60天更新热力图', 'pan'); ?>">
                                <?php foreach ($memos_heatmap_cells as $heatmap_cell) : ?>
                                    <span
                                        class="memos-mini-heatmap-cell tone-red level-<?php echo esc_attr((string) $heatmap_cell['level']); ?>"
                                        title="<?php echo esc_attr((string) $heatmap_cell['date'] . ' · ' . (int) $heatmap_cell['count'] . ' 篇'); ?>"
                                    ></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>

                    <!-- 关键词 -->
                    <section class="memos-sidebar-block memos-sidebar-block--tags">
                        <div class="memos-sidebar-block-title">
                            <h3><i class="fa-solid fa-hashtag" aria-hidden="true"></i><span><?php esc_html_e('关键词', 'pan'); ?></span></h3>
                        </div>
                        <div class="memos-sidebar-block-body">
                            <?php if (!empty($memos_keywords)) : ?>
                                <div class="memos-sidebar-tags">
                                    <?php 
                                    $tag_colors = ['red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'pink'];
                                    $color_index = 0;
                                    foreach ($memos_keywords as $keyword_item) : 
                                        $color_class = $tag_colors[$color_index % count($tag_colors)];
                                        $color_index++;
                                    ?>
                                        <span class="memos-tag-<?php echo esc_attr($color_class); ?>" data-keyword="<?php echo esc_attr($keyword_item['name']); ?>">#<?php echo esc_html($keyword_item['name']); ?> (<?php echo (int) $keyword_item['count']; ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <p><?php esc_html_e('暂无关键词。', 'pan'); ?></p>
                            <?php endif; ?>
                        </div>
                    </section>
                </aside>
            </div>
        </main>
        
        <?php
        // 显示缓存信息
        $cached_date = $memos_stats['cached_date'] ?? '';
        if ('' !== $cached_date) : ?>
        <!-- 缓存信息 -->
        <div class="memos-cache-info" style="display:none;" data-cache-date="<?php echo esc_attr($cached_date); ?>"></div>
        <?php endif; ?>

        <?php
    endwhile;
endif;

get_footer();
