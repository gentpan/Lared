<?php
/*
Template Name: 友情链接
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $bookmarks = get_bookmarks([
            'orderby'        => 'name',
            'order'          => 'ASC',
            'hide_invisible' => 1,
        ]);
        $bookmark_count = is_array($bookmarks) ? count($bookmarks) : 0;

        // 按分类分组
        $link_categories = get_terms([
            'taxonomy'   => 'link_category',
            'hide_empty' => true,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ]);
        if (is_wp_error($link_categories)) {
            $link_categories = [];
        }
        ?>

        <main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#ffffff] pb-[90px] max-[900px]:pb-16">
            <section class="listing-head border-b border-[#d9d9d9]">
                <div class="listing-head-inner">
                    <span class="listing-head-accent" aria-hidden="true"></span>
                    <div class="listing-head-main">
                        <div class="listing-head-title-row">
                            <h1 class="listing-head-title"><i class="fa-sharp fa-thin fa-square-share-nodes" aria-hidden="true"></i><?php the_title(); ?></h1>
                            <p class="listing-head-side-stat"><?php printf(esc_html__('%d 个链接', 'lared'), $bookmark_count); ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="listing-content friend-links-content">
                <?php if ('' !== trim((string) get_the_content())) : ?>
                    <article class="friend-links-intro page-content prose prose-neutral max-w-none">
                        <?php the_content(); ?>
                    </article>
                <?php endif; ?>

                <?php if (!empty($link_categories)) : ?>
                    <?php
                    $flink_cat_icons = [
                        'fa-planet-ringed',
                        'fa-galaxy',
                        'fa-star-shooting',
                        'fa-sun',
                        'fa-moon-stars',
                        'fa-meteor',
                        'fa-rocket-launch',
                        'fa-satellite',
                    ];
                    $flink_cat_icon_idx = 0;
                    ?>
                    <?php foreach ($link_categories as $link_cat) :
                        $cat_bookmarks = get_bookmarks([
                            'orderby'        => 'name',
                            'order'          => 'ASC',
                            'hide_invisible' => 1,
                            'category'       => $link_cat->term_id,
                        ]);
                        if (empty($cat_bookmarks)) continue;

                        // 分类描述中包含 "text" 则使用纯文字样式，否则使用带头像卡片样式
                        $cat_desc_raw = strtolower(trim((string) $link_cat->description));
                        $is_text_style = (str_contains($cat_desc_raw, 'text'));
                        $style_class = $is_text_style ? 'friend-links-category--text' : 'friend-links-category--card';
                        $current_icon = $flink_cat_icons[$flink_cat_icon_idx % count($flink_cat_icons)];
                        $flink_cat_icon_idx++;
                    ?>
                    <div class="friend-links-category <?php echo esc_attr($style_class); ?>">
                        <h2 class="friend-links-category-title">
                            <i class="fa-sharp fa-thin <?php echo esc_attr($current_icon); ?>" aria-hidden="true"></i>
                            <?php echo esc_html($link_cat->name); ?>
                            <span class="friend-links-category-count"><?php echo count($cat_bookmarks); ?></span>
                        </h2>

                        <?php if ($is_text_style) : ?>
                        <!-- 纯文字列表样式（含头像） -->
                        <div class="friend-links-text-grid">
                            <?php foreach ($cat_bookmarks as $bookmark) :
                                $site_url  = (string) $bookmark->link_url;
                                $site_name = (string) $bookmark->link_name;
                                $site_desc = (string) $bookmark->link_description;
                                $host      = wp_parse_url($site_url, PHP_URL_HOST);

                                // 优先使用自定义图片，没有则用 ico 服务获取 favicon
                                $avatar_url = lared_extract_bookmark_avatar_url($bookmark->link_image ?? '');
                                if ('' === $avatar_url && !empty($host)) {
                                    $avatar_url = 'https://ico.bluecdn.com/' . urlencode($host);
                                }
                            ?>
                            <a class="friend-link-text-item" href="<?php echo esc_url($site_url); ?>" target="_blank" rel="noopener noreferrer">
                                <?php if ('' !== $avatar_url) : ?>
                                <img class="friend-link-text-avatar lazyload" data-src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($site_name); ?>" width="32" height="32" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" />
                                <span class="friend-link-text-avatar-letter" style="display:none;" aria-hidden="true"><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
                                <?php else : ?>
                                <span class="friend-link-text-avatar-letter" aria-hidden="true"><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
                                <?php endif; ?>
                                <div class="friend-link-text-body">
                                    <span class="friend-link-text-name"><?php echo esc_html($site_name); ?></span>
                                    <?php if ('' !== trim($site_desc)) : ?>
                                        <span class="friend-link-text-desc"><?php echo esc_html($site_desc); ?></span>
                                    <?php endif; ?>
                                </div>
                                <i class="fa-solid fa-arrow-up-right-from-square friend-link-text-arrow" aria-hidden="true"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>

                        <?php else : ?>
                        <!-- 带头像卡片样式 -->
                        <div class="friend-links-grid">
                            <?php foreach ($cat_bookmarks as $bookmark) : ?>
                                <?php
                                $site_url  = (string) $bookmark->link_url;
                                $site_name = (string) $bookmark->link_name;
                                $site_desc = (string) $bookmark->link_description;
                                $host      = wp_parse_url($site_url, PHP_URL_HOST);

                                $avatar_url = lared_extract_bookmark_avatar_url($bookmark->link_image ?? '');

                                if ('' === $avatar_url && !empty($host)) {
                                    global $wpdb;
                                    $like_host = '%' . $wpdb->esc_like($host) . '%';
                                    $commenter_email = $wpdb->get_var($wpdb->prepare(
                                        "SELECT comment_author_email FROM {$wpdb->comments}
                                         WHERE comment_author_url LIKE %s
                                           AND comment_author_email != ''
                                           AND comment_approved = '1'
                                         ORDER BY comment_date_gmt DESC
                                         LIMIT 1",
                                        $like_host
                                    ));
                                    if (!empty($commenter_email)) {
                                        $avatar_url = get_avatar_url($commenter_email, ['size' => 128]);
                                    }
                                }

                                if ('' === $avatar_url && !empty($host)) {
                                    $avatar_url = 'https://ico.bluecdn.com/' . urlencode($host);
                                }
                                ?>
                                <article class="friend-link-card">
                                    <a class="friend-link-card-link" href="<?php echo esc_url($site_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <div class="friend-link-card-avatar">
                                            <img class="friend-link-card-avatar-img" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($site_name); ?>" loading="lazy" />
                                        </div>
                                        <div class="friend-link-card-body">
                                            <h2 class="friend-link-card-title"><?php echo esc_html($site_name); ?></h2>
                                            <?php if ('' !== trim($site_desc)) : ?>
                                                <p class="friend-link-card-desc"><?php echo esc_html($site_desc); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="listing-empty">
                        <p><?php esc_html_e('暂未配置友情链接。', 'lared'); ?></p>
                        <p class="listing-empty-note"><?php esc_html_e('可在后台 Links/链接 管理中添加站点后自动展示。', 'lared'); ?></p>
                    </div>
                <?php endif; ?>

                <?php
                // 我的站点信息卡片
                $my_site_name   = get_bloginfo('name');
                $my_site_desc   = get_bloginfo('description');
                $my_site_url    = home_url('/');
                $my_feed_url    = get_feed_link();
                $my_avatar_url  = home_url('/images/avatar.svg');

                $my_info_items = [
                    ['label' => __('站点名称', 'lared'), 'value' => $my_site_name,  'icon' => 'fa-globe'],
                    ['label' => __('站点描述', 'lared'), 'value' => $my_site_desc,  'icon' => 'fa-align-left'],
                    ['label' => __('网址',     'lared'), 'value' => $my_site_url,   'icon' => 'fa-link'],
                    ['label' => __('Feed',     'lared'), 'value' => $my_feed_url,   'icon' => 'fa-rss'],
                    ['label' => __('头像地址', 'lared'), 'value' => $my_avatar_url,  'icon' => 'fa-image'],
                ];
                ?>
                <div class="friend-links-bottom-grid">
                <section class="friend-links-myinfo">
                    <div class="friend-links-myinfo-head">
                        <h2 class="friend-links-myinfo-title">
                            <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                            <?php esc_html_e('我的信息', 'lared'); ?>
                        </h2>
                        <button type="button" class="friend-links-apply-btn" id="friendLinkApplyBtn">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            <?php esc_html_e('申请友链', 'lared'); ?>
                        </button>
                    </div>
                    <p class="friend-links-myinfo-hint"><?php esc_html_e('欢迎互换友链，以下信息可直接复制', 'lared'); ?></p>
                    <ul class="friend-links-myinfo-list">
                        <?php foreach ($my_info_items as $info_item) : ?>
                            <li class="friend-links-myinfo-item">
                                <span class="friend-links-myinfo-label">
                                    <i class="fa-solid <?php echo esc_attr($info_item['icon']); ?>" aria-hidden="true"></i>
                                    <?php echo esc_html($info_item['label']); ?>
                                </span>
                                <span class="friend-links-myinfo-value" title="<?php echo esc_attr($info_item['value']); ?>"><?php echo esc_html($info_item['value']); ?></span>
                                <button type="button" class="friend-links-myinfo-copy" data-copy-value="<?php echo esc_attr($info_item['value']); ?>" aria-label="<?php esc_attr_e('复制', 'lared'); ?>">
                                    <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <section class="friend-links-notice">
                    <div class="friend-links-notice-head">
                        <h2 class="friend-links-notice-title">
                            <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>
                            <?php esc_html_e('友链须知', 'lared'); ?>
                        </h2>
                    </div>
                    <p class="friend-links-notice-hint"><?php esc_html_e('申请前请确认满足以下基本要求', 'lared'); ?></p>
                    <ul class="friend-links-notice-list">
                        <li class="friend-links-notice-item"><span class="friend-links-notice-num">1</span><span class="friend-links-notice-text"><?php esc_html_e('优先收录有独立域名、原创内容的博客站点', 'lared'); ?></span></li>
                        <li class="friend-links-notice-item"><span class="friend-links-notice-num">2</span><span class="friend-links-notice-text"><?php esc_html_e('站点需已运行 3 个月以上且内容持续更新', 'lared'); ?></span></li>
                        <li class="friend-links-notice-item"><span class="friend-links-notice-num">3</span><span class="friend-links-notice-text"><?php esc_html_e('请先将本站添加至贵站友链后再提交申请', 'lared'); ?></span></li>
                        <li class="friend-links-notice-item"><span class="friend-links-notice-num">4</span><span class="friend-links-notice-text"><?php esc_html_e('站点不含违法违规、低俗或恶意软件内容', 'lared'); ?></span></li>
                        <li class="friend-links-notice-item"><span class="friend-links-notice-num">5</span><span class="friend-links-notice-text"><?php esc_html_e('长期无法访问或停止更新的站点将被移除', 'lared'); ?></span></li>
                    </ul>
                </section>
                </div>
            </section>

            <!-- 申请友链模态窗 -->
            <div class="flink-apply-modal" id="friendLinkModal">
                <div class="flink-apply-modal-inner">
                    <div class="flink-apply-modal-head">
                        <h3 class="flink-apply-modal-title">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            <?php esc_html_e('申请友链', 'lared'); ?>
                        </h3>
                        <button type="button" class="flink-apply-modal-close" id="friendLinkModalClose" aria-label="<?php esc_attr_e('关闭', 'lared'); ?>">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                    <form id="friendLinkApplyForm" class="flink-apply-form">
                        <div class="flink-apply-field">
                            <label class="flink-apply-label" for="flink_name"><?php esc_html_e('站点名称', 'lared'); ?> <span class="flink-required">*</span></label>
                            <input type="text" id="flink_name" name="flink_name" class="flink-apply-input" required placeholder="<?php esc_attr_e('你的站点名称', 'lared'); ?>" />
                        </div>
                        <div class="flink-apply-field">
                            <label class="flink-apply-label" for="flink_url"><?php esc_html_e('网址', 'lared'); ?> <span class="flink-required">*</span></label>
                            <input type="url" id="flink_url" name="flink_url" class="flink-apply-input" required placeholder="https://" />
                        </div>
                        <div class="flink-apply-field">
                            <label class="flink-apply-label" for="flink_desc"><?php esc_html_e('站点描述', 'lared'); ?></label>
                            <input type="text" id="flink_desc" name="flink_desc" class="flink-apply-input" placeholder="<?php esc_attr_e('一句话描述', 'lared'); ?>" />
                        </div>
                        <div class="flink-apply-field">
                            <label class="flink-apply-label" for="flink_feed"><?php esc_html_e('Feed 地址', 'lared'); ?></label>
                            <input type="url" id="flink_feed" name="flink_feed" class="flink-apply-input" placeholder="https://example.com/feed" />
                        </div>
                        <div class="flink-apply-field">
                            <label class="flink-apply-label" for="flink_avatar"><?php esc_html_e('头像地址', 'lared'); ?></label>
                            <input type="url" id="flink_avatar" name="flink_avatar" class="flink-apply-input" placeholder="https://example.com/avatar.png" />
                        </div>
                        <div id="friendLinkMsg" class="flink-apply-msg"></div>
                        <button type="submit" class="flink-apply-submit"><?php esc_html_e('提交申请', 'lared'); ?></button>
                    </form>
                </div>
            </div>

            <script>
            (function(){
                // ── 复制按钮 ──
                document.querySelectorAll('.friend-links-myinfo-copy').forEach(function(btn){
                    btn.addEventListener('click', function(e){
                        e.preventDefault();
                        var val = this.getAttribute('data-copy-value');
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(val).then(function(){ showCopied(btn); });
                        } else {
                            var ta = document.createElement('textarea');
                            ta.value = val;
                            ta.style.cssText = 'position:fixed;left:-9999px';
                            document.body.appendChild(ta);
                            ta.select();
                            document.execCommand('copy');
                            document.body.removeChild(ta);
                            showCopied(btn);
                        }
                    });
                });
                function showCopied(btn){
                    var icon = btn.querySelector('i');
                    if(icon){
                        icon.className = 'fa-solid fa-check';
                        setTimeout(function(){ icon.className = 'fa-regular fa-copy'; }, 1500);
                    }
                }

                // ── 申请友链模态窗 ──
                var modal = document.getElementById('friendLinkModal');
                var openBtn = document.getElementById('friendLinkApplyBtn');
                var closeBtn = document.getElementById('friendLinkModalClose');
                var form = document.getElementById('friendLinkApplyForm');
                var msgEl = document.getElementById('friendLinkMsg');

                if (openBtn && modal) {
                    openBtn.addEventListener('click', function(){ modal.classList.add('is-open'); });
                    closeBtn.addEventListener('click', function(){ modal.classList.remove('is-open'); });
                    modal.addEventListener('click', function(e){ if (e.target === modal) modal.classList.remove('is-open'); });
                }

                if (form) {
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        var submitBtn = form.querySelector('.flink-apply-submit');
                        submitBtn.disabled = true;
                        submitBtn.textContent = '<?php echo esc_js(__('提交中…', 'lared')); ?>';
                        msgEl.textContent = '';
                        msgEl.className = 'flink-apply-msg';

                        var fd = new FormData(form);
                        fd.append('action', 'lared_apply_friend_link');
                        fd.append('nonce', (typeof LaredAjax !== 'undefined' && LaredAjax.friendLinkNonce) || '');

                        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                            method: 'POST',
                            body: fd
                        })
                        .then(function(r){ return r.json(); })
                        .then(function(res){
                            if (res.success) {
                                msgEl.textContent = res.data.message || '<?php echo esc_js(__('提交成功，等待审核', 'lared')); ?>';
                                msgEl.className = 'flink-apply-msg flink-apply-msg-ok';
                                form.reset();
                            } else {
                                msgEl.textContent = res.data.message || '<?php echo esc_js(__('提交失败', 'lared')); ?>';
                                msgEl.className = 'flink-apply-msg flink-apply-msg-err';
                            }
                        })
                        .catch(function(){
                            msgEl.textContent = '<?php echo esc_js(__('网络错误，请稍后重试', 'lared')); ?>';
                            msgEl.className = 'flink-apply-msg flink-apply-msg-err';
                        })
                        .finally(function(){
                            submitBtn.disabled = false;
                            submitBtn.textContent = '<?php echo esc_js(__('提交申请', 'lared')); ?>';
                        });
                    });
                }
            })();
            </script>
        </main>

        <?php
    endwhile;
endif;

get_footer();
