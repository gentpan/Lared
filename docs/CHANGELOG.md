# 更新日志

所有对 Lared 主题的显著更改都将记录在此文件中。

格式基于 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.0.0/)，
并且本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

---

## [1.1.0] - 2026-02-26

### ✨ 新增
- 🆕 悬浮播放器进度条悬停时间提示（tooltip 跟随鼠标显示当前时间点）
- 🆕 首页播放器进度条悬停时间提示
- 🆕 歌词面板限制为内页显示，PJAX 返回首页时自动隐藏
- 🆕 邮件调试：`wp_mail_failed` 钩子记录失败详情至 `debug.log`
- 🆕 友链 text 样式字母兜底头像（favicon 加载失败时显示站名首字母）
- 🆕 友链头像支持 lazysizes 懒加载与页面图片动画效果

### 🎨 改进
- 💅 悬浮播放器：进度条容器高度修复（1px→14px）、圆点归位、红色填充覆盖底线
- 💅 悬浮播放器：歌曲名与按钮间距增加、按钮阴影/轮廓全部移除
- 💅 首页播放器：可视化柱高度增大（10-18px→14-26px）、时间显示隐藏、歌曲名居中
- 💅 首页播放器：播放时隐藏进度条避免与可视化柱重叠，hover 时恢复显示
- 💅 评论 UA 信息：操作系统与浏览器版本号改为行内显示（如 macOS 26、Chrome 133.0）
- 💅 友情链接 text 样式重构：2 列→4 列布局、32×32 favicon 图标（圆角 4px）
- 💅 友链头像统一使用 ico 服务获取，移除自定义图片与评论者头像回退逻辑
- 💅 Favicon API 从 Google 迁移至自托管 `ico.bluecdn.com`（card 样式同步迁移）

---

## [1.0.9] - 2026-02-25

### ✨ 新增
- 🆕 邮件模板系统全面重构 — 直角风格 + 主题配色
  - 基础外壳 `lared_email_shell()`：深色 Header（#21201c）+ 红色顶线（#f53004）+ 管理员头像 + 站点名称 + Footer 版权
  - 评论卡片组件 `lared_email_comment_card()`：头像 + 昵称 + 时间 + 内容，左侧红色边线
  - 通用模板 `lared_email_html_template()`：标题 + 正文 + CTA 按钮（测试邮件/简单通知）
  - 管理员通知模板 `lared_email_admin_notify()`：新评论/待审核通知，待审核含「审核评论」按钮
  - 回复通知模板 `lared_email_reply_notify()`：原评论 + 回复评论卡片 + 查看按钮
- 🆕 评论通知 Hook 系统
  - `comment_post`（priority 20）：新评论时通知管理员（管理员自己的评论自动跳过）
  - `comment_post`（priority 21）：回复评论时通知被回复者（回复自己自动跳过）
  - `wp_set_comment_status`：待审核回复被批准时，自动发送回复通知
- 🆕 邮件模板预览支持 4 种类型切换
  - 下拉选择：测试邮件 / 管理员通知（新评论）/ 管理员通知（待审核）/ 回复通知
  - 切换类型时自动刷新预览
- 🆕 文章页脚版权链接前添加 CC 图标（`fa-brands fa-creative-commons`）

### 🎨 改进
- 💅 邮件模板全面使用主题色：#f53004（强调）/ #21201c（深色背景）/ #63635e（正文色）
- 💅 邮件模板字体栈与主题统一：PingFang SC / Microsoft YaHei / system-ui
- 💅 所有邮件模板采用直角设计 — 零 `border-radius`
- 💅 `lared_send_email()` 签名更新，新增 `$vars` 参数支持模板变量传递
- 💅 CC 图标样式：`font-size: .85em`，`vertical-align: -0.05em`，`margin-right: 3px`

### 🗑️ 移除
- 禁用 WordPress 默认 `notify_post_author` 和 `notify_moderator` 通知（由主题自定义通知替代）
- 移除旧版紫色渐变邮件模板，替换为直角风格主题配色模板

---

## [1.0.8] - 2026-02-25

### ✨ 新增
- 🆕 后台新增「邮件设置」Tab，支持两种邮件发送模式切换
  - **SMTP 模式**：标准邮件协议，配置服务器地址、端口、加密方式、用户名密码
  - **Resend API 模式**：现代 HTTP 邮件 API，仅需填写 API Key
  - 两种模式通过 Radio 按钮一键切换，配置区域自动显示/隐藏
- 🆕 统一发件人配置：发件人名称 + 发件人邮箱，两种模式共用
- 🆕 邮件测试发送功能
  - 收件人输入框默认填充当前登录用户邮箱
  - 「发送测试邮件」按钮，实时显示发送结果（成功/失败 + 错误详情）
