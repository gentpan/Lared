# 代码运行器使用说明

> Lared 主题内置代码运行器，支持 HTML/CSS/JS 实时预览。

---

## 基础用法

### 方式 1：属性参数

```
[code_runner html="<h1>Hello World</h1>" css="h1{color:red;}" js="console.log('hi')"]
```

### 方式 2：标签内容（推荐）

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
    border-radius: 4px;
    cursor: pointer;
  }
  #btn:hover {
    opacity: 0.9;
  }
</css>
<js>
  document.getElementById('btn').onclick = function() {
    document.getElementById('result').textContent = '你好！';
  };
</js>
[/code_runner]
```

---

## 参数说明

| 参数 | 说明 | 默认值 |
|------|------|--------|
| `html` | HTML 代码 | 空 |
| `css` | CSS 样式 | 空 |
| `js` | JavaScript 代码 | 空 |
| `height` | 预览区域高度(px) | 300 |
| `show_code` | 是否显示代码(yes/no) | yes |
| `title` | 标题 | "代码预览" |

---

## 使用示例

### 示例 1：简单按钮

```
[code_runner title="红色按钮" height="200"]
<html>
  <button class="btn">点击我</button>
</html>
<css>
  .btn {
    padding: 12px 24px;
    background: #f53004;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
  }
</css>
[/code_runner]
```

### 示例 2：带动画效果

```
[code_runner title="CSS 动画" height="300"]
<html>
  <div class="box"></div>
</html>
<css>
  .box {
    width: 100px;
    height: 100px;
    background: #f53004;
    animation: rotate 2s linear infinite;
  }
  @keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
</css>
[/code_runner]
```

### 示例 3：表单交互

```
[code_runner title="表单验证" height="350"]
<html>
  <input type="text" id="name" placeholder="输入名字">
  <button onclick="greet()">问候</button>
  <p id="message"></p>
</html>
<css>
  input, button {
    padding: 10px;
    margin: 5px;
    font-size: 14px;
  }
  #message {
    color: #f53004;
    margin-top: 10px;
  }
</css>
<js>
  function greet() {
    var name = document.getElementById('name').value;
    if (name) {
      document.getElementById('message').textContent = '你好，' + name + '！';
    } else {
      document.getElementById('message').textContent = '请输入名字';
    }
  }
</js>
[/code_runner]
```

---

## 界面说明

```
┌─────────────────────────────────────┐
│ [icon] 代码预览     [HTML] [CSS] [JS]│  ← 标题栏
├─────────────────────────────────────┤
│                                     │
│         实时预览区域                 │  ← iframe 预览
│         (高度可自定义)               │
│                                     │
├─────────────────────────────────────┤
│ [HTML] [CSS] [JS]                   │  ← 代码标签页
├─────────────────────────────────────┤
│ <button>点击我</button>             │  ← 代码展示
│                                     │
│ .btn { color: red; }                │
└─────────────────────────────────────┘
```

---

## 注意事项

1. **安全性** - 代码在 iframe 沙盒中运行，不会影响主页面
2. **外部资源** - 无法加载外部 CSS/JS 文件（如 CDN）
3. **控制台** - 可以打开浏览器控制台查看 console.log 输出
4. **响应式** - 预览区域宽度自适应，高度可自定义

---

## 常见问题

### Q: 为什么我的 JS 不生效？

**A:** 检查是否有语法错误。代码运行器会自动捕获并显示错误。

### Q: 可以使用 jQuery 吗？

**A:** 不可以直接加载 jQuery，需要内联引入或改用原生 JS。

### Q: 代码会保存吗？

**A:** 代码保存在文章短代码中，刷新页面后仍然有效。

---

## 更新日志

### 2026-02-23

- ✨ 新增代码运行器功能
- ✨ 支持 HTML/CSS/JS 实时预览
- ✨ 代码高亮展示
- ✨ Tab 切换查看不同代码
