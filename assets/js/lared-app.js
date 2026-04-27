/* ============================================================
 *  Lared Theme - app.js (merged bundle)
 *  Includes: lazysizes, Pjax, ViewImage, Theme main
 * ============================================================ */

/* ----------------------------------------------------------------
 *  0. lazysizes v5.3.2 - high-performance lazy loader
 * ---------------------------------------------------------------- */
/*! lazysizes - v5.3.2 */

!(function (e) {
  var t = (function (u, D, f) {
    "use strict";
    var k, H;
    if (
      ((function () {
        var e;
        var t = {
          lazyClass: "lazyload",
          loadedClass: "lazyloaded",
          loadingClass: "lazyloading",
          preloadClass: "lazypreload",
          errorClass: "lazyerror",
          autosizesClass: "lazyautosizes",
          fastLoadedClass: "ls-is-cached",
          iframeLoadMode: 0,
          srcAttr: "data-src",
          srcsetAttr: "data-srcset",
          sizesAttr: "data-sizes",
          minSize: 40,
          customMedia: {},
          init: true,
          expFactor: 1.5,
          hFac: 0.8,
          loadMode: 2,
          loadHidden: true,
          ricTimeout: 0,
          throttleDelay: 125,
        };
        H = u.lazySizesConfig || u.lazysizesConfig || {};
        for (e in t) {
          if (!(e in H)) {
            H[e] = t[e];
          }
        }
      })(),
      !D || !D.getElementsByClassName)
    ) {
      return { init: function () {}, cfg: H, noSupport: true };
    }
    var O = D.documentElement,
      i = u.HTMLPictureElement,
      P = "addEventListener",
      $ = "getAttribute",
      q = u[P].bind(u),
      I = u.setTimeout,
      U = u.requestAnimationFrame || I,
      o = u.requestIdleCallback,
      j = /^picture$/i,
      r = ["load", "error", "lazyincluded", "_lazyloaded"],
      a = {},
      G = Array.prototype.forEach,
      J = function (e, t) {
        if (!a[t]) {
          a[t] = new RegExp("(\\s|^)" + t + "(\\s|$)");
        }
        return a[t].test(e[$]("class") || "") && a[t];
      },
      K = function (e, t) {
        if (!J(e, t)) {
          e.setAttribute("class", (e[$]("class") || "").trim() + " " + t);
        }
      },
      Q = function (e, t) {
        var a;
        if ((a = J(e, t))) {
          e.setAttribute("class", (e[$]("class") || "").replace(a, " "));
        }
      },
      V = function (t, a, e) {
        var i = e ? P : "removeEventListener";
        if (e) {
          V(t, a);
        }
        r.forEach(function (e) {
          t[i](e, a);
        });
      },
      X = function (e, t, a, i, r) {
        var n = D.createEvent("Event");
        if (!a) {
          a = {};
        }
        a.instance = k;
        n.initEvent(t, !i, !r);
        n.detail = a;
        e.dispatchEvent(n);
        return n;
      },
      Y = function (e, t) {
        var a;
        if (!i && (a = u.picturefill || H.pf)) {
          if (t && t.src && !e[$]("srcset")) {
            e.setAttribute("srcset", t.src);
          }
          a({ reevaluate: true, elements: [e] });
        } else if (t && t.src) {
          e.src = t.src;
        }
      },
      Z = function (e, t) {
        return (getComputedStyle(e, null) || {})[t];
      },
      s = function (e, t, a) {
        a = a || e.offsetWidth;
        while (a < H.minSize && t && !e._lazysizesWidth) {
          a = t.offsetWidth;
          t = t.parentNode;
        }
        return a;
      },
      ee = (function () {
        var a, i;
        var t = [];
        var r = [];
        var n = t;
        var s = function () {
          var e = n;
          n = t.length ? r : t;
          a = true;
          i = false;
          while (e.length) {
            e.shift()();
          }
          a = false;
        };
        var e = function (e, t) {
          if (a && !t) {
            e.apply(this, arguments);
          } else {
            n.push(e);
            if (!i) {
              i = true;
              (D.hidden ? I : U)(s);
            }
          }
        };
        e._lsFlush = s;
        return e;
      })(),
      te = function (a, e) {
        return e
          ? function () {
              ee(a);
            }
          : function () {
              var e = this;
              var t = arguments;
              ee(function () {
                a.apply(e, t);
              });
            };
      },
      ae = function (e) {
        var a;
        var i = 0;
        var r = H.throttleDelay;
        var n = H.ricTimeout;
        var t = function () {
          a = false;
          i = f.now();
          e();
        };
        var s =
          o && n > 49
            ? function () {
                o(t, { timeout: n });
                if (n !== H.ricTimeout) {
                  n = H.ricTimeout;
                }
              }
            : te(function () {
                I(t);
              }, true);
        return function (e) {
          var t;
          if ((e = e === true)) {
            n = 33;
          }
          if (a) {
            return;
          }
          a = true;
          t = r - (f.now() - i);
          if (t < 0) {
            t = 0;
          }
          if (e || t < 9) {
            s();
          } else {
            I(s, t);
          }
        };
      },
      ie = function (e) {
        var t, a;
        var i = 99;
        var r = function () {
          t = null;
          e();
        };
        var n = function () {
          var e = f.now() - a;
          if (e < i) {
            I(n, i - e);
          } else {
            (o || r)(r);
          }
        };
        return function () {
          a = f.now();
          if (!t) {
            t = I(n, i);
          }
        };
      },
      e = (function () {
        var v, m, c, h, e;
        var y, z, g, p, C, b, A;
        var n = /^img$/i;
        var d = /^iframe$/i;
        var E = "onscroll" in u && !/(gle|ing)bot/.test(navigator.userAgent);
        var _ = 0;
        var w = 0;
        var M = 0;
        var N = -1;
        var L = function (e) {
          M--;
          if (!e || M < 0 || !e.target) {
            M = 0;
          }
        };
        var x = function (e) {
          if (A == null) {
            A = Z(D.body, "visibility") == "hidden";
          }
          return (
            A ||
            !(
              Z(e.parentNode, "visibility") == "hidden" &&
              Z(e, "visibility") == "hidden"
            )
          );
        };
        var W = function (e, t) {
          var a;
          var i = e;
          var r = x(e);
          g -= t;
          b += t;
          p -= t;
          C += t;
          while (r && (i = i.offsetParent) && i != D.body && i != O) {
            r = (Z(i, "opacity") || 1) > 0;
            if (r && Z(i, "overflow") != "visible") {
              a = i.getBoundingClientRect();
              r =
                C > a.left && p < a.right && b > a.top - 1 && g < a.bottom + 1;
            }
          }
          return r;
        };
        var t = function () {
          var e, t, a, i, r, n, s, o, l, u, f, c;
          var d = k.elements;
          if ((h = H.loadMode) && M < 8 && (e = d.length)) {
            t = 0;
            N++;
            for (; t < e; t++) {
              if (!d[t] || d[t]._lazyRace) {
                continue;
              }
              if (!E || (k.prematureUnveil && k.prematureUnveil(d[t]))) {
                R(d[t]);
                continue;
              }
              if (!(o = d[t][$]("data-expand")) || !(n = o * 1)) {
                n = w;
              }
              if (!u) {
                u =
                  !H.expand || H.expand < 1
                    ? O.clientHeight > 500 && O.clientWidth > 500
                      ? 500
                      : 370
                    : H.expand;
                k._defEx = u;
                f = u * H.expFactor;
                c = H.hFac;
                A = null;
                if (w < f && M < 1 && N > 2 && h > 2 && !D.hidden) {
                  w = f;
                  N = 0;
                } else if (h > 1 && N > 1 && M < 6) {
                  w = u;
                } else {
                  w = _;
                }
              }
              if (l !== n) {
                y = innerWidth + n * c;
                z = innerHeight + n;
                s = n * -1;
                l = n;
              }
              a = d[t].getBoundingClientRect();
              if (
                (b = a.bottom) >= s &&
                (g = a.top) <= z &&
                (C = a.right) >= s * c &&
                (p = a.left) <= y &&
                (b || C || p || g) &&
                (H.loadHidden || x(d[t])) &&
                ((m && M < 3 && !o && (h < 3 || N < 4)) || W(d[t], n))
              ) {
                R(d[t]);
                r = true;
                if (M > 9) {
                  break;
                }
              } else if (
                !r &&
                m &&
                !i &&
                M < 4 &&
                N < 4 &&
                h > 2 &&
                (v[0] || H.preloadAfterLoad) &&
                (v[0] ||
                  (!o && (b || C || p || g || d[t][$](H.sizesAttr) != "auto")))
              ) {
                i = v[0] || d[t];
              }
            }
            if (i && !r) {
              R(i);
            }
          }
        };
        var a = ae(t);
        var S = function (e) {
          var t = e.target;
          if (t._lazyCache) {
            delete t._lazyCache;
            return;
          }
          L(e);
          K(t, H.loadedClass);
          Q(t, H.loadingClass);
          V(t, B);
          X(t, "lazyloaded");
        };
        var i = te(S);
        var B = function (e) {
          i({ target: e.target });
        };
        var T = function (e, t) {
          var a = e.getAttribute("data-load-mode") || H.iframeLoadMode;
          if (a == 0) {
            e.contentWindow.location.replace(t);
          } else if (a == 1) {
            e.src = t;
          }
        };
        var F = function (e) {
          var t;
          var a = e[$](H.srcsetAttr);
          if ((t = H.customMedia[e[$]("data-media") || e[$]("media")])) {
            e.setAttribute("media", t);
          }
          if (a) {
            e.setAttribute("srcset", a);
          }
        };
        var s = te(function (t, e, a, i, r) {
          var n, s, o, l, u, f;
          if (!(u = X(t, "lazybeforeunveil", e)).defaultPrevented) {
            if (i) {
              if (a) {
                K(t, H.autosizesClass);
              } else {
                t.setAttribute("sizes", i);
              }
            }
            s = t[$](H.srcsetAttr);
            n = t[$](H.srcAttr);
            if (r) {
              o = t.parentNode;
              l = o && j.test(o.nodeName || "");
            }
            f = e.firesLoad || ("src" in t && (s || n || l));
            u = { target: t };
            K(t, H.loadingClass);
            if (f) {
              clearTimeout(c);
              c = I(L, 2500);
              V(t, B, true);
            }
            if (l) {
              G.call(o.getElementsByTagName("source"), F);
            }
            if (s) {
              t.setAttribute("srcset", s);
            } else if (n && !l) {
              if (d.test(t.nodeName)) {
                T(t, n);
              } else {
                t.src = n;
              }
            }
            if (r && (s || l)) {
              Y(t, { src: n });
            }
          }
          if (t._lazyRace) {
            delete t._lazyRace;
          }
          Q(t, H.lazyClass);
          ee(function () {
            var e = t.complete && t.naturalWidth > 1;
            if (!f || e) {
              if (e) {
                K(t, H.fastLoadedClass);
              }
              S(u);
              t._lazyCache = true;
              I(function () {
                if ("_lazyCache" in t) {
                  delete t._lazyCache;
                }
              }, 9);
            }
            if (t.loading == "lazy") {
              M--;
            }
          }, true);
        });
        var R = function (e) {
          if (e._lazyRace) {
            return;
          }
          var t;
          var a = n.test(e.nodeName);
          var i = a && (e[$](H.sizesAttr) || e[$]("sizes"));
          var r = i == "auto";
          if (
            (r || !m) &&
            a &&
            (e[$]("src") || e.srcset) &&
            !e.complete &&
            !J(e, H.errorClass) &&
            J(e, H.lazyClass)
          ) {
            return;
          }
          t = X(e, "lazyunveilread").detail;
          if (r) {
            re.updateElem(e, true, e.offsetWidth);
          }
          e._lazyRace = true;
          M++;
          s(e, t, r, i, a);
        };
        var r = ie(function () {
          H.loadMode = 3;
          a();
        });
        var o = function () {
          if (H.loadMode == 3) {
            H.loadMode = 2;
          }
          r();
        };
        var l = function () {
          if (m) {
            return;
          }
          if (f.now() - e < 999) {
            I(l, 999);
            return;
          }
          m = true;
          H.loadMode = 3;
          a();
          q("scroll", o, true);
        };
        return {
          _: function () {
            e = f.now();
            k.elements = D.getElementsByClassName(H.lazyClass);
            v = D.getElementsByClassName(H.lazyClass + " " + H.preloadClass);
            q("scroll", a, true);
            q("resize", a, true);
            q("pageshow", function (e) {
              if (e.persisted) {
                var t = D.querySelectorAll("." + H.loadingClass);
                if (t.length && t.forEach) {
                  U(function () {
                    t.forEach(function (e) {
                      if (e.complete) {
                        R(e);
                      }
                    });
                  });
                }
              }
            });
            if (u.MutationObserver) {
              new MutationObserver(a).observe(O, {
                childList: true,
                subtree: true,
                attributes: true,
              });
            } else {
              O[P]("DOMNodeInserted", a, true);
              O[P]("DOMAttrModified", a, true);
              setInterval(a, 999);
            }
            q("hashchange", a, true);
            [
              "focus",
              "mouseover",
              "click",
              "load",
              "transitionend",
              "animationend",
            ].forEach(function (e) {
              D[P](e, a, true);
            });
            if (/d$|^c/.test(D.readyState)) {
              l();
            } else {
              q("load", l);
              D[P]("DOMContentLoaded", a);
              I(l, 2e4);
            }
            if (k.elements.length) {
              t();
              ee._lsFlush();
            } else {
              a();
            }
          },
          checkElems: a,
          unveil: R,
          _aLSL: o,
        };
      })(),
      re = (function () {
        var a;
        var n = te(function (e, t, a, i) {
          var r, n, s;
          e._lazysizesWidth = i;
          i += "px";
          e.setAttribute("sizes", i);
          if (j.test(t.nodeName || "")) {
            r = t.getElementsByTagName("source");
            for (n = 0, s = r.length; n < s; n++) {
              r[n].setAttribute("sizes", i);
            }
          }
          if (!a.detail.dataAttr) {
            Y(e, a.detail);
          }
        });
        var i = function (e, t, a) {
          var i;
          var r = e.parentNode;
          if (r) {
            a = s(e, r, a);
            i = X(e, "lazybeforesizes", { width: a, dataAttr: !!t });
            if (!i.defaultPrevented) {
              a = i.detail.width;
              if (a && a !== e._lazysizesWidth) {
                n(e, r, i, a);
              }
            }
          }
        };
        var e = function () {
          var e;
          var t = a.length;
          if (t) {
            e = 0;
            for (; e < t; e++) {
              i(a[e]);
            }
          }
        };
        var t = ie(e);
        return {
          _: function () {
            a = D.getElementsByClassName(H.autosizesClass);
            q("resize", t);
          },
          checkElems: t,
          updateElem: i,
        };
      })(),
      t = function () {
        if (!t.i && D.getElementsByClassName) {
          t.i = true;
          re._();
          e._();
        }
      };
    return (
      I(function () {
        H.init && t();
      }),
      (k = {
        cfg: H,
        autoSizer: re,
        loader: e,
        init: t,
        uP: Y,
        aC: K,
        rC: Q,
        hC: J,
        fire: X,
        gW: s,
        rAF: ee,
      })
    );
  })(e, e.document, Date);
  ((e.lazySizes = t),
    "object" == typeof module && module.exports && (module.exports = t));
})("undefined" != typeof window ? window : {});

/* ----------------------------------------------------------------
 *  1. Pjax.js - AJAX page navigation
 * ---------------------------------------------------------------- */