- 🆕 邮件 HTML 模板系统
  - 响应式 600px 宽度，兼容主流邮件客户端
  - 统一入口函数 `lared_send_email()` 自动包裹模板
- 🆕 邮件模板预览功能
  - 「加载预览」按钮在 iframe 中渲染模板效果
  - 「全屏查看」弹窗预览，模拟真实邮件客户端显示效果
- 🆕 SMTP 通过 `phpmailer_init` hook 配置，接管 WordPress 全站 `wp_mail()` 发信
- 🆕 Resend API 通过 `wp_remote_post()` 调用 `https://api.resend.com/emails`
- 🆕 新增 `inc/inc-email.php` 邮件模块，独立管理邮件发送、模板、设置注册、AJAX 处理

---

## [1.0.7] - 2026-02-25

### ✨ 新增
- 🆕 AI 摘要卡片显示服务商图标
  - OpenAI → Font Awesome `fa-openai` 图标
  - DeepSeek → 官方 SVG logo（绿色）
  - Kimi → 官方 SVG logo（原色）
  - MiniMax → 官方 SVG logo（绿色）
  - 服务商自动读取 `lared_ai_provider` 选项

### 🎨 改进
- 💅 AI 摘要机器人图标更换为 Font Awesome `fa-sharp fa-light fa-robot`
- 💅 AI 摘要卡片 `margin-top` 改为 `0`，紧贴文章顶部
- 💅 评论提交按钮加载态优化：CSS `color: transparent` + `::after` 旋转动画
- 💅 RSS 订阅按钮 tooltip 改为自定义 `.rss-tooltip`（红色主题色，复制成功反馈）
- 💅 评论区所有 tooltip 统一为自定义 `.comment-tooltip` 样式（移除原生 `title`）
- 💅 评论时间改为 `<time>` 标签 + `cursor: default`
- 💅 管理员徽章紧贴昵称：`gap: 0` 隔离父级间距
- 💅 AI 摘要位置移至文章 `<article>` 内部顶端

### 🐛 修复
- 🔧 修复 macOS 版本号冻结在 10.15.7 的问题
  - 实现 User-Agent Client Hints：`Accept-CH` 头 + JS `navigator.userAgentData.getHighEntropyValues()`
  - Chromium 浏览器可获取真实 macOS 版本号

### 🗑️ 移除
- ❌ 移除 IP 地理位置 JSON 文件缓存机制，改为纯数据库 `comment_meta` + 内存级静态缓存
- ❌ IP API 响应字段精简：移除 `status` 字段

---

## [1.0.6] - 2026-02-25

### 🐛 修复
- 🔧 修复 Xplayer API 模板 URL 无法保存的问题
  - `api.xifengcdn.com` 被错误列入废弃 API 主机名单
- 🔧 修复 RSS 订阅「一键刷新全部订阅」功能报错"获取源列表失败"
  - `check_ajax_referer()` 改为非致命验证，失败时返回 `wp_send_json_error()`
  - `lared_fetch_source_feed()` 增加具体错误消息捕获
  - JS 端增加 null-safe 检查

### ✨ 新增
- 🆕 RSS 拉取跳过机制：失败源标记"（已跳过）"并继续拉取

### 🎨 改进
- 💅 字体系统重构：系统原生字体替代 CDN 网络字体
  - 新字体栈：`-apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", system-ui, sans-serif`

### 🗑️ 移除
- ❌ 完全移除暗黑模式（Dark Mode）— 1054 行 CSS + JS + 内联脚本 + 切换按钮
- ❌ 移除 CDN 字体加载（`fonts.bluecdn.com`）

---

## [1.0.5] - 2026-02-24

### ✨ 新增
- 🆕 Xplayer 插件 UI 全面重构为 **胶囊型设计**
  - 迷你胶囊（封面旋转 + 标题）⇄ 展开胶囊（深蓝主题 + 行内歌词 + 控制按钮）
  - 流畅展开/收起过渡动画
  - 进度条：底部 2px 细线
- 🆕 Xplayer 右键菜单：播放控制、模式切换、歌曲列表、复制歌名
- 🆕 Xplayer 可部署 Meting API（`api/` 目录）
  - 支持网易云 / QQ 音乐（歌单/单曲/专辑/搜索/歌词）
  - 文件缓存系统（1 小时 TTL）、CORS 跨域

### 🎨 改进
- 💅 Xplayer 从圆形+面板架构重构为胶囊架构
- 💅 播放列表改为弹出式面板，控制区仅保留核心按钮

---

## [1.0.4] - 2026-02-24

### ✨ 新增
- 🆕 引入 Plyr v3.7.8 作为文章内 `<video>` / `<audio>` 播放器
  - CDN 加载，中文 i18n，兼容 PJAX
