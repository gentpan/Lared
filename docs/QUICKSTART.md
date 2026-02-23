# 快速上手指南

> Lared WordPress 主题 v1.0.0

---

## ⚡ 5分钟快速配置

### 1. 安装并启用主题

```
后台 → 外观 → 主题 → 添加新主题 → 上传 → 启用
```

### 2. 设置网站基本信息

```
后台 → 设置 → 常规
├── 站点标题: 你的博客名称
└── 站点副标题: 简短描述
```

### 3. 创建导航菜单

```
后台 → 外观 → 菜单
├── 创建菜单
├── 添加页面/分类/链接
└── 显示位置: Primary Menu
```

### 4. 配置主题设置

```
后台 → 外观 → 主题设置
```

#### 基础配置

| 设置项 | 推荐值 | 说明 |
|--------|--------|------|
| Memos 站点地址 | `https://memos.yourdomain.com` | 留空不显示 Memos |
| Umami 统计代码 | 粘贴完整 script 标签 | 留空不启用 |
| 博客十年起始日期 | 选择博客创建日期 | 用于显示进度条 |

#### 音乐播放器（可选）

**方式一: 使用网易云/QQ音乐歌单**
```
音乐页歌单地址: https://music.163.com/#/playlist?id=xxxxx
```

**方式二: 自定义播放列表**
```json
[
  {
    "name": "歌曲名称",
    "artist": "艺术家",
    "url": "https://example.com/music.mp3",
    "cover": "https://example.com/cover.jpg"
  }
]
```

---

## 📄 创建页面

### 必须页面

| 页面 | 模板 | 用途 |
|------|------|------|
| 首页 | 默认 | 文章列表 |
| Memos | 备忘录动态 | 显示 Memos 动态 |

### 可选页面

| 页面 | 模板 | 用途 |
|------|------|------|
| 归档 | 文章归档 | 文章时间线 |
| 订阅 | 订阅聚合 | RSS 聚合展示 |
| 友链 | 友情链接 | 友情链接列表 |
| 关于 | 关于主页 | 关于博客 |

**创建步骤**:
```
后台 → 页面 → 新建页面
├── 输入标题
├── 右侧选择模板
└── 发布
```

---

## 🏷️ 设置分类图标

1. 进入 `后台 → 文章 → 分类`
2. 编辑分类
3. 在描述中添加图标 HTML:

```html
<i class="fa-solid fa-plane"></i>
```

**可用图标类**:
- `fa-solid fa-*` - 实心图标
- `fa-regular fa-*` - 常规图标
- `fa-brands fa-*` - 品牌图标

查找图标: https://fontawesome.com/icons

---

## 🎨 自定义样式

### 修改主题色

编辑 `style.css`:

```css
:root {
    --color-accent: #f53004;  /* 主色 - 默认红色 */
    --color-title:  #21201d;  /* 标题颜色 */
    --color-body:   #63635e;  /* 正文颜色 */
}
```

### 修改字体

编辑 `functions.php` 中 `pan_assets()` 函数:

```php
// 修改 Google Fonts 地址
wp_enqueue_style(
    'pan-fonts',
    'https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700;900&display=swap',
    [],
    null
);
```

---

## 🔧 常见问题

### Q: 首页不显示文章？

**A**: 检查以下设置:
```
后台 → 设置 → 阅读
├── 您的最新文章 ✓
└── 文章页显示至少 1 篇
```

### Q: 音乐播放器不显示？

**A**: 确保已配置:
1. 歌单地址 或 自定义播放列表 JSON
2. Meting API 可用（可留空使用默认）

### Q: Memos 动态不显示？

**A**: 检查:
1. Memos 站点地址是否正确
2. API 地址是否可访问（如私有需配置 Token）
3. Memos 版本是否兼容

### Q: 代码高亮不生效？

**A**: 代码块需指定语言:

````markdown
```php
// PHP 代码
```

```javascript
// JavaScript 代码
```
````

### Q: 如何修改页脚链接？

**A**: 编辑 `footer.php` 中的链接地址。

---

## 🚀 性能优化

### 启用缓存

1. **WordPress 缓存插件**
   - WP Rocket
   - W3 Total Cache
   - WP Super Cache

2. **对象缓存** (推荐 Redis)
   ```php
   // wp-config.php
   define('WP_CACHE', true);
   ```

### 图片优化

- 使用 WebP 格式
- 启用懒加载
- 压缩图片尺寸

### CDN 加速

修改 CDN 常量（`wp-config.php`）:

```php
define('PAN_CDN_FONTS', 'https://your-cdn.com/fonts.css');
define('PAN_CDN_STATIC', 'https://your-cdn.com/npm');
```

---

## 📱 移动端适配

主题已完全响应式，无需额外配置。

测试断点:
- 桌面: > 1024px
- 平板: 768px - 1024px
- 手机: < 768px

---

## 🔒 安全建议

1. **保持更新**: 定期更新 WordPress 和主题
2. **HTTPS**: 启用 SSL 证书
3. **强密码**: 使用复杂的管理员密码
4. **备份**: 定期备份数据库和文件
5. **安全插件**: 考虑使用 Wordfence 或 Sucuri

---

## 📞 获取帮助

- 📖 完整文档: 查看 `README.md`
- 🐛 问题反馈: 主题设置页面
- 💡 功能建议: 通过邮件联系

---

**开始你的博客之旅吧！** 🎉