(function (f) {
  if (typeof exports === "object" && typeof module !== "undefined") {
    module.exports = f();
  } else if (typeof define === "function" && define.amd) {
    define([], f);
  } else {
    var g;
    if (typeof window !== "undefined") {
      g = window;
    } else if (typeof global !== "undefined") {
      g = global;
    } else if (typeof self !== "undefined") {
      g = self;
    } else {
      g = this;
    }
    g.Pjax = f();
  }
})(function () {
  var define, module, exports;
  return (function () {
    function r(e, n, t) {
      function o(i, f) {
        if (!n[i]) {
          if (!e[i]) {
            var c = "function" == typeof require && require;
            if (!f && c) return c(i, !0);
            if (u) return u(i, !0);
            var a = new Error("Cannot find module '" + i + "'");
            throw ((a.code = "MODULE_NOT_FOUND"), a);
          }
          var p = (n[i] = { exports: {} });
          e[i][0].call(
            p.exports,
            function (r) {
              var n = e[i][1][r];
              return o(n || r);
            },
            p,
            p.exports,
            r,
            e,
            n,
            t,
          );
        }
        return n[i].exports;
      }
      for (
        var u = "function" == typeof require && require, i = 0;
        i < t.length;
        i++
      )
        o(t[i]);
      return o;
    }
    return r;
  })()(
    {
      1: [
        function (require, module, exports) {
          var executeScripts = require("./lib/execute-scripts");
          var forEachEls = require("./lib/foreach-els");
          var parseOptions = require("./lib/parse-options");
          var switches = require("./lib/switches");
          var newUid = require("./lib/uniqueid");
          var on = require("./lib/events/on");
          var trigger = require("./lib/events/trigger");
          var clone = require("./lib/util/clone");
          var contains = require("./lib/util/contains");
          var extend = require("./lib/util/extend");
          var noop = require("./lib/util/noop");
          var Pjax = function (options) {
            this.state = { numPendingSwitches: 0, href: null, options: null };
            this.options = parseOptions(options);
            this.log("Pjax options", this.options);
            if (
              this.options.scrollRestoration &&
              "scrollRestoration" in history
            ) {
              history.scrollRestoration = "manual";
            }
            this.maxUid = this.lastUid = newUid();
            this.parseDOM(document);
            on(
              window,
              "popstate",
              function (st) {
                if (st.state) {
                  var opt = clone(this.options);
                  opt.url = st.state.url;
                  opt.title = st.state.title;
                  opt.history = false;
                  opt.scrollPos = st.state.scrollPos;
                  if (st.state.uid < this.lastUid) {
                    opt.backward = true;
                  } else {
                    opt.forward = true;
                  }
                  this.lastUid = st.state.uid;
                  this.loadUrl(st.state.url, opt);
                }
              }.bind(this),
            );
          };
          Pjax.switches = switches;
          Pjax.prototype = {
            log: require("./lib/proto/log"),
            getElements: function (el) {
              return el.querySelectorAll(this.options.elements);
            },
            parseDOM: function (el) {
              var parseElement = require("./lib/proto/parse-element");
              forEachEls(this.getElements(el), parseElement, this);
            },
            refresh: function (el) {
              this.parseDOM(el || document);
            },
            reload: function () {
              window.location.reload();
            },
            attachLink: require("./lib/proto/attach-link"),
            attachForm: require("./lib/proto/attach-form"),
            forEachSelectors: function (cb, context, DOMcontext) {
              return require("./lib/foreach-selectors").bind(this)(
                this.options.selectors,
                cb,
                context,
                DOMcontext,
              );
            },
            switchSelectors: function (selectors, fromEl, toEl, options) {
              return require("./lib/switches-selectors").bind(this)(
                this.options.switches,
                this.options.switchesOptions,
                selectors,
                fromEl,
                toEl,
                options,
              );
            },
            latestChance: function (href) {
              window.location = href;
            },
            onSwitch: function () {
              trigger(window, "resize scroll");
              this.state.numPendingSwitches--;
              if (this.state.numPendingSwitches === 0) {
                this.afterAllSwitches();
              }
            },
            loadContent: function (html, options) {
              if (typeof html !== "string") {
                trigger(document, "pjax:complete pjax:error", options);
                return;
              }
              var tmpEl = document.implementation.createHTMLDocument("pjax");
              var htmlRegex = /<html[^>]+>/gi;
              var htmlAttribsRegex = /\s?[a-z:]+(?:=['"][^'">]+['"])*/gi;
              var matches = html.match(htmlRegex);
              if (matches && matches.length) {
                matches = matches[0].match(htmlAttribsRegex);
                if (matches.length) {
                  matches.shift();
                  matches.forEach(function (htmlAttrib) {
                    var attr = htmlAttrib.trim().split("=");
                    if (attr.length === 1) {
                      tmpEl.documentElement.setAttribute(attr[0], true);
                    } else {
                      tmpEl.documentElement.setAttribute(
                        attr[0],
                        attr[1].slice(1, -1),
                      );
                    }
                  });
                }
              }
              tmpEl.documentElement.innerHTML = html;
              this.log(
                "load content",
                tmpEl.documentElement.attributes,
                tmpEl.documentElement.innerHTML.length,
              );
              if (
                document.activeElement &&
                contains(
                  document,
                  this.options.selectors,
                  document.activeElement,
                )
              ) {
                try {
                  document.activeElement.blur();
                } catch (e) {}
              }
              this.switchSelectors(
                this.options.selectors,
                tmpEl,
                document,
                options,
              );
            },
            abortRequest: require("./lib/abort-request"),
            doRequest: require("./lib/send-request"),
            handleResponse: require("./lib/proto/handle-response"),
            loadUrl: function (href, options) {
              options =
                typeof options === "object"
                  ? extend({}, this.options, options)
                  : clone(this.options);
              this.log("load href", href, options);
              this.abortRequest(this.request);
              trigger(document, "pjax:send", options);
              this.request = this.doRequest(
                href,
                options,
                this.handleResponse.bind(this),
              );
            },
            afterAllSwitches: function () {
              var autofocusEl = Array.prototype.slice
                .call(document.querySelectorAll("[autofocus]"))
                .pop();
              if (autofocusEl && document.activeElement !== autofocusEl) {
                autofocusEl.focus();
              }
              this.options.selectors.forEach(function (selector) {
                forEachEls(document.querySelectorAll(selector), function (el) {
                  executeScripts(el);
                });
              });
              var state = this.state;
              if (state.options.history) {
                if (!window.history.state) {
                  this.lastUid = this.maxUid = newUid();
                  window.history.replaceState(
                    {
                      url: window.location.href,
                      title: document.title,
                      uid: this.maxUid,
                      scrollPos: [0, 0],
                    },
                    document.title,
                  );
                }
                this.lastUid = this.maxUid = newUid();
                window.history.pushState(
                  {
                    url: state.href,
                    title: state.options.title,
                    uid: this.maxUid,
                    scrollPos: [0, 0],
                  },
                  state.options.title,
                  state.href,
                );
              }
              this.forEachSelectors(function (el) {
                this.parseDOM(el);
              }, this);
              trigger(document, "pjax:complete pjax:success", state.options);
              if (typeof state.options.analytics === "function") {
                state.options.analytics();
              }
              if (state.options.history) {
                var a = document.createElement("a");
                a.href = this.state.href;
                if (a.hash) {
                  var name = a.hash.slice(1);
                  name = decodeURIComponent(name);
                  var curtop = 0;
                  var target =
                    document.getElementById(name) ||
                    document.getElementsByName(name)[0];
                  if (target) {
                    if (target.offsetParent) {
                      do {
                        curtop += target.offsetTop;
                        target = target.offsetParent;
                      } while (target);
                    }
                  }
                  window.scrollTo(0, curtop);
                } else if (state.options.scrollTo !== false) {
                  if (state.options.scrollTo.length > 1) {
                    window.scrollTo(
                      state.options.scrollTo[0],
                      state.options.scrollTo[1],
                    );
                  } else {
                    window.scrollTo(0, state.options.scrollTo);
                  }
                }
              } else if (
                state.options.scrollRestoration &&
                state.options.scrollPos
              ) {
                window.scrollTo(
                  state.options.scrollPos[0],
                  state.options.scrollPos[1],
                );
              }
              this.state = { numPendingSwitches: 0, href: null, options: null };
            },
          };
          Pjax.isSupported = require("./lib/is-supported");
          if (Pjax.isSupported()) {
            module.exports = Pjax;
          } else {
            var stupidPjax = noop;
            for (var key in Pjax.prototype) {
              if (
                Pjax.prototype.hasOwnProperty(key) &&
                typeof Pjax.prototype[key] === "function"
              ) {
                stupidPjax[key] = noop;
              }
            }
            module.exports = stupidPjax;
          }
        },
        {
          "./lib/abort-request": 2,
          "./lib/events/on": 4,
          "./lib/events/trigger": 5,
          "./lib/execute-scripts": 6,
          "./lib/foreach-els": 7,
          "./lib/foreach-selectors": 8,
          "./lib/is-supported": 9,
          "./lib/parse-options": 10,
          "./lib/proto/attach-form": 11,
          "./lib/proto/attach-link": 12,
          "./lib/proto/handle-response": 13,
          "./lib/proto/log": 14,
          "./lib/proto/parse-element": 15,
          "./lib/send-request": 16,
          "./lib/switches": 18,
          "./lib/switches-selectors": 17,
          "./lib/uniqueid": 19,
          "./lib/util/clone": 20,
          "./lib/util/contains": 21,
          "./lib/util/extend": 22,
          "./lib/util/noop": 23,
        },
      ],
      2: [
        function (require, module, exports) {
          var noop = require("./util/noop");
          module.exports = function (request) {
            if (request && request.readyState < 4) {
              request.onreadystatechange = noop;
              request.abort();
            }
          };
        },
        { "./util/noop": 23 },
      ],
      3: [
        function (require, module, exports) {
          module.exports = function (el) {
            var code = el.text || el.textContent || el.innerHTML || "";
            var src = el.src || "";
            var parent =
              el.parentNode ||
              document.querySelector("head") ||
              document.documentElement;
            var script = document.createElement("script");
            if (code.match("document.write")) {
              if (console && console.log) {
                console.log(
                  "Script contains document.write. Can’t be executed correctly. Code skipped ",
                  el,
                );
              }
              return false;
            }
            script.type = "text/javascript";
            script.id = el.id;
            if (src !== "") {
              script.src = src;
              script.async = false;
            }
            if (code !== "") {
              try {
                script.appendChild(document.createTextNode(code));
              } catch (e) {
                script.text = code;
              }
            }
            parent.appendChild(script);
            if (
              (parent instanceof HTMLHeadElement ||
                parent instanceof HTMLBodyElement) &&
              parent.contains(script)
            ) {
              parent.removeChild(script);
            }
            return true;
          };
        },
        {},
      ],
      4: [
        function (require, module, exports) {
          var forEachEls = require("../foreach-els");
          module.exports = function (els, events, listener, useCapture) {
            events = typeof events === "string" ? events.split(" ") : events;
            events.forEach(function (e) {
              forEachEls(els, function (el) {
                el.addEventListener(e, listener, useCapture);
              });
            });
          };
        },
        { "../foreach-els": 7 },
      ],
      5: [
        function (require, module, exports) {
          var forEachEls = require("../foreach-els");
          module.exports = function (els, events, opts) {
            events = typeof events === "string" ? events.split(" ") : events;
            events.forEach(function (e) {
              var event;
              event = document.createEvent("HTMLEvents");
              event.initEvent(e, true, true);
              event.eventName = e;
              if (opts) {
                Object.keys(opts).forEach(function (key) {
                  event[key] = opts[key];
                });
              }
              forEachEls(els, function (el) {
                var domFix = false;
                if (!el.parentNode && el !== document && el !== window) {
                  domFix = true;
                  document.body.appendChild(el);
                }
                el.dispatchEvent(event);
                if (domFix) {
                  el.parentNode.removeChild(el);
                }
              });
            });
          };
        },
        { "../foreach-els": 7 },
      ],
      6: [
        function (require, module, exports) {
          var forEachEls = require("./foreach-els");
          var evalScript = require("./eval-script");
          module.exports = function (el) {
            if (el.tagName.toLowerCase() === "script") {
              evalScript(el);
            }
            forEachEls(el.querySelectorAll("script"), function (script) {
              if (
                !script.type ||
                script.type.toLowerCase() === "text/javascript"
              ) {
                if (script.parentNode) {
                  script.parentNode.removeChild(script);
                }
                evalScript(script);
              }
            });
          };
        },
        { "./eval-script": 3, "./foreach-els": 7 },
      ],
      7: [
        function (require, module, exports) {
          module.exports = function (els, fn, context) {
            if (
              els instanceof HTMLCollection ||
              els instanceof NodeList ||
              els instanceof Array
            ) {
              return Array.prototype.forEach.call(els, fn, context);
            }
            return fn.call(context, els);
          };
        },
        {},
      ],
      8: [
        function (require, module, exports) {
          var forEachEls = require("./foreach-els");
          module.exports = function (selectors, cb, context, DOMcontext) {
            DOMcontext = DOMcontext || document;
            selectors.forEach(function (selector) {
              forEachEls(DOMcontext.querySelectorAll(selector), cb, context);
            });
          };
        },
        { "./foreach-els": 7 },
      ],
      9: [
        function (require, module, exports) {
          module.exports = function () {
            return (
              window.history &&
              window.history.pushState &&
              window.history.replaceState &&
              !navigator.userAgent.match(
                /((iPod|iPhone|iPad).+\bOS\s+[1-4]\D|WebApps\/.+CFNetwork)/,
              )
            );
          };
        },
        {},
      ],
      10: [
        function (require, module, exports) {
          var defaultSwitches = require("./switches");
          module.exports = function (options) {
            options = options || {};
            options.elements = options.elements || "a[href], form[action]";
            options.selectors = options.selectors || ["title", ".js-Pjax"];
            options.switches = options.switches || {};
            options.switchesOptions = options.switchesOptions || {};
            options.history =
              typeof options.history === "undefined" ? true : options.history;
            options.analytics =
              typeof options.analytics === "function" ||
              options.analytics === false
                ? options.analytics
                : defaultAnalytics;
            options.scrollTo =
              typeof options.scrollTo === "undefined" ? 0 : options.scrollTo;
            options.scrollRestoration =
              typeof options.scrollRestoration !== "undefined"
                ? options.scrollRestoration
                : true;
            options.cacheBust =
              typeof options.cacheBust === "undefined"
                ? true
                : options.cacheBust;
            options.debug = options.debug || false;
            options.timeout = options.timeout || 0;
            options.currentUrlFullReload =
              typeof options.currentUrlFullReload === "undefined"
                ? false
                : options.currentUrlFullReload;
            if (!options.switches.head) {
              options.switches.head = defaultSwitches.switchElementsAlt;
            }
            if (!options.switches.body) {
              options.switches.body = defaultSwitches.switchElementsAlt;
            }
            return options;
          };
          function defaultAnalytics() {
            if (window._gaq) {
              _gaq.push(["_trackPageview"]);
            }
            if (window.ga) {
              ga("send", "pageview", {
                page: location.pathname,
                title: document.title,
              });
            }
          }
        },
        { "./switches": 18 },
      ],
      11: [
        function (require, module, exports) {
          var on = require("../events/on");
          var clone = require("../util/clone");
          var attrState = "data-pjax-state";
          var formAction = function (el, event) {
            if (isDefaultPrevented(event)) {
              return;
            }
            var options = clone(this.options);
            options.requestOptions = {
              requestUrl: el.getAttribute("action") || window.location.href,
              requestMethod: el.getAttribute("method") || "GET",
            };
            var virtLinkElement = document.createElement("a");
            virtLinkElement.setAttribute(
              "href",
              options.requestOptions.requestUrl,
            );
            var attrValue = checkIfShouldAbort(virtLinkElement, options);
            if (attrValue) {
              el.setAttribute(attrState, attrValue);
              return;
            }
            event.preventDefault();
            if (el.enctype === "multipart/form-data") {
              options.requestOptions.formData = new FormData(el);
            } else {
              options.requestOptions.requestParams = parseFormElements(el);
            }
            el.setAttribute(attrState, "submit");
            options.triggerElement = el;
            this.loadUrl(virtLinkElement.href, options);
          };
          function parseFormElements(el) {
            var requestParams = [];
            var formElements = el.elements;
            for (var i = 0; i < formElements.length; i++) {
              var element = formElements[i];
              var tagName = element.tagName.toLowerCase();
              if (
                !!element.name &&
                element.attributes !== undefined &&
                tagName !== "button"
              ) {
                var type = element.attributes.type;
                if (
                  !type ||
                  (type.value !== "checkbox" && type.value !== "radio") ||
                  element.checked
                ) {
                  var values = [];
                  if (tagName === "select") {
                    var opt;
                    for (var j = 0; j < element.options.length; j++) {
                      opt = element.options[j];
                      if (opt.selected && !opt.disabled) {
                        values.push(
                          opt.hasAttribute("value") ? opt.value : opt.text,
                        );
                      }
                    }
                  } else {
                    values.push(element.value);
                  }
                  for (var k = 0; k < values.length; k++) {
                    requestParams.push({
                      name: encodeURIComponent(element.name),
                      value: encodeURIComponent(values[k]),
                    });
                  }
                }
              }
            }
            return requestParams;
          }
          function checkIfShouldAbort(virtLinkElement, options) {
            if (
              virtLinkElement.protocol !== window.location.protocol ||
              virtLinkElement.host !== window.location.host
            ) {
              return "external";
            }
            if (
              virtLinkElement.hash &&
              virtLinkElement.href.replace(virtLinkElement.hash, "") ===
                window.location.href.replace(location.hash, "")
            ) {
              return "anchor";
            }
            if (
              virtLinkElement.href ===
              window.location.href.split("#")[0] + "#"
            ) {
              return "anchor-empty";
            }
            if (
              options.currentUrlFullReload &&
              virtLinkElement.href === window.location.href.split("#")[0]
            ) {
              return "reload";
            }
          }
          var isDefaultPrevented = function (event) {
            return event.defaultPrevented || event.returnValue === false;
          };
          module.exports = function (el) {
            var that = this;
            el.setAttribute(attrState, "");
            on(el, "submit", function (event) {
              formAction.call(that, el, event);
            });
          };
        },
        { "../events/on": 4, "../util/clone": 20 },
      ],
      12: [
        function (require, module, exports) {
          var on = require("../events/on");
          var clone = require("../util/clone");
          var attrState = "data-pjax-state";
          var linkAction = function (el, event) {
            if (isDefaultPrevented(event)) {
              return;
            }
            var options = clone(this.options);
            var attrValue = checkIfShouldAbort(el, event);
            if (attrValue) {
              el.setAttribute(attrState, attrValue);
              return;
            }
            event.preventDefault();
            if (
              this.options.currentUrlFullReload &&
              el.href === window.location.href.split("#")[0]
            ) {
              el.setAttribute(attrState, "reload");
              this.reload();
              return;
            }
            el.setAttribute(attrState, "load");
            options.triggerElement = el;
            this.loadUrl(el.href, options);
          };
          function checkIfShouldAbort(el, event) {
            if (
              event.which > 1 ||
              event.metaKey ||
              event.ctrlKey ||
              event.shiftKey ||
              event.altKey
            ) {
              return "modifier";
            }
            if (
              el.protocol !== window.location.protocol ||
              el.host !== window.location.host
            ) {
              return "external";
            }
            if (
              el.hash &&
              el.href.replace(el.hash, "") ===
                window.location.href.replace(location.hash, "")
            ) {
              return "anchor";
            }
            if (el.href === window.location.href.split("#")[0] + "#") {
              return "anchor-empty";
            }
          }
          var isDefaultPrevented = function (event) {
            return event.defaultPrevented || event.returnValue === false;
          };
          module.exports = function (el) {
            var that = this;
            el.setAttribute(attrState, "");
            on(el, "click", function (event) {
              linkAction.call(that, el, event);
            });
            on(
              el,
              "keyup",
              function (event) {
                if (event.keyCode === 13) {
                  linkAction.call(that, el, event);
                }
              }.bind(this),
            );
          };
        },
        { "../events/on": 4, "../util/clone": 20 },
      ],
      13: [
        function (require, module, exports) {
          var clone = require("../util/clone");
          var newUid = require("../uniqueid");
          var trigger = require("../events/trigger");
          module.exports = function (responseText, request, href, options) {
            options = clone(options || this.options);
            options.request = request;
            if (responseText === false) {
              trigger(document, "pjax:complete pjax:error", options);
              return;
            }
            var currentState = window.history.state || {};
            window.history.replaceState(
              {
                url: currentState.url || window.location.href,
                title: currentState.title || document.title,
                uid: currentState.uid || newUid(),
                scrollPos: [
                  document.documentElement.scrollLeft ||
                    document.body.scrollLeft,
                  document.documentElement.scrollTop || document.body.scrollTop,
                ],
              },
              document.title,
              window.location.href,
            );
            var oldHref = href;
            if (request.responseURL) {
              if (href !== request.responseURL) {
                href = request.responseURL;
              }
            } else if (request.getResponseHeader("X-PJAX-URL")) {
              href = request.getResponseHeader("X-PJAX-URL");
            } else if (request.getResponseHeader("X-XHR-Redirected-To")) {
              href = request.getResponseHeader("X-XHR-Redirected-To");
            }
            var a = document.createElement("a");
            a.href = oldHref;
            var oldHash = a.hash;
            a.href = href;
            if (oldHash && !a.hash) {
              a.hash = oldHash;
              href = a.href;
            }
            this.state.href = href;
            this.state.options = options;
            try {
              this.loadContent(responseText, options);
            } catch (e) {
              trigger(document, "pjax:error", options);
              if (!this.options.debug) {
                if (console && console.error) {
                  console.error("Pjax switch fail: ", e);
                }
                return this.latestChance(href);
              } else {
                throw e;
              }
            }
          };
        },
        { "../events/trigger": 5, "../uniqueid": 19, "../util/clone": 20 },
      ],
      14: [
        function (require, module, exports) {
          module.exports = function () {
            if (this.options.debug && console) {
              if (typeof console.log === "function") {
                console.log.apply(console, arguments);
              } else if (console.log) {
                console.log(arguments);
              }
            }
          };
        },
        {},
      ],
      15: [
        function (require, module, exports) {
          var attrState = "data-pjax-state";
          module.exports = function (el) {
            switch (el.tagName.toLowerCase()) {
              case "a":
                if (!el.hasAttribute(attrState)) {
                  this.attachLink(el);
                }
                break;
              case "form":
                if (!el.hasAttribute(attrState)) {
                  this.attachForm(el);
                }
                break;
              default:
                throw "Pjax can only be applied on <a> or <form> submit";
            }
          };
        },
        {},
      ],
      16: [
        function (require, module, exports) {
          var updateQueryString = require("./util/update-query-string");
          module.exports = function (location, options, callback) {
            options = options || {};
            var queryString;
            var requestOptions = options.requestOptions || {};
            var requestMethod = (
              requestOptions.requestMethod || "GET"
            ).toUpperCase();
            var requestParams = requestOptions.requestParams || null;
            var formData = requestOptions.formData || null;
            var requestPayload = null;
            var request = new XMLHttpRequest();
            var timeout = options.timeout || 0;
            request.onreadystatechange = function () {
              if (request.readyState === 4) {
                if (request.status === 200) {
                  callback(request.responseText, request, location, options);
                } else if (request.status !== 0) {
                  callback(null, request, location, options);
                }
              }
            };
            request.onerror = function (e) {
              console.log(e);
              callback(null, request, location, options);
            };
            request.ontimeout = function () {
              callback(null, request, location, options);
            };
            if (requestParams && requestParams.length) {
              queryString = requestParams
                .map(function (param) {
                  return param.name + "=" + param.value;
                })
                .join("&");
              switch (requestMethod) {
                case "GET":
                  location = location.split("?")[0];
                  location += "?" + queryString;
                  break;
                case "POST":
                  requestPayload = queryString;
                  break;
              }
            } else if (formData) {
              requestPayload = formData;
            }
            if (options.cacheBust) {
              location = updateQueryString(location, "t", Date.now());
            }
            request.open(requestMethod, location, true);
            request.timeout = timeout;
            request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            request.setRequestHeader("X-PJAX", "true");
            request.setRequestHeader(
              "X-PJAX-Selectors",
              JSON.stringify(options.selectors),
            );
            if (requestPayload && requestMethod === "POST" && !formData) {
              request.setRequestHeader(
                "Content-Type",
                "application/x-www-form-urlencoded",
              );
            }
            request.send(requestPayload);
            return request;
          };
        },
        { "./util/update-query-string": 24 },
      ],
      17: [
        function (require, module, exports) {
          var forEachEls = require("./foreach-els");
          var defaultSwitches = require("./switches");
          module.exports = function (
            switches,
            switchesOptions,
            selectors,
            fromEl,
            toEl,
            options,
          ) {
            var switchesQueue = [];
            selectors.forEach(function (selector) {
              var newEls = fromEl.querySelectorAll(selector);
              var oldEls = toEl.querySelectorAll(selector);
              if (this.log) {
                this.log("Pjax switch", selector, newEls, oldEls);
              }
              if (newEls.length !== oldEls.length) {
                throw (
                  "DOM doesn’t look the same on new loaded page: ’" +
                  selector +
                  "’ - new " +
                  newEls.length +
                  ", old " +
                  oldEls.length
                );
              }
              forEachEls(
                newEls,
                function (newEl, i) {
                  var oldEl = oldEls[i];
                  if (this.log) {
                    this.log("newEl", newEl, "oldEl", oldEl);
                  }
                  var callback = switches[selector]
                    ? switches[selector].bind(
                        this,
                        oldEl,
                        newEl,
                        options,
                        switchesOptions[selector],
                      )
                    : defaultSwitches.outerHTML.bind(
                        this,
                        oldEl,
                        newEl,
                        options,
                      );
                  switchesQueue.push(callback);
                },
                this,
              );
            }, this);
            this.state.numPendingSwitches = switchesQueue.length;
            switchesQueue.forEach(function (queuedSwitch) {
              queuedSwitch();
            });
          };
        },
        { "./foreach-els": 7, "./switches": 18 },
      ],
      18: [
        function (require, module, exports) {
          var on = require("./events/on");
          module.exports = {
            outerHTML: function (oldEl, newEl) {
              oldEl.outerHTML = newEl.outerHTML;
              this.onSwitch();
            },
            innerHTML: function (oldEl, newEl) {
              oldEl.innerHTML = newEl.innerHTML;
              if (newEl.className === "") {
                oldEl.removeAttribute("class");
              } else {
                oldEl.className = newEl.className;
              }
              this.onSwitch();
            },
            switchElementsAlt: function (oldEl, newEl) {
              oldEl.innerHTML = newEl.innerHTML;
              if (newEl.hasAttributes()) {
                var attrs = newEl.attributes;
                for (var i = 0; i < attrs.length; i++) {
                  oldEl.attributes.setNamedItem(attrs[i].cloneNode());
                }
              }
              this.onSwitch();
            },
            replaceNode: function (oldEl, newEl) {
              oldEl.parentNode.replaceChild(newEl, oldEl);
              this.onSwitch();
            },
            sideBySide: function (oldEl, newEl, options, switchOptions) {
              var forEach = Array.prototype.forEach;
              var elsToRemove = [];
              var elsToAdd = [];
              var fragToAppend = document.createDocumentFragment();
              var animationEventNames =
                "animationend webkitAnimationEnd MSAnimationEnd oanimationend";
              var animatedElsNumber = 0;
              var sexyAnimationEnd = function (e) {
                if (e.target !== e.currentTarget) {
                  return;
                }
                animatedElsNumber--;
                if (animatedElsNumber <= 0 && elsToRemove) {
                  elsToRemove.forEach(function (el) {
                    if (el.parentNode) {
                      el.parentNode.removeChild(el);
                    }
                  });
                  elsToAdd.forEach(function (el) {
                    el.className = el.className.replace(
                      el.getAttribute("data-pjax-classes"),
                      "",
                    );
                    el.removeAttribute("data-pjax-classes");
                  });
                  elsToAdd = null;
                  elsToRemove = null;
                  this.onSwitch();
                }
              }.bind(this);
              switchOptions = switchOptions || {};
              forEach.call(oldEl.childNodes, function (el) {
                elsToRemove.push(el);
                if (el.classList && !el.classList.contains("js-Pjax-remove")) {
                  if (el.hasAttribute("data-pjax-classes")) {
                    el.className = el.className.replace(
                      el.getAttribute("data-pjax-classes"),
                      "",
                    );
                    el.removeAttribute("data-pjax-classes");
                  }
                  el.classList.add("js-Pjax-remove");
                  if (
                    switchOptions.callbacks &&
                    switchOptions.callbacks.removeElement
                  ) {
                    switchOptions.callbacks.removeElement(el);
                  }
                  if (switchOptions.classNames) {
                    el.className +=
                      " " +
                      switchOptions.classNames.remove +
                      " " +
                      (options.backward
                        ? switchOptions.classNames.backward
                        : switchOptions.classNames.forward);
                  }
                  animatedElsNumber++;
                  on(el, animationEventNames, sexyAnimationEnd, true);
                }
              });
              forEach.call(newEl.childNodes, function (el) {
                if (el.classList) {
                  var addClasses = "";
                  if (switchOptions.classNames) {
                    addClasses =
                      " js-Pjax-add " +
                      switchOptions.classNames.add +
                      " " +
                      (options.backward
                        ? switchOptions.classNames.forward
                        : switchOptions.classNames.backward);
                  }
                  if (
                    switchOptions.callbacks &&
                    switchOptions.callbacks.addElement
                  ) {
                    switchOptions.callbacks.addElement(el);
                  }
                  el.className += addClasses;
                  el.setAttribute("data-pjax-classes", addClasses);
                  elsToAdd.push(el);
                  fragToAppend.appendChild(el);
                  animatedElsNumber++;
                  on(el, animationEventNames, sexyAnimationEnd, true);
                }
              });
              oldEl.className = newEl.className;
              oldEl.appendChild(fragToAppend);
            },
          };
        },
        { "./events/on": 4 },
      ],
      19: [
        function (require, module, exports) {
          module.exports = (function () {
            var counter = 0;
            return function () {
              var id = "pjax" + new Date().getTime() + "_" + counter;
              counter++;
              return id;
            };
          })();
        },
        {},
      ],
      20: [
        function (require, module, exports) {
          module.exports = function (obj) {
            if (null === obj || "object" !== typeof obj) {
              return obj;
            }
            var copy = obj.constructor();
            for (var attr in obj) {
              if (obj.hasOwnProperty(attr)) {
                copy[attr] = obj[attr];
              }
            }
            return copy;
          };
        },
        {},
      ],
      21: [
        function (require, module, exports) {
          module.exports = function contains(doc, selectors, el) {
            for (var i = 0; i < selectors.length; i++) {
              var selectedEls = doc.querySelectorAll(selectors[i]);
              for (var j = 0; j < selectedEls.length; j++) {
                if (selectedEls[j].contains(el)) {
                  return true;
                }
              }
            }
            return false;
          };
        },
        {},
      ],
      22: [
        function (require, module, exports) {
          module.exports = function (target) {
            if (target == null) {
              return null;
            }
            var to = Object(target);
            for (var i = 1; i < arguments.length; i++) {
              var source = arguments[i];
              if (source != null) {
                for (var key in source) {
                  if (Object.prototype.hasOwnProperty.call(source, key)) {
                    to[key] = source[key];
                  }
                }
              }
            }
            return to;
          };
        },
        {},
      ],
      23: [
        function (require, module, exports) {
          module.exports = function () {};
        },
        {},
      ],
      24: [
        function (require, module, exports) {
          module.exports = function (uri, key, value) {
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf("?") !== -1 ? "&" : "?";
            if (uri.match(re)) {
              return uri.replace(re, "$1" + key + "=" + value + "$2");
            } else {
              return uri + separator + key + "=" + value;
            }
          };
        },
        {},
      ],
    },
    {},
    [1],
  )(1);
});

/* ----------------------------------------------------------------
 *  2. ViewImage.js 2.0.2 (optimized) - image lightbox
 * ---------------------------------------------------------------- */
/**
 * ViewImage.js 2.0.2 (optimized)
 * MIT License - http://www.opensource.org/licenses/mit-license.php
 * https://tokinx.github.io/ViewImage/
 *
 * Changes vs original:
 *   - Removed $jscomp ES5 polyfills (30+ lines), use native ES6
 *   - All border-radius removed (直角模式)
 *   - [view-image] img shows cursor:pointer on hover
 */
