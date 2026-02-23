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

    /* home-article-toc.js */
    function initToc() {
        var sections = Array.prototype.slice.call(document.querySelectorAll('.home-article'));

        if (!sections.length) {
            return;
        }

        sections.forEach(function (section) {
            var links = Array.prototype.slice.call(section.querySelectorAll('[data-toc-link]'));
            var headings = Array.prototype.slice.call(section.querySelectorAll('.home-article-body h2[id], .home-article-body h3[id]'));
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

            if (links.length && headings.length) {
                var parentByLink = new Map();
                var linkById = new Map();
                var currentParentId = null;

                links.forEach(function (link) {
                    var href = link.getAttribute('href') || '';
                    var id = href.startsWith('#') ? href.slice(1) : '';
                    if (id) {
                        linkById.set(id, link);
                    }

                    if (link.classList.contains('level-2')) {
                        currentParentId = id || null;
                        parentByLink.set(link, currentParentId);
                        return;
                    }

                    parentByLink.set(link, currentParentId);
                });

                var setExpandedParent = function (parentId) {
                    links.forEach(function (link) {
                        if (!link.classList.contains('level-3')) {
                            return;
                        }

                        var parent = parentByLink.get(link);
                        var visible = parentId && parent === parentId;
                        link.classList.toggle('is-visible', !!visible);
                    });
                };

                var activateLink = function (id) {
                    links.forEach(function (link) {
                        var isActive = link.getAttribute('href') === '#' + id;
                        link.classList.toggle('is-active', isActive);
                    });

                    var activeLink = linkById.get(id);
                    if (!activeLink) {
                        setExpandedParent(null);
                        return;
                    }

                    if (activeLink.classList.contains('level-2')) {
                        setExpandedParent(id);
                        return;
                    }

                    var parentId = parentByLink.get(activeLink);
                    setExpandedParent(parentId || null);
                };

                var firstLevel2 = links.find(function (link) {
                    return link.classList.contains('level-2');
                });
                if (firstLevel2) {
                    var firstId = (firstLevel2.getAttribute('href') || '').replace('#', '');
                    setExpandedParent(firstId || null);
                } else {
                    setExpandedParent(null);
                }

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
                        var top = target.getBoundingClientRect().top - content.getBoundingClientRect().top + content.scrollTop - 22;
                        content.scrollTo({ top: top, behavior: 'smooth' });
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
                        root: content,
                        rootMargin: '-80px 0px -60% 0px',
                        threshold: 0.1,
                    }
                );

                headings.forEach(function (heading) {
                    observer.observe(heading);
                });
            }

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

        if (!toc || !content || toc.getAttribute('data-single-toc-ready') === '1') {
            return;
        }

        toc.setAttribute('data-single-toc-ready', '1');

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
        if (!commentNode || commentNode.querySelector('.lared-comment-new-hint')) {
            return;
        }

        var metaNode = commentNode.querySelector('.comment-meta, .comment-metadata, .comment-author');
        if (!metaNode) {
            return;
        }

        var hint = document.createElement('span');
        hint.className = 'lared-comment-new-hint';
        hint.textContent = ' · 刚刚发布（1 分钟内可编辑）';
        hint.style.color = '#999';
        hint.style.fontSize = '12px';
        metaNode.appendChild(hint);
    }

    function scrollToNewComment(commentNode) {
        if (!commentNode || typeof commentNode.scrollIntoView !== 'function') {
            return;
        }

        commentNode.scrollIntoView({ behavior: 'smooth', block: 'center' });

        commentNode.classList.add('lared-comment-newly-added');
        window.setTimeout(function () {
            commentNode.classList.remove('lared-comment-newly-added');
        }, 1500);
    }

    function initAjaxCommentSubmit() {
        var form = document.getElementById('commentform');
        if (!form || form.getAttribute('data-ajax-ready') === '1') {
            return;
        }

        if (!window.LaredCommentAjax || !window.LaredCommentAjax.ajaxUrl || !window.LaredCommentAjax.nonce) {
            return;
        }

        form.setAttribute('data-ajax-ready', '1');

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

            var formData = new FormData(form);
            formData.append('action', 'lared_submit_comment');
            formData.append('nonce', window.LaredCommentAjax.nonce);

            fetch(window.LaredCommentAjax.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData,
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (result) {
                    var notice = ensureCommentNotice(form);

                    if (!result || !result.success) {
                        var errorMessage = (result && result.data && result.data.message)
                            ? result.data.message
                            : window.LaredCommentAjax.errorMessage;
                        notice.textContent = errorMessage;
                        notice.style.color = '#c53030';
                        return;
                    }

                    notice.textContent = result.data.message || window.LaredCommentAjax.successMessage;
                    notice.style.color = '#2f855a';

                    if (result.data.approved) {
                        var newCommentNode = insertCommentHtml(result.data);
                        updateCommentStats(result.data);
                        markNewCommentHint(newCommentNode);
                        scrollToNewComment(newCommentNode);
                    }

                    var commentField = form.querySelector('#comment');
                    if (commentField) {
                        commentField.value = '';
                    }

                    if (window.addComment && typeof window.addComment.init === 'function') {
                        window.addComment.init();
                    }
                })
                .catch(function () {
                    var notice = ensureCommentNotice(form);
                    notice.textContent = window.LaredCommentAjax.errorMessage;
                    notice.style.color = '#c53030';
                })
                .finally(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });
        });
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

        function loadingShow() {
            if (!headerLoading) {
                return;
            }
            headerLoading.classList.add('is-active');
        }

        function loadingHide() {
            if (!headerLoading) {
                return;
            }
            headerLoading.classList.remove('is-active');
        }

        var pjax = new window.Pjax({
            elements: 'a[href], form[action]',
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
        initRssCopyButton();
        initArticleImageLoading();
        initHeaderLogin();
        initInlineCodeCleaner();
        initSearchModal();
        initMemosPublish();
        initMemosFilter();
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

        // 触发按钮
        document.querySelectorAll('[data-search-open]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        });

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
