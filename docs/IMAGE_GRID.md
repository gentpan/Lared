# 文章图片网格布局

在文章中使用 `pan-grid-2`、`pan-grid-3`、`pan-grid-4` CSS 类，可将多张图片按网格排列展示。

## 使用方法

在 WordPress 编辑器中切换到 **HTML 模式**（或插入「自定义 HTML」块），用 `<div>` 包裹图片并添加对应 class。

### pan-grid-2：两图并排

两张图片 50%/50% 等宽并排，适合对比图、前后对照。

```html
<div class="pan-grid-2">
  <img src="图片1地址" alt="说明1">
  <img src="图片2地址" alt="说明2">
</div>
```

效果：
| 图片 1 | 图片 2 |
|--------|--------|

### pan-grid-3：三图等宽

三张图片等宽并排，适合流程步骤、系列展示。

```html
<div class="pan-grid-3">
  <img src="图片1地址" alt="说明1">
  <img src="图片2地址" alt="说明2">
  <img src="图片3地址" alt="说明3">
</div>
```

效果：
| 图片 1 | 图片 2 | 图片 3 |
|--------|--------|--------|

> 移动端自动变为两图并排 + 第三张独占一行。

### pan-grid-4：四图方阵

四张图片 2×2 网格排列，适合产品展示、相册。

```html
<div class="pan-grid-4">
  <img src="图片1地址" alt="说明1">
  <img src="图片2地址" alt="说明2">
  <img src="图片3地址" alt="说明3">
  <img src="图片4地址" alt="说明4">
</div>
```

效果：
| 图片 1 | 图片 2 |
|--------|--------|
| 图片 3 | 图片 4 |

## 带说明文字

可用 `<figure>` + `<figcaption>` 为每张图片添加文字说明：

```html
<div class="pan-grid-2">
  <figure>
    <img src="图片1地址" alt="说明1">
    <figcaption>这是第一张图的说明</figcaption>
  </figure>
  <figure>
    <img src="图片2地址" alt="说明2">
    <figcaption>这是第二张图的说明</figcaption>
  </figure>
</div>
```

## 交互特性

- **Hover 效果**：鼠标悬停时图片微放大并提亮
- **点击查看**：支持 ViewImage 点击放大查看
- **响应式**：移动端自动调整布局（三图变为 2+1）

## 注意事项

- 图片数量应与 class 名称匹配（grid-2 放 2 张，grid-3 放 3 张，grid-4 放 4 张）
- 图片会自动裁切填充：grid-2 为 3:2 比例，grid-3 和 grid-4 为 1:1 正方形
- 可在古腾堡编辑器中使用「自定义 HTML」块来书写