(function () {
  window.ViewImage = new (function () {
    var self = this;
    this.target = "[view-image] img";
    this._styleInjected = false;

    /* ── inject global cursor style once ── */
    this._injectPointerStyle = function () {
      if (self._styleInjected) return;
      var s = document.createElement("style");
      s.textContent =
        self.target
          .split(",")
          .map(function (t) {
            return t.trim() + ":not([no-view])";
          })
          .join(",") + "{cursor:pointer}";
      document.head.appendChild(s);
      self._styleInjected = true;
    };

    this.listener = function (a) {
      if (a.ctrlKey || a.metaKey || a.shiftKey || a.altKey) return;
      var selector = self.target
          .split(",")
          .map(function (t) {
            return t.trim() + ":not([no-view])";
          })
          .join(","),
        hit = a.target.closest(selector);
      if (!hit) return;
      var scope = hit.closest("[view-image]") || document.body;
      var list = Array.from(scope.querySelectorAll(selector)).map(
        function (el) {
          return el.href || el.src;
        },
      );
      self.display(list, hit.href || hit.src);
      a.stopPropagation();
      a.preventDefault();
    };

    this.init = function (customTarget) {
      if (customTarget) self.target = customTarget;
      document.removeEventListener("click", self.listener, false);
      document.addEventListener("click", self.listener, false);
      self._injectPointerStyle();
    };

    this.display = function (images, current) {
      var idx = images.indexOf(current);
      var overlay = new DOMParser().parseFromString(
        '<div class="view-image">' +
          "<style>" +
          ".view-image{position:fixed;inset:0;z-index:500;padding:1rem;display:flex;flex-direction:column;animation:view-image-in 300ms;backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}" +
          ".view-image__out{animation:view-image-out 300ms}" +
          "@keyframes view-image-in{0%{opacity:0}}" +
          "@keyframes view-image-out{100%{opacity:0}}" +
          ".view-image-btn{width:32px;height:32px;display:flex;justify-content:center;align-items:center;cursor:pointer;background-color:rgba(255,255,255,0.2)}" +
          ".view-image-btn:hover{background-color:rgba(255,255,255,0.5)}" +
          ".view-image-close__full{position:absolute;inset:0;background-color:rgba(48,55,66,0.3);z-index:unset;cursor:zoom-out;margin:0}" +
          ".view-image-container{height:0;flex:1;display:flex;align-items:center;justify-content:center}" +
          ".view-image-lead{display:contents}" +
          ".view-image-lead img{position:relative;z-index:1;max-width:100%;max-height:100%;object-fit:contain}" +
          ".view-image-lead__in img{animation:view-image-lead-in 300ms}" +
          ".view-image-lead__out img{animation:view-image-lead-out 300ms forwards}" +
          "@keyframes view-image-lead-in{0%{opacity:0;transform:translateY(-20px)}}" +
          "@keyframes view-image-lead-out{100%{opacity:0;transform:translateY(20px)}}" +
          "[class*=__out]~.view-image-loading{display:block}" +
          ".view-image-loading{position:absolute;inset:50%;width:8rem;height:2rem;color:#aab2bd;overflow:hidden;text-align:center;margin:-1rem -4rem;z-index:1;display:none}" +
          '.view-image-loading::after{content:"";position:absolute;inset:50% 0;width:100%;height:3px;background:rgba(255,255,255,0.5);transform:translateX(-100%) translateY(-50%);animation:view-image-loading 800ms -100ms ease-in-out infinite}' +
          "@keyframes view-image-loading{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}" +
          ".view-image-tools{display:flex;justify-content:space-between;align-content:center;color:#fff;max-width:600px;position:absolute;bottom:5%;left:1rem;right:1rem;backdrop-filter:blur(10px);margin:0 auto;padding:10px;background:rgba(0,0,0,0.1);margin-bottom:constant(safe-area-inset-bottom);margin-bottom:env(safe-area-inset-bottom);z-index:1}" +
          ".view-image-tools__count{width:60px;display:flex;align-items:center;justify-content:center}" +
          ".view-image-tools__flip{display:flex;gap:10px}" +
          ".view-image-tools [class*=-close]{margin:0 10px}" +
          "</style>" +
          '<div class="view-image-container">' +
          '<div class="view-image-lead"></div>' +
          '<div class="view-image-loading"></div>' +
          '<div class="view-image-close view-image-close__full"></div>' +
          "</div>" +
          '<div class="view-image-tools">' +
          '<div class="view-image-tools__count"><span><b class="view-image-index">' +
          (idx + 1) +
          "</b>/" +
          images.length +
          "</span></div>" +
          '<div class="view-image-tools__flip">' +
          '<div class="view-image-btn view-image-tools__flip-prev"><svg width="20" height="20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="48" height="48" fill="white" fill-opacity="0.01"/><path d="M31 36L19 24L31 12" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>' +
          '<div class="view-image-btn view-image-tools__flip-next"><svg width="20" height="20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="48" height="48" fill="white" fill-opacity="0.01"/><path d="M19 12L31 24L19 36" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>' +
          "</div>" +
          '<div class="view-image-btn view-image-close"><svg width="16" height="16" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="48" height="48" fill="white" fill-opacity="0.01"/><path d="M8 8L40 40" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 40L40 8" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>' +
          "</div>" +
          "</div>",
        "text/html",
      ).body.firstChild;

      var onKey = function (ev) {
        var map = {
          Escape: "close",
          ArrowLeft: "tools__flip-prev",
          ArrowRight: "tools__flip-next",
        };
        if (map[ev.key])
          overlay.querySelector(".view-image-" + map[ev.key]).click();
      };

      var loadImg = function (src) {
        var img = new Image();
        var lead = overlay.querySelector(".view-image-lead");
        lead.className = "view-image-lead view-image-lead__out";
        setTimeout(function () {
          lead.innerHTML = "";
          img.onload = function () {
            setTimeout(function () {
              lead.innerHTML =
                '<img src="' + img.src + '" alt="ViewImage" no-view/>';
              lead.className = "view-image-lead view-image-lead__in";
            }, 100);
          };
          img.src = src;
        }, 300);
      };

      document.body.appendChild(overlay);
      loadImg(current);
      window.addEventListener("keydown", onKey);

      overlay.onclick = function (ev) {
        if (ev.target.closest(".view-image-close")) {
          window.removeEventListener("keydown", onKey);
          overlay.onclick = null;
          overlay.classList.add("view-image__out");
          setTimeout(function () {
            overlay.remove();
          }, 290);
        } else if (ev.target.closest(".view-image-tools__flip")) {
          idx = ev.target.closest(".view-image-tools__flip-prev")
            ? idx === 0
              ? images.length - 1
              : idx - 1
            : idx === images.length - 1
              ? 0
              : idx + 1;
          loadImg(images[idx]);
          overlay.querySelector(".view-image-index").innerHTML = idx + 1;
        }
      };
    };
  })();
})();

/* ----------------------------------------------------------------
 *  3. Lared Theme Main
 * ---------------------------------------------------------------- */
