/**
 * Lared Editor Admin — 排版指南
 *
 * 包含：
 *   0. 共享工具函数
 *   1. TinyMCE 插件注册（排版指南）
 *   2. 排版指南（TinyMCE / Quicktags 双模式）
 *
 * 注意：「插入图片」功能由 xalbum 插件管理，不在此文件中。
 */

/* ================================================================
 *  0. 共享工具
 * ================================================================ */
var LaredEditorUtils = (function () {
    'use strict';

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    function isTextMode() {
        var htmlTab = document.getElementById('content-html');
        return htmlTab && htmlTab.classList.contains('active');
    }

    function insertIntoTextarea(code) {
        var ta = document.getElementById('content');
        if (!ta) return false;
        var start = ta.selectionStart || 0;
        var end   = ta.selectionEnd   || start;
        ta.value = ta.value.substring(0, start) + code + ta.value.substring(end);
        ta.selectionStart = ta.selectionEnd = start + code.length;
        ta.focus();
        ta.dispatchEvent(new Event('input', { bubbles: true }));
        return true;
    }

    return { esc: esc, isTextMode: isTextMode, insertIntoTextarea: insertIntoTextarea };
})();


/* ================================================================
 *  1. TinyMCE 插件 —— 主题排版指南（轻量包装器）
 * ================================================================ */
(function () {
    'use strict';
    if (typeof tinymce === 'undefined') return;
    if (tinymce.PluginManager.lookup.laredThemeGuide) return; /* 防止重复注册 */

    tinymce.PluginManager.add('laredThemeGuide', function (editor) {
        editor.addButton('lared_theme_guide', {
            title: '主题排版指南',
            icon: 'help',
            onclick: function () {
                if (window.laredThemeGuide && window.laredThemeGuide.open) {
                    window.laredThemeGuide.open();
                }
            }
        });
    });
})();


/* ================================================================
 *  2. 排版指南 —— 双模式（TinyMCE + Quicktags）
 * ================================================================ */
