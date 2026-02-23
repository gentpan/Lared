# 文章图片加载占位与 Loading 效果

> Lared 主题支持文章内图片的占位框和 Loading 加载动画，提升用户体验。

---

## 功能特点

- ✅ **提前占位** - 图片加载前显示占位框，避免布局跳动 (CLS)
- ✅ **Tailwind 风格 Loading** - 使用 Tailwind CSS 风格的旋转圆圈
- ✅ **自适应比例** - 支持 4:3、16:9、1:1 等常见图片比例
- ✅ **主题色适配** - Loading 圆圈使用主题强调色
- ✅ **错误处理** - 加载失败显示错误图标

### Loading 样式

```
┌─────────────────┐
│   ⭮ 旋转圆圈    │  ← Tailwind 风格
│   border-4      │     rounded-full
│   border-gray   │     animate-spin
│   border-t-强调色│
└─────────────────┘
```

---

## 实现原理

### HTML 结构

```html
<figure class="img-loading-wrapper" style="aspect-ratio: 16/9;">
    <!-- Loading 圆圈 -->
    <div class="img-loading-spinner">
        <div class="spinner-circle"></div>
    </div>
    <!-- 实际图片 -->
    <img src="image.jpg" class="img-loading-target" loading="lazy">
</figure>
```

### 加载流程

```
1. 页面渲染 → 显示占位框 + Loading 圆圈
2. 图片加载中 → Loading 动画旋转
3. 加载完成 → 图片淡入显示，Loading 消失
4. 加载失败 → 显示错误图标
```

---

## 技术细节

### CSS 样式

```css
/* 占位容器 */
.img-loading-wrapper {
    position: relative;
    background: #f0f0f0;
    overflow: hidden;
}

/* Loading 圆圈 */
.img-loading-spinner {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.spinner-circle {
    width: 40px;
    height: 40px;
    border: 4px solid #e5e7eb;           /* border-4 border-gray-300 */
    border-top-color: var(--color-accent, #f53004);  /* border-t 主题色 */
    border-radius: 9999px;                /* rounded-full */
    animation: pan-loading-spin 1s linear infinite;  /* animate-spin */
}

@keyframes pan-loading-spin {
    to { transform: rotate(360deg); }
}
```

### JavaScript 控制

```javascript
// 监听图片加载状态
img.addEventListener('load', () => {
    img.classList.add('is-loaded');
    wrapper.querySelector('.img-loading-spinner').remove();
});

img.addEventListener('error', () => {
    wrapper.classList.add('is-error');
});
```

---

## 使用方法

### 自动应用

文章内的图片会自动添加 loading 效果，无需手动操作。

### 手动添加

在编辑器中使用 HTML：

```html
<figure class="img-loading-wrapper" style="aspect-ratio: 16/9;">
    <div class="img-loading-spinner">
        <div class="spinner-circle"></div>
    </div>
    <img src="image.jpg" class="img-loading-target" loading="lazy">
</figure>
```

### 自定义比例

通过 `aspect-ratio` 设置占位框比例：

```css
/* 16:9 宽屏 */
aspect-ratio: 16/9;

/* 4:3 标准 */
aspect-ratio: 4/3;

/* 1:1 方形 */
aspect-ratio: 1/1;

/* 3:2 照片 */
aspect-ratio: 3/2;
```

---

## 与懒加载配合

图片 loading 效果与懒加载完美配合：

```html
<img src="image.jpg" 
     loading="lazy" 
     class="img-loading-target">
```

流程：
1. 图片进入视口（懒加载触发）
2. 显示占位框 + Loading 圆圈
3. 开始加载图片
4. 加载完成，图片显示

---

## 浏览器兼容性

| 特性 | 兼容性 |
|------|--------|
| aspect-ratio | Chrome 88+, Firefox 89+, Safari 15+ |
| CSS Animation | 所有现代浏览器 |

不支持的浏览器会正常显示图片，只是没有 loading 效果。

---

## 性能优化

### 减少 CLS (Cumulative Layout Shift)

占位框提前占用空间，避免图片加载后页面跳动：

```css
/* 关键 CSS - 内联在 head 中 */
.img-loading-wrapper {
    aspect-ratio: 16/9; /* 提前确定高度 */
}
```

### 加载优先级

首屏图片不使用 loading 效果，直接显示：

```javascript
// 跳过首屏图片
if (img.closest('.hero-section')) {
    return;
}
```

---

## 自定义样式

### 修改 Loading 颜色

```css
.spinner-circle {
    border-top-color: #ff0000; /* 改为红色 */
}
```

### 修改占位背景

```css
.img-loading-wrapper {
    background: #000; /* 改为黑色 */
}
```

### 修改动画速度

```css
.spinner-circle {
    animation-duration: 0.5s; /* 加速到 0.5 秒 */
}
```

---

## 相关文件

| 文件 | 说明 |
|------|------|
| `style.css` | Loading 效果 CSS |
| `assets/js/app.js` | 图片 loading 初始化 |
| `functions.php` | the_content 过滤器 |

---

## 更新日志

### 2026-02-23

- ✨ 新增文章图片 loading 占位效果
- ✨ 旋转圆圈加载动画
- ✨ 自适应 aspect-ratio 占位框
- ✨ 错误状态处理
