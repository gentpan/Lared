(function () {
    'use strict';

     /* =========================
         Home Modules
         ========================= */

     /* home-article-tabs.js */
    function initTabs() {
        var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-article-tab]'));
        var panels = Array.prototype.slice.call(document.querySelectorAll('[data-article-panel]'));

        if (!tabs.length || !panels.length) {
            return;
        }

        var activatePanel = function (targetId) {
            tabs.forEach(function (tab) {
                var active = tab.getAttribute('data-target') === targetId;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                var active = panel.id === targetId;
                panel.classList.toggle('is-active', active);
                panel.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
        };

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var targetId = tab.getAttribute('data-target');
                if (targetId) {
                    activatePanel(targetId);
                }
            });
        });
    }

    /* home-hero-switch.js */
    function initHeroSwitch() {
        var heroItems = Array.prototype.slice.call(document.querySelectorAll('[data-hero-item]'));
        if (!heroItems.length) {
            return;
        }

        var mainTitle    = document.querySelector('[data-hero-main-title]');
        var mainLink     = document.querySelector('[data-hero-main-link]');
        var mainImage    = document.querySelector('[data-hero-main-image]');
        var mainFallback = document.querySelector('[data-hero-main-fallback]');
        var mainBadge    = document.querySelector('[data-hero-main-badge]');
        var heroArticle  = mainImage ? mainImage.closest('article') : null;

        if (!mainImage || !mainTitle || !mainLink) {
            return;
        }

        /* Hero 图片加载完成 → 移除骨架屏 shimmer */
        function onHeroImgLoaded() {
            if (heroArticle) { heroArticle.classList.add('hero-img-loaded'); }
        }
        if (mainImage.complete && mainImage.naturalWidth > 0) {
            onHeroImgLoaded();
        } else {
            mainImage.addEventListener('load', onHeroImgLoaded, { once: true });
        }

        /* 当前展示的文章 ID — 从 DOM 读取初始值 */
        var currentPostId = heroArticle ? parseInt(heroArticle.getAttribute('data-hero-current-post-id'), 10) || 0 : 0;

        /* Update right-side display with given data */
        var applyHeroData = function (item, title, image, link, badge, badgeKey, postId) {
            if (postId) {
                currentPostId = postId;
            }
            mainTitle.textContent = title;
            mainLink.setAttribute('href', link);

            if (image) {
                /* 重置模糊 → 切换图片 → 加载完成后淡入 */
                if (heroArticle) { heroArticle.classList.remove('hero-img-loaded'); }
                mainImage.setAttribute('src', image);
                mainImage.setAttribute('alt', title);
                if (mainFallback) { mainFallback.classList.add('hidden'); }
                if (mainImage.complete && mainImage.naturalWidth > 0) {
                    onHeroImgLoaded();
                } else {
                    mainImage.addEventListener('load', onHeroImgLoaded, { once: true });
                }
            } else {
                if (heroArticle) { heroArticle.classList.remove('hero-img-loaded'); }
                if (mainFallback) { mainFallback.classList.remove('hidden'); }
            }

            if (mainBadge) {
                mainBadge.textContent = badge;
                if (badgeKey) {
                    mainBadge.setAttribute('data-hero-main-badge-key', badgeKey);
                } else {
                    mainBadge.removeAttribute('data-hero-main-badge-key');
                }
            }

            heroItems.forEach(function (button) {
                var isActive = button === item;
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                button.classList.toggle('is-hero-active', isActive);
            });

            /* 淡入过渡 */
            if (heroArticle) {
                heroArticle.style.opacity = '1';
            }
        };

        /* Activate a hero tab: fetch random article type via AJAX */
        var activateItem = function (item, skipAjax) {
            /* Always highlight the tab immediately */
            heroItems.forEach(function (button) {
                var isActive = button === item;
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                button.classList.toggle('is-hero-active', isActive);
            });

            /* On first load (skipAjax), use server-rendered data */
            if (skipAjax) {
                applyHeroData(
                    item,
                    item.getAttribute('data-hero-title') || '',
                    item.getAttribute('data-hero-image') || '',
                    item.getAttribute('data-hero-link') || '',
                    item.getAttribute('data-hero-badge') || '',
                    item.getAttribute('data-hero-badge-key') || '',
                    0
                );
                return;
            }

            /* AJAX: randomly pick one of the 4 article types for this tab's category */
            if (!window.LaredAjax || !window.LaredAjax.ajaxUrl) {
                applyHeroData(
                    item,
                    item.getAttribute('data-hero-title') || '',
                    item.getAttribute('data-hero-image') || '',
                    item.getAttribute('data-hero-link') || '',
                    item.getAttribute('data-hero-badge') || '',
                    item.getAttribute('data-hero-badge-key') || '',
                    0
                );
                return;
            }

            /* 淡出当前内容 */
            if (heroArticle) {
                heroArticle.style.opacity = '0.4';
            }

            var fd = new FormData();
            fd.append('action', 'lared_hero_random_article');
            fd.append('nonce', window.LaredAjax.nonce);
            fd.append('taxonomy', item.getAttribute('data-hero-taxonomy') || '');
            fd.append('term_id', item.getAttribute('data-hero-term-id') || '0');
            fd.append('exclude_id', String(currentPostId || 0));

            fetch(window.LaredAjax.ajaxUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success && res.data) {
                        var d = res.data;
                        /* Update data attrs cache */
                        item.setAttribute('data-hero-title', d.title);
                        item.setAttribute('data-hero-image', d.image);
                        item.setAttribute('data-hero-link', d.permalink || '');
                        item.setAttribute('data-hero-badge', d.type_label);
                        item.setAttribute('data-hero-badge-key', d.type_key);

                        applyHeroData(
                            item,
                            d.title,
                            d.image,
                            d.permalink || item.getAttribute('data-hero-link') || '',
                            d.type_label,
                            d.type_key,
                            d.post_id || 0
                        );
                    } else {
                        /* AJAX 返回失败，恢复显示 */
                        if (heroArticle) { heroArticle.style.opacity = '1'; }
                    }
                })
                .catch(function () {
                    /* Fallback to cached data attrs */
                    if (heroArticle) { heroArticle.style.opacity = '1'; }
                    applyHeroData(
                        item,
                        item.getAttribute('data-hero-title') || '',
                        item.getAttribute('data-hero-image') || '',
                        item.getAttribute('data-hero-link') || '',
                        item.getAttribute('data-hero-badge') || '',
                        item.getAttribute('data-hero-badge-key') || '',
                        0
                    );
                });
        };

        heroItems.forEach(function (item) {
            item.addEventListener('click', function (event) {
                event.preventDefault();
                currentIndex = heroItems.indexOf(item);
                activateItem(item);
                startAutoSwitch();
            });
        });

        var initial = heroItems.find(function (item) {
            return item.getAttribute('aria-pressed') === 'true';
        }) || heroItems[0];

        var currentIndex = heroItems.indexOf(initial);
        if (currentIndex < 0) {
            currentIndex = 0;
        }

        var timerKey = '__panHeroSwitchTimer';
        var clearAutoSwitch = function () {
            if (window[timerKey]) {
                window.clearInterval(window[timerKey]);
                window[timerKey] = null;
            }
        };

        var startAutoSwitch = function () {
            clearAutoSwitch();

            if (heroItems.length <= 1) {
                return;
            }

            window[timerKey] = window.setInterval(function () {
                if (!document.body.contains(heroItems[0])) {
                    clearAutoSwitch();
                    return;
                }

                currentIndex = (currentIndex + 1) % heroItems.length;
                activateItem(heroItems[currentIndex]);
            }, 8000);
        };

        activateItem(initial, true);
        startAutoSwitch();
    }

    /* home-article-toc.js — removed, scrollbar only */
    function initToc() {
        var sections = Array.prototype.slice.call(document.querySelectorAll('.home-article'));

        if (!sections.length) {
            return;
        }

        sections.forEach(function (section) {
            var content = section.querySelector('[data-article-scroll]');
            var scrollbarThumb = section.querySelector('.home-article-scrollbar-thumb');

            if (!content) {
                return;
            }

            var updateScrollbar = function () {
                if (!scrollbarThumb) {
                    return;
                }

                var scrollHeight = content.scrollHeight;
                var clientHeight = content.clientHeight;

                if (scrollHeight <= clientHeight) {
                    scrollbarThumb.style.opacity = '0';
                    return;
                }

                scrollbarThumb.style.opacity = '1';

                var thumbHeight = Math.max((clientHeight / scrollHeight) * clientHeight, 40);
                var maxOffset = clientHeight - thumbHeight;
                var offset = (content.scrollTop / (scrollHeight - clientHeight)) * maxOffset;

                scrollbarThumb.style.height = thumbHeight + 'px';
                scrollbarThumb.style.transform = 'translateY(' + offset + 'px)';
            };

            content.addEventListener('scroll', updateScrollbar, { passive: true });
            window.addEventListener('resize', updateScrollbar);
            updateScrollbar();
        });
    }

     /* =========================
         Global Modules
         ========================= */

     /* back-to-top.js */
    function initBackToTop() {
        var button = document.querySelector('[data-back-to-top]');
        if (!button || button.getAttribute('data-back-to-top-ready') === '1') {
            return;
        }

        button.setAttribute('data-back-to-top-ready', '1');

        var updateVisibility = function () {
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
            var docHeight = Math.max(
                document.body.scrollHeight,
                document.documentElement.scrollHeight,
                document.body.offsetHeight,
                document.documentElement.offsetHeight
            );
            var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
            var scrollable = Math.max(docHeight - viewportHeight, 1);
            var ratio = scrollTop / scrollable;

            button.classList.toggle('is-visible', ratio >= 0.3);
        };

        button.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', updateVisibility, { passive: true });
        window.addEventListener('resize', updateVisibility);
        updateVisibility();
    }

     /* =========================
         Single Modules
         ========================= */

     /* single-article-toc.js */
    function initSingleSideToc() {
        var toc = document.querySelector('.single-side-toc');
        var content = document.querySelector('.single-article-content');
        var banner = document.querySelector('.single-top-banner');

        if (!toc || !content || toc.getAttribute('data-single-toc-ready') === '1') {
            return;
        }

        toc.setAttribute('data-single-toc-ready', '1');

        /* ── Banner visibility → show / hide TOC ── */
        if (banner) {
            var bannerObserver = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        // Banner out of view → show TOC; banner visible → hide TOC
                        toc.classList.toggle('is-visible', !entry.isIntersecting);
                    });
                },
                { root: null, threshold: 0 }
            );
            bannerObserver.observe(banner);
        }

        var links = Array.prototype.slice.call(toc.querySelectorAll('[data-single-toc-link]'));
        var headings = Array.prototype.slice.call(content.querySelectorAll('h2[id], h3[id]'));

        if (!links.length || !headings.length) {
            return;
        }

        var activateLink = function (id) {
            links.forEach(function (link) {
                var isActive = link.getAttribute('href') === '#' + id;
                link.classList.toggle('is-active', isActive);
            });
        };

        links.forEach(function (link) {
            link.addEventListener('click', function (event) {
                var href = link.getAttribute('href') || '';
                if (!href.startsWith('#')) {
                    return;
                }

                var targetId = href.slice(1);
                var target = document.getElementById(targetId);
                if (!target) {
                    return;
                }

                event.preventDefault();
                var top = target.getBoundingClientRect().top + window.pageYOffset - 86;
                window.scrollTo({ top: top, behavior: 'smooth' });
                activateLink(targetId);
            });
        });

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        activateLink(entry.target.id);
                    }
                });
            },
            {
                root: null,
                rootMargin: '-90px 0px -62% 0px',
                threshold: 0.1,
            }
        );

        headings.forEach(function (heading) {
            observer.observe(heading);
        });
    }

     /* =========================
         Global Services
         ========================= */

    /* ── Plyr：文章内 video/audio 增强 ── */
    function initPlyr(scope) {
        if (typeof window.Plyr === 'undefined') return;
        var root = scope || document;
        var targets = root.querySelectorAll('.page-content video, .page-content audio, .single-article-content video, .single-article-content audio, .entry-content video, .entry-content audio');
        if (!targets.length) return;
        targets.forEach(function (el) {
            if (el.__plyrInstance) return;
            el.__plyrInstance = new Plyr(el, {
                controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'fullscreen'],
                settings: ['speed'],
                speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
                i18n: {
                    play: '播放',
                    pause: '暂停',
                    mute: '静音',
                    unmute: '取消静音',
                    settings: '设置',
                    speed: '速度',
                    normal: '正常',
                    enterFullscreen: '全屏',
                    exitFullscreen: '退出全屏',
                },
            });
        });
    }

    function updateCommentStats(data) {
        if (!data) {
            return;
        }

        var stats = document.querySelector('.comments-header__stats');
        if (!stats) {
            return;
        }

        var nums = stats.querySelectorAll('.comments-header__num');
        if (nums.length >= 2) {
            if (typeof data.commenterCount !== 'undefined') {
                nums[0].textContent = String(data.commenterCount);
            }
            if (typeof data.commentTotal !== 'undefined') {
                nums[1].textContent = String(data.commentTotal);
            }
        }
    }

    function insertCommentHtml(data) {
        if (!data || !data.html) {
            return null;
        }

        var parent = Number(data.parent || 0);

        if (parent > 0) {
            var parentNode = document.getElementById('comment-' + parent);
            if (parentNode) {
                var childrenList = parentNode.querySelector('ol.children');
                if (!childrenList) {
                    childrenList = document.createElement('ol');
                    childrenList.className = 'children';
                    parentNode.appendChild(childrenList);
                }
                childrenList.insertAdjacentHTML('beforeend', data.html);
                return document.getElementById('comment-' + String(data.commentId || ''));
            }
        }

        var commentList = document.querySelector('.comment-list');
        if (!commentList) {
            var commentsInner = document.querySelector('.comments-inner');
            if (!commentsInner) {
                return;
            }

            commentList = document.createElement('ol');
            commentList.className = 'comment-list';
            var commentFormWrap = commentsInner.querySelector('#respond');
            if (commentFormWrap) {
                commentsInner.insertBefore(commentList, commentFormWrap);
            } else {
                commentsInner.appendChild(commentList);
            }
        }

        commentList.insertAdjacentHTML('beforeend', data.html);
        return document.getElementById('comment-' + String(data.commentId || ''));
    }

    function markNewCommentHint(commentNode) {
        if (!commentNode || commentNode.querySelector('.lared-comment-edit-btn')) {
            return;
        }

        var metaNode = commentNode.querySelector('.comment-header, .comment-meta, .comment-metadata');
        if (!metaNode) {
            return;
        }

        // 编辑按钮 + 倒计时
        var editBtn = document.createElement('button');
        editBtn.type = 'button';
        editBtn.className = 'lared-comment-edit-btn';
        editBtn.innerHTML = '<i class="fa-regular fa-pen-to-square"></i> 编辑 <span class="lared-edit-countdown">60s</span>';
        metaNode.appendChild(editBtn);

        var remaining = 60;
        var countdownSpan = editBtn.querySelector('.lared-edit-countdown');
        var timer = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(timer);
                editBtn.remove();
                return;
            }
            countdownSpan.textContent = remaining + 's';
        }, 1000);

        // 保存评论原文用于编辑（还原表情代码 + 处理 HTML 段落）
        var commentId = commentNode.id ? commentNode.id.replace('comment-', '') : '';
        var contentNode = commentNode.querySelector('.comment-content');
        var originalContent = contentNode ? extractEditableContent(contentNode) : '';

        editBtn.addEventListener('click', function () {
            startEditComment(commentId, originalContent, commentNode, editBtn, timer);
        });
    }

    /**
     * 从渲染后的评论 DOM 中提取可编辑的纯文本。
     * - <img class="lared-emoji" data-code=":xxx:"> → :xxx:
     * - <p>...</p> → 段落间用换行分隔
     * - <br> → 换行
     * - 其他 HTML 标签去除
     */
    function extractEditableContent(node) {
        var clone = node.cloneNode(true);

        // 1. 将表情 img 替换为其 data-code
        var emojis = clone.querySelectorAll('img.lared-emoji[data-code]');
        emojis.forEach(function (img) {
            var code = img.getAttribute('data-code');
            img.replaceWith(code);
        });

        // 2. 在 <p> 结尾插入换行标记
        var paragraphs = clone.querySelectorAll('p');
        paragraphs.forEach(function (p) {
            p.insertAdjacentText('afterend', '\n');
        });

        // 3. <br> 转为换行
        var brs = clone.querySelectorAll('br');
        brs.forEach(function (br) {
            br.replaceWith('\n');
        });

        // 4. 获取文本并清理多余空行
        var text = clone.textContent || '';
        text = text.replace(/\n{3,}/g, '\n\n').trim();
        return text;
    }

    // ====== 评论编辑机制 ======
    var _editingCommentId = null;

    function startEditComment(commentId, content, commentNode, editBtn, timer) {
        var form = document.getElementById('commentform');
        if (!form) return;

        var textarea = form.querySelector('#comment');
        var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
        if (!textarea || !submitBtn) return;

        // 如果已经在编辑模式，先清理旧的取消按钮
        var existingCancel = form.querySelector('.lared-comment-cancel-edit');
        if (existingCancel) existingCancel.remove();

        // 清理旧的编辑高亮
        var oldEditing = document.querySelector('.lared-comment-editing');
        if (oldEditing) oldEditing.classList.remove('lared-comment-editing');

        // 标记编辑模式
        _editingCommentId = commentId;
        form.setAttribute('data-editing', commentId);

        // 填充内容
        textarea.value = content;
        textarea.focus();

        // 修改按钮文字
        var originalSubmitText = submitBtn.value || submitBtn.textContent;
        if (submitBtn.tagName === 'INPUT') {
            submitBtn.value = '更新评论';
        } else {
            submitBtn.textContent = '更新评论';
        }

        // 添加取消编辑按钮
        var cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'lared-comment-cancel-edit';
        cancelBtn.textContent = '取消编辑';
        submitBtn.parentNode.insertBefore(cancelBtn, submitBtn);

        // 高亮正在编辑的评论
        commentNode.classList.add('lared-comment-editing');

        // 滚动到表单
        var formRect = form.getBoundingClientRect();
        var targetY = window.pageYOffset + formRect.top - 100;
        window.scrollTo({ top: targetY, behavior: 'smooth' });

        cancelBtn.addEventListener('click', function () {
            cancelEditComment(form, submitBtn, originalSubmitText, cancelBtn, commentNode);
        });
    }

    function cancelEditComment(form, submitBtn, originalText, cancelBtn, commentNode) {
        _editingCommentId = null;
        form.removeAttribute('data-editing');

        var textarea = form.querySelector('#comment');
        if (textarea) textarea.value = '';

        if (submitBtn.tagName === 'INPUT') {
            submitBtn.value = originalText;
        } else {
            submitBtn.textContent = originalText;
        }

        cancelBtn.remove();
        commentNode.classList.remove('lared-comment-editing');
    }

    function submitEditComment(commentId, newContent, form, submitBtn, originalSubmitText) {
        if (!window.LaredAjax || !window.LaredAjax.commentEditNonce) return;

        submitBtn.disabled = true;
        submitBtn.classList.add('is-loading');
        var savedText = submitBtn.value || submitBtn.textContent;
        if (submitBtn.tagName === 'INPUT') submitBtn.value = '';
        else submitBtn.textContent = '';

        var formData = new FormData();
        formData.append('action', 'lared_edit_comment');
        formData.append('nonce', window.LaredAjax.commentEditNonce);
        formData.append('comment_id', commentId);
        formData.append('comment', newContent);

        // 游客需要附带邮箱用于身份验证
        var emailField = form.querySelector('#email');
        if (emailField) {
            formData.append('author_email', emailField.value);
        }

        var startTime = Date.now();

        fetch(window.LaredAjax.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (result) {
            var elapsed = Date.now() - startTime;
            var delay = Math.max(0, 800 - elapsed);

            setTimeout(function () {
                resetSubmitButton(submitBtn, originalSubmitText);

                if (!result || !result.success) {
                    showToast((result && result.data && result.data.message) || '编辑失败', 'error');
                    return;
                }

                showToast('评论已更新', 'success');

                // 替换评论 HTML
                var commentNode = document.getElementById('comment-' + commentId);
                if (commentNode && result.data.html) {
                    var temp = document.createElement('div');
                    temp.innerHTML = result.data.html;
                    var newArticle = temp.querySelector('.comment-body');
                    var oldArticle = commentNode.querySelector('.comment-body');
                    if (newArticle && oldArticle) {
                        oldArticle.innerHTML = newArticle.innerHTML;
                    }
                    // 重新添加编辑按钮（如果还在60秒内）
                    markNewCommentHint(commentNode);
                    initCommentExpand();
                }

                // 清理编辑状态
                var cancelBtn = form.querySelector('.lared-comment-cancel-edit');
                if (cancelBtn) cancelBtn.remove();
                _editingCommentId = null;
                form.removeAttribute('data-editing');

                var textarea = form.querySelector('#comment');
                if (textarea) textarea.value = '';

                if (submitBtn.tagName === 'INPUT') submitBtn.value = originalSubmitText;
                else submitBtn.textContent = originalSubmitText;
            }, delay);
        })
        .catch(function () {
            var elapsed = Date.now() - startTime;
            var delay = Math.max(0, 800 - elapsed);
            setTimeout(function () {
                resetSubmitButton(submitBtn, originalSubmitText);
                showToast('编辑失败，请重试', 'error');
            }, delay);
        });
    }

    function scrollToNewComment(commentNode) {
        if (!commentNode || typeof commentNode.scrollIntoView !== 'function') {
            return;
        }

        // 直接跳转到评论附近，不从页面顶部平滑滚动
        var rect = commentNode.getBoundingClientRect();
        var targetY = window.pageYOffset + rect.top - (window.innerHeight / 3);
        window.scrollTo({ top: targetY, behavior: 'instant' });

        commentNode.classList.add('lared-comment-newly-added');
        window.setTimeout(function () {
            commentNode.classList.remove('lared-comment-newly-added');
        }, 1500);
    }

    // ====== 回复链接安全拦截（防止导航刷新） ======
    // 使用事件委托，确保动态插入的评论回复链接也能正常工作

    function updateReplyTitleText(text) {
        var replyTitle = document.getElementById('reply-title');
        if (!replyTitle) return;
        var nodes = replyTitle.childNodes;
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType === 3 && nodes[i].textContent.trim().length > 0) {
                nodes[i].textContent = text;
                return;
            }
        }
    }

    document.addEventListener('click', function (e) {
        // —— 处理回复链接 ——
        var link = e.target.closest('.comment-reply-link');
        if (link) {
            e.preventDefault();
            e.stopImmediatePropagation();

            // 如果正在编辑评论，先取消编辑模式
            if (_editingCommentId) {
                var form = document.getElementById('commentform');
                if (form) {
                    var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                    var cancelBtn = form.querySelector('.lared-comment-cancel-edit');
                    var editingNode = document.querySelector('.lared-comment-editing');

                    _editingCommentId = null;
                    form.removeAttribute('data-editing');
                    var textarea = form.querySelector('#comment');
                    if (textarea) textarea.value = '';
                    if (submitBtn) {
                        if (submitBtn.tagName === 'INPUT') submitBtn.value = '提交评论';
                        else submitBtn.textContent = '提交评论';
                    }
                    if (cancelBtn) cancelBtn.remove();
                    if (editingNode) editingNode.classList.remove('lared-comment-editing');
                }
            }

            // 获取被回复评论的昵称
            var commentBody = link.closest('.comment-body') || link.closest('li[id^="comment-"]');
            var authorName = '';
            if (commentBody) {
                var authorEl = commentBody.querySelector('.comment-author-name');
                if (authorEl) authorName = authorEl.textContent.trim();
            }

            // 调用 WordPress 的 moveForm 移动评论表单
            var commId = link.getAttribute('data-belowelement');
            var parentId = link.getAttribute('data-commentid');
            var respondId = link.getAttribute('data-respondelement');
            var postId = link.getAttribute('data-postid');
            var replyTo = link.getAttribute('data-replyto') || '';

            if (commId && parentId && respondId && postId) {
                // 优先使用 WordPress 内置 addComment.moveForm
                var moved = false;
                if (window.addComment && typeof window.addComment.moveForm === 'function') {
                    try {
                        window.addComment.moveForm(commId, parentId, respondId, postId, replyTo);
                        moved = true;
                    } catch (err) {
                        // moveForm 内部可能因 cancelElement 未初始化而抛出异常
                        // 尝试重新 init 后再试一次
                        if (typeof window.addComment.init === 'function') {
                            try {
                                window.addComment.init();
                                window.addComment.moveForm(commId, parentId, respondId, postId, replyTo);
                                moved = true;
                            } catch (err2) { /* fallback below */ }
                        }
                    }
                }

                // Fallback：如果 addComment 不可用或 moveForm 失败，手动移动表单
                if (!moved) {
                    var addBelowEl = document.getElementById(commId);
                    var respondEl = document.getElementById(respondId);
                    var parentField = document.getElementById('comment_parent');
                    var postField = document.getElementById('comment_post_ID');
                    var cancelEl = document.getElementById('cancel-comment-reply-link');

                    if (addBelowEl && respondEl && parentField) {
                        // 创建占位符（如果不存在）以便取消回复时还原位置
                        var tempId = 'wp-temp-form-div';
                        if (!document.getElementById(tempId)) {
                            var placeholder = document.createElement('div');
                            placeholder.id = tempId;
                            placeholder.style.display = 'none';
                            respondEl.parentNode.insertBefore(placeholder, respondEl);
                        }

                        parentField.value = parentId;
                        if (postField && postId) postField.value = postId;
                        addBelowEl.parentNode.insertBefore(respondEl, addBelowEl.nextSibling);
                        if (cancelEl) cancelEl.style.display = '';
                    }
                }
            }

            // 更新标题为"回复 昵称"
            if (authorName) {
                updateReplyTitleText(' 回复 ' + authorName + ' ');
            }
            return;
        }

        // —— 处理取消回复链接 —— 恢复标题文字 + fallback 还原表单位置
        var cancelLink = e.target.closest('#cancel-comment-reply-link');
        if (cancelLink) {
            updateReplyTitleText(' 发表评论');

            // Fallback：如果 WordPress addComment 未接管，手动还原表单
            var tempPlaceholder = document.getElementById('wp-temp-form-div');
            var respondEl = document.getElementById('respond');
            if (tempPlaceholder && respondEl && tempPlaceholder.parentNode) {
                tempPlaceholder.parentNode.replaceChild(respondEl, tempPlaceholder);
                var parentField = document.getElementById('comment_parent');
                if (parentField) parentField.value = '0';
                cancelLink.style.display = 'none';
                e.preventDefault();
            }
        }
    }, true); // 捕获阶段，优先于其他 click 处理器

    // ====== 评论内容展开/收起（已移除） ======
    function initCommentExpand() {}

    // ====== 评论表情面板 ======
    var _emojiData = null;
    var _emojiFetching = false;
    var _emojiCallbacks = [];

    function getEmojiData(callback) {
        if (_emojiData) {
            callback(_emojiData);
            return;
        }
        _emojiCallbacks.push(callback);
        if (_emojiFetching) return;
        _emojiFetching = true;

        var themeUrl = (window.LaredAjax && window.LaredAjax.themeUrl) || '';
        if (!themeUrl) {
            // 回退：从已有 link/script 标签推断主题路径
            var links = document.querySelectorAll('link[href*="/themes/Lared/"]');
            if (links.length > 0) {
                var m = links[0].href.match(/(.*\/themes\/Lared)\//);
                if (m) themeUrl = m[1];
            }
        }
        if (!themeUrl) {
            _emojiFetching = false;
            return;
        }

        fetch(themeUrl + '/assets/json/bilibili-emojis.json')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                _emojiData = { map: data, themeUrl: themeUrl };
                _emojiCallbacks.forEach(function (cb) { cb(_emojiData); });
                _emojiCallbacks = [];
            })
            .catch(function () {
                _emojiFetching = false;
            });
    }

    function initEmojiPanel() {
        var panels = document.querySelectorAll('.lared-emoji-panel');
        if (!panels.length) return;

        panels.forEach(function (panel) {
            if (panel.getAttribute('data-emoji-ready') === '1') return;
            panel.setAttribute('data-emoji-ready', '1');

            var emojiBar = panel.closest('.lared-emoji-bar');
            var form = panel.closest('form') || panel.closest('.comment-form');
            if (!form) return;

            var toggle = emojiBar ? emojiBar.querySelector('.lared-emoji-toggle') : null;
            var textarea = form.querySelector('textarea#comment') || form.querySelector('textarea');
            if (!toggle || !textarea) return;

            // 点击切换面板
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var isOpen = panel.style.display !== 'none';
                if (isOpen) {
                    panel.style.display = 'none';
                    toggle.classList.remove('is-active');
                    return;
                }

                // 首次打开时加载表情
                if (!panel.children.length) {
                    getEmojiData(function (data) {
                        buildEmojiGrid(panel, data, textarea);
                    });
                }

                panel.style.display = 'grid';
                toggle.classList.add('is-active');
            });

            // 点击外部关闭
            document.addEventListener('click', function (e) {
                if (!emojiBar.contains(e.target)) {
                    panel.style.display = 'none';
                    toggle.classList.remove('is-active');
                }
            });
        });
    }

    function buildEmojiGrid(panel, data, textarea) {
        var map = data.map;
        var themeUrl = data.themeUrl;
        var fragment = document.createDocumentFragment();

        Object.keys(map).forEach(function (code) {
            var emoji = map[code];
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'lared-emoji-item';
            btn.title = emoji.name;
            btn.innerHTML = '<img src="' + themeUrl + '/assets/images/bilibili/' + emoji.file + '" alt="' + emoji.name + '" loading="lazy">';

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                insertAtCursor(textarea, code);
                // 点击表情后自动关闭面板
                panel.style.display = 'none';
                var toggle = panel.closest('.lared-emoji-bar');
                if (toggle) {
                    var toggleBtn = toggle.querySelector('.lared-emoji-toggle');
                    if (toggleBtn) toggleBtn.classList.remove('is-active');
                }
            });

            fragment.appendChild(btn);
        });

        panel.appendChild(fragment);
    }

    function insertAtCursor(textarea, text) {
        textarea.focus();
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var before = textarea.value.substring(0, start);
        var after = textarea.value.substring(end);
        textarea.value = before + text + after;
        var newPos = start + text.length;
        textarea.setSelectionRange(newPos, newPos);

        // 触发 input 事件以便框架感知变化
        var evt = new Event('input', { bubbles: true });
        textarea.dispatchEvent(evt);
    }

    // ====== Toast 提示 ======
    var _toastMessages = [
        '评论成功，恭喜发财！',
        '评论成功，万事如意！',
        '发表成功，好运连连！',
        '评论成功，大吉大利！',
        '发表成功，心想事成！',
        '评论成功，笑口常开！',
        '发表成功，前程似锦！',
        '评论成功，一帆风顺！',
        '发表成功，福星高照！',
        '评论成功，步步高升！',
    ];

    var _toastErrorMessages = [
        '提交失败，请稍后重试',
    ];

    function showToast(message, type) {
        var existing = document.querySelector('.lared-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.className = 'lared-toast lared-toast--' + (type || 'success');
        toast.textContent = message;
        document.body.appendChild(toast);

        // 强制重排后添加动画类
        toast.offsetHeight;
        toast.classList.add('is-visible');

        setTimeout(function () {
            toast.classList.remove('is-visible');
            toast.classList.add('is-hiding');
            setTimeout(function () { toast.remove(); }, 400);
        }, 2500);
    }

    function getRandomToast(arr) {
        return arr[Math.floor(Math.random() * arr.length)];
    }

    // ====== 回头访客编辑信息切换 ======
    function initEditInfoToggle() {
        var toggle = document.querySelector('.lared-edit-info-toggle');
        if (!toggle || toggle.getAttribute('data-edit-info-ready') === '1') return;
        toggle.setAttribute('data-edit-info-ready', '1');

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var form = document.querySelector('.comment-form.lared-returning-guest');
            if (!form) return;
            form.classList.toggle('lared-show-fields');
        });
    }

    // ====== 邮箱输入自动识别头像 ======
    function md5(s){function rl(v,b){return(v<<b)|(v>>>(32-b))}function au(x,y){var x8=x&0x80000000,y8=y&0x80000000,x4=x&0x40000000,y4=y&0x40000000,r=(x&0x3FFFFFFF)+(y&0x3FFFFFFF);if(x4&y4)return r^0x80000000^x8^y8;if(x4|y4){if(r&0x40000000)return r^0xC0000000^x8^y8;else return r^0x40000000^x8^y8}else return r^x8^y8}function F(x,y,z){return(x&y)|((~x)&z)}function G(x,y,z){return(x&z)|(y&(~z))}function H(x,y,z){return x^y^z}function I(x,y,z){return y^(x|(~z))}function t(fn,a,b,c,d,x,s,ac){return au(rl(au(a,au(au(fn,x),ac)),s),b)}function FF(a,b,c,d,x,s,ac){return t(F(b,c,d),a,b,c,d,x,s,ac)}function GG(a,b,c,d,x,s,ac){return t(G(b,c,d),a,b,c,d,x,s,ac)}function HH(a,b,c,d,x,s,ac){return t(H(b,c,d),a,b,c,d,x,s,ac)}function II(a,b,c,d,x,s,ac){return t(I(b,c,d),a,b,c,d,x,s,ac)}function cw(s){var l=s.length,n=((l+8-(l+8)%64)/64+1)*16,w=Array(n-1),p=0,c=0;while(c<l){var wc=(c-c%4)/4;p=(c%4)*8;w[wc]=w[wc]|(s.charCodeAt(c)<<p);c++}w[(c-c%4)/4]=w[(c-c%4)/4]|(0x80<<((c%4)*8));w[n-2]=l<<3;w[n-1]=l>>>29;return w}function wh(v){var r='',t,b,i;for(i=0;i<=3;i++){b=(v>>>(i*8))&255;t='0'+b.toString(16);r+=t.substr(t.length-2,2)}return r}var x=cw(s),a=0x67452301,b=0xEFCDAB89,c=0x98BADCFE,d=0x10325476;for(var k=0;k<x.length;k+=16){var A=a,B=b,C=c,D=d;a=FF(a,b,c,d,x[k],7,0xD76AA478);d=FF(d,a,b,c,x[k+1],12,0xE8C7B756);c=FF(c,d,a,b,x[k+2],17,0x242070DB);b=FF(b,c,d,a,x[k+3],22,0xC1BDCEEE);a=FF(a,b,c,d,x[k+4],7,0xF57C0FAF);d=FF(d,a,b,c,x[k+5],12,0x4787C62A);c=FF(c,d,a,b,x[k+6],17,0xA8304613);b=FF(b,c,d,a,x[k+7],22,0xFD469501);a=FF(a,b,c,d,x[k+8],7,0x698098D8);d=FF(d,a,b,c,x[k+9],12,0x8B44F7AF);c=FF(c,d,a,b,x[k+10],17,0xFFFF5BB1);b=FF(b,c,d,a,x[k+11],22,0x895CD7BE);a=FF(a,b,c,d,x[k+12],7,0x6B901122);d=FF(d,a,b,c,x[k+13],12,0xFD987193);c=FF(c,d,a,b,x[k+14],17,0xA679438E);b=FF(b,c,d,a,x[k+15],22,0x49B40821);a=GG(a,b,c,d,x[k+1],5,0xF61E2562);d=GG(d,a,b,c,x[k+6],9,0xC040B340);c=GG(c,d,a,b,x[k+11],14,0x265E5A51);b=GG(b,c,d,a,x[k],20,0xE9B6C7AA);a=GG(a,b,c,d,x[k+5],5,0xD62F105D);d=GG(d,a,b,c,x[k+10],9,0x2441453);c=GG(c,d,a,b,x[k+15],14,0xD8A1E681);b=GG(b,c,d,a,x[k+4],20,0xE7D3FBC8);a=GG(a,b,c,d,x[k+9],5,0x21E1CDE6);d=GG(d,a,b,c,x[k+14],9,0xC33707D6);c=GG(c,d,a,b,x[k+3],14,0xF4D50D87);b=GG(b,c,d,a,x[k+8],20,0x455A14ED);a=GG(a,b,c,d,x[k+13],5,0xA9E3E905);d=GG(d,a,b,c,x[k+2],9,0xFCEFA3F8);c=GG(c,d,a,b,x[k+7],14,0x676F02D9);b=GG(b,c,d,a,x[k+12],20,0x8D2A4C8A);a=HH(a,b,c,d,x[k+5],4,0xFFFA3942);d=HH(d,a,b,c,x[k+8],11,0x8771F681);c=HH(c,d,a,b,x[k+11],16,0x6D9D6122);b=HH(b,c,d,a,x[k+14],23,0xFDE5380C);a=HH(a,b,c,d,x[k+1],4,0xA4BEEA44);d=HH(d,a,b,c,x[k+4],11,0x4BDECFA9);c=HH(c,d,a,b,x[k+7],16,0xF6BB4B60);b=HH(b,c,d,a,x[k+10],23,0xBEBFBC70);a=HH(a,b,c,d,x[k+13],4,0x289B7EC6);d=HH(d,a,b,c,x[k],11,0xEAA127FA);c=HH(c,d,a,b,x[k+3],16,0xD4EF3085);b=HH(b,c,d,a,x[k+6],23,0x4881D05);a=HH(a,b,c,d,x[k+9],4,0xD9D4D039);d=HH(d,a,b,c,x[k+12],11,0xE6DB99E5);c=HH(c,d,a,b,x[k+15],16,0x1FA27CF8);b=HH(b,c,d,a,x[k+2],23,0xC4AC5665);a=II(a,b,c,d,x[k],6,0xF4292244);d=II(d,a,b,c,x[k+7],10,0x432AFF97);c=II(c,d,a,b,x[k+14],15,0xAB9423A7);b=II(b,c,d,a,x[k+5],21,0xFC93A039);a=II(a,b,c,d,x[k+12],6,0x655B59C3);d=II(d,a,b,c,x[k+3],10,0x8F0CCC92);c=II(c,d,a,b,x[k+10],15,0xFFEFF47D);b=II(b,c,d,a,x[k+1],21,0x85845DD1);a=II(a,b,c,d,x[k+8],6,0x6FA87E4F);d=II(d,a,b,c,x[k+15],10,0xFE2CE6E0);c=II(c,d,a,b,x[k+6],15,0xA3014314);b=II(b,c,d,a,x[k+13],21,0x4E0811A1);a=II(a,b,c,d,x[k+4],6,0xF7537E82);d=II(d,a,b,c,x[k+11],10,0xBD3AF235);c=II(c,d,a,b,x[k+2],15,0x2AD7D2BB);b=II(b,c,d,a,x[k+9],21,0xEB86D391);a=au(a,A);b=au(b,B);c=au(c,C);d=au(d,D)}return(wh(a)+wh(b)+wh(c)+wh(d)).toLowerCase()}

    function initEmailAvatar() {
        var emailField = document.getElementById('email');
        var avatarWrap = document.getElementById('lared-title-avatar-wrap');
        if (!emailField || !avatarWrap) return;
        if (emailField.getAttribute('data-avatar-ready') === '1') return;
        emailField.setAttribute('data-avatar-ready', '1');

        var _defaultIcon = '<i class="fa-regular fa-comment-dots" style="color:var(--color-accent,#f53004);font-size:16px;"></i>';
        var _lastHash = '';
        // 如果页面加载时已有头像（回头访客 cookie），标记为已有头像状态
        var _hasInitialAvatar = !!avatarWrap.querySelector('img');

        function updateAvatar() {
            var email = (emailField.value || '').trim().toLowerCase();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                // 无效邮箱或已清空：恢复默认图标
                if (_lastHash !== '' || _hasInitialAvatar) {
                    _lastHash = '';
                    _hasInitialAvatar = false;
                    avatarWrap.innerHTML = _defaultIcon;
                }
                return;
            }
            var hash = md5(email);
            if (hash === _lastHash) return;
            _lastHash = hash;
            _hasInitialAvatar = false;
            var baseUrl = (window.LaredAjax && window.LaredAjax.avatarBaseUrl) || 'https://secure.gravatar.com/avatar/';
            avatarWrap.innerHTML = '<img class="lared-title-avatar" src="' + baseUrl + hash + '?s=96&d=mp" alt="">';
        }

        emailField.addEventListener('input', updateAvatar);
        emailField.addEventListener('change', updateAvatar);
        // 初始检查（可能有预填值）
        if (emailField.value) updateAvatar();
    }

    function initAjaxCommentSubmit() {
        var form = document.getElementById('commentform');
        if (!form || form.getAttribute('data-ajax-ready') === '1') {
            return;
        }

        if (!window.LaredAjax || !window.LaredAjax.ajaxUrl || !window.LaredAjax.commentSubmitNonce) {
            return;
        }

        form.setAttribute('data-ajax-ready', '1');

        var _isSubmitting = false;

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (_isSubmitting) return;

            // 阻止事件冒泡，防止 PJAX 拦截表单提交导致页面跳转
            event.stopPropagation();

            // 编辑模式：拦截提交，走编辑接口
            var editingId = form.getAttribute('data-editing');
            if (editingId && _editingCommentId) {
                _isSubmitting = true;
                var submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
                var textarea = form.querySelector('#comment');
                var originalText = submitButton.value || submitButton.textContent;
                submitEditComment(editingId, textarea.value, form, submitButton, originalText);
                _isSubmitting = false;
                return;
            }

            var submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
            if (!submitButton || submitButton.disabled) return;

            _isSubmitting = true;

            // 按钮进入 loading 状态
            submitButton.disabled = true;
            var originalText = submitButton.value || submitButton.textContent;
            submitButton.classList.add('is-loading');

            var startTime = Date.now();

            var formData = new FormData(form);
            formData.append('action', 'lared_submit_comment');
            formData.append('nonce', window.LaredAjax.commentSubmitNonce);

            fetch(window.LaredAjax.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData,
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (result) {
                    // 确保至少 1s 的 loading 展示
                    var elapsed = Date.now() - startTime;
                    var delay = Math.max(0, 1000 - elapsed);

                    setTimeout(function () {
                        // 恢复按钮
                        _isSubmitting = false;
                        resetSubmitButton(submitButton, originalText);

                        if (!result || !result.success) {
                            var errorMessage = (result && result.data && result.data.message)
                                ? result.data.message
                                : '提交失败，请稍后重试';
                            showToast(errorMessage || getRandomToast(_toastErrorMessages), 'error');
                            return;
                        }

                        // 成功 toast
                        if (result.data.approved) {
                            showToast(getRandomToast(_toastMessages), 'success');
                        } else {
                            showToast('评论已提交，审核通过后显示', 'success');
                        }

                        if (result.data.approved) {
                            var newCommentNode = insertCommentHtml(result.data);
                            updateCommentStats(result.data);
                            markNewCommentHint(newCommentNode);
                            initCommentExpand();

                            // 延迟滚动，让 toast 先展示
                            setTimeout(function () {
                                scrollToNewComment(newCommentNode);
                            }, 500);
                        }

                        var commentField = form.querySelector('#comment');
                        if (commentField) {
                            commentField.value = '';
                        }

                        // 提交成功后：隐藏信息字段，切换为回头访客状态
                        var authorField = form.querySelector('#author');
                        if (authorField && authorField.value) {
                            form.classList.add('lared-returning-guest');
                            form.classList.remove('lared-show-fields');

                            var commenterName = (result.data && result.data.commenterName) || authorField.value;
                            var titleMeta = document.querySelector('.lared-title-meta');
                            if (titleMeta) {
                                titleMeta.className = 'lared-title-meta lared-title-meta--returning';
                                titleMeta.innerHTML = '欢迎回来，<strong>' + commenterName + '</strong>'
                                    + ' <a href="#" class="lared-edit-info-toggle" onclick="return false;"><i class="fa-regular fa-pen-to-square" style="font-size:11px"></i> 编辑信息</a>';
                                initEditInfoToggle();
                            } else {
                                var replyTitle = document.querySelector('#reply-title');
                                if (replyTitle) {
                                    var newMeta = document.createElement('span');
                                    newMeta.className = 'lared-title-meta lared-title-meta--returning';
                                    newMeta.innerHTML = '欢迎回来，<strong>' + commenterName + '</strong>'
                                        + ' <a href="#" class="lared-edit-info-toggle" onclick="return false;"><i class="fa-regular fa-pen-to-square" style="font-size:11px"></i> 编辑信息</a>';
                                    replyTitle.appendChild(newMeta);
                                    initEditInfoToggle();
                                }
                            }
                        }

                        // 关闭表情面板
                        var emojiPanel = form.querySelector('.lared-emoji-panel');
                        var emojiToggle = form.querySelector('.lared-emoji-toggle');
                        if (emojiPanel) emojiPanel.style.display = 'none';
                        if (emojiToggle) emojiToggle.classList.remove('is-active');

                        // 回复成功后，将表单移回评论区底部（重置回复状态）
                        var cancelReplyBtn = document.getElementById('cancel-comment-reply-link');
                        var savedScrollY = window.pageYOffset || document.documentElement.scrollTop;

                        if (cancelReplyBtn && cancelReplyBtn.style.display !== 'none') {
                            cancelReplyBtn.click();
                        }

                        if (window.addComment && typeof window.addComment.init === 'function') {
                            window.addComment.init();
                        }

                        window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                    }, delay);
                })
                .catch(function () {
                    var elapsed = Date.now() - startTime;
                    var delay = Math.max(0, 1000 - elapsed);

                    setTimeout(function () {
                        _isSubmitting = false;
                        resetSubmitButton(submitButton, originalText);
                        showToast(getRandomToast(_toastErrorMessages), 'error');
                    }, delay);
                });
        });
    }

    function resetSubmitButton(btn, text) {
        btn.classList.remove('is-loading');
        btn.disabled = false;
        if (btn.tagName === 'INPUT') {
            btn.value = text;
        } else {
            btn.textContent = text;
        }
    }

    /* prism-enhance.js */
    function copyText(text) {
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            try {
                var textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', 'readonly');
                textarea.style.position = 'fixed';
                textarea.style.top = '-9999px';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                var ok = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (ok) {
                    resolve();
                    return;
                }

                reject(new Error('copy-failed'));
            } catch (error) {
                reject(error);
            }
        });
    }

    function ensureCopyButtons(root) {
        var scope = root || document;
        var nodes = Array.prototype.slice.call(scope.querySelectorAll('pre > code'));

        var MAX_VISIBLE_LINES = 20;

        var normalizeCode = function (source) {
            return String(source || '')
                .replace(/\r\n/g, '\n')
                .replace(/\n+$/g, '');
        };

        // 转义 HTML 特殊字符，防止代码内容被浏览器解析
        var escapeHtml = function (str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        };

        var getLineCount = function (source) {
            var normalized = normalizeCode(source);
            if (!normalized) {
                return 1;
            }

            return normalized.split('\n').length;
        };

        var isSingleLineCode = function (source) {
            var normalized = normalizeCode(source);

            return normalized.indexOf('\n') === -1;
        };

        nodes.forEach(function (codeEl) {
            var preEl = codeEl.parentElement;
            if (!preEl || preEl.getAttribute('data-lared-copy-ready') === '1') {
                return;
            }

            preEl.setAttribute('data-lared-copy-ready', '1');
            preEl.classList.add('lared-prism-pre');

            // 清除旧的行号（避免 PJAX 重复生成）
            var oldLineNumbers = preEl.querySelector('.line-numbers-rows');
            if (oldLineNumbers) {
                oldLineNumbers.remove();
            }

            // 去除代码首尾空行（编辑器/数据库存储常带多余换行，兼容 CRLF）
            var rawText = codeEl.textContent || '';
            var trimmedText = rawText.replace(/^[\r\n]+/, '').replace(/[\r\n]+$/, '');
            if (trimmedText !== rawText) {
                // 如果有 Prism token 子元素，只修剪 innerHTML 的首尾换行
                if (codeEl.children.length > 0 && codeEl.querySelector('span[class*="token"]')) {
                    codeEl.innerHTML = codeEl.innerHTML.replace(/^[\s\r\n]+/, '').replace(/[\s\r\n]+$/, '');
                } else {
                    codeEl.textContent = trimmedText;
                }
            }

            // 转义 HTML 特殊字符，防止代码内容被浏览器解析执行
            // 仅对非 Prism 高亮、非 language-markup 的代码块执行
            // language-markup 代码块由 PHP esc_html() 已转义，且 Prism 高亮后会产生 <span> 子元素
            var isMarkup = codeEl.classList.contains('language-markup')
                        || codeEl.classList.contains('language-html')
                        || codeEl.classList.contains('language-xml');
            if (!isMarkup && codeEl.children.length > 0 && !codeEl.querySelector('span[class*="token"]')) {
                var textContent = codeEl.textContent;
                codeEl.innerHTML = escapeHtml(textContent);
            }

            if (isSingleLineCode(codeEl.textContent || '')) {
                preEl.classList.add('lared-prism-pre--single-line');
            } else {
                preEl.classList.add('line-numbers');

                // 生成行号
                var lineCount = getLineCount(codeEl.textContent || '');
                if (lineCount > 1) {
                    var lineNumbersWrapper = document.createElement('span');
                    lineNumbersWrapper.className = 'line-numbers-rows';
                    lineNumbersWrapper.setAttribute('aria-hidden', 'true');
                    
                    var lineNumbersHtml = '';
                    for (var i = 0; i < lineCount; i++) {
                        lineNumbersHtml += '<span></span>';
                    }
                    lineNumbersWrapper.innerHTML = lineNumbersHtml;
                    
                    // 插入到 pre 元素的最前面
                    preEl.insertBefore(lineNumbersWrapper, preEl.firstChild);
                }

                if (lineCount > MAX_VISIBLE_LINES) {
                    preEl.classList.add('lared-prism-pre--collapsible', 'lared-prism-pre--collapsed');

                    var foldBtn = document.createElement('button');
                    foldBtn.type = 'button';
                    foldBtn.className = 'lared-code-fold-btn';
                    foldBtn.textContent = '展开';
                    foldBtn.setAttribute('aria-label', '展开代码');
                    foldBtn.setAttribute('aria-expanded', 'false');

                    foldBtn.addEventListener('click', function () {
                        var expanded = preEl.classList.contains('lared-prism-pre--expanded');

                        if (expanded) {
                            preEl.classList.remove('lared-prism-pre--expanded');
                            preEl.classList.add('lared-prism-pre--collapsed');
                            foldBtn.textContent = '展开';
                            foldBtn.setAttribute('aria-label', '展开代码');
                            foldBtn.setAttribute('aria-expanded', 'false');
                            return;
                        }

                        preEl.classList.remove('lared-prism-pre--collapsed');
                        preEl.classList.add('lared-prism-pre--expanded');
                        foldBtn.textContent = '收起';
                        foldBtn.setAttribute('aria-label', '收起代码');
                        foldBtn.setAttribute('aria-expanded', 'true');
                    });

                    preEl.appendChild(foldBtn);
                }
            }

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'lared-code-copy-btn';
            button.setAttribute('aria-label', '复制代码');
            button.innerHTML = '<i class="fa-regular fa-copy" aria-hidden="true"></i>';

            button.addEventListener('click', function () {
                var source = codeEl.textContent || '';
                if (!source) {
                    return;
                }

                copyText(source)
                    .then(function () {
                        button.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i>';
                        button.classList.add('is-copied');
                        window.setTimeout(function () {
                            button.innerHTML = '<i class="fa-regular fa-copy" aria-hidden="true"></i>';
                            button.classList.remove('is-copied');
                        }, 3000);
                    })
                    .catch(function () {
                        button.innerHTML = '<i class="fa-regular fa-copy" aria-hidden="true"></i>';
                        window.setTimeout(function () {
                            button.innerHTML = '<i class="fa-regular fa-copy" aria-hidden="true"></i>';
                        }, 3000);
                    });
            });

            preEl.appendChild(button);

            /* ── 运行按钮：仅对 language-html 或有 data-cr-runnable 的代码块显示 ── */
            var isHtml = codeEl.classList.contains('language-html')
                      || preEl.hasAttribute('data-cr-runnable');
            if (isHtml && !preEl.querySelector('.lared-code-run-btn')) {
                var runBtn = document.createElement('button');
                runBtn.type = 'button';
                runBtn.className = 'lared-code-run-btn';
                runBtn.setAttribute('aria-label', '运行代码');
                runBtn.innerHTML = '<i class="fa-solid fa-play" aria-hidden="true"></i>';
                runBtn.addEventListener('click', function () {
                    var code = codeEl.textContent || '';
                    var title = preEl.getAttribute('data-cr-title') || '代码预览';
                    var height = parseInt(preEl.getAttribute('data-cr-height')) || 400;
                    laredCodeRunnerOpen(code, title, height);
                });
                preEl.appendChild(runBtn);
            }
        });
    }

    function highlight(root) {
        if (typeof Prism === 'undefined') {
            return;
        }

        Prism.highlightAllUnder(root || document);
    }

    function initPrismEnhance(root) {
        var scope = root || document;
        ensureCopyButtons(scope);
        highlight(scope);
    }

    /* view-image.js - lightweight image lightbox */
    function initViewImage() {
        if (typeof window.ViewImage === 'undefined') {
            return;
        }

        // Initialize for article content images
        var contents = Array.prototype.slice.call(document.querySelectorAll('.single-article-content, .home-article-body, .memos-card-body, .album-grid'));
        contents.forEach(function (content) {
            if (!content.hasAttribute('view-image')) {
                content.setAttribute('view-image', '');
            }
        });

        // Initialize ViewImage
        window.ViewImage && window.ViewImage.init('.single-article-content img, .home-article-body img, .memos-card-body img, .album-grid img');
    }

    /* article-image-loading.js - 文章图片占位 loading 效果 */
    function initArticleImageLoading() {
        // 只处理文章内容的图片
        var articleContents = document.querySelectorAll('.single-article-content, .home-article-body');
        
        articleContents.forEach(function (content) {
            var images = Array.prototype.slice.call(content.querySelectorAll('img:not(.emoji):not(.avatar)'));
            
            images.forEach(function (img) {
                // 跳过网格布局内的图片（由 initLaredGrid 处理）
                if (img.closest('.lared-grid-2, .lared-grid-3, .lared-grid-4')) {
                    return;
                }

                // 跳过已经包装过的图片 (PHP 已经处理过)
                if (img.classList.contains('img-loading-target')) {
                    // 确保加载状态正确
                    ensureImageLoaded(img);
                    return;
                }
                
                // 跳过代码块内的图片
                if (img.closest('pre, code')) {
                    return;
                }
                
                wrapImageWithLoader(img);
            });
        });

        // 处理网格布局
        initLaredGrid();
    }

    /**
     * lared-grid 图片网格布局初始化
     * 清理 PHP loading-wrapper 残留，保持 grid 结构纯净
     */
    function initLaredGrid() {
        var grids = document.querySelectorAll('.lared-grid-2, .lared-grid-3, .lared-grid-4');

        grids.forEach(function (grid) {
            // 清理 wpautop 产生的 <br> 标签（会成为多余 grid item）
            var brs = Array.prototype.slice.call(grid.querySelectorAll(':scope > br'));
            brs.forEach(function (br) { br.remove(); });

            // 清理 wpautop 产生的空 <p> 标签
            var ps = Array.prototype.slice.call(grid.querySelectorAll(':scope > p'));
            ps.forEach(function (p) {
                // 把 p 内的子节点（img 等）移到 grid 直接下级
                while (p.firstChild) {
                    grid.insertBefore(p.firstChild, p);
                }
                p.remove();
            });

            // 如果 PHP 的 lared_wrap_images_with_loader 把 img 包在了 figure.img-loading-wrapper 里，
            // 需要把 img 解放出来，直接放入 grid 容器
            var wrappers = Array.prototype.slice.call(grid.querySelectorAll('.img-loading-wrapper'));
            wrappers.forEach(function (wrapper) {
                var img = wrapper.querySelector('img');
                if (img) {
                    img.classList.remove('img-loading-target');
                    img.style.opacity = '';
                    img.style.position = '';
                    grid.insertBefore(img, wrapper);
                }
                wrapper.remove();
            });

            // 确保所有图片可见
            var imgs = Array.prototype.slice.call(grid.querySelectorAll('img'));
            imgs.forEach(function (img) {
                img.classList.remove('img-loading-target');
                img.style.opacity = '1';
            });
        });
    }
    
    function ensureImageLoaded(img) {
        var wrapper = img.closest('.img-loading-wrapper');
        if (!wrapper) return;
        
        function markLoaded() {
            wrapper.classList.add('is-loaded');
        }
        
        // lazysizes 已加载完成
        if (img.classList.contains('lazyloaded')) {
            markLoaded();
        } else if (img.complete && img.naturalWidth > 0) {
            markLoaded();
        } else {
            img.addEventListener('lazyloaded', markLoaded, { once: true });
            img.addEventListener('load', markLoaded, { once: true });
            img.addEventListener('error', function() {
                wrapper.classList.add('is-error');
            }, { once: true });
        }
    }
    
    function wrapImageWithLoader(img) {
        // 创建占位容器
        var wrapper = document.createElement('figure');
        wrapper.className = 'img-loading-wrapper';
        
        // 从图片的 width/height 属性获取尺寸设置比例
        var width = img.getAttribute('width') || img.naturalWidth || 0;
        var height = img.getAttribute('height') || img.naturalHeight || 0;
        if (width && height && parseInt(height) > 0) {
            wrapper.style.aspectRatio = width + '/' + height;
        }
        
        // 创建 loading 圆圈
        var spinner = document.createElement('div');
        spinner.className = 'img-loading-spinner';
        spinner.innerHTML = '<div class="spinner-circle"></div>';
        
        // 设置图片类名
        img.classList.add('img-loading-target');
        
        // 如果图片还没有 lazyload 类且有 src，转换为 lazysizes 格式
        if (!img.classList.contains('lazyload') && !img.classList.contains('lazyloaded') && img.getAttribute('src')) {
            var src = img.getAttribute('src');
            img.setAttribute('data-src', src);
            img.removeAttribute('src');
            img.classList.add('lazyload');
        }
        
        // 包装图片
        img.parentNode.insertBefore(wrapper, img);
        wrapper.appendChild(spinner);
        wrapper.appendChild(img);
        
        // 监听 lazysizes 完成事件
        function onImageLoad() {
            wrapper.classList.add('is-loaded');
        }
        
        // 检查图片是否已加载完成（包括缓存或已被 lazysizes 处理）
        if (img.classList.contains('lazyloaded') || (img.complete && img.naturalWidth > 0)) {
            onImageLoad();
        } else {
            img.addEventListener('lazyloaded', onImageLoad, { once: true });
            img.addEventListener('load', onImageLoad, { once: true });
            
            img.addEventListener('error', function () {
                wrapper.classList.add('is-error');
                spinner.innerHTML = '<div class="img-loading-error-icon"><i class="fa-solid fa-circle-exclamation"></i></div>';
            }, { once: true });
        }
    }

    /* image-load-animation.js — lazysizes 全局事件驱动 */
    function initImageLoadAnimation() {
        var htmlEl = document.documentElement;
        var animationType = htmlEl.getAttribute('data-img-animation') || 'none';
        
        if (animationType === 'none') {
            return;
        }

        // 为所有图片设置动画属性
        var images = Array.prototype.slice.call(document.querySelectorAll('img'));
        
        images.forEach(function (img) {
            // 排除特定图片
            if (img.closest('pre, code, #xplayer') || 
                img.classList.contains('emoji') || 
                img.classList.contains('lared-emoji') ||
                img.classList.contains('avatar') ||
                img.classList.contains('comment-ua-icon') ||
                img.classList.contains('friend-link-card-avatar-img') ||
                img.closest('.comment-ua-geo') ||
                img.closest('.friend-link-card-avatar') ||
                img.hasAttribute('data-hero-main-image')) {
                return;
            }

            // 设置动画类型
            img.setAttribute('data-img-animation', animationType);
        });
    }

    // 全局 lazysizes 事件监听（只注册一次）
    if (!window.__laredLazysizesInited) {
        window.__laredLazysizesInited = true;
        document.addEventListener('lazyloaded', function (e) {
            var img = e.target;
            if (!img || img.tagName !== 'IMG') return;
            
            // 处理 loading-wrapper 的加载完成
            var wrapper = img.closest('.img-loading-wrapper');
            if (wrapper && !wrapper.classList.contains('is-loaded')) {
                wrapper.classList.add('is-loaded');
            }
        });
    }

    function normalizePath(path) {
        if (!path) {
            return '/';
        }

        var normalized = path.replace(/\/+$/, '');
        return normalized || '/';
    }

    function syncHeaderNavActiveState() {
        var nav = document.querySelector('.nav-wrap .nav');
        if (!nav) {
            return;
        }

        var currentUrl;
        try {
            currentUrl = new URL(window.location.href);
        } catch (error) {
            return;
        }

        var currentPath = normalizePath(currentUrl.pathname);
        var currentSearch = currentUrl.search || '';

        var links = Array.prototype.slice.call(nav.querySelectorAll('a[href]'));
        if (!links.length) {
            return;
        }

        var candidates = [];

        links.forEach(function (link) {
            var li = link.closest('li');
            if (!li) {
                return;
            }

            li.classList.remove('current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current_page_ancestor');
            link.classList.remove('is-active', 'is-ancestor-active');

            var linkUrl;
            try {
                linkUrl = new URL(link.href, window.location.origin);
            } catch (error) {
                return;
            }

            if (linkUrl.origin !== currentUrl.origin) {
                return;
            }

            var linkPath = normalizePath(linkUrl.pathname);
            var linkSearch = linkUrl.search || '';
            var score = 0;

            if (linkPath === currentPath && linkSearch === currentSearch) {
                score = 3000 + linkPath.length;
            } else if (linkPath === currentPath) {
                score = 2000 + linkPath.length;
            } else if (linkPath !== '/' && (currentPath + '/').indexOf(linkPath + '/') === 0) {
                score = 1000 + linkPath.length;
            } else if (linkPath === '/' && currentPath === '/') {
                score = 10;
            }

            if (score > 0) {
                candidates.push({ li: li, score: score });
            }
        });

        if (!candidates.length) {
            return;
        }

        candidates.sort(function (a, b) {
            return b.score - a.score;
        });

        var activeLi = candidates[0].li;
        activeLi.classList.add('current-menu-item', 'current_page_item');
        var activeLink = activeLi.querySelector(':scope > a') || activeLi.querySelector('a');
        if (activeLink) {
            activeLink.classList.add('is-active');
        }

        var ancestor = activeLi.parentElement;
        while (ancestor) {
            if (ancestor.tagName && ancestor.tagName.toLowerCase() === 'li') {
                ancestor.classList.add('current-menu-ancestor', 'current_page_ancestor');
                var ancestorLink = ancestor.querySelector(':scope > a') || ancestor.querySelector('a');
                if (ancestorLink) {
                    ancestorLink.classList.add('is-ancestor-active');
                }
            }
            ancestor = ancestor.parentElement;
        }
    }

    function initRssCopyButton() {
        var rssButtons = document.querySelectorAll('[data-rss-copy]:not([data-rss-copy-ready="1"])');
        if (!rssButtons.length) {
            return;
        }

        rssButtons.forEach(function(rssButton) {
            rssButton.setAttribute('data-rss-copy-ready', '1');

            var icon = rssButton.querySelector('i');
            var resetTimer = null;

            var copyText = function (text) {
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function' && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                }

                return new Promise(function (resolve, reject) {
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();

                    try {
                        var copied = document.execCommand('copy');
                        document.body.removeChild(textarea);
                        if (copied) {
                            resolve();
                        } else {
                            reject(new Error('copy failed'));
                        }
                    } catch (error) {
                        document.body.removeChild(textarea);
                        reject(error);
                    }
                });
            };

            var setCopiedState = function () {
                rssButton.classList.add('is-copied');
                rssButton.setAttribute('aria-label', 'Feed copied');
                var tooltipEl = rssButton.querySelector('.rss-tooltip');
                if (tooltipEl) {
                    tooltipEl.textContent = '复制成功';
                }
                if (icon) {
                    icon.classList.remove('fa-rss');
                    icon.classList.add('fa-check');
                }

                if (resetTimer) {
                    window.clearTimeout(resetTimer);
                }
                resetTimer = window.setTimeout(function () {
                    rssButton.classList.remove('is-copied');
                    rssButton.setAttribute('aria-label', 'RSS Feed');
                    if (tooltipEl) {
                        tooltipEl.textContent = '点击复制订阅地址';
                    }
                    if (icon) {
                        icon.classList.remove('fa-check');
                        icon.classList.add('fa-rss');
                    }
                }, 1600);
            };

            rssButton.addEventListener('click', function (event) {
                event.preventDefault();
                var feedUrl = rssButton.getAttribute('data-feed-url') || rssButton.getAttribute('href') || '';
                if (!feedUrl) {
                    return;
                }

                copyText(feedUrl).then(function () {
                    setCopiedState();
                }).catch(function () {
                    rssButton.setAttribute('title', '复制失败');
                });
            });
        });
    }

    /* header-login.js - Header 和 Footer 登录下拉框 + AJAX 登录 */
    function initHeaderLogin() {
        // 支持多个登录按钮（header 和 footer）
        var loginWrappers = document.querySelectorAll('.header-login-wrapper, .footer-login-wrapper');

        if (!loginWrappers.length) {
            return;
        }

        loginWrappers.forEach(function(loginWrapper) {
            // 防止 PJAX 导航后重复绑定（footer 不在 Barba 容器内，DOM 不会被替换）
            if (loginWrapper._loginBound) {
                return;
            }
            loginWrapper._loginBound = true;

            var loginToggle = loginWrapper.querySelector('[data-login-toggle]');
            var loginDropdown = loginWrapper.querySelector('[data-login-dropdown]');

            if (!loginToggle || !loginDropdown) {
                return;
            }

            // 切换下拉框显示/隐藏
            loginToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                closeAllLoginDropdowns(loginDropdown, loginWrapper);
                var isActive = loginDropdown.classList.contains('is-active');
                loginDropdown.classList.toggle('is-active', !isActive);
                loginWrapper.classList.toggle('is-open', !isActive);
            });

            // 点击下拉框内部不关闭
            loginDropdown.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            // AJAX 登录表单
            var loginForm = loginWrapper.querySelector('[data-login-form]');
            if (loginForm) {
                loginForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    handleAjaxLogin(loginForm, loginWrapper);
                });
            }
        });

        // 全局事件只绑定一次
        if (!window._loginClickHandlerBound) {
            document.addEventListener('click', function () {
                closeAllLoginDropdowns();
            });
            window._loginClickHandlerBound = true;
        }

        if (!window._loginKeyHandlerBound) {
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeAllLoginDropdowns();
                }
            });
            window._loginKeyHandlerBound = true;
        }
    }

    function closeAllLoginDropdowns(exceptDropdown, exceptWrapper) {
        document.querySelectorAll('[data-login-dropdown]').forEach(function(dropdown) {
            if (dropdown !== exceptDropdown) {
                dropdown.classList.remove('is-active');
            }
        });
        document.querySelectorAll('.header-login-wrapper, .footer-login-wrapper').forEach(function(wrapper) {
            if (wrapper !== exceptWrapper) {
                wrapper.classList.remove('is-open');
            }
        });
    }

    function handleAjaxLogin(form, wrapper) {
        var submitBtn = form.querySelector('[data-login-submit]');
        var textEl    = form.querySelector('.footer-login-submit-text');
        var loadingEl = form.querySelector('.footer-login-submit-loading');
        var errorEl   = form.querySelector('[data-login-error]');

        if (!submitBtn) return;

        var username = form.querySelector('input[name="log"]').value.trim();
        var password = form.querySelector('input[name="pwd"]').value;
        var remember = form.querySelector('input[name="rememberme"]');

        // 清空错误
        if (errorEl) { errorEl.textContent = ''; errorEl.style.display = 'none'; }

        if (!username || !password) {
            showLoginError(errorEl, '请填写用户名和密码');
            return;
        }

        // 显示加载状态
        submitBtn.disabled = true;
        if (textEl) textEl.style.display = 'none';
        if (loadingEl) loadingEl.style.display = 'inline-flex';

        var formData = new FormData();
        formData.append('action', 'lared_ajax_login');
        formData.append('nonce', LaredAjax.loginNonce);
        formData.append('log', username);
        formData.append('pwd', password);
        if (remember && remember.checked) {
            formData.append('rememberme', 'forever');
        }

        fetch(LaredAjax.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                // 登录成功 → 替换整个 login wrapper 为头像菜单
                var avatarHtml =
                    '<div class="footer-avatar-wrapper">' +
                        '<a href="' + escHtml(data.data.admin_url) + '" class="site-footer-icon-link footer-user-avatar" title="' + escHtml(data.data.name) + '">' +
                            '<img src="' + escHtml(data.data.avatar) + '" alt="' + escHtml(data.data.name) + '" class="h-full w-full object-cover" />' +
                        '</a>' +
                        '<div class="footer-avatar-menu">' +
                            '<a href="' + escHtml(data.data.admin_url) + '" class="footer-avatar-menu-item">' +
                                '<i class="fa-solid fa-gauge" aria-hidden="true"></i> 仪表盘</a>' +
                            '<a href="' + escHtml(data.data.admin_url) + 'profile.php" class="footer-avatar-menu-item">' +
                                '<i class="fa-solid fa-user-pen" aria-hidden="true"></i> 个人资料</a>' +
                            '<div class="footer-avatar-menu-divider"></div>' +
                            '<a href="' + escHtml(data.data.logout_url) + '" class="footer-avatar-menu-item footer-avatar-menu-logout" data-no-pjax>' +
                                '<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> 退出登录</a>' +
                        '</div>' +
                    '</div>';
                wrapper.outerHTML = avatarHtml;
            } else {
                showLoginError(errorEl, data.data && data.data.message ? data.data.message : '登录失败');
                resetLoginBtn(submitBtn, textEl, loadingEl);
            }
        })
        .catch(function() {
            showLoginError(errorEl, '网络错误，请重试');
            resetLoginBtn(submitBtn, textEl, loadingEl);
        });
    }

    function showLoginError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
    }

    function resetLoginBtn(btn, textEl, loadingEl) {
        btn.disabled = false;
        if (textEl) textEl.style.display = 'inline';
        if (loadingEl) loadingEl.style.display = 'none';
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /* pjax-init.js */
    function initPjaxZhheo() {
        if (document.documentElement.getAttribute('data-lared-pjax-ready') === '1') {
            return;
        }

        if (typeof window.Pjax === 'undefined') {
            return;
        }

        document.documentElement.setAttribute('data-lared-pjax-ready', '1');

        var headerLoading = document.querySelector('[data-header-loading]');
        var loadingShowTime = 0;
        var MIN_LOADING_MS = 500;

        function loadingShow() {
            if (!headerLoading) {
                return;
            }
            loadingShowTime = Date.now();
            headerLoading.classList.add('is-active');
        }

        function loadingHide() {
            if (!headerLoading) {
                return;
            }
            var elapsed = Date.now() - loadingShowTime;
            var remaining = MIN_LOADING_MS - elapsed;
            if (remaining > 0) {
                setTimeout(function () {
                    headerLoading.classList.remove('is-active');
                }, remaining);
            } else {
                headerLoading.classList.remove('is-active');
            }
        }

        var pjax = new window.Pjax({
            elements: 'a[href]:not([data-no-pjax]):not(.comment-reply-link):not(#cancel-comment-reply-link), form[action]:not(#commentform):not([data-no-pjax])',
            selectors: ['title', '[data-barba="container"]'],
            cacheBust: false,
            scrollTo: 0,
        });

        if (pjax && typeof pjax.refresh === 'function') {
            pjax.refresh();
        }

        document.addEventListener('pjax:send', function () {
            loadingShow();
            // 强制关闭 header 下拉菜单
            document.querySelectorAll('.nav .menu-item-has-children').forEach(function(item) {
                item.classList.add('nav-dropdown-hidden');
            });
        });

        document.addEventListener('pjax:complete', function () {
            loadingHide();
            // 移除下拉菜单隐藏标记
            document.querySelectorAll('.nav .menu-item-has-children.nav-dropdown-hidden').forEach(function(item) {
                item.classList.remove('nav-dropdown-hidden');
            });
            reinitAfterPjax();
        });

        document.addEventListener('pjax:error', function () {
            loadingHide();
        });
    }

    function initHomeModules() {
        initTabs();
        initHeroSwitch();
        initToc();
        initSidebarTabs();
    }

    /* 首页侧边栏 Tab 切换 */
    function initSidebarTabs() {
        var tabContainer = document.querySelector('.home-sidebar-tabs');
        if (!tabContainer) return;

        var tabs = tabContainer.querySelectorAll('.home-sidebar-tab');
        var panels = document.querySelectorAll('.home-sidebar-tab-panel');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var targetTab = this.getAttribute('data-tab');

                // 切换 Tab 按钮状态
                tabs.forEach(function(t) {
                    t.classList.remove('is-active');
                });
                this.classList.add('is-active');

                // 切换面板显示
                panels.forEach(function(panel) {
                    if (panel.getAttribute('data-panel') === targetTab) {
                        panel.classList.add('is-active');
                    } else {
                        panel.classList.remove('is-active');
                    }
                });
            });
        });
    }

    function initSingleModules() {
        initSingleSideToc();
        trackPostViews();
    }

    function initGlobalModules() {
        initPjaxZhheo();
        syncHeaderNavActiveState();
        initRssCopyButton();
        initBackToTop();
        initAjaxCommentSubmit();
        initEmojiPanel();
        initCommentExpand();
        initEditInfoToggle();
        initEmailAvatar();
        initPrismEnhance(document);
        initViewImage();
        initImageLoadAnimation();
        initArticleImageLoading();
        initHeaderLogin();
        initInlineCodeCleaner();
        initSearchModal();
        initPlyr();
        trackFooterVisitor();
        initMusicPlayer();
    }

    /**
     * 清理内联 code 标签中的反引号
     * 处理 Gutenberg/编辑器自动添加的反引号
     */
    function initInlineCodeCleaner() {
        // 只处理不在 pre 标签内的 code 标签
        var codes = document.querySelectorAll('code:not(pre code)');
        codes.forEach(function(code) {
            var html = code.innerHTML;
            
            // 检查是否包含反引号（包括 HTML 实体编码的）
            // 匹配开头的反引号: ` 或 &#96; 或 &#x60; 或 &grave;
            // 匹配结尾的反引号
            var hasLeadingBacktick = /^(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+/.test(html);
            var hasTrailingBacktick = /(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+$/.test(html);
            
            if (hasLeadingBacktick || hasTrailingBacktick) {
                // 去除开头和结尾的反引号及其 HTML 实体
                var cleaned = html
                    .replace(/^(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+/, '')
                    .replace(/(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+$/, '');
                code.innerHTML = cleaned;
            }
        });
    }

    function reinitAfterPjax() {
        syncHeaderNavActiveState();
        initHeroSwitch();
        initTabs();
        initSingleSideToc();
        initToc();
        initSidebarTabs();
        trackPostViews();
        initPrismEnhance(document);
        initViewImage();
        initImageLoadAnimation();
        initAjaxCommentSubmit();
        initEmojiPanel();
        initCommentExpand();
        initEditInfoToggle();
        initEmailAvatar();
        initRssCopyButton();
        initArticleImageLoading();
        initHeaderLogin();
        initInlineCodeCleaner();
        initSearchModal();
        initMemosPublish();
        initMemosFilter();
        initPlyr();
        trackFooterVisitor();
        initMusicPlayer();

        /* PJAX 后重新初始化 xalbum 插件 */
        if (typeof window.initXalbum === 'function') {
            window.initXalbum();
        }

        // 重新初始化 WordPress 评论回复表单移动功能（PJAX 替换内容后旧的事件绑定已丢失）
        if (window.addComment && typeof window.addComment.init === 'function') {
            window.addComment.init();
        }
    }

    function init() {
        initHomeModules();
        initSingleModules();
        initGlobalModules();
    }

    /* memos-publish.js - Memos 发布功能 */
    function initMemosPublish() {
        var form = document.getElementById('memos-publish-form');
        if (!form) {
            return;
        }

        var submitBtn = form.querySelector('.memos-publish-submit');
        var statusDiv = document.getElementById('memos-publish-status');
        var textarea = document.getElementById('memos-content');
        var tagList = document.getElementById('memos-tag-list');

        // 点击标签按钮，自动填入文本框
        if (tagList && textarea) {
            tagList.addEventListener('click', function(e) {
                var tagBtn = e.target.closest('.memos-publish-tag-btn');
                if (tagBtn) {
                    var tag = tagBtn.getAttribute('data-tag');
                    if (tag) {
                        var currentValue = textarea.value;
                        var tagText = '#' + tag;
                        // 检查是否已包含该标签
                        if (currentValue.indexOf(tagText) === -1) {
                            // 在末尾添加标签（前面加空格）
                            var newValue = currentValue ? currentValue + ' ' + tagText : tagText;
                            textarea.value = newValue;
                        }
                        // 聚焦文本框
                        textarea.focus();
                    }
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var content = textarea ? textarea.value.trim() : '';
            if (!content) {
                showStatus('请输入内容', 'error');
                return;
            }

            // 从内容中提取标签
            var tags = [];
            var tagMatches = content.match(/#([\w\u4e00-\u9fa5\-]{1,32})/g);
            if (tagMatches) {
                tagMatches.forEach(function(match) {
                    var tag = match.replace(/^#/, '');
                    if (tags.indexOf(tag) === -1) {
                        tags.push(tag);
                    }
                });
            }

            var visibility = form.querySelector('[name="memos_visibility"]');
            var visibilityValue = visibility ? visibility.value : 'PUBLIC';

            // 禁用提交按钮
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> 发布中...';
            }

            // 发送 AJAX 请求
            var formData = new FormData();
            formData.append('action', 'lared_publish_memo');
            formData.append('nonce', form.querySelector('[name="memos_publish_nonce"]').value);
            formData.append('content', content);
            formData.append('visibility', visibilityValue);
            tags.forEach(function(tag) {
                formData.append('tags[]', tag);
            });

            var ajaxUrl = (window.LaredAjax && window.LaredAjax.ajaxUrl) || '/wp-admin/admin-ajax.php';
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    showStatus(data.data.message || '发布成功', 'success');
                    // 清空表单
                    if (textarea) textarea.value = '';
                    // 可选：刷新页面显示新内容
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    showStatus(data.data.message || '发布失败', 'error');
                }
            })
            .catch(function(error) {
                showStatus('网络错误，请重试', 'error');
            })
            .finally(function() {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> 发布';
                }
            });
        });

        function showStatus(message, type) {
            if (!statusDiv) return;
            statusDiv.textContent = message;
            statusDiv.className = 'memos-publish-status is-' + type;
            setTimeout(function() {
                statusDiv.className = 'memos-publish-status';
            }, 5000);
        }
    }

    /* memos-filter.js - Memos 筛选功能（日历、关键词） */
    function initMemosFilter() {
        var grid = document.querySelector('.memos-grid');
        if (!grid) return;

        var ajaxUrl = (window.LaredAjax && window.LaredAjax.ajaxUrl) || '/wp-admin/admin-ajax.php';
        var nonce = (window.LaredAjax && window.LaredAjax.memosFilterNonce) || '';
        var isLoading = false;

        // 创建 loading 元素
        var loadingEl = document.createElement('div');
        loadingEl.className = 'memos-loading';
        loadingEl.innerHTML = '<span class="memos-loading-spinner"></span>';
        loadingEl.style.display = 'none';
        grid.parentNode.insertBefore(loadingEl, grid);

        // 创建过滤器标题
        var filterTitleEl = document.createElement('div');
        filterTitleEl.className = 'memos-filter-title';
        filterTitleEl.style.display = 'none';
        filterTitleEl.innerHTML = '<span class="memos-filter-text"></span><button type="button" class="memos-filter-clear" title="清除筛选"><i class="fa-solid fa-xmark"></i></button>';
        grid.parentNode.insertBefore(filterTitleEl, grid);

        // 清除筛选按钮点击事件
        filterTitleEl.querySelector('.memos-filter-clear').addEventListener('click', function() {
            resetFilter();
        });

        // 恢复默认（重新加载所有）
        function resetFilter() {
            filterTitleEl.style.display = 'none';
            // 重新加载页面获取所有内容
            window.location.reload();
        }

        // 绑定日历点击事件
        document.querySelectorAll('.memos-calendar-day.has-memos').forEach(function(day) {
            day.addEventListener('click', function() {
                var date = this.getAttribute('data-date');
                if (date && !isLoading) {
                    loadMemosByDate(date);
                }
            });
        });

        // 绑定关键词点击事件
        document.querySelectorAll('.memos-sidebar-tags [data-keyword], .memos-card-keyword[data-keyword]').forEach(function(tag) {
            tag.style.cursor = 'pointer';
            tag.addEventListener('click', function(e) {
                e.preventDefault();
                var keyword = this.getAttribute('data-keyword');
                if (keyword && !isLoading) {
                    loadMemosByKeyword(keyword);
                }
            });
        });

        // 按日期加载
        function loadMemosByDate(date) {
            if (isLoading) return;
            isLoading = true;

            showLoading(true);
            filterTitleEl.querySelector('.memos-filter-text').textContent = '日期: ' + date;
            filterTitleEl.style.display = 'flex';

            var formData = new FormData();
            formData.append('action', 'lared_get_memos_by_date');
            formData.append('nonce', nonce);
            formData.append('date', date);

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    grid.innerHTML = data.data.html;
                    // 重新绑定新加载内容的关键词点击
                    bindKeywordClicks();
                } else {
                    grid.innerHTML = '<div class="memos-error">' + (data.data.message || '加载失败') + '</div>';
                }
            })
            .catch(function() {
                grid.innerHTML = '<div class="memos-error">网络错误，请重试</div>';
            })
            .finally(function() {
                showLoading(false);
                isLoading = false;
            });
        }

        // 按关键词加载
        function loadMemosByKeyword(keyword) {
            if (isLoading) return;
            isLoading = true;

            showLoading(true);
            filterTitleEl.querySelector('.memos-filter-text').innerHTML = '关键词: <span class="memos-filter-keyword">#' + keyword + '</span>';
            filterTitleEl.style.display = 'flex';

            var formData = new FormData();
            formData.append('action', 'lared_get_memos_by_keyword');
            formData.append('nonce', nonce);
            formData.append('keyword', keyword);

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    grid.innerHTML = data.data.html;
                    // 重新绑定新加载内容的关键词点击
                    bindKeywordClicks();
                } else {
                    grid.innerHTML = '<div class="memos-error">' + (data.data.message || '加载失败') + '</div>';
                }
            })
            .catch(function() {
                grid.innerHTML = '<div class="memos-error">网络错误，请重试</div>';
            })
            .finally(function() {
                showLoading(false);
                isLoading = false;
            });
        }

        // 绑定关键词点击（用于新加载的内容）
        function bindKeywordClicks() {
            document.querySelectorAll('.memos-card-keyword[data-keyword]').forEach(function(tag) {
                tag.style.cursor = 'pointer';
                tag.addEventListener('click', function(e) {
                    e.preventDefault();
                    var keyword = this.getAttribute('data-keyword');
                    if (keyword && !isLoading) {
                        loadMemosByKeyword(keyword);
                    }
                });
            });
        }

        // 显示/隐藏 loading
        function showLoading(show) {
            loadingEl.style.display = show ? 'flex' : 'none';
            if (show) {
                grid.style.opacity = '0.5';
            } else {
                grid.style.opacity = '1';
            }
        }

        // 日历翻页功能
        initCalendarNav();
        
        function initCalendarNav() {
            var calendar = document.getElementById('memos-calendar');
            var sidebar = document.querySelector('.memos-sidebar');
            if (!calendar || !sidebar) return;

            var prevBtn = sidebar.querySelector('.memos-calendar-prev');
            var nextBtn = sidebar.querySelector('.memos-calendar-next');
            var titleEl = document.getElementById('memos-calendar-title');
            var daysEl = document.getElementById('memos-calendar-days');

            var currentYear = parseInt(calendar.getAttribute('data-year'), 10);
            var currentMonth = parseInt(calendar.getAttribute('data-month'), 10);

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    changeMonth(-1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    changeMonth(1);
                });
            }

            function changeMonth(delta) {
                currentMonth += delta;
                if (currentMonth > 12) {
                    currentMonth = 1;
                    currentYear++;
                } else if (currentMonth < 1) {
                    currentMonth = 12;
                    currentYear--;
                }
                updateCalendar();
            }

            function updateCalendar() {
                // 更新标题
                if (titleEl) titleEl.textContent = currentYear + '-' + String(currentMonth).padStart(2, '0');
                calendar.setAttribute('data-year', currentYear);
                calendar.setAttribute('data-month', currentMonth);

                // 重新生成日期（先显示基础日历，高亮需要AJAX获取）
                if (daysEl) {
                    var daysHtml = generateCalendarDays(currentYear, currentMonth);
                    daysEl.innerHTML = daysHtml;
                    // 重新绑定点击事件
                    bindCalendarClicks();
                    // 从服务器获取该月数据来高亮有文章的日期
                    fetchCalendarData(currentYear, currentMonth);
                }
            }

            function generateCalendarDays(year, month) {
                var firstDay = new Date(year, month - 1, 1);
                var daysInMonth = new Date(year, month, 0).getDate();
                var startWeekday = firstDay.getDay();
                var today = new Date().toISOString().split('T')[0];
                var html = '';

                // 空白填充
                for (var i = 0; i < startWeekday; i++) {
                    html += '<span class="memos-calendar-day memos-calendar-day-empty"></span>';
                }

                // 日期（先生成基础结构，has-memos类通过AJAX添加）
                for (var day = 1; day <= daysInMonth; day++) {
                    var date = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                    var isToday = date === today ? ' is-today' : '';
                    html += '<span class="memos-calendar-day' + isToday + '" data-date="' + date + '" data-day="' + day + '">' + day + '</span>';
                }

                return html;
            }

            function fetchCalendarData(year, month) {
                // 使用现有的AJAX端点获取数据
                var formData = new FormData();
                formData.append('action', 'lared_get_memos_calendar');
                formData.append('nonce', nonce);
                formData.append('year', year);
                formData.append('month', month);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.data.days) {
                        // 高亮有文章的日期
                        data.data.days.forEach(function(dayInfo) {
                            if (dayInfo.has_content) {
                                var dayEl = daysEl.querySelector('[data-date="' + dayInfo.date + '"]');
                                if (dayEl) {
                                    dayEl.classList.add('has-memos');
                                    dayEl.setAttribute('title', dayInfo.count + ' 条动态');
                                }
                            }
                        });
                    }
                })
                .catch(function() {
                    // 静默失败，不影响使用
                });
            }

            function bindCalendarClicks() {
                daysEl.querySelectorAll('.memos-calendar-day').forEach(function(day) {
                    day.addEventListener('click', function() {
                        var date = this.getAttribute('data-date');
                        if (date && !isLoading) {
                            loadMemosByDate(date);
                        }
                    });
                });
            }
        }
    }

    /* footer-visitor-tracking.js - 首页访问量 & 最近访客追踪 */
    function trackFooterVisitor() {
        // 防止同一次页面/PJAX 导航重复追踪
        var container = document.querySelector('[data-barba="container"]');
        if (!container) return;
        if (container.getAttribute('data-visitor-tracked') === '1') return;
        container.setAttribute('data-visitor-tracked', '1');

        var ajaxUrl = (window.LaredAjax && window.LaredAjax.ajaxUrl) || '/wp-admin/admin-ajax.php';
        var homeUrl = (window.LaredAjax && window.LaredAjax.homeUrl) || '/';

        // 检测是否首页（初始加载 body.home 或 PJAX 后 data-barba-namespace="home"）
        var isHome = document.body.classList.contains('home')
            || (container.getAttribute('data-barba-namespace') === 'home');

        // 首页访问量自增
        if (isHome) {
            var homeData = new FormData();
            homeData.append('action', 'lared_track_home_views');
            fetch(ajaxUrl, { method: 'POST', body: homeData, credentials: 'same-origin' }).catch(function() {});
        }

        // 记录最近访客地理位置
        var visitorData = new FormData();
        visitorData.append('action', 'lared_track_visitor');
        fetch(ajaxUrl, { method: 'POST', body: visitorData, credentials: 'same-origin' })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (!json.success || !json.data || json.data.skipped) return;
                var d = json.data;
                var loc = d.city || d.regionName || d.country || '';
                if (!loc) return;

                // 更新 footer 中的最近访客显示
                var infoEl = document.querySelector('.footer-visitor-info');
                if (!infoEl) return;

                // 查找或创建最近访客 span
                var existingFrom = infoEl.querySelectorAll('.footer-visitor-stat')[1];
                if (!existingFrom && loc) {
                    var span = document.createElement('span');
                    span.className = 'footer-visitor-stat';
                    var flagHtml = d.countryCode
                        ? '<span class="fi fi-' + d.countryCode.toLowerCase() + ' footer-visitor-flag"></span>'
                        : '';
                    span.innerHTML = '<i class="fa-sharp fa-light fa-location-dot" aria-hidden="true"></i> 最近访客来自 ' + flagHtml + ' <span class="footer-visitor-value">' + loc + '</span>';
                    infoEl.appendChild(span);
                }
            })
            .catch(function() {});
    }

    /* track-views.js - AJAX 记录文章浏览量（兼容 PJAX） */
    function trackPostViews() {
        var main = document.querySelector('[data-post-id]');
        if (!main) return;

        var postId = main.getAttribute('data-post-id');
        if (!postId || postId === '0') return;

        // 防止同一次导航重复计数
        if (main.getAttribute('data-views-tracked') === '1') return;
        main.setAttribute('data-views-tracked', '1');

        var ajaxUrl = (window.LaredAjax && window.LaredAjax.ajaxUrl) || '/wp-admin/admin-ajax.php';
        var nonce = (window.LaredAjax && window.LaredAjax.nonce) || '';

        var formData = new FormData();
        formData.append('action', 'lared_track_views');
        formData.append('nonce', nonce);
        formData.append('post_id', postId);

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.data && typeof data.data.views !== 'undefined') {
                // 更新页面上的热度数字
                var heatBox = document.querySelector('.single-top-banner__stat-box--heat');
                if (heatBox) {
                    var numEl = heatBox.querySelector('.single-top-banner__stat-number');
                    if (numEl) {
                        numEl.textContent = String(data.data.views);
                    }
                }
            }
        })
        .catch(function() {
            // 静默失败
        });
    }

    /* search-modal.js - 搜索模态框 */
    function initSearchModal() {
        if (window._searchModalBound) return;

        var modal = document.querySelector('[data-search-modal]');
        if (!modal) return;

        var input = modal.querySelector('.search-modal-input');
        var resultsContainer = modal.querySelector('[data-search-results]');
        var escBtn = modal.querySelector('.search-modal-esc');
        var searchTimer = null;

        // 检测是否 Mac
        var isMac = /Mac|iPod|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
        // 设置 kbd 文本
        var kbdEls = document.querySelectorAll('[data-search-kbd]');
        kbdEls.forEach(function(kbd) {
            kbd.textContent = isMac ? '⌘K' : 'Ctrl+K';
        });

        function openModal() {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            /* 计算滚动条宽度，用 padding-right 补偿避免页面抖动 */
            var scrollbarW = window.innerWidth - document.documentElement.clientWidth;
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = scrollbarW + 'px';
            setTimeout(function() {
                if (input) input.focus();
            }, 100);
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            if (input) input.value = '';
            if (resultsContainer) {
                resultsContainer.innerHTML = '<div class="search-modal-hint"><p>输入关键词搜索文章</p></div>';
            }
        }

        // 触发按钮 — 仅保留快捷键，不绑定点击

        // 关闭 overlay
        var overlay = modal.querySelector('[data-search-close]');
        if (overlay) {
            overlay.addEventListener('click', closeModal);
        }

        // ESC 按钮
        if (escBtn) {
            escBtn.addEventListener('click', closeModal);
        }

        // 键盘快捷键 ⌘K / Ctrl+K
        document.addEventListener('keydown', function(e) {
            var isK = e.key === 'k' || e.key === 'K';
            if (isK && (isMac ? e.metaKey : e.ctrlKey)) {
                e.preventDefault();
                var isOpen = modal.classList.contains('is-open');
                if (isOpen) {
                    closeModal();
                } else {
                    openModal();
                }
            }
            // ESC 关闭
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        // 实时搜索
        if (input) {
            input.addEventListener('input', function() {
                var keyword = this.value.trim();
                if (searchTimer) clearTimeout(searchTimer);

                if (keyword.length < 2) {
                    if (resultsContainer) {
                        resultsContainer.innerHTML = '<div class="search-modal-hint"><p>输入关键词搜索文章</p></div>';
                    }
                    return;
                }

                searchTimer = setTimeout(function() {
                    doSearch(keyword);
                }, 350);
            });

            // 阻止表单默认提交，改为搜索页面跳转
            var form = input.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    var keyword = input.value.trim();
                    if (keyword.length < 1) {
                        e.preventDefault();
                        return;
                    }
                    // 允许表单正常提交到搜索结果页
                });
            }
        }

        function doSearch(keyword) {
            if (!resultsContainer) return;

            resultsContainer.innerHTML = '<div class="search-modal-loading"><i class="fa-solid fa-spinner fa-spin"></i> 搜索中…</div>';

            var ajaxUrl = (window.LaredAjax && window.LaredAjax.ajaxUrl) || '/wp-admin/admin-ajax.php';
            var nonce = (window.LaredAjax && window.LaredAjax.nonce) || '';

            var formData = new FormData();
            formData.append('action', 'lared_ajax_search');
            formData.append('nonce', nonce);
            formData.append('keyword', keyword);

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success && data.data.html) {
                    resultsContainer.innerHTML = data.data.html;
                } else {
                    resultsContainer.innerHTML = '<div class="search-modal-empty">没有找到相关文章</div>';
                }
            })
            .catch(function() {
                resultsContainer.innerHTML = '<div class="search-modal-empty">搜索失败，请重试</div>';
            });
        }

        window._searchModalBound = true;
    }

    /* ================================================================
     *  Code Runner — 模拟浏览器窗口（全局共用一个）
     * ================================================================ */
    var crOverlay, crWinBody, crWinAddr, crWinTitle, crCurrentBlob;

    function crEnsureWindow() {
        if (crOverlay) return;
        crOverlay = document.createElement('div');
        crOverlay.className = 'cr-window-overlay';
        crOverlay.innerHTML =
            '<div class="cr-window">'
            + '<div class="cr-window-titlebar">'
            +   '<span class="cr-window-dots"><i title="关闭"></i><i></i><i></i></span>'
            +   '<div class="cr-window-address"><i class="fa-solid fa-lock"></i><span class="cr-window-addr-text">about:blank</span></div>'
            +   '<span class="cr-window-title"></span>'
            + '</div>'
            + '<div class="cr-window-body"></div>'
            + '</div>';
        document.body.appendChild(crOverlay);

        crWinBody  = crOverlay.querySelector('.cr-window-body');
        crWinAddr  = crOverlay.querySelector('.cr-window-addr-text');
        crWinTitle = crOverlay.querySelector('.cr-window-title');

        crOverlay.querySelector('.cr-window-dots i:first-child').addEventListener('click', crClose);
        crOverlay.addEventListener('click', function (e) {
            if (e.target === crOverlay) crClose();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && crOverlay.classList.contains('is-open')) crClose();
        });
    }

    function crClose() {
        crOverlay.classList.remove('is-open');
        setTimeout(function () {
            crWinBody.innerHTML = '';
            if (crCurrentBlob) { URL.revokeObjectURL(crCurrentBlob); crCurrentBlob = null; }
        }, 300);
    }

    function laredCodeRunnerOpen(htmlCode, title, height) {
        crEnsureWindow();

        /* 构建完整 HTML 文档 */
        var doc = htmlCode;
        /* 如果内容不是完整文档（没有 <html 或 <!DOCTYPE），包裹一下 */
        if (!/<!doctype|<html/i.test(htmlCode)) {
            doc = '<!DOCTYPE html><html><head><meta charset="UTF-8">'
                + '<meta name="viewport" content="width=device-width,initial-scale=1.0">'
                + '<style>*{margin:0;padding:0;box-sizing:border-box}'
                + 'body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;padding:20px;background:#fff}</style>'
                + '</head><body>' + htmlCode + '</body></html>';
        }

        if (crCurrentBlob) { URL.revokeObjectURL(crCurrentBlob); crCurrentBlob = null; }
        var blob = new Blob([doc], { type: 'text/html;charset=utf-8' });
        crCurrentBlob = URL.createObjectURL(blob);

        var iframe = document.createElement('iframe');
        iframe.src = crCurrentBlob;
        iframe.sandbox = 'allow-scripts';
        iframe.style.cssText = 'width:100%;border:none;background:#fff;height:100%';

        crWinBody.innerHTML = '';
        crWinBody.appendChild(iframe);
        crWinAddr.textContent = 'code-runner://localhost/' + (title || 'preview');
        crWinTitle.textContent = title || '代码预览';
        crOverlay.classList.add('is-open');
    }

    /* ================================================================
       Home Music Player — PJAX 持久化
       ================================================================ */
    // 全局持久 Audio 实例（跨 PJAX 不销毁）
    var _musicAudio = null;
    var _musicTracks = [];
    var _musicIndex = 0;
    var _musicPlaying = false;

    /* ── Lyrics state ── */
    var _lrcCache = {};          // url → [{time, text}]
    var _musicLyrics = null;     // current parsed lyrics array
    var _musicLyricIdx = -1;     // current active lyric line index
    var _lyricsPanel = null;     // side panel DOM (inner pages)
    var _homeLyricsEl = null;    // home inline lyrics DOM

    function initMusicPlayer() {
        var el = document.getElementById('lared-music-player');
        var floatEl = document.getElementById('lared-music-float');

        // 至少需要一个播放器元素
        var sourceEl = el || floatEl;
        if (!sourceEl) return;

        var rawTracks = sourceEl.getAttribute('data-tracks');
        if (!rawTracks) return;

        try {
            var tracks = JSON.parse(rawTracks);
            if (!Array.isArray(tracks) || tracks.length === 0) return;
            _musicTracks = tracks;
        } catch (e) { return; }

        // 切换浮动播放器可见性
        if (floatEl) {
            var floatVisible = floatEl.getAttribute('data-float-visible') !== '0';
            if (el) {
                // 首页有内联播放器，隐藏浮动
                floatEl.classList.remove('is-active');
            } else if (floatVisible) {
                // 非首页，后台开启了内页播放器：始终显示
                floatEl.classList.add('is-active');
            } else {
                // 后台关闭了内页播放器
                floatEl.classList.remove('is-active');
            }
        }

        // 已有 Audio，只同步 UI
        if (_musicAudio) {
            if (el) { _syncMusicUI(el); _bindMusicEvents(el); _bindMusicContext(el); _bindMusicProgress(el); }
            if (floatEl) { _syncMusicUI(floatEl); _bindMusicEvents(floatEl); _bindMusicContext(floatEl); _bindMusicProgress(floatEl); }
            // PJAX 切回时恢复歌词 UI
            if (_musicPlaying && _musicLyrics) {
                _showLyricsUI(_musicLyrics);
                // 首页时隐藏侧边歌词面板（内页残留）
                if (el && _lyricsPanel) {
                    _lyricsPanel.classList.remove('is-visible');
                }
            }
            return;
        }

        _musicAudio = new Audio();
        _musicAudio.preload = 'auto';
        _musicIndex = 0;
        _musicAudio.src = _musicTracks[0].url;

        _musicAudio.addEventListener('ended', function () {
            _musicNext();
        });

        // 实时更新播放时间 + 歌词 + 进度
        _musicAudio.addEventListener('timeupdate', function () {
            var el = document.getElementById('lared-music-player');
            var floatEl = document.getElementById('lared-music-float');
            if (el) { _updateMusicTime(el); _updateMusicProgress(el); }
            if (floatEl) { _updateMusicTime(floatEl); _updateMusicProgress(floatEl); }
            _updateLyrics();
        });

        // 首次加载歌词
        _loadCurrentLyrics();

        if (el) { _syncMusicUI(el); _bindMusicEvents(el); _bindMusicContext(el); _bindMusicProgress(el); }
        if (floatEl) { _syncMusicUI(floatEl); _bindMusicEvents(floatEl); _bindMusicContext(floatEl); _bindMusicProgress(floatEl); }
    }

    function _syncMusicUI(el) {
        if (!el) return;
        var nameEl = el.querySelector('[data-music="name"]');
        var toggleBtn = el.querySelector('[data-music="toggle"]');

        // 切换 player 级 is-playing 类（控制 controls/viz 可见性）
        el.classList.toggle('is-playing', _musicPlaying);

        // 歌名
        if (nameEl) {
            var trackName = _musicTracks[_musicIndex].name;
            var isFloat = el.id === 'lared-music-float';

            if (isFloat) {
                // 浮动播放器：marquee 滚动逻辑
                var textSpan = nameEl.querySelector('.lared-music-track-text');
                if (!textSpan) {
                    textSpan = document.createElement('span');
                    textSpan.className = 'lared-music-track-text';
                    nameEl.textContent = '';
                    nameEl.appendChild(textSpan);
                }
                if (textSpan.getAttribute('data-raw') !== trackName) {
                    textSpan.setAttribute('data-raw', trackName);
                    textSpan.textContent = trackName;
                    nameEl.classList.remove('is-overflow');
                    var dup = textSpan.querySelector('.lared-marquee-dup');
                    if (dup) dup.remove();
                    setTimeout(function () { _checkMarquee(nameEl, textSpan, trackName); }, 60);
                }
            } else {
                // 首页播放器：marquee 逻辑
                var textSpan = nameEl.querySelector('.lared-music-track-text');
                if (!textSpan) {
                    textSpan = document.createElement('span');
                    textSpan.className = 'lared-music-track-text';
                    nameEl.textContent = '';
                    nameEl.appendChild(textSpan);
                }
                if (textSpan.getAttribute('data-raw') !== trackName) {
                    textSpan.setAttribute('data-raw', trackName);
                    textSpan.textContent = trackName;
                    nameEl.classList.remove('is-overflow');
                    var dup = textSpan.querySelector('.lared-marquee-dup');
                    if (dup) dup.remove();
                    setTimeout(function () { _checkMarquee(nameEl, textSpan, trackName); }, 60);
                }
                nameEl.classList.toggle('is-playing', _musicPlaying);
            }
        }

        // toggle 按钮图标
        if (toggleBtn) {
            var icon = toggleBtn.querySelector('i');
            toggleBtn.classList.toggle('is-playing', _musicPlaying);
            if (icon) {
                icon.className = _musicPlaying ? 'fa-solid fa-pause' : 'fa-solid fa-play';
            }
        }

        // 时间 + 进度
        _updateMusicTime(el);
        _updateMusicProgress(el);
    }

    function _checkMarquee(nameEl, textSpan, trackName) {
        if (!nameEl || !textSpan) return;
        if (textSpan.scrollWidth > nameEl.clientWidth) {
            // 需要滚动 — 在文本后追加一份副本实现无缝循环
            var dup = document.createElement('span');
            dup.className = 'lared-marquee-dup';
            dup.textContent = trackName;
            dup.style.paddingLeft = '4em';
            textSpan.appendChild(dup);
            // 根据文本长度计算动画时长
            var dur = Math.max(6, textSpan.scrollWidth / 30);
            nameEl.style.setProperty('--marquee-dur', dur + 's');
            nameEl.classList.add('is-overflow');
        } else {
            nameEl.classList.remove('is-overflow');
        }
    }

    function _formatTime(sec) {
        if (!sec || !isFinite(sec)) return '0:00';
        var m = Math.floor(sec / 60);
        var s = Math.floor(sec % 60);
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function _updateMusicTime(el) {
        if (!el) return;
        // Home player: single [data-music="time"]
        var timeEl = el.querySelector('[data-music="time"]');
        if (timeEl) {
            if (_musicAudio && _musicPlaying) {
                timeEl.textContent = _formatTime(_musicAudio.currentTime);
            } else {
                timeEl.textContent = '0:00';
            }
        }
        // Float player: separate current / duration
        var curEl = el.querySelector('[data-music="time-current"]');
        var durEl = el.querySelector('[data-music="time-duration"]');
        if (curEl) {
            curEl.textContent = _musicAudio ? _formatTime(_musicAudio.currentTime) : '0:00';
        }
        if (durEl) {
            durEl.textContent = (_musicAudio && _musicAudio.duration && isFinite(_musicAudio.duration)) ? _formatTime(_musicAudio.duration) : '0:00';
        }
    }

    /* --- Progress bar update --- */
    function _updateMusicProgress(el) {
        if (!el) return;
        var fillEl = el.querySelector('[data-music="progress-fill"]');
        var dotEl = el.querySelector('[data-music="progress-dot"]');
        if (!fillEl && !dotEl) return;

        var pct = 0;
        if (_musicAudio && _musicAudio.duration && isFinite(_musicAudio.duration) && _musicAudio.duration > 0) {
            pct = (_musicAudio.currentTime / _musicAudio.duration) * 100;
        }
        if (fillEl) fillEl.style.width = pct + '%';
        if (dotEl) dotEl.style.left = pct + '%';
    }

    /* --- Progress bar: click + drag to seek --- */
    function _bindMusicProgress(el) {
        if (el._musicProgressBound) return;
        el._musicProgressBound = true;

        var progressEl = el.querySelector('[data-music="progress"]');
        if (!progressEl) return;

        var dotEl = el.querySelector('[data-music="progress-dot"]');
        var tipEl = el.querySelector('[data-music="progress-tip"]');
        var dragging = false;

        // Hover tooltip: show time at cursor position
        if (tipEl) {
            progressEl.addEventListener('mousemove', function (e) {
                if (!_musicAudio || !_musicAudio.duration || !isFinite(_musicAudio.duration)) {
                    tipEl.style.opacity = '0';
                    return;
                }
                var rect = progressEl.getBoundingClientRect();
                var x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
                var ratio = x / rect.width;
                var t = ratio * _musicAudio.duration;
                tipEl.textContent = _formatTime(t);
                tipEl.style.left = x + 'px';
                tipEl.style.opacity = '1';
            });
            progressEl.addEventListener('mouseleave', function () {
                tipEl.style.opacity = '0';
            });
        }

        function seekToX(clientX) {
            if (!_musicAudio || !_musicAudio.duration || !isFinite(_musicAudio.duration)) return;
            var rect = progressEl.getBoundingClientRect();
            var x = Math.max(0, Math.min(clientX - rect.left, rect.width));
            var ratio = x / rect.width;
            _musicAudio.currentTime = ratio * _musicAudio.duration;
            _syncAllMusicUI();
        }

        // Click on progress bar
        progressEl.addEventListener('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dragging = true;
            if (dotEl) dotEl.classList.add('is-dragging');
            seekToX(e.clientX);
        });

        document.addEventListener('mousemove', function (e) {
            if (!dragging) return;
            e.preventDefault();
            seekToX(e.clientX);
        });

        document.addEventListener('mouseup', function () {
            if (!dragging) return;
            dragging = false;
            if (dotEl) dotEl.classList.remove('is-dragging');
        });

        // Touch support
        progressEl.addEventListener('touchstart', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dragging = true;
            if (dotEl) dotEl.classList.add('is-dragging');
            seekToX(e.touches[0].clientX);
        }, { passive: false });

        document.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            seekToX(e.touches[0].clientX);
        }, { passive: true });

        document.addEventListener('touchend', function () {
            if (!dragging) return;
            dragging = false;
            if (dotEl) dotEl.classList.remove('is-dragging');
        });
    }

    function _bindMusicEvents(el) {
        // 防止重复绑定
        if (el._musicBound) return;
        el._musicBound = true;

        // 阻止整个 player 区域的链接跳转
        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // 控制按钮
            var btn = e.target.closest('[data-music]');
            if (!btn) return;

            var action = btn.getAttribute('data-music');
            if (action === 'toggle') {
                _musicToggle();
            } else if (action === 'prev') {
                _musicPrev();
            } else if (action === 'next') {
                _musicNext();
            }
            _syncAllMusicUI();
        });

        // 阻止 player 区域的链接点击冒泡
        el.addEventListener('mousedown', function (e) {
            e.stopPropagation();
        });
    }

    function _musicToggle() {
        if (!_musicAudio) return;
        if (_musicPlaying) {
            _musicAudio.pause();
            _musicPlaying = false;
            _hideLyricsUI();
        } else {
            _musicAudio.play().catch(function () {});
            _musicPlaying = true;
            if (_musicLyrics) {
                _showLyricsUI(_musicLyrics);
            }
        }
    }

    function _musicPrev() {
        if (_musicTracks.length === 0) return;
        _musicIndex = (_musicIndex - 1 + _musicTracks.length) % _musicTracks.length;
        _musicLoad();
    }

    function _musicNext() {
        if (_musicTracks.length === 0) return;
        _musicIndex = (_musicIndex + 1) % _musicTracks.length;
        _musicLoad();
    }

    function _musicLoad() {
        if (!_musicAudio) return;
        _musicAudio.src = _musicTracks[_musicIndex].url;
        if (_musicPlaying) {
            _musicAudio.play().catch(function () {});
        }
        _syncAllMusicUI();
        _loadCurrentLyrics();
    }

    function _syncAllMusicUI() {
        var el = document.getElementById('lared-music-player');
        var floatEl = document.getElementById('lared-music-float');
        if (el) _syncMusicUI(el);
        if (floatEl) {
            _syncMusicUI(floatEl);
            // 非首页：根据后台开关决定是否显示
            if (!el) {
                var floatVisible = floatEl.getAttribute('data-float-visible') !== '0';
                if (floatVisible) {
                    floatEl.classList.add('is-active');
                } else {
                    floatEl.classList.remove('is-active');
                }
            }
        }
    }

    function _musicPlayIndex(idx) {
        if (idx < 0 || idx >= _musicTracks.length) return;
        _musicIndex = idx;
        _musicPlaying = true;
        _musicLoad();
    }

    /* 右键菜单 */
    var _musicCtx = null;

    function _bindMusicContext(el) {
        if (el._musicCtxBound) return;
        el._musicCtxBound = true;

        el.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            e.stopPropagation();
            _showMusicCtx(e.clientX, e.clientY);
        });
    }

    function _createMusicCtx() {
        if (_musicCtx) {
            _musicCtx.remove();
        }
        var ctx = document.createElement('div');
        ctx.className = 'lared-music-ctx';
        ctx.innerHTML = '<div class="lared-music-ctx-title">播放列表</div>';

        _musicTracks.forEach(function (track, i) {
            var item = document.createElement('div');
            item.className = 'lared-music-ctx-item' + (i === _musicIndex ? ' is-current' : '');
            item.innerHTML = '<span class="ctx-icon">' + (i === _musicIndex && _musicPlaying ? '<i class="fa-solid fa-volume-high"></i>' : (i + 1)) + '</span>' + _escHtml(track.name);
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                _musicPlayIndex(i);
                _hideMusicCtx();
            });
            ctx.appendChild(item);
        });

        document.body.appendChild(ctx);
        _musicCtx = ctx;
        return ctx;
    }

    function _showMusicCtx(x, y) {
        var ctx = _createMusicCtx();
        ctx.style.left = Math.min(x, window.innerWidth - 200) + 'px';
        ctx.style.top = Math.min(y, window.innerHeight - 340) + 'px';
        ctx.classList.add('is-open');

        // 点击外部关闭
        setTimeout(function () {
            document.addEventListener('click', _hideMusicCtx, { once: true });
        }, 0);
    }

    function _hideMusicCtx() {
        if (_musicCtx) {
            _musicCtx.classList.remove('is-open');
            setTimeout(function () {
                if (_musicCtx) { _musicCtx.remove(); _musicCtx = null; }
            }, 120);
        }
    }

    function _escHtml(s) {
        var d = document.createElement('span');
        d.textContent = s;
        return d.innerHTML;
    }

    /* ========================================
       Lyrics — LRC parse / fetch / UI
       ======================================== */

    /**
     * Parse LRC text → sorted array of { time: seconds, text: string }
     */
    function _parseLRC(raw) {
        var lines = raw.split(/\r?\n/);
        var result = [];
        var re = /\[(\d{1,3}):(\d{2})(?:[.:]\d+)?\]/g;
        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            var match;
            var timestamps = [];
            while ((match = re.exec(line)) !== null) {
                timestamps.push(parseInt(match[1], 10) * 60 + parseInt(match[2], 10));
            }
            re.lastIndex = 0;
            var text = line.replace(/\[\d{1,3}:\d{2}(?:[.:]\d+)?\]/g, '').trim();
            if (text === '' && timestamps.length === 0) continue;
            for (var t = 0; t < timestamps.length; t++) {
                result.push({ time: timestamps[t], text: text });
            }
        }
        result.sort(function (a, b) { return a.time - b.time; });
        return result;
    }

    /**
     * Fetch & cache LRC file, then call cb(lyrics)
     */
    function _fetchLRC(url, cb) {
        if (!url) { cb(null); return; }
        if (_lrcCache[url]) { cb(_lrcCache[url]); return; }
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                var lyrics = _parseLRC(xhr.responseText);
                if (lyrics.length > 0) {
                    _lrcCache[url] = lyrics;
                    cb(lyrics);
                } else {
                    cb(null);
                }
            } else {
                cb(null);
            }
        };
        xhr.onerror = function () { cb(null); };
        xhr.send();
    }

    /**
     * Find the active lyric index for a given playback time
     */
    function _getLyricIndex(time, lyrics) {
        if (!lyrics || lyrics.length === 0) return -1;
        var idx = -1;
        for (var i = 0; i < lyrics.length; i++) {
            if (lyrics[i].time <= time) {
                idx = i;
            } else {
                break;
            }
        }
        return idx;
    }

    /**
     * Load lyrics for the current track. Hides lyrics UI if none.
     */
    function _loadCurrentLyrics() {
        var track = _musicTracks[_musicIndex];
        if (!track || !track.lrc) {
            _musicLyrics = null;
            _musicLyricIdx = -1;
            _hideLyricsUI();
            return;
        }
        _fetchLRC(track.lrc, function (lyrics) {
            // Guard: still same track?
            if (_musicTracks[_musicIndex] !== track) return;
            _musicLyrics = lyrics;
            _musicLyricIdx = -1;
            if (lyrics && _musicPlaying) {
                _showLyricsUI(lyrics);
            } else {
                _hideLyricsUI();
            }
        });
    }

    /* ── Home inline lyrics ── */

    function _ensureHomeLyricsEl() {
        if (_homeLyricsEl && document.body.contains(_homeLyricsEl)) return _homeLyricsEl;
        var link = document.querySelector('.home-memo-strip-link');
        if (!link) { _homeLyricsEl = null; return null; }
        var el = link.querySelector('.home-memo-strip-lyrics');
        if (!el) {
            el = document.createElement('span');
            el.className = 'home-memo-strip-lyrics';
            // Insert after bird-track, before/alongside memo-strip-main
            var main = link.querySelector('.home-memo-strip-main');
            if (main) {
                link.insertBefore(el, main);
            } else {
                link.appendChild(el);
            }
        }
        _homeLyricsEl = el;
        return el;
    }

    function _updateHomeLyrics(lyrics, idx) {
        var el = _ensureHomeLyricsEl();
        if (!el) return;
        var link = el.closest('.home-memo-strip-link');

        if (!lyrics || idx < 0 || !_musicPlaying) {
            el.classList.remove('is-visible');
            el.innerHTML = '';
            if (link) link.classList.remove('has-lyrics');
            return;
        }

        if (link) link.classList.add('has-lyrics');
        el.classList.add('is-visible');

        // Check if we already have this idx Active
        var activeLine = el.querySelector('.lared-lyric-line.is-active');
        if (activeLine && activeLine.getAttribute('data-lyric-idx') === String(idx)) {
            return; // no change
        }

        // Deactivate old
        if (activeLine) {
            activeLine.classList.remove('is-active');
        }

        // Create or reuse line element
        var lineEl = el.querySelector('.lared-lyric-line[data-lyric-idx="' + idx + '"]');
        if (!lineEl) {
            // Remove all old lines to keep DOM clean
            el.innerHTML = '';
            lineEl = document.createElement('span');
            lineEl.className = 'lared-lyric-line';
            lineEl.setAttribute('data-lyric-idx', idx);
            lineEl.textContent = lyrics[idx].text || '♪';
            el.appendChild(lineEl);
        }

        // Force reflow then activate
        void lineEl.offsetWidth;
        lineEl.classList.add('is-active');
    }

    /* ── Side lyrics panel (inner pages) ── */

    function _ensureLyricsPanel() {
        if (_lyricsPanel && document.body.contains(_lyricsPanel)) return _lyricsPanel;
        var panel = document.getElementById('lared-lyrics-panel');
        if (!panel) {
            panel = document.createElement('div');
            panel.className = 'lared-lyrics-panel';
            panel.id = 'lared-lyrics-panel';
            document.body.appendChild(panel);

            // Click on lyric line → seek
            panel.addEventListener('click', function (e) {
                var item = e.target.closest('.lared-lyrics-panel__item');
                if (!item || !_musicAudio || !_musicLyrics) return;
                var t = parseFloat(item.getAttribute('data-time'));
                if (isFinite(t)) {
                    _musicAudio.currentTime = t;
                    if (!_musicPlaying) {
                        _musicToggle();
                        _syncAllMusicUI();
                    }
                }
            });
        }
        _lyricsPanel = panel;
        return panel;
    }

    function _populateLyricsPanel(lyrics) {
        var panel = _ensureLyricsPanel();
        panel.innerHTML = '';
        if (!lyrics) return;
        for (var i = 0; i < lyrics.length; i++) {
            var item = document.createElement('div');
            item.className = 'lared-lyrics-panel__item';
            item.setAttribute('data-time', lyrics[i].time);
            item.setAttribute('data-lyric-panel-idx', i);
            item.textContent = lyrics[i].text || '♪';
            panel.appendChild(item);
        }
    }

    function _updateLyricsPanel(idx) {
        if (!_lyricsPanel) return;
        var prev = _lyricsPanel.querySelector('.lared-lyrics-panel__item.is-active');
        if (prev) prev.classList.remove('is-active');
        if (idx < 0) return;
        var cur = _lyricsPanel.querySelector('[data-lyric-panel-idx="' + idx + '"]');
        if (!cur) return;
        cur.classList.add('is-active');
        // Auto-scroll to keep active centered
        var panelH = _lyricsPanel.clientHeight;
        var itemTop = cur.offsetTop;
        var itemH = cur.offsetHeight;
        _lyricsPanel.scrollTo({
            top: itemTop - panelH / 2 + itemH / 2,
            behavior: 'smooth'
        });
    }

    /* ── Show / Hide lyrics UI ── */

    function _showLyricsUI(lyrics) {
        // Home inline
        _updateHomeLyrics(lyrics, _musicLyricIdx);

        // Side panel (inner pages: no home player means we're on an inner page)
        var homeEl = document.getElementById('lared-music-player');
        var floatEl = document.getElementById('lared-music-float');
        if (!homeEl && floatEl && floatEl.getAttribute('data-float-visible') !== '0') {
            _populateLyricsPanel(lyrics);
            _ensureLyricsPanel().classList.add('is-visible');
        }
    }

    function _hideLyricsUI() {
        // Home
        var el = _homeLyricsEl;
        if (el) {
            el.classList.remove('is-visible');
            el.innerHTML = '';
            var link = el.closest('.home-memo-strip-link');
            if (link) link.classList.remove('has-lyrics');
        }
        // Side panel
        if (_lyricsPanel) {
            _lyricsPanel.classList.remove('is-visible');
        }
    }

    /**
     * Called on every timeupdate — updates active lyric index & UI
     */
    function _updateLyrics() {
        if (!_musicLyrics || !_musicPlaying || !_musicAudio) {
            return;
        }
        var idx = _getLyricIndex(_musicAudio.currentTime, _musicLyrics);
        if (idx === _musicLyricIdx) return;
        _musicLyricIdx = idx;

        // Home inline
        _updateHomeLyrics(_musicLyrics, idx);
        // Side panel
        _updateLyricsPanel(idx);
    }

    window.LaredTheme = { init: init };
    window.LaredPrism = { init: initPrismEnhance };

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('DOMContentLoaded', initMemosPublish);
    document.addEventListener('DOMContentLoaded', initMemosFilter);

}());
