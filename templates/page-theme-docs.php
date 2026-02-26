<?php
/*
Template Name: 主题说明
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        // 主题信息
        $theme        = wp_get_theme();
        $theme_ver    = '1.1.0';
        $theme_name   = 'Lared';
        $theme_author = '西风';
        $theme_url    = 'https://xifeng.net/wordpress-lared-theme.html';
        $author_url   = 'https://xifeng.net';

        // 功能分类数据
        $feature_groups = [
            [
                'icon'  => 'fa-sharp fa-thin fa-pen-nib',
                'title' => '写作体验',
                'items' => [
                    ['label' => '双模式编辑器工具', 'desc' => '插图和排版指南同时兼容「可视化模式」和「代码模式」'],
                    ['label' => '短代码工具箱', 'desc' => '[download_button] 下载卡片 · [code_runner] 实时沙盒 · [red_code] 高亮行内代码'],
                    ['label' => '文章目录', 'desc' => '自动生成、滚动高亮、平滑跳转，无需额外插件'],
                    ['label' => '图片网格', 'desc' => 'lared-grid-2/3/4 多栏图片布局，hover 放大、灯箱查看'],
                ],
            ],
            [
                'icon'  => 'fa-sharp fa-thin fa-comments',
                'title' => '评论系统',
                'items' => [
                    ['label' => 'AJAX 无刷新提交', 'desc' => '评论即时出现，无需等待页面重载'],
                    ['label' => '60 秒编辑窗口', 'desc' => '提交后发现错别字？60 秒内可以修改'],
                    ['label' => '表情面板', 'desc' => 'Bilibili 表情 + Emoji 面板一键插入'],
                    ['label' => '回头访客识别', 'desc' => 'Cookie 记忆 + 头像检测，老朋友回来有欢迎语'],
                    ['label' => '管理员徽章', 'desc' => '博主评论自动标记皇冠图标 + "博主" 标识'],
                    ['label' => 'UA 解析', 'desc' => '操作系统 + 浏览器版本自动解析，支持 Client Hints'],
                ],
            ],
            [
                'icon'  => 'fa-sharp fa-thin fa-envelope',
                'title' => '邮件通知',
                'items' => [
                    ['label' => '双通道发送', 'desc' => 'SMTP 标准协议 / Resend API 现代 HTTP 发送'],
                    ['label' => '三类通知模板', 'desc' => '管理员通知（新评论 / 待审核）、回复通知、测试邮件'],
                    ['label' => '模板风格', 'desc' => '深色 Header + 红色强调线 + 管理员头像，直角设计与主题一体'],
                    ['label' => '后台预览', 'desc' => '4 种模板类型实时预览，所见即所得'],
                ],
            ],
            [
                'icon'  => 'fa-sharp fa-thin fa-robot',
                'title' => 'AI 文章摘要',
                'items' => [
                    ['label' => '多服务商支持', 'desc' => 'OpenAI / DeepSeek / Kimi / MiniMax 四家服务商'],
                    ['label' => '品牌图标', 'desc' => '右上角自动展示对应服务商品牌图标'],
                    ['label' => '自动生成', 'desc' => '每篇文章自动生成 AI 摘要卡片'],
                ],
            ],
            [
                'icon'  => 'fa-sharp fa-thin fa-image',
                'title' => '图片处理',
                'items' => [
                    ['label' => 'WebP 自动转换', 'desc' => 'Imagick 驱动，上传时自动生成 WebP 格式'],
                    ['label' => '云存储集成', 'desc' => 'Cloudflare R2 + Lsky Pro 图床，一键配置'],
                    ['label' => '懒加载', 'desc' => 'lazysizes v5.3.2，IntersectionObserver + MutationObserver 双驱动'],
                    ['label' => '加载动画', 'desc' => '7 种效果任选：淡入 / 像素化 / 模糊 / 扩散 / 百叶窗 / 滑入 / 旋转缩放'],
                    ['label' => '灯箱查看', 'desc' => '点击图片大图预览，轻量级实现'],
                    ['label' => '相册页面', 'desc' => '独立模板，支持前端多图上传至 R2，方形卡片网格布局'],
                ],
            ],
            [
                'icon'  => 'fa-sharp fa-thin fa-music',
                'title' => '音乐播放器',
                'items' => [
                    ['label' => '首页内联播放器', 'desc' => '可视化频谱柱 + 居中歌曲名 + 进度条悬停时间提示'],
                    ['label' => '悬浮播放器', 'desc' => '固定在页面左上方，歌曲名 + 控制按钮 + 进度条'],
                    ['label' => '歌词面板', 'desc' => '侧边歌词同步滚动，仅内页显示，PJAX 自动切换'],
                    ['label' => '进度条交互', 'desc' => '点击跳转 + 拖拽 + 悬停时间 tooltip'],
                    ['label' => 'Xplayer 插件', 'desc' => '胶囊型设计，深蓝面板 + 右键菜单 + 网易云/QQ 音乐数据源'],
                ],
            ],
            [
                'icon'  => 'fa-sharp fa-thin fa-bolt',
                'title' => '性能与更多',
                'items' => [
                    ['label' => 'PJAX 导航', 'desc' => 'Barba.js 无刷新页面切换，丝滑体验'],
                    ['label' => '零网络字体', 'desc' => '系统原生字体栈，零额外请求'],
                    ['label' => '智能缓存', 'desc' => 'CSS/JS 基于 filemtime() 自动版本号 + Transient 缓存'],
                    ['label' => 'RSS 订阅聚合', 'desc' => '多源管理 + 一键刷新 + 单源失败自动跳过 + 本地缓存'],
                    ['label' => 'Memos 动态', 'desc' => '接入 Memos API：短动态展示 + 关键词提取'],
                    ['label' => '代码高亮', 'desc' => 'Prism.js 驱动 · 自动语言检测 / 行号 / 复制 / 折叠'],
                ],
            ],
        ];

        // 更新记录数据
        $changelogs = [
            [
                'version' => '1.1.0',
                'date'    => '2026-02-26',
                'changes' => [
                    'new'     => [
                        '悬浮 + 首页播放器进度条悬停时间提示（tooltip 跟随鼠标）',
                        '歌词面板限制为内页显示，PJAX 返回首页时自动隐藏',
                        '邮件调试：wp_mail_failed 钩子记录失败详情至 debug.log',
                        '友链 text 样式字母兜底头像（favicon 加载失败时显示站名首字母）',
                        '友链头像支持 lazysizes 懒加载与页面图片动画效果',
                    ],
                    'improve' => [
                        '悬浮播放器：进度条容器高度修复（1px→14px）、圆点归位、红色填充覆盖底线',
                        '悬浮播放器：歌曲名与按钮间距增加、按钮阴影/轮廓全部移除',
                        '首页播放器：可视化柱高度增大（10-18px→14-26px）、时间显示隐藏、歌曲名居中',
                        '首页播放器：播放时隐藏进度条避免与可视化柱重叠，hover 时恢复',
                        '评论 UA 信息：操作系统与浏览器版本号改为行内显示',
                        '友情链接 text 样式重构：2 列→4 列布局、32×32 favicon 图标（圆角 4px）',
                        '友链头像优先使用自定义图片，无设定时 ico.bluecdn.com 获取 favicon',
                        'Favicon API 从 Google 迁移至自托管 ico.bluecdn.com（card 样式同步迁移）',
                    ],
                    'remove'  => [],
                ],
            ],
            [
                'version' => '1.0.9',
                'date'    => '2026-02-25',
                'changes' => [
                    'new'     => [
                        '邮件模板系统全面重构 — 直角风格 + 主题配色',
                        '评论通知 Hook 系统，新评论/回复自动通知',
                        '邮件模板预览支持 4 种类型切换',
                        '文章页脚版权链接前添加 CC 图标',
                    ],
                    'improve' => [
                        '邮件模板全面使用主题色 + 统一字体栈',
                        '所有邮件模板采用直角设计 — 零 border-radius',
                    ],
                    'remove'  => [
                        '禁用 WordPress 默认评论通知（由主题自定义通知替代）',
                        '移除旧版紫色渐变邮件模板',
                    ],
                ],
            ],
            [
                'version' => '1.0.8',
                'date'    => '2026-02-25',
                'changes' => [
                    'new'     => [
                        '后台新增「邮件设置」Tab — SMTP / Resend API 双模式切换',
                        '统一发件人配置：发件人名称 + 发件人邮箱',
                        '邮件测试发送功能 + 实时发送结果反馈',
                        '邮件 HTML 模板系统（响应式 600px 宽度）',
                        '邮件模板预览 + 全屏查看功能',
                    ],
                    'improve' => [],
                    'remove'  => [],
                ],
            ],
            [
                'version' => '1.0.7',
                'date'    => '2026-02-25',
                'changes' => [
                    'new'     => [
                        'AI 摘要卡片显示服务商图标（OpenAI / DeepSeek / Kimi / MiniMax）',
                    ],
                    'improve' => [
                        'AI 摘要卡片紧贴文章顶部',
                        '评论提交按钮加载态优化',
                        'RSS 订阅按钮 tooltip 改为自定义主题色弹窗',
                        '评论区所有 tooltip 统一为自定义样式',
                    ],
                    'fix'     => [
                        '修复 macOS 版本号冻结在 10.15.7 — 实现 User-Agent Client Hints',
                    ],
                    'remove'  => [
                        '移除 IP 地理位置 JSON 缓存，改为纯数据库 + 内存级静态缓存',
                    ],
                ],
            ],
            [
                'version' => '1.0.6',
                'date'    => '2026-02-25',
                'changes' => [
                    'new'     => [
                        'RSS 拉取跳过机制：失败源标记 "（已跳过）" 并继续拉取',
                    ],
                    'improve' => [
                        '字体系统重构：系统原生字体替代 CDN 网络字体',
                    ],
                    'fix'     => [
                        '修复 Xplayer API 模板 URL 无法保存的问题',
                        '修复 RSS 订阅「一键刷新」报错 "获取源列表失败"',
                    ],
                    'remove'  => [
                        '完全移除暗黑模式 — 1054 行 CSS + JS',
                        '移除 CDN 字体加载',
                    ],
                ],
            ],
            [
                'version' => '1.0.5',
                'date'    => '2026-02-24',
                'changes' => [
                    'new'     => [
                        'Xplayer 插件 UI 全面重构为胶囊型设计',
                        'Xplayer 右键菜单：播放控制 / 模式切换 / 歌曲列表',
                        'Xplayer 可部署 Meting API（网易云 / QQ 音乐）',
                    ],
                    'improve' => [
                        '播放列表改为弹出式面板，控制区仅保留核心按钮',
                    ],
                    'remove'  => [],
                ],
            ],
            [
                'version' => '1.0.4',
                'date'    => '2026-02-24',
                'changes' => [
                    'new'     => [
                        '引入 Plyr v3.7.8 作为文章内 video / audio 播放器',
                        '全新 Xplayer WordPress 插件 — 毛玻璃面板 + 封面旋转',
                    ],
                    'remove'  => [
                        '从主题中完全移除 APlayer v1.10.1（~430 行）',
                        '音乐功能全部迁移到 Xplayer 插件',
                    ],
                ],
            ],
            [
                'version' => '1.0.3',
                'date'    => '2026-02-24',
                'changes' => [
                    'new'     => [
                        'RSS 管理页添加「刷新全部订阅」按钮',
                        '相册页面模板 — 前端多图上传至 R2 + 方形卡片网格',
                        '图片加载动画扩展至 7 种效果',
                        'lazysizes v5.3.2 懒加载取代原生 loading="lazy"',
                    ],
                    'improve' => [
                        'RSS / Memos 缓存 JSON 统一迁移至 data/',                        
                        'initImageLoadAnimation() 简化为仅设置 data-img-animation 属性',
                    ],
                    'remove'  => [
                        '删除未使用的 lazyImage.js / lazyVideo.js',
                    ],
                ],
            ],
            [
                'version' => '1.0.2',
                'date'    => '2026-02-24',
                'changes' => [
                    'new'     => [
                        '下载按钮短代码 [download_button] PHP 实现',
                        '文章内链接自动添加 link icon',
                        '评论系统全面增强 — 回复拦截 / 60 秒编辑 / 表情面板 / 回头访客',
                    ],
                    'fix'     => [
                        '修复 PJAX 拦截评论回复链接导致页面刷新',
                        '修复 addComment.init() 未重新初始化',
                        '修复 Cookie 保存 + CSS/JS 版本号缓存',
                    ],
                    'improve' => [
                        '提交按钮 loading 动画增强',
                        'Toast 改为直角小字体，移除 emoji',
                    ],
                ],
            ],
            [
                'version' => '1.0.0',
                'date'    => '2026-02-23',
                'changes' => [
                    'new'     => [
                        '主题基础框架 — WordPress 6.0+ / PHP 8.0+',
                        '响应式设计，适配移动端和桌面端',
                        'PJAX 无刷新页面加载 · Memos 动态集成 · RSS 订阅聚合',
                        'PrismJS 代码高亮 · ViewImage 灯箱 · 图片懒加载 + 动画',
                        '短代码工具箱 · AJAX 评论 · 文章目录导航',
                        '首页 Hero 区域 + 60 天热力图 + 热门文章 + 标签云',
                    ],
                    'improve' => [],
                    'remove'  => [],
                ],
            ],
        ];

        // 技术栈
        $tech_stack = [
            ['name' => 'Tailwind CSS v4', 'desc' => '样式系统', 'icon' => 'fa-sharp fa-thin fa-palette'],
            ['name' => 'Barba.js',        'desc' => 'PJAX 无刷新导航', 'icon' => 'fa-sharp fa-thin fa-arrows-rotate'],
            ['name' => 'Prism.js',        'desc' => '代码高亮', 'icon' => 'fa-sharp fa-thin fa-code'],
            ['name' => 'lazysizes',       'desc' => '图片懒加载', 'icon' => 'fa-sharp fa-thin fa-spinner'],
            ['name' => 'Plyr',            'desc' => '音视频播放器', 'icon' => 'fa-sharp fa-thin fa-play'],
            ['name' => 'Font Awesome',    'desc' => '图标库', 'icon' => 'fa-sharp fa-thin fa-icons'],
            ['name' => 'PHP 8.0+',        'desc' => '后端语言', 'icon' => 'fa-brands fa-php'],
            ['name' => 'WordPress 6.0+',  'desc' => 'CMS 基础', 'icon' => 'fa-brands fa-wordpress'],
        ];

        // 页面模板列表
        $page_templates = [
            ['name' => '首页',       'desc' => 'Hero 区域 + 热力图 + 热门文章 + 最新评论 + 标签云'],
            ['name' => '文章页',     'desc' => '目录导航 + AI 摘要 + 评论区 + 版权信息（CC BY-NC-SA 4.0）'],
            ['name' => '归档页',     'desc' => '时间线归档 + 年月分组 + 文章统计'],
            ['name' => 'RSS 订阅',   'desc' => '多源聚合阅读器 + 一键刷新'],
            ['name' => 'Memos 动态', 'desc' => '短内容展示 + 关键词提取'],
            ['name' => '相册',       'desc' => '图片网格 + 前端上传（R2 存储）'],
            ['name' => '友链',       'desc' => '卡片 / 文字双样式 + 申请友链表单'],
            ['name' => '关于',       'desc' => '个人介绍 + 十年博客进度'],
        ];

        // 更新类型标签配置
        $change_type_labels = [
            'new'     => ['label' => '新增', 'color' => '#f53004', 'bg' => 'rgba(245,48,4,0.08)'],
            'improve' => ['label' => '改进', 'color' => '#2563eb', 'bg' => 'rgba(37,99,235,0.08)'],
            'fix'     => ['label' => '修复', 'color' => '#16a34a', 'bg' => 'rgba(22,163,74,0.08)'],
            'remove'  => ['label' => '移除', 'color' => '#9333ea', 'bg' => 'rgba(147,51,234,0.08)'],
        ];
        ?>

        <main class="main-shell mx-auto w-full max-w-[1280px] min-h-[calc(100vh-64px)] border-x border-[#d9d9d9] bg-[#fff] pb-[90px] max-[900px]:pb-16">

            <!-- 页面标题区域 -->
            <section class="listing-head border-b border-[#d9d9d9]">
                <div class="listing-head-inner">
                    <span class="listing-head-accent" aria-hidden="true"></span>
                    <div class="listing-head-main">
                        <div class="listing-head-title-row">
                            <h1 class="listing-head-title">
                                <i class="fa-sharp fa-thin fa-book-open" aria-hidden="true"></i>
                                <?php the_title(); ?>
                                <span class="theme-docs-head-subtitle">— <?php esc_html_e('基于 Tailwind CSS，专注内容本身', 'lared'); ?></span>
                            </h1>
                            <div class="theme-docs-head-stats" aria-label="<?php esc_attr_e('主题信息', 'lared'); ?>">
                                <span class="theme-docs-head-stat">
                                    <em><?php esc_html_e('主题', 'lared'); ?></em>
                                    <b><?php echo esc_html($theme_name); ?></b>
                                </span>
                                <span class="theme-docs-head-stat">
                                    <em><?php esc_html_e('版本', 'lared'); ?></em>
                                    <b>v<?php echo esc_html($theme_ver); ?></b>
                                </span>
                                <span class="theme-docs-head-stat">
                                    <em><?php esc_html_e('作者', 'lared'); ?></em>
                                    <b><a href="<?php echo esc_url($author_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($theme_author); ?></a></b>
                                </span>
                                <span class="theme-docs-head-stat">
                                    <em><?php esc_html_e('许可', 'lared'); ?></em>
                                    <b>GPL v2+</b>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tab 切换导航 -->
            <section class="theme-docs-tabs">
                <div class="theme-docs-tabs-inner">
                    <button class="theme-docs-tab is-active" data-tab="features" type="button">
                        <i class="fa-sharp fa-thin fa-star" aria-hidden="true"></i>
                        <span><?php esc_html_e('主题特色', 'lared'); ?></span>
                    </button>
                    <button class="theme-docs-tab" data-tab="changelog" type="button">
                        <i class="fa-sharp fa-thin fa-clock-rotate-left" aria-hidden="true"></i>
                        <span><?php esc_html_e('更新记录', 'lared'); ?></span>
                    </button>
                    <button class="theme-docs-tab" data-tab="techstack" type="button">
                        <i class="fa-sharp fa-thin fa-layer-group" aria-hidden="true"></i>
                        <span><?php esc_html_e('技术栈', 'lared'); ?></span>
                    </button>
                </div>
            </section>

            <!-- ==================== 主题特色 ==================== -->
            <section class="theme-docs-panel is-active" data-panel="features">
                <div class="theme-docs-content mx-auto max-w-[1150px] px-0 py-10 max-[900px]:px-5 max-[900px]:py-8">

                    <!-- 功能分组 -->
                    <?php foreach ($feature_groups as $group_index => $group) : ?>
                        <div class="theme-docs-feature-group<?php echo $group_index > 0 ? ' mt-12' : ''; ?>">
                            <h2 class="theme-docs-section-title">
                                <i class="<?php echo esc_attr($group['icon']); ?>" aria-hidden="true"></i>
                                <?php echo esc_html($group['title']); ?>
                            </h2>
                            <div class="theme-docs-feature-grid">
                                <?php foreach ($group['items'] as $item) : ?>
                                    <div class="theme-docs-feature-card">
                                        <h3 class="theme-docs-feature-card-title"><?php echo esc_html($item['label']); ?></h3>
                                        <p class="theme-docs-feature-card-desc"><?php echo esc_html($item['desc']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- 页面模板一览 -->
                    <div class="theme-docs-feature-group mt-12">
                        <h2 class="theme-docs-section-title">
                            <i class="fa-sharp fa-thin fa-files" aria-hidden="true"></i>
                            <?php esc_html_e('页面模板', 'lared'); ?>
                        </h2>
                        <div class="theme-docs-template-list mt-5">
                            <?php foreach ($page_templates as $tpl) : ?>
                                <div class="theme-docs-template-item">
                                    <span class="theme-docs-template-name"><?php echo esc_html($tpl['name']); ?></span>
                                    <span class="theme-docs-template-desc"><?php echo esc_html($tpl['desc']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ==================== 更新记录 ==================== -->
            <section class="theme-docs-panel" data-panel="changelog">
                <div class="theme-docs-content mx-auto max-w-[1150px] px-0 py-10 max-[900px]:px-5 max-[900px]:py-8">

                    <div class="theme-docs-changelog-timeline">
                        <?php foreach ($changelogs as $cl_index => $cl) : ?>
                            <div class="theme-docs-changelog-entry<?php echo $cl_index === 0 ? ' is-latest' : ''; ?>">
                                <!-- 版本号标题 -->
                                <div class="theme-docs-changelog-header">
                                    <span class="theme-docs-changelog-version">v<?php echo esc_html($cl['version']); ?></span>
                                    <span class="theme-docs-changelog-date"><?php echo esc_html($cl['date']); ?></span>
                                    <?php if ($cl_index === 0) : ?>
                                        <span class="theme-docs-changelog-badge"><?php esc_html_e('最新', 'lared'); ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- 变更内容 -->
                                <div class="theme-docs-changelog-body">
                                    <?php foreach ($cl['changes'] as $type => $items) : ?>
                                        <?php if (empty($items)) continue; ?>
                                        <?php $type_conf = $change_type_labels[$type] ?? null; ?>
                                        <?php if (!$type_conf) continue; ?>
                                        <div class="theme-docs-changelog-section">
                                            <span class="theme-docs-changelog-type" style="color: <?php echo esc_attr($type_conf['color']); ?>; background: <?php echo esc_attr($type_conf['bg']); ?>;">
                                                <?php echo esc_html($type_conf['label']); ?>
                                            </span>
                                            <ul class="theme-docs-changelog-list">
                                                <?php foreach ($items as $item_text) : ?>
                                                    <li><?php echo esc_html($item_text); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </section>

            <!-- ==================== 技术栈 ==================== -->
            <section class="theme-docs-panel" data-panel="techstack">
                <div class="theme-docs-content mx-auto max-w-[1150px] px-0 py-10 max-[900px]:px-5 max-[900px]:py-8">

                    <h2 class="theme-docs-section-title">
                        <i class="fa-sharp fa-thin fa-microchip" aria-hidden="true"></i>
                        <?php esc_html_e('技术栈', 'lared'); ?>
                    </h2>

                    <div class="theme-docs-tech-grid">
                        <?php foreach ($tech_stack as $tech) : ?>
                            <div class="theme-docs-tech-card">
                                <div class="theme-docs-tech-icon">
                                    <i class="<?php echo esc_attr($tech['icon']); ?>" aria-hidden="true"></i>
                                </div>
                                <div class="theme-docs-tech-info">
                                    <h3 class="theme-docs-tech-name"><?php echo esc_html($tech['name']); ?></h3>
                                    <p class="theme-docs-tech-desc"><?php echo esc_html($tech['desc']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- 环境要求 -->
                    <div class="theme-docs-feature-group mt-12">
                        <h2 class="theme-docs-section-title">
                            <i class="fa-sharp fa-thin fa-server" aria-hidden="true"></i>
                            <?php esc_html_e('环境要求', 'lared'); ?>
                        </h2>
                        <div class="theme-docs-template-list">
                            <div class="theme-docs-template-item">
                                <span class="theme-docs-template-name">WordPress</span>
                                <span class="theme-docs-template-desc"><?php esc_html_e('6.0 或更高（测试至 6.8）', 'lared'); ?></span>
                            </div>
                            <div class="theme-docs-template-item">
                                <span class="theme-docs-template-name">PHP</span>
                                <span class="theme-docs-template-desc"><?php esc_html_e('8.0 或更高（推荐 8.1+）', 'lared'); ?></span>
                            </div>
                            <div class="theme-docs-template-item">
                                <span class="theme-docs-template-name"><?php esc_html_e('推荐', 'lared'); ?></span>
                                <span class="theme-docs-template-desc"><?php esc_html_e('启用 HTTPS、启用 Imagick 扩展', 'lared'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- 致谢 -->
                    <div class="theme-docs-feature-group mt-12">
                        <h2 class="theme-docs-section-title">
                            <i class="fa-sharp fa-thin fa-heart" aria-hidden="true"></i>
                            <?php esc_html_e('致谢', 'lared'); ?>
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed text-[#63635e]">
                            <a href="https://tailwindcss.com/" target="_blank" rel="noopener">Tailwind CSS</a> ·
                            <a href="https://barba.js.org/" target="_blank" rel="noopener">Barba.js</a> ·
                            <a href="https://prismjs.com/" target="_blank" rel="noopener">PrismJS</a> ·
                            <a href="https://github.com/Tokinx/ViewImage" target="_blank" rel="noopener">ViewImage</a> ·
                            <a href="https://github.com/aFarkas/lazysizes" target="_blank" rel="noopener">lazysizes</a> ·
                            <a href="https://plyr.io/" target="_blank" rel="noopener">Plyr</a> ·
                            <a href="https://fontawesome.com/" target="_blank" rel="noopener">Font Awesome</a>
                        </p>
                        <p class="mt-3 text-sm text-[#999]">
                            <?php echo esc_html($theme_name); ?> <?php esc_html_e('由', 'lared'); ?> <a href="<?php echo esc_url($author_url); ?>" target="_blank" rel="noopener" class="text-[#f53004] hover:underline"><?php echo esc_html($theme_author); ?></a> <?php esc_html_e('独立设计开发。', 'lared'); ?>
                        </p>
                    </div>

                </div>
            </section>

        </main>

        <!-- Tab 切换脚本 -->
        <script>
        (function() {
            function initThemeDocsTabs() {
                var tabs = document.querySelectorAll('.theme-docs-tab');
                var panels = document.querySelectorAll('.theme-docs-panel');
                if (!tabs.length || !panels.length) return;

                tabs.forEach(function(tab) {
                    tab.addEventListener('click', function() {
                        var target = this.getAttribute('data-tab');
                        tabs.forEach(function(t) { t.classList.remove('is-active'); });
                        panels.forEach(function(p) { p.classList.remove('is-active'); });
                        this.classList.add('is-active');
                        var targetPanel = document.querySelector('.theme-docs-panel[data-panel="' + target + '"]');
                        if (targetPanel) targetPanel.classList.add('is-active');
                    });
                });
            }

            // 初始化
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initThemeDocsTabs);
            } else {
                initThemeDocsTabs();
            }

            // PJAX 兼容
            document.addEventListener('pjax:complete', initThemeDocsTabs);
        })();
        </script>

    <?php endwhile;
endif;

get_footer();
