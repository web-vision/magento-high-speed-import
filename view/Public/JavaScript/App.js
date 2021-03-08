/*! SimpleGruntTask 2017-11-30 */

(function(t, e) {
    "object" == typeof exports && "undefined" != typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define(e) : t.Popper = e();
})(this, function() {
    "use strict";
    function t(t) {
        return t && "[object Function]" === {}.toString.call(t);
    }
    function e(t, e) {
        if (1 !== t.nodeType) return [];
        var n = window.getComputedStyle(t, null);
        return e ? n[e] : n;
    }
    function n(t) {
        return "HTML" === t.nodeName ? t : t.parentNode || t.host;
    }
    function i(t) {
        if (!t || -1 !== [ "HTML", "BODY", "#document" ].indexOf(t.nodeName)) return window.document.body;
        var r = e(t), o = r.overflow, s = r.overflowX, a = r.overflowY;
        return /(auto|scroll)/.test(o + a + s) ? t : i(n(t));
    }
    function r(t) {
        var n = t && t.offsetParent, i = n && n.nodeName;
        return i && "BODY" !== i && "HTML" !== i ? -1 !== [ "TD", "TABLE" ].indexOf(n.nodeName) && "static" === e(n, "position") ? r(n) : n : window.document.documentElement;
    }
    function o(t) {
        var e = t.nodeName;
        return "BODY" !== e && ("HTML" === e || r(t.firstElementChild) === t);
    }
    function s(t) {
        return null === t.parentNode ? t : s(t.parentNode);
    }
    function a(t, e) {
        if (!t || !t.nodeType || !e || !e.nodeType) return window.document.documentElement;
        var n = t.compareDocumentPosition(e) & Node.DOCUMENT_POSITION_FOLLOWING, i = n ? t : e, l = n ? e : t, f = document.createRange();
        f.setStart(i, 0), f.setEnd(l, 0);
        var c = f.commonAncestorContainer;
        if (t !== c && e !== c || i.contains(l)) return o(c) ? c : r(c);
        var u = s(t);
        return u.host ? a(u.host, e) : a(t, s(e).host);
    }
    function l(t) {
        var e = 1 < arguments.length && void 0 !== arguments[1] ? arguments[1] : "top", n = "top" === e ? "scrollTop" : "scrollLeft", i = t.nodeName;
        if ("BODY" === i || "HTML" === i) {
            var r = window.document.documentElement, o = window.document.scrollingElement || r;
            return o[n];
        }
        return t[n];
    }
    function f(t, e) {
        var n = 2 < arguments.length && void 0 !== arguments[2] && arguments[2], i = l(e, "top"), r = l(e, "left"), o = n ? -1 : 1;
        return t.top += i * o, t.bottom += i * o, t.left += r * o, t.right += r * o, t;
    }
    function c(t, e) {
        var n = "x" === e ? "Left" : "Top", i = "Left" == n ? "Right" : "Bottom";
        return +t["border" + n + "Width"].split("px")[0] + +t["border" + i + "Width"].split("px")[0];
    }
    function u(t, e, n, i) {
        return Y(e["offset" + t], n["client" + t], n["offset" + t], it() ? n["offset" + t] + i["margin" + ("Height" === t ? "Top" : "Left")] + i["margin" + ("Height" === t ? "Bottom" : "Right")] : 0);
    }
    function h() {
        var t = window.document.body, e = window.document.documentElement, n = it() && window.getComputedStyle(e);
        return {
            height: u("Height", t, e, n),
            width: u("Width", t, e, n)
        };
    }
    function d(t) {
        return at({}, t, {
            right: t.left + t.width,
            bottom: t.top + t.height
        });
    }
    function p(t) {
        var n = {};
        if (it()) try {
            n = t.getBoundingClientRect();
            var i = l(t, "top"), r = l(t, "left");
            n.top += i, n.left += r, n.bottom += i, n.right += r;
        } catch (t) {} else n = t.getBoundingClientRect();
        var o = {
            left: n.left,
            top: n.top,
            width: n.right - n.left,
            height: n.bottom - n.top
        }, s = "HTML" === t.nodeName ? h() : {}, a = s.width || t.clientWidth || o.right - o.left, f = s.height || t.clientHeight || o.bottom - o.top, u = t.offsetWidth - a, p = t.offsetHeight - f;
        if (u || p) {
            var g = e(t);
            u -= c(g, "x"), p -= c(g, "y"), o.width -= u, o.height -= p;
        }
        return d(o);
    }
    function g(t, n) {
        var r = it(), o = "HTML" === n.nodeName, s = p(t), a = p(n), l = i(t), c = e(n), u = +c.borderTopWidth.split("px")[0], h = +c.borderLeftWidth.split("px")[0], g = d({
            top: s.top - a.top - u,
            left: s.left - a.left - h,
            width: s.width,
            height: s.height
        });
        if (g.marginTop = 0, g.marginLeft = 0, !r && o) {
            var m = +c.marginTop.split("px")[0], v = +c.marginLeft.split("px")[0];
            g.top -= u - m, g.bottom -= u - m, g.left -= h - v, g.right -= h - v, g.marginTop = m, 
            g.marginLeft = v;
        }
        return (r ? n.contains(l) : n === l && "BODY" !== l.nodeName) && (g = f(g, n)), 
        g;
    }
    function m(t) {
        var e = window.document.documentElement, n = g(t, e), i = Y(e.clientWidth, window.innerWidth || 0), r = Y(e.clientHeight, window.innerHeight || 0), o = l(e), s = l(e, "left"), a = {
            top: o - n.top + n.marginTop,
            left: s - n.left + n.marginLeft,
            width: i,
            height: r
        };
        return d(a);
    }
    function v(t) {
        var i = t.nodeName;
        return "BODY" === i || "HTML" === i ? !1 : "fixed" === e(t, "position") || v(n(t));
    }
    function _(t, e, r, o) {
        var s = {
            top: 0,
            left: 0
        }, l = a(t, e);
        if ("viewport" === o) s = m(l); else {
            var f;
            "scrollParent" === o ? (f = i(n(t)), "BODY" === f.nodeName && (f = window.document.documentElement)) : "window" === o ? f = window.document.documentElement : f = o;
            var c = g(f, l);
            if ("HTML" === f.nodeName && !v(l)) {
                var u = h(), d = u.height, p = u.width;
                s.top += c.top - c.marginTop, s.bottom = d + c.top, s.left += c.left - c.marginLeft, 
                s.right = p + c.left;
            } else s = c;
        }
        return s.left += r, s.top += r, s.right -= r, s.bottom -= r, s;
    }
    function E(t) {
        var e = t.width, n = t.height;
        return e * n;
    }
    function T(t, e, n, i, r) {
        var o = 5 < arguments.length && void 0 !== arguments[5] ? arguments[5] : 0;
        if (-1 === t.indexOf("auto")) return t;
        var s = _(n, i, o, r), a = {
            top: {
                width: s.width,
                height: e.top - s.top
            },
            right: {
                width: s.right - e.right,
                height: s.height
            },
            bottom: {
                width: s.width,
                height: s.bottom - e.bottom
            },
            left: {
                width: e.left - s.left,
                height: s.height
            }
        }, l = Object.keys(a).map(function(t) {
            return at({
                key: t
            }, a[t], {
                area: E(a[t])
            });
        }).sort(function(t, e) {
            return e.area - t.area;
        }), f = l.filter(function(t) {
            var e = t.width, i = t.height;
            return e >= n.clientWidth && i >= n.clientHeight;
        }), c = 0 < f.length ? f[0].key : l[0].key, u = t.split("-")[1];
        return c + (u ? "-" + u : "");
    }
    function y(t, e, n) {
        var i = a(e, n);
        return g(n, i);
    }
    function A(t) {
        var e = window.getComputedStyle(t), n = parseFloat(e.marginTop) + parseFloat(e.marginBottom), i = parseFloat(e.marginLeft) + parseFloat(e.marginRight), r = {
            width: t.offsetWidth + i,
            height: t.offsetHeight + n
        };
        return r;
    }
    function C(t) {
        var e = {
            left: "right",
            right: "left",
            bottom: "top",
            top: "bottom"
        };
        return t.replace(/left|right|bottom|top/g, function(t) {
            return e[t];
        });
    }
    function O(t, e, n) {
        n = n.split("-")[0];
        var i = A(t), r = {
            width: i.width,
            height: i.height
        }, o = -1 !== [ "right", "left" ].indexOf(n), s = o ? "top" : "left", a = o ? "left" : "top", l = o ? "height" : "width", f = o ? "width" : "height";
        return r[s] = e[s] + e[l] / 2 - i[l] / 2, r[a] = n === a ? e[a] - i[f] : e[C(a)], 
        r;
    }
    function b(t, e) {
        return Array.prototype.find ? t.find(e) : t.filter(e)[0];
    }
    function I(t, e, n) {
        if (Array.prototype.findIndex) return t.findIndex(function(t) {
            return t[e] === n;
        });
        var i = b(t, function(t) {
            return t[e] === n;
        });
        return t.indexOf(i);
    }
    function S(e, n, i) {
        var r = void 0 === i ? e : e.slice(0, I(e, "name", i));
        return r.forEach(function(e) {
            e.function && console.warn("`modifier.function` is deprecated, use `modifier.fn`!");
            var i = e.function || e.fn;
            e.enabled && t(i) && (n.offsets.popper = d(n.offsets.popper), n.offsets.reference = d(n.offsets.reference), 
            n = i(n, e));
        }), n;
    }
    function D() {
        if (!this.state.isDestroyed) {
            var t = {
                instance: this,
                styles: {},
                attributes: {},
                flipped: !1,
                offsets: {}
            };
            t.offsets.reference = y(this.state, this.popper, this.reference), t.placement = T(this.options.placement, t.offsets.reference, this.popper, this.reference, this.options.modifiers.flip.boundariesElement, this.options.modifiers.flip.padding), 
            t.originalPlacement = t.placement, t.offsets.popper = O(this.popper, t.offsets.reference, t.placement), 
            t.offsets.popper.position = "absolute", t = S(this.modifiers, t), this.state.isCreated ? this.options.onUpdate(t) : (this.state.isCreated = !0, 
            this.options.onCreate(t));
        }
    }
    function w(t, e) {
        return t.some(function(t) {
            var n = t.name, i = t.enabled;
            return i && n === e;
        });
    }
    function N(t) {
        for (var e = [ !1, "ms", "Webkit", "Moz", "O" ], n = t.charAt(0).toUpperCase() + t.slice(1), i = 0; i < e.length - 1; i++) {
            var r = e[i], o = r ? "" + r + n : t;
            if ("undefined" != typeof window.document.body.style[o]) return o;
        }
        return null;
    }
    function L() {
        return this.state.isDestroyed = !0, w(this.modifiers, "applyStyle") && (this.popper.removeAttribute("x-placement"), 
        this.popper.style.left = "", this.popper.style.position = "", this.popper.style.top = "", 
        this.popper.style[N("transform")] = ""), this.disableEventListeners(), this.options.removeOnDestroy && this.popper.parentNode.removeChild(this.popper), 
        this;
    }
    function P(t, e, n, r) {
        var o = "BODY" === t.nodeName, s = o ? window : t;
        s.addEventListener(e, n, {
            passive: !0
        }), o || P(i(s.parentNode), e, n, r), r.push(s);
    }
    function k(t, e, n, r) {
        n.updateBound = r, window.addEventListener("resize", n.updateBound, {
            passive: !0
        });
        var o = i(t);
        return P(o, "scroll", n.updateBound, n.scrollParents), n.scrollElement = o, n.eventsEnabled = !0, 
        n;
    }
    function R() {
        this.state.eventsEnabled || (this.state = k(this.reference, this.options, this.state, this.scheduleUpdate));
    }
    function H(t, e) {
        return window.removeEventListener("resize", e.updateBound), e.scrollParents.forEach(function(t) {
            t.removeEventListener("scroll", e.updateBound);
        }), e.updateBound = null, e.scrollParents = [], e.scrollElement = null, e.eventsEnabled = !1, 
        e;
    }
    function W() {
        this.state.eventsEnabled && (window.cancelAnimationFrame(this.scheduleUpdate), this.state = H(this.reference, this.state));
    }
    function x(t) {
        return "" !== t && !isNaN(parseFloat(t)) && isFinite(t);
    }
    function U(t, e) {
        Object.keys(e).forEach(function(n) {
            var i = "";
            -1 !== [ "width", "height", "top", "right", "bottom", "left" ].indexOf(n) && x(e[n]) && (i = "px"), 
            t.style[n] = e[n] + i;
        });
    }
    function M(t, e) {
        Object.keys(e).forEach(function(n) {
            var i = e[n];
            !1 === i ? t.removeAttribute(n) : t.setAttribute(n, e[n]);
        });
    }
    function j(t, e, n) {
        var i = b(t, function(t) {
            var n = t.name;
            return n === e;
        }), r = !!i && t.some(function(t) {
            return t.name === n && t.enabled && t.order < i.order;
        });
        if (!r) {
            var o = "`" + e + "`";
            console.warn("`" + n + "`" + " modifier is required by " + o + " modifier in order to work, be sure to include it before " + o + "!");
        }
        return r;
    }
    function V(t) {
        return "end" === t ? "start" : "start" === t ? "end" : t;
    }
    function F(t) {
        var e = 1 < arguments.length && void 0 !== arguments[1] && arguments[1], n = ft.indexOf(t), i = ft.slice(n + 1).concat(ft.slice(0, n));
        return e ? i.reverse() : i;
    }
    function B(t, e, n, i) {
        var r = t.match(/((?:\-|\+)?\d*\.?\d*)(.*)/), o = +r[1], s = r[2];
        if (!o) return t;
        if (0 === s.indexOf("%")) {
            var a;
            switch (s) {
              case "%p":
                a = n;
                break;

              case "%":
              case "%r":
              default:
                a = i;
            }
            var l = d(a);
            return l[e] / 100 * o;
        }
        if ("vh" === s || "vw" === s) {
            var f;
            return f = "vh" === s ? Y(document.documentElement.clientHeight, window.innerHeight || 0) : Y(document.documentElement.clientWidth, window.innerWidth || 0), 
            f / 100 * o;
        }
        return o;
    }
    function G(t, e, n, i) {
        var r = [ 0, 0 ], o = -1 !== [ "right", "left" ].indexOf(i), s = t.split(/(\+|\-)/).map(function(t) {
            return t.trim();
        }), a = s.indexOf(b(s, function(t) {
            return -1 !== t.search(/,|\s/);
        }));
        s[a] && -1 === s[a].indexOf(",") && console.warn("Offsets separated by white space(s) are deprecated, use a comma (,) instead.");
        var l = /\s*,\s*|\s+/, f = -1 === a ? [ s ] : [ s.slice(0, a).concat([ s[a].split(l)[0] ]), [ s[a].split(l)[1] ].concat(s.slice(a + 1)) ];
        return f = f.map(function(t, i) {
            var r = (1 === i ? !o : o) ? "height" : "width", s = !1;
            return t.reduce(function(t, e) {
                return "" === t[t.length - 1] && -1 !== [ "+", "-" ].indexOf(e) ? (t[t.length - 1] = e, 
                s = !0, t) : s ? (t[t.length - 1] += e, s = !1, t) : t.concat(e);
            }, []).map(function(t) {
                return B(t, r, e, n);
            });
        }), f.forEach(function(t, e) {
            t.forEach(function(n, i) {
                x(n) && (r[e] += n * ("-" === t[i - 1] ? -1 : 1));
            });
        }), r;
    }
    for (var K = Math.min, Q = Math.floor, Y = Math.max, $ = [ "native code", "[object MutationObserverConstructor]" ], X = function(t) {
        return $.some(function(e) {
            return -1 < (t || "").toString().indexOf(e);
        });
    }, q = "undefined" != typeof window, z = [ "Edge", "Trident", "Firefox" ], Z = 0, J = 0; J < z.length; J += 1) if (q && 0 <= navigator.userAgent.indexOf(z[J])) {
        Z = 1;
        break;
    }
    var tt, et = q && X(window.MutationObserver), nt = et ? function(t) {
        var e = !1, n = 0, i = document.createElement("span"), r = new MutationObserver(function() {
            t(), e = !1;
        });
        return r.observe(i, {
            attributes: !0
        }), function() {
            e || (e = !0, i.setAttribute("x-index", n), ++n);
        };
    } : function(t) {
        var e = !1;
        return function() {
            e || (e = !0, setTimeout(function() {
                e = !1, t();
            }, Z));
        };
    }, it = function() {
        return void 0 == tt && (tt = -1 !== navigator.appVersion.indexOf("MSIE 10")), tt;
    }, rt = function(t, e) {
        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function");
    }, ot = function() {
        function t(t, e) {
            for (var n, i = 0; i < e.length; i++) n = e[i], n.enumerable = n.enumerable || !1, 
            n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(t, n.key, n);
        }
        return function(e, n, i) {
            return n && t(e.prototype, n), i && t(e, i), e;
        };
    }(), st = function(t, e, n) {
        return e in t ? Object.defineProperty(t, e, {
            value: n,
            enumerable: !0,
            configurable: !0,
            writable: !0
        }) : t[e] = n, t;
    }, at = Object.assign || function(t) {
        for (var e, n = 1; n < arguments.length; n++) for (var i in e = arguments[n], e) Object.prototype.hasOwnProperty.call(e, i) && (t[i] = e[i]);
        return t;
    }, lt = [ "auto-start", "auto", "auto-end", "top-start", "top", "top-end", "right-start", "right", "right-end", "bottom-end", "bottom", "bottom-start", "left-end", "left", "left-start" ], ft = lt.slice(3), ct = {
        FLIP: "flip",
        CLOCKWISE: "clockwise",
        COUNTERCLOCKWISE: "counterclockwise"
    }, ut = function() {
        function e(n, i) {
            var r = this, o = 2 < arguments.length && void 0 !== arguments[2] ? arguments[2] : {};
            rt(this, e), this.scheduleUpdate = function() {
                return requestAnimationFrame(r.update);
            }, this.update = nt(this.update.bind(this)), this.options = at({}, e.Defaults, o), 
            this.state = {
                isDestroyed: !1,
                isCreated: !1,
                scrollParents: []
            }, this.reference = n.jquery ? n[0] : n, this.popper = i.jquery ? i[0] : i, this.options.modifiers = {}, 
            Object.keys(at({}, e.Defaults.modifiers, o.modifiers)).forEach(function(t) {
                r.options.modifiers[t] = at({}, e.Defaults.modifiers[t] || {}, o.modifiers ? o.modifiers[t] : {});
            }), this.modifiers = Object.keys(this.options.modifiers).map(function(t) {
                return at({
                    name: t
                }, r.options.modifiers[t]);
            }).sort(function(t, e) {
                return t.order - e.order;
            }), this.modifiers.forEach(function(e) {
                e.enabled && t(e.onLoad) && e.onLoad(r.reference, r.popper, r.options, e, r.state);
            }), this.update();
            var s = this.options.eventsEnabled;
            s && this.enableEventListeners(), this.state.eventsEnabled = s;
        }
        return ot(e, [ {
            key: "update",
            value: function() {
                return D.call(this);
            }
        }, {
            key: "destroy",
            value: function() {
                return L.call(this);
            }
        }, {
            key: "enableEventListeners",
            value: function() {
                return R.call(this);
            }
        }, {
            key: "disableEventListeners",
            value: function() {
                return W.call(this);
            }
        } ]), e;
    }();
    return ut.Utils = ("undefined" == typeof window ? global : window).PopperUtils, 
    ut.placements = lt, ut.Defaults = {
        placement: "bottom",
        eventsEnabled: !0,
        removeOnDestroy: !1,
        onCreate: function() {},
        onUpdate: function() {},
        modifiers: {
            shift: {
                order: 100,
                enabled: !0,
                fn: function(t) {
                    var e = t.placement, n = e.split("-")[0], i = e.split("-")[1];
                    if (i) {
                        var r = t.offsets, o = r.reference, s = r.popper, a = -1 !== [ "bottom", "top" ].indexOf(n), l = a ? "left" : "top", f = a ? "width" : "height", c = {
                            start: st({}, l, o[l]),
                            end: st({}, l, o[l] + o[f] - s[f])
                        };
                        t.offsets.popper = at({}, s, c[i]);
                    }
                    return t;
                }
            },
            offset: {
                order: 200,
                enabled: !0,
                fn: function(t, e) {
                    var n, i = e.offset, r = t.placement, o = t.offsets, s = o.popper, a = o.reference, l = r.split("-")[0];
                    return n = x(+i) ? [ +i, 0 ] : G(i, s, a, l), "left" === l ? (s.top += n[0], s.left -= n[1]) : "right" === l ? (s.top += n[0], 
                    s.left += n[1]) : "top" === l ? (s.left += n[0], s.top -= n[1]) : "bottom" === l && (s.left += n[0], 
                    s.top += n[1]), t.popper = s, t;
                },
                offset: 0
            },
            preventOverflow: {
                order: 300,
                enabled: !0,
                fn: function(t, e) {
                    var n = e.boundariesElement || r(t.instance.popper);
                    t.instance.reference === n && (n = r(n));
                    var i = _(t.instance.popper, t.instance.reference, e.padding, n);
                    e.boundaries = i;
                    var o = e.priority, s = t.offsets.popper, a = {
                        primary: function(t) {
                            var n = s[t];
                            return s[t] < i[t] && !e.escapeWithReference && (n = Y(s[t], i[t])), st({}, t, n);
                        },
                        secondary: function(t) {
                            var n = "right" === t ? "left" : "top", r = s[n];
                            return s[t] > i[t] && !e.escapeWithReference && (r = K(s[n], i[t] - ("right" === t ? s.width : s.height))), 
                            st({}, n, r);
                        }
                    };
                    return o.forEach(function(t) {
                        var e = -1 === [ "left", "top" ].indexOf(t) ? "secondary" : "primary";
                        s = at({}, s, a[e](t));
                    }), t.offsets.popper = s, t;
                },
                priority: [ "left", "right", "top", "bottom" ],
                padding: 5,
                boundariesElement: "scrollParent"
            },
            keepTogether: {
                order: 400,
                enabled: !0,
                fn: function(t) {
                    var e = t.offsets, n = e.popper, i = e.reference, r = t.placement.split("-")[0], o = Q, s = -1 !== [ "top", "bottom" ].indexOf(r), a = s ? "right" : "bottom", l = s ? "left" : "top", f = s ? "width" : "height";
                    return n[a] < o(i[l]) && (t.offsets.popper[l] = o(i[l]) - n[f]), n[l] > o(i[a]) && (t.offsets.popper[l] = o(i[a])), 
                    t;
                }
            },
            arrow: {
                order: 500,
                enabled: !0,
                fn: function(t, e) {
                    if (!j(t.instance.modifiers, "arrow", "keepTogether")) return t;
                    var n = e.element;
                    if ("string" == typeof n) {
                        if (n = t.instance.popper.querySelector(n), !n) return t;
                    } else if (!t.instance.popper.contains(n)) return console.warn("WARNING: `arrow.element` must be child of its popper element!"), 
                    t;
                    var i = t.placement.split("-")[0], r = t.offsets, o = r.popper, s = r.reference, a = -1 !== [ "left", "right" ].indexOf(i), l = a ? "height" : "width", f = a ? "top" : "left", c = a ? "left" : "top", u = a ? "bottom" : "right", h = A(n)[l];
                    s[u] - h < o[f] && (t.offsets.popper[f] -= o[f] - (s[u] - h)), s[f] + h > o[u] && (t.offsets.popper[f] += s[f] + h - o[u]);
                    var p = s[f] + s[l] / 2 - h / 2, g = p - d(t.offsets.popper)[f];
                    return g = Y(K(o[l] - h, g), 0), t.arrowElement = n, t.offsets.arrow = {}, t.offsets.arrow[f] = Math.round(g), 
                    t.offsets.arrow[c] = "", t;
                },
                element: "[x-arrow]"
            },
            flip: {
                order: 600,
                enabled: !0,
                fn: function(t, e) {
                    if (w(t.instance.modifiers, "inner")) return t;
                    if (t.flipped && t.placement === t.originalPlacement) return t;
                    var n = _(t.instance.popper, t.instance.reference, e.padding, e.boundariesElement), i = t.placement.split("-")[0], r = C(i), o = t.placement.split("-")[1] || "", s = [];
                    switch (e.behavior) {
                      case ct.FLIP:
                        s = [ i, r ];
                        break;

                      case ct.CLOCKWISE:
                        s = F(i);
                        break;

                      case ct.COUNTERCLOCKWISE:
                        s = F(i, !0);
                        break;

                      default:
                        s = e.behavior;
                    }
                    return s.forEach(function(a, l) {
                        if (i !== a || s.length === l + 1) return t;
                        i = t.placement.split("-")[0], r = C(i);
                        var f = t.offsets.popper, c = t.offsets.reference, u = Q, h = "left" === i && u(f.right) > u(c.left) || "right" === i && u(f.left) < u(c.right) || "top" === i && u(f.bottom) > u(c.top) || "bottom" === i && u(f.top) < u(c.bottom), d = u(f.left) < u(n.left), p = u(f.right) > u(n.right), g = u(f.top) < u(n.top), m = u(f.bottom) > u(n.bottom), v = "left" === i && d || "right" === i && p || "top" === i && g || "bottom" === i && m, _ = -1 !== [ "top", "bottom" ].indexOf(i), E = !!e.flipVariations && (_ && "start" === o && d || _ && "end" === o && p || !_ && "start" === o && g || !_ && "end" === o && m);
                        (h || v || E) && (t.flipped = !0, (h || v) && (i = s[l + 1]), E && (o = V(o)), t.placement = i + (o ? "-" + o : ""), 
                        t.offsets.popper = at({}, t.offsets.popper, O(t.instance.popper, t.offsets.reference, t.placement)), 
                        t = S(t.instance.modifiers, t, "flip"));
                    }), t;
                },
                behavior: "flip",
                padding: 5,
                boundariesElement: "viewport"
            },
            inner: {
                order: 700,
                enabled: !1,
                fn: function(t) {
                    var e = t.placement, n = e.split("-")[0], i = t.offsets, r = i.popper, o = i.reference, s = -1 !== [ "left", "right" ].indexOf(n), a = -1 === [ "top", "left" ].indexOf(n);
                    return r[s ? "left" : "top"] = o[e] - (a ? r[s ? "width" : "height"] : 0), t.placement = C(e), 
                    t.offsets.popper = d(r), t;
                }
            },
            hide: {
                order: 800,
                enabled: !0,
                fn: function(t) {
                    if (!j(t.instance.modifiers, "hide", "preventOverflow")) return t;
                    var e = t.offsets.reference, n = b(t.instance.modifiers, function(t) {
                        return "preventOverflow" === t.name;
                    }).boundaries;
                    if (e.bottom < n.top || e.left > n.right || e.top > n.bottom || e.right < n.left) {
                        if (!0 === t.hide) return t;
                        t.hide = !0, t.attributes["x-out-of-boundaries"] = "";
                    } else {
                        if (!1 === t.hide) return t;
                        t.hide = !1, t.attributes["x-out-of-boundaries"] = !1;
                    }
                    return t;
                }
            },
            computeStyle: {
                order: 850,
                enabled: !0,
                fn: function(t, e) {
                    var n = e.x, i = e.y, o = t.offsets.popper, s = b(t.instance.modifiers, function(t) {
                        return "applyStyle" === t.name;
                    }).gpuAcceleration;
                    void 0 !== s && console.warn("WARNING: `gpuAcceleration` option moved to `computeStyle` modifier and will not be supported in future versions of Popper.js!");
                    var a, l, f = void 0 === s ? e.gpuAcceleration : s, c = r(t.instance.popper), u = p(c), h = {
                        position: o.position
                    }, d = {
                        left: Q(o.left),
                        top: Q(o.top),
                        bottom: Q(o.bottom),
                        right: Q(o.right)
                    }, g = "bottom" === n ? "top" : "bottom", m = "right" === i ? "left" : "right", v = N("transform");
                    if (l = "bottom" == g ? -u.height + d.bottom : d.top, a = "right" == m ? -u.width + d.right : d.left, 
                    f && v) h[v] = "translate3d(" + a + "px, " + l + "px, 0)", h[g] = 0, h[m] = 0, h.willChange = "transform"; else {
                        var _ = "bottom" == g ? -1 : 1, E = "right" == m ? -1 : 1;
                        h[g] = l * _, h[m] = a * E, h.willChange = g + ", " + m;
                    }
                    var T = {
                        "x-placement": t.placement
                    };
                    return t.attributes = at({}, T, t.attributes), t.styles = at({}, h, t.styles), t;
                },
                gpuAcceleration: !0,
                x: "bottom",
                y: "right"
            },
            applyStyle: {
                order: 900,
                enabled: !0,
                fn: function(t) {
                    return U(t.instance.popper, t.styles), M(t.instance.popper, t.attributes), t.offsets.arrow && U(t.arrowElement, t.offsets.arrow), 
                    t;
                },
                onLoad: function(t, e, n, i, r) {
                    var o = y(r, e, t), s = T(n.placement, o, e, t, n.modifiers.flip.boundariesElement, n.modifiers.flip.padding);
                    return e.setAttribute("x-placement", s), U(e, {
                        position: "absolute"
                    }), n;
                },
                gpuAcceleration: void 0
            }
        }
    }, ut;
});

