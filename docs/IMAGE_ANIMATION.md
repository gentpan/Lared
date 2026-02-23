# 图片加载动画使用说明

> Lared WordPress 主题支持图片加载动画效果，让页面更具视觉吸引力。

---

## 简介

图片加载动画可以在图片从网络加载完成后，以优雅的方式显示在页面上，而不是突兀地"闪现"。

### 两种动画效果

| 效果 | 描述 | 时长 |
|------|------|------|
| **淡入效果** | 从完全透明渐变到完全不透明 | 1.5秒 |
| **像素化显现** | 从模糊、灰度、暗淡逐渐变得清晰彩色 | 2秒 |

---

## 使用方法

### 后台设置

```
后台 → 外观 → 主题设置 → 图片加载动画
```

选择以下选项之一：

- **无动画** - 图片直接显示（默认）
- **淡入效果** - 柔和自然的透明度渐变
- **像素化显现** - 艺术感的模糊到清晰效果

---

## 效果对比

### 淡入效果 (Fade)

```css
@keyframes img-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
```

**特点：**
- ✅ 简单自然，不分散注意力
- ✅ 适合内容型网站
- ✅ 性能开销小

**视觉效果：**
```
透明度: 0% → 100%
时间: 1.5秒
缓动: ease
```

### 像素化显现 (Pixelate)

```css
@keyframes img-pixelate {
    0% {
        filter: blur(20px) grayscale(100%) brightness(0);
        opacity: 0;
    }
    30% {
        filter: blur(15px) grayscale(80%) brightness(0.3);
        opacity: 0.5;
    }
    60% {
        filter: blur(8px) grayscale(40%) brightness(0.7);
        opacity: 0.8;
    }
    100% {
        filter: blur(0) grayscale(0) brightness(1);
        opacity: 1;
    }
}
```

**特点：**
- ✅ 具有艺术感和设计感
- ✅ 适合摄影、设计类网站
- ✅ 给用户"图片在逐渐变清晰"的期待感

**视觉效果：**
```
阶段1 (0-30%): 高度模糊 + 黑白 + 暗淡
阶段2 (30-60%): 中等模糊 + 部分彩色 + 较亮
阶段3 (60-100%): 清晰 + 全彩 + 正常亮度
```

---

## 技术实现

### CSS 动画

动画样式定义在 `style.css` 中：

```css
/* 基础状态 - 所有动画图片初始隐藏 */
img[data-img-animation] {
    opacity: 0;
}

/* 淡入效果 */
img[data-img-animation="fade"].img-loaded {
    animation: img-fade-in 1.5s forwards;
}

/* 像素化显现效果 */
img[data-img-animation="pixelate"].img-loaded {
    animation: img-pixelate 2s forwards;
}
```

### JavaScript 控制

JavaScript 在 `app.js` 中处理：

```javascript
function initImageLoadAnimation() {
    // 从 html 标签读取设置
    var animationType = document.documentElement.getAttribute('data-img-animation');
    
    // 为所有图片添加动画
    document.querySelectorAll('img').forEach(function (img) {
        // 跳过已加载的图片
        if (img.complete) {
            img.classList.add('img-loaded');
            return;
        }
        
        // 设置动画类型
        img.setAttribute('data-img-animation', animationType);
        
        // 加载完成后添加动画类
        img.addEventListener('load', function () {
            img.classList.add('img-loaded');
        });
    });
}
```

### 设置传递

主题设置通过 HTML 属性传递给 JavaScript：

```html
<html data-img-animation="fade">
```

---

## 排除规则

以下图片不会应用动画：

| 类型 | 原因 |
|------|------|
| Hero 主图 | 首屏图片需要立即显示 |
| Emoji 表情 | 小图标不需要动画 |
| 头像图片 | 避免头像区域闪烁 |
| 代码块内图片 | 技术文档不需要动画 |
| 已缓存图片 | 瞬间加载，无需动画 |

---

## 性能考虑

### GPU 加速

动画使用 CSS transforms 和 opacity，会触发 GPU 加速：

```css
/* 这些属性会触发 GPU 加速 */
opacity: 0 → 1;
filter: blur() grayscale() brightness();
```

### 避免重排

动画不会触发布局重排（reflow），只触发重绘（repaint）。

### 与懒加载配合

图片动画与懒加载完美配合：

1. 图片进入视口（懒加载触发）
2. 开始从网络加载
3. 加载完成
4. 触发动画效果

---

## 自定义动画

如果你想修改或添加新的动画效果，可以编辑 `style.css`：

### 修改动画时长

```css
/* 将淡入效果改为 3 秒 */
img[data-img-animation="fade"].img-loaded {
    animation: img-fade-in 3s forwards;
}
```

### 添加新效果

```css
/* 缩放效果示例 */
@keyframes img-zoom-in {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

img[data-img-animation="zoom"].img-loaded {
    animation: img-zoom-in 1s forwards;
}
```

然后在 `functions.php` 中添加选项：

```php
<option value="zoom" <?php selected($image_load_animation, 'zoom'); ?>>缩放效果</option>
```

---

## 常见问题

### Q: 动画会影响页面性能吗？

**A:** 不会。动画使用 CSS transforms 和 opacity，都是 GPU 加速的属性，性能开销很小。

### Q: 为什么有些图片没有动画？

**A:** 以下图片被排除：
- 首屏 Hero 图片
- Emoji 和头像
- 代码块内的图片
- 浏览器已缓存的图片（瞬间加载）

### Q: 动画在移动端也有效吗？

**A:** 是的，动画在所有现代浏览器中都有效，包括移动端。

### Q: 可以只为特定图片启用动画吗？

**A:** 可以，手动添加属性：

```html
<img src="image.jpg" data-img-animation="fade">
```

然后添加 `.img-loaded` 类触发动画。

### Q: PJAX 切换页面后动画还生效吗？

**A:** 是的，主题会在 PJAX 完成后重新初始化动画。

---

## 兼容性

| 浏览器 | 支持状态 |
|--------|----------|
| Chrome 60+ | ✅ 完全支持 |
| Firefox 55+ | ✅ 完全支持 |
| Safari 12+ | ✅ 完全支持 |
| Edge 79+ | ✅ 完全支持 |

不支持动画的浏览器会正常显示图片，只是没有动画效果。

---

## 相关文件

| 文件 | 说明 |
|------|------|
| `style.css` | 动画 CSS 定义 |
| `assets/js/app.js` | 动画初始化 JavaScript |
| `functions.php` | 后台设置 |
| `header.php` | 设置传递 |

---

## 更新日志

### 2026-02-23

- ✨ 新增图片加载动画功能
- ✨ 支持淡入效果
- ✨ 支持像素化显现效果
- ✨ 后台可视化设置

---

**推荐设置**: 内容型网站使用"淡入效果"，摄影/设计类网站使用"像素化显现"。
