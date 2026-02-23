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

        if (!mainImage || !mainTitle || !mainLink) {
            return;
        }

        /* Update right-side display with given data */
        var applyHeroData = function (item, title, image, link, badge, badgeKey) {
            mainTitle.textContent = title;
            mainLink.setAttribute('href', link);

            if (image) {
                mainImage.setAttribute('src', image);
                mainImage.setAttribute('alt', title);
                mainImage.style.opacity = '1';
                if (mainFallback) { mainFallback.classList.add('hidden'); }
            } else {
                mainImage.style.opacity = '0';
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
                    item.getAttribute('data-hero-badge-key') || ''
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
                    item.getAttribute('data-hero-badge-key') || ''
                );
                return;
            }

            var fd = new FormData();
            fd.append('action', 'lared_hero_random_article');
            fd.append('nonce', window.LaredAjax.nonce);
            fd.append('taxonomy', item.getAttribute('data-hero-taxonomy') || '');
            fd.append('term_id', item.getAttribute('data-hero-term-id') || '0');

            fetch(window.LaredAjax.ajaxUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success && res.data) {
                        var d = res.data;
                        /* Update data attrs cache */
                        item.setAttribute('data-hero-title', d.title);
                        item.setAttribute('data-hero-image', d.image);
                        item.setAttribute('data-hero-badge', d.type_label);
                        item.setAttribute('data-hero-badge-key', d.type_key);

                        applyHeroData(
                            item,
                            d.title,
                            d.image,
                            d.permalink || item.getAttribute('data-hero-link') || '',
                            d.type_label,
                            d.type_key
                        );
                    }
                })
                .catch(function () {
                    /* Fallback to cached data attrs */
                    applyHeroData(
                        item,
                        item.getAttribute('data-hero-title') || '',
                        item.getAttribute('data-hero-image') || '',
                        item.getAttribute('data-hero-link') || '',
                        item.getAttribute('data-hero-badge') || '',
                        item.getAttribute('data-hero-badge-key') || ''
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

    /* aplayer-init.js */
    function fallbackAPlayerPlaylistFromDom() {
        var fallbackContainer = document.getElementById('lared-aplayer');
        if (!fallbackContainer) {
            return [];
        }

        var audioUrl = fallbackContainer.getAttribute('data-url') || '';
        if (!audioUrl) {
            return [];
        }

        return [{
            name: fallbackContainer.getAttribute('data-title') || 'Lared Radio',
            artist: fallbackContainer.getAttribute('data-artist') || 'Unknown Artist',
            url: audioUrl,
            cover: fallbackContainer.getAttribute('data-cover') || '',
        }];
    }

    function normalizePlaylistForAPlayer(list, defaultCover) {
        if (!Array.isArray(list)) {
            return [];
        }

        return list
            .map(function (item) {
                if (!item || !item.url) {
                    return null;
                }

                var cover = item.cover || '';

                if (cover && /^\//.test(cover) && typeof window !== 'undefined' && window.location && window.location.origin) {
                    cover = window.location.origin + cover;
                }

                if (cover && /\/img\/icon\.webp(?:\?|#|$)/i.test(cover) && defaultCover) {
                    cover = defaultCover;
                }

                if (!cover && defaultCover) {
                    cover = defaultCover;
                }

                return {
                    name: item.name || 'Unknown Title',
                    artist: item.artist || 'Unknown Artist',
                    url: item.url,
                    cover: cover,
                    lrc: item.lrc || '',
                };
            })
            .filter(function (item) {
                return !!item;
            });
    }

    var aplayerPlaylistPromise = null;
    function getAPlayerPlaylistAsync() {
        if (aplayerPlaylistPromise) {
            return aplayerPlaylistPromise;
        }

        var config = window.LaredAPlayerConfig || {};
        var defaultCover = typeof config.defaultCover === 'string' ? config.defaultCover : '';

        if (Array.isArray(config.playlist) && config.playlist.length) {
            aplayerPlaylistPromise = Promise.resolve(normalizePlaylistForAPlayer(config.playlist, defaultCover));
            return aplayerPlaylistPromise;
        }

        aplayerPlaylistPromise = Promise.resolve(normalizePlaylistForAPlayer(fallbackAPlayerPlaylistFromDom(), defaultCover));
        return aplayerPlaylistPromise;
    }

    function initAPlayer() {
        var fixedContainer = document.getElementById('lared-aplayer');
        if (!fixedContainer || fixedContainer.getAttribute('data-aplayer-ready') === '1') {
            return;
        }

        if (typeof window.APlayer === 'undefined') {
            window.setTimeout(initAPlayer, 300);
            return;
        }

        getAPlayerPlaylistAsync().then(function (playlist) {
            if (!Array.isArray(playlist) || !playlist.length) {
                return;
            }

            if (fixedContainer.getAttribute('data-aplayer-ready') === '1') {
                return;
            }

            window.panFixedAPlayer = new window.APlayer({
                container: fixedContainer,
                fixed: true,
                autoplay: false,
                preload: 'none',
                audio: playlist,
            });
            fixedContainer.setAttribute('data-aplayer-ready', '1');
        });
    }

    /* comment-ajax.js */
    function ensureCommentNotice(form) {
        var notice = form.querySelector('.lared-comment-ajax-notice');
        if (notice) {
            return notice;
        }

        notice = document.createElement('div');
        notice.className = 'lared-comment-ajax-notice';
        notice.style.marginTop = '10px';
        notice.style.fontSize = '13px';
        notice.style.color = '#666';
        form.appendChild(notice);
        return notice;
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
            if (window.addComment && typeof window.addComment.moveForm === 'function') {
                var commId = link.getAttribute('data-belowelement');
                var parentId = link.getAttribute('data-commentid');
                var respondId = link.getAttribute('data-respondelement');
                var postId = link.getAttribute('data-postid');
                var replyTo = link.getAttribute('data-replyto') || '';

                if (commId && parentId && respondId && postId) {
                    window.addComment.moveForm(commId, parentId, respondId, postId, replyTo);
                }
            }

            // 更新标题为"回复 昵称"
            if (authorName) {
                updateReplyTitleText(' 回复 ' + authorName + ' ');
            }
            return;
        }

        // —— 处理取消回复链接 —— 恢复标题文字
        var cancelLink = e.target.closest('#cancel-comment-reply-link');
        if (cancelLink) {
            updateReplyTitleText(' 发表评论');
        }
    }, true); // 捕获阶段，优先于其他 click 处理器

    // ====== 评论内容展开/收起 ======

    function initCommentExpand() {
        var contents = document.querySelectorAll('.comment-list .comment-content');
        if (!contents.length) return;

        contents.forEach(function (el) {
            if (el.getAttribute('data-expand-init') === '1') return;
            el.setAttribute('data-expand-init', '1');

            // 先加 clamp 测量
            el.classList.add('is-clamped');

            // 等渲染完再测高度（图片/emoji 可能还没加载）
            requestAnimationFrame(function () {
                if (el.scrollHeight <= el.clientHeight + 2) {
                    // 内容没超出 2 行，取消 clamp
                    el.classList.remove('is-clamped');
                    return;
                }

                // 插入展开按钮
                var toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'comment-content-toggle';
                toggle.innerHTML = '展开 <i class="fa-solid fa-chevron-down" style="font-size:11px"></i>';
                el.parentNode.insertBefore(toggle, el.nextSibling);

                toggle.addEventListener('click', function () {
                    var clamped = el.classList.contains('is-clamped');
                    if (clamped) {
                        el.classList.remove('is-clamped');
                        toggle.innerHTML = '收起 <i class="fa-solid fa-chevron-up" style="font-size:11px"></i>';
                    } else {
                        el.classList.add('is-clamped');
                        toggle.innerHTML = '展开 <i class="fa-solid fa-chevron-down" style="font-size:11px"></i>';
                    }
                });
            });
        });
    }

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
    function md5(s){function f(a,b){var c=(a&65535)+(b&65535);return(((a>>16)+(b>>16)+(c>>16))<<16)|(c&65535)}function g(a,b){return(a<<b)|(a>>>(32-b))}function h(a,b,c,d,e,i,j){return f(g(f(f(b,a),f(e,j)),i),c)}function ff(a,b,c,d,e,i,j){return h((b&c)|((~b)&d),a,b,e,i,j)}function gg(a,b,c,d,e,i,j){return h((b&d)|(c&(~d)),a,b,e,i,j)}function hh(a,b,c,d,e,i,j){return h(b^c^d,a,b,e,i,j)}function ii(a,b,c,d,e,i,j){return h(c^(b|(~d)),a,b,e,i,j)}function md5c(x,l){x[l>>5]|=128<<(l%32);x[(((l+64)>>>9)<<4)+14]=l;var a=1732584193,b=-271733879,c=-1732584194,d=271733878;for(var i=0;i<x.length;i+=16){var o=a,p=b,q=c,r=d;a=ff(a,b,c,d,x[i],7,-680876936);d=ff(d,a,b,c,x[i+1],12,-389564586);c=ff(c,d,a,b,x[i+2],17,606105819);b=ff(b,c,d,a,x[i+3],22,-1044525330);a=ff(a,b,c,d,x[i+4],7,-176418897);d=ff(d,a,b,c,x[i+5],12,1200080426);c=ff(c,d,a,b,x[i+6],17,-1473231341);b=ff(b,c,d,a,x[i+7],22,-45705983);a=ff(a,b,c,d,x[i+8],7,1770035416);d=ff(d,a,b,c,x[i+9],12,-1958414417);c=ff(c,d,a,b,x[i+10],17,-42063);b=ff(b,c,d,a,x[i+11],22,-1990404162);a=ff(a,b,c,d,x[i+12],7,1804603682);d=ff(d,a,b,c,x[i+13],12,-40341101);c=ff(c,d,a,b,x[i+14],17,-1502002290);b=ff(b,c,d,a,x[i+15],22,1236535329);a=gg(a,b,c,d,x[i+1],5,-165796510);d=gg(d,a,b,c,x[i+6],9,-1069501632);c=gg(c,d,a,b,x[i+11],14,643717713);b=gg(b,c,d,a,x[i],20,-373897302);a=gg(a,b,c,d,x[i+5],5,-701558691);d=gg(d,a,b,c,x[i+10],9,38016083);c=gg(c,d,a,b,x[i+15],14,-660478335);b=gg(b,c,d,a,x[i+4],20,-405537848);a=gg(a,b,c,d,x[i+9],5,568446438);d=gg(d,a,b,c,x[i+14],9,-1019803690);c=gg(c,d,a,b,x[i+3],14,-187363961);b=gg(b,c,d,a,x[i+8],20,1163531501);a=gg(a,b,c,d,x[i+13],5,-1444681467);d=gg(d,a,b,c,x[i+2],9,-51403784);c=gg(c,d,a,b,x[i+7],14,1735328473);b=gg(b,c,d,a,x[i+12],20,-1926607734);a=hh(a,b,c,d,x[i+5],4,-378558);d=hh(d,a,b,c,x[i+8],11,-2022574463);c=hh(c,d,a,b,x[i+11],16,1839030562);b=hh(b,c,d,a,x[i+14],23,-35309556);a=hh(a,b,c,d,x[i+1],4,-1530992060);d=hh(d,a,b,c,x[i+4],11,1272893353);c=hh(c,d,a,b,x[i+7],16,-155497632);b=hh(b,c,d,a,x[i+10],23,-1094730640);a=hh(a,b,c,d,x[i+13],4,681279174);d=hh(d,a,b,c,x[i],11,-358537222);c=hh(c,d,a,b,x[i+3],16,-722521979);b=hh(b,c,d,a,x[i+6],23,76029189);a=hh(a,b,c,d,x[i+9],4,-640364487);d=hh(d,a,b,c,x[i+12],11,-421815835);c=hh(c,d,a,b,x[i+15],16,530742520);b=hh(b,c,d,a,x[i+2],23,-995338651);a=ii(a,b,c,d,x[i],6,-198630844);d=ii(d,a,b,c,x[i+7],10,1126891415);c=ii(c,d,a,b,x[i+14],15,-1416354905);b=ii(b,c,d,a,x[i+5],21,-57434055);a=ii(a,b,c,d,x[i+12],6,1700485571);d=ii(d,a,b,c,x[i+3],10,-1894986606);c=ii(c,d,a,b,x[i+10],15,-1051523);b=ii(b,c,d,a,x[i+1],21,-2054922799);a=ii(a,b,c,d,x[i+8],6,1873313359);d=ii(d,a,b,c,x[i+15],10,-30611744);c=ii(c,d,a,b,x[i+6],15,-1560198380);b=ii(b,c,d,a,x[i+13],21,1309151649);a=ii(a,b,c,d,x[i+4],6,-145523070);d=ii(d,a,b,c,x[i+11],10,-1120210379);c=ii(c,d,a,b,x[i+2],15,718787259);b=ii(b,c,d,a,x[i+9],21,-343485551);a=f(a,o);b=f(b,p);c=f(c,q);d=f(d,r)}return[a,b,c,d]}function str2bin(s){var b=[];for(var i=0;i<s.length*8;i+=8)b[i>>5]|=(s.charCodeAt(i/8)&255)<<(i%32);return b}function bin2hex(b){var h='0123456789abcdef',s='';for(var i=0;i<b.length*4;i++)s+=h.charAt((b[i>>2]>>((i%4)*8+4))&15)+h.charAt((b[i>>2]>>((i%4)*8))&15);return s}return bin2hex(md5c(str2bin(s),s.length*8))}

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
            avatarWrap.innerHTML = '<img class="lared-title-avatar" src="' + baseUrl + hash + '?s=48&d=mp" style="width:24px;height:24px;border-radius:2px;object-fit:cover;vertical-align:middle;" alt="">';
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
            if (submitButton.tagName === 'INPUT') {
                submitButton.value = '';
            } else {
                submitButton.textContent = '';
            }

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

                        if (window.addComment && typeof window.addComment.init === 'function') {
                            // 保存滚动位置，防止 addComment.init() 跳转
                            var savedScrollY = window.pageYOffset || document.documentElement.scrollTop;
                            window.addComment.init();
                            window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                        }
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

            // 转义 HTML 特殊字符，防止代码内容被浏览器解析执行
            // 检查是否有子元素（如果有，说明 HTML 被解析了，需要转义）
            if (codeEl.children.length > 0) {
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
        var contents = Array.prototype.slice.call(document.querySelectorAll('.single-article-content, .home-article-body, .memos-card-body'));
        contents.forEach(function (content) {
            if (!content.hasAttribute('view-image')) {
                content.setAttribute('view-image', '');
            }
        });

        // Initialize ViewImage
        window.ViewImage && window.ViewImage.init('.single-article-content img, .home-article-body img, .memos-card-body img');
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
            img.classList.add('is-loaded');
            wrapper.classList.add('is-loaded');
        }
        
        if (img.complete && img.naturalWidth > 0) {
            markLoaded();
        } else {
            img.addEventListener('load', markLoaded);
            img.addEventListener('error', function() {
                wrapper.classList.add('is-error');
            });
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
        
        // 创建 loading 圆圈 - 使用更明确的样式确保圆形
        var spinner = document.createElement('div');
        spinner.className = 'img-loading-spinner';
        spinner.innerHTML = '<div class="spinner-circle" style="border-radius:50% !important;"></div>';
        
        // 设置图片类名
        img.classList.add('img-loading-target');
        
        // 包装图片
        img.parentNode.insertBefore(wrapper, img);
        wrapper.appendChild(spinner);
        wrapper.appendChild(img);
        
        // 强制触发重绘以确保圆形生效
        spinner.offsetHeight;
        
        // 监听加载完成
        function onImageLoad() {
            img.classList.add('is-loaded');
            wrapper.classList.add('is-loaded');
            
            // 如果有动画类型，也添加动画类
            var animationType = document.documentElement.getAttribute('data-img-animation');
            if (animationType && animationType !== 'none') {
                img.setAttribute('data-img-animation', animationType);
                setTimeout(function () {
                    img.classList.add('img-loaded');
                }, 100);
            }
        }
        
        // 检查图片是否已加载完成（包括缓存）
        if (img.complete && img.naturalWidth > 0) {
            // 图片已缓存，直接显示
            onImageLoad();
        } else {
            img.addEventListener('load', onImageLoad);
            
            img.addEventListener('error', function () {
                wrapper.classList.add('is-error');
                spinner.innerHTML = '<div class="img-loading-error-icon"><i class="fa-solid fa-circle-exclamation"></i></div>';
            });
        }
    }

    /* image-load-animation.js */
    function initImageLoadAnimation() {
        // 获取主题设置（通过 data 属性传递）
        var htmlEl = document.documentElement;
        var animationType = htmlEl.getAttribute('data-img-animation') || 'none';
        
        if (animationType === 'none') {
            return;
        }

        // 为所有图片添加动画属性
        var images = Array.prototype.slice.call(document.querySelectorAll('img'));
        
        images.forEach(function (img) {
            // 排除已加载完成的图片
            if (img.complete) {
                img.classList.add('img-loaded');
                return;
            }

            // 排除特定图片
            if (img.closest('pre, code') || 
                img.classList.contains('emoji') || 
                img.classList.contains('avatar') ||
                img.hasAttribute('data-hero-main-image')) {
                return;
            }

            // 设置动画类型
            img.setAttribute('data-img-animation', animationType);

            // 监听加载完成
            img.addEventListener('load', function () {
                img.classList.add('img-loaded');
            });

            // 监听加载失败
            img.addEventListener('error', function () {
                img.classList.add('img-error');
            });
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
                rssButton.setAttribute('title', '复制成功');
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
                    rssButton.removeAttribute('title');
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

    /* header-login.js - Header 和 Footer 登录下拉框 */
    function initHeaderLogin() {
        // 支持多个登录按钮（header 和 footer）
        var loginWrappers = document.querySelectorAll('.header-login-wrapper, .footer-login-wrapper');
        
        if (!loginWrappers.length) {
            return;
        }

        loginWrappers.forEach(function(loginWrapper) {
            var loginToggle = loginWrapper.querySelector('[data-login-toggle]');
            var loginDropdown = loginWrapper.querySelector('[data-login-dropdown]');

            if (!loginToggle || !loginDropdown) {
                return;
            }

            // 切换下拉框显示/隐藏
            loginToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                
                // 先关闭其他所有下拉框
                document.querySelectorAll('[data-login-dropdown]').forEach(function(dropdown) {
                    if (dropdown !== loginDropdown) {
                        dropdown.classList.remove('is-active');
                    }
                });
                document.querySelectorAll('.header-login-wrapper, .footer-login-wrapper').forEach(function(wrapper) {
                    if (wrapper !== loginWrapper) {
                        wrapper.classList.remove('is-open');
                    }
                });
                
                var isActive = loginDropdown.classList.contains('is-active');
                
                if (isActive) {
                    loginDropdown.classList.remove('is-active');
                    loginWrapper.classList.remove('is-open');
                } else {
                    loginDropdown.classList.add('is-active');
                    loginWrapper.classList.add('is-open');
                }
            });

            // 点击下拉框内部不关闭
            loginDropdown.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        // 点击外部关闭所有下拉框（只绑定一次）
        if (!window._loginClickHandlerBound) {
            document.addEventListener('click', function () {
                document.querySelectorAll('[data-login-dropdown]').forEach(function(dropdown) {
                    dropdown.classList.remove('is-active');
                });
                document.querySelectorAll('.header-login-wrapper, .footer-login-wrapper').forEach(function(wrapper) {
                    wrapper.classList.remove('is-open');
                });
            });
            window._loginClickHandlerBound = true;
        }

        // ESC 键关闭所有下拉框（只绑定一次）
        if (!window._loginKeyHandlerBound) {
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('[data-login-dropdown]').forEach(function(dropdown) {
                        dropdown.classList.remove('is-active');
                    });
                    document.querySelectorAll('.header-login-wrapper, .footer-login-wrapper').forEach(function(wrapper) {
                        wrapper.classList.remove('is-open');
                    });
                }
            });
            window._loginKeyHandlerBound = true;
        }
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
        });

        document.addEventListener('pjax:complete', function () {
            loadingHide();
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
        initAPlayer();
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
            document.body.style.overflow = 'hidden';
            setTimeout(function() {
                if (input) input.focus();
            }, 100);
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
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

    window.LaredTheme = { init: init };
    window.LaredPrism = { init: initPrismEnhance };

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('DOMContentLoaded', initMemosPublish);
    document.addEventListener('DOMContentLoaded', initMemosFilter);
}());
