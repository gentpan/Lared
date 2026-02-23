# Lared WordPress 主题

> **版本**: 1.0.2  
> **作者**: 西风  
> **主题URI**: https://xifeng.net/wordpress-lared-theme.html

---

## 📋 目录

1. [主题简介](#主题简介)
2. [功能特性](#功能特性)
3. [系统要求](#系统要求)
4. [安装方法](#安装方法)
5. [使用说明](#使用说明)
6. [主题设置](#主题设置)
7. [页面模板](#页面模板)
8. [开发构建](#开发构建)
9. [代码分析](#代码分析)
10. [改进建议](#改进建议)

---

## 主题简介

Lared 是一款现代化的 WordPress 主题，采用 Tailwind CSS v4.1.6 构建，设计理念简洁优雅，功能丰富实用。主题支持 PJAX 无刷新页面加载、音乐播放器、Memos 动态集成、RSS 订阅聚合等特性，适合个人博客和技术博客使用。

### 设计特点

- **极简风格**: 黑白红三色搭配，突出重点内容
- **响应式设计**: 完美适配桌面端和移动端
- **无刷新体验**: PJAX 技术实现流畅的页面切换
- **丰富交互**: 代码复制、图片灯箱、目录导航等功能

---

## 功能特性

### 核心功能

| 功能 | 描述 | 状态 |
|------|------|------|
| PJAX 无刷新加载 | 页面切换无刷新，提升用户体验 | ✅ |
| APlayer 音乐播放器 | 固定底部音乐播放器，支持歌单 | ✅ |
| Memos 动态集成 | 集成 Memos 备忘录动态展示 | ✅ |
| RSS 订阅聚合 | 聚合友链 RSS 源，展示最新文章 | ✅ |
| 代码高亮 | PrismJS 代码高亮，支持自动加载语言 | ✅ |
| 图片灯箱 | ViewImage 图片放大查看 | ✅ |
| 图片懒加载 | 原生懒加载优化性能 | ✅ |
| 图片加载动画 | 淡入/像素化显现效果 | ✅ |
| 文章图片 Loading | 占位框 + 旋转圆圈 | ✅ |
| 下载按钮短代码 | [download_button] 短代码 | ✅ |
| 内联代码样式 | 普通代码 + 红色高亮代码 | ✅ |
| 代码运行器 | HTML/CSS/JS 实时预览 | ✅ |
| AJAX 评论 | 无刷新提交评论 | ✅ |
| 文章目录 | 自动生成文章目录导航 | ✅ |
| 热力图 | 首页显示近60天更新热力图 | ✅ |
| 十年进度条 | 博客十年计划进度展示 | ✅ |

### 页面模板

- **首页** (`index.php`) - 文章列表、侧边栏、热力图
- **文章页** (`single.php`) - 文章详情、目录、上下篇导航
- **归档页** (`templates/page-archive.php`) - 文章归档展示
- **Memos页** (`templates/page-memos.php`) - Memos 动态展示
- **订阅页** (`templates/page-feed.php`) - RSS 聚合展示
- **友链页** (`templates/page-friend-links.php`) - 友情链接展示
- **关于页** (`templates/page-about-main.php`) - 关于页面

---

## 系统要求

- **WordPress**: 6.0 或更高版本
- **PHP**: 8.0 或更高版本
- **测试环境**: WordPress 6.8

### 推荐环境

- PHP 8.1+
- WordPress 6.5+
- 启用 HTTPS

---

## 安装方法

### 方法一：后台上传

1. 登录 WordPress 后台
2. 进入 **外观** → **主题** → **添加新主题**
3. 点击 **上传主题**，选择主题压缩包
4. 点击 **立即安装**，然后 **启用**

### 方法二：FTP上传

1. 解压主题压缩包
2. 将 `Lared` 文件夹上传到 `/wp-content/themes/` 目录
3. 登录 WordPress 后台，进入 **外观** → **主题**
4. 找到 Lared 主题，点击 **启用**

### 方法三：Git 克隆

```bash
cd /path/to/wordpress/wp-content/themes/
git clone <repository-url> Lared
```

---

## 使用说明

### 基础设置

1. **设置网站标题和描述**
   - 进入 **设置** → **常规**
   - 填写 **站点标题** 和 **站点副标题**

2. **设置静态首页**（可选）
   - 进入 **设置** → **阅读**
   - 选择 **您的最新文章** 或 **一个静态页面**

3. **创建菜单**
   - 进入 **外观** → **菜单**
   - 创建新菜单并分配到 **Primary Menu** 位置

### 主题设置

进入 **外观** → **主题设置**，配置以下选项：

#### 音乐播放器

| 设置项 | 说明 | 示例 |
|--------|------|------|
| APlayer Playlist JSON | 自定义播放列表 JSON | `[{"name":"Song","artist":"Artist","url":"..."}]` |
| 音乐页歌单地址 | 网易云/QQ音乐歌单链接 | `https://music.163.com/#/playlist?id=xxx` |
| Meting API 模板 | 歌单解析 API 地址 | 留空使用默认 |

#### Memos 集成

| 设置项 | 说明 | 示例 |
|--------|------|------|
| Memos 站点地址 | Memos 实例地址 | `https://memos.example.com` |
| Memos API 地址 | API 端点 | `https://memos.example.com/api/v1/memos` |
| Memos API Token | 私有实例 Token | `your-token-here` |
| Memos 拉取数量 | 每次请求条数 | `20` |

#### 其他设置

| 设置项 | 说明 |
|--------|------|
| Umami 统计代码 | 粘贴 Umami 统计脚本 |
| 博客十年起始日期 | 用于计算十年进度 |
| RSS Cache | 手动刷新缓存 |

### 分类图标设置

在分类描述中添加 Font Awesome 图标：

```html
<i class="fa-solid fa-plane"></i>
```

支持的图标类名格式：
- `fa-solid fa-*`
- `fa-regular fa-*`
- `fa-brands fa-*`
- `fa-thin fa-*`
- `fa-sharp fa-*`

---

## 页面模板

### 创建模板页面

1. 进入 **页面** → **新建页面**
2. 填写页面标题
3. 在右侧 **页面属性** → **模板** 中选择对应模板
4. 发布页面

### 可用模板

| 模板名称 | 用途 | 模板文件 |
|----------|------|----------|
| 默认模板 | 普通页面 | `page.php` |
| 备忘录动态 | Memos 动态展示 | `templates/page-memos.php` |
| 文章归档 | 文章时间线归档 | `templates/page-archive.php` |
| 订阅聚合 | RSS 订阅展示 | `templates/page-feed.php` |
| 友情链接 | 友链展示 | `templates/page-friend-links.php` |
| 关于主页 | 关于页面 | `templates/page-about-main.php` |

---

## 开发构建

### 环境要求

- Node.js 18+
- npm 9+

### 安装依赖

```bash
cd wp-content/themes/Lared
npm install
```

### 构建命令

```bash
# 构建 CSS（生产环境）
npm run build:css

# 构建 JS（生产环境）
npm run build:js

# 构建所有资源
npm run build

# 监听 CSS 变化（开发环境）
npm run watch:css
```

### 文件结构

```
Lared/
├── assets/
│   ├── css/
│   │   ├── tailwind.input.css    # Tailwind 输入文件
│   │   └── tailwind.css          # 编译后的 CSS（自动生成）
│   ├── js/
│   │   ├── app.js                # 主题主脚本
│   │   └── pjax.min.js           # PJAX 库
│   ├── fonts/
│   │   └── PaperMono-Regular.woff2
│   └── music/                    # 音乐播放器资源
│       ├── css/
│       ├── js/
│       └── playlists/
├── inc/
│   ├── inc-memos.php             # Memos 集成
│   └── inc-rss.php               # RSS 聚合
├── templates/                    # 页面模板
├── functions.php                 # 主题功能
├── style.css                     # 主题样式
├── index.php                     # 首页
├── single.php                    # 文章页
├── page.php                      # 页面模板
├── header.php                    # 页头
├── footer.php                    # 页脚
└── comments.php                  # 评论模板
```

---

## 代码分析

### 代码质量评估

| 方面 | 评分 | 说明 |
|------|------|------|
| 代码规范 | ⭐⭐⭐⭐ | 良好的命名规范，使用了类型提示 |
| 安全性 | ⭐⭐⭐⭐⭐ | 完善的数据过滤和转义 |
| 可维护性 | ⭐⭐⭐⭐ | 模块化设计，功能分离清晰 |
| 性能优化 | ⭐⭐⭐⭐ | 使用了缓存机制，但仍有优化空间 |
| 文档注释 | ⭐⭐⭐ | 部分函数缺少文档注释 |

### 安全实践

✅ **良好实践**
- 所有输出都使用 `esc_html()`, `esc_attr()`, `esc_url()` 等函数转义
- 使用 `wp_kses()` 限制允许的 HTML 标签
- 使用 `wp_nonce_field()` 和 `check_ajax_referer()` 进行权限验证
- 使用 `current_user_can()` 检查用户权限
- 使用 `sanitize_text_field()`, `esc_url_raw()` 等函数过滤输入

### 性能优化

✅ **已实现的优化**
- 使用 `filemtime()` 为静态资源添加版本号
- RSS 聚合使用文件缓存
- Memos 数据使用 Transient 缓存
- 使用 `no_found_rows` 优化查询

---

## 改进建议

### 🔴 高优先级

#### 1. 主题信息不一致

**问题**: `style.css` 中主题名为 `Lared`，与文件夹名 `Lared` 不一致。

**建议**:
```css
/* 修改前 */
Theme Name: Lared

/* 修改后 */
Theme Name: Lared
```

#### 2. 缺少 `screenshot.png`

**问题**: 主题缺少截图文件，后台预览时显示空白。

**建议**: 添加 `screenshot.png` (1200×900px) 到主题根目录。

#### 3. 外部依赖硬编码

**问题**: CDN 地址硬编码在代码中，不利于维护。

**位置**: `functions.php` 第 715-725 行

**建议**: 使用过滤器或常量定义 CDN 地址：
```php
// 在 functions.php 顶部定义
if (!defined('LARED_CDN_FONTS')) {
    define('LARED_CDN_FONTS', 'https://fonts.bluecdn.com/css2?family=Noto+Sans+SC:wght@400;500;700;900&display=swap');
}

// 使用时
wp_enqueue_style('lared-fonts', LARED_CDN_FONTS, [], null);
```

#### 4. 外部链接占位符

**问题**: `footer.php` 中的社交链接使用 `#` 占位。

**建议**: 添加主题设置选项或过滤器：
```php
// 在 functions.php 中添加
function lared_get_social_links(): array {
    $defaults = [
        'github' => '',
        'twitter' => '',
        'telegram' => '',
        'rss' => get_feed_link(),
    ];
    return apply_filters('lared_social_links', $defaults);
}
```

### 🟡 中优先级

#### 5. 图片使用随机占位图

**问题**: 首页和文章页使用 `picsum.photos` 随机图片。

**位置**: 
- `index.php` 第 18, 28 行
- `single.php` 第 86 行

**建议**: 添加主题选项或优先使用特色图片：
```php
function lared_get_post_image_url(int $post_id, string $size = 'large'): string {
    // 1. 优先使用特色图片
    if (has_post_thumbnail($post_id)) {
        $url = get_the_post_thumbnail_url($post_id, $size);
        if ($url) return $url;
    }
    
    // 2. 使用文章内第一张图片
    // ... 现有逻辑
    
    // 3. 使用主题设置的默认图片
    $default_image = get_option('lared_default_post_image');
    if ($default_image) return $default_image;
    
    // 4. 最后使用占位图
    return 'https://picsum.photos/seed/lared-post-' . $post_id . '/1600/900';
}
```

#### 6. 缺少多语言支持文件

**问题**: 主题声明支持国际化，但没有 `.pot` 文件。

**建议**: 使用 WP-CLI 生成翻译模板：
```bash
wp i18n make-pot . languages/lared.pot
```

#### 7. 错误处理可以改进

**问题**: 某些函数在出错时返回空值，没有日志记录。

**建议**: 添加错误日志：
```php
function lared_get_memos_stream(array $args = []): array {
    // ... 现有代码
    
    if (is_wp_error($response)) {
        error_log('Lared Theme: Memos API error - ' . $response->get_error_message());
        return [
            'items' => [],
            'stats' => ['count' => 0, 'latest_timestamp' => 0],
            'errors' => [$response->get_error_message()],
        ];
    }
    
    // ...
}
```

#### 8. 数据库查询优化

**问题**: `index.php` 中进行多次独立查询。

**建议**: 考虑使用 Transient 缓存热门文章、最新评论等数据。

### 🟢 低优先级

#### 9. 添加 Service Worker

**建议**: 添加 Service Worker 实现离线访问和推送通知。

#### 10. 添加暗黑模式

**建议**: 添加暗黑/亮色模式切换功能。

#### 11. 优化 SEO

**建议**: 
- 添加结构化数据 (Schema.org)
- 优化 Open Graph 标签
- 添加面包屑导航

#### 12. 代码分割

**建议**: 将 `app.js` 按功能拆分为多个模块，按需加载。

---

## 更新日志

### v1.0.2 (2026-02-24)

- 🆕 下载按钮短代码 `[download_button]` 完整实现
- 🆕 文章链接自动添加 link icon
- 🆕 评论系统全面增强（回复拦截 / 编辑倒计时 / 表情面板 / 管理员徽章）
- 🔧 修复 PJAX 与评论系统冲突
- 🔧 修复链接 icon class 名不匹配
- 💅 Toast / Loading 动画 / 取消回复按钮样式优化

### v1.0.0 (2026-02-23)

- 🎉 初始版本发布
- ✨ 支持 PJAX 无刷新加载
- ✨ 集成 APlayer 音乐播放器
- ✨ 支持 Memos 动态集成
- ✨ RSS 订阅聚合功能
- ✨ 代码高亮和复制功能
- ✨ 图片灯箱查看
- ✨ AJAX 评论提交
- ✨ 文章目录导航
- ✨ 更新热力图
- ✨ 十年进度条

---

## 许可证

本主题采用 GPL v2 或更高版本许可证。

---

## 致谢

- [Tailwind CSS](https://tailwindcss.com/)
- [APlayer](https://github.com/DIYgod/APlayer)
- [PrismJS](https://prismjs.com/)
- [ViewImage](https://github.com/Tokinx/ViewImage)
- 原生图片懒加载 API
- 图片加载动画（淡入、像素化显现）
- 文章图片 Loading 占位效果
- 下载按钮短代码
- 内联代码红色背景样式
- 代码运行器短代码
- [PJAX](https://github.com/MoOx/pjax)

---

**制作 with ❤️ by 西风**
