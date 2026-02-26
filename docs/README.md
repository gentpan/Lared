# Lared — 一款为写作者打造的极简 WordPress 主题

> 基于 Tailwind CSS，专注内容本身。不堆砌功能，但每个细节都经得起推敲。

**版本**: 1.0.9 · **作者**: [西风](https://xifeng.net) · **主题主页**: [xifeng.net/wordpress-lared-theme.html](https://xifeng.net/wordpress-lared-theme.html)

---

## 最近更新

- IP 地理信息接口已从 `ip-api.com` 迁移到 `https://ip.bluecdn.com/geoip/{ip}`。
- 修复后台「在内页显示浮动播放器和歌词」开关：取消勾选后可正确保存为关闭状态并在前端立即生效。

---

## 为什么做 Lared

市面上的 WordPress 主题要么过度设计，塞满用不到的功能；要么过于简陋，基本功能都要自己造轮子。

**Lared** 想做一个平衡点：视觉上克制、功能上完整、代码上现代。一个写博客的人真正需要的主题。

名字来自西班牙语 *La Red*（网络），也可以读作"拉红"——主题的标志色就是那抹 `#f53004` 的红。

---

## 核心设计理念

### 直角 · 克制 · 内容优先

没有圆角卡片，没有渐变背景，没有阴影堆叠。Lared 用直角线条和大量留白构建阅读空间，让文字成为页面的主角。

- **配色**：红色强调（#f53004）+ 深色标题（#21201d）+ 柔和正文（#63635e）
- **字体**：系统原生字体栈，macOS 苹方、Windows 微软雅黑、Linux Noto Sans SC，零网络字体请求
- **排版**：Tailwind CSS v4 驱动，响应式适配从手机到宽屏

---

## 主题特色

### 📝 写作体验

- **双模式编辑器工具** — 插图和排版指南同时兼容「可视化模式」和「代码模式」
- **短代码工具箱** — `[download_button]` 下载卡片、`[code_runner]` 实时沙盒预览、`[red_code]` 高亮行内代码
- **文章目录** — 自动生成、滚动高亮、平滑跳转，无需额外插件
- **图片网格** — `lared-grid-2/3/4` 多栏图片布局，hover 放大、灯箱查看

### 💬 评论系统

| 功能 | 说明 |
|------|------|
| AJAX 无刷新提交 | 评论即时出现，无需等待页面重载 |
| 60 秒编辑窗口 | 提交后发现错别字？60 秒内可以修改 |
| 表情面板 | Bilibili 表情 + Emoji 面板一键插入 |
| 回头访客识别 | Cookie 记忆 + 头像检测，老朋友回来有欢迎语 |
| 管理员徽章 | 博主评论自动标记皇冠图标 + "博主" 标识 |
| UA 解析 | 操作系统 + 浏览器版本自动解析，支持 Client Hints 获取真实 macOS 版本 |
| 自定义 Tooltip | 所有交互提示统一主题色弹窗，替代原生 `title` 属性 |

### 📧 邮件通知

内置完整的评论邮件通知系统，不需要第三方插件。

- **双通道发送**：SMTP 标准邮件协议 / Resend API 现代 HTTP 发送
- **三类通知模板**：管理员通知（新评论 / 待审核）、回复通知、测试邮件
- **模板风格**：深色 Header + 红色强调线 + 管理员头像 + 站点名称，直角设计，与主题浑然一体
- **后台预览**：4 种模板类型实时预览，所见即所得

### 🤖 AI 文章摘要

每篇文章自动生成 AI 摘要卡片，支持 OpenAI / DeepSeek / Kimi / MiniMax 四家服务商，右上角自动展示对应品牌图标。

### 🖼️ 图片处理

从上传到展示，全链路优化：

- **WebP 自动转换** — Imagick 驱动，上传时自动生成 WebP 格式
- **云存储集成** — Cloudflare R2 + Lsky Pro 图床，一键配置
- **懒加载** — lazysizes v5.3.2，IntersectionObserver + MutationObserver 双驱动
- **加载动画** — 7 种效果任选：淡入 / 像素化 / 模糊 / 扩散 / 百叶窗 / 滑入 / 旋转缩放
- **占位预留** — 图片加载前保持正确宽高比，零布局偏移
- **灯箱查看** — 点击图片大图预览，轻量级实现
- **相册页面** — 独立模板，支持前端多图上传至 R2，方形卡片网格布局

### 🎵 Xplayer 音乐播放器

独立插件，胶囊型设计，不依赖任何第三方播放器库。

- **迷你态** — 封面旋转 + 歌曲标题，悬浮在页面左下角
- **展开态** — 深蓝主题面板 + 实时歌词同步 + 控制按钮
- **右键菜单** — 播放控制 / 模式切换 / 歌曲列表 / 复制歌名
- **数据源** — 本地 JSON / 网易云歌单 / QQ 音乐（带可部署的 Meting API）

### 📡 RSS 订阅聚合

内置 RSS 阅读器页面：多源管理 + 一键刷新 + 单源失败自动跳过 + 本地缓存。

### 💭 Memos 动态

接入 Memos API：短动态展示 + 关键词提取 + 独立页面模板。

### 💻 代码高亮

Prism.js 驱动：自动语言检测 / 行号显示 / 一键复制 / 长代码折叠 / Plyr 增强的音视频播放。

### ⚡ 性能

- **PJAX 导航** — Barba.js 无刷新页面切换
- **零网络字体** — 系统原生字体栈
- **智能缓存** — CSS/JS 基于 `filemtime()` 自动版本号
- **Transient 缓存** — WordPress 原生缓存 API

---

## 功能总览

| 功能 | 描述 | 状态 |
|------|------|------|
| PJAX 无刷新加载 | Barba.js 页面切换，丝滑体验 | ✅ |
| Xplayer 音乐播放器 | 胶囊型设计，实时歌词，右键菜单 | ✅ |
| Memos 动态集成 | API 数据获取 + 本地缓存 | ✅ |
| RSS 订阅聚合 | 多源管理，失败自动跳过 | ✅ |
| 代码高亮 | PrismJS 自动加载语言 + 折叠/复制 | ✅ |
| 图片灯箱 | ViewImage 轻量级图片灯箱 | ✅ |
| 图片懒加载 | lazysizes + MutationObserver | ✅ |
| 图片加载动画 | 7 种效果任选 | ✅ |
| 图片 Loading 占位 | 旋转圆圈 + 宽高比保持 | ✅ |
| 下载按钮短代码 | `[download_button]` | ✅ |
| 代码运行器短代码 | `[code_runner]` HTML/CSS/JS 沙盒 | ✅ |
| 内联代码样式 | 普通代码 + `[red_code]` 高亮 | ✅ |
| 图片网格布局 | `lared-grid-2/3/4` 多栏排列 | ✅ |
| AJAX 评论 | 无刷新提交 + 60 秒编辑 + 表情面板 | ✅ |
| 评论邮件通知 | SMTP / Resend 双通道 + 模板预览 | ✅ |
| AI 文章摘要 | 4 家 AI 服务商 + 品牌图标 | ✅ |
| 文章目录 | 自动生成 + 滚动高亮 | ✅ |
| 热力图 | 首页 60 天更新日历 | ✅ |
| 友情链接 | 卡片/文字双样式 + 申请表单 | ✅ |
| 相册页面 | R2 存储 + 前端上传 + 网格布局 | ✅ |
| WebP 自动转换 | Imagick 驱动 | ✅ |
| 回头访客识别 | Cookie + 头像检测 | ✅ |

---

## 页面模板

| 模板 | 用途 |
|------|------|
| 首页 | Hero 区域 + 热力图 + 热门文章 + 最新评论 + 标签云 + 统计 |
| 文章页 | 目录导航 + AI 摘要 + 评论区 + 版权信息（CC BY-NC-SA 4.0） |
| 归档页 | 时间线归档 |
| RSS 订阅 | 多源聚合阅读器 |
| Memos 动态 | 短内容展示 |
| 相册 | 图片网格 + 前端上传 |
| 友链 | 卡片/文字双样式 + 申请友链 |
| 关于 | 个人介绍 |

---

## 技术栈

| 技术 | 用途 |
|------|------|
| Tailwind CSS v4 | 样式系统 |
| Barba.js | PJAX 无刷新导航 |
| Prism.js | 代码高亮 |
| lazysizes | 图片懒加载 |
| Plyr | 视频/音频播放器 |
| Font Awesome | 图标库 |
| PHP 8.0+ | 后端，全面使用类型提示 |
| WordPress 6.0+ | CMS 基础 |

---

## 环境要求

| 项目 | 要求 |
|------|------|
| WordPress | 6.0 或更高（测试至 6.8） |
| PHP | 8.0 或更高（推荐 8.1+） |
| 推荐 | 启用 HTTPS、启用 Imagick 扩展 |

---

## 安装

### 方法一：后台上传

1. 登录 WordPress 后台 → **外观** → **主题** → **添加新主题**
2. 点击 **上传主题**，选择主题 `.zip` 压缩包
3. 点击 **立即安装** → **启用**

### 方法二：FTP 上传

1. 解压主题压缩包
2. 将 `Lared` 文件夹上传到 `/wp-content/themes/`
3. 后台 → **外观** → **主题** → 找到 Lared → **启用**

### 方法三：Git 克隆

```bash
cd /path/to/wordpress/wp-content/themes/
git clone <repository-url> Lared
```

---

## 后台设置

一个面板管理所有配置（**外观 → Lared 设置**），Tab 分区清晰：

| Tab | 配置项 |
|-----|--------|
| 基础设置 | 图片懒加载 / 加载动画效果 / Umami 统计代码 / 十年博客起始日期 |
| AI 摘要 | 服务商选择 / API Key / 模型配置 |
| RSS 管理 | 订阅源增删 / 缓存刷新 |
| Memos | API 地址 / Token / 拉取数量 |
| 邮件设置 | SMTP / Resend 切换 / 发件人配置 / 模板预览 / 测试发送 |

> 详细使用说明请查看 [GUIDE.md](GUIDE.md)

---

## 文件结构

```
Lared/
├── assets/
│   ├── css/
│   │   ├── tailwind.input.css    # Tailwind 源文件
│   │   ├── tailwind.css          # 编译后 CSS
│   │   └── main.css              # 主题自定义样式
│   ├── js/
│   │   ├── app.js                # 主题主脚本
│   │   └── pjax.min.js           # PJAX 库
│   ├── fonts/                    # PaperMono 等效果字体
│   ├── images/                   # 主题图片资源
│   ├── json/                     # Emoji / 缓存 JSON
│   └── music/                    # 音乐播放器资源
├── inc/
│   ├── inc-memos.php             # Memos 集成
│   ├── inc-rss.php               # RSS 聚合
│   ├── inc-email.php             # 邮件模块
│   ├── inc-comments.php          # 评论 UA/Geo 解析
│   └── inc-download-button.php   # 下载按钮短代码
├── templates/                    # 页面模板
│   ├── page-archive.php
│   ├── page-memos.php
│   ├── page-feed.php
│   ├── page-friend-links.php
│   ├── page-album.php
│   └── page-about-main.php
├── docs/                         # 文档
│   ├── README.md                 # 本文件（主题介绍 + 特色）
│   ├── CHANGELOG.md              # 更新日志
│   └── GUIDE.md                  # 使用说明
├── functions.php                 # 主题功能
├── style.css                     # 主题声明
├── index.php                     # 首页
├── single.php                    # 文章页
├── page.php                      # 页面模板
├── header.php                    # 页头
├── footer.php                    # 页脚
└── comments.php                  # 评论模板
```

---

## 开发构建

```bash
cd wp-content/themes/Lared
npm install

npm run build:css     # 构建 Tailwind CSS
npm run build:js      # 构建 JS
npm run build         # 构建所有
npm run watch:css     # 开发模式监听
```

---

## 许可证

本主题采用 GPL v2 或更高版本许可证。

---

## 致谢

[Tailwind CSS](https://tailwindcss.com/) · [Barba.js](https://barba.js.org/) · [PrismJS](https://prismjs.com/) · [ViewImage](https://github.com/Tokinx/ViewImage) · [lazysizes](https://github.com/aFarkas/lazysizes) · [Plyr](https://plyr.io/) · [Font Awesome](https://fontawesome.com/)

---

**Lared** 由 [西风](https://xifeng.net) 独立设计开发。

如果你和我一样，相信博客的价值在于文字本身，那 Lared 值得一试。
