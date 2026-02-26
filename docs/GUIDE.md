# Lared 主题使用说明

> 从安装到精通，这份指南覆盖 Lared 主题的所有功能和配置。

---

## 目录

1. [快速上手](#快速上手)
2. [基础设置](#基础设置)
3. [后台设置面板](#后台设置面板)
4. [页面模板](#页面模板)
5. [评论系统](#评论系统)
6. [邮件通知](#邮件通知)
7. [短代码工具箱](#短代码工具箱)
8. [图片系统](#图片系统)
9. [友情链接](#友情链接)
10. [自定义样式](#自定义样式)
11. [开发构建](#开发构建)
12. [常见问题](#常见问题)
13. [性能优化](#性能优化)
14. [安全建议](#安全建议)

---

## 快速上手

### 5 分钟配置清单

```
1. 安装启用     后台 → 外观 → 主题 → 上传 → 启用
2. 站点信息     后台 → 设置 → 常规 → 填写标题和副标题
3. 导航菜单     后台 → 外观 → 菜单 → 创建菜单 → 显示位置: Primary Menu
4. 主题设置     后台 → 外观 → Lared 设置 → 按需配置
```

---

## 基础设置

### 设置网站信息

后台 → **设置** → **常规**：
- **站点标题**：博客名称
- **站点副标题**：简短描述

### 设置阅读选项

后台 → **设置** → **阅读**：
- 选择 **您的最新文章** 或 **一个静态页面**
- 设置文章显示数量

### 创建导航菜单

后台 → **外观** → **菜单**：
1. 创建新菜单
2. 添加页面 / 分类 / 自定义链接
3. 显示位置选择 **Primary Menu**

### 设置分类图标

后台 → **文章** → **分类** → 编辑分类 → 在描述中添加：

```html
<i class="fa-solid fa-plane"></i>
```

支持的图标类名：
- `fa-solid fa-*` — 实心图标
- `fa-regular fa-*` — 常规图标
- `fa-brands fa-*` — 品牌图标
- `fa-thin fa-*` — 细线图标
- `fa-sharp fa-*` — 锐利图标

查找图标：[fontawesome.com/icons](https://fontawesome.com/icons)

---

## 后台设置面板

后台 → **外观** → **Lared 设置**

### 基础设置 Tab

| 设置项 | 说明 | 示例 |
|--------|------|------|
| 图片懒加载 | 开启/关闭 lazysizes | 默认开启 |
| 图片加载动画 | 选择动画效果（7 种） | 淡入效果 |
| Umami 统计代码 | 粘贴完整 `<script>` 标签 | 留空不启用 |
| 博客十年起始日期 | 用于计算十年进度条 | 选择博客创建日期 |

### AI 摘要 Tab

| 设置项 | 说明 |
|--------|------|
| AI 服务商 | OpenAI / DeepSeek / Kimi / MiniMax |
| API Key | 对应服务商的 API 密钥 |
| 模型名称 | 使用的模型（如 `gpt-4o`、`deepseek-chat`） |

### RSS 管理 Tab

| 设置项 | 说明 |
|--------|------|
| 订阅源列表 | 添加/删除 RSS 源 |
| 刷新全部 | 一键拉取所有订阅源 |
| 清除缓存 | 手动删除本地缓存 |

### Memos Tab

| 设置项 | 说明 | 示例 |
|--------|------|------|
| Memos 站点地址 | Memos 实例 URL | `https://memos.example.com` |
| Memos API 地址 | API 端点 | `https://memos.example.com/api/v1/memos` |
| Memos API Token | 私有实例访问令牌 | 留空为公开实例 |
| 拉取数量 | 每次请求条数 | `20` |

### 邮件设置 Tab

#### SMTP 模式

| 设置项 | 说明 |
|--------|------|
| 服务器地址 | SMTP 主机（如 `smtp.qq.com`） |
| 端口 | 25 / 465 / 587 |
| 加密方式 | None / SSL / TLS |
| 用户名 | SMTP 登录用户名 |
| 密码 | SMTP 登录密码 |

#### Resend API 模式

| 设置项 | 说明 |
|--------|------|
| API Key | Resend 的 API Key（`re_` 开头） |

#### 通用配置

| 设置项 | 说明 |
|--------|------|
| 发件人名称 | 邮件显示的发件人（如"西风的博客"） |
| 发件人邮箱 | 发件人邮箱地址 |
| 测试发送 | 输入收件人邮箱，点击发送测试 |
| 模板预览 | 4 种模板类型实时预览 |

---

## 页面模板

### 创建模板页面

1. 后台 → **页面** → **新建页面**
2. 输入页面标题
3. 右侧 **页面属性** → **模板** → 选择对应模板
4. 发布

### 可用模板

| 模板名称 | 文件 | 用途 |
|----------|------|------|
| 默认模板 | `page.php` | 普通页面 |
| 备忘录动态 | `templates/page-memos.php` | Memos 动态展示 |
| 文章归档 | `templates/page-archive.php` | 文章时间线归档 |
| 订阅聚合 | `templates/page-feed.php` | RSS 聚合阅读器 |
| 友情链接 | `templates/page-friend-links.php` | 友链展示 + 申请 |
| 相册 | `templates/page-album.php` | 图片网格 + 前端上传 |
| 关于主页 | `templates/page-about-main.php` | 关于页面 |

### 推荐页面规划

| 页面 | 模板 | 必需 |
|------|------|------|
| Memos | 备忘录动态 | 使用 Memos 时必需 |
| 归档 | 文章归档 | 推荐 |
| 订阅 | 订阅聚合 | 使用 RSS 聚合时必需 |
| 友链 | 友情链接 | 推荐 |
| 关于 | 关于主页 | 推荐 |

---

## 评论系统

### 功能特性

| 功能 | 说明 |
|------|------|
| **AJAX 提交** | 评论无刷新即时出现 |
| **60 秒编辑** | 提交后 60 秒内可修改评论内容 |
| **表情面板** | 点击表情插入 Bilibili 自定义表情 |
| **回头访客** | Cookie 记忆 + Gravatar 检测，显示欢迎语 |
| **管理员徽章** | 博主评论自动标记皇冠 + "博主" 标签 |
| **UA 解析** | 自动显示操作系统 + 浏览器版本 |
| **Client Hints** | Chromium 浏览器可获取真实 macOS 版本号 |
| **自定义 Tooltip** | 所有交互提示使用主题色弹窗 |

### 评论回复

- 点击回复按钮，评论表单自动移动到该评论下方
- 表单标题显示「回复 昵称」
- 点击红色 X 图标取消回复
- 兼容 PJAX 页面切换（`comment-reply.js` 全局加载）

---

## 邮件通知

### 通知类型

| 类型 | 触发条件 | 收件人 |
|------|----------|--------|
| 管理员通知（新评论） | 有人发表新评论 | 管理员 |
| 管理员通知（待审核） | 评论需要审核 | 管理员 |
| 回复通知 | 有人回复了某条评论 | 被回复者 |

### 智能跳过规则

- 管理员自己发的评论不会通知自己
- 回复自己的评论不会发送通知
- 待审核回复在审核通过后自动发送通知

### 模板预览

后台邮件设置中可切换 4 种模板类型实时预览：
1. 测试邮件
2. 管理员通知（新评论）
3. 管理员通知（待审核）
4. 回复通知

---

## 短代码工具箱

### 下载按钮 `[download_button]`

在文章中添加美观的下载卡片。

**基础用法：**

```
[download_button dl_url="https://example.com/file.zip" dl_name="文件名.zip"]
```

**完整参数：**

```
[download_button
    dl_url="https://example.com/file.zip"
    dl_name="HEIC转换脚本"
    dl_text="本站下载"
    dl_size="1.4 KB"
    dl_format="RAR"
    dl_version="v1.0.0"
    dl_note="解压即用"
]
```

| 参数 | 说明 | 必需 | 默认值 |
|------|------|------|--------|
| `dl_url` | 下载链接 | ✅ | — |
| `dl_name` | 文件名称 | ❌ | "未知文件" |
| `dl_text` | 按钮文字 | ❌ | "立即下载" |
| `dl_size` | 文件大小 | ❌ | — |
| `dl_format` | 文件格式 | ❌ | — |
| `dl_version` | 版本号 | ❌ | — |
| `dl_note` | 备注说明 | ❌ | — |

---

### 代码运行器 `[code_runner]`

HTML/CSS/JS 实时沙盒预览，代码在 iframe 中安全运行。

**基础用法：**

```
[code_runner html="<h1>Hello</h1>" css="h1{color:red;}" js="console.log('hi')"]
```

**标签内容方式（推荐）：**

```
[code_runner height="400" title="按钮点击效果"]
<html>
  <button id="btn">点击我</button>
  <p id="result"></p>
</html>
<css>
  #btn {
    padding: 10px 20px;
    background: #f53004;
    color: white;
    border: none;
    cursor: pointer;
  }
</css>
<js>
  document.getElementById('btn').onclick = function() {
    document.getElementById('result').textContent = '你好！';
  };
</js>
[/code_runner]
```

| 参数 | 说明 | 默认值 |
|------|------|--------|
| `html` | HTML 代码 | 空 |
| `css` | CSS 样式 | 空 |
| `js` | JavaScript 代码 | 空 |
| `height` | 预览区域高度 (px) | 300 |
| `show_code` | 是否显示代码 (yes/no) | yes |
| `title` | 标题 | "代码预览" |

---

### 红色高亮代码 `[red_code]`

用于需要醒目显示的内容，如 UUID、密钥、验证码。

**用法：**

```
您的激活码是 [red_code]ABC-123-XYZ[/red_code]
```

或使用属性方式：

```
[red_code text="ABC-123-XYZ"]
```

也可以直接用 HTML class：

```html
<code class="code-red">ABC-123-XYZ</code>
```

**效果对比：**

| 类型 | 样式 | 适用场景 |
|------|------|----------|
| 普通 `<code>` | 淡红底 + 深红字 | 代码、文件名、路径 |
| `[red_code]` | 红底白字加粗 | UUID、密钥、验证码 |

---

### 图片网格布局

在编辑器中使用「自定义 HTML」块，用 `<div>` 包裹图片：

**两图并排：**

```html
<div class="lared-grid-2">
  <img src="图片1地址" alt="说明1">
  <img src="图片2地址" alt="说明2">
</div>
```

**三图等宽：**

```html
<div class="lared-grid-3">
  <img src="图片1" alt=""><img src="图片2" alt=""><img src="图片3" alt="">
</div>
```

**四图方阵（2×2）：**

```html
<div class="lared-grid-4">
  <img src="图片1" alt=""><img src="图片2" alt="">
  <img src="图片3" alt=""><img src="图片4" alt="">
</div>
```

**带说明文字：**

```html
<div class="lared-grid-2">
  <figure>
    <img src="图片1" alt="">
    <figcaption>说明文字</figcaption>
  </figure>
  <figure>
    <img src="图片2" alt="">
    <figcaption>说明文字</figcaption>
  </figure>
</div>
```

> 移动端自动调整：三图变为 2+1 布局。图片自动裁切填充（grid-2 为 3:2 比例，grid-3/4 为 1:1）。

---

## 图片系统

### 懒加载

基于 lazysizes v5.3.2，自动处理所有图片：
- `src` → `data-src`，`srcset` → `data-srcset`
- IntersectionObserver + MutationObserver 双驱动
- PJAX 兼容（Barba.js 切换后新 DOM 自动检测）

### 加载动画

后台 → **Lared 设置** → **图片加载动画**，可选：

| 效果 | 描述 |
|------|------|
| 无动画 | 图片直接显示 |
| 淡入（Fade） | 透明度渐变，1.5s |
| 像素化（Pixelate） | 模糊→清晰，2s |
| 模糊淡入（Blur） | 高斯模糊渐变 |
| 扩散（Expand） | 中心向外扩散 |
| 百叶窗（Blinds） | 条纹渐显 |
| 滑入（Slide） | 从边缘滑入 |
| 旋转缩放（Rotate） | 旋转缩放渐显 |

推荐：内容型网站用「淡入」，摄影/设计类用「像素化」。

**排除规则**（以下图片不应用动画）：
- 首屏 Hero 图片
- Emoji 表情
- 头像图片
- 评论区 UA 图标
- 代码块内图片
- 已缓存的图片

### Loading 占位

文章内图片自动添加占位效果：
- 加载前显示占位框 + 旋转圆圈
- 保持图片宽高比（`aspect-ratio`），零布局偏移
- 加载完成后图片淡入显示
- 加载失败显示错误图标

### 灯箱查看

文章内图片点击自动放大查看（ViewImage）。要排除特定图片，添加 `no-view` 属性：

```html
<img src="image.jpg" no-view>
```

---

## 友情链接

### 链接管理

后台 → **链接** → **添加新链接**：

| 字段 | 说明 | 必填 |
|------|------|------|
| 名称 | 站点名称 | ✅ |
| Web 地址 | 站点 URL | ✅ |
| 描述 | 一句话描述 | 推荐 |
| 分类 | 所属链接分类 | 推荐 |
| 图片地址 | 头像/Logo URL | 推荐 |

### 分类样式

通过链接分类的 **描述** 字段控制显示样式：

| 描述内容 | 样式 |
|----------|------|
| 留空 或 不含 `text` | **卡片样式** — 4 列网格，头像 + 站名 + 描述 |
| 包含 `text` | **文字样式** — 单列列表，站名 + 描述 + 箭头 |

每个分类标题前自动匹配宇宙主题图标（行星、星系、流星、太阳、星月、陨石、火箭、卫星），8 个图标循环。

### 头像获取优先级

1. 链接的 **Image** 字段
2. 评论中匹配该域名的评论者 Gravatar
3. Google Favicon 服务兜底

### 申请友链

页面底部有「申请友链」按钮：
1. 访客点击 → 弹出模态窗表单
2. 填写站名、网址、描述、Feed、头像
3. AJAX 提交 → 保存为隐藏链接（`link_visible = N`）
4. 管理员后台审核 → 设为可见即展示

---

## 自定义样式

### 修改主题色

编辑 `assets/css/main.css` 或通过后台自定义 CSS：

```css
:root {
    --color-accent: #f53004;  /* 主强调色（默认红） */
    --color-title:  #21201d;  /* 标题色 */
    --color-body:   #63635e;  /* 正文色 */
}
```

### 修改字体

主题使用系统原生字体栈，无需 CDN 加载。如需修改，编辑 CSS 中的字体栈：

```css
body {
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC",
                 "Microsoft YaHei", "Helvetica Neue", "Noto Sans SC",
                 system-ui, sans-serif;
}
```

---

## 开发构建

### 环境要求

- Node.js 18+
- npm 9+

### 安装与构建

```bash
cd wp-content/themes/Lared
npm install

npm run build:css     # 构建 Tailwind CSS（生产环境）
npm run build:js      # 构建 JS（生产环境）
npm run build         # 构建所有资源
npm run watch:css     # 监听 CSS 变化（开发环境）
```

### 文件结构

```
Lared/
├── assets/
│   ├── css/
│   │   ├── tailwind.input.css    # Tailwind 源文件（编辑此文件）
│   │   ├── tailwind.css          # 编译输出（自动生成）
│   │   └── main.css              # 主题自定义样式
│   ├── js/
│   │   ├── app.js                # 主题核心 JS
│   │   └── pjax.min.js           # PJAX 库
│   ├── fonts/                    # 效果字体
│   ├── images/                   # 图片资源
│   │   ├── bilibili/             # Bilibili 表情 PNG
│   │   └── useragenticons/       # UA 识别 SVG 图标
│   ├── json/                     # Emoji / 缓存 JSON
│   └── music/                    # 音乐播放器资源
├── inc/                          # PHP 功能模块
├── templates/                    # 页面模板
├── docs/                         # 文档
├── functions.php                 # 主题功能入口
├── style.css                     # 主题声明头
└── ...
```

---

## 常见问题

### 首页不显示文章？

检查 后台 → **设置** → **阅读** → 确保选择了 **您的最新文章** 且显示数量 ≥ 1。

### 音乐播放器不显示？

Xplayer 是独立插件，需要在 **插件** 页面安装并启用，然后在 **设置 → Xplayer** 中配置播放列表。

### Memos 动态不显示？

检查：
1. Memos 站点地址是否正确
2. API 地址是否可访问（私有实例需配置 Token）
3. 是否创建了使用「备忘录动态」模板的页面

### 代码高亮不生效？

代码块需指定语言：

````markdown
```php
// 指定语言后自动高亮
echo "Hello";
```
````

### PJAX 切换后功能失效？

PJAX 切换后所有模块会自动重新初始化（`reinitAfterPjax`）。如果出现问题，通常是第三方脚本未兼容，可在浏览器控制台查看报错。

### 评论回复表单不移动？

已内置多层兼容方案：`addComment.moveForm()` → 重新初始化重试 → 手动 DOM 操作兜底。如仍有问题，检查 `comment-reply.js` 是否正常加载。

### 如何修改页脚链接？

编辑 `footer.php` 中的链接地址和图标。

---

## 性能优化

### 推荐措施

1. **缓存插件** — WP Rocket / W3 Total Cache / WP Super Cache
2. **对象缓存** — Redis（在 `wp-config.php` 中设置 `define('WP_CACHE', true);`）
3. **图片优化** — 使用 WebP 格式 + 压缩尺寸 + 启用懒加载
4. **CDN 加速** — 将静态资源托管到 CDN
5. **HTTPS** — 启用 SSL 证书

### 已内置的优化

- CSS/JS 基于 `filemtime()` 自动版本号，无需手动清缓存
- RSS 聚合使用文件缓存
- Memos 使用 WordPress Transient 缓存
- 数据库查询使用 `no_found_rows` 优化
- 图片懒加载减少首屏请求
- 系统原生字体栈，零字体网络请求

---

## 安全建议

1. **保持更新** — 定期更新 WordPress 和主题
2. **HTTPS** — 启用 SSL 证书
3. **强密码** — 使用复杂的管理员密码
4. **定期备份** — 备份数据库和文件
5. **安全插件** — 考虑使用 Wordfence 或 Sucuri

### 主题内置安全措施

- 所有输出使用 `esc_html()` / `esc_attr()` / `esc_url()` 转义
- 所有输入使用 `sanitize_text_field()` / `esc_url_raw()` 过滤
- AJAX 请求使用 Nonce 验证
- 管理操作检查 `current_user_can()` 权限

---

## 响应式断点

主题已完全响应式，无需额外配置。

| 设备 | 宽度 |
|------|------|
| 桌面 | > 1024px |
| 平板 | 768px – 1024px |
| 手机 | < 768px |

---

*本文档基于 Lared v1.0.9，最后更新 2026 年 2 月 25 日。*