if (typeof jQuery === "undefined") {
    throw new Error("Bootstrap's JavaScript requires jQuery. jQuery must be included before Bootstrap's JavaScript.");
}

(function(t) {
    var e = t.fn.jquery.split(" ")[0].split(".");
    if (e[0] < 2 && e[1] < 9 || e[0] == 1 && e[1] == 9 && e[2] < 1 || e[0] >= 4) {
        throw new Error("Bootstrap's JavaScript requires at least jQuery v1.9.1 but less than v4.0.0");
    }
})(jQuery);

(function() {
    var t = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function(t) {
        return typeof t;
    } : function(t) {
        return t && typeof Symbol === "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
    };
    var e = function() {
        function t(t, e) {
            for (var n = 0; n < e.length; n++) {
                var i = e[n];
                i.enumerable = i.enumerable || false;
                i.configurable = true;
                if ("value" in i) i.writable = true;
                Object.defineProperty(t, i.key, i);
            }
        }
        return function(e, n, i) {
            if (n) t(e.prototype, n);
            if (i) t(e, i);
            return e;
        };
    }();
    function n(t, e) {
        if (!t) {
            throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        }
        return e && (typeof e === "object" || typeof e === "function") ? e : t;
    }
    function i(t, e) {
        if (typeof e !== "function" && e !== null) {
            throw new TypeError("Super expression must either be null or a function, not " + typeof e);
        }
        t.prototype = Object.create(e && e.prototype, {
            constructor: {
                value: t,
                enumerable: false,
                writable: true,
                configurable: true
            }
        });
        if (e) Object.setPrototypeOf ? Object.setPrototypeOf(t, e) : t.__proto__ = e;
    }
    function r(t, e) {
        if (!(t instanceof e)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }
    var o = function(t) {
        var e = false;
        var n = 1e6;
        var i = {
            WebkitTransition: "webkitTransitionEnd",
            MozTransition: "transitionend",
            OTransition: "oTransitionEnd otransitionend",
            transition: "transitionend"
        };
        function r(t) {
            return {}.toString.call(t).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
        }
        function o(t) {
            return (t[0] || t).nodeType;
        }
        function s() {
            return {
                bindType: e.end,
                delegateType: e.end,
                handle: function e(n) {
                    if (t(n.target).is(this)) {
                        return n.handleObj.handler.apply(this, arguments);
                    }
                    return undefined;
                }
            };
        }
        function a() {
            if (window.QUnit) {
                return false;
            }
            var t = document.createElement("bootstrap");
            for (var e in i) {
                if (t.style[e] !== undefined) {
                    return {
                        end: i[e]
                    };
                }
            }
            return false;
        }
        function l(e) {
            var n = this;
            var i = false;
            t(this).one(c.TRANSITION_END, function() {
                i = true;
            });
            setTimeout(function() {
                if (!i) {
                    c.triggerTransitionEnd(n);
                }
            }, e);
            return this;
        }
        function f() {
            e = a();
            t.fn.emulateTransitionEnd = l;
            if (c.supportsTransitionEnd()) {
                t.event.special[c.TRANSITION_END] = s();
            }
        }
        var c = {
            TRANSITION_END: "bsTransitionEnd",
            getUID: function t(e) {
                do {
                    e += ~~(Math.random() * n);
                } while (document.getElementById(e));
                return e;
            },
            getSelectorFromElement: function e(n) {
                var i = n.getAttribute("data-target");
                if (!i || i === "#") {
                    i = n.getAttribute("href") || "";
                }
                try {
                    var r = t(i);
                    return r.length > 0 ? i : null;
                } catch (t) {
                    return null;
                }
            },
            reflow: function t(e) {
                return e.offsetHeight;
            },
            triggerTransitionEnd: function n(i) {
                t(i).trigger(e.end);
            },
            supportsTransitionEnd: function t() {
                return Boolean(e);
            },
            typeCheckConfig: function t(e, n, i) {
                for (var s in i) {
                    if (i.hasOwnProperty(s)) {
                        var a = i[s];
                        var l = n[s];
                        var f = l && o(l) ? "element" : r(l);
                        if (!new RegExp(a).test(f)) {
                            throw new Error(e.toUpperCase() + ": " + ('Option "' + s + '" provided type "' + f + '" ') + ('but expected type "' + a + '".'));
                        }
                    }
                }
            }
        };
        f();
        return c;
    }(jQuery);
    var s = function(t) {
        var n = "alert";
        var i = "4.0.0-beta";
        var s = "bs.alert";
        var a = "." + s;
        var l = ".data-api";
        var f = t.fn[n];
        var c = 150;
        var u = {
            DISMISS: '[data-dismiss="alert"]'
        };
        var h = {
            CLOSE: "close" + a,
            CLOSED: "closed" + a,
            CLICK_DATA_API: "click" + a + l
        };
        var d = {
            ALERT: "alert",
            FADE: "fade",
            SHOW: "show"
        };
        var p = function() {
            function n(t) {
                r(this, n);
                this._element = t;
            }
            n.prototype.close = function t(e) {
                e = e || this._element;
                var n = this._getRootElement(e);
                var i = this._triggerCloseEvent(n);
                if (i.isDefaultPrevented()) {
                    return;
                }
                this._removeElement(n);
            };
            n.prototype.dispose = function e() {
                t.removeData(this._element, s);
                this._element = null;
            };
            n.prototype._getRootElement = function e(n) {
                var i = o.getSelectorFromElement(n);
                var r = false;
                if (i) {
                    r = t(i)[0];
                }
                if (!r) {
                    r = t(n).closest("." + d.ALERT)[0];
                }
                return r;
            };
            n.prototype._triggerCloseEvent = function e(n) {
                var i = t.Event(h.CLOSE);
                t(n).trigger(i);
                return i;
            };
            n.prototype._removeElement = function e(n) {
                var i = this;
                t(n).removeClass(d.SHOW);
                if (!o.supportsTransitionEnd() || !t(n).hasClass(d.FADE)) {
                    this._destroyElement(n);
                    return;
                }
                t(n).one(o.TRANSITION_END, function(t) {
                    return i._destroyElement(n, t);
                }).emulateTransitionEnd(c);
            };
            n.prototype._destroyElement = function e(n) {
                t(n).detach().trigger(h.CLOSED).remove();
            };
            n._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = t(this);
                    var r = e.data(s);
                    if (!r) {
                        r = new n(this);
                        e.data(s, r);
                    }
                    if (i === "close") {
                        r[i](this);
                    }
                });
            };
            n._handleDismiss = function t(e) {
                return function(t) {
                    if (t) {
                        t.preventDefault();
                    }
                    e.close(this);
                };
            };
            e(n, null, [ {
                key: "VERSION",
                get: function t() {
                    return i;
                }
            } ]);
            return n;
        }();
        t(document).on(h.CLICK_DATA_API, u.DISMISS, p._handleDismiss(new p()));
        t.fn[n] = p._jQueryInterface;
        t.fn[n].Constructor = p;
        t.fn[n].noConflict = function() {
            t.fn[n] = f;
            return p._jQueryInterface;
        };
        return p;
    }(jQuery);
    var a = function(t) {
        var n = "button";
        var i = "4.0.0-beta";
        var o = "bs.button";
        var s = "." + o;
        var a = ".data-api";
        var l = t.fn[n];
        var f = {
            ACTIVE: "active",
            BUTTON: "btn",
            FOCUS: "focus"
        };
        var c = {
            DATA_TOGGLE_CARROT: '[data-toggle^="button"]',
            DATA_TOGGLE: '[data-toggle="buttons"]',
            INPUT: "input",
            ACTIVE: ".active",
            BUTTON: ".btn"
        };
        var u = {
            CLICK_DATA_API: "click" + s + a,
            FOCUS_BLUR_DATA_API: "focus" + s + a + " " + ("blur" + s + a)
        };
        var h = function() {
            function n(t) {
                r(this, n);
                this._element = t;
            }
            n.prototype.toggle = function e() {
                var n = true;
                var i = true;
                var r = t(this._element).closest(c.DATA_TOGGLE)[0];
                if (r) {
                    var o = t(this._element).find(c.INPUT)[0];
                    if (o) {
                        if (o.type === "radio") {
                            if (o.checked && t(this._element).hasClass(f.ACTIVE)) {
                                n = false;
                            } else {
                                var s = t(r).find(c.ACTIVE)[0];
                                if (s) {
                                    t(s).removeClass(f.ACTIVE);
                                }
                            }
                        }
                        if (n) {
                            if (o.hasAttribute("disabled") || r.hasAttribute("disabled") || o.classList.contains("disabled") || r.classList.contains("disabled")) {
                                return;
                            }
                            o.checked = !t(this._element).hasClass(f.ACTIVE);
                            t(o).trigger("change");
                        }
                        o.focus();
                        i = false;
                    }
                }
                if (i) {
                    this._element.setAttribute("aria-pressed", !t(this._element).hasClass(f.ACTIVE));
                }
                if (n) {
                    t(this._element).toggleClass(f.ACTIVE);
                }
            };
            n.prototype.dispose = function e() {
                t.removeData(this._element, o);
                this._element = null;
            };
            n._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = t(this).data(o);
                    if (!e) {
                        e = new n(this);
                        t(this).data(o, e);
                    }
                    if (i === "toggle") {
                        e[i]();
                    }
                });
            };
            e(n, null, [ {
                key: "VERSION",
                get: function t() {
                    return i;
                }
            } ]);
            return n;
        }();
        t(document).on(u.CLICK_DATA_API, c.DATA_TOGGLE_CARROT, function(e) {
            e.preventDefault();
            var n = e.target;
            if (!t(n).hasClass(f.BUTTON)) {
                n = t(n).closest(c.BUTTON);
            }
            h._jQueryInterface.call(t(n), "toggle");
        }).on(u.FOCUS_BLUR_DATA_API, c.DATA_TOGGLE_CARROT, function(e) {
            var n = t(e.target).closest(c.BUTTON)[0];
            t(n).toggleClass(f.FOCUS, /^focus(in)?$/.test(e.type));
        });
        t.fn[n] = h._jQueryInterface;
        t.fn[n].Constructor = h;
        t.fn[n].noConflict = function() {
            t.fn[n] = l;
            return h._jQueryInterface;
        };
        return h;
    }(jQuery);
    var l = function(n) {
        var i = "carousel";
        var s = "4.0.0-beta";
        var a = "bs.carousel";
        var l = "." + a;
        var f = ".data-api";
        var c = n.fn[i];
        var u = 600;
        var h = 37;
        var d = 39;
        var p = 500;
        var g = {
            interval: 5e3,
            keyboard: true,
            slide: false,
            pause: "hover",
            wrap: true
        };
        var m = {
            interval: "(number|boolean)",
            keyboard: "boolean",
            slide: "(boolean|string)",
            pause: "(string|boolean)",
            wrap: "boolean"
        };
        var v = {
            NEXT: "next",
            PREV: "prev",
            LEFT: "left",
            RIGHT: "right"
        };
        var _ = {
            SLIDE: "slide" + l,
            SLID: "slid" + l,
            KEYDOWN: "keydown" + l,
            MOUSEENTER: "mouseenter" + l,
            MOUSELEAVE: "mouseleave" + l,
            TOUCHEND: "touchend" + l,
            LOAD_DATA_API: "load" + l + f,
            CLICK_DATA_API: "click" + l + f
        };
        var E = {
            CAROUSEL: "carousel",
            ACTIVE: "active",
            SLIDE: "slide",
            RIGHT: "carousel-item-right",
            LEFT: "carousel-item-left",
            NEXT: "carousel-item-next",
            PREV: "carousel-item-prev",
            ITEM: "carousel-item"
        };
        var T = {
            ACTIVE: ".active",
            ACTIVE_ITEM: ".active.carousel-item",
            ITEM: ".carousel-item",
            NEXT_PREV: ".carousel-item-next, .carousel-item-prev",
            INDICATORS: ".carousel-indicators",
            DATA_SLIDE: "[data-slide], [data-slide-to]",
            DATA_RIDE: '[data-ride="carousel"]'
        };
        var y = function() {
            function f(t, e) {
                r(this, f);
                this._items = null;
                this._interval = null;
                this._activeElement = null;
                this._isPaused = false;
                this._isSliding = false;
                this.touchTimeout = null;
                this._config = this._getConfig(e);
                this._element = n(t)[0];
                this._indicatorsElement = n(this._element).find(T.INDICATORS)[0];
                this._addEventListeners();
            }
            f.prototype.next = function t() {
                if (!this._isSliding) {
                    this._slide(v.NEXT);
                }
            };
            f.prototype.nextWhenVisible = function t() {
                if (!document.hidden) {
                    this.next();
                }
            };
            f.prototype.prev = function t() {
                if (!this._isSliding) {
                    this._slide(v.PREV);
                }
            };
            f.prototype.pause = function t(e) {
                if (!e) {
                    this._isPaused = true;
                }
                if (n(this._element).find(T.NEXT_PREV)[0] && o.supportsTransitionEnd()) {
                    o.triggerTransitionEnd(this._element);
                    this.cycle(true);
                }
                clearInterval(this._interval);
                this._interval = null;
            };
            f.prototype.cycle = function t(e) {
                if (!e) {
                    this._isPaused = false;
                }
                if (this._interval) {
                    clearInterval(this._interval);
                    this._interval = null;
                }
                if (this._config.interval && !this._isPaused) {
                    this._interval = setInterval((document.visibilityState ? this.nextWhenVisible : this.next).bind(this), this._config.interval);
                }
            };
            f.prototype.to = function t(e) {
                var i = this;
                this._activeElement = n(this._element).find(T.ACTIVE_ITEM)[0];
                var r = this._getItemIndex(this._activeElement);
                if (e > this._items.length - 1 || e < 0) {
                    return;
                }
                if (this._isSliding) {
                    n(this._element).one(_.SLID, function() {
                        return i.to(e);
                    });
                    return;
                }
                if (r === e) {
                    this.pause();
                    this.cycle();
                    return;
                }
                var o = e > r ? v.NEXT : v.PREV;
                this._slide(o, this._items[e]);
            };
            f.prototype.dispose = function t() {
                n(this._element).off(l);
                n.removeData(this._element, a);
                this._items = null;
                this._config = null;
                this._element = null;
                this._interval = null;
                this._isPaused = null;
                this._isSliding = null;
                this._activeElement = null;
                this._indicatorsElement = null;
            };
            f.prototype._getConfig = function t(e) {
                e = n.extend({}, g, e);
                o.typeCheckConfig(i, e, m);
                return e;
            };
            f.prototype._addEventListeners = function t() {
                var e = this;
                if (this._config.keyboard) {
                    n(this._element).on(_.KEYDOWN, function(t) {
                        return e._keydown(t);
                    });
                }
                if (this._config.pause === "hover") {
                    n(this._element).on(_.MOUSEENTER, function(t) {
                        return e.pause(t);
                    }).on(_.MOUSELEAVE, function(t) {
                        return e.cycle(t);
                    });
                    if ("ontouchstart" in document.documentElement) {
                        n(this._element).on(_.TOUCHEND, function() {
                            e.pause();
                            if (e.touchTimeout) {
                                clearTimeout(e.touchTimeout);
                            }
                            e.touchTimeout = setTimeout(function(t) {
                                return e.cycle(t);
                            }, p + e._config.interval);
                        });
                    }
                }
            };
            f.prototype._keydown = function t(e) {
                if (/input|textarea/i.test(e.target.tagName)) {
                    return;
                }
                switch (e.which) {
                  case h:
                    e.preventDefault();
                    this.prev();
                    break;

                  case d:
                    e.preventDefault();
                    this.next();
                    break;

                  default:
                    return;
                }
            };
            f.prototype._getItemIndex = function t(e) {
                this._items = n.makeArray(n(e).parent().find(T.ITEM));
                return this._items.indexOf(e);
            };
            f.prototype._getItemByDirection = function t(e, n) {
                var i = e === v.NEXT;
                var r = e === v.PREV;
                var o = this._getItemIndex(n);
                var s = this._items.length - 1;
                var a = r && o === 0 || i && o === s;
                if (a && !this._config.wrap) {
                    return n;
                }
                var l = e === v.PREV ? -1 : 1;
                var f = (o + l) % this._items.length;
                return f === -1 ? this._items[this._items.length - 1] : this._items[f];
            };
            f.prototype._triggerSlideEvent = function t(e, i) {
                var r = this._getItemIndex(e);
                var o = this._getItemIndex(n(this._element).find(T.ACTIVE_ITEM)[0]);
                var s = n.Event(_.SLIDE, {
                    relatedTarget: e,
                    direction: i,
                    from: o,
                    to: r
                });
                n(this._element).trigger(s);
                return s;
            };
            f.prototype._setActiveIndicatorElement = function t(e) {
                if (this._indicatorsElement) {
                    n(this._indicatorsElement).find(T.ACTIVE).removeClass(E.ACTIVE);
                    var i = this._indicatorsElement.children[this._getItemIndex(e)];
                    if (i) {
                        n(i).addClass(E.ACTIVE);
                    }
                }
            };
            f.prototype._slide = function t(e, i) {
                var r = this;
                var s = n(this._element).find(T.ACTIVE_ITEM)[0];
                var a = this._getItemIndex(s);
                var l = i || s && this._getItemByDirection(e, s);
                var f = this._getItemIndex(l);
                var c = Boolean(this._interval);
                var h = void 0;
                var d = void 0;
                var p = void 0;
                if (e === v.NEXT) {
                    h = E.LEFT;
                    d = E.NEXT;
                    p = v.LEFT;
                } else {
                    h = E.RIGHT;
                    d = E.PREV;
                    p = v.RIGHT;
                }
                if (l && n(l).hasClass(E.ACTIVE)) {
                    this._isSliding = false;
                    return;
                }
                var g = this._triggerSlideEvent(l, p);
                if (g.isDefaultPrevented()) {
                    return;
                }
                if (!s || !l) {
                    return;
                }
                this._isSliding = true;
                if (c) {
                    this.pause();
                }
                this._setActiveIndicatorElement(l);
                var m = n.Event(_.SLID, {
                    relatedTarget: l,
                    direction: p,
                    from: a,
                    to: f
                });
                if (o.supportsTransitionEnd() && n(this._element).hasClass(E.SLIDE)) {
                    n(l).addClass(d);
                    o.reflow(l);
                    n(s).addClass(h);
                    n(l).addClass(h);
                    n(s).one(o.TRANSITION_END, function() {
                        n(l).removeClass(h + " " + d).addClass(E.ACTIVE);
                        n(s).removeClass(E.ACTIVE + " " + d + " " + h);
                        r._isSliding = false;
                        setTimeout(function() {
                            return n(r._element).trigger(m);
                        }, 0);
                    }).emulateTransitionEnd(u);
                } else {
                    n(s).removeClass(E.ACTIVE);
                    n(l).addClass(E.ACTIVE);
                    this._isSliding = false;
                    n(this._element).trigger(m);
                }
                if (c) {
                    this.cycle();
                }
            };
            f._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = n(this).data(a);
                    var r = n.extend({}, g, n(this).data());
                    if ((typeof i === "undefined" ? "undefined" : t(i)) === "object") {
                        n.extend(r, i);
                    }
                    var o = typeof i === "string" ? i : r.slide;
                    if (!e) {
                        e = new f(this, r);
                        n(this).data(a, e);
                    }
                    if (typeof i === "number") {
                        e.to(i);
                    } else if (typeof o === "string") {
                        if (e[o] === undefined) {
                            throw new Error('No method named "' + o + '"');
                        }
                        e[o]();
                    } else if (r.interval) {
                        e.pause();
                        e.cycle();
                    }
                });
            };
            f._dataApiClickHandler = function t(e) {
                var i = o.getSelectorFromElement(this);
                if (!i) {
                    return;
                }
                var r = n(i)[0];
                if (!r || !n(r).hasClass(E.CAROUSEL)) {
                    return;
                }
                var s = n.extend({}, n(r).data(), n(this).data());
                var l = this.getAttribute("data-slide-to");
                if (l) {
                    s.interval = false;
                }
                f._jQueryInterface.call(n(r), s);
                if (l) {
                    n(r).data(a).to(l);
                }
                e.preventDefault();
            };
            e(f, null, [ {
                key: "VERSION",
                get: function t() {
                    return s;
                }
            }, {
                key: "Default",
                get: function t() {
                    return g;
                }
            } ]);
            return f;
        }();
        n(document).on(_.CLICK_DATA_API, T.DATA_SLIDE, y._dataApiClickHandler);
        n(window).on(_.LOAD_DATA_API, function() {
            n(T.DATA_RIDE).each(function() {
                var t = n(this);
                y._jQueryInterface.call(t, t.data());
            });
        });
        n.fn[i] = y._jQueryInterface;
        n.fn[i].Constructor = y;
        n.fn[i].noConflict = function() {
            n.fn[i] = c;
            return y._jQueryInterface;
        };
        return y;
    }(jQuery);
    var f = function(n) {
        var i = "collapse";
        var s = "4.0.0-beta";
        var a = "bs.collapse";
        var l = "." + a;
        var f = ".data-api";
        var c = n.fn[i];
        var u = 600;
        var h = {
            toggle: true,
            parent: ""
        };
        var d = {
            toggle: "boolean",
            parent: "string"
        };
        var p = {
            SHOW: "show" + l,
            SHOWN: "shown" + l,
            HIDE: "hide" + l,
            HIDDEN: "hidden" + l,
            CLICK_DATA_API: "click" + l + f
        };
        var g = {
            SHOW: "show",
            COLLAPSE: "collapse",
            COLLAPSING: "collapsing",
            COLLAPSED: "collapsed"
        };
        var m = {
            WIDTH: "width",
            HEIGHT: "height"
        };
        var v = {
            ACTIVES: ".show, .collapsing",
            DATA_TOGGLE: '[data-toggle="collapse"]'
        };
        var _ = function() {
            function l(t, e) {
                r(this, l);
                this._isTransitioning = false;
                this._element = t;
                this._config = this._getConfig(e);
                this._triggerArray = n.makeArray(n('[data-toggle="collapse"][href="#' + t.id + '"],' + ('[data-toggle="collapse"][data-target="#' + t.id + '"]')));
                var i = n(v.DATA_TOGGLE);
                for (var s = 0; s < i.length; s++) {
                    var a = i[s];
                    var f = o.getSelectorFromElement(a);
                    if (f !== null && n(f).filter(t).length > 0) {
                        this._triggerArray.push(a);
                    }
                }
                this._parent = this._config.parent ? this._getParent() : null;
                if (!this._config.parent) {
                    this._addAriaAndCollapsedClass(this._element, this._triggerArray);
                }
                if (this._config.toggle) {
                    this.toggle();
                }
            }
            l.prototype.toggle = function t() {
                if (n(this._element).hasClass(g.SHOW)) {
                    this.hide();
                } else {
                    this.show();
                }
            };
            l.prototype.show = function t() {
                var e = this;
                if (this._isTransitioning || n(this._element).hasClass(g.SHOW)) {
                    return;
                }
                var i = void 0;
                var r = void 0;
                if (this._parent) {
                    i = n.makeArray(n(this._parent).children().children(v.ACTIVES));
                    if (!i.length) {
                        i = null;
                    }
                }
                if (i) {
                    r = n(i).data(a);
                    if (r && r._isTransitioning) {
                        return;
                    }
                }
                var s = n.Event(p.SHOW);
                n(this._element).trigger(s);
                if (s.isDefaultPrevented()) {
                    return;
                }
                if (i) {
                    l._jQueryInterface.call(n(i), "hide");
                    if (!r) {
                        n(i).data(a, null);
                    }
                }
                var f = this._getDimension();
                n(this._element).removeClass(g.COLLAPSE).addClass(g.COLLAPSING);
                this._element.style[f] = 0;
                if (this._triggerArray.length) {
                    n(this._triggerArray).removeClass(g.COLLAPSED).attr("aria-expanded", true);
                }
                this.setTransitioning(true);
                var c = function t() {
                    n(e._element).removeClass(g.COLLAPSING).addClass(g.COLLAPSE).addClass(g.SHOW);
                    e._element.style[f] = "";
                    e.setTransitioning(false);
                    n(e._element).trigger(p.SHOWN);
                };
                if (!o.supportsTransitionEnd()) {
                    c();
                    return;
                }
                var h = f[0].toUpperCase() + f.slice(1);
                var d = "scroll" + h;
                n(this._element).one(o.TRANSITION_END, c).emulateTransitionEnd(u);
                this._element.style[f] = this._element[d] + "px";
            };
            l.prototype.hide = function t() {
                var e = this;
                if (this._isTransitioning || !n(this._element).hasClass(g.SHOW)) {
                    return;
                }
                var i = n.Event(p.HIDE);
                n(this._element).trigger(i);
                if (i.isDefaultPrevented()) {
                    return;
                }
                var r = this._getDimension();
                this._element.style[r] = this._element.getBoundingClientRect()[r] + "px";
                o.reflow(this._element);
                n(this._element).addClass(g.COLLAPSING).removeClass(g.COLLAPSE).removeClass(g.SHOW);
                if (this._triggerArray.length) {
                    for (var s = 0; s < this._triggerArray.length; s++) {
                        var a = this._triggerArray[s];
                        var l = o.getSelectorFromElement(a);
                        if (l !== null) {
                            var f = n(l);
                            if (!f.hasClass(g.SHOW)) {
                                n(a).addClass(g.COLLAPSED).attr("aria-expanded", false);
                            }
                        }
                    }
                }
                this.setTransitioning(true);
                var c = function t() {
                    e.setTransitioning(false);
                    n(e._element).removeClass(g.COLLAPSING).addClass(g.COLLAPSE).trigger(p.HIDDEN);
                };
                this._element.style[r] = "";
                if (!o.supportsTransitionEnd()) {
                    c();
                    return;
                }
                n(this._element).one(o.TRANSITION_END, c).emulateTransitionEnd(u);
            };
            l.prototype.setTransitioning = function t(e) {
                this._isTransitioning = e;
            };
            l.prototype.dispose = function t() {
                n.removeData(this._element, a);
                this._config = null;
                this._parent = null;
                this._element = null;
                this._triggerArray = null;
                this._isTransitioning = null;
            };
            l.prototype._getConfig = function t(e) {
                e = n.extend({}, h, e);
                e.toggle = Boolean(e.toggle);
                o.typeCheckConfig(i, e, d);
                return e;
            };
            l.prototype._getDimension = function t() {
                var e = n(this._element).hasClass(m.WIDTH);
                return e ? m.WIDTH : m.HEIGHT;
            };
            l.prototype._getParent = function t() {
                var e = this;
                var i = n(this._config.parent)[0];
                var r = '[data-toggle="collapse"][data-parent="' + this._config.parent + '"]';
                n(i).find(r).each(function(t, n) {
                    e._addAriaAndCollapsedClass(l._getTargetFromElement(n), [ n ]);
                });
                return i;
            };
            l.prototype._addAriaAndCollapsedClass = function t(e, i) {
                if (e) {
                    var r = n(e).hasClass(g.SHOW);
                    if (i.length) {
                        n(i).toggleClass(g.COLLAPSED, !r).attr("aria-expanded", r);
                    }
                }
            };
            l._getTargetFromElement = function t(e) {
                var i = o.getSelectorFromElement(e);
                return i ? n(i)[0] : null;
            };
            l._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = n(this);
                    var r = e.data(a);
                    var o = n.extend({}, h, e.data(), (typeof i === "undefined" ? "undefined" : t(i)) === "object" && i);
                    if (!r && o.toggle && /show|hide/.test(i)) {
                        o.toggle = false;
                    }
                    if (!r) {
                        r = new l(this, o);
                        e.data(a, r);
                    }
                    if (typeof i === "string") {
                        if (r[i] === undefined) {
                            throw new Error('No method named "' + i + '"');
                        }
                        r[i]();
                    }
                });
            };
            e(l, null, [ {
                key: "VERSION",
                get: function t() {
                    return s;
                }
            }, {
                key: "Default",
                get: function t() {
                    return h;
                }
            } ]);
            return l;
        }();
        n(document).on(p.CLICK_DATA_API, v.DATA_TOGGLE, function(t) {
            if (!/input|textarea/i.test(t.target.tagName)) {
                t.preventDefault();
            }
            var e = n(this);
            var i = o.getSelectorFromElement(this);
            n(i).each(function() {
                var t = n(this);
                var i = t.data(a);
                var r = i ? "toggle" : e.data();
                _._jQueryInterface.call(t, r);
            });
        });
        n.fn[i] = _._jQueryInterface;
        n.fn[i].Constructor = _;
        n.fn[i].noConflict = function() {
            n.fn[i] = c;
            return _._jQueryInterface;
        };
        return _;
    }(jQuery);
    var c = function(n) {
        if (typeof Popper === "undefined") {
            throw new Error("Bootstrap dropdown require Popper.js (https://popper.js.org)");
        }
        var i = "dropdown";
        var s = "4.0.0-beta";
        var a = "bs.dropdown";
        var l = "." + a;
        var f = ".data-api";
        var c = n.fn[i];
        var u = 27;
        var h = 32;
        var d = 9;
        var p = 38;
        var g = 40;
        var m = 3;
        var v = new RegExp(p + "|" + g + "|" + u);
        var _ = {
            HIDE: "hide" + l,
            HIDDEN: "hidden" + l,
            SHOW: "show" + l,
            SHOWN: "shown" + l,
            CLICK: "click" + l,
            CLICK_DATA_API: "click" + l + f,
            KEYDOWN_DATA_API: "keydown" + l + f,
            KEYUP_DATA_API: "keyup" + l + f
        };
        var E = {
            DISABLED: "disabled",
            SHOW: "show",
            DROPUP: "dropup",
            MENURIGHT: "dropdown-menu-right",
            MENULEFT: "dropdown-menu-left"
        };
        var T = {
            DATA_TOGGLE: '[data-toggle="dropdown"]',
            FORM_CHILD: ".dropdown form",
            MENU: ".dropdown-menu",
            NAVBAR_NAV: ".navbar-nav",
            VISIBLE_ITEMS: ".dropdown-menu .dropdown-item:not(.disabled)"
        };
        var y = {
            TOP: "top-start",
            TOPEND: "top-end",
            BOTTOM: "bottom-start",
            BOTTOMEND: "bottom-end"
        };
        var A = {
            placement: y.BOTTOM,
            offset: 0,
            flip: true
        };
        var C = {
            placement: "string",
            offset: "(number|string)",
            flip: "boolean"
        };
        var O = function() {
            function f(t, e) {
                r(this, f);
                this._element = t;
                this._popper = null;
                this._config = this._getConfig(e);
                this._menu = this._getMenuElement();
                this._inNavbar = this._detectNavbar();
                this._addEventListeners();
            }
            f.prototype.toggle = function t() {
                if (this._element.disabled || n(this._element).hasClass(E.DISABLED)) {
                    return;
                }
                var e = f._getParentFromElement(this._element);
                var i = n(this._menu).hasClass(E.SHOW);
                f._clearMenus();
                if (i) {
                    return;
                }
                var r = {
                    relatedTarget: this._element
                };
                var o = n.Event(_.SHOW, r);
                n(e).trigger(o);
                if (o.isDefaultPrevented()) {
                    return;
                }
                var s = this._element;
                if (n(e).hasClass(E.DROPUP)) {
                    if (n(this._menu).hasClass(E.MENULEFT) || n(this._menu).hasClass(E.MENURIGHT)) {
                        s = e;
                    }
                }
                this._popper = new Popper(s, this._menu, this._getPopperConfig());
                if ("ontouchstart" in document.documentElement && !n(e).closest(T.NAVBAR_NAV).length) {
                    n("body").children().on("mouseover", null, n.noop);
                }
                this._element.focus();
                this._element.setAttribute("aria-expanded", true);
                n(this._menu).toggleClass(E.SHOW);
                n(e).toggleClass(E.SHOW).trigger(n.Event(_.SHOWN, r));
            };
            f.prototype.dispose = function t() {
                n.removeData(this._element, a);
                n(this._element).off(l);
                this._element = null;
                this._menu = null;
                if (this._popper !== null) {
                    this._popper.destroy();
                }
                this._popper = null;
            };
            f.prototype.update = function t() {
                this._inNavbar = this._detectNavbar();
                if (this._popper !== null) {
                    this._popper.scheduleUpdate();
                }
            };
            f.prototype._addEventListeners = function t() {
                var e = this;
                n(this._element).on(_.CLICK, function(t) {
                    t.preventDefault();
                    t.stopPropagation();
                    e.toggle();
                });
            };
            f.prototype._getConfig = function t(e) {
                var r = n(this._element).data();
                if (r.placement !== undefined) {
                    r.placement = y[r.placement.toUpperCase()];
                }
                e = n.extend({}, this.constructor.Default, n(this._element).data(), e);
                o.typeCheckConfig(i, e, this.constructor.DefaultType);
                return e;
            };
            f.prototype._getMenuElement = function t() {
                if (!this._menu) {
                    var e = f._getParentFromElement(this._element);
                    this._menu = n(e).find(T.MENU)[0];
                }
                return this._menu;
            };
            f.prototype._getPlacement = function t() {
                var e = n(this._element).parent();
                var i = this._config.placement;
                if (e.hasClass(E.DROPUP) || this._config.placement === y.TOP) {
                    i = y.TOP;
                    if (n(this._menu).hasClass(E.MENURIGHT)) {
                        i = y.TOPEND;
                    }
                } else if (n(this._menu).hasClass(E.MENURIGHT)) {
                    i = y.BOTTOMEND;
                }
                return i;
            };
            f.prototype._detectNavbar = function t() {
                return n(this._element).closest(".navbar").length > 0;
            };
            f.prototype._getPopperConfig = function t() {
                var e = {
                    placement: this._getPlacement(),
                    modifiers: {
                        offset: {
                            offset: this._config.offset
                        },
                        flip: {
                            enabled: this._config.flip
                        }
                    }
                };
                if (this._inNavbar) {
                    e.modifiers.applyStyle = {
                        enabled: !this._inNavbar
                    };
                }
                return e;
            };
            f._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = n(this).data(a);
                    var r = (typeof i === "undefined" ? "undefined" : t(i)) === "object" ? i : null;
                    if (!e) {
                        e = new f(this, r);
                        n(this).data(a, e);
                    }
                    if (typeof i === "string") {
                        if (e[i] === undefined) {
                            throw new Error('No method named "' + i + '"');
                        }
                        e[i]();
                    }
                });
            };
            f._clearMenus = function t(e) {
                if (e && (e.which === m || e.type === "keyup" && e.which !== d)) {
                    return;
                }
                var i = n.makeArray(n(T.DATA_TOGGLE));
                for (var r = 0; r < i.length; r++) {
                    var o = f._getParentFromElement(i[r]);
                    var s = n(i[r]).data(a);
                    var l = {
                        relatedTarget: i[r]
                    };
                    if (!s) {
                        continue;
                    }
                    var c = s._menu;
                    if (!n(o).hasClass(E.SHOW)) {
                        continue;
                    }
                    if (e && (e.type === "click" && /input|textarea/i.test(e.target.tagName) || e.type === "keyup" && e.which === d) && n.contains(o, e.target)) {
                        continue;
                    }
                    var u = n.Event(_.HIDE, l);
                    n(o).trigger(u);
                    if (u.isDefaultPrevented()) {
                        continue;
                    }
                    if ("ontouchstart" in document.documentElement) {
                        n("body").children().off("mouseover", null, n.noop);
                    }
                    i[r].setAttribute("aria-expanded", "false");
                    n(c).removeClass(E.SHOW);
                    n(o).removeClass(E.SHOW).trigger(n.Event(_.HIDDEN, l));
                }
            };
            f._getParentFromElement = function t(e) {
                var i = void 0;
                var r = o.getSelectorFromElement(e);
                if (r) {
                    i = n(r)[0];
                }
                return i || e.parentNode;
            };
            f._dataApiKeydownHandler = function t(e) {
                if (!v.test(e.which) || /button/i.test(e.target.tagName) && e.which === h || /input|textarea/i.test(e.target.tagName)) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                if (this.disabled || n(this).hasClass(E.DISABLED)) {
                    return;
                }
                var i = f._getParentFromElement(this);
                var r = n(i).hasClass(E.SHOW);
                if (!r && (e.which !== u || e.which !== h) || r && (e.which === u || e.which === h)) {
                    if (e.which === u) {
                        var o = n(i).find(T.DATA_TOGGLE)[0];
                        n(o).trigger("focus");
                    }
                    n(this).trigger("click");
                    return;
                }
                var s = n(i).find(T.VISIBLE_ITEMS).get();
                if (!s.length) {
                    return;
                }
                var a = s.indexOf(e.target);
                if (e.which === p && a > 0) {
                    a--;
                }
                if (e.which === g && a < s.length - 1) {
                    a++;
                }
                if (a < 0) {
                    a = 0;
                }
                s[a].focus();
            };
            e(f, null, [ {
                key: "VERSION",
                get: function t() {
                    return s;
                }
            }, {
                key: "Default",
                get: function t() {
                    return A;
                }
            }, {
                key: "DefaultType",
                get: function t() {
                    return C;
                }
            } ]);
            return f;
        }();
        n(document).on(_.KEYDOWN_DATA_API, T.DATA_TOGGLE, O._dataApiKeydownHandler).on(_.KEYDOWN_DATA_API, T.MENU, O._dataApiKeydownHandler).on(_.CLICK_DATA_API + " " + _.KEYUP_DATA_API, O._clearMenus).on(_.CLICK_DATA_API, T.DATA_TOGGLE, function(t) {
            t.preventDefault();
            t.stopPropagation();
            O._jQueryInterface.call(n(this), "toggle");
        }).on(_.CLICK_DATA_API, T.FORM_CHILD, function(t) {
            t.stopPropagation();
        });
        n.fn[i] = O._jQueryInterface;
        n.fn[i].Constructor = O;
        n.fn[i].noConflict = function() {
            n.fn[i] = c;
            return O._jQueryInterface;
        };
        return O;
    }(jQuery);
    var u = function(n) {
        var i = "modal";
        var s = "4.0.0-beta";
        var a = "bs.modal";
        var l = "." + a;
        var f = ".data-api";
        var c = n.fn[i];
        var u = 300;
        var h = 150;
        var d = 27;
        var p = {
            backdrop: true,
            keyboard: true,
            focus: true,
            show: true
        };
        var g = {
            backdrop: "(boolean|string)",
            keyboard: "boolean",
            focus: "boolean",
            show: "boolean"
        };
        var m = {
            HIDE: "hide" + l,
            HIDDEN: "hidden" + l,
            SHOW: "show" + l,
            SHOWN: "shown" + l,
            FOCUSIN: "focusin" + l,
            RESIZE: "resize" + l,
            CLICK_DISMISS: "click.dismiss" + l,
            KEYDOWN_DISMISS: "keydown.dismiss" + l,
            MOUSEUP_DISMISS: "mouseup.dismiss" + l,
            MOUSEDOWN_DISMISS: "mousedown.dismiss" + l,
            CLICK_DATA_API: "click" + l + f
        };
        var v = {
            SCROLLBAR_MEASURER: "modal-scrollbar-measure",
            BACKDROP: "modal-backdrop",
            OPEN: "modal-open",
            FADE: "fade",
            SHOW: "show"
        };
        var _ = {
            DIALOG: ".modal-dialog",
            DATA_TOGGLE: '[data-toggle="modal"]',
            DATA_DISMISS: '[data-dismiss="modal"]',
            FIXED_CONTENT: ".fixed-top, .fixed-bottom, .is-fixed, .sticky-top",
            NAVBAR_TOGGLER: ".navbar-toggler"
        };
        var E = function() {
            function f(t, e) {
                r(this, f);
                this._config = this._getConfig(e);
                this._element = t;
                this._dialog = n(t).find(_.DIALOG)[0];
                this._backdrop = null;
                this._isShown = false;
                this._isBodyOverflowing = false;
                this._ignoreBackdropClick = false;
                this._originalBodyPadding = 0;
                this._scrollbarWidth = 0;
            }
            f.prototype.toggle = function t(e) {
                return this._isShown ? this.hide() : this.show(e);
            };
            f.prototype.show = function t(e) {
                var i = this;
                if (this._isTransitioning) {
                    return;
                }
                if (o.supportsTransitionEnd() && n(this._element).hasClass(v.FADE)) {
                    this._isTransitioning = true;
                }
                var r = n.Event(m.SHOW, {
                    relatedTarget: e
                });
                n(this._element).trigger(r);
                if (this._isShown || r.isDefaultPrevented()) {
                    return;
                }
                this._isShown = true;
                this._checkScrollbar();
                this._setScrollbar();
                n(document.body).addClass(v.OPEN);
                this._setEscapeEvent();
                this._setResizeEvent();
                n(this._element).on(m.CLICK_DISMISS, _.DATA_DISMISS, function(t) {
                    return i.hide(t);
                });
                n(this._dialog).on(m.MOUSEDOWN_DISMISS, function() {
                    n(i._element).one(m.MOUSEUP_DISMISS, function(t) {
                        if (n(t.target).is(i._element)) {
                            i._ignoreBackdropClick = true;
                        }
                    });
                });
                this._showBackdrop(function() {
                    return i._showElement(e);
                });
            };
            f.prototype.hide = function t(e) {
                var i = this;
                if (e) {
                    e.preventDefault();
                }
                if (this._isTransitioning || !this._isShown) {
                    return;
                }
                var r = o.supportsTransitionEnd() && n(this._element).hasClass(v.FADE);
                if (r) {
                    this._isTransitioning = true;
                }
                var s = n.Event(m.HIDE);
                n(this._element).trigger(s);
                if (!this._isShown || s.isDefaultPrevented()) {
                    return;
                }
                this._isShown = false;
                this._setEscapeEvent();
                this._setResizeEvent();
                n(document).off(m.FOCUSIN);
                n(this._element).removeClass(v.SHOW);
                n(this._element).off(m.CLICK_DISMISS);
                n(this._dialog).off(m.MOUSEDOWN_DISMISS);
                if (r) {
                    n(this._element).one(o.TRANSITION_END, function(t) {
                        return i._hideModal(t);
                    }).emulateTransitionEnd(u);
                } else {
                    this._hideModal();
                }
            };
            f.prototype.dispose = function t() {
                n.removeData(this._element, a);
                n(window, document, this._element, this._backdrop).off(l);
                this._config = null;
                this._element = null;
                this._dialog = null;
                this._backdrop = null;
                this._isShown = null;
                this._isBodyOverflowing = null;
                this._ignoreBackdropClick = null;
                this._scrollbarWidth = null;
            };
            f.prototype.handleUpdate = function t() {
                this._adjustDialog();
            };
            f.prototype._getConfig = function t(e) {
                e = n.extend({}, p, e);
                o.typeCheckConfig(i, e, g);
                return e;
            };
            f.prototype._showElement = function t(e) {
                var i = this;
                var r = o.supportsTransitionEnd() && n(this._element).hasClass(v.FADE);
                if (!this._element.parentNode || this._element.parentNode.nodeType !== Node.ELEMENT_NODE) {
                    document.body.appendChild(this._element);
                }
                this._element.style.display = "block";
                this._element.removeAttribute("aria-hidden");
                this._element.scrollTop = 0;
                if (r) {
                    o.reflow(this._element);
                }
                n(this._element).addClass(v.SHOW);
                if (this._config.focus) {
                    this._enforceFocus();
                }
                var s = n.Event(m.SHOWN, {
                    relatedTarget: e
                });
                var a = function t() {
                    if (i._config.focus) {
                        i._element.focus();
                    }
                    i._isTransitioning = false;
                    n(i._element).trigger(s);
                };
                if (r) {
                    n(this._dialog).one(o.TRANSITION_END, a).emulateTransitionEnd(u);
                } else {
                    a();
                }
            };
            f.prototype._enforceFocus = function t() {
                var e = this;
                n(document).off(m.FOCUSIN).on(m.FOCUSIN, function(t) {
                    if (document !== t.target && e._element !== t.target && !n(e._element).has(t.target).length) {
                        e._element.focus();
                    }
                });
            };
            f.prototype._setEscapeEvent = function t() {
                var e = this;
                if (this._isShown && this._config.keyboard) {
                    n(this._element).on(m.KEYDOWN_DISMISS, function(t) {
                        if (t.which === d) {
                            t.preventDefault();
                            e.hide();
                        }
                    });
                } else if (!this._isShown) {
                    n(this._element).off(m.KEYDOWN_DISMISS);
                }
            };
            f.prototype._setResizeEvent = function t() {
                var e = this;
                if (this._isShown) {
                    n(window).on(m.RESIZE, function(t) {
                        return e.handleUpdate(t);
                    });
                } else {
                    n(window).off(m.RESIZE);
                }
            };
            f.prototype._hideModal = function t() {
                var e = this;
                this._element.style.display = "none";
                this._element.setAttribute("aria-hidden", true);
                this._isTransitioning = false;
                this._showBackdrop(function() {
                    n(document.body).removeClass(v.OPEN);
                    e._resetAdjustments();
                    e._resetScrollbar();
                    n(e._element).trigger(m.HIDDEN);
                });
            };
            f.prototype._removeBackdrop = function t() {
                if (this._backdrop) {
                    n(this._backdrop).remove();
                    this._backdrop = null;
                }
            };
            f.prototype._showBackdrop = function t(e) {
                var i = this;
                var r = n(this._element).hasClass(v.FADE) ? v.FADE : "";
                if (this._isShown && this._config.backdrop) {
                    var s = o.supportsTransitionEnd() && r;
                    this._backdrop = document.createElement("div");
                    this._backdrop.className = v.BACKDROP;
                    if (r) {
                        n(this._backdrop).addClass(r);
                    }
                    n(this._backdrop).appendTo(document.body);
                    n(this._element).on(m.CLICK_DISMISS, function(t) {
                        if (i._ignoreBackdropClick) {
                            i._ignoreBackdropClick = false;
                            return;
                        }
                        if (t.target !== t.currentTarget) {
                            return;
                        }
                        if (i._config.backdrop === "static") {
                            i._element.focus();
                        } else {
                            i.hide();
                        }
                    });
                    if (s) {
                        o.reflow(this._backdrop);
                    }
                    n(this._backdrop).addClass(v.SHOW);
                    if (!e) {
                        return;
                    }
                    if (!s) {
                        e();
                        return;
                    }
                    n(this._backdrop).one(o.TRANSITION_END, e).emulateTransitionEnd(h);
                } else if (!this._isShown && this._backdrop) {
                    n(this._backdrop).removeClass(v.SHOW);
                    var a = function t() {
                        i._removeBackdrop();
                        if (e) {
                            e();
                        }
                    };
                    if (o.supportsTransitionEnd() && n(this._element).hasClass(v.FADE)) {
                        n(this._backdrop).one(o.TRANSITION_END, a).emulateTransitionEnd(h);
                    } else {
                        a();
                    }
                } else if (e) {
                    e();
                }
            };
            f.prototype._adjustDialog = function t() {
                var e = this._element.scrollHeight > document.documentElement.clientHeight;
                if (!this._isBodyOverflowing && e) {
                    this._element.style.paddingLeft = this._scrollbarWidth + "px";
                }
                if (this._isBodyOverflowing && !e) {
                    this._element.style.paddingRight = this._scrollbarWidth + "px";
                }
            };
            f.prototype._resetAdjustments = function t() {
                this._element.style.paddingLeft = "";
                this._element.style.paddingRight = "";
            };
            f.prototype._checkScrollbar = function t() {
                this._isBodyOverflowing = document.body.clientWidth < window.innerWidth;
                this._scrollbarWidth = this._getScrollbarWidth();
            };
            f.prototype._setScrollbar = function t() {
                var e = this;
                if (this._isBodyOverflowing) {
                    n(_.FIXED_CONTENT).each(function(t, i) {
                        var r = n(i)[0].style.paddingRight;
                        var o = n(i).css("padding-right");
                        n(i).data("padding-right", r).css("padding-right", parseFloat(o) + e._scrollbarWidth + "px");
                    });
                    n(_.NAVBAR_TOGGLER).each(function(t, i) {
                        var r = n(i)[0].style.marginRight;
                        var o = n(i).css("margin-right");
                        n(i).data("margin-right", r).css("margin-right", parseFloat(o) + e._scrollbarWidth + "px");
                    });
                    var i = document.body.style.paddingRight;
                    var r = n("body").css("padding-right");
                    n("body").data("padding-right", i).css("padding-right", parseFloat(r) + this._scrollbarWidth + "px");
                }
            };
            f.prototype._resetScrollbar = function t() {
                n(_.FIXED_CONTENT).each(function(t, e) {
                    var i = n(e).data("padding-right");
                    if (typeof i !== "undefined") {
                        n(e).css("padding-right", i).removeData("padding-right");
                    }
                });
                n(_.NAVBAR_TOGGLER).each(function(t, e) {
                    var i = n(e).data("margin-right");
                    if (typeof i !== "undefined") {
                        n(e).css("margin-right", i).removeData("margin-right");
                    }
                });
                var e = n("body").data("padding-right");
                if (typeof e !== "undefined") {
                    n("body").css("padding-right", e).removeData("padding-right");
                }
            };
            f.prototype._getScrollbarWidth = function t() {
                var e = document.createElement("div");
                e.className = v.SCROLLBAR_MEASURER;
                document.body.appendChild(e);
                var n = e.getBoundingClientRect().width - e.clientWidth;
                document.body.removeChild(e);
                return n;
            };
            f._jQueryInterface = function e(i, r) {
                return this.each(function() {
                    var e = n(this).data(a);
                    var o = n.extend({}, f.Default, n(this).data(), (typeof i === "undefined" ? "undefined" : t(i)) === "object" && i);
                    if (!e) {
                        e = new f(this, o);
                        n(this).data(a, e);
                    }
                    if (typeof i === "string") {
                        if (e[i] === undefined) {
                            throw new Error('No method named "' + i + '"');
                        }
                        e[i](r);
                    } else if (o.show) {
                        e.show(r);
                    }
                });
            };
            e(f, null, [ {
                key: "VERSION",
                get: function t() {
                    return s;
                }
            }, {
                key: "Default",
                get: function t() {
                    return p;
                }
            } ]);
            return f;
        }();
        n(document).on(m.CLICK_DATA_API, _.DATA_TOGGLE, function(t) {
            var e = this;
            var i = void 0;
            var r = o.getSelectorFromElement(this);
            if (r) {
                i = n(r)[0];
            }
            var s = n(i).data(a) ? "toggle" : n.extend({}, n(i).data(), n(this).data());
            if (this.tagName === "A" || this.tagName === "AREA") {
                t.preventDefault();
            }
            var l = n(i).one(m.SHOW, function(t) {
                if (t.isDefaultPrevented()) {
                    return;
                }
                l.one(m.HIDDEN, function() {
                    if (n(e).is(":visible")) {
                        e.focus();
                    }
                });
            });
            E._jQueryInterface.call(n(i), s, this);
        });
        n.fn[i] = E._jQueryInterface;
        n.fn[i].Constructor = E;
        n.fn[i].noConflict = function() {
            n.fn[i] = c;
            return E._jQueryInterface;
        };
        return E;
    }(jQuery);
    var h = function(n) {
        var i = "scrollspy";
        var s = "4.0.0-beta";
        var a = "bs.scrollspy";
        var l = "." + a;
        var f = ".data-api";
        var c = n.fn[i];
        var u = {
            offset: 10,
            method: "auto",
            target: ""
        };
        var h = {
            offset: "number",
            method: "string",
            target: "(string|element)"
        };
        var d = {
            ACTIVATE: "activate" + l,
            SCROLL: "scroll" + l,
            LOAD_DATA_API: "load" + l + f
        };
        var p = {
            DROPDOWN_ITEM: "dropdown-item",
            DROPDOWN_MENU: "dropdown-menu",
            ACTIVE: "active"
        };
        var g = {
            DATA_SPY: '[data-spy="scroll"]',
            ACTIVE: ".active",
            NAV_LIST_GROUP: ".nav, .list-group",
            NAV_LINKS: ".nav-link",
            LIST_ITEMS: ".list-group-item",
            DROPDOWN: ".dropdown",
            DROPDOWN_ITEMS: ".dropdown-item",
            DROPDOWN_TOGGLE: ".dropdown-toggle"
        };
        var m = {
            OFFSET: "offset",
            POSITION: "position"
        };
        var v = function() {
            function f(t, e) {
                var i = this;
                r(this, f);
                this._element = t;
                this._scrollElement = t.tagName === "BODY" ? window : t;
                this._config = this._getConfig(e);
                this._selector = this._config.target + " " + g.NAV_LINKS + "," + (this._config.target + " " + g.LIST_ITEMS + ",") + (this._config.target + " " + g.DROPDOWN_ITEMS);
                this._offsets = [];
                this._targets = [];
                this._activeTarget = null;
                this._scrollHeight = 0;
                n(this._scrollElement).on(d.SCROLL, function(t) {
                    return i._process(t);
                });
                this.refresh();
                this._process();
            }
            f.prototype.refresh = function t() {
                var e = this;
                var i = this._scrollElement !== this._scrollElement.window ? m.POSITION : m.OFFSET;
                var r = this._config.method === "auto" ? i : this._config.method;
                var s = r === m.POSITION ? this._getScrollTop() : 0;
                this._offsets = [];
                this._targets = [];
                this._scrollHeight = this._getScrollHeight();
                var a = n.makeArray(n(this._selector));
                a.map(function(t) {
                    var e = void 0;
                    var i = o.getSelectorFromElement(t);
                    if (i) {
                        e = n(i)[0];
                    }
                    if (e) {
                        var a = e.getBoundingClientRect();
                        if (a.width || a.height) {
                            return [ n(e)[r]().top + s, i ];
                        }
                    }
                    return null;
                }).filter(function(t) {
                    return t;
                }).sort(function(t, e) {
                    return t[0] - e[0];
                }).forEach(function(t) {
                    e._offsets.push(t[0]);
                    e._targets.push(t[1]);
                });
            };
            f.prototype.dispose = function t() {
                n.removeData(this._element, a);
                n(this._scrollElement).off(l);
                this._element = null;
                this._scrollElement = null;
                this._config = null;
                this._selector = null;
                this._offsets = null;
                this._targets = null;
                this._activeTarget = null;
                this._scrollHeight = null;
            };
            f.prototype._getConfig = function t(e) {
                e = n.extend({}, u, e);
                if (typeof e.target !== "string") {
                    var r = n(e.target).attr("id");
                    if (!r) {
                        r = o.getUID(i);
                        n(e.target).attr("id", r);
                    }
                    e.target = "#" + r;
                }
                o.typeCheckConfig(i, e, h);
                return e;
            };
            f.prototype._getScrollTop = function t() {
                return this._scrollElement === window ? this._scrollElement.pageYOffset : this._scrollElement.scrollTop;
            };
            f.prototype._getScrollHeight = function t() {
                return this._scrollElement.scrollHeight || Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
            };
            f.prototype._getOffsetHeight = function t() {
                return this._scrollElement === window ? window.innerHeight : this._scrollElement.getBoundingClientRect().height;
            };
            f.prototype._process = function t() {
                var e = this._getScrollTop() + this._config.offset;
                var n = this._getScrollHeight();
                var i = this._config.offset + n - this._getOffsetHeight();
                if (this._scrollHeight !== n) {
                    this.refresh();
                }
                if (e >= i) {
                    var r = this._targets[this._targets.length - 1];
                    if (this._activeTarget !== r) {
                        this._activate(r);
                    }
                    return;
                }
                if (this._activeTarget && e < this._offsets[0] && this._offsets[0] > 0) {
                    this._activeTarget = null;
                    this._clear();
                    return;
                }
                for (var o = this._offsets.length; o--; ) {
                    var s = this._activeTarget !== this._targets[o] && e >= this._offsets[o] && (this._offsets[o + 1] === undefined || e < this._offsets[o + 1]);
                    if (s) {
                        this._activate(this._targets[o]);
                    }
                }
            };
            f.prototype._activate = function t(e) {
                this._activeTarget = e;
                this._clear();
                var i = this._selector.split(",");
                i = i.map(function(t) {
                    return t + '[data-target="' + e + '"],' + (t + '[href="' + e + '"]');
                });
                var r = n(i.join(","));
                if (r.hasClass(p.DROPDOWN_ITEM)) {
                    r.closest(g.DROPDOWN).find(g.DROPDOWN_TOGGLE).addClass(p.ACTIVE);
                    r.addClass(p.ACTIVE);
                } else {
                    r.addClass(p.ACTIVE);
                    r.parents(g.NAV_LIST_GROUP).prev(g.NAV_LINKS + ", " + g.LIST_ITEMS).addClass(p.ACTIVE);
                }
                n(this._scrollElement).trigger(d.ACTIVATE, {
                    relatedTarget: e
                });
            };
            f.prototype._clear = function t() {
                n(this._selector).filter(g.ACTIVE).removeClass(p.ACTIVE);
            };
            f._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = n(this).data(a);
                    var r = (typeof i === "undefined" ? "undefined" : t(i)) === "object" && i;
                    if (!e) {
                        e = new f(this, r);
                        n(this).data(a, e);
                    }
                    if (typeof i === "string") {
                        if (e[i] === undefined) {
                            throw new Error('No method named "' + i + '"');
                        }
                        e[i]();
                    }
                });
            };
            e(f, null, [ {
                key: "VERSION",
                get: function t() {
                    return s;
                }
            }, {
                key: "Default",
                get: function t() {
                    return u;
                }
            } ]);
            return f;
        }();
        n(window).on(d.LOAD_DATA_API, function() {
            var t = n.makeArray(n(g.DATA_SPY));
            for (var e = t.length; e--; ) {
                var i = n(t[e]);
                v._jQueryInterface.call(i, i.data());
            }
        });
        n.fn[i] = v._jQueryInterface;
        n.fn[i].Constructor = v;
        n.fn[i].noConflict = function() {
            n.fn[i] = c;
            return v._jQueryInterface;
        };
        return v;
    }(jQuery);
    var d = function(t) {
        var n = "tab";
        var i = "4.0.0-beta";
        var s = "bs.tab";
        var a = "." + s;
        var l = ".data-api";
        var f = t.fn[n];
        var c = 150;
        var u = {
            HIDE: "hide" + a,
            HIDDEN: "hidden" + a,
            SHOW: "show" + a,
            SHOWN: "shown" + a,
            CLICK_DATA_API: "click" + a + l
        };
        var h = {
            DROPDOWN_MENU: "dropdown-menu",
            ACTIVE: "active",
            DISABLED: "disabled",
            FADE: "fade",
            SHOW: "show"
        };
        var d = {
            DROPDOWN: ".dropdown",
            NAV_LIST_GROUP: ".nav, .list-group",
            ACTIVE: ".active",
            DATA_TOGGLE: '[data-toggle="tab"], [data-toggle="pill"], [data-toggle="list"]',
            DROPDOWN_TOGGLE: ".dropdown-toggle",
            DROPDOWN_ACTIVE_CHILD: "> .dropdown-menu .active"
        };
        var p = function() {
            function n(t) {
                r(this, n);
                this._element = t;
            }
            n.prototype.show = function e() {
                var n = this;
                if (this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE && t(this._element).hasClass(h.ACTIVE) || t(this._element).hasClass(h.DISABLED)) {
                    return;
                }
                var i = void 0;
                var r = void 0;
                var s = t(this._element).closest(d.NAV_LIST_GROUP)[0];
                var a = o.getSelectorFromElement(this._element);
                if (s) {
                    r = t.makeArray(t(s).find(d.ACTIVE));
                    r = r[r.length - 1];
                }
                var l = t.Event(u.HIDE, {
                    relatedTarget: this._element
                });
                var f = t.Event(u.SHOW, {
                    relatedTarget: r
                });
                if (r) {
                    t(r).trigger(l);
                }
                t(this._element).trigger(f);
                if (f.isDefaultPrevented() || l.isDefaultPrevented()) {
                    return;
                }
                if (a) {
                    i = t(a)[0];
                }
                this._activate(this._element, s);
                var c = function e() {
                    var i = t.Event(u.HIDDEN, {
                        relatedTarget: n._element
                    });
                    var o = t.Event(u.SHOWN, {
                        relatedTarget: r
                    });
                    t(r).trigger(i);
                    t(n._element).trigger(o);
                };
                if (i) {
                    this._activate(i, i.parentNode, c);
                } else {
                    c();
                }
            };
            n.prototype.dispose = function e() {
                t.removeData(this._element, s);
                this._element = null;
            };
            n.prototype._activate = function e(n, i, r) {
                var s = this;
                var a = t(i).find(d.ACTIVE)[0];
                var l = r && o.supportsTransitionEnd() && a && t(a).hasClass(h.FADE);
                var f = function t() {
                    return s._transitionComplete(n, a, l, r);
                };
                if (a && l) {
                    t(a).one(o.TRANSITION_END, f).emulateTransitionEnd(c);
                } else {
                    f();
                }
                if (a) {
                    t(a).removeClass(h.SHOW);
                }
            };
            n.prototype._transitionComplete = function e(n, i, r, s) {
                if (i) {
                    t(i).removeClass(h.ACTIVE);
                    var a = t(i.parentNode).find(d.DROPDOWN_ACTIVE_CHILD)[0];
                    if (a) {
                        t(a).removeClass(h.ACTIVE);
                    }
                    i.setAttribute("aria-expanded", false);
                }
                t(n).addClass(h.ACTIVE);
                n.setAttribute("aria-expanded", true);
                if (r) {
                    o.reflow(n);
                    t(n).addClass(h.SHOW);
                } else {
                    t(n).removeClass(h.FADE);
                }
                if (n.parentNode && t(n.parentNode).hasClass(h.DROPDOWN_MENU)) {
                    var l = t(n).closest(d.DROPDOWN)[0];
                    if (l) {
                        t(l).find(d.DROPDOWN_TOGGLE).addClass(h.ACTIVE);
                    }
                    n.setAttribute("aria-expanded", true);
                }
                if (s) {
                    s();
                }
            };
            n._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = t(this);
                    var r = e.data(s);
                    if (!r) {
                        r = new n(this);
                        e.data(s, r);
                    }
                    if (typeof i === "string") {
                        if (r[i] === undefined) {
                            throw new Error('No method named "' + i + '"');
                        }
                        r[i]();
                    }
                });
            };
            e(n, null, [ {
                key: "VERSION",
                get: function t() {
                    return i;
                }
            } ]);
            return n;
        }();
        t(document).on(u.CLICK_DATA_API, d.DATA_TOGGLE, function(e) {
            e.preventDefault();
            p._jQueryInterface.call(t(this), "show");
        });
        t.fn[n] = p._jQueryInterface;
        t.fn[n].Constructor = p;
        t.fn[n].noConflict = function() {
            t.fn[n] = f;
            return p._jQueryInterface;
        };
        return p;
    }(jQuery);
    var p = function(n) {
        if (typeof Popper === "undefined") {
            throw new Error("Bootstrap tooltips require Popper.js (https://popper.js.org)");
        }
        var i = "tooltip";
        var s = "4.0.0-beta";
        var a = "bs.tooltip";
        var l = "." + a;
        var f = n.fn[i];
        var c = 150;
        var u = "bs-tooltip";
        var h = new RegExp("(^|\\s)" + u + "\\S+", "g");
        var d = {
            animation: "boolean",
            template: "string",
            title: "(string|element|function)",
            trigger: "string",
            delay: "(number|object)",
            html: "boolean",
            selector: "(string|boolean)",
            placement: "(string|function)",
            offset: "(number|string)",
            container: "(string|element|boolean)",
            fallbackPlacement: "(string|array)"
        };
        var p = {
            AUTO: "auto",
            TOP: "top",
            RIGHT: "right",
            BOTTOM: "bottom",
            LEFT: "left"
        };
        var g = {
            animation: true,
            template: '<div class="tooltip" role="tooltip">' + '<div class="arrow"></div>' + '<div class="tooltip-inner"></div></div>',
            trigger: "hover focus",
            title: "",
            delay: 0,
            html: false,
            selector: false,
            placement: "top",
            offset: 0,
            container: false,
            fallbackPlacement: "flip"
        };
        var m = {
            SHOW: "show",
            OUT: "out"
        };
        var v = {
            HIDE: "hide" + l,
            HIDDEN: "hidden" + l,
            SHOW: "show" + l,
            SHOWN: "shown" + l,
            INSERTED: "inserted" + l,
            CLICK: "click" + l,
            FOCUSIN: "focusin" + l,
            FOCUSOUT: "focusout" + l,
            MOUSEENTER: "mouseenter" + l,
            MOUSELEAVE: "mouseleave" + l
        };
        var _ = {
            FADE: "fade",
            SHOW: "show"
        };
        var E = {
            TOOLTIP: ".tooltip",
            TOOLTIP_INNER: ".tooltip-inner",
            ARROW: ".arrow"
        };
        var T = {
            HOVER: "hover",
            FOCUS: "focus",
            CLICK: "click",
            MANUAL: "manual"
        };
        var y = function() {
            function f(t, e) {
                r(this, f);
                this._isEnabled = true;
                this._timeout = 0;
                this._hoverState = "";
                this._activeTrigger = {};
                this._popper = null;
                this.element = t;
                this.config = this._getConfig(e);
                this.tip = null;
                this._setListeners();
            }
            f.prototype.enable = function t() {
                this._isEnabled = true;
            };
            f.prototype.disable = function t() {
                this._isEnabled = false;
            };
            f.prototype.toggleEnabled = function t() {
                this._isEnabled = !this._isEnabled;
            };
            f.prototype.toggle = function t(e) {
                if (e) {
                    var i = this.constructor.DATA_KEY;
                    var r = n(e.currentTarget).data(i);
                    if (!r) {
                        r = new this.constructor(e.currentTarget, this._getDelegateConfig());
                        n(e.currentTarget).data(i, r);
                    }
                    r._activeTrigger.click = !r._activeTrigger.click;
                    if (r._isWithActiveTrigger()) {
                        r._enter(null, r);
                    } else {
                        r._leave(null, r);
                    }
                } else {
                    if (n(this.getTipElement()).hasClass(_.SHOW)) {
                        this._leave(null, this);
                        return;
                    }
                    this._enter(null, this);
                }
            };
            f.prototype.dispose = function t() {
                clearTimeout(this._timeout);
                n.removeData(this.element, this.constructor.DATA_KEY);
                n(this.element).off(this.constructor.EVENT_KEY);
                n(this.element).closest(".modal").off("hide.bs.modal");
                if (this.tip) {
                    n(this.tip).remove();
                }
                this._isEnabled = null;
                this._timeout = null;
                this._hoverState = null;
                this._activeTrigger = null;
                if (this._popper !== null) {
                    this._popper.destroy();
                }
                this._popper = null;
                this.element = null;
                this.config = null;
                this.tip = null;
            };
            f.prototype.show = function t() {
                var e = this;
                if (n(this.element).css("display") === "none") {
                    throw new Error("Please use show on visible elements");
                }
                var i = n.Event(this.constructor.Event.SHOW);
                if (this.isWithContent() && this._isEnabled) {
                    n(this.element).trigger(i);
                    var r = n.contains(this.element.ownerDocument.documentElement, this.element);
                    if (i.isDefaultPrevented() || !r) {
                        return;
                    }
                    var s = this.getTipElement();
                    var a = o.getUID(this.constructor.NAME);
                    s.setAttribute("id", a);
                    this.element.setAttribute("aria-describedby", a);
                    this.setContent();
                    if (this.config.animation) {
                        n(s).addClass(_.FADE);
                    }
                    var l = typeof this.config.placement === "function" ? this.config.placement.call(this, s, this.element) : this.config.placement;
                    var c = this._getAttachment(l);
                    this.addAttachmentClass(c);
                    var u = this.config.container === false ? document.body : n(this.config.container);
                    n(s).data(this.constructor.DATA_KEY, this);
                    if (!n.contains(this.element.ownerDocument.documentElement, this.tip)) {
                        n(s).appendTo(u);
                    }
                    n(this.element).trigger(this.constructor.Event.INSERTED);
                    this._popper = new Popper(this.element, s, {
                        placement: c,
                        modifiers: {
                            offset: {
                                offset: this.config.offset
                            },
                            flip: {
                                behavior: this.config.fallbackPlacement
                            },
                            arrow: {
                                element: E.ARROW
                            }
                        },
                        onCreate: function t(n) {
                            if (n.originalPlacement !== n.placement) {
                                e._handlePopperPlacementChange(n);
                            }
                        },
                        onUpdate: function t(n) {
                            e._handlePopperPlacementChange(n);
                        }
                    });
                    n(s).addClass(_.SHOW);
                    if ("ontouchstart" in document.documentElement) {
                        n("body").children().on("mouseover", null, n.noop);
                    }
                    var h = function t() {
                        if (e.config.animation) {
                            e._fixTransition();
                        }
                        var i = e._hoverState;
                        e._hoverState = null;
                        n(e.element).trigger(e.constructor.Event.SHOWN);
                        if (i === m.OUT) {
                            e._leave(null, e);
                        }
                    };
                    if (o.supportsTransitionEnd() && n(this.tip).hasClass(_.FADE)) {
                        n(this.tip).one(o.TRANSITION_END, h).emulateTransitionEnd(f._TRANSITION_DURATION);
                    } else {
                        h();
                    }
                }
            };
            f.prototype.hide = function t(e) {
                var i = this;
                var r = this.getTipElement();
                var s = n.Event(this.constructor.Event.HIDE);
                var a = function t() {
                    if (i._hoverState !== m.SHOW && r.parentNode) {
                        r.parentNode.removeChild(r);
                    }
                    i._cleanTipClass();
                    i.element.removeAttribute("aria-describedby");
                    n(i.element).trigger(i.constructor.Event.HIDDEN);
                    if (i._popper !== null) {
                        i._popper.destroy();
                    }
                    if (e) {
                        e();
                    }
                };
                n(this.element).trigger(s);
                if (s.isDefaultPrevented()) {
                    return;
                }
                n(r).removeClass(_.SHOW);
                if ("ontouchstart" in document.documentElement) {
                    n("body").children().off("mouseover", null, n.noop);
                }
                this._activeTrigger[T.CLICK] = false;
                this._activeTrigger[T.FOCUS] = false;
                this._activeTrigger[T.HOVER] = false;
                if (o.supportsTransitionEnd() && n(this.tip).hasClass(_.FADE)) {
                    n(r).one(o.TRANSITION_END, a).emulateTransitionEnd(c);
                } else {
                    a();
                }
                this._hoverState = "";
            };
            f.prototype.update = function t() {
                if (this._popper !== null) {
                    this._popper.scheduleUpdate();
                }
            };
            f.prototype.isWithContent = function t() {
                return Boolean(this.getTitle());
            };
            f.prototype.addAttachmentClass = function t(e) {
                n(this.getTipElement()).addClass(u + "-" + e);
            };
            f.prototype.getTipElement = function t() {
                return this.tip = this.tip || n(this.config.template)[0];
            };
            f.prototype.setContent = function t() {
                var e = n(this.getTipElement());
                this.setElementContent(e.find(E.TOOLTIP_INNER), this.getTitle());
                e.removeClass(_.FADE + " " + _.SHOW);
            };
            f.prototype.setElementContent = function e(i, r) {
                var o = this.config.html;
                if ((typeof r === "undefined" ? "undefined" : t(r)) === "object" && (r.nodeType || r.jquery)) {
                    if (o) {
                        if (!n(r).parent().is(i)) {
                            i.empty().append(r);
                        }
                    } else {
                        i.text(n(r).text());
                    }
                } else {
                    i[o ? "html" : "text"](r);
                }
            };
            f.prototype.getTitle = function t() {
                var e = this.element.getAttribute("data-original-title");
                if (!e) {
                    e = typeof this.config.title === "function" ? this.config.title.call(this.element) : this.config.title;
                }
                return e;
            };
            f.prototype._getAttachment = function t(e) {
                return p[e.toUpperCase()];
            };
            f.prototype._setListeners = function t() {
                var e = this;
                var i = this.config.trigger.split(" ");
                i.forEach(function(t) {
                    if (t === "click") {
                        n(e.element).on(e.constructor.Event.CLICK, e.config.selector, function(t) {
                            return e.toggle(t);
                        });
                    } else if (t !== T.MANUAL) {
                        var i = t === T.HOVER ? e.constructor.Event.MOUSEENTER : e.constructor.Event.FOCUSIN;
                        var r = t === T.HOVER ? e.constructor.Event.MOUSELEAVE : e.constructor.Event.FOCUSOUT;
                        n(e.element).on(i, e.config.selector, function(t) {
                            return e._enter(t);
                        }).on(r, e.config.selector, function(t) {
                            return e._leave(t);
                        });
                    }
                    n(e.element).closest(".modal").on("hide.bs.modal", function() {
                        return e.hide();
                    });
                });
                if (this.config.selector) {
                    this.config = n.extend({}, this.config, {
                        trigger: "manual",
                        selector: ""
                    });
                } else {
                    this._fixTitle();
                }
            };
            f.prototype._fixTitle = function e() {
                var n = t(this.element.getAttribute("data-original-title"));
                if (this.element.getAttribute("title") || n !== "string") {
                    this.element.setAttribute("data-original-title", this.element.getAttribute("title") || "");
                    this.element.setAttribute("title", "");
                }
            };
            f.prototype._enter = function t(e, i) {
                var r = this.constructor.DATA_KEY;
                i = i || n(e.currentTarget).data(r);
                if (!i) {
                    i = new this.constructor(e.currentTarget, this._getDelegateConfig());
                    n(e.currentTarget).data(r, i);
                }
                if (e) {
                    i._activeTrigger[e.type === "focusin" ? T.FOCUS : T.HOVER] = true;
                }
                if (n(i.getTipElement()).hasClass(_.SHOW) || i._hoverState === m.SHOW) {
                    i._hoverState = m.SHOW;
                    return;
                }
                clearTimeout(i._timeout);
                i._hoverState = m.SHOW;
                if (!i.config.delay || !i.config.delay.show) {
                    i.show();
                    return;
                }
                i._timeout = setTimeout(function() {
                    if (i._hoverState === m.SHOW) {
                        i.show();
                    }
                }, i.config.delay.show);
            };
            f.prototype._leave = function t(e, i) {
                var r = this.constructor.DATA_KEY;
                i = i || n(e.currentTarget).data(r);
                if (!i) {
                    i = new this.constructor(e.currentTarget, this._getDelegateConfig());
                    n(e.currentTarget).data(r, i);
                }
                if (e) {
                    i._activeTrigger[e.type === "focusout" ? T.FOCUS : T.HOVER] = false;
                }
                if (i._isWithActiveTrigger()) {
                    return;
                }
                clearTimeout(i._timeout);
                i._hoverState = m.OUT;
                if (!i.config.delay || !i.config.delay.hide) {
                    i.hide();
                    return;
                }
                i._timeout = setTimeout(function() {
                    if (i._hoverState === m.OUT) {
                        i.hide();
                    }
                }, i.config.delay.hide);
            };
            f.prototype._isWithActiveTrigger = function t() {
                for (var e in this._activeTrigger) {
                    if (this._activeTrigger[e]) {
                        return true;
                    }
                }
                return false;
            };
            f.prototype._getConfig = function t(e) {
                e = n.extend({}, this.constructor.Default, n(this.element).data(), e);
                if (e.delay && typeof e.delay === "number") {
                    e.delay = {
                        show: e.delay,
                        hide: e.delay
                    };
                }
                if (e.title && typeof e.title === "number") {
                    e.title = e.title.toString();
                }
                if (e.content && typeof e.content === "number") {
                    e.content = e.content.toString();
                }
                o.typeCheckConfig(i, e, this.constructor.DefaultType);
                return e;
            };
            f.prototype._getDelegateConfig = function t() {
                var e = {};
                if (this.config) {
                    for (var n in this.config) {
                        if (this.constructor.Default[n] !== this.config[n]) {
                            e[n] = this.config[n];
                        }
                    }
                }
                return e;
            };
            f.prototype._cleanTipClass = function t() {
                var e = n(this.getTipElement());
                var i = e.attr("class").match(h);
                if (i !== null && i.length > 0) {
                    e.removeClass(i.join(""));
                }
            };
            f.prototype._handlePopperPlacementChange = function t(e) {
                this._cleanTipClass();
                this.addAttachmentClass(this._getAttachment(e.placement));
            };
            f.prototype._fixTransition = function t() {
                var e = this.getTipElement();
                var i = this.config.animation;
                if (e.getAttribute("x-placement") !== null) {
                    return;
                }
                n(e).removeClass(_.FADE);
                this.config.animation = false;
                this.hide();
                this.show();
                this.config.animation = i;
            };
            f._jQueryInterface = function e(i) {
                return this.each(function() {
                    var e = n(this).data(a);
                    var r = (typeof i === "undefined" ? "undefined" : t(i)) === "object" && i;
                    if (!e && /dispose|hide/.test(i)) {
                        return;
                    }
                    if (!e) {
                        e = new f(this, r);
                        n(this).data(a, e);
                    }
                    if (typeof i === "string") {
                        if (e[i] === undefined) {
                            throw new Error('No method named "' + i + '"');
                        }
                        e[i]();
                    }
                });
            };
            e(f, null, [ {
                key: "VERSION",
                get: function t() {
                    return s;
                }
            }, {
                key: "Default",
                get: function t() {
                    return g;
                }
            }, {
                key: "NAME",
                get: function t() {
                    return i;
                }
            }, {
                key: "DATA_KEY",
                get: function t() {
                    return a;
                }
            }, {
                key: "Event",
                get: function t() {
                    return v;
                }
            }, {
                key: "EVENT_KEY",
                get: function t() {
                    return l;
                }
            }, {
                key: "DefaultType",
                get: function t() {
                    return d;
                }
            } ]);
            return f;
        }();
        n.fn[i] = y._jQueryInterface;
        n.fn[i].Constructor = y;
        n.fn[i].noConflict = function() {
            n.fn[i] = f;
            return y._jQueryInterface;
        };
        return y;
    }(jQuery);
    var g = function(o) {
        var s = "popover";
        var a = "4.0.0-beta";
        var l = "bs.popover";
        var f = "." + l;
        var c = o.fn[s];
        var u = "bs-popover";
        var h = new RegExp("(^|\\s)" + u + "\\S+", "g");
        var d = o.extend({}, p.Default, {
            placement: "right",
            trigger: "click",
            content: "",
            template: '<div class="popover" role="tooltip">' + '<div class="arrow"></div>' + '<h3 class="popover-header"></h3>' + '<div class="popover-body"></div></div>'
        });
        var g = o.extend({}, p.DefaultType, {
            content: "(string|element|function)"
        });
        var m = {
            FADE: "fade",
            SHOW: "show"
        };
        var v = {
            TITLE: ".popover-header",
            CONTENT: ".popover-body"
        };
        var _ = {
            HIDE: "hide" + f,
            HIDDEN: "hidden" + f,
            SHOW: "show" + f,
            SHOWN: "shown" + f,
            INSERTED: "inserted" + f,
            CLICK: "click" + f,
            FOCUSIN: "focusin" + f,
            FOCUSOUT: "focusout" + f,
            MOUSEENTER: "mouseenter" + f,
            MOUSELEAVE: "mouseleave" + f
        };
        var E = function(c) {
            i(p, c);
            function p() {
                r(this, p);
                return n(this, c.apply(this, arguments));
            }
            p.prototype.isWithContent = function t() {
                return this.getTitle() || this._getContent();
            };
            p.prototype.addAttachmentClass = function t(e) {
                o(this.getTipElement()).addClass(u + "-" + e);
            };
            p.prototype.getTipElement = function t() {
                return this.tip = this.tip || o(this.config.template)[0];
            };
            p.prototype.setContent = function t() {
                var e = o(this.getTipElement());
                this.setElementContent(e.find(v.TITLE), this.getTitle());
                this.setElementContent(e.find(v.CONTENT), this._getContent());
                e.removeClass(m.FADE + " " + m.SHOW);
            };
            p.prototype._getContent = function t() {
                return this.element.getAttribute("data-content") || (typeof this.config.content === "function" ? this.config.content.call(this.element) : this.config.content);
            };
            p.prototype._cleanTipClass = function t() {
                var e = o(this.getTipElement());
                var n = e.attr("class").match(h);
                if (n !== null && n.length > 0) {
                    e.removeClass(n.join(""));
                }
            };
            p._jQueryInterface = function e(n) {
                return this.each(function() {
                    var e = o(this).data(l);
                    var i = (typeof n === "undefined" ? "undefined" : t(n)) === "object" ? n : null;
                    if (!e && /destroy|hide/.test(n)) {
                        return;
                    }
                    if (!e) {
                        e = new p(this, i);
                        o(this).data(l, e);
                    }
                    if (typeof n === "string") {
                        if (e[n] === undefined) {
                            throw new Error('No method named "' + n + '"');
                        }
                        e[n]();
                    }
                });
            };
            e(p, null, [ {
                key: "VERSION",
                get: function t() {
                    return a;
                }
            }, {
                key: "Default",
                get: function t() {
                    return d;
                }
            }, {
                key: "NAME",
                get: function t() {
                    return s;
                }
            }, {
                key: "DATA_KEY",
                get: function t() {
                    return l;
                }
            }, {
                key: "Event",
                get: function t() {
                    return _;
                }
            }, {
                key: "EVENT_KEY",
                get: function t() {
                    return f;
                }
            }, {
                key: "DefaultType",
                get: function t() {
                    return g;
                }
            } ]);
            return p;
        }(p);
        o.fn[s] = E._jQueryInterface;
        o.fn[s].Constructor = E;
        o.fn[s].noConflict = function() {
            o.fn[s] = c;
            return E._jQueryInterface;
        };
        return E;
    }(jQuery);
})();

