# Lared

一款为写作者打造的极简 WordPress 主题，基于 Tailwind CSS，专注内容本身。

**作者:** [西风](https://xifeng.net) | **主题主页:** [xifeng.net](https://xifeng.net/wordpress-lared-theme.html) | **许可:** GPL v2+

## 环境要求

| 依赖 | 版本 |
|------|------|
| WordPress | 6.0+（测试至 6.8） |
| PHP | 8.0+（推荐 8.1+） |

## 快速开始

1. 将主题目录放到 `wp-content/themes/Lared`
2. 后台启用主题
3. 打开 **外观 → Lared 设置** 完成基础配置

## 主题特色

### 写作体验

- **双模式编辑器工具** — 插图和排版指南同时兼容可视化模式和代码模式
- **短代码工具箱** — `[download_button]` 下载卡片 · `[code_runner]` 实时沙盒 · `[red_code]` 高亮行内代码
- **文章目录** — 自动生成、滚动高亮、平滑跳转，无需额外插件
- **图片网格** — 2/3/4 多栏图片布局，hover 放大、灯箱查看

### 评论系统

- **AJAX 无刷新提交** — 评论即时出现，无需等待页面重载
- **60 秒编辑窗口** — 提交后发现错别字？60 秒内可以修改
- **回头访客识别** — Cookie 记忆 + 头像检测，老朋友回来有欢迎语
- **管理员徽章** — 博主评论自动标记皇冠图标 + "博主" 标识
- **UA 解析** — 操作系统 + 浏览器版本自动解析，支持 Client Hints

### 邮件通知

- **双通道发送** — SMTP 标准协议 / Resend API 现代 HTTP 发送
- **三类通知模板** — 管理员通知（新评论 / 待审核）、回复通知、测试邮件
- **模板风格** — 深色 Header + 红色强调线 + 管理员头像，直角设计与主题一体
- **后台预览** — 4 种模板类型实时预览，所见即所得

### AI 文章摘要

- **多服务商支持** — OpenAI / DeepSeek / Kimi / MiniMax
- **品牌图标** — 右上角自动展示对应服务商品牌图标
- **自动生成** — 每篇文章自动生成 AI 摘要卡片

### 图片处理

- **WebP 自动转换** — Imagick 驱动，上传时自动生成 WebP 格式
- **云存储集成** — Cloudflare R2 + Lsky Pro 图床，一键配置
- **懒加载** — lazysizes v5.3.2，IntersectionObserver + MutationObserver 双驱动
- **加载动画** — 7 种效果：淡入 / 像素化 / 模糊 / 扩散 / 百叶窗 / 滑入 / 旋转缩放
- **灯箱查看** — 点击图片大图预览，轻量级实现
- **相册页面** — 独立模板，支持前端多图上传至 R2，方形卡片网格布局

### 音乐播放器

- **首页内联播放器** — 可视化频谱柱 + 居中歌曲名 + 进度条悬停时间提示
- **悬浮播放器** — 固定在页面左上方，歌曲名 + 控制按钮 + 进度条
- **歌词面板** — 侧边歌词同步滚动，仅内页显示，PJAX 自动切换
- **Xplayer 插件** — 胶囊型设计，深蓝面板 + 右键菜单 + 网易云/QQ 音乐数据源

### 性能与导航

- **PJAX 导航** — 无刷新页面切换，丝滑体验
- **零网络字体** — 系统原生字体栈，零额外请求
- **智能缓存** — CSS/JS 基于 `filemtime()` 自动版本号 + Transient 缓存
- **代码高亮** — Prism.js 驱动 · 自动语言检测 / 行号 / 复制 / 折叠

### 更多功能

- **RSS 订阅聚合** — 多源管理 + 一键刷新 + 单源失败自动跳过 + 本地缓存
- **Memos 动态** — 接入 Memos API：短动态展示 + 关键词提取

## 页面模板

| 模板 | 说明 |
|------|------|
| 首页 | Hero 区域 + 热力图 + 热门文章 + 最新评论 + 标签云 |
| 文章页 | 目录导航 + AI 摘要 + 评论区 + 版权信息（CC BY-NC-SA 4.0） |
| 归档页 | 时间线归档 + 年月分组 + 文章统计 |
| RSS 订阅 | 多源聚合阅读器 + 一键刷新 |
| Memos 动态 | 短内容展示 + 关键词提取 |
| 相册 | 图片网格 + 前端上传（R2 存储） |
| 友链 | 卡片 / 文字双样式 + 申请友链表单 |
| 关于 | 个人介绍 + 十年博客进度 |

## 技术栈

Tailwind CSS v4 · Barba.js · Prism.js · lazysizes · Plyr · Font Awesome · PHP 8.0+ · WordPress 6.0+

## 文档

- [完整文档](docs/README.md)
- [更新记录](docs/CHANGELOG.md)
- [快速上手](docs/QUICKSTART.md)

## 致谢

[Tailwind CSS](https://tailwindcss.com/) · [Barba.js](https://barba.js.org/) · [PrismJS](https://prismjs.com/) · [ViewImage](https://github.com/Tokinx/ViewImage) · [lazysizes](https://github.com/aFarkas/lazysizes) · [Plyr](https://plyr.io/) · [Font Awesome](https://fontawesome.com/)