- 🆕 全新 **Xplayer** WordPress 插件
  - 左下角固定音乐播放器，完全自研 UI
  - 毛玻璃面板 + 封面旋转 + 播放列表
  - 支持本地 JSON / QQ 音乐 / 网易云歌单
  - 独立设置页，兼容旧主题 option

### 🗑️ 移除
- ❌ 从主题中完全移除 APlayer v1.10.1（CSS/JS/初始化代码/设置字段，~430 行）
- ❌ 音乐功能全部迁移到 Xplayer 插件

---

## [1.0.3] - 2026-02-24

### ✨ 新增
- 🆕 RSS 管理页添加「刷新全部订阅」按钮
- 🆕 相册页面模板（`page-album.php` + `inc-album.php`）
  - 前端多图上传至 Cloudflare R2 + 方形卡片网格 + 管理员操作按钮
  - 自定义 `.lared-dialog` 对话框组件
- 🆕 图片加载动画扩展至 7 种效果（新增：模糊、扩散、百叶窗、滑入、旋转缩放）
- 🆕 lazysizes v5.3.2 懒加载取代原生 `loading="lazy"`

### 🐛 修复
- 🔧 清理 `spinner-circle` 冗余内联样式

### 🎨 改进
- 💅 RSS / Memos 缓存 JSON 文件统一迁移至 `assets/json/`
- 💅 CSS 动画选择器迁移至 `.lazyloaded`
- 💅 `initImageLoadAnimation()` 简化为仅设置 `data-img-animation` 属性

### 🗑️ 移除
- ❌ 删除未使用的 `lazyImage.js`、`lazyVideo.js`
- ❌ 移除 `style.css` 中冗余样式，仅保留主题头部声明

---

## [1.0.2] - 2026-02-24

### ✨ 新增
- 🆕 下载按钮短代码 `[download_button]` PHP 实现
- 🆕 文章内链接自动添加 link icon
- 🆕 评论系统全面增强
  - 回复链接安全拦截（防止 PJAX 导航刷新）
  - 60 秒编辑倒计时
  - 表情面板 + 自定义表情渲染
  - 评论表头标题栏（头像/登录信息/欢迎语）
  - 回头访客自动识别
  - 管理员徽章（皇冠 + "博主"）
  - 时间精确到秒 + 回复/取消按钮图标化

### 🐛 修复
- 🔧 修复链接 icon class 名不匹配
- 🔧 修复 PJAX 拦截评论回复链接导致页面刷新
- 🔧 修复 `addComment.init()` 未重新初始化
- 🔧 修复评论作者名红色被 Tailwind 覆盖
- 🔧 修复 Cookie 保存 + CSS/JS 版本号缓存

### 🎨 改进
- 💅 下载按钮排除 link icon
- 💅 提交按钮 loading 动画增强
- 💅 Toast 改为直角小字体，移除 emoji
- 💅 取消编辑按钮样式优化

---

## [1.0.0] - 2026-02-23

### 🎉 初始发布

#### 核心功能
- ✨ 主题基础框架，支持 WordPress 6.0+ / PHP 8.0+
- ✨ 响应式设计，适配移动端和桌面端
- ✨ PJAX 无刷新页面加载
- ✨ APlayer 音乐播放器集成（已在 v1.0.4 替换为 Xplayer）
- ✨ Memos 动态集成（API + 本地缓存 + 关键词提取）
- ✨ RSS 订阅聚合（文件缓存 + 自动刷新）
- ✨ PrismJS 代码高亮（自动语言检测 + 行号 + 复制 + 折叠）
- ✨ ViewImage 图片灯箱
- ✨ 图片懒加载 + 加载动画（淡入 / 像素化）
- ✨ 文章图片 Loading 占位
- ✨ `[download_button]` / `[code_runner]` / `[red_code]` 短代码
- ✨ AJAX 评论提交 + 文章目录导航
- ✨ 首页 Hero 区域 + 60 天热力图 + 热门文章 + 标签云
- ✨ 主题设置面板
- ✨ Font Awesome 图标支持

#### 技术特性
- 🚀 PHP 8.0+ 类型提示
- 🚀 Tailwind CSS v4.1.6
- 🚀 模块化代码结构
- 🚀 完善的输入过滤和输出转义
- 🚀 Transient 缓存机制

---

## 版本号格式

`主版本号.次版本号.修订号`

- **主版本号**：重大更新，可能包含破坏性变更
- **次版本号**：新增功能，向下兼容
- **修订号**：问题修复，向下兼容

### 标签说明

- 🎉 重大更新 · ✨ 新增 · 🐛 修复 · 🎨 改进 · 🗑️ 移除 · 🚀 优化

---

## 兼容性

| 版本 | WordPress | PHP |
|------|-----------|-----|
| 1.0.x | 6.0+ | 8.0+ |

---

## 贡献者

- 西风 — 独立设计开发