$(function() {
    var t = $("#addEmail"), e = $("#addSimpleXmlToCsv"), n = $("#addFTPDownload"), i = $("#addHeadline");
    if (t.length) {
        t.off("click").on("click", function() {
            var t = $(this).data("class");
            if ($(document).find("#AppendScripts .AppendScript.EmailScript").length === 0) {
                var e = $("#" + t).clone(), n = '<div class="form-group AppendScript EmailScript">' + e.html() + "</div>";
                $("#AppendScripts").append(n);
            }
        });
    }
    if (e.length) {
        e.off("click").on("click", function() {
            var t = $(this).data("class");
            if ($(document).find("#AppendScripts .AppendScript.SimpleXmlToCsvScript").length === 0) {
                var e = $("#" + t).clone(), n = '<div class="form-group AppendScript SimpleXmlToCsvScript">' + e.html() + "</div>";
                $("#AppendScripts").append(n);
            }
        });
    }
    if (n.length) {
        n.off("click").on("click", function() {
            var t = $(this).data("class");
            if ($(document).find("#AppendScripts .AppendScript.FTPDownloadScript").length === 0) {
                var e = $("#" + t).clone(), n = '<div class="form-group AppendScript FTPDownloadScript">' + e.html() + "</div>";
                $("#AppendScripts").append(n);
            }
        });
    }
    if (i.length) {
        i.off("click").on("click", function() {
            var t = $(this).data("class");
            if ($(document).find("#AppendScripts .AppendScript.HeadlineScript").length === 0) {
                var e = $("#" + t).clone(), n = '<div class="form-group AppendScript HeadlineScript">' + e.html() + "</div>";
                $("#AppendScripts").append(n);
            }
        });
    }
    $(document).off("click").on("click", "#AppendScripts .AppendScript button", function() {
        $(this).parents(".AppendScript").remove();
        return false;
    });
});

$(function() {
    $(document).on("click", "button.add", function() {
        var t = $(document).find("#js-mapping-wrap .js-mapping-field").size();
        var e = $("#js-mapping-default").clone(), n = e.html().replace(/###COUNT###/g, t);
        $("#js-mapping-wrap").append(n);
    }).on("click", "button.remove", function(t) {
        t.preventDefault();
        $(this).parent().parent().remove();
    });
});
//# sourceMappingURL=App.js.map