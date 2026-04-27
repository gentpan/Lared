# Changelog

所有重要变更记录在此文件。

格式参考 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.1.0/)，版本号遵循 [Semantic Versioning](https://semver.org/lang/zh-CN/)。

---

## [1.3.0] — 2026-04-27

### Added

- **文章图片自动排版** `.lared-photos` — 连续 `<figure>` 与旧式 `<div class="lared-grid-N">` 自动合并为分组，按数量分行：
  `1=1`, `2=2`, `3=3`, `4=4`, `5=2+3`, `6=3+3`, `7=3+4`, `8+` 递归 `[4]+split(n-4)`。
  每行 2 张为 16:9，3/4 张为 1:1。直角风格 + 图说磨砂层。
- **图片淡入效果** — `blur(40px) → blur(0)` 1.2s 线性过渡，由 lazysizes 自身的 class 切换（`.lazyload` → `.lazyloaded` / `.ls-is-cached`）驱动。
- **后台「缓存管理」tab**（外观 → Lared 主题设置 → 缓存管理）:
  - 一键关闭本地缓存开关
  - 清空所有缓存按钮（friend-link favicon + Gravatar 头像）
  - 强制刷新缓存按钮（清空 + 预热所有友链书签 favicon）
- **Feeds 卡片头像** — RSS feed 没自带 `site_avatar` 时调用 `favicon.im` + 本地缓存。
- **Feeds 卡片字母兜底** — favicon 加载失败时自动显示站点首字符（中英文支持，CSS `text-transform: uppercase`）。
- **ViewImage 防重复打开 patch** — Lightbox 不再叠加多个 overlay，关闭按钮一次清空所有 `.view-image`。
- **ViewImage 列表去重** — `Array.from(new Set(images))`，防止 `1/N` 计数虚高与左右切换重复同张图。

### Changed

- 友链图标数据源：`ico.bluecdn.com` → `https://favicon.im/<host>?larger=true`。
- 占位检测调整：`content-type: image/svg+xml` 或 body < 500B 视为占位失败。
- 文章图片 `figcaption` 样式重写：右下角磨砂层 → GeistMono 字体 + `rgba(0,0,0,0.4)` 黑色 40% 透明 + 2px 圆角。
- `.lared-photos` 内的图片完全跳出主题原 `.img-loading-wrapper.is-loaded` 体系，由独立 fade-in 控制可见性。
- `.lared-photos` 间距收紧：行 gap `8px → 4px`，块 margin `1.5rem → 0.75rem`。
- DOM 嵌套从 7 层减到 4 层（PHP filter 在 priority 30 剥掉 `.img-loading-wrapper` shell）。

### Fixed

- `lared_track_home_views_ajax` UPDATE 影响 0 行 bug — option 不存在时永远不创建（已加 `add_option` guard）。
- `.lared-photos` 单图模式 figure 高度变 0（`flex: 1 1 0` 在 column 容器主轴算法 bug，已用 `flex: none + width: 100%` 覆盖）。
- `.lared-photos` 内图片受主题 `.is-loaded { height: auto; object-fit: initial }` 影响导致 16:9 容器上下出现灰底（已用 (0,4,2) specificity + `!important` 强制 `object-fit: cover`）。
- 主题 `img[data-img-animation] { opacity: 0 }` 影响 `.lared-photos` 内图片不显示（已绕过）。
- 主题 `.img-loading-target { opacity: 0 !important }` 让首屏 cached 图永久不可见（强制 `opacity: 1 !important`）。
- lazysizes 已缓存图片用 `.ls-is-cached` 而非 `.lazyloaded` —— CSS 选择器补充。
- `transition: !important` 让淡入实际触发（之前被主题 `transition: opacity .35s, transform .6s !important` 干掉）。
- 全文章范围 hover scale(1.2) 取消（之前只覆盖 `.lared-photos` 内，导致裸 `<img>` 仍放大产生不一致）。
- 文章图片自动排版仅在 `assets/css/lared-main.min.css` 生效（之前 patch 错地方）。
- 移动端 single-image bug 复发（media query 内补 `flex: none + width: 100%`）。
- 相邻 `.lared-grid-N` div 中间仅空白时未合并 — 改为正则 `(?:<div class="lared-grid-[234]">...</div>\s*(?:<p>\s*</p>\s*)*)+` 一次匹配多个。

### Refactored

- `.lared-photos` 内 `figure > figure.img-loading-wrapper > div > img` 嵌套结构在 PHP `lared_strip_loader_in_photos` (priority 30) 阶段剥离为 `figure > img`。

---

## [1.2.1] — 2026-03-10

### Fixed

- PJAX hover prefetch 与 click navigation 重复请求去重。
- 登出对话框重复 overlay。
- xMojipick 表情插件 PJAX 兼容。

### Changed

- README 更新（v1.2.1 完整功能展示）。
- contributors cache 触发刷新。

---

## [1.1.0] — 2026-03-10

初始公开发布。

---

[1.3.0]: https://github.com/gentpan/Lared/releases/tag/v1.3.0
[1.2.1]: https://github.com/gentpan/Lared/releases/tag/v1.2.1
[1.1.0]: https://github.com/gentpan/Lared/releases/tag/v1.1.0
