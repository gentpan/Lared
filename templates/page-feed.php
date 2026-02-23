<?php
/*
Template Name: 订阅内容
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        // 从 JSON 缓存获取友联动态数据
        $feed_data = function_exists('pan_get_feed_items_paged') 
            ? pan_get_feed_items_paged(0, 18) 
            : ['items' => [], 'total' => 0, 'has_more' => false, 'stats' => []];
        
        $items = $feed_data['items'] ?? [];
        $total = $feed_data['total'] ?? 0;
        $has_more = $feed_data['has_more'] ?? false;
        $stats = $feed_data['stats'] ?? [];
        
        $source_count = $stats['source_count'] ?? 0;
        $item_count = $stats['item_count'] ?? 0;
        $latest_timestamp = $stats['latest_timestamp'] ?? 0;
        $latest_updated = $latest_timestamp > 0
            ? wp_date('Y-m-d H:i:s', $latest_timestamp)
            : __('暂无更新', 'pan');
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
                                    /* translators: 1: sources count, 2: items count, 3: latest updated */
                                    esc_html__('已订阅 %1$d 个站点，聚合 %2$d 条内容。最后更新：%3$s', 'pan'),
                                    $source_count,
                                    $item_count,
                                    $latest_updated
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="listing-content rss-subscribe-content">
                <?php if ('' !== trim((string) get_the_content())) : ?>
                    <article class="rss-subscribe-hero-text page-content prose prose-neutral max-w-none">
                        <?php the_content(); ?>
                    </article>
                <?php endif; ?>

                <?php if (!empty($items)) : ?>
                    <div class="rss-feed-grid" id="rss-feed-grid" data-offset="18" data-total="<?php echo esc_attr($total); ?>">
                        <?php 
                        foreach ($items as $item) {
                            if (function_exists('pan_render_feed_card')) {
                                pan_render_feed_card($item);
                            }
                        }
                        ?>
                    </div>
                    
                    <!-- 加载动画 -->
                    <div class="feed-loading-spinner" id="feed-loading-spinner" style="display: none;">
                        <span class="feed-spinner"></span>
                    </div>
                    
                    <!-- 加载更多按钮 -->
                    <div class="feed-load-more-wrap" id="feed-load-more-wrap" style="display: <?php echo $has_more ? 'block' : 'none'; ?>;">
                        <button type="button" class="feed-load-more-btn" id="feed-load-more-btn">
                            <span><?php esc_html_e('加载更多', 'pan'); ?></span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </div>
                    
                    <!-- 没有更多内容提示 -->
                    <div class="feed-no-more" id="feed-no-more" style="display: none;">
                        <p><?php esc_html_e('已加载全部内容', 'pan'); ?></p>
                    </div>
                <?php elseif ($source_count > 0) : ?>
                    <div class="listing-empty">
                        <p><?php esc_html_e('已检测到订阅源，但暂未抓取到可展示内容。', 'pan'); ?></p>
                        <p class="listing-empty-note"><?php esc_html_e('请检查友情链接 RSS 地址是否可访问，系统会自动缓存后重试。', 'pan'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="listing-empty">
                        <p><?php esc_html_e('暂未配置可用订阅源。', 'pan'); ?></p>
                        <p class="listing-empty-note"><?php esc_html_e('请先在友情链接中配置站点及 RSS 地址（link_rss）。', 'pan'); ?></p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        
        <?php if (!empty($items)) : ?>
        <script>
        (function() {
            var grid = document.getElementById('rss-feed-grid');
            var spinner = document.getElementById('feed-loading-spinner');
            var loadMoreWrap = document.getElementById('feed-load-more-wrap');
            var loadMoreBtn = document.getElementById('feed-load-more-btn');
            var noMore = document.getElementById('feed-no-more');
            
            if (!grid) return;
            
            var offset = 18; // 初始已加载18个
            var limit = 9;   // 每次加载9个
            var total = parseInt(grid.getAttribute('data-total') || '0', 10);
            var isLoading = false;
            var autoLoadCount = 0; // 自动加载次数（0 = 还没自动加载过）
            var maxAutoLoad = 1;   // 只自动加载1次
            
            // 加载数据函数
            function loadFeedItems(isManual) {
                if (isLoading) return;
                if (offset >= total) {
                    if (loadMoreWrap) loadMoreWrap.style.display = 'none';
                    if (noMore) noMore.style.display = 'block';
                    return;
                }
                
                isLoading = true;
                
                // 显示 loading 动画
                if (spinner) spinner.style.display = 'flex';
                if (isManual && loadMoreBtn) {
                    loadMoreBtn.disabled = true;
                    loadMoreBtn.innerHTML = '<span class="feed-spinner-small"></span>';
                }
                
                // 延迟1秒后加载（确保 loading 动画可见）
                setTimeout(function() {
                    var formData = new FormData();
                    formData.append('action', 'pan_get_feed_items');
                    formData.append('nonce', '<?php echo wp_create_nonce('pan_feed_nonce'); ?>');
                    formData.append('offset', offset);
                    formData.append('limit', limit);
                    
                    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success && data.data.html) {
                            // 插入新内容
                            var tempDiv = document.createElement('div');
                            tempDiv.innerHTML = data.data.html;
                            var newItems = tempDiv.querySelectorAll('.rss-feed-card');
                            newItems.forEach(function(item) {
                                grid.appendChild(item);
                            });
                            
                            // 更新偏移量
                            offset += newItems.length;
                            grid.setAttribute('data-offset', offset);
                            
                            // 检查是否还有更多
                            if (!data.data.has_more || offset >= total) {
                                if (loadMoreWrap) loadMoreWrap.style.display = 'none';
                                if (noMore) noMore.style.display = 'block';
                            }
                        }
                    })
                    .catch(function() {
                        // 出错时显示加载更多按钮
                        if (loadMoreWrap) loadMoreWrap.style.display = 'block';
                    })
                    .finally(function() {
                        isLoading = false;
                        if (spinner) spinner.style.display = 'none';
                        if (isManual && loadMoreBtn) {
                            loadMoreBtn.disabled = false;
                            loadMoreBtn.innerHTML = '<span><?php esc_html_e('加载更多', 'pan'); ?></span><i class="fa-solid fa-chevron-down"></i>';
                        }
                    });
                }, 1000);
            }
            
            // 手动加载按钮点击
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    loadFeedItems(true);
                });
            }
            
            // 滚动自动加载（只执行一次）
            function onScroll() {
                if (autoLoadCount >= maxAutoLoad) {
                    window.removeEventListener('scroll', onScroll);
                    return;
                }
                
                if (isLoading) return;
                
                var rect = loadMoreWrap ? loadMoreWrap.getBoundingClientRect() : null;
                if (rect && rect.top < window.innerHeight + 200) {
                    autoLoadCount++;
                    loadFeedItems(false);
                    
                    // 自动加载一次后移除监听
                    if (autoLoadCount >= maxAutoLoad) {
                        window.removeEventListener('scroll', onScroll);
                    }
                }
            }
            
            window.addEventListener('scroll', onScroll);
            // 初始检查（如果内容少，按钮已经在视口内）
            onScroll();
        })();
        </script>
        <?php endif; ?>

        <?php
    endwhile;
endif;

get_footer();