(function () {
  "use strict";

  /* =========================
         Home Modules
         ========================= */

  /* home-article-tabs.js */
  function initTabs() {
    var tabs = Array.prototype.slice.call(
      document.querySelectorAll("[data-article-tab]"),
    );
    var panels = Array.prototype.slice.call(
      document.querySelectorAll("[data-article-panel]"),
    );

    if (!tabs.length || !panels.length) {
      return;
    }

    var activatePanel = function (targetId) {
      tabs.forEach(function (tab) {
        var active = tab.getAttribute("data-target") === targetId;
        tab.classList.toggle("is-active", active);
        tab.setAttribute("aria-selected", active ? "true" : "false");
      });

      panels.forEach(function (panel) {
        var active = panel.id === targetId;
        panel.classList.toggle("is-active", active);
        panel.setAttribute("aria-hidden", active ? "false" : "true");
      });
    };

    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        var targetId = tab.getAttribute("data-target");
        if (targetId) {
          activatePanel(targetId);
        }
      });
    });
  }

  /* home-hero-switch.js */
  function initHeroSwitch() {
    var heroItems = Array.prototype.slice.call(
      document.querySelectorAll("[data-hero-item]"),
    );
    if (!heroItems.length) {
      return;
    }

    var mainTitle = document.querySelector("[data-hero-main-title]");
    var mainLink = document.querySelector("[data-hero-main-link]");
    var mainImage = document.querySelector("[data-hero-main-image]");
    var mainFallback = document.querySelector("[data-hero-main-fallback]");
    var mainBadge = document.querySelector("[data-hero-main-badge]");
    var heroArticle = mainImage ? mainImage.closest("article") : null;

    if (!mainImage || !mainTitle || !mainLink) {
      return;
    }

    /* Hero 图片加载完成 → 移除骨架屏 shimmer */
    function onHeroImgLoaded() {
      if (heroArticle) {
        heroArticle.classList.add("hero-img-loaded");
      }
    }
    if (mainImage.complete && mainImage.naturalWidth > 0) {
      onHeroImgLoaded();
    } else {
      mainImage.addEventListener("load", onHeroImgLoaded, { once: true });
    }

    /* 当前展示的文章 ID — 从 DOM 读取初始值 */
    var currentPostId = heroArticle
      ? parseInt(heroArticle.getAttribute("data-hero-current-post-id"), 10) || 0
      : 0;

    /* ── 预取数据：从 data-hero-articles 解析到内存 ── */
    var heroLocalData = new Map();
    heroItems.forEach(function (item) {
      var raw = item.getAttribute("data-hero-articles");
      var articles = [];
      if (raw) {
        try { articles = JSON.parse(raw); } catch (e) { /* ignore */ }
      }
      var startIdx = parseInt(item.getAttribute("data-hero-start"), 10) || 0;
      heroLocalData.set(item, {
        articles: articles,
        cursor: startIdx,  /* 当前展示的文章索引 */
      });
    });

    /* Update right-side display with given data */
    var applyHeroData = function (
      item,
      title,
      image,
      link,
      badge,
      badgeKey,
      postId,
    ) {
      if (postId) {
        currentPostId = postId;
      }
      mainTitle.textContent = title;
      mainLink.setAttribute("href", link);

      if (image) {
        /* 重置模糊 → 切换图片 → 加载完成后淡入 */
        if (heroArticle) {
          heroArticle.classList.remove("hero-img-loaded");
        }
        mainImage.setAttribute("src", image);
        mainImage.setAttribute("alt", title);
        if (mainFallback) {
          mainFallback.classList.add("hidden");
        }
        if (mainImage.complete && mainImage.naturalWidth > 0) {
          onHeroImgLoaded();
        } else {
          mainImage.addEventListener("load", onHeroImgLoaded, { once: true });
        }
      } else {
        if (heroArticle) {
          heroArticle.classList.remove("hero-img-loaded");
        }
        if (mainFallback) {
          mainFallback.classList.remove("hidden");
        }
      }

      if (mainBadge) {
        mainBadge.textContent = badge;
        if (badgeKey) {
          mainBadge.setAttribute("data-hero-main-badge-key", badgeKey);
        } else {
          mainBadge.removeAttribute("data-hero-main-badge-key");
        }
      }

      heroItems.forEach(function (button) {
        var isActive = button === item;
        button.setAttribute("aria-pressed", isActive ? "true" : "false");
        button.classList.toggle("is-hero-active", isActive);
      });

      /* 淡入过渡 */
      if (heroArticle) {
        heroArticle.style.opacity = "1";
      }
    };

    /* Activate a hero tab: use pre-fetched local data, no AJAX */
    var activateItem = function (item, skipAjax) {
      /* Always highlight the tab immediately */
      heroItems.forEach(function (button) {
        var isActive = button === item;
        button.setAttribute("aria-pressed", isActive ? "true" : "false");
        button.classList.toggle("is-hero-active", isActive);
      });

      var local = heroLocalData.get(item);

      /* On first load (skipAjax), use current cursor (server-rendered start index) */
      if (skipAjax && local && local.articles.length > 0) {
        var art = local.articles[local.cursor % local.articles.length];
        applyHeroData(
          item,
          art.title || "",
          art.image || "",
          art.permalink || item.getAttribute("data-hero-link") || "",
          art.type_label || "",
          art.type_key || "",
          art.post_id || 0,
        );
        return;
      }

      /* Auto-switch / tab click: advance cursor and use next local article */
      if (local && local.articles.length > 0) {
        local.cursor = (local.cursor + 1) % local.articles.length;
        var nextArt = local.articles[local.cursor];
        applyHeroData(
          item,
          nextArt.title || "",
          nextArt.image || "",
          nextArt.permalink || item.getAttribute("data-hero-link") || "",
          nextArt.type_label || "",
          nextArt.type_key || "",
          nextArt.post_id || 0,
        );
        return;
      }

      /* Fallback: no local data, use data attributes */
      applyHeroData(
        item,
        item.getAttribute("data-hero-title") || "",
        item.getAttribute("data-hero-image") || "",
        item.getAttribute("data-hero-link") || "",
        item.getAttribute("data-hero-badge") || "",
        item.getAttribute("data-hero-badge-key") || "",
        0,
      );
    };

    heroItems.forEach(function (item) {
      item.addEventListener("click", function (event) {
        event.preventDefault();
        currentIndex = heroItems.indexOf(item);
        activateItem(item);
        startAutoSwitch();
      });
    });

    var initial =
      heroItems.find(function (item) {
        return item.getAttribute("aria-pressed") === "true";
      }) || heroItems[0];

    var currentIndex = heroItems.indexOf(initial);
    if (currentIndex < 0) {
      currentIndex = 0;
    }

    var timerKey = "__panHeroSwitchTimer";
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
    var sections = Array.prototype.slice.call(
      document.querySelectorAll(".home-article"),
    );

    if (!sections.length) {
      return;
    }

    sections.forEach(function (section) {
      var content = section.querySelector("[data-article-scroll]");
      var scrollbarThumb = section.querySelector(
        ".home-article-scrollbar-thumb",
      );

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
          scrollbarThumb.style.opacity = "0";
          return;
        }

        scrollbarThumb.style.opacity = "1";

        var thumbHeight = Math.max(
          (clientHeight / scrollHeight) * clientHeight,
          40,
        );
        var maxOffset = clientHeight - thumbHeight;
        var offset =
          (content.scrollTop / (scrollHeight - clientHeight)) * maxOffset;

        scrollbarThumb.style.height = thumbHeight + "px";
        scrollbarThumb.style.transform = "translateY(" + offset + "px)";
      };

      content.addEventListener("scroll", updateScrollbar, { passive: true });
      window.addEventListener("resize", updateScrollbar);
      updateScrollbar();
    });
  }

  /* =========================
         Global Modules
         ========================= */

  /* back-to-top.js */
  function initBackToTop() {
    var button = document.querySelector("[data-back-to-top]");
    if (!button || button.getAttribute("data-back-to-top-ready") === "1") {
      return;
    }

    button.setAttribute("data-back-to-top-ready", "1");

    var updateVisibility = function () {
      var scrollTop =
        window.pageYOffset || document.documentElement.scrollTop || 0;
      var docHeight = Math.max(
        document.body.scrollHeight,
        document.documentElement.scrollHeight,
        document.body.offsetHeight,
        document.documentElement.offsetHeight,
      );
      var viewportHeight =
        window.innerHeight || document.documentElement.clientHeight || 0;
      var scrollable = Math.max(docHeight - viewportHeight, 1);
      var ratio = scrollTop / scrollable;

      button.classList.toggle("is-visible", ratio >= 0.3);
    };

    button.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    window.addEventListener("scroll", updateVisibility, { passive: true });
    window.addEventListener("resize", updateVisibility);
    updateVisibility();
  }

  /* =========================
         Single Modules
         ========================= */

  /* single-article-toc.js */
  function initSingleSideToc() {
    var toc = document.querySelector(".single-side-toc");
    var content = document.querySelector(".single-article-content");
    var banner = document.querySelector(".single-top-banner");
    var mainShell = document.querySelector(".single-page-square.main-shell");

    if (!toc || !content || toc.getAttribute("data-single-toc-ready") === "1") {
      return;
    }

    toc.setAttribute("data-single-toc-ready", "1");

    var syncTocPosition = function () {
      if (!mainShell || !toc) {
        return;
      }

      var viewportWidth =
        window.innerWidth || document.documentElement.clientWidth || 0;
      if (viewportWidth <= 1500) {
        toc.style.left = "";
        toc.style.right = "";
        toc.style.position = "";
        return;
      }

      var shellRect = mainShell.getBoundingClientRect();
      var left = Math.round(shellRect.right);

      toc.style.position = "fixed";
      toc.style.right = "auto";
      toc.style.left = left + "px";
    };

    syncTocPosition();
    window.addEventListener("resize", syncTocPosition, { passive: true });
    if (window.visualViewport) {
      window.visualViewport.addEventListener("resize", syncTocPosition, {
        passive: true,
      });
      window.visualViewport.addEventListener("scroll", syncTocPosition, {
        passive: true,
      });
    }

    /* ── Banner visibility → show / hide TOC ── */
    if (banner) {
      var bannerObserver = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            // Banner out of view → show TOC; banner visible → hide TOC
            toc.classList.toggle("is-visible", !entry.isIntersecting);
          });
        },
        { root: null, threshold: 0 },
      );
      bannerObserver.observe(banner);
    }

    var links = Array.prototype.slice.call(
      toc.querySelectorAll("[data-single-toc-link]"),
    );
    var headings = Array.prototype.slice.call(
      content.querySelectorAll("h2[id], h3[id]"),
    );

    if (!links.length || !headings.length) {
      return;
    }

    var activateLink = function (id) {
      links.forEach(function (link) {
        var isActive = link.getAttribute("href") === "#" + id;
        link.classList.toggle("is-active", isActive);
      });
    };

    links.forEach(function (link) {
      link.addEventListener("click", function (event) {
        var href = link.getAttribute("href") || "";
        if (!href.startsWith("#")) {
          return;
        }

        var targetId = href.slice(1);
        var target = document.getElementById(targetId);
        if (!target) {
          return;
        }

        event.preventDefault();
        var top = target.getBoundingClientRect().top + window.pageYOffset - 86;
        window.scrollTo({ top: top, behavior: "smooth" });
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
        rootMargin: "-90px 0px -62% 0px",
        threshold: 0.1,
      },
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
    if (typeof window.Plyr === "undefined") return;
    var root = scope || document;
    var targets = root.querySelectorAll(
      ".page-content video, .page-content audio, .single-article-content video, .single-article-content audio, .entry-content video, .entry-content audio",
    );
    if (!targets.length) return;
    targets.forEach(function (el) {
      if (el.__plyrInstance) return;
      el.__plyrInstance = new Plyr(el, {
        controls: [
          "play-large",
          "play",
          "progress",
          "current-time",
          "duration",
          "mute",
          "volume",
          "settings",
          "fullscreen",
        ],
        settings: ["speed"],
        speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
        i18n: {
          play: "播放",
          pause: "暂停",
          mute: "静音",
          unmute: "取消静音",
          settings: "设置",
          speed: "速度",
          normal: "正常",
          enterFullscreen: "全屏",
          exitFullscreen: "退出全屏",
        },
      });
    });
  }

  function updateCommentStats(data) {
    if (!data) {
      return;
    }

    var stats = document.querySelector(".comments-header__stats");
    if (!stats) {
      return;
    }

    // comments.php: 第1个 num = commentTotal（总评论数），第2个 num = toplevelCount（楼层数）
    var nums = stats.querySelectorAll(".comments-header__num");
    if (nums.length >= 2) {
      // 已有统计数字，直接更新
      if (typeof data.commentTotal !== "undefined") {
        nums[0].textContent = String(data.commentTotal);
      }
      if (typeof data.toplevelCount !== "undefined") {
        nums[1].textContent = String(data.toplevelCount);
      }
    } else if (typeof data.commentTotal !== "undefined") {
      // 从 0 条评论切换到有评论：重建统计 HTML
      var toplevel = typeof data.toplevelCount !== "undefined" ? data.toplevelCount : data.commentTotal;
      stats.innerHTML =
        '<span>感谢</span>' +
        '<span class="comments-header__num">' + String(data.commentTotal) + '</span>' +
        '<span>位民工兄弟的积极参与，</span>' +
        '<span class="comments-header__num">' + String(toplevel) + '</span>' +
        '<span>楼已竣工！</span>' +
        '<a href="#respond" class="comments-header__gai">»»盖否？</a>';
    }
  }

  function insertCommentHtml(data) {
    if (!data || !data.html) {
      return null;
    }

    var parent = Number(data.parent || 0);

    if (parent > 0) {
      var parentNode = document.getElementById("comment-" + parent);
      if (parentNode) {
        var childrenList = parentNode.querySelector("ol.children");
        if (!childrenList) {
          childrenList = document.createElement("ol");
          childrenList.className = "children";
          parentNode.appendChild(childrenList);
        }
        childrenList.insertAdjacentHTML("beforeend", data.html);
        return document.getElementById(
          "comment-" + String(data.commentId || ""),
        );
      }
    }

    var commentList = document.querySelector(".comment-list");
    if (!commentList) {
      var commentsInner = document.querySelector(".comments-inner");
      if (!commentsInner) {
        return;
      }

      commentList = document.createElement("ol");
      commentList.className = "comment-list";
      var commentFormWrap = commentsInner.querySelector("#respond");
      if (commentFormWrap) {
        commentsInner.insertBefore(commentList, commentFormWrap);
      } else {
        commentsInner.appendChild(commentList);
      }
    }

    commentList.insertAdjacentHTML("afterbegin", data.html);
    return document.getElementById("comment-" + String(data.commentId || ""));
  }

  function markNewCommentHint(commentNode) {
    if (!commentNode || commentNode.querySelector(".lared-comment-edit-btn")) {
      return;
    }

    var metaNode = commentNode.querySelector(
      ".comment-header, .comment-meta, .comment-metadata",
    );
    if (!metaNode) {
      return;
    }

    // 编辑按钮 + 倒计时
    var editBtn = document.createElement("button");
    editBtn.type = "button";
    editBtn.className = "lared-comment-edit-btn";
    editBtn.innerHTML =
      '<i class="fa-regular fa-pen-to-square"></i> 编辑 <span class="lared-edit-countdown">60s</span>';
    metaNode.appendChild(editBtn);

    var remaining = 60;
    var countdownSpan = editBtn.querySelector(".lared-edit-countdown");
    var timer = setInterval(function () {
      remaining--;
      if (remaining <= 0) {
        clearInterval(timer);
        editBtn.remove();
        return;
      }
      countdownSpan.textContent = remaining + "s";
    }, 1000);

    // 保存评论原文用于编辑（还原表情代码 + 处理 HTML 段落）
    var commentId = commentNode.id
      ? commentNode.id.replace("comment-", "")
      : "";
    var contentNode = commentNode.querySelector(".comment-content");
    var originalContent = contentNode
      ? extractEditableContent(contentNode)
      : "";

    editBtn.addEventListener("click", function () {
      startEditComment(commentId, originalContent, commentNode, editBtn, timer);
    });
  }

  /**
   * 从渲染后的评论 DOM 中提取可编辑的纯文本。
   * - <p>...</p> → 段落间用换行分隔
   * - <br> → 换行
   * - 其他 HTML 标签去除
   */
  function extractEditableContent(node) {
    var clone = node.cloneNode(true);

    var legacyEmojis = clone.querySelectorAll("img.lared-legacy-emoji[data-code]");
    legacyEmojis.forEach(function (img) {
      var code = img.getAttribute("data-code");
      img.replaceWith(code);
    });

    // 1. 在 <p> 结尾插入换行标记
    var paragraphs = clone.querySelectorAll("p");
    paragraphs.forEach(function (p) {
      p.insertAdjacentText("afterend", "\n");
    });

    // 2. <br> 转为换行
    var brs = clone.querySelectorAll("br");
    brs.forEach(function (br) {
      br.replaceWith("\n");
    });

    // 3. 获取文本并清理多余空行
    var text = clone.textContent || "";
    text = text.replace(/\n{3,}/g, "\n\n").trim();
    return text;
  }

  // ====== 评论编辑机制 ======
  var _editingCommentId = null;

  function startEditComment(commentId, content, commentNode, editBtn, timer) {
    var form = document.getElementById("commentform");
    if (!form) return;

    var textarea = form.querySelector("#comment");
    var submitBtn = form.querySelector(
      'input[type="submit"], button[type="submit"]',
    );
    if (!textarea || !submitBtn) return;

    // 如果已经在编辑模式，先清理旧的取消按钮
    var existingCancel = form.querySelector(".lared-comment-cancel-edit");
    if (existingCancel) existingCancel.remove();

    // 清理旧的编辑高亮
    var oldEditing = document.querySelector(".lared-comment-editing");
    if (oldEditing) oldEditing.classList.remove("lared-comment-editing");

    // 标记编辑模式
    _editingCommentId = commentId;
    form.setAttribute("data-editing", commentId);

    // 填充内容
    textarea.value = content;
    textarea.focus();

    // 修改按钮文字
    var originalSubmitText = submitBtn.value || submitBtn.textContent;
    if (submitBtn.tagName === "INPUT") {
      submitBtn.value = "更新评论";
    } else {
      submitBtn.textContent = "更新评论";
    }

    // 添加取消编辑按钮
    var cancelBtn = document.createElement("button");
    cancelBtn.type = "button";
    cancelBtn.className = "lared-comment-cancel-edit";
    cancelBtn.textContent = "取消编辑";
    submitBtn.parentNode.insertBefore(cancelBtn, submitBtn);

    // 高亮正在编辑的评论
    commentNode.classList.add("lared-comment-editing");

    // 滚动到表单
    var formRect = form.getBoundingClientRect();
    var targetY = window.pageYOffset + formRect.top - 100;
    window.scrollTo({ top: targetY, behavior: "smooth" });

    cancelBtn.addEventListener("click", function () {
      cancelEditComment(
        form,
        submitBtn,
        originalSubmitText,
        cancelBtn,
        commentNode,
      );
    });
  }

  function cancelEditComment(
    form,
    submitBtn,
    originalText,
    cancelBtn,
    commentNode,
  ) {
    _editingCommentId = null;
    form.removeAttribute("data-editing");

    var textarea = form.querySelector("#comment");
    if (textarea) textarea.value = "";

    if (submitBtn.tagName === "INPUT") {
      submitBtn.value = originalText;
    } else {
      submitBtn.textContent = originalText;
    }

    cancelBtn.remove();
    commentNode.classList.remove("lared-comment-editing");
  }

  function submitEditComment(
    commentId,
    newContent,
    form,
    submitBtn,
    originalSubmitText,
  ) {
    if (!window.LaredAjax || !window.LaredAjax.commentEditNonce) return;

    submitBtn.disabled = true;
    submitBtn.classList.add("is-loading");
    var savedText = submitBtn.value || submitBtn.textContent;
    if (submitBtn.tagName === "INPUT") submitBtn.value = "";
    else submitBtn.textContent = "";

    var formData = new FormData();
    formData.append("action", "lared_edit_comment");
    formData.append("nonce", window.LaredAjax.commentEditNonce);
    formData.append("comment_id", commentId);
    formData.append("comment", newContent);

    // 游客需要附带邮箱用于身份验证
    var emailField = form.querySelector("#email");
    if (emailField) {
      formData.append("author_email", emailField.value);
    }

    var startTime = Date.now();

    fetch(window.LaredAjax.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      body: formData,
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (result) {
        var elapsed = Date.now() - startTime;
        var delay = Math.max(0, 800 - elapsed);

        setTimeout(function () {
          resetSubmitButton(submitBtn, originalSubmitText);

          if (!result || !result.success) {
            showToast(
              (result && result.data && result.data.message) || "编辑失败",
              "error",
            );
            return;
          }

          showToast("评论已更新", "success");

          // 替换评论 HTML
          var commentNode = document.getElementById("comment-" + commentId);
          if (commentNode && result.data.html) {
            var temp = document.createElement("div");
            temp.innerHTML = result.data.html;
            var newArticle = temp.querySelector(".comment-body");
            var oldArticle = commentNode.querySelector(".comment-body");
            if (newArticle && oldArticle) {
              oldArticle.innerHTML = newArticle.innerHTML;
            }
            // 重新添加编辑按钮（如果还在60秒内）
            markNewCommentHint(commentNode);
            initCommentExpand();
          }

          // 清理编辑状态
          var cancelBtn = form.querySelector(".lared-comment-cancel-edit");
          if (cancelBtn) cancelBtn.remove();
          _editingCommentId = null;
          form.removeAttribute("data-editing");

          var textarea = form.querySelector("#comment");
          if (textarea) textarea.value = "";

          if (submitBtn.tagName === "INPUT")
            submitBtn.value = originalSubmitText;
          else submitBtn.textContent = originalSubmitText;
        }, delay);
      })
      .catch(function () {
        var elapsed = Date.now() - startTime;
        var delay = Math.max(0, 800 - elapsed);
        setTimeout(function () {
          resetSubmitButton(submitBtn, originalSubmitText);
          showToast("编辑失败，请重试", "error");
        }, delay);
      });
  }

  function scrollToNewComment(commentNode) {
    if (!commentNode || typeof commentNode.scrollIntoView !== "function") {
      return;
    }

    // 直接跳转到评论附近，不从页面顶部平滑滚动
    var rect = commentNode.getBoundingClientRect();
    var targetY = window.pageYOffset + rect.top - window.innerHeight / 3;
    window.scrollTo({ top: targetY, behavior: "instant" });

    commentNode.classList.add("lared-comment-newly-added");
    window.setTimeout(function () {
      commentNode.classList.remove("lared-comment-newly-added");
    }, 1500);
  }

  // ====== 回复链接安全拦截（防止导航刷新） ======
  // 使用事件委托，确保动态插入的评论回复链接也能正常工作

  function updateReplyTitleText(text) {
    var replyTitle = document.getElementById("reply-title");
    if (!replyTitle) return;
    var nodes = replyTitle.childNodes;
    for (var i = 0; i < nodes.length; i++) {
      if (nodes[i].nodeType === 3 && nodes[i].textContent.trim().length > 0) {
        nodes[i].textContent = text;
        return;
      }
    }
  }

  document.addEventListener(
    "click",
    function (e) {
      // —— 处理回复链接 ——
      var link = e.target.closest(".comment-reply-link");
      if (link) {
        e.preventDefault();
        e.stopImmediatePropagation();

        // 如果正在编辑评论，先取消编辑模式
        if (_editingCommentId) {
          var form = document.getElementById("commentform");
          if (form) {
            var submitBtn = form.querySelector(
              'input[type="submit"], button[type="submit"]',
            );
            var cancelBtn = form.querySelector(".lared-comment-cancel-edit");
            var editingNode = document.querySelector(".lared-comment-editing");

            _editingCommentId = null;
            form.removeAttribute("data-editing");
            var textarea = form.querySelector("#comment");
            if (textarea) textarea.value = "";
            if (submitBtn) {
              if (submitBtn.tagName === "INPUT") submitBtn.value = "提交评论";
              else submitBtn.textContent = "提交评论";
            }
            if (cancelBtn) cancelBtn.remove();
            if (editingNode)
              editingNode.classList.remove("lared-comment-editing");
          }
        }

        // 获取被回复评论的昵称
        var commentBody =
          link.closest(".comment-body") || link.closest('li[id^="comment-"]');
        var authorName = "";
        if (commentBody) {
          var authorEl = commentBody.querySelector(".comment-author-name");
          if (authorEl) authorName = authorEl.textContent.trim();
        }

        // 调用 WordPress 的 moveForm 移动评论表单
        var commId = link.getAttribute("data-belowelement");
        var parentId = link.getAttribute("data-commentid");
        var respondId = link.getAttribute("data-respondelement");
        var postId = link.getAttribute("data-postid");
        var replyTo = link.getAttribute("data-replyto") || "";

        if (commId && parentId && respondId && postId) {
          // 优先使用 WordPress 内置 addComment.moveForm
          var moved = false;
          if (
            window.addComment &&
            typeof window.addComment.moveForm === "function"
          ) {
            try {
              window.addComment.moveForm(
                commId,
                parentId,
                respondId,
                postId,
                replyTo,
              );
              moved = true;
            } catch (err) {
              // moveForm 内部可能因 cancelElement 未初始化而抛出异常
              // 尝试重新 init 后再试一次
              if (typeof window.addComment.init === "function") {
                try {
                  window.addComment.init();
                  window.addComment.moveForm(
                    commId,
                    parentId,
                    respondId,
                    postId,
                    replyTo,
                  );
                  moved = true;
                } catch (err2) {
                  /* fallback below */
                }
              }
            }
          }

          // Fallback：如果 addComment 不可用或 moveForm 失败，手动移动表单
          if (!moved) {
            var addBelowEl = document.getElementById(commId);
            var respondEl = document.getElementById(respondId);
            var parentField = document.getElementById("comment_parent");
            var postField = document.getElementById("comment_post_ID");
            var cancelEl = document.getElementById("cancel-comment-reply-link");

            if (addBelowEl && respondEl && parentField) {
              // 创建占位符（如果不存在）以便取消回复时还原位置
              var tempId = "wp-temp-form-div";
              if (!document.getElementById(tempId)) {
                var placeholder = document.createElement("div");
                placeholder.id = tempId;
                placeholder.style.display = "none";
                respondEl.parentNode.insertBefore(placeholder, respondEl);
              }

              parentField.value = parentId;
              if (postField && postId) postField.value = postId;
              addBelowEl.parentNode.insertBefore(
                respondEl,
                addBelowEl.nextSibling,
              );
              if (cancelEl) cancelEl.style.display = "";
            }
          }
        }

        // 更新标题为"回复 昵称"
        if (authorName) {
          updateReplyTitleText(" 回复 " + authorName + " ");
        }
        return;
      }

      // —— 处理取消回复链接 —— 恢复标题文字 + fallback 还原表单位置
      var cancelLink = e.target.closest("#cancel-comment-reply-link");
      if (cancelLink) {
        updateReplyTitleText(" 发表评论");

        // Fallback：如果 WordPress addComment 未接管，手动还原表单
        var tempPlaceholder = document.getElementById("wp-temp-form-div");
        var respondEl = document.getElementById("respond");
        if (tempPlaceholder && respondEl && tempPlaceholder.parentNode) {
          tempPlaceholder.parentNode.replaceChild(respondEl, tempPlaceholder);
          var parentField = document.getElementById("comment_parent");
          if (parentField) parentField.value = "0";
          cancelLink.style.display = "none";
          e.preventDefault();
        }
      }
    },
    true,
  ); // 捕获阶段，优先于其他 click 处理器

  // ====== 评论内容展开/收起（已移除） ======
  function initCommentExpand() {}

  // ====== Toast 提示 ======
  var _toastMessages = [
    "评论成功，恭喜发财！",
    "评论成功，万事如意！",
    "发表成功，好运连连！",
    "评论成功，大吉大利！",
    "发表成功，心想事成！",
    "评论成功，笑口常开！",
    "发表成功，前程似锦！",
    "评论成功，一帆风顺！",
    "发表成功，福星高照！",
    "评论成功，步步高升！",
  ];

  var _toastErrorMessages = ["提交失败，请稍后重试"];

  function showToast(message, type) {
    var existing = document.querySelector(".lared-toast");
    if (existing) existing.remove();

    var toast = document.createElement("div");
    toast.className = "lared-toast lared-toast--" + (type || "success");
    toast.textContent = message;
    document.body.appendChild(toast);

    // 强制重排后添加动画类
    toast.offsetHeight;
    toast.classList.add("is-visible");

    setTimeout(function () {
      toast.classList.remove("is-visible");
      toast.classList.add("is-hiding");
      setTimeout(function () {
        toast.remove();
      }, 400);
    }, 2500);
  }

  function getRandomToast(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
  }

  // ====== 回头访客编辑信息切换 ======
  function initEditInfoToggle() {
    var toggle = document.querySelector(".lared-edit-info-toggle");
    if (!toggle || toggle.getAttribute("data-edit-info-ready") === "1") return;
    toggle.setAttribute("data-edit-info-ready", "1");

    toggle.addEventListener("click", function (e) {
      e.preventDefault();
      var form = document.querySelector(".comment-form.lared-returning-guest");
      if (!form) return;
      form.classList.toggle("lared-show-fields");
    });
  }

  // ====== 邮箱输入自动识别头像 ======
  function md5(s) {
    function rl(v, b) {
      return (v << b) | (v >>> (32 - b));
    }
    function au(x, y) {
      var x8 = x & 0x80000000,
        y8 = y & 0x80000000,
        x4 = x & 0x40000000,
        y4 = y & 0x40000000,
        r = (x & 0x3fffffff) + (y & 0x3fffffff);
      if (x4 & y4) return r ^ 0x80000000 ^ x8 ^ y8;
      if (x4 | y4) {
        if (r & 0x40000000) return r ^ 0xc0000000 ^ x8 ^ y8;
        else return r ^ 0x40000000 ^ x8 ^ y8;
      } else return r ^ x8 ^ y8;
    }
    function F(x, y, z) {
      return (x & y) | (~x & z);
    }
    function G(x, y, z) {
      return (x & z) | (y & ~z);
    }
    function H(x, y, z) {
      return x ^ y ^ z;
    }
    function I(x, y, z) {
      return y ^ (x | ~z);
    }
    function t(fn, a, b, c, d, x, s, ac) {
      return au(rl(au(a, au(au(fn, x), ac)), s), b);
    }
    function FF(a, b, c, d, x, s, ac) {
      return t(F(b, c, d), a, b, c, d, x, s, ac);
    }
    function GG(a, b, c, d, x, s, ac) {
      return t(G(b, c, d), a, b, c, d, x, s, ac);
    }
    function HH(a, b, c, d, x, s, ac) {
      return t(H(b, c, d), a, b, c, d, x, s, ac);
    }
    function II(a, b, c, d, x, s, ac) {
      return t(I(b, c, d), a, b, c, d, x, s, ac);
    }
    function cw(s) {
      var l = s.length,
        n = ((l + 8 - ((l + 8) % 64)) / 64 + 1) * 16,
        w = Array(n - 1),
        p = 0,
        c = 0;
      while (c < l) {
        var wc = (c - (c % 4)) / 4;
        p = (c % 4) * 8;
        w[wc] = w[wc] | (s.charCodeAt(c) << p);
        c++;
      }
      w[(c - (c % 4)) / 4] = w[(c - (c % 4)) / 4] | (0x80 << ((c % 4) * 8));
      w[n - 2] = l << 3;
      w[n - 1] = l >>> 29;
      return w;
    }
    function wh(v) {
      var r = "",
        t,
        b,
        i;
      for (i = 0; i <= 3; i++) {
        b = (v >>> (i * 8)) & 255;
        t = "0" + b.toString(16);
        r += t.substr(t.length - 2, 2);
      }
      return r;
    }
    var x = cw(s),
      a = 0x67452301,
      b = 0xefcdab89,
      c = 0x98badcfe,
      d = 0x10325476;
    for (var k = 0; k < x.length; k += 16) {
      var A = a,
        B = b,
        C = c,
        D = d;
      a = FF(a, b, c, d, x[k], 7, 0xd76aa478);
      d = FF(d, a, b, c, x[k + 1], 12, 0xe8c7b756);
      c = FF(c, d, a, b, x[k + 2], 17, 0x242070db);
      b = FF(b, c, d, a, x[k + 3], 22, 0xc1bdceee);
      a = FF(a, b, c, d, x[k + 4], 7, 0xf57c0faf);
      d = FF(d, a, b, c, x[k + 5], 12, 0x4787c62a);
      c = FF(c, d, a, b, x[k + 6], 17, 0xa8304613);
      b = FF(b, c, d, a, x[k + 7], 22, 0xfd469501);
      a = FF(a, b, c, d, x[k + 8], 7, 0x698098d8);
      d = FF(d, a, b, c, x[k + 9], 12, 0x8b44f7af);
      c = FF(c, d, a, b, x[k + 10], 17, 0xffff5bb1);
      b = FF(b, c, d, a, x[k + 11], 22, 0x895cd7be);
      a = FF(a, b, c, d, x[k + 12], 7, 0x6b901122);
      d = FF(d, a, b, c, x[k + 13], 12, 0xfd987193);
      c = FF(c, d, a, b, x[k + 14], 17, 0xa679438e);
      b = FF(b, c, d, a, x[k + 15], 22, 0x49b40821);
      a = GG(a, b, c, d, x[k + 1], 5, 0xf61e2562);
      d = GG(d, a, b, c, x[k + 6], 9, 0xc040b340);
      c = GG(c, d, a, b, x[k + 11], 14, 0x265e5a51);
      b = GG(b, c, d, a, x[k], 20, 0xe9b6c7aa);
      a = GG(a, b, c, d, x[k + 5], 5, 0xd62f105d);
      d = GG(d, a, b, c, x[k + 10], 9, 0x2441453);
      c = GG(c, d, a, b, x[k + 15], 14, 0xd8a1e681);
      b = GG(b, c, d, a, x[k + 4], 20, 0xe7d3fbc8);
      a = GG(a, b, c, d, x[k + 9], 5, 0x21e1cde6);
      d = GG(d, a, b, c, x[k + 14], 9, 0xc33707d6);
      c = GG(c, d, a, b, x[k + 3], 14, 0xf4d50d87);
      b = GG(b, c, d, a, x[k + 8], 20, 0x455a14ed);
      a = GG(a, b, c, d, x[k + 13], 5, 0xa9e3e905);
      d = GG(d, a, b, c, x[k + 2], 9, 0xfcefa3f8);
      c = GG(c, d, a, b, x[k + 7], 14, 0x676f02d9);
      b = GG(b, c, d, a, x[k + 12], 20, 0x8d2a4c8a);
      a = HH(a, b, c, d, x[k + 5], 4, 0xfffa3942);
      d = HH(d, a, b, c, x[k + 8], 11, 0x8771f681);
      c = HH(c, d, a, b, x[k + 11], 16, 0x6d9d6122);
      b = HH(b, c, d, a, x[k + 14], 23, 0xfde5380c);
      a = HH(a, b, c, d, x[k + 1], 4, 0xa4beea44);
      d = HH(d, a, b, c, x[k + 4], 11, 0x4bdecfa9);
      c = HH(c, d, a, b, x[k + 7], 16, 0xf6bb4b60);
      b = HH(b, c, d, a, x[k + 10], 23, 0xbebfbc70);
      a = HH(a, b, c, d, x[k + 13], 4, 0x289b7ec6);
      d = HH(d, a, b, c, x[k], 11, 0xeaa127fa);
      c = HH(c, d, a, b, x[k + 3], 16, 0xd4ef3085);
      b = HH(b, c, d, a, x[k + 6], 23, 0x4881d05);
      a = HH(a, b, c, d, x[k + 9], 4, 0xd9d4d039);
      d = HH(d, a, b, c, x[k + 12], 11, 0xe6db99e5);
      c = HH(c, d, a, b, x[k + 15], 16, 0x1fa27cf8);
      b = HH(b, c, d, a, x[k + 2], 23, 0xc4ac5665);
      a = II(a, b, c, d, x[k], 6, 0xf4292244);
      d = II(d, a, b, c, x[k + 7], 10, 0x432aff97);
      c = II(c, d, a, b, x[k + 14], 15, 0xab9423a7);
      b = II(b, c, d, a, x[k + 5], 21, 0xfc93a039);
      a = II(a, b, c, d, x[k + 12], 6, 0x655b59c3);
      d = II(d, a, b, c, x[k + 3], 10, 0x8f0ccc92);
      c = II(c, d, a, b, x[k + 10], 15, 0xffeff47d);
      b = II(b, c, d, a, x[k + 1], 21, 0x85845dd1);
      a = II(a, b, c, d, x[k + 8], 6, 0x6fa87e4f);
      d = II(d, a, b, c, x[k + 15], 10, 0xfe2ce6e0);
      c = II(c, d, a, b, x[k + 6], 15, 0xa3014314);
      b = II(b, c, d, a, x[k + 13], 21, 0x4e0811a1);
      a = II(a, b, c, d, x[k + 4], 6, 0xf7537e82);
      d = II(d, a, b, c, x[k + 11], 10, 0xbd3af235);
      c = II(c, d, a, b, x[k + 2], 15, 0x2ad7d2bb);
      b = II(b, c, d, a, x[k + 9], 21, 0xeb86d391);
      a = au(a, A);
      b = au(b, B);
      c = au(c, C);
      d = au(d, D);
    }
    return (wh(a) + wh(b) + wh(c) + wh(d)).toLowerCase();
  }

  function initEmailAvatar() {
    var emailField = document.getElementById("email");
    var avatarWrap = document.getElementById("lared-title-avatar-wrap");
    if (!emailField || !avatarWrap) return;
    if (emailField.getAttribute("data-avatar-ready") === "1") return;
    emailField.setAttribute("data-avatar-ready", "1");

    var _defaultIcon =
      '<i class="fa-regular fa-comment-dots" style="color:var(--color-accent,#f53004);font-size:16px;"></i>';
    var _lastHash = "";
    // 如果页面加载时已有头像（回头访客 cookie），标记为已有头像状态
    var _hasInitialAvatar = !!avatarWrap.querySelector("img");

    function updateAvatar() {
      var email = (emailField.value || "").trim().toLowerCase();
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        // 无效邮箱或已清空：恢复默认图标
        if (_lastHash !== "" || _hasInitialAvatar) {
          _lastHash = "";
          _hasInitialAvatar = false;
          avatarWrap.innerHTML = _defaultIcon;
        }
        return;
      }
      var hash = md5(email);
      if (hash === _lastHash) return;
      _lastHash = hash;
      _hasInitialAvatar = false;
      var baseUrl =
        (window.LaredAjax && window.LaredAjax.avatarBaseUrl) ||
        "https://gravatar.bluecdn.com/avatar/";
      avatarWrap.innerHTML =
        '<img class="lared-title-avatar" src="' +
        baseUrl +
        hash +
        '?s=96&d=mp" alt="">';
    }

    emailField.addEventListener("input", updateAvatar);
    emailField.addEventListener("change", updateAvatar);
    // 初始检查（可能有预填值）
    if (emailField.value) updateAvatar();
  }

  /* ========== 评论区懒加载 ========== */
  function initLazyComments() {
    var placeholder = document.querySelector("[data-lazy-comments]");
    if (!placeholder) return;

    var postId = placeholder.getAttribute("data-post-id");
    if (!postId) return;

    var loaded = false;
    var headerLoading = document.querySelector("[data-header-loading]");

    var observer = new IntersectionObserver(
      function (entries) {
        if (entries[0].isIntersecting && !loaded) {
          loaded = true;
          observer.disconnect();

          // 显示右上角 loading
          if (headerLoading) headerLoading.classList.add("is-active");

          fetch(
            LaredAjax.ajaxUrl +
              "?action=lared_load_comments&post_id=" +
              encodeURIComponent(postId),
            { credentials: "same-origin" },
          )
            .then(function (r) {
              return r.json();
            })
            .then(function (data) {
              if (headerLoading) headerLoading.classList.remove("is-active");
              if (data.success && data.data.html) {
                var temp = document.createElement("div");
                temp.innerHTML = data.data.html;
                var realComments = temp.firstElementChild;
                if (realComments) {
                  placeholder.parentNode.replaceChild(realComments, placeholder);
                }
                // 重新初始化评论相关 JS
                _boundCommentForm = null;
                initAjaxCommentSubmit();
                initCommentExpand();
                initEditInfoToggle();
                initEmailAvatar();
                markCommentLinksNoPjax();
                if (window.xmojipick && typeof window.xmojipick.refresh === "function") {
                  window.xmojipick.refresh();
                }
                // WordPress 内置回复链接初始化
                if (
                  window.addComment &&
                  typeof window.addComment.init === "function"
                ) {
                  window.addComment.init();
                }
              }
            })
            .catch(function () {
              if (headerLoading) headerLoading.classList.remove("is-active");
              var spinner = placeholder.querySelector(".comments-lazy-spinner");
              if (spinner) {
                spinner.innerHTML =
                  '<p style="color:#999;text-align:center;">评论加载失败，请刷新页面。</p>';
              }
            });
        }
      },
      { rootMargin: "800px" },
    );

    observer.observe(placeholder);
  }

  // 防止 PJAX 拦截评论列表内的链接（作者链接、评论内容链接等），
  // 避免导航后评论区被重置为懒加载占位符而"消失"
  function markCommentLinksNoPjax() {
    var commentList = document.querySelector(".comment-list");
    if (!commentList) return;
    commentList.querySelectorAll("a[href]").forEach(function (a) {
      if (a.classList.contains("comment-reply-link") || a.id === "cancel-comment-reply-link") return;
      if (!a.hasAttribute("data-no-pjax")) a.setAttribute("data-no-pjax", "");
      if (!a.getAttribute("target")) a.setAttribute("target", "_blank");
    });
  }

  // 记录已绑定的评论表单 DOM 节点，用于判断是否需要重新绑定
  var _boundCommentForm = null;

  function initAjaxCommentSubmit() {
    var form = document.getElementById("commentform");
    if (!form) {
      return;
    }

    // 同一 DOM 节点已绑定则跳过（innerHTML 替换后是新节点，会重新绑定）
    if (form === _boundCommentForm) {
      return;
    }

    if (
      !window.LaredAjax ||
      !window.LaredAjax.ajaxUrl ||
      !window.LaredAjax.commentSubmitNonce
    ) {
      return;
    }

    _boundCommentForm = form;
    form.setAttribute("data-ajax-ready", "1");

    var _isSubmitting = false;

    form.addEventListener("submit", function (event) {
      event.preventDefault();

      if (_isSubmitting) return;

      // 阻止事件冒泡，防止 PJAX 拦截表单提交导致页面跳转
      event.stopPropagation();

      // 编辑模式：拦截提交，走编辑接口
      var editingId = form.getAttribute("data-editing");
      if (editingId && _editingCommentId) {
        _isSubmitting = true;
        var submitButton = form.querySelector(
          'input[type="submit"], button[type="submit"]',
        );
        var textarea = form.querySelector("#comment");
        var originalText = submitButton.value || submitButton.textContent;
        submitEditComment(
          editingId,
          textarea.value,
          form,
          submitButton,
          originalText,
        );
        _isSubmitting = false;
        return;
      }

      var submitButton = form.querySelector(
        'input[type="submit"], button[type="submit"]',
      );
      if (!submitButton || submitButton.disabled) return;

      _isSubmitting = true;

      // 按钮进入 loading 状态
      submitButton.disabled = true;
      var originalText = submitButton.value || submitButton.textContent;
      submitButton.classList.add("is-loading");

      var formData = new FormData(form);
      formData.append("action", "lared_submit_comment");
      formData.append("nonce", window.LaredAjax.commentSubmitNonce);

      fetch(window.LaredAjax.ajaxUrl, {
        method: "POST",
        credentials: "same-origin",
        body: formData,
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (result) {
            // 恢复按钮
            _isSubmitting = false;
            resetSubmitButton(submitButton, originalText);

            if (!result || !result.success) {
              var errorMessage =
                result && result.data && result.data.message
                  ? result.data.message
                  : "提交失败，请稍后重试";
              showToast(
                errorMessage || getRandomToast(_toastErrorMessages),
                "error",
              );
              return;
            }

            // 成功 toast
            if (result.data.approved) {
              showToast(getRandomToast(_toastMessages), "success");
            } else {
              showToast("评论已提交，审核通过后显示", "success");
            }

            if (result.data.approved) {
              var newCommentNode = insertCommentHtml(result.data);
              updateCommentStats(result.data);
              markNewCommentHint(newCommentNode);
              initCommentExpand();
              scrollToNewComment(newCommentNode);
            }

            var commentField = form.querySelector("#comment");
            if (commentField) {
              commentField.value = "";
            }

            // 提交成功后：隐藏信息字段，切换为回头访客状态
            var authorField = form.querySelector("#author");
            if (authorField && authorField.value) {
              form.classList.add("lared-returning-guest");
              form.classList.remove("lared-show-fields");

              var commenterName =
                (result.data && result.data.commenterName) || authorField.value;
              var titleMeta = document.querySelector(".lared-title-meta");
              if (titleMeta) {
                titleMeta.className =
                  "lared-title-meta lared-title-meta--returning";
                titleMeta.innerHTML =
                  "欢迎回来，<strong>" +
                  commenterName +
                  "</strong>" +
                  ' <a href="#" class="lared-edit-info-toggle" onclick="return false;"><i class="fa-regular fa-pen-to-square" style="font-size:11px"></i> 编辑信息</a>';
                initEditInfoToggle();
              } else {
                var replyTitle = document.querySelector("#reply-title");
                if (replyTitle) {
                  var newMeta = document.createElement("span");
                  newMeta.className =
                    "lared-title-meta lared-title-meta--returning";
                  newMeta.innerHTML =
                    "欢迎回来，<strong>" +
                    commenterName +
                    "</strong>" +
                    ' <a href="#" class="lared-edit-info-toggle" onclick="return false;"><i class="fa-regular fa-pen-to-square" style="font-size:11px"></i> 编辑信息</a>';
                  replyTitle.appendChild(newMeta);
                  initEditInfoToggle();
                }
              }
            }

            // 回复成功后，将表单移回评论区底部（重置回复状态）
            var cancelReplyBtn = document.getElementById(
              "cancel-comment-reply-link",
            );
            var savedScrollY =
              window.pageYOffset || document.documentElement.scrollTop;

            if (cancelReplyBtn && cancelReplyBtn.style.display !== "none") {
              cancelReplyBtn.click();
            }

            if (
              window.addComment &&
              typeof window.addComment.init === "function"
            ) {
              window.addComment.init();
            }

            window.scrollTo({ top: savedScrollY, behavior: "instant" });
        })
        .catch(function () {
            _isSubmitting = false;
            resetSubmitButton(submitButton, originalText);
            showToast(getRandomToast(_toastErrorMessages), "error");
        });
    });
  }

  function resetSubmitButton(btn, text) {
    btn.classList.remove("is-loading");
    btn.disabled = false;
    if (btn.tagName === "INPUT") {
      btn.value = text;
    } else {
      btn.textContent = text;
    }
  }

  /* prism-enhance.js */
  function copyText(text) {
    if (
      navigator.clipboard &&
      typeof navigator.clipboard.writeText === "function"
    ) {
      return navigator.clipboard.writeText(text);
    }

    return new Promise(function (resolve, reject) {
      try {
        var textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.setAttribute("readonly", "readonly");
        textarea.style.position = "fixed";
        textarea.style.top = "-9999px";
        textarea.style.left = "-9999px";
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        var ok = document.execCommand("copy");
        document.body.removeChild(textarea);

        if (ok) {
          resolve();
          return;
        }

        reject(new Error("copy-failed"));
      } catch (error) {
        reject(error);
      }
    });
  }

  function ensureCopyButtons(root) {
    var scope = root || document;
    var nodes = Array.prototype.slice.call(
      scope.querySelectorAll("pre > code"),
    );

    var MAX_VISIBLE_LINES = 20;

    var normalizeCode = function (source) {
      return String(source || "")
        .replace(/\r\n/g, "\n")
        .replace(/^\n+/, "")
        .replace(/\n+$/g, "");
    };

    // 转义 HTML 特殊字符，防止代码内容被浏览器解析
    var escapeHtml = function (str) {
      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    };

    var getLineCount = function (source) {
      var normalized = normalizeCode(source);
      if (!normalized) {
        return 1;
      }

      return normalized.split("\n").length;
    };

    var isSingleLineCode = function (source) {
      var normalized = normalizeCode(source);

      return normalized.indexOf("\n") === -1;
    };

    nodes.forEach(function (codeEl) {
      var preEl = codeEl.parentElement;
      if (!preEl || preEl.getAttribute("data-lared-copy-ready") === "1") {
        return;
      }

      preEl.setAttribute("data-lared-copy-ready", "1");
      preEl.classList.add("lared-prism-pre");

      // 没有 language-* class 的代码块，添加 language-plaintext
      // 以便 Prism 处理并触发 line-numbers 插件的 complete 钩子
      var hasLang = false;
      var codeClasses = codeEl.className.split(/\s+/);
      for (var ci = 0; ci < codeClasses.length; ci++) {
        if (codeClasses[ci].indexOf("language-") === 0) {
          hasLang = true;
          break;
        }
      }
      if (!hasLang) {
        codeEl.classList.add("language-plaintext");
        preEl.classList.add("language-plaintext");
      }

      // 清除旧的行号（避免 PJAX 重复生成）
      // Prism 插件把 .line-numbers-rows 插入到 code 内部
      var oldRows = codeEl.querySelector(".line-numbers-rows");
      if (oldRows) {
        oldRows.remove();
      }
      // 兼容旧版本：也检查 pre 内的行号容器
      var oldPreRows = preEl.querySelector(".line-numbers-rows");
      if (oldPreRows) {
        oldPreRows.remove();
      }

      // 去除代码首尾空行（编辑器/数据库存储常带多余换行，兼容 CRLF）
      var rawText = codeEl.textContent || "";
      var trimmedText = rawText.replace(/^[\r\n]+/, "").replace(/[\r\n]+$/, "");
      if (trimmedText !== rawText) {
        // 如果有 Prism token 子元素，只修剪 innerHTML 的首尾换行
        if (
          codeEl.children.length > 0 &&
          codeEl.querySelector('span[class*="token"]')
        ) {
          codeEl.innerHTML = codeEl.innerHTML
            .replace(/^[\s\r\n]+/, "")
            .replace(/[\s\r\n]+$/, "");
        } else {
          codeEl.textContent = trimmedText;
        }
      }

      // 转义 HTML 特殊字符，防止代码内容被浏览器解析执行
      // 仅对非 Prism 高亮、非 language-markup 的代码块执行
      // language-markup 代码块由 PHP esc_html() 已转义，且 Prism 高亮后会产生 <span> 子元素
      var isMarkup =
        codeEl.classList.contains("language-markup") ||
        codeEl.classList.contains("language-html") ||
        codeEl.classList.contains("language-xml");
      if (
        !isMarkup &&
        codeEl.children.length > 0 &&
        !codeEl.querySelector('span[class*="token"]')
      ) {
        var textContent = codeEl.textContent;
        codeEl.innerHTML = escapeHtml(textContent);
      }

      if (isSingleLineCode(codeEl.textContent || "")) {
        preEl.classList.add("lared-prism-pre--single-line");
      } else {
        // 添加 line-numbers class，Prism line-numbers 插件会在 complete 钩子中自动生成行号
        preEl.classList.add("line-numbers");

        var lineCount = getLineCount(codeEl.textContent || "");
        if (lineCount > MAX_VISIBLE_LINES) {
          preEl.classList.add(
            "lared-prism-pre--collapsible",
            "lared-prism-pre--collapsed",
          );

          var foldBtn = document.createElement("button");
          foldBtn.type = "button";
          foldBtn.className = "lared-code-fold-btn";
          foldBtn.textContent = "展开";
          foldBtn.setAttribute("aria-label", "展开代码");
          foldBtn.setAttribute("aria-expanded", "false");

          foldBtn.addEventListener("click", function () {
            var expanded = preEl.classList.contains(
              "lared-prism-pre--expanded",
            );

            if (expanded) {
              preEl.classList.remove("lared-prism-pre--expanded");
              preEl.classList.add("lared-prism-pre--collapsed");
              foldBtn.textContent = "展开";
              foldBtn.setAttribute("aria-label", "展开代码");
              foldBtn.setAttribute("aria-expanded", "false");
              return;
            }

            preEl.classList.remove("lared-prism-pre--collapsed");
            preEl.classList.add("lared-prism-pre--expanded");
            foldBtn.textContent = "收起";
            foldBtn.setAttribute("aria-label", "收起代码");
            foldBtn.setAttribute("aria-expanded", "true");
          });

          preEl.appendChild(foldBtn);
        }
      }

      var button = document.createElement("button");
      button.type = "button";
      button.className = "lared-code-copy-btn";
      button.setAttribute("aria-label", "复制代码");
      button.innerHTML =
        '<i class="fa-regular fa-copy" aria-hidden="true"></i>';

      button.addEventListener("click", function () {
        var source = codeEl.textContent || "";
        if (!source) {
          return;
        }

        copyText(source)
          .then(function () {
            button.innerHTML =
              '<i class="fa-solid fa-check" aria-hidden="true"></i>';
            button.classList.add("is-copied");
            window.setTimeout(function () {
              button.innerHTML =
                '<i class="fa-regular fa-copy" aria-hidden="true"></i>';
              button.classList.remove("is-copied");
            }, 3000);
          })
          .catch(function () {
            button.innerHTML =
              '<i class="fa-regular fa-copy" aria-hidden="true"></i>';
            window.setTimeout(function () {
              button.innerHTML =
                '<i class="fa-regular fa-copy" aria-hidden="true"></i>';
            }, 3000);
          });
      });

      preEl.appendChild(button);

      /* ── 运行按钮：仅对 language-html 或有 data-cr-runnable 的代码块显示 ── */
      var isHtml =
        codeEl.classList.contains("language-html") ||
        preEl.hasAttribute("data-cr-runnable");
      if (isHtml && !preEl.querySelector(".lared-code-run-btn")) {
        var runBtn = document.createElement("button");
        runBtn.type = "button";
        runBtn.className = "lared-code-run-btn";
        runBtn.setAttribute("aria-label", "运行代码");
        runBtn.innerHTML =
          '<i class="fa-solid fa-play" aria-hidden="true"></i>';
        runBtn.addEventListener("click", function () {
          var code = codeEl.textContent || "";
          var title = preEl.getAttribute("data-cr-title") || "代码预览";
          var height = parseInt(preEl.getAttribute("data-cr-height")) || 400;
          laredCodeRunnerOpen(code, title, height);
        });
        preEl.appendChild(runBtn);
      }
    });
  }

  function highlight(root) {
    if (typeof Prism === "undefined") {
      return;
    }

    // 逐个高亮已标记的代码块，避免 highlightAllUnder 遗漏新增的 language-plaintext
    var scope = root || document;
    var codeEls = scope.querySelectorAll(
      'code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code',
    );
    for (var i = 0; i < codeEls.length; i++) {
      try {
        Prism.highlightElement(codeEls[i]);
      } catch (err) {
        if (typeof console !== "undefined" && console.error) {
          console.error("[Prism] highlightElement failed:", err);
        }
      }
    }
  }

  function initPrismEnhance(root) {
    var scope = root || document;
    ensureCopyButtons(scope);
    highlight(scope);
  }

  /* view-image.js - lightweight image lightbox */
  function initViewImage() {
    if (typeof window.ViewImage === "undefined") {
      return;
    }

    // Initialize for article content images
    var contents = Array.prototype.slice.call(
      document.querySelectorAll(
        ".single-article-content, .home-article-body, .memos-card-body, .album-grid",
      ),
    );
    contents.forEach(function (content) {
      if (!content.hasAttribute("view-image")) {
        content.setAttribute("view-image", "");
      }
    });

    // Initialize ViewImage
    window.ViewImage &&
      window.ViewImage.init(
        ".single-article-content img, .home-article-body img, .memos-card-body img, .album-grid img",
      );
  }

  /* article-image-loading.js - 文章图片占位 loading 效果 */
  function initArticleImageLoading() {
    // 只处理文章内容的图片
    var articleContents = document.querySelectorAll(
      ".single-article-content, .home-article-body",
    );

    articleContents.forEach(function (content) {
      var images = Array.prototype.slice.call(
        content.querySelectorAll("img:not(.emoji):not(.avatar)"),
      );

      images.forEach(function (img) {
        // 跳过网格布局内的图片（由 initLaredGrid 处理）
        if (img.closest(".lared-grid-2, .lared-grid-3, .lared-grid-4")) {
          return;
        }

        // 跳过已经包装过的图片 (PHP 已经处理过)
        if (img.classList.contains("img-loading-target")) {
          // 确保加载状态正确
          ensureImageLoaded(img);
          return;
        }

        // 跳过代码块内的图片
        if (img.closest("pre, code")) {
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
    var grids = document.querySelectorAll(
      ".lared-grid-2, .lared-grid-3, .lared-grid-4",
    );

    grids.forEach(function (grid) {
      // 清理 wpautop 产生的 <br> 标签（会成为多余 grid item）
      var brs = Array.prototype.slice.call(
        grid.querySelectorAll(":scope > br"),
      );
      brs.forEach(function (br) {
        br.remove();
      });

      // 清理 wpautop 产生的空 <p> 标签
      var ps = Array.prototype.slice.call(grid.querySelectorAll(":scope > p"));
      ps.forEach(function (p) {
        // 把 p 内的子节点（img 等）移到 grid 直接下级
        while (p.firstChild) {
          grid.insertBefore(p.firstChild, p);
        }
        p.remove();
      });

      // 如果 PHP 的 lared_wrap_images_with_loader 把 img 包在了 figure.img-loading-wrapper 里，
      // 需要把 img 解放出来，直接放入 grid 容器
      var wrappers = Array.prototype.slice.call(
        grid.querySelectorAll(".img-loading-wrapper"),
      );
      wrappers.forEach(function (wrapper) {
        var img = wrapper.querySelector("img");
        if (img) {
          img.classList.remove("img-loading-target");
          img.style.opacity = "";
          img.style.position = "";
          grid.insertBefore(img, wrapper);
        }
        wrapper.remove();
      });

      // 确保所有图片可见
      var imgs = Array.prototype.slice.call(grid.querySelectorAll("img"));
      imgs.forEach(function (img) {
        img.classList.remove("img-loading-target");
        img.style.opacity = "1";

        if (img.parentElement === grid) {
          var item = document.createElement("div");
          item.className = "lared-grid-item";
          grid.insertBefore(item, img);
          item.appendChild(img);
        }
      });
    });
  }

  function ensureImageLoaded(img) {
    var wrapper = img.closest(".img-loading-wrapper");
    if (!wrapper) return;

    function markLoaded() {
      wrapper.classList.add("is-loaded");
    }

    // lazysizes 已加载完成
    if (img.classList.contains("lazyloaded")) {
      markLoaded();
    } else if (img.complete && img.naturalWidth > 0) {
      markLoaded();
    } else {
      img.addEventListener("lazyloaded", markLoaded, { once: true });
      img.addEventListener("load", markLoaded, { once: true });
      img.addEventListener(
        "error",
        function () {
          wrapper.classList.add("is-error");
        },
        { once: true },
      );
    }
  }

  function wrapImageWithLoader(img) {
    // 创建占位容器
    var wrapper = document.createElement("figure");
    wrapper.className = "img-loading-wrapper";

    var media = document.createElement("div");
    media.className = "img-loading-media";

    // 从图片的 width/height 属性获取尺寸设置比例
    var width = img.getAttribute("width") || img.naturalWidth || 0;
    var height = img.getAttribute("height") || img.naturalHeight || 0;
    if (width && height && parseInt(height) > 0) {
      media.style.aspectRatio = width + "/" + height;
    }

    // 创建 loading 圆圈
    var spinner = document.createElement("div");
    spinner.className = "img-loading-spinner";
    spinner.innerHTML = '<div class="spinner-circle"></div>';

    // 设置图片类名
    img.classList.add("img-loading-target");

    // 如果图片还没有 lazyload 类且有 src，转换为 lazysizes 格式
    if (
      !img.classList.contains("lazyload") &&
      !img.classList.contains("lazyloaded") &&
      img.getAttribute("src")
    ) {
      var src = img.getAttribute("src");
      img.setAttribute("data-src", src);
      img.removeAttribute("src");
      img.classList.add("lazyload");
    }

    // 包装图片
    img.parentNode.insertBefore(wrapper, img);
    wrapper.appendChild(media);
    media.appendChild(spinner);
    media.appendChild(img);

    // 监听 lazysizes 完成事件
    function onImageLoad() {
      wrapper.classList.add("is-loaded");
    }

    // 检查图片是否已加载完成（包括缓存或已被 lazysizes 处理）
    if (
      img.classList.contains("lazyloaded") ||
      (img.complete && img.naturalWidth > 0)
    ) {
      onImageLoad();
    } else {
      img.addEventListener("lazyloaded", onImageLoad, { once: true });
      img.addEventListener("load", onImageLoad, { once: true });

      img.addEventListener(
        "error",
        function () {
          wrapper.classList.add("is-error");
          spinner.innerHTML =
            '<div class="img-loading-error-icon"><i class="fa-solid fa-circle-exclamation"></i></div>';
        },
        { once: true },
      );
    }
  }

  /* image-load-animation.js — lazysizes 全局事件驱动 */
  function initImageLoadAnimation() {
    var htmlEl = document.documentElement;
    var animationType = htmlEl.getAttribute("data-img-animation") || "none";

    if (animationType === "none") {
      return;
    }

    // 为所有图片设置动画属性
    var images = Array.prototype.slice.call(document.querySelectorAll("img"));

    images.forEach(function (img) {
      // 排除特定图片
      if (
        img.closest("pre, code, #xplayer") ||
        img.classList.contains("emoji") ||
        img.classList.contains("avatar") ||
        img.classList.contains("comment-ua-icon") ||
        img.classList.contains("friend-link-card-avatar-img") ||
        img.classList.contains("site-logo-img") ||
        img.classList.contains("lared-title-avatar") ||
        img.closest(".comment-ua-geo") ||
        img.closest(".friend-link-card-avatar") ||
        img.hasAttribute("data-hero-main-image")
      ) {
        return;
      }

      // 设置动画类型
      img.setAttribute("data-img-animation", animationType);
    });
  }

  // 全局 lazysizes 事件监听（只注册一次）
  if (!window.__laredLazysizesInited) {
    window.__laredLazysizesInited = true;
    document.addEventListener("lazyloaded", function (e) {
      var img = e.target;
      if (!img || img.tagName !== "IMG") return;

      // 处理 loading-wrapper 的加载完成
      var wrapper = img.closest(".img-loading-wrapper");
      if (wrapper && !wrapper.classList.contains("is-loaded")) {
        wrapper.classList.add("is-loaded");
      }
    });
  }

  function normalizePath(path) {
    if (!path) {
      return "/";
    }

    var normalized = path.replace(/\/+$/, "");
    return normalized || "/";
  }

  function syncHeaderNavActiveState() {
    var nav = document.querySelector(".nav-wrap .nav");
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
    var currentSearch = currentUrl.search || "";

    var links = Array.prototype.slice.call(nav.querySelectorAll("a[href]"));
    if (!links.length) {
      return;
    }

    var candidates = [];

    links.forEach(function (link) {
      var li = link.closest("li");
      if (!li) {
        return;
      }

      li.classList.remove(
        "current-menu-item",
        "current_page_item",
        "current-menu-ancestor",
        "current_page_ancestor",
      );
      link.classList.remove("is-active", "is-ancestor-active");

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
      var linkSearch = linkUrl.search || "";
      var score = 0;

      if (linkPath === currentPath && linkSearch === currentSearch) {
        score = 3000 + linkPath.length;
      } else if (linkPath === currentPath) {
        score = 2000 + linkPath.length;
      } else if (
        linkPath !== "/" &&
        (currentPath + "/").indexOf(linkPath + "/") === 0
      ) {
        score = 1000 + linkPath.length;
      } else if (linkPath === "/" && currentPath === "/") {
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
    activeLi.classList.add("current-menu-item", "current_page_item");
    var activeLink =
      activeLi.querySelector(":scope > a") || activeLi.querySelector("a");
    if (activeLink) {
      activeLink.classList.add("is-active");
    }

    var ancestor = activeLi.parentElement;
    while (ancestor) {
      if (ancestor.tagName && ancestor.tagName.toLowerCase() === "li") {
        ancestor.classList.add(
          "current-menu-ancestor",
          "current_page_ancestor",
        );
        var ancestorLink =
          ancestor.querySelector(":scope > a") || ancestor.querySelector("a");
        if (ancestorLink) {
          ancestorLink.classList.add("is-ancestor-active");
        }
      }
      ancestor = ancestor.parentElement;
    }
  }

  function initRssCopyButton() {
    var rssButtons = document.querySelectorAll(
      '[data-rss-copy]:not([data-rss-copy-ready="1"])',
    );
    if (!rssButtons.length) {
      return;
    }

    rssButtons.forEach(function (rssButton) {
      rssButton.setAttribute("data-rss-copy-ready", "1");

      var icon = rssButton.querySelector("i");
      var resetTimer = null;

      var copyText = function (text) {
        if (
          navigator.clipboard &&
          typeof navigator.clipboard.writeText === "function" &&
          window.isSecureContext
        ) {
          return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
          var textarea = document.createElement("textarea");
          textarea.value = text;
          textarea.setAttribute("readonly", "");
          textarea.style.position = "fixed";
          textarea.style.left = "-9999px";
          document.body.appendChild(textarea);
          textarea.select();

          try {
            var copied = document.execCommand("copy");
            document.body.removeChild(textarea);
            if (copied) {
              resolve();
            } else {
              reject(new Error("copy failed"));
            }
          } catch (error) {
            document.body.removeChild(textarea);
            reject(error);
          }
        });
      };

      var setCopiedState = function () {
        rssButton.classList.add("is-copied");
        rssButton.setAttribute("aria-label", "Feed copied");
        var tooltipEl = rssButton.querySelector(".rss-tooltip");
        if (tooltipEl) {
          tooltipEl.textContent = "复制成功";
        }
        if (icon) {
          icon.classList.remove("fa-rss");
          icon.classList.add("fa-check");
        }

        if (resetTimer) {
          window.clearTimeout(resetTimer);
        }
        resetTimer = window.setTimeout(function () {
          rssButton.classList.remove("is-copied");
          rssButton.setAttribute("aria-label", "RSS Feed");
          if (tooltipEl) {
            tooltipEl.textContent = "点击复制订阅地址";
          }
          if (icon) {
            icon.classList.remove("fa-check");
            icon.classList.add("fa-rss");
          }
        }, 1600);
      };

      rssButton.addEventListener("click", function (event) {
        event.preventDefault();
        var feedUrl =
          rssButton.getAttribute("data-feed-url") ||
          rssButton.getAttribute("href") ||
          "";
        if (!feedUrl) {
          return;
        }

        copyText(feedUrl)
          .then(function () {
            setCopiedState();
          })
          .catch(function () {
            rssButton.setAttribute("title", "复制失败");
          });
      });
    });
  }

  /* header-login.js - Header 和 Footer 登录下拉框 + AJAX 登录 */
  function initHeaderLogin() {
    // 支持多个登录按钮（header 和 footer）
    var loginWrappers = document.querySelectorAll(
      ".header-login-wrapper, .footer-login-wrapper",
    );

    if (!loginWrappers.length) {
      return;
    }

    loginWrappers.forEach(function (loginWrapper) {
      // 防止 PJAX 导航后重复绑定（footer 不在 Barba 容器内，DOM 不会被替换）
      if (loginWrapper._loginBound) {
        return;
      }
      loginWrapper._loginBound = true;

      var loginToggle = loginWrapper.querySelector("[data-login-toggle]");
      var loginDropdown = loginWrapper.querySelector("[data-login-dropdown]");

      if (!loginToggle || !loginDropdown) {
        return;
      }

      // 切换下拉框显示/隐藏
      loginToggle.addEventListener("click", function (event) {
        event.stopPropagation();
        closeAllLoginDropdowns(loginDropdown, loginWrapper);
        var isActive = loginDropdown.classList.contains("is-active");
        loginDropdown.classList.toggle("is-active", !isActive);
        loginWrapper.classList.toggle("is-open", !isActive);
      });

      // 点击下拉框内部不关闭
      loginDropdown.addEventListener("click", function (event) {
        event.stopPropagation();
      });

      // AJAX 登录表单
      var loginForm = loginWrapper.querySelector("[data-login-form]");
      if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
          event.preventDefault();
          handleAjaxLogin(loginForm, loginWrapper);
        });
      }
    });

    // 全局事件只绑定一次
    if (!window._loginClickHandlerBound) {
      document.addEventListener("click", function () {
        closeAllLoginDropdowns();
      });
      window._loginClickHandlerBound = true;
    }

    if (!window._loginKeyHandlerBound) {
      document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
          closeAllLoginDropdowns();
        }
      });
      window._loginKeyHandlerBound = true;
    }
  }

  
// 初始化页脚/头部头像菜单（PJAX 后需要重新绑定）
function initFooterAvatar() {
  // 处理头像菜单的点击显示/隐藏（移动端或 focus 管理）
  document.querySelectorAll(".footer-avatar-wrapper").forEach(function (wrapper) {
    // 防止重复绑定
    if (wrapper._avatarBound) return;
    wrapper._avatarBound = true;

    var avatarLink = wrapper.querySelector(".footer-user-avatar");
    var menu = wrapper.querySelector(".footer-avatar-menu");
    if (!avatarLink || !menu) return;

    // 点击头像切换菜单显示（移动端）
    avatarLink.addEventListener("click", function (e) {
      // 只在移动端处理点击事件，桌面端用 CSS hover
      if (window.innerWidth > 768) return;
      e.preventDefault();
      e.stopPropagation();
      var isActive = menu.classList.contains("is-active");
      // 关闭其他菜单
      document.querySelectorAll(".footer-avatar-menu").forEach(function (m) {
        if (m !== menu) m.classList.remove("is-active");
      });
      menu.classList.toggle("is-active", !isActive);
    });
  });
}
function closeAllLoginDropdowns(exceptDropdown, exceptWrapper) {
    document
      .querySelectorAll("[data-login-dropdown]")
      .forEach(function (dropdown) {
        if (dropdown !== exceptDropdown) {
          dropdown.classList.remove("is-active");
        }
      });
    document
      .querySelectorAll(".header-login-wrapper, .footer-login-wrapper")
      .forEach(function (wrapper) {
        if (wrapper !== exceptWrapper) {
          wrapper.classList.remove("is-open");
        }
      });
  }

  function handleAjaxLogin(form, wrapper) {
    var submitBtn = form.querySelector("[data-login-submit]");
    var textEl = form.querySelector(".footer-login-submit-text");
    var loadingEl = form.querySelector(".footer-login-submit-loading");
    var errorEl = form.querySelector("[data-login-error]");

    if (!submitBtn) return;

    var username = form.querySelector('input[name="log"]').value.trim();
    var password = form.querySelector('input[name="pwd"]').value;
    var remember = form.querySelector('input[name="rememberme"]');

    // 清空错误
    if (errorEl) {
      errorEl.textContent = "";
      errorEl.style.display = "none";
    }

    if (!username || !password) {
      showLoginError(errorEl, "请填写用户名和密码");
      return;
    }

    // 显示加载状态
    submitBtn.disabled = true;
    if (textEl) textEl.style.display = "none";
    if (loadingEl) loadingEl.style.display = "inline-flex";

    var formData = new FormData();
    formData.append("action", "lared_ajax_login");
    formData.append("nonce", LaredAjax.loginNonce);
    formData.append("log", username);
    formData.append("pwd", password);
    if (remember && remember.checked) {
      formData.append("rememberme", "forever");
    }

    fetch(LaredAjax.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      body: formData,
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.success) {
          // 登录成功 → 替换整个 login wrapper 为头像菜单
          var avatarHtml =
            '<div class="footer-avatar-wrapper">' +
            '<a href="' +
            escHtml(data.data.admin_url) +
            '" class="site-footer-icon-link footer-user-avatar" title="' +
            escHtml(data.data.name) +
            '">' +
            '<img src="' +
            escHtml(data.data.avatar) +
            '" alt="' +
            escHtml(data.data.name) +
            '" class="avatar h-full w-full object-cover" />' +
            "</a>" +
            '<div class="footer-avatar-menu">' +
            '<a href="' +
            escHtml(data.data.admin_url) +
            '" class="footer-avatar-menu-item">' +
            '<i class="fa-solid fa-gauge" aria-hidden="true"></i> 仪表盘</a>' +
            '<a href="' +
            escHtml(data.data.admin_url) +
            'profile.php" class="footer-avatar-menu-item">' +
            '<i class="fa-solid fa-user-pen" aria-hidden="true"></i> 个人资料</a>' +
            '<div class="footer-avatar-menu-divider"></div>' +
            '<a href="' +
            escHtml(data.data.logout_url) +
            '" class="footer-avatar-menu-item footer-avatar-menu-logout" data-no-pjax>' +
            '<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> 退出登录</a>' +
            "</div>" +
            "</div>";
          wrapper.outerHTML = avatarHtml;

          // 同步更新所有登录 wrapper（header + footer 可能存在多个）
          document
            .querySelectorAll(".header-login-wrapper, .footer-login-wrapper")
            .forEach(function (otherWrapper) {
              otherWrapper.outerHTML = avatarHtml;
            });

          // 先更新全局登录状态及 nonce（必须在刷新评论区之前，确保 initAjaxCommentSubmit 读到新 nonce）
          if (typeof LaredAjax !== "undefined") {
            LaredAjax.isLoggedIn = true;
            var nonceKeys = ["commentSubmitNonce", "commentEditNonce", "nonce", "memosFilterNonce", "memosPublishNonce", "friendLinkNonce", "levelNonce"];
            nonceKeys.forEach(function (key) {
              if (data.data[key]) LaredAjax[key] = data.data[key];
            });
          }

          // 重置已绑定的表单节点引用，使 initAjaxCommentSubmit 能重新绑定新表单
          _boundCommentForm = null;

          // 刷新评论区：登录后需要重新加载已登录版本的评论
          var commentsSection = document.getElementById("comments");
          if (commentsSection) {
            // 获取 post ID：优先从占位符属性，回退到 body class
            var postId = commentsSection.getAttribute("data-post-id");
            if (!postId) {
              var bodyMatch = document.body.className.match(/postid-(\d+)/);
              if (bodyMatch) postId = bodyMatch[1];
            }

            var _reinitCommentJS = function () {
              _boundCommentForm = null;
              initAjaxCommentSubmit();
              initCommentExpand();
              initEditInfoToggle();
              initEmailAvatar();
              markCommentLinksNoPjax();
              if (window.xmojipick && typeof window.xmojipick.refresh === "function") window.xmojipick.refresh();
              if (window.addComment && typeof window.addComment.init === "function") {
                window.addComment.init();
              }
            };

            if (postId && typeof LaredAjax !== "undefined") {
              // 文章页：直接用 AJAX 接口加载完整评论（避免整页 HTML 返回懒加载占位符）
              fetch(
                LaredAjax.ajaxUrl + "?action=lared_load_comments&post_id=" + encodeURIComponent(postId),
                { credentials: "same-origin" }
              )
                .then(function (r) { return r.json(); })
                .then(function (result) {
                  if (result.success && result.data.html) {
                    var temp = document.createElement("div");
                    temp.innerHTML = result.data.html;
                    var realComments = temp.firstElementChild;
                    if (realComments) {
                      commentsSection.parentNode.replaceChild(realComments, commentsSection);
                    }
                  }
                  _reinitCommentJS();
                })
                .catch(function () { window.location.reload(); });
            } else {
              // 非文章页（无 post ID）：获取整页 HTML 提取已登录版评论区
              fetch(window.location.href, { credentials: "same-origin" })
                .then(function (res) { return res.text(); })
                .then(function (html) {
                  var parser = new DOMParser();
                  var doc = parser.parseFromString(html, "text/html");
                  var newComments = doc.getElementById("comments");
                  if (newComments) {
                    commentsSection.innerHTML = newComments.innerHTML;

                    var freshNonceEl = doc.getElementById("lared-pjax-nonce");
                    if (freshNonceEl && typeof LaredAjax !== "undefined") {
                      try {
                        var freshNonces = JSON.parse(freshNonceEl.textContent);
                        if (freshNonces.commentSubmitNonce) LaredAjax.commentSubmitNonce = freshNonces.commentSubmitNonce;
                        if (freshNonces.commentEditNonce) LaredAjax.commentEditNonce = freshNonces.commentEditNonce;
                        if (freshNonces.loginNonce) LaredAjax.loginNonce = freshNonces.loginNonce;
                      } catch (e) {}
                    }

                    _reinitCommentJS();
                  }
                })
                .catch(function () { window.location.reload(); });
            }
          }
        } else {
          showLoginError(
            errorEl,
            data.data && data.data.message ? data.data.message : "登录失败",
          );
          resetLoginBtn(submitBtn, textEl, loadingEl);
        }
      })
      .catch(function () {
        showLoginError(errorEl, "网络错误，请重试");
        resetLoginBtn(submitBtn, textEl, loadingEl);
      });
  }

  function showLoginError(el, msg) {
    if (!el) return;
    el.textContent = msg;
    el.style.display = "block";
  }

  function resetLoginBtn(btn, textEl, loadingEl) {
    btn.disabled = false;
    if (textEl) textEl.style.display = "inline";
    if (loadingEl) loadingEl.style.display = "none";
  }

  function escHtml(str) {
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  /* pjax-init.js */
  function initPjaxZhheo() {
    if (
      document.documentElement.getAttribute("data-lared-pjax-ready") === "1"
    ) {
      return;
    }

    if (typeof window.Pjax === "undefined") {
      return;
    }

    document.documentElement.setAttribute("data-lared-pjax-ready", "1");

    var headerLoading = document.querySelector("[data-header-loading]");
    var loadingShowTime = 0;
    var MIN_LOADING_MS = 0;

    function loadingShow() {
      if (!headerLoading) {
        return;
      }
      loadingShowTime = Date.now();
      headerLoading.classList.add("is-active");
    }

    function loadingHide() {
      if (!headerLoading) {
        return;
      }
      var elapsed = Date.now() - loadingShowTime;
      var remaining = MIN_LOADING_MS - elapsed;
      if (remaining > 0) {
        setTimeout(function () {
          headerLoading.classList.remove("is-active");
        }, remaining);
      } else {
        headerLoading.classList.remove("is-active");
      }
    }

    var pjax = new window.Pjax({
      elements:
        "a[href]:not([data-no-pjax]):not(.comment-reply-link):not(#cancel-comment-reply-link), form[action]:not(#commentform):not([data-no-pjax])",
      selectors: ["title", "head", '[data-barba="container"]'],
      switches: {
        /**
         * 智能合并 <head>：PJAX 导航时自动加载/卸载插件的页面专属 CSS 和 JS
         * - 新页面有、当前没有的 CSS/JS → 追加
         * - 当前有、新页面没有的插件 CSS → 移除（避免样式冲突）
         * - 内联 wp_localize_script 数据 → 同步更新
         */
        head: function (oldHead, newHead) {
          /* ---- 收集当前 head 中的 stylesheet 和 script ---- */
          var curCSS = {};
          var curJS = {};
          Array.prototype.forEach.call(
            oldHead.querySelectorAll('link[rel="stylesheet"][href]'),
            function (el) {
              curCSS[el.id || el.getAttribute("href")] = el;
            },
          );
          Array.prototype.forEach.call(
            oldHead.querySelectorAll("script[src]"),
            function (el) {
              curJS[el.id || el.getAttribute("src")] = el;
            },
          );

          /* ---- 收集新页面 head 中的 stylesheet 和 script ---- */
          var newCSS = {};
          var newJS = {};
          Array.prototype.forEach.call(
            newHead.querySelectorAll('link[rel="stylesheet"][href]'),
            function (el) {
              newCSS[el.id || el.getAttribute("href")] = el;
            },
          );
          Array.prototype.forEach.call(
            newHead.querySelectorAll("script[src]"),
            function (el) {
              newJS[el.id || el.getAttribute("src")] = el;
            },
          );

          /* ---- 追加缺失的 CSS ---- */
          Object.keys(newCSS).forEach(function (key) {
            if (!curCSS[key]) {
              oldHead.appendChild(document.importNode(newCSS[key], true));
            }
          });

          /* ---- 移除新页面不需要的插件 CSS（保留主题/WP 核心样式） ---- */
          Object.keys(curCSS).forEach(function (key) {
            if (
              !newCSS[key] &&
              curCSS[key].id &&
              !/^(lared-|wp-|admin-|dashicons-|global-|xmojipick-)/.test(curCSS[key].id)
            ) {
              curCSS[key].remove();
            }
          });

          /* ---- 追加缺失的 JS（含内联 localize 数据） ---- */
          Object.keys(newJS).forEach(function (key) {
            if (!curJS[key]) {
              var orig = newJS[key];
              // 先插入关联的 wp_localize_script 内联数据（id 形如 handle-js-extra）
              if (orig.id) {
                var extraId = orig.id.replace(/-js$/, "-js-extra");
                var extraEl = newHead.querySelector(
                  "script[id=" + JSON.stringify(extraId) + "]",
                );
                if (extraEl) {
                  var inlineS = document.createElement("script");
                  inlineS.id = extraId;
                  inlineS.textContent = extraEl.textContent;
                  oldHead.appendChild(inlineS);
                }
              }
              // 再插入外部脚本（保证 localize 数据先就绪）
              var s = document.createElement("script");
              s.src = orig.getAttribute("src");
              if (orig.id) s.id = orig.id;
              oldHead.appendChild(s);
            }
          });

          /* ---- 更新已有脚本的 wp_localize_script 内联数据 ---- */
          Array.prototype.forEach.call(
            newHead.querySelectorAll("script[id]:not([src])"),
            function (newInline) {
              if (!newInline.id) return;
              var oldInline = oldHead.querySelector(
                "script[id=" + JSON.stringify(newInline.id) + "]",
              );
              if (oldInline && oldInline.textContent !== newInline.textContent) {
                var fresh = document.createElement("script");
                fresh.id = newInline.id;
                if (newInline.type) fresh.type = newInline.type;
                fresh.textContent = newInline.textContent;
                oldInline.parentNode.replaceChild(fresh, oldInline);
              }
            },
          );

          this.onSwitch();
        },
      },
      cacheBust: false,
      scrollTo: 0,
    });

    if (pjax && typeof pjax.refresh === "function") {
      pjax.refresh();
    }

    /* ── PJAX hover 预加载 ── */
    var prefetchCache = {};
    var PREFETCH_MAX = 20;
    var PREFETCH_TTL = 30000; // 30s

    function prefetchClean() {
      var keys = Object.keys(prefetchCache);
      var now = Date.now();
      for (var i = 0; i < keys.length; i++) {
        if (now - prefetchCache[keys[i]].time > PREFETCH_TTL) {
          delete prefetchCache[keys[i]];
        }
      }
      if (Object.keys(prefetchCache).length > PREFETCH_MAX) {
        var sorted = Object.keys(prefetchCache).sort(function (a, b) {
          return prefetchCache[a].time - prefetchCache[b].time;
        });
        for (var j = 0; j < sorted.length - PREFETCH_MAX; j++) {
          delete prefetchCache[sorted[j]];
        }
      }
    }

    function prefetchUrl(url) {
      if (prefetchCache[url]) return;
      var xhr = new XMLHttpRequest();
      prefetchCache[url] = { time: Date.now(), html: null, pending: true, xhr: xhr };
      xhr.open("GET", url, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.setRequestHeader("X-PJAX", "true");
      xhr.setRequestHeader(
        "X-PJAX-Selectors",
        JSON.stringify(pjax.options.selectors)
      );
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          prefetchCache[url] = {
            time: Date.now(),
            html: xhr.responseText,
            pending: false,
            xhr: null,
          };
        } else if (xhr.readyState === 4) {
          delete prefetchCache[url];
        }
      };
      xhr.send(null);
      prefetchClean();
    }

    // monkey-patch doRequest：命中缓存或复用进行中的预加载，避免重复请求
    var origDoRequest = pjax.doRequest;
    pjax.doRequest = function (href, options, callback) {
      var cached = prefetchCache[href];

      // 预加载已完成 → 直接使用缓存
      if (cached && !cached.pending && cached.html) {
        var fakeXhr = {
          readyState: 4,
          status: 200,
          responseText: cached.html,
          getResponseHeader: function (h) {
            if (h.toLowerCase() === "x-pjax-url") return href;
            return null;
          },
          setRequestHeader: function () {},
          abort: function () {},
        };
        delete prefetchCache[href];
        setTimeout(function () {
          callback(cached.html, fakeXhr, href, options);
        }, 0);
        return fakeXhr;
      }

      // 预加载进行中 → 等待已有请求完成，不发第二个请求
      if (cached && cached.pending && cached.xhr) {
        var pendingXhr = cached.xhr;
        var origHandler = pendingXhr.onreadystatechange;
        pendingXhr.onreadystatechange = function () {
          if (origHandler) origHandler.call(this);
          if (pendingXhr.readyState === 4) {
            if (pendingXhr.status === 200) {
              callback(pendingXhr.responseText, pendingXhr, href, options);
            } else {
              // 预加载失败 → 回退到新请求
              origDoRequest.call(pjax, href, options, callback);
            }
            delete prefetchCache[href];
          }
        };
        return { abort: function () { pendingXhr.abort(); } };
      }

      return origDoRequest.call(pjax, href, options, callback);
    };

    // hover 时预加载
    function isPjaxLink(el) {
      if (!el || el.tagName !== "A") return false;
      if (!el.href) return false;
      if (el.hasAttribute("data-no-pjax")) return false;
      if (el.classList.contains("comment-reply-link")) return false;
      if (el.getAttribute("target") === "_blank") return false;
      // 同源检查
      try {
        var u = new URL(el.href, location.origin);
        if (u.origin !== location.origin) return false;
      } catch (e) {
        return false;
      }
      return true;
    }

    var prefetchTimer = null;
    document.addEventListener(
      "mouseenter",
      function (e) {
        var link = e.target.closest ? e.target.closest("a") : null;
        if (!isPjaxLink(link)) return;
        var url = link.href;
        if (prefetchCache[url]) return;
        prefetchTimer = setTimeout(function () {
          prefetchUrl(url);
        }, 65);
      },
      true
    );
    document.addEventListener(
      "mouseleave",
      function (e) {
        if (prefetchTimer) {
          clearTimeout(prefetchTimer);
          prefetchTimer = null;
        }
      },
      true
    );
    /* ── /PJAX hover 预加载 ── */

    /* ── PJAX 模糊遮罩 ── */
    var pjaxOverlay = null;

    function ensurePjaxOverlay() {
      if (pjaxOverlay) return pjaxOverlay;
      var el = document.createElement("div");
      el.className = "pjax-overlay";
      el.setAttribute("aria-hidden", "true");
      el.innerHTML = '<div class="pjax-overlay-spinner"></div>';
      document.body.appendChild(el);
      pjaxOverlay = el;
      return el;
    }

    function showPjaxOverlay() {
      ensurePjaxOverlay().style.display = "block";
    }

    function hidePjaxOverlay() {
      if (pjaxOverlay) pjaxOverlay.style.display = "none";
    }
    /* ── /PJAX 模糊遮罩 ── */

    document.addEventListener("pjax:send", function () {
      loadingShow();
      window.scrollTo(0, 0);
      document.documentElement.classList.add("pjax-loading");
      showPjaxOverlay();
      // 强制关闭 header 下拉菜单
      document
        .querySelectorAll(".nav .menu-item-has-children")
        .forEach(function (item) {
          item.classList.add("nav-dropdown-hidden");
        });
      // 关闭搜索弹窗（搜索表单提交触发 PJAX 时弹窗需要关闭）
      var searchModal = document.querySelector("[data-search-modal]");
      if (searchModal && searchModal.open) searchModal.close();
      // View Transitions: 标记过渡中状态
      if (document.startViewTransition) {
        document.documentElement.classList.add("vt-transitioning");
      }
    });

    document.addEventListener("pjax:complete", function () {
      loadingHide();
      hidePjaxOverlay();
      // 新内容已替换，延一帧确保浏览器渲染后再淡入
      requestAnimationFrame(function () {
        document.documentElement.classList.remove("pjax-loading");
      });
      // 移除下拉菜单隐藏标记
      document
        .querySelectorAll(".nav .menu-item-has-children.nav-dropdown-hidden")
        .forEach(function (item) {
          item.classList.remove("nav-dropdown-hidden");
        });
      // View Transitions: 清除过渡标记
      if (document.startViewTransition) {
        document.documentElement.classList.remove("vt-transitioning");
      }
      // 延迟一帧确保 PJAX 替换的 DOM 完全就绪后再初始化
      setTimeout(reinitAfterPjax, 0);
    });

    document.addEventListener("pjax:error", function () {
      loadingHide();
      hidePjaxOverlay();
      document.documentElement.classList.remove("pjax-loading");
    });
  }

  function initHomeModules() {
    initTabs();
    initHeroSwitch();
    initToc();
    initSidebarTabs();
    initCategoryIconShift();
  }

  /* 侧边栏分类 icon 悬浮滑动 */
  var _categoryIconResizeHandler = null;
  function initCategoryIconShift() {
    var categoryLinks = document.querySelectorAll(
      ".home-main-sidebar-list-categories a"
    );

    if (_categoryIconResizeHandler) {
      window.removeEventListener("resize", _categoryIconResizeHandler);
      _categoryIconResizeHandler = null;
    }

    if (!categoryLinks.length) return;

    var update = function (link) {
      var icon = link.querySelector(".home-main-category-icon");
      var count = link.querySelector("em");
      if (!icon || !count) return;

      var linkRect = link.getBoundingClientRect();
      var iconRect = icon.getBoundingClientRect();
      var countRect = count.getBoundingClientRect();
      var gap = 12;
      var shift = countRect.left - iconRect.left - iconRect.width - gap;

      if (!Number.isFinite(shift) || shift < 0) shift = 0;
      if (countRect.right > linkRect.right) {
        shift = Math.max(0, shift - (countRect.right - linkRect.right));
      }
      link.style.setProperty("--category-icon-shift", shift + "px");
    };

    categoryLinks.forEach(function (link) {
      update(link);
      link.addEventListener("mouseenter", function () {
        update(link);
      });
      link.addEventListener("focus", function () {
        update(link);
      });
    });

    _categoryIconResizeHandler = function () {
      categoryLinks.forEach(update);
    };
    window.addEventListener("resize", _categoryIconResizeHandler);
  }

  /* 首页侧边栏 Tab 切换 */
  function initSidebarTabs() {
    var tabContainer = document.querySelector(".home-sidebar-tabs");
    if (!tabContainer) return;

    var tabs = tabContainer.querySelectorAll(".home-sidebar-tab");
    var panels = document.querySelectorAll(".home-sidebar-tab-panel");

    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        var targetTab = this.getAttribute("data-tab");

        // 切换 Tab 按钮状态
        tabs.forEach(function (t) {
          t.classList.remove("is-active");
        });
        this.classList.add("is-active");

        // 切换面板显示
        panels.forEach(function (panel) {
          if (panel.getAttribute("data-panel") === targetTab) {
            panel.classList.add("is-active");
          } else {
            panel.classList.remove("is-active");
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
    initLazyComments();
    initAjaxCommentSubmit();
    initCommentExpand();
    initEditInfoToggle();
    initEmailAvatar();
    initPrismEnhance(document);
    initViewImage();
    initImageLoadAnimation();
    initArticleImageLoading();
    initHeaderLogin();
    initFooterAvatar(); initInlineCodeCleaner();
    initInlineColorCodeChip();
    initSearchModal();
    initMobileMenu();
    initMobileSearchBtn();
    initMobileToc();
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
    var codes = document.querySelectorAll("code:not(pre code)");
    codes.forEach(function (code) {
      var html = code.innerHTML;

      // 检查是否包含反引号（包括 HTML 实体编码的）
      // 匹配开头的反引号: ` 或 &#96; 或 &#x60; 或 &grave;
      // 匹配结尾的反引号
      var hasLeadingBacktick =
        /^(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+/.test(
          html,
        );
      var hasTrailingBacktick =
        /(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+$/.test(
          html,
        );

      if (hasLeadingBacktick || hasTrailingBacktick) {
        // 去除开头和结尾的反引号及其 HTML 实体
        var cleaned = html
          .replace(
            /^(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+/,
            "",
          )
          .replace(
            /(&#96;|&#x60;|&grave;|`|'|&apos;|&#39;|&lsquo;|&rsquo;|&#8216;|&#8217;)+$/,
            "",
          );
        code.innerHTML = cleaned;
      }
    });
  }

  /**
   * 文章内颜色值高亮：识别 #RGB/#RRGGBB/#RRGGBBAA。
   * 显示“色点 + 色号”，悬浮提示 RGB 与复制信息，点击复制 HEX。
   */
  function initInlineColorCodeChip() {
    var codes = document.querySelectorAll(
      ".single-article-content code:not(pre code)",
    );
    if (!codes.length) {
      return;
    }

    var normalizeHexColor = function (input) {
      var raw = String(input || "").trim();
      if (!raw) {
        return "";
      }

      var value = raw.charAt(0) === "#" ? raw.slice(1) : raw;
      if (!/^[0-9a-fA-F]{3,8}$/.test(value)) {
        return "";
      }

      if (
        value.length !== 3 &&
        value.length !== 4 &&
        value.length !== 6 &&
        value.length !== 8
      ) {
        return "";
      }

      return "#" + value.toUpperCase();
    };

    var hexToRgbaString = function (hexColor) {
      var hex = String(hexColor || "").replace("#", "");
      if (hex.length === 3 || hex.length === 4) {
        hex = hex
          .split("")
          .map(function (ch) {
            return ch + ch;
          })
          .join("");
      }

      if (hex.length !== 6 && hex.length !== 8) {
        return "";
      }

      var r = parseInt(hex.slice(0, 2), 16);
      var g = parseInt(hex.slice(2, 4), 16);
      var b = parseInt(hex.slice(4, 6), 16);

      if (hex.length === 8) {
        var a = parseInt(hex.slice(6, 8), 16) / 255;
        return "rgba(" + r + ", " + g + ", " + b + ", " + a.toFixed(2) + ")";
      }

      return "rgb(" + r + ", " + g + ", " + b + ")";
    };

    var getReadableTextColor = function (hexColor) {
      var hex = String(hexColor || "").replace("#", "");
      if (hex.length === 3 || hex.length === 4) {
        hex = hex
          .split("")
          .map(function (ch) {
            return ch + ch;
          })
          .join("");
      }

      if (hex.length < 6) {
        return "#FFFFFF";
      }

      var r = parseInt(hex.slice(0, 2), 16);
      var g = parseInt(hex.slice(2, 4), 16);
      var b = parseInt(hex.slice(4, 6), 16);
      var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

      return luminance > 0.62 ? "#1B1B1B" : "#FFFFFF";
    };

    var copyText = function (value) {
      if (
        navigator.clipboard &&
        typeof navigator.clipboard.writeText === "function"
      ) {
        return navigator.clipboard.writeText(value);
      }

      return new Promise(function (resolve, reject) {
        var textarea = document.createElement("textarea");
        textarea.value = value;
        textarea.setAttribute("readonly", "readonly");
        textarea.style.position = "fixed";
        textarea.style.left = "-9999px";
        document.body.appendChild(textarea);
        textarea.select();

        try {
          var ok = document.execCommand("copy");
          document.body.removeChild(textarea);
          if (ok) {
            resolve();
          } else {
            reject(new Error("copy_failed"));
          }
        } catch (error) {
          document.body.removeChild(textarea);
          reject(error);
        }
      });
    };

    codes.forEach(function (code) {
      if (code.getAttribute("data-color-chip-ready") === "1") {
        return;
      }

      var normalizedHex = normalizeHexColor(code.textContent || "");
      if (!normalizedHex) {
        return;
      }

      var rgbText = hexToRgbaString(normalizedHex);
      var tooltipBase =
        "点击复制 " + normalizedHex + (rgbText ? " · " + rgbText : "");

      code.setAttribute("data-color-chip-ready", "1");
      code.classList.add("lared-color-code");
      code.style.setProperty("--lared-color-hex", normalizedHex);
      code.style.setProperty(
        "--lared-color-text",
        getReadableTextColor(normalizedHex),
      );
      code.style.setProperty("--lared-tooltip-bg", normalizedHex);
      code.style.setProperty(
        "--lared-tooltip-text",
        getReadableTextColor(normalizedHex),
      );
      code.setAttribute("data-color-hex", normalizedHex);
      if (rgbText) {
        code.setAttribute("data-color-rgb", rgbText);
      }
      code.setAttribute("data-tooltip", tooltipBase);
      code.setAttribute("role", "button");
      code.setAttribute("tabindex", "0");
      code.setAttribute(
        "aria-label",
        "颜色值 " +
          normalizedHex +
          (rgbText ? "，" + rgbText : "") +
          "，点击复制",
      );

      code.innerHTML =
        '<span class="lared-color-code__dot" aria-hidden="true"></span><span class="lared-color-code__label">' +
        normalizedHex +
        "</span>";

      var onCopy = function (event) {
        if (event) {
          event.preventDefault();
        }

        copyText(normalizedHex)
          .then(function () {
            code.setAttribute(
              "data-tooltip",
              "已复制 " + normalizedHex + (rgbText ? " · " + rgbText : ""),
            );
            window.setTimeout(function () {
              code.setAttribute("data-tooltip", tooltipBase);
            }, 1500);
          })
          .catch(function () {
            code.setAttribute("data-tooltip", "复制失败，请手动复制");
            window.setTimeout(function () {
              code.setAttribute("data-tooltip", tooltipBase);
            }, 1500);
          });
      };

      code.addEventListener("click", onCopy);
      code.addEventListener("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
          onCopy(event);
        }
      });
    });
  }

  function reinitAfterPjax() {
    // PJAX 不会重新执行 wp_footer 中的脚本，从容器内的 JSON 元素提取最新 nonce
    var nonceEl = document.getElementById("lared-pjax-nonce");
    if (nonceEl && typeof LaredAjax !== "undefined") {
      try {
        var nd = JSON.parse(nonceEl.textContent);
        if (nd.commentSubmitNonce)
          LaredAjax.commentSubmitNonce = nd.commentSubmitNonce;
        if (nd.commentEditNonce)
          LaredAjax.commentEditNonce = nd.commentEditNonce;
        if (nd.loginNonce) LaredAjax.loginNonce = nd.loginNonce;
        if (typeof nd.isLoggedIn !== "undefined")
          LaredAjax.isLoggedIn = nd.isLoggedIn;
      } catch (e) {}
    }

    // 重置已绑定表单引用，PJAX 替换内容后旧节点已被销毁
    _boundCommentForm = null;

    // 用 safeCalls 包裹每个初始化函数，防止某个函数抛错导致其余全部跳过
    var tasks = [
      syncHeaderNavActiveState,
      initHeroSwitch,
      initTabs,
      initSingleSideToc,
      initToc,
      initSidebarTabs,
      trackPostViews,
      function () {
        initPrismEnhance(document);
      },
      initViewImage,
      initImageLoadAnimation,
      initLazyComments,
      initAjaxCommentSubmit,
      initCommentExpand,
      initEditInfoToggle,
      initEmailAvatar,
      markCommentLinksNoPjax,
      initRssCopyButton,
      initArticleImageLoading,
      initHeaderLogin,
      initFooterAvatar, initInlineCodeCleaner,
      initInlineColorCodeChip,
      initSearchModal,
      initMobileMenu,
      initMobileSearchBtn,
      initMobileToc,
      initMemosPublish,
      initMemosFilter,
      initPlyr,
      trackFooterVisitor,
      initMusicPlayer,
      initCategoryIconShift,
      function () {
        /* PJAX 后重新初始化 xalbum 插件 */
        if (typeof window.initXalbum === "function") {
          window.initXalbum();
        }
      },
      function () {
        // 重新初始化 WordPress 评论回复表单移动功能（PJAX 替换内容后旧的事件绑定已丢失）
        if (window.addComment && typeof window.addComment.init === "function") {
          window.addComment.init();
        }
      },
      function () {
        /* PJAX 后重新初始化 xMojipick 表情插件 */
        if (window.xmojipick && typeof window.xmojipick.refresh === "function") {
          window.xmojipick.refresh();
        }
      },
    ];

    for (var i = 0; i < tasks.length; i++) {
      try {
        tasks[i]();
      } catch (err) {
        if (typeof console !== "undefined" && console.error) {
          console.error("[PJAX reinit] task " + i + " failed:", err);
        }
      }
    }
  }

  function init() {
    initHomeModules();
    initSingleModules();
    initGlobalModules();
  }

  /* memos-publish.js - Memos 发布功能 */
  function initMemosPublish() {
    var form = document.getElementById("memos-publish-form");
    if (!form) {
      return;
    }

    var submitBtn = form.querySelector(".memos-publish-submit");
    var statusDiv = document.getElementById("memos-publish-status");
    var textarea = document.getElementById("memos-content");
    var tagList = document.getElementById("memos-tag-list");

    // 点击标签按钮，自动填入文本框
    if (tagList && textarea) {
      tagList.addEventListener("click", function (e) {
        var tagBtn = e.target.closest(".memos-publish-tag-btn");
        if (tagBtn) {
          var tag = tagBtn.getAttribute("data-tag");
          if (tag) {
            var currentValue = textarea.value;
            var tagText = "#" + tag;
            // 检查是否已包含该标签
            if (currentValue.indexOf(tagText) === -1) {
              // 在末尾添加标签（前面加空格）
              var newValue = currentValue
                ? currentValue + " " + tagText
                : tagText;
              textarea.value = newValue;
            }
            // 聚焦文本框
            textarea.focus();
          }
        }
      });
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var content = textarea ? textarea.value.trim() : "";
      if (!content) {
        showStatus("请输入内容", "error");
        return;
      }

      // 从内容中提取标签
      var tags = [];
      var tagMatches = content.match(/#([\w\u4e00-\u9fa5\-]{1,32})/g);
      if (tagMatches) {
        tagMatches.forEach(function (match) {
          var tag = match.replace(/^#/, "");
          if (tags.indexOf(tag) === -1) {
            tags.push(tag);
          }
        });
      }

      var visibility = form.querySelector('[name="memos_visibility"]');
      var visibilityValue = visibility ? visibility.value : "PUBLIC";

      // 禁用提交按钮
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML =
          '<i class="fa-solid fa-spinner fa-spin"></i> 发布中...';
      }

      // 发送 AJAX 请求
      var formData = new FormData();
      formData.append("action", "lared_publish_memo");
      formData.append(
        "nonce",
        form.querySelector('[name="memos_publish_nonce"]').value,
      );
      formData.append("content", content);
      formData.append("visibility", visibilityValue);
      tags.forEach(function (tag) {
        formData.append("tags[]", tag);
      });

      var ajaxUrl =
        (window.LaredAjax && window.LaredAjax.ajaxUrl) ||
        "/wp-admin/admin-ajax.php";
      fetch(ajaxUrl, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data.success) {
            showStatus(data.data.message || "发布成功", "success");
            // 清空表单
            if (textarea) textarea.value = "";
            // 可选：刷新页面显示新内容
            setTimeout(function () {
              window.location.reload();
            }, 1000);
          } else {
            showStatus(data.data.message || "发布失败", "error");
          }
        })
        .catch(function (error) {
          showStatus("网络错误，请重试", "error");
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML =
              '<i class="fa-solid fa-paper-plane"></i> 发布';
          }
        });
    });

    function showStatus(message, type) {
      if (!statusDiv) return;
      statusDiv.textContent = message;
      statusDiv.className = "memos-publish-status is-" + type;
      setTimeout(function () {
        statusDiv.className = "memos-publish-status";
      }, 5000);
    }
  }

  /* memos-filter.js - Memos 筛选功能（日历、关键词） */
  function initMemosFilter() {
    var grid = document.querySelector(".memos-grid");
    if (!grid) return;

    var ajaxUrl =
      (window.LaredAjax && window.LaredAjax.ajaxUrl) ||
      "/wp-admin/admin-ajax.php";
    var nonce = (window.LaredAjax && window.LaredAjax.memosFilterNonce) || "";
    var isLoading = false;

    // 创建 loading 元素
    var loadingEl = document.createElement("div");
    loadingEl.className = "memos-loading";
    loadingEl.innerHTML = '<span class="memos-loading-spinner"></span>';
    loadingEl.style.display = "none";
    grid.parentNode.insertBefore(loadingEl, grid);

    // 创建过滤器标题
    var filterTitleEl = document.createElement("div");
    filterTitleEl.className = "memos-filter-title";
    filterTitleEl.style.display = "none";
    filterTitleEl.innerHTML =
      '<span class="memos-filter-text"></span><button type="button" class="memos-filter-clear" title="清除筛选"><i class="fa-solid fa-xmark"></i></button>';
    grid.parentNode.insertBefore(filterTitleEl, grid);

    // 清除筛选按钮点击事件
    filterTitleEl
      .querySelector(".memos-filter-clear")
      .addEventListener("click", function () {
        resetFilter();
      });

    // 恢复默认（重新加载所有）
    function resetFilter() {
      filterTitleEl.style.display = "none";
      // 重新加载页面获取所有内容
      window.location.reload();
    }

    // 绑定日历点击事件
    document
      .querySelectorAll(".memos-calendar-day.has-memos")
      .forEach(function (day) {
        day.addEventListener("click", function () {
          var date = this.getAttribute("data-date");
          if (date && !isLoading) {
            loadMemosByDate(date);
          }
        });
      });

    // 绑定关键词点击事件
    document
      .querySelectorAll(
        ".memos-sidebar-tags [data-keyword], .memos-card-keyword[data-keyword]",
      )
      .forEach(function (tag) {
        tag.style.cursor = "pointer";
        tag.addEventListener("click", function (e) {
          e.preventDefault();
          var keyword = this.getAttribute("data-keyword");
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
      filterTitleEl.querySelector(".memos-filter-text").textContent =
        "日期: " + date;
      filterTitleEl.style.display = "flex";

      var formData = new FormData();
      formData.append("action", "lared_get_memos_by_date");
      formData.append("nonce", nonce);
      formData.append("date", date);

      fetch(ajaxUrl, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data.success) {
            grid.innerHTML = data.data.html;
            // 重新绑定新加载内容的关键词点击
            bindKeywordClicks();
          } else {
            grid.innerHTML =
              '<div class="memos-error">' +
              (data.data.message || "加载失败") +
              "</div>";
          }
        })
        .catch(function () {
          grid.innerHTML = '<div class="memos-error">网络错误，请重试</div>';
        })
        .finally(function () {
          showLoading(false);
          isLoading = false;
        });
    }

    // 按关键词加载
    function loadMemosByKeyword(keyword) {
      if (isLoading) return;
      isLoading = true;

      showLoading(true);
      filterTitleEl.querySelector(".memos-filter-text").innerHTML =
        '关键词: <span class="memos-filter-keyword">#' + keyword + "</span>";
      filterTitleEl.style.display = "flex";

      var formData = new FormData();
      formData.append("action", "lared_get_memos_by_keyword");
      formData.append("nonce", nonce);
      formData.append("keyword", keyword);

      fetch(ajaxUrl, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data.success) {
            grid.innerHTML = data.data.html;
            // 重新绑定新加载内容的关键词点击
            bindKeywordClicks();
          } else {
            grid.innerHTML =
              '<div class="memos-error">' +
              (data.data.message || "加载失败") +
              "</div>";
          }
        })
        .catch(function () {
          grid.innerHTML = '<div class="memos-error">网络错误，请重试</div>';
        })
        .finally(function () {
          showLoading(false);
          isLoading = false;
        });
    }

    // 绑定关键词点击（用于新加载的内容）
    function bindKeywordClicks() {
      document
        .querySelectorAll(".memos-card-keyword[data-keyword]")
        .forEach(function (tag) {
          tag.style.cursor = "pointer";
          tag.addEventListener("click", function (e) {
            e.preventDefault();
            var keyword = this.getAttribute("data-keyword");
            if (keyword && !isLoading) {
              loadMemosByKeyword(keyword);
            }
          });
        });
    }

    // 显示/隐藏 loading
    function showLoading(show) {
      loadingEl.style.display = show ? "flex" : "none";
      if (show) {
        grid.style.opacity = "0.5";
      } else {
        grid.style.opacity = "1";
      }
    }

    // 日历翻页功能
    initCalendarNav();

    function initCalendarNav() {
      var calendar = document.getElementById("memos-calendar");
      var sidebar = document.querySelector(".memos-sidebar");
      if (!calendar || !sidebar) return;

      var prevBtn = sidebar.querySelector(".memos-calendar-prev");
      var nextBtn = sidebar.querySelector(".memos-calendar-next");
      var titleEl = document.getElementById("memos-calendar-title");
      var daysEl = document.getElementById("memos-calendar-days");

      var currentYear = parseInt(calendar.getAttribute("data-year"), 10);
      var currentMonth = parseInt(calendar.getAttribute("data-month"), 10);

      if (prevBtn) {
        prevBtn.addEventListener("click", function () {
          changeMonth(-1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener("click", function () {
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
        if (titleEl)
          titleEl.textContent =
            currentYear + "-" + String(currentMonth).padStart(2, "0");
        calendar.setAttribute("data-year", currentYear);
        calendar.setAttribute("data-month", currentMonth);

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
        var today = new Date().toISOString().split("T")[0];
        var html = "";

        // 空白填充
        for (var i = 0; i < startWeekday; i++) {
          html +=
            '<span class="memos-calendar-day memos-calendar-day-empty"></span>';
        }

        // 日期（先生成基础结构，has-memos类通过AJAX添加）
        for (var day = 1; day <= daysInMonth; day++) {
          var date =
            year +
            "-" +
            String(month).padStart(2, "0") +
            "-" +
            String(day).padStart(2, "0");
          var isToday = date === today ? " is-today" : "";
          html +=
            '<span class="memos-calendar-day' +
            isToday +
            '" data-date="' +
            date +
            '" data-day="' +
            day +
            '">' +
            day +
            "</span>";
        }

        return html;
      }

      function fetchCalendarData(year, month) {
        // 使用现有的AJAX端点获取数据
        var formData = new FormData();
        formData.append("action", "lared_get_memos_calendar");
        formData.append("nonce", nonce);
        formData.append("year", year);
        formData.append("month", month);

        fetch(ajaxUrl, {
          method: "POST",
          body: formData,
          credentials: "same-origin",
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (data.success && data.data.days) {
              // 高亮有文章的日期
              data.data.days.forEach(function (dayInfo) {
                if (dayInfo.has_content) {
                  var dayEl = daysEl.querySelector(
                    '[data-date="' + dayInfo.date + '"]',
                  );
                  if (dayEl) {
                    dayEl.classList.add("has-memos");
                    dayEl.setAttribute("title", dayInfo.count + " 条动态");
                  }
                }
              });
            }
          })
          .catch(function () {
            // 静默失败，不影响使用
          });
      }

      function bindCalendarClicks() {
        daysEl.querySelectorAll(".memos-calendar-day").forEach(function (day) {
          day.addEventListener("click", function () {
            var date = this.getAttribute("data-date");
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
    // 访客追踪：整个会话只执行一次（PJAX 导航不重复）
    if (window._laredVisitorTracked) {
      // 仍需检测首页访问量
      var container2 = document.querySelector('[data-barba="container"]');
      if (container2) {
        var isHome2 =
          document.body.classList.contains("home") ||
          container2.getAttribute("data-barba-namespace") === "home";
        if (isHome2 && !container2.getAttribute("data-home-tracked")) {
          container2.setAttribute("data-home-tracked", "1");
          var hd = new FormData();
          hd.append("action", "lared_track_home_views");
          var ajaxUrl2 =
            (window.LaredAjax && window.LaredAjax.ajaxUrl) ||
            "/wp-admin/admin-ajax.php";
          fetch(ajaxUrl2, {
            method: "POST",
            body: hd,
            credentials: "same-origin",
          }).catch(function () {});
        }
      }
      return;
    }
    window._laredVisitorTracked = true;

    var container = document.querySelector('[data-barba="container"]');
    if (!container) return;

    var ajaxUrl =
      (window.LaredAjax && window.LaredAjax.ajaxUrl) ||
      "/wp-admin/admin-ajax.php";
    var homeUrl = (window.LaredAjax && window.LaredAjax.homeUrl) || "/";

    // 检测是否首页（初始加载 body.home 或 PJAX 后 data-barba-namespace="home"）
    var isHome =
      document.body.classList.contains("home") ||
      container.getAttribute("data-barba-namespace") === "home";

    // 首页访问量自增
    if (isHome) {
      container.setAttribute("data-home-tracked", "1");
      var homeData = new FormData();
      homeData.append("action", "lared_track_home_views");
      fetch(ajaxUrl, {
        method: "POST",
        body: homeData,
        credentials: "same-origin",
      }).catch(function () {});
    }

    // 记录最近访客地理位置（整个会话仅一次）
    var visitorData = new FormData();
    visitorData.append("action", "lared_track_visitor");
    fetch(ajaxUrl, {
      method: "POST",
      body: visitorData,
      credentials: "same-origin",
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (json) {
        if (!json.success || !json.data || json.data.skipped) return;
        var d = json.data;
        var loc = d.city || d.regionName || d.country || "";
        if (!loc) return;

        // 更新 footer 中的最近访客显示
        var infoEl = document.querySelector(".footer-visitor-info");
        if (!infoEl) return;

        // 查找或创建最近访客 span
        var existingFrom = infoEl.querySelectorAll(".footer-visitor-stat")[1];
        if (!existingFrom && loc) {
          var span = document.createElement("span");
          span.className = "footer-visitor-stat";
          var flagHtml = d.countryCode
            ? '<span class="fi fi-' +
              d.countryCode.toLowerCase() +
              ' footer-visitor-flag"></span>'
            : "";
          span.innerHTML =
            '<i class="fa-sharp fa-light fa-location-dot" aria-hidden="true"></i> 最近访客来自 ' +
            flagHtml +
            ' <span class="footer-visitor-value">' +
            loc +
            "</span>";
          infoEl.appendChild(span);
        }
      })
      .catch(function () {});
  }

  /* track-views.js - AJAX 记录文章浏览量（兼容 PJAX） */
  function trackPostViews() {
    var main = document.querySelector("[data-post-id]");
    if (!main) return;

    var postId = main.getAttribute("data-post-id");
    if (!postId || postId === "0") return;

    // 防止同一次导航重复计数
    if (main.getAttribute("data-views-tracked") === "1") return;
    main.setAttribute("data-views-tracked", "1");

    var ajaxUrl =
      (window.LaredAjax && window.LaredAjax.ajaxUrl) ||
      "/wp-admin/admin-ajax.php";
    var nonce = (window.LaredAjax && window.LaredAjax.nonce) || "";

    var formData = new FormData();
    formData.append("action", "lared_track_views");
    formData.append("nonce", nonce);
    formData.append("post_id", postId);

    fetch(ajaxUrl, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (
          data.success &&
          data.data &&
          typeof data.data.views !== "undefined"
        ) {
          // 更新页面上的热度数字
          var heatBox = document.querySelector(
            ".single-top-banner__stat-box--heat",
          );
          if (heatBox) {
            var numEl = heatBox.querySelector(
              ".single-top-banner__stat-number",
            );
            if (numEl) {
              var v = Number(data.data.views);
              numEl.textContent = v >= 10000 ? (Math.round(v / 100) / 10) + "k" : v.toLocaleString("en-US");
            }
          }
        }
      })
      .catch(function () {
        // 静默失败
      });
  }

  /* ================================================================
   *  移动端汉堡菜单
   * ================================================================ */
  function initMobileMenu() {
    var btn = document.querySelector("[data-mobile-menu-btn]");
    var navWrap = document.querySelector(".nav-wrap");
    if (!btn || !navWrap) return;

    // 防止重复绑定
    if (btn._mobileMenuBound) return;
    btn._mobileMenuBound = true;

    btn.addEventListener("click", function () {
      var isOpen = navWrap.classList.contains("is-open");
      if (isOpen) {
        navWrap.classList.remove("is-open");
        btn.classList.remove("is-active");
        btn.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
      } else {
        navWrap.classList.add("is-open");
        btn.classList.add("is-active");
        btn.setAttribute("aria-expanded", "true");
        document.body.style.overflow = "hidden";
      }
    });

    // PJAX 导航后自动关闭菜单
    navWrap.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        navWrap.classList.remove("is-open");
        btn.classList.remove("is-active");
        btn.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
      });
    });
  }

  /* ================================================================
   *  移动端搜索按钮 → 打开搜索模态框
   * ================================================================ */
  function initMobileSearchBtn() {
    var btn = document.querySelector("[data-mobile-search-btn]");
    if (!btn) return;
    if (btn._mobileSearchBound) return;
    btn._mobileSearchBound = true;

    btn.addEventListener("click", function () {
      var modal = document.querySelector("[data-search-modal]");
      if (!modal) return;
      modal.showModal();
      var input = modal.querySelector(".search-modal-input");
      if (input) {
        setTimeout(function () {
          input.focus();
        }, 100);
      }
    });
  }

  /* ================================================================
   *  移动端 TOC 顶栏按钮 + 底部面板
   * ================================================================ */
  function initMobileToc() {
    var btn = document.querySelector("[data-mobile-toc-btn]");
    var panel = document.querySelector("[data-mobile-toc-panel]");
    var overlay = document.querySelector("[data-mobile-toc-overlay]");
    var closeBtn = document.querySelector("[data-mobile-toc-close]");

    // 没有面板时（非文章页）移除激活class确保按钮不可见
    if (!panel) {
      if (btn) btn.classList.remove("is-active-toc");
      return;
    }
    if (!btn) return;

    // 文章页 — 激活按钮使其可见
    btn.classList.add("is-active-toc");

    // PJAX 后面板是新的，需要重新绑定 — 用面板元素标记
    if (panel._mobileTocBound) return;
    panel._mobileTocBound = true;

    // 先移除旧的事件（克隆替换按钮）
    var newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    btn = newBtn;

    function openToc() {
      panel.classList.add("is-open");
      panel.setAttribute("aria-hidden", "false");
      if (overlay) overlay.classList.add("is-open");
      document.body.style.overflow = "hidden";
    }

    function closeToc() {
      panel.classList.remove("is-open");
      panel.setAttribute("aria-hidden", "true");
      if (overlay) overlay.classList.remove("is-open");
      document.body.style.overflow = "";
    }

    btn.addEventListener("click", openToc);
    if (closeBtn) closeBtn.addEventListener("click", closeToc);
    if (overlay) overlay.addEventListener("click", closeToc);

    // 点击 TOC 链接后关闭面板
    panel.querySelectorAll("[data-mobile-toc-link]").forEach(function (link) {
      link.addEventListener("click", function () {
        closeToc();
      });
    });
  }

  /* search-modal.js - 搜索模态框（<dialog> 原生实现） */
  function initSearchModal() {
    if (window._searchModalBound) return;

    var modal = document.querySelector("[data-search-modal]");
    if (!modal) return;

    var input = modal.querySelector(".search-modal-input");
    var resultsContainer = modal.querySelector("[data-search-results]");
    var escBtn = modal.querySelector(".search-modal-esc");
    var searchTimer = null;

    // 检测是否 Mac
    var isMac = /Mac|iPod|iPhone|iPad/.test(
      navigator.platform || navigator.userAgent,
    );
    // 设置 kbd 文本
    var kbdEls = document.querySelectorAll("[data-search-kbd]");
    kbdEls.forEach(function (kbd) {
      kbd.textContent = isMac ? "⌘K" : "Ctrl+K";
    });

    function openModal() {
      modal.showModal();
      setTimeout(function () {
        if (input) input.focus();
      }, 100);
    }

    function closeModal() {
      modal.close();
      if (input) input.value = "";
      if (resultsContainer) {
        resultsContainer.innerHTML =
          '<div class="search-modal-hint"><p>输入关键词搜索文章</p></div>';
      }
    }

    // 点击 ::backdrop 关闭（dialog 原生事件）
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    // ESC 按钮（dialog 自带 ESC 关闭，此处用于手动点击）
    if (escBtn) {
      escBtn.addEventListener("click", closeModal);
    }

    // dialog 关闭时重置内容
    modal.addEventListener("close", function () {
      if (input) input.value = "";
      if (resultsContainer) {
        resultsContainer.innerHTML =
          '<div class="search-modal-hint"><p>输入关键词搜索文章</p></div>';
      }
    });

    // 键盘快捷键 ⌘K / Ctrl+K
    document.addEventListener("keydown", function (e) {
      var isK = e.key === "k" || e.key === "K";
      if (isK && (isMac ? e.metaKey : e.ctrlKey)) {
        e.preventDefault();
        if (modal.open) {
          closeModal();
        } else {
          openModal();
        }
      }
    });

    // 实时搜索
    if (input) {
      input.addEventListener("input", function () {
        var keyword = this.value.trim();
        if (searchTimer) clearTimeout(searchTimer);

        if (keyword.length < 2) {
          if (resultsContainer) {
            resultsContainer.innerHTML =
              '<div class="search-modal-hint"><p>输入关键词搜索文章</p></div>';
          }
          return;
        }

        searchTimer = setTimeout(function () {
          doSearch(keyword);
        }, 350);
      });

      // 阻止表单默认提交，改为搜索页面跳转
      var form = input.closest("form");
      if (form) {
        form.addEventListener("submit", function (e) {
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

      resultsContainer.innerHTML =
        '<div class="search-modal-loading"><i class="fa-solid fa-spinner fa-spin"></i> 搜索中…</div>';

      var ajaxUrl =
        (window.LaredAjax && window.LaredAjax.ajaxUrl) ||
        "/wp-admin/admin-ajax.php";
      var nonce = (window.LaredAjax && window.LaredAjax.nonce) || "";

      var formData = new FormData();
      formData.append("action", "lared_ajax_search");
      formData.append("nonce", nonce);
      formData.append("keyword", keyword);

      fetch(ajaxUrl, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          if (data.success && data.data.html) {
            resultsContainer.innerHTML = data.data.html;
          } else {
            resultsContainer.innerHTML =
              '<div class="search-modal-empty">没有找到相关文章</div>';
          }
        })
        .catch(function () {
          resultsContainer.innerHTML =
            '<div class="search-modal-empty">搜索失败，请重试</div>';
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
    crOverlay = document.createElement("div");
    crOverlay.className = "cr-window-overlay";
    crOverlay.innerHTML =
      '<div class="cr-window">' +
      '<div class="cr-window-titlebar">' +
      '<span class="cr-window-dots"><i title="关闭"></i><i></i><i></i></span>' +
      '<div class="cr-window-address"><i class="fa-solid fa-lock"></i><span class="cr-window-addr-text">about:blank</span></div>' +
      '<span class="cr-window-title"></span>' +
      "</div>" +
      '<div class="cr-window-body"></div>' +
      "</div>";
    document.body.appendChild(crOverlay);

    crWinBody = crOverlay.querySelector(".cr-window-body");
    crWinAddr = crOverlay.querySelector(".cr-window-addr-text");
    crWinTitle = crOverlay.querySelector(".cr-window-title");

    crOverlay
      .querySelector(".cr-window-dots i:first-child")
      .addEventListener("click", crClose);
    crOverlay.addEventListener("click", function (e) {
      if (e.target === crOverlay) crClose();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && crOverlay.classList.contains("is-open"))
        crClose();
    });
  }

  function crClose() {
    crOverlay.classList.remove("is-open");
    setTimeout(function () {
      crWinBody.innerHTML = "";
      if (crCurrentBlob) {
        URL.revokeObjectURL(crCurrentBlob);
        crCurrentBlob = null;
      }
    }, 300);
  }

  function laredCodeRunnerOpen(htmlCode, title, height) {
    crEnsureWindow();

    /* 构建完整 HTML 文档 */
    var doc = htmlCode;
    /* 如果内容不是完整文档（没有 <html 或 <!DOCTYPE），包裹一下 */
    if (!/<!doctype|<html/i.test(htmlCode)) {
      doc =
        '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
        '<meta name="viewport" content="width=device-width,initial-scale=1.0">' +
        "<style>*{margin:0;padding:0;box-sizing:border-box}" +
        'body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;padding:20px;background:#fff}</style>' +
        "</head><body>" +
        htmlCode +
        "</body></html>";
    }

    if (crCurrentBlob) {
      URL.revokeObjectURL(crCurrentBlob);
      crCurrentBlob = null;
    }
    var blob = new Blob([doc], { type: "text/html;charset=utf-8" });
    crCurrentBlob = URL.createObjectURL(blob);

    var iframe = document.createElement("iframe");
    iframe.src = crCurrentBlob;
    iframe.sandbox = "allow-scripts";
    iframe.style.cssText = "width:100%;border:none;background:#fff;height:100%";

    crWinBody.innerHTML = "";
    crWinBody.appendChild(iframe);
    crWinAddr.textContent = "code-runner://localhost/" + (title || "preview");
    crWinTitle.textContent = title || "代码预览";
    crOverlay.classList.add("is-open");
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
  var _lrcCache = {}; // url → [{time, text}]
  var _musicLyrics = null; // current parsed lyrics array
  var _musicLyricIdx = -1; // current active lyric line index
  var _lyricsPanel = null; // side panel DOM (inner pages)
  var _homeLyricsEl = null; // home inline lyrics DOM
  var _musicSideAnchorBound = false;

  function _syncMusicSideAnchors() {
    var mainShell =
      document.querySelector(".single-page-square.main-shell") ||
      document.querySelector(".main-shell");
    var floatEl = document.getElementById("lared-music-float");
    var lyricsEl = document.getElementById("lared-lyrics-panel");

    if (!mainShell) {
      return;
    }

    var viewportWidth =
      window.innerWidth || document.documentElement.clientWidth || 0;
    if (viewportWidth <= 900) {
      if (floatEl) {
        floatEl.style.left = "";
        floatEl.style.right = "";
        floatEl.style.position = "";
      }
      if (lyricsEl) {
        lyricsEl.style.left = "";
        lyricsEl.style.right = "";
        lyricsEl.style.position = "";
      }
      return;
    }

    var shellRect = mainShell.getBoundingClientRect();
    var leftBorderX = Math.round(shellRect.left);
    var sideWidth = 160;
    if (floatEl) {
      sideWidth =
        floatEl.offsetWidth ||
        parseFloat(window.getComputedStyle(floatEl).width) ||
        sideWidth;
    } else if (lyricsEl) {
      sideWidth =
        lyricsEl.offsetWidth ||
        parseFloat(window.getComputedStyle(lyricsEl).width) ||
        sideWidth;
    }
    var anchoredLeft = leftBorderX - sideWidth + "px";

    if (floatEl) {
      floatEl.style.position = "fixed";
      floatEl.style.right = "auto";
      floatEl.style.left = anchoredLeft;
    }

    if (lyricsEl) {
      lyricsEl.style.position = "fixed";
      lyricsEl.style.right = "auto";
      lyricsEl.style.left = anchoredLeft;
    }
  }

  function initMusicPlayer() {
    var el = document.getElementById("lared-music-player");
    var floatEl = document.getElementById("lared-music-float");

    // 至少需要一个播放器元素
    var sourceEl = el || floatEl;
    if (!sourceEl) return;

    var rawTracks = sourceEl.getAttribute("data-tracks");
    if (!rawTracks) return;

    try {
      var tracks = JSON.parse(rawTracks);
      if (!Array.isArray(tracks) || tracks.length === 0) return;
      _musicTracks = tracks;
    } catch (e) {
      return;
    }

    // 切换浮动播放器可见性
    if (floatEl) {
      var floatVisible = floatEl.getAttribute("data-float-visible") !== "0";
      if (el) {
        // 首页有内联播放器，隐藏浮动
        floatEl.classList.remove("is-active");
      } else if (floatVisible && _musicPlaying) {
        // 非首页，后台开启 + 正在播放：显示
        floatEl.classList.add("is-active");
      } else {
        // 后台关闭 或 未播放/暂停
        floatEl.classList.remove("is-active");
      }
    }

    _syncMusicSideAnchors();
    if (!_musicSideAnchorBound) {
      _musicSideAnchorBound = true;
      window.addEventListener("resize", _syncMusicSideAnchors, {
        passive: true,
      });
      if (window.visualViewport) {
        window.visualViewport.addEventListener(
          "resize",
          _syncMusicSideAnchors,
          {
            passive: true,
          },
        );
        window.visualViewport.addEventListener(
          "scroll",
          _syncMusicSideAnchors,
          {
            passive: true,
          },
        );
      }
      document.addEventListener("pjax:complete", function () {
        window.setTimeout(_syncMusicSideAnchors, 0);
      });
    }

    // 已有 Audio，只同步 UI
    if (_musicAudio) {
      if (el) {
        _syncMusicUI(el);
        _bindMusicEvents(el);
        _bindMusicContext(el);
        _bindMusicProgress(el);
      }
      if (floatEl) {
        _syncMusicUI(floatEl);
        _bindMusicEvents(floatEl);
        _bindMusicContext(floatEl);
        _bindMusicProgress(floatEl);
      }
      // PJAX 切回时恢复歌词 UI
      if (_musicPlaying && _musicLyrics) {
        _showLyricsUI(_musicLyrics);
        // 首页时隐藏侧边歌词面板（内页残留）
        if (el && _lyricsPanel) {
          _lyricsPanel.classList.remove("is-visible");
        }
      }
      return;
    }

    _musicAudio = new Audio();
    _musicAudio.preload = "auto";
    _musicIndex = 0;
    _musicAudio.src = _musicTracks[0].url;

    _musicAudio.addEventListener("ended", function () {
      _musicNext();
    });

    // 浏览器/系统级暂停（如切标签页、耳机拔出等）→ 同步歌词
    _musicAudio.addEventListener("pause", function () {
      _musicPlaying = false;
      _hideLyricsUI();
      _syncAllMusicUI();
    });
    _musicAudio.addEventListener("play", function () {
      _musicPlaying = true;
      if (_musicLyrics) {
        _showLyricsUI(_musicLyrics);
      }
      _syncAllMusicUI();
    });

    // 实时更新播放时间 + 歌词 + 进度
    _musicAudio.addEventListener("timeupdate", function () {
      var el = document.getElementById("lared-music-player");
      var floatEl = document.getElementById("lared-music-float");
      if (el) {
        _updateMusicTime(el);
        _updateMusicProgress(el);
      }
      if (floatEl) {
        _updateMusicTime(floatEl);
        _updateMusicProgress(floatEl);
      }
      _updateLyrics();
    });

    // 首次加载歌词
    _loadCurrentLyrics();

    if (el) {
      _syncMusicUI(el);
      _bindMusicEvents(el);
      _bindMusicContext(el);
      _bindMusicProgress(el);
    }
    if (floatEl) {
      _syncMusicUI(floatEl);
      _bindMusicEvents(floatEl);
      _bindMusicContext(floatEl);
      _bindMusicProgress(floatEl);
    }
  }

  function _syncMusicUI(el) {
    if (!el) return;
    var nameEl = el.querySelector('[data-music="name"]');
    var toggleBtn = el.querySelector('[data-music="toggle"]');

    // 切换 player 级 is-playing 类（控制 controls/viz 可见性）
    el.classList.toggle("is-playing", _musicPlaying);

    // 歌名
    if (nameEl) {
      var trackName = _musicTracks[_musicIndex].name;
      var isFloat = el.id === "lared-music-float";

      if (isFloat) {
        // 浮动播放器：marquee 滚动逻辑
        var textSpan = nameEl.querySelector(".lared-music-track-text");
        if (!textSpan) {
          textSpan = document.createElement("span");
          textSpan.className = "lared-music-track-text";
          nameEl.textContent = "";
          nameEl.appendChild(textSpan);
        }
        if (textSpan.getAttribute("data-raw") !== trackName) {
          textSpan.setAttribute("data-raw", trackName);
          textSpan.textContent = trackName;
          nameEl.classList.remove("is-overflow");
          var dup = textSpan.querySelector(".lared-marquee-dup");
          if (dup) dup.remove();
          setTimeout(function () {
            _checkMarquee(nameEl, textSpan, trackName);
          }, 60);
        }
      } else {
        // 首页播放器：marquee 逻辑
        var textSpan = nameEl.querySelector(".lared-music-track-text");
        if (!textSpan) {
          textSpan = document.createElement("span");
          textSpan.className = "lared-music-track-text";
          nameEl.textContent = "";
          nameEl.appendChild(textSpan);
        }
        if (textSpan.getAttribute("data-raw") !== trackName) {
          textSpan.setAttribute("data-raw", trackName);
          textSpan.textContent = trackName;
          nameEl.classList.remove("is-overflow");
          var dup = textSpan.querySelector(".lared-marquee-dup");
          if (dup) dup.remove();
          setTimeout(function () {
            _checkMarquee(nameEl, textSpan, trackName);
          }, 60);
        }
        nameEl.classList.toggle("is-playing", _musicPlaying);
      }
    }

    // toggle 按钮图标
    if (toggleBtn) {
      var icon = toggleBtn.querySelector("i");
      toggleBtn.classList.toggle("is-playing", _musicPlaying);
      if (icon) {
        icon.className = _musicPlaying
          ? "fa-solid fa-pause"
          : "fa-solid fa-play";
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
      var dup = document.createElement("span");
      dup.className = "lared-marquee-dup";
      dup.textContent = trackName;
      dup.style.paddingLeft = "4em";
      textSpan.appendChild(dup);
      // 根据文本长度计算动画时长
      var dur = Math.max(6, textSpan.scrollWidth / 30);
      nameEl.style.setProperty("--marquee-dur", dur + "s");
      nameEl.classList.add("is-overflow");
    } else {
      nameEl.classList.remove("is-overflow");
    }
  }

  function _formatTime(sec) {
    if (!sec || !isFinite(sec)) return "0:00";
    var m = Math.floor(sec / 60);
    var s = Math.floor(sec % 60);
    return m + ":" + (s < 10 ? "0" : "") + s;
  }

  function _updateMusicTime(el) {
    if (!el) return;
    // Home player: single [data-music="time"]
    var timeEl = el.querySelector('[data-music="time"]');
    if (timeEl) {
      if (_musicAudio && _musicPlaying) {
        timeEl.textContent = _formatTime(_musicAudio.currentTime);
      } else {
        timeEl.textContent = "0:00";
      }
    }
    // Float player: separate current / duration
    var curEl = el.querySelector('[data-music="time-current"]');
    var durEl = el.querySelector('[data-music="time-duration"]');
    if (curEl) {
      curEl.textContent = _musicAudio
        ? _formatTime(_musicAudio.currentTime)
        : "0:00";
    }
    if (durEl) {
      durEl.textContent =
        _musicAudio && _musicAudio.duration && isFinite(_musicAudio.duration)
          ? _formatTime(_musicAudio.duration)
          : "0:00";
    }
  }

  /* --- Progress bar update --- */
  function _updateMusicProgress(el) {
    if (!el) return;
    var fillEl = el.querySelector('[data-music="progress-fill"]');
    var dotEl = el.querySelector('[data-music="progress-dot"]');
    if (!fillEl && !dotEl) return;

    var pct = 0;
    if (
      _musicAudio &&
      _musicAudio.duration &&
      isFinite(_musicAudio.duration) &&
      _musicAudio.duration > 0
    ) {
      pct = (_musicAudio.currentTime / _musicAudio.duration) * 100;
    }
    if (fillEl) fillEl.style.width = pct + "%";
    if (dotEl) dotEl.style.left = pct + "%";
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
      progressEl.addEventListener("mousemove", function (e) {
        if (
          !_musicAudio ||
          !_musicAudio.duration ||
          !isFinite(_musicAudio.duration)
        ) {
          tipEl.style.opacity = "0";
          return;
        }
        var rect = progressEl.getBoundingClientRect();
        var x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
        var ratio = x / rect.width;
        var t = ratio * _musicAudio.duration;
        tipEl.textContent = _formatTime(t);
        tipEl.style.left = x + "px";
        tipEl.style.opacity = "1";
      });
      progressEl.addEventListener("mouseleave", function () {
        tipEl.style.opacity = "0";
      });
    }

    function seekToX(clientX) {
      if (
        !_musicAudio ||
        !_musicAudio.duration ||
        !isFinite(_musicAudio.duration)
      )
        return;
      var rect = progressEl.getBoundingClientRect();
      var x = Math.max(0, Math.min(clientX - rect.left, rect.width));
      var ratio = x / rect.width;
      _musicAudio.currentTime = ratio * _musicAudio.duration;
      _syncAllMusicUI();
    }

    // Click on progress bar
    progressEl.addEventListener("mousedown", function (e) {
      e.preventDefault();
      e.stopPropagation();
      dragging = true;
      if (dotEl) dotEl.classList.add("is-dragging");
      seekToX(e.clientX);
    });

    document.addEventListener("mousemove", function (e) {
      if (!dragging) return;
      e.preventDefault();
      seekToX(e.clientX);
    });

    document.addEventListener("mouseup", function () {
      if (!dragging) return;
      dragging = false;
      if (dotEl) dotEl.classList.remove("is-dragging");
    });

    // Touch support
    progressEl.addEventListener(
      "touchstart",
      function (e) {
        e.preventDefault();
        e.stopPropagation();
        dragging = true;
        if (dotEl) dotEl.classList.add("is-dragging");
        seekToX(e.touches[0].clientX);
      },
      { passive: false },
    );

    document.addEventListener(
      "touchmove",
      function (e) {
        if (!dragging) return;
        seekToX(e.touches[0].clientX);
      },
      { passive: true },
    );

    document.addEventListener("touchend", function () {
      if (!dragging) return;
      dragging = false;
      if (dotEl) dotEl.classList.remove("is-dragging");
    });
  }

  function _bindMusicEvents(el) {
    // 防止重复绑定
    if (el._musicBound) return;
    el._musicBound = true;

    // 阻止整个 player 区域的链接跳转
    el.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      // 控制按钮
      var btn = e.target.closest("[data-music]");
      if (!btn) return;

      var action = btn.getAttribute("data-music");
      if (action === "toggle") {
        _musicToggle();
      } else if (action === "prev") {
        _musicPrev();
      } else if (action === "next") {
        _musicNext();
      }
      _syncAllMusicUI();
    });

    // 阻止 player 区域的链接点击冒泡
    el.addEventListener("mousedown", function (e) {
      e.stopPropagation();
    });
  }

  function _musicToggle() {
    if (!_musicAudio) return;
    if (_musicPlaying) {
      _musicAudio.pause();
      // pause 事件监听器会自动同步 _musicPlaying / lyrics / UI
    } else {
      _musicAudio.play().catch(function () {});
      // play 事件监听器会自动同步 _musicPlaying / lyrics / UI
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
    var el = document.getElementById("lared-music-player");
    var floatEl = document.getElementById("lared-music-float");
    if (el) _syncMusicUI(el);
    if (floatEl) {
      _syncMusicUI(floatEl);
      // 非首页：后台开关 + 播放状态共同决定
      if (!el) {
        var floatVisible = floatEl.getAttribute("data-float-visible") !== "0";
        if (floatVisible && _musicPlaying) {
          floatEl.classList.add("is-active");
        } else {
          floatEl.classList.remove("is-active");
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

    el.addEventListener("contextmenu", function (e) {
      e.preventDefault();
      e.stopPropagation();
      _showMusicCtx(e.clientX, e.clientY);
    });
  }

  function _createMusicCtx() {
    if (_musicCtx) {
      _musicCtx.remove();
    }
    var ctx = document.createElement("div");
    ctx.className = "lared-music-ctx";
    ctx.innerHTML = '<div class="lared-music-ctx-title">播放列表</div>';

    _musicTracks.forEach(function (track, i) {
      var item = document.createElement("div");
      item.className =
        "lared-music-ctx-item" + (i === _musicIndex ? " is-current" : "");
      item.innerHTML =
        '<span class="ctx-icon">' +
        (i === _musicIndex && _musicPlaying
          ? '<i class="fa-solid fa-volume-high"></i>'
          : i + 1) +
        "</span>" +
        _escHtml(track.name);
      item.addEventListener("click", function (e) {
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
    ctx.style.left = Math.min(x, window.innerWidth - 200) + "px";
    ctx.style.top = Math.min(y, window.innerHeight - 340) + "px";
    ctx.classList.add("is-open");

    // 点击外部关闭
    setTimeout(function () {
      document.addEventListener("click", _hideMusicCtx, { once: true });
    }, 0);
  }

  function _hideMusicCtx() {
    if (_musicCtx) {
      _musicCtx.classList.remove("is-open");
      setTimeout(function () {
        if (_musicCtx) {
          _musicCtx.remove();
          _musicCtx = null;
        }
      }, 120);
    }
  }

  function _escHtml(s) {
    var d = document.createElement("span");
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
      var text = line.replace(/\[\d{1,3}:\d{2}(?:[.:]\d+)?\]/g, "").trim();
      if (text === "" && timestamps.length === 0) continue;
      for (var t = 0; t < timestamps.length; t++) {
        result.push({ time: timestamps[t], text: text });
      }
    }
    result.sort(function (a, b) {
      return a.time - b.time;
    });
    return result;
  }

  /**
   * Fetch & cache LRC file, then call cb(lyrics)
   */
  function _fetchLRC(url, cb) {
    if (!url) {
      cb(null);
      return;
    }
    if (_lrcCache[url]) {
      cb(_lrcCache[url]);
      return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
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
    xhr.onerror = function () {
      cb(null);
    };
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
    if (_homeLyricsEl && document.body.contains(_homeLyricsEl))
      return _homeLyricsEl;
    var link = document.querySelector(".home-memo-strip-link");
    if (!link) {
      _homeLyricsEl = null;
      return null;
    }
    var el = link.querySelector(".home-memo-strip-lyrics");
    if (!el) {
      el = document.createElement("span");
      el.className = "home-memo-strip-lyrics";
      // Insert after bird-track, before/alongside memo-strip-main
      var main = link.querySelector(".home-memo-strip-main");
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
    var link = el.closest(".home-memo-strip-link");

    if (!lyrics || idx < 0 || !_musicPlaying) {
      el.classList.remove("is-visible");
      el.innerHTML = "";
      if (link) link.classList.remove("has-lyrics");
      return;
    }

    if (link) link.classList.add("has-lyrics");
    el.classList.add("is-visible");

    // Check if we already have this idx Active
    var activeLine = el.querySelector(".lared-lyric-line.is-active");
    if (
      activeLine &&
      activeLine.getAttribute("data-lyric-idx") === String(idx)
    ) {
      return; // no change
    }

    // Deactivate old
    if (activeLine) {
      activeLine.classList.remove("is-active");
    }

    // Create or reuse line element
    var lineEl = el.querySelector(
      '.lared-lyric-line[data-lyric-idx="' + idx + '"]',
    );
    if (!lineEl) {
      // Remove all old lines to keep DOM clean
      el.innerHTML = "";
      lineEl = document.createElement("span");
      lineEl.className = "lared-lyric-line";
      lineEl.setAttribute("data-lyric-idx", idx);
      lineEl.textContent = lyrics[idx].text || "♪";
      el.appendChild(lineEl);
    }

    // Force reflow then activate
    void lineEl.offsetWidth;
    lineEl.classList.add("is-active");
  }

  /* ── Side lyrics panel (inner pages) ── */

  function _ensureLyricsPanel() {
    if (_lyricsPanel && document.body.contains(_lyricsPanel))
      return _lyricsPanel;
    var panel = document.getElementById("lared-lyrics-panel");
    if (!panel) {
      panel = document.createElement("div");
      panel.className = "lared-lyrics-panel";
      panel.id = "lared-lyrics-panel";
      document.body.appendChild(panel);

      // Click on lyric line → seek
      panel.addEventListener("click", function (e) {
        var item = e.target.closest(".lared-lyrics-panel__item");
        if (!item || !_musicAudio || !_musicLyrics) return;
        var t = parseFloat(item.getAttribute("data-time"));
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
    _syncMusicSideAnchors();
    return panel;
  }

  function _populateLyricsPanel(lyrics) {
    var panel = _ensureLyricsPanel();
    panel.innerHTML = "";
    if (!lyrics) return;
    for (var i = 0; i < lyrics.length; i++) {
      var item = document.createElement("div");
      item.className = "lared-lyrics-panel__item";
      item.setAttribute("data-time", lyrics[i].time);
      item.setAttribute("data-lyric-panel-idx", i);
      item.textContent = lyrics[i].text || "♪";
      panel.appendChild(item);
    }
  }

  function _updateLyricsPanel(idx) {
    if (!_lyricsPanel) return;
    var prev = _lyricsPanel.querySelector(
      ".lared-lyrics-panel__item.is-active",
    );
    if (prev) prev.classList.remove("is-active");
    if (idx < 0) return;
    var cur = _lyricsPanel.querySelector(
      '[data-lyric-panel-idx="' + idx + '"]',
    );
    if (!cur) return;
    cur.classList.add("is-active");
    // Auto-scroll to keep active centered
    var panelH = _lyricsPanel.clientHeight;
    var itemTop = cur.offsetTop;
    var itemH = cur.offsetHeight;
    _lyricsPanel.scrollTo({
      top: itemTop - panelH / 2 + itemH / 2,
      behavior: "smooth",
    });
  }

  /* ── Show / Hide lyrics UI ── */

  function _showLyricsUI(lyrics) {
    // 必须正在播放且音频未暂停
    if (!_musicPlaying || (_musicAudio && _musicAudio.paused)) {
      _hideLyricsUI();
      return;
    }

    // Home inline
    _updateHomeLyrics(lyrics, _musicLyricIdx);

    // Side panel (inner pages: no home player means we're on an inner page)
    var homeEl = document.getElementById("lared-music-player");
    var floatEl = document.getElementById("lared-music-float");
    // 后台设置关闭 → 不显示歌词
    if (
      !homeEl &&
      floatEl &&
      floatEl.getAttribute("data-float-visible") !== "0"
    ) {
      _populateLyricsPanel(lyrics);
      _ensureLyricsPanel().classList.add("is-visible");
    }
  }

  function _hideLyricsUI() {
    // Home
    var el = _homeLyricsEl;
    if (el) {
      el.classList.remove("is-visible");
      el.innerHTML = "";
      var link = el.closest(".home-memo-strip-link");
      if (link) link.classList.remove("has-lyrics");
    }
    // Side panel
    if (_lyricsPanel) {
      _lyricsPanel.classList.remove("is-visible");
    }
  }

  /**
   * Called on every timeupdate — updates active lyric index & UI
   */
  function _updateLyrics() {
    if (!_musicLyrics || !_musicPlaying || !_musicAudio || _musicAudio.paused) {
      if (_musicAudio && _musicAudio.paused) _hideLyricsUI();
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

  /* ── 注销确认 Dialog ── */
  (function () {
    /* 防止脚本被多次执行时重复绑定事件（PJAX 场景下可能出现） */
    if (window.__laredLogoutDialogInit) return;
    window.__laredLogoutDialogInit = true;

    var backdrop = null;
    var pendingUrl = "";

    function ensureDialog() {
      if (backdrop) return backdrop;
      /* 复用已有 DOM 元素（防止多次创建） */
      var existing = document.querySelector(".logout-confirm-overlay");
      if (existing) { backdrop = existing; return backdrop; }

      backdrop = document.createElement("div");
      backdrop.className = "logout-confirm-overlay";
      backdrop.innerHTML =
        '<div class="logout-confirm-box">' +
          '<div class="logout-confirm-body">' +
            '<div class="logout-confirm-icon"><i class="fa-solid fa-right-from-bracket"></i></div>' +
            '<div class="logout-confirm-title">确认退出登录？</div>' +
            '<div class="logout-confirm-desc">退出后需要重新登录才能发表评论</div>' +
          "</div>" +
          '<div class="logout-confirm-actions">' +
            '<button class="logout-confirm-btn logout-confirm-btn--cancel" data-dialog-cancel>取消</button>' +
            '<button class="logout-confirm-btn logout-confirm-btn--confirm" data-dialog-confirm>确认退出</button>' +
          "</div>" +
        "</div>";
      document.body.appendChild(backdrop);

      backdrop.querySelector("[data-dialog-cancel]").addEventListener("click", closeDialog);
      backdrop.querySelector("[data-dialog-confirm]").addEventListener("click", function () {
        if (pendingUrl) window.location.href = pendingUrl;
      });
      backdrop.addEventListener("click", function (e) {
        if (e.target === backdrop) closeDialog();
      });

      return backdrop;
    }

    function closeDialog() {
      if (backdrop) backdrop.classList.remove("is-open");
      pendingUrl = "";
    }

    document.addEventListener("click", function (e) {
      var link = e.target.closest(".footer-avatar-menu-logout, .lared-meta-logout");
      if (!link) return;
      e.preventDefault();
      e.stopPropagation();
      pendingUrl = link.href || link.getAttribute("href");
      ensureDialog().classList.add("is-open");
    }, true);

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && backdrop && backdrop.classList.contains("is-open")) {
        closeDialog();
      }
    });
  })();
  /* ── /注销确认 Dialog ── */

  document.addEventListener("DOMContentLoaded", init);
  document.addEventListener("DOMContentLoaded", initMemosPublish);
  document.addEventListener("DOMContentLoaded", initMemosFilter);
})();
