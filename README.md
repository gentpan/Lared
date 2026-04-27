<div align="center">

# Lared

**面向写作者的极简 WordPress 主题**
基于 Tailwind CSS · PJAX 无刷新导航 · 专注内容本身

[![Version](https://img.shields.io/badge/version-1.3.0-2563eb?style=flat-square)](https://github.com/gentpan/Lared/releases/latest)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-16a34a?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b?style=flat-square&logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-v4-06b6d4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![Last Commit](https://img.shields.io/github/last-commit/gentpan/Lared?style=flat-square&color=8b5cf6)](https://github.com/gentpan/Lared/commits/main)

[演示站](https://xifeng.net) · [主题主页](https://xifeng.net/wordpress-lared-theme.html) · [更新日志](CHANGELOG.md) · [问题反馈](https://github.com/gentpan/Lared/issues)

</div>

---

## 概述

Lared 是一款为长文写作者设计的 WordPress 主题。设计目标只有一个 — **让读者把注意力放在内容上**。所有交互、动画、性能优化都围绕这个目标取舍。

- **极简版式** — 无侧边栏堆砌，正文居中，留白充足
- **无刷新导航** — Barba.js + PJAX，页面切换不闪屏
- **零网络字体** — 系统字体栈直出，无额外字体请求
- **写作友好** — 编辑器工具、短代码、目录、AI 摘要全自带，不依赖外部插件

---

## 安装

```bash
cd wp-content/themes/
git clone https://github.com/gentpan/Lared.git
```

后台 **外观 → 主题** 启用 Lared，再到 **外观 → Lared 主题设置** 完成基础配置。

> 升级：进入设置页底部 **版本** → 一键升级，或重新 `git pull`。

---

## 环境要求

| 依赖 | 最低版本 | 推荐 |
|---|---|---|
| WordPress | 6.0 | 6.8（已测试） |
| PHP | 8.0 | 8.1+ |
| MySQL / MariaDB | 5.7 / 10.3 | 8.0 / 10.6+ |
| 服务器扩展 | `imagick`（WebP 转换）、`curl`（远程拉取）、`mbstring` | |

---

## 主要特色

### 写作体验

- 双模式编辑器工具，可视化与代码模式都能用
- 短代码工具箱：`[download_button]` 下载卡 · `[code_runner]` 实时沙盒 · `[red_code]` 行内高亮
- 文章目录自动生成、滚动高亮、平滑跳转
- **文章图片自动排版**（v1.3.0）— 连续插图自动按数量分组：1=1，2=2，3=3，4=4，5=2+3，6=3+3，7=3+4，8+ 递归 `[4]+split`，每行 2 张 16:9，3/4 张 1:1。直角风格 + 图说磨砂层

### 评论系统

- AJAX 无刷新提交，评论即时出现
- 60 秒编辑窗口，提交后可改错
- Cookie 回头访客识别 + 头像检测
- 管理员评论自动徽章
- UA 解析（OS + 浏览器版本，支持 Client Hints）

### 邮件通知

- 双通道：SMTP / Resend API
- 三类模板：管理员通知、回复通知、测试邮件
- 后台所见即所得预览

### AI 摘要

- 多服务商：OpenAI · DeepSeek · Kimi · MiniMax
- 文章右上角自动展示对应品牌图标
- 每篇文章自动生成摘要卡片

### 图片处理

- WebP 自动转换（Imagick 驱动）
- Cloudflare R2 + Lsky Pro 图床一键集成
- lazysizes v5.3.2 双驱动懒加载（IntersectionObserver + MutationObserver）
- **图片淡入**（v1.3.0）— `blur(40px) → blur(0)` 1.2s 线性过渡，由 lazysizes class 切换驱动
- 内置轻量灯箱（ViewImage），含防重复打开 + 列表去重

### 缓存与运维（v1.3.0 新增）

- 后台 **外观 → Lared 主题设置 → 缓存管理** tab：
  - 一键关闭本地缓存（友链 favicon + Gravatar 头像直走远端）
  - 清空所有缓存
  - 强制刷新缓存（清空 + 预热所有友链图标）
- 友链头像数据源：`favicon.im?larger=true`（带本地 30 天缓存）
- Gravatar 反代：`gravatar.bluecdn.com`，本地缓存 30 天

### 音乐与 RSS

- 首页内联播放器（频谱 + 进度条）
- 悬浮播放器（左上贴边）
- 侧边歌词同步滚动，PJAX 自动切换
- RSS 多源聚合：失败自动跳过、本地缓存、Feeds 卡片字母兜底
- Memos 动态接入，关键词自动提取

### 性能与导航

- Barba.js + PJAX 无刷新导航
- 系统字体栈，零网络字体请求
- CSS/JS 自动 `filemtime()` 版本号 + Transient 缓存
- Prism.js 自动语言检测 + 行号 + 复制 + 折叠

---

## 页面模板

| 模板 | 说明 |
|---|---|
| 首页 | Hero + 热力图 + 热门文章 + 最新评论 + 标签云 |
| 文章页 | 目录导航 + AI 摘要 + 评论区 + CC BY-NC-SA 4.0 版权信息 |
| 归档页 | 时间线 + 年月分组 + 文章统计 |
| RSS 订阅 | 多源聚合阅读器 + 一键刷新 |
| Memos 动态 | 短内容展示 + 关键词提取 |
| 相册 | 网格布局 + 前端上传至 R2 |
| 友链 | 卡片 / 文字双样式 + 申请表单 |
| 关于 | 个人介绍 + 十年博客进度图 |

---

## 技术栈

[Tailwind CSS v4](https://tailwindcss.com/) · [Barba.js](https://barba.js.org/) · [Prism.js](https://prismjs.com/) · [lazysizes](https://github.com/aFarkas/lazysizes) · [Plyr](https://plyr.io/) · [Font Awesome Pro](https://fontawesome.com/) · [ViewImage](https://github.com/Tokinx/ViewImage)

---

## 兼容性

- 浏览器：Chrome 90+、Safari 14+、Firefox 88+、Edge 90+
- 移动端：iOS Safari 14+、Android Chrome 90+
- 测试 WordPress：6.0、6.4、6.6、6.8

---

## 常见问题

<details>
<summary>升级后图片显示模糊不消失？</summary>

v1.3.0 修复了已缓存图片的 blur fade-in 残留 bug。如果硬刷后仍模糊，去 **外观 → Lared 主题设置 → 缓存管理** → 强制刷新缓存。
</details>

<details>
<summary>页脚总浏览量长期不变？</summary>

总浏览量缓存 1 小时（`_transient_lared_total_views_cache`）。如需立即更新，去 **缓存管理** → 强制刷新，或后台数据维护 → 重置 / 重算。
</details>

<details>
<summary>FontAwesome 图标显示成方块？</summary>

主题用了 FontAwesome Pro 7.x。CDN 默认走 `icons.bluecdn.com`，可以在 `wp-config.php` 用常量覆盖：

```php
define('LARED_CDN_FONTAWESOME', 'https://your-cdn.example/fontawesome-pro/css/all.min.css');
```
</details>

---

## 许可

GPL-2.0-or-later · Copyright © 2025 [西风](https://xifeng.net)

允许商用、修改、分发，二次分发须保留版权声明并采用同等许可。
