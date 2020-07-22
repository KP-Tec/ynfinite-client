function showCookieConsent() {
    const e = document.getElementById("yn-cookies");
    e && e.classList.add("yn-cookies--show")
}

function hideCookieConsent() {
    const e = document.getElementById("yn-cookies");
    e && e.classList.remove("yn-cookies--show")
}

function ynCookiesShowPage(e) {
    const t = document.querySelectorAll("[data-yn-cookie-page]");
    for (let e = 0; e < t.length; e++) t[e].classList.remove("yn-cookies__page--visible"), t[e].classList.add("yn-cookies__page--hidden");
    const n = document.querySelector(`[data-yn-cookie-page="${e}"]`);
    n && (n.classList.remove("yn-cookies__page--hidden"), n.classList.add("yn-cookies__page--visible"))
}

function ynSetCookie(e, t, n) {
    const o = new Date;
    o.setTime(o.getTime() + 24 * n * 60 * 60 * 1e3);
    const i = `expires=${o.toUTCString()}`;
    document.cookie = `${e}=${t};${i};path=/`
}

function ynGetCookie(e) {
    const t = `${e}=`,
        n = decodeURIComponent(document.cookie).split(";");
    for (let e = 0; e < n.length; e++) {
        let o = n[e];
        for (;
            " " === o.charAt(0);) o = o.substring(1);
        if (0 === o.indexOf(t)) return o.substring(t.length, o.length)
    }
    return ""
}

function addScripts(e, t) {
    for (let n = 0; n < t.length; n++) {
        if (!document.querySelector(`script[src="${t[n]}"]`)) {
            const o = document.createElement("script");
            o.src = t[n], e.appendChild(o)
        }
    }
}

function ynCheckForCookieConsents() {
    const e = ynGetCookie("ynfinite-cookies");
    if (e) {
        !0 === JSON.parse(e).done ? hideCookieConsent() : showCookieConsent()
    } else showCookieConsent()
}

function ynSetCookieSettings() {
    const e = document.querySelector("#yn-cookies-form"),
        t = {};
    if (e) {
        const n = new FormData(e);
        t.activeScripts = n.getAll("activatedScripts[]")
    }
    t.done = !0, ynSetCookie("ynfinite-cookies", JSON.stringify(t), 365), window.location.reload()
}

function ynSetDefaultCookieSettings() {
    const e = document.querySelectorAll("[data-yn-default='1'] input"),
        t = {};
    if (e.length > 0) {
        const n = [];
        for (let t = 0; t < e.length; t += 1) n.push(e[t].value);
        t.activeScripts = n
    }
    t.done = !0, ynSetCookie("ynfinite-cookies", JSON.stringify(t), 365), window.location.reload()
}

function ynAcceptCookie(e) {
    let t = ynGetCookie("ynfinite-cookies");
    -1 === (t = JSON.parse(t)).activeScripts.findIndex(t => t === e) && t.activeScripts.push(e), ynSetCookie("ynfinite-cookies", JSON.stringify(t), 365), window.location.reload()
}

function ynDeclineCookie(e) {
    let t = ynGetCookie("ynfinite-cookies");
    const n = (t = JSON.parse(t)).activeScripts.findIndex(t => t === e);
    n > -1 && t.activeScripts.splice(n, 1), ynSetCookie("ynfinite-cookies", JSON.stringify(t), 365), window.location.reload()
}

function handleResponse(e, t) {
    t.json().then(t => {
        const {inline: n, redirect: o, type: i} = t;
        "information" === i && (e.innerHTML = n[0].template), "redirect" === i && window.location.replace(o), "inline" === i && n.forEach(e => {
            if (e.selector) {
                const t = e.selector.substring(1);
                if ("." === e.selector.charAt(0)) {
                    document.getElementsByClassName(t).forEach(t => {
                        t.innerHTML = e.template
                    })
                }
                if ("#" === e.selector.charAt(0)) {
                    document.getElementsByClassName(t).innerHTML = e.template
                }
            }
        })
    })
}

function functSubmit(e) {
    const t = e.target,
        n = new FormData(t),
        o = e.target.getAttribute("data-ynfiniteid"),
        i = e.target.getAttribute("data-ynfinitelang"),
        c = e.target.getAttribute("data-ynfinite-content"),
        s = e.target.getAttribute("data-ynfinite-section");
    n.append("formId", o), n.append("lang", i), n.append("contentId", c), n.append("sectionId", s), n.append("action", "submit"), fetch(t.action, {
        method: t.method,
        body: n
    }).then(handleResponse.bind(null, t)), e.preventDefault()
}

function functOnInput(e) {
    const {form: t} = e.target, n = new FormData(t), o = t.getAttribute("data-ynfiniteid"),
        i = t.getAttribute("data-ynfinitelang");
    n.append("formId", o), n.append("lang", i), n.append("action", "change"), ("insertText" === e.inputType && e.target.value.length > 3 || "insertText" !== e.inputType) && fetch(t.action, {
        method: t.method,
        body: n
    }).then(handleResponse.bind(null, t)), e.preventDefault()
}

document.addEventListener("DOMContentLoaded", () => {
    ynCheckForCookieConsents()
});
const forms = document.querySelectorAll('*[data-ynfiniteform="true"]');
for (const e of forms) e.addEventListener("submit", functSubmit);
const onChangeForms = document.querySelectorAll('*[data-ynfiniteform-onchange="true"]');
onChangeForms.forEach(function (e) {
    for (var t, n = 0; t = e.elements[n++];) t.addEventListener("input", functOnInput)
});
const buttons = document.querySelectorAll(".gdpr-button");
for (const e of buttons) e.addEventListener("click", function (e) {
    e.preventDefault();
    const t = e.target.getAttribute("data-formid"),
        n = e.target.getAttribute("data-formaction"),
        o = document.getElementById(t),
        i = new FormData(o);
    fetch(n || o.action, {method: o.method, body: i}).then(e => {
        200 === e.status && e.json().then(e => {
            o.innerHTML = `<div class="response">${e.message}</div>`
        })
    })
});