(function () {
    'use strict';

    var esc = LaredEditorUtils.esc;
    var isTextMode = LaredEditorUtils.isTextMode;
    var insertIntoTextarea = LaredEditorUtils.insertIntoTextarea;

    /* 排版组件库 */
    var components = [
        {
            cat: '代码',
            items: [
                {
                    name: '代码块（语法高亮）',
                    desc: '支持 Prism.js 语言：javascript、python、css、html、php、bash、json 等。多行自动行号，超 25 行自动折叠。',
                    code: '<pre><code class="language-javascript">\nconst hello = "world";\nconsole.log(hello);\n</code></pre>'
                },
                {
                    name: '行内代码',
                    desc: '淡红底 + 深红字的行内代码。',
                    code: '<code>行内代码</code>'
                },
                {
                    name: '红色高亮代码',
                    desc: '红色背景 + 白色粗体文字，适合密钥、验证码等醒目内容。',
                    code: '<code class="code-red">ABC-123-XYZ</code>'
                }
            ]
        },
        {
            cat: '图片排版',
            items: [
                {
                    name: '2 图并排',
                    desc: '两张图片等宽并排，3:2 比例，间距 4px。',
                    code: '<div class="lared-grid-2">\n  <img src="图片1地址" alt="">\n  <img src="图片2地址" alt="">\n</div>'
                },
                {
                    name: '3 图并排',
                    desc: '三张图片等宽，1:1 比例，移动端自动响应。',
                    code: '<div class="lared-grid-3">\n  <img src="图片1地址" alt="">\n  <img src="图片2地址" alt="">\n  <img src="图片3地址" alt="">\n</div>'
                },
                {
                    name: '4 图网格',
                    desc: '2×2 网格布局，1:1 比例。',
                    code: '<div class="lared-grid-4">\n  <img src="图片1地址" alt="">\n  <img src="图片2地址" alt="">\n  <img src="图片3地址" alt="">\n  <img src="图片4地址" alt="">\n</div>'
                },
                {
                    name: '图片 + 说明文字',
                    desc: 'figure + figcaption 给图片添加底部说明。',
                    code: '<figure>\n  <img src="图片地址" alt="描述">\n  <figcaption>图片说明文字</figcaption>\n</figure>'
                },
                {
                    name: '网格图片 + 说明',
                    desc: '在网格布局内使用 figure + figcaption。',
                    code: '<div class="lared-grid-2">\n  <figure>\n    <img src="图片1地址" alt="">\n    <figcaption>说明1</figcaption>\n  </figure>\n  <figure>\n    <img src="图片2地址" alt="">\n    <figcaption>说明2</figcaption>\n  </figure>\n</div>'
                }
            ]
        },
        {
            cat: '文本排版',
            items: [
                {
                    name: '二级标题（H2）',
                    desc: '左侧红色竖线 + 底部分割线。',
                    code: '<h2>二级标题</h2>'
                },
                {
                    name: '三级标题（H3）',
                    desc: '左侧红色边框 + 缩进。',
                    code: '<h3>三级标题</h3>'
                },
                {
                    name: '引用块',
                    desc: '浅绿色背景 + 左侧绿色边框的引用。',
                    code: '<blockquote>\n  <p>这是引用内容，适合名言或注释说明。</p>\n</blockquote>'
                },
                {
                    name: '表格',
                    desc: '粉红主题表格，表头深色背景，隔行变色。',
                    code: '<table>\n  <thead>\n    <tr><th>列1</th><th>列2</th><th>列3</th></tr>\n  </thead>\n  <tbody>\n    <tr><td>数据1</td><td>数据2</td><td>数据3</td></tr>\n    <tr><td>数据4</td><td>数据5</td><td>数据6</td></tr>\n  </tbody>\n</table>'
                }
            ]
        },
        {
            cat: '短代码',
            items: [
                {
                    name: '下载按钮',
                    desc: '渐变卡片 + 红色下载按钮 + 文件信息标签。可选参数：dl_size、dl_format、dl_version、dl_note。',
                    code: '[download_button dl_url="https://example.com/file.zip" dl_name="文件名称" dl_text="立即下载" dl_size="12.5 MB" dl_format="ZIP" dl_version="v2.1" dl_note="解压密码：1234"]'
                },
                {
                    name: '代码运行器',
                    desc: '在线代码沙箱预览，支持 HTML/CSS/JS 标签页。',
                    code: '[code_runner height="300" show_code="yes" title="示例"]\n<html><h1>Hello World</h1></html>\n<css>h1 { color: red; font-family: sans-serif; }</css>\n<js>document.querySelector("h1").onclick = () => alert("Click!");</js>\n[/code_runner]'
                }
            ]
        },
        {
            cat: '多媒体',
            items: [
                {
                    name: '视频播放器',
                    desc: '自动增强为 Plyr 播放器。',
                    code: '<video src="视频地址.mp4" controls></video>'
                },
                {
                    name: '音频播放器',
                    desc: '自动增强为 Plyr 播放器。',
                    code: '<audio src="音频地址.mp3" controls></audio>'
                }
            ]
        }
    ];

    function switchToTextAndInsert(code, callback) {
        var htmlTab = document.getElementById('content-html');
        if (htmlTab && !htmlTab.classList.contains('active')) htmlTab.click();
        setTimeout(function () {
            insertIntoTextarea(code);
            if (callback) callback();
        }, 120);
    }

    /* 模态窗 UI */
    function openGuide() {
        var old = document.getElementById('lared-guide-backdrop');
        if (old) old.remove();

        var backdrop = document.createElement('div');
        backdrop.id = 'lared-guide-backdrop';
        backdrop.className = 'lared-guide-backdrop';

        var cats = [];
        components.forEach(function (g) { if (cats.indexOf(g.cat) === -1) cats.push(g.cat); });

        var tabsHtml = '';
        cats.forEach(function (cat, i) {
            tabsHtml += '<button type="button" class="lared-guide-tab' + (i === 0 ? ' is-active' : '') + '" data-cat="' + esc(cat) + '">' + esc(cat) + '</button>';
        });

        var panelsHtml = '';
        cats.forEach(function (cat, ci) {
            panelsHtml += '<div class="lared-guide-panel" data-cat="' + esc(cat) + '"' + (ci > 0 ? ' style="display:none;"' : '') + '>';
            components.forEach(function (g) {
                if (g.cat !== cat) return;
                g.items.forEach(function (item) {
                    panelsHtml += '<div class="lared-guide-card">'
                        + '<div class="lared-guide-card-header">'
                        +   '<span class="lared-guide-card-title">' + esc(item.name) + '</span>'
                        +   '<div class="lared-guide-card-actions">'
                        +     '<button type="button" class="lared-guide-copy-btn" title="复制代码到剪贴板">复制</button>'
                        +     '<button type="button" class="lared-guide-insert-btn" title="插入代码到编辑器（自动切换文本模式）">插入</button>'
                        +   '</div>'
                        + '</div>'
                        + '<p class="lared-guide-card-desc">' + esc(item.desc) + '</p>'
                        + '<pre class="lared-guide-code"><code>' + esc(item.code) + '</code></pre>'
                        + '</div>';
                });
            });
            panelsHtml += '</div>';
        });

        var modeLabel = isTextMode() ? '文本模式' : '可视化模式';
        var modeClass = isTextMode() ? 'is-text' : 'is-visual';

        backdrop.innerHTML =
            '<div class="lared-guide-modal">'
            + '<div class="lared-guide-header">'
            +   '<div class="lared-guide-header-left">'
            +     '<span class="lared-guide-title">📋 主题排版指南</span>'
            +     '<span class="lared-guide-mode ' + modeClass + '">当前：' + modeLabel + '</span>'
            +   '</div>'
            +   '<button type="button" class="lared-guide-close" title="关闭">&times;</button>'
            + '</div>'
            + '<div class="lared-guide-notice">💡 点击「插入」会自动切换到文本模式并将代码插入光标位置</div>'
            + '<div class="lared-guide-tabs">' + tabsHtml + '</div>'
            + '<div class="lared-guide-body">' + panelsHtml + '</div>'
            + '</div>';

        document.body.appendChild(backdrop);
        requestAnimationFrame(function () { backdrop.classList.add('is-visible'); });

        function closeGuide() {
            backdrop.classList.remove('is-visible');
            setTimeout(function () { backdrop.remove(); }, 200);
        }
        backdrop.querySelector('.lared-guide-close').addEventListener('click', closeGuide);
        backdrop.addEventListener('click', function (ev) { if (ev.target === backdrop) closeGuide(); });

        var escHandler = function (ev) {
            if (ev.key === 'Escape') { closeGuide(); document.removeEventListener('keydown', escHandler); }
        };
        document.addEventListener('keydown', escHandler);

        /* Tab 切换 */
        var tabEls = backdrop.querySelectorAll('.lared-guide-tab');
        var panels = backdrop.querySelectorAll('.lared-guide-panel');
        for (var i = 0; i < tabEls.length; i++) {
            tabEls[i].addEventListener('click', function () {
                var cat = this.getAttribute('data-cat');
                for (var j = 0; j < tabEls.length; j++) tabEls[j].classList.toggle('is-active', tabEls[j] === this);
                for (var k = 0; k < panels.length; k++) panels[k].style.display = panels[k].getAttribute('data-cat') === cat ? '' : 'none';
            });
        }

        /* 复制 / 插入 */
        backdrop.addEventListener('click', function (ev) {
            var btn = ev.target;
            if (!btn.classList) return;

            var card = btn.closest('.lared-guide-card');
            if (!card) return;

            var codeEl = card.querySelector('.lared-guide-code code');
            if (!codeEl) return;
            var code = codeEl.textContent;

            if (btn.classList.contains('lared-guide-copy-btn')) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(code).then(function () { feedback(btn, '已复制 ✓'); });
                } else {
                    var tmp = document.createElement('textarea');
                    tmp.value = code;
                    tmp.style.cssText = 'position:fixed;left:-9999px;';
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand('copy');
                    tmp.remove();
                    feedback(btn, '已复制 ✓');
                }
            }

            if (btn.classList.contains('lared-guide-insert-btn')) {
                closeGuide();
                if (isTextMode()) {
                    insertIntoTextarea(code);
                    feedback(btn, '已插入 ✓');
                } else {
                    switchToTextAndInsert(code, function () { feedback(btn, '已插入 ✓'); });
                }
            }
        });

        function feedback(btn, text) {
            var orig = btn.textContent;
            btn.textContent = text;
            btn.style.color = '#16a34a';
            setTimeout(function () { btn.textContent = orig; btn.style.color = ''; }, 1500);
        }
    }

    /* 暴露全局接口 */
    window.laredThemeGuide = { open: openGuide };

    /* Quicktags 按钮（全局标记防重复） */
    function registerGuideQT() {
        if (typeof QTags !== 'undefined' && !window._laredGuideQTDone) {
            window._laredGuideQTDone = true;
            QTags.addButton('lared_theme_guide', '排版指南', function () { openGuide(); });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', registerGuideQT);
    } else {
        registerGuideQT();
    }
})();
