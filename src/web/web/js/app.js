function showCookieConsent() {
  const cookieConsent = document.getElementById("yn-cookies");
  if (cookieConsent) {
    cookieConsent.classList.add("yn-cookies--show");
  }
}

function hideCookieConsent() {
  const cookieConsent = document.getElementById("yn-cookies");
  if (cookieConsent) {
    cookieConsent.classList.remove("yn-cookies--show");
  }
}

function ynCookiesShowPage(page) {
  const hidePages = document.querySelectorAll("[data-yn-cookie-page]");
  for (let i = 0; i < hidePages.length; i++) {
    hidePages[i].classList.remove("yn-cookies__page--visible");
    hidePages[i].classList.add("yn-cookies__page--hidden");
  }

  const showPage = document.querySelector(`[data-yn-cookie-page="${page}"]`);

  if (showPage) {
    showPage.classList.remove("yn-cookies__page--hidden");
    showPage.classList.add("yn-cookies__page--visible");
  }
}

function ynSetCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  const expires = `expires=${d.toUTCString()}`;
  document.cookie = `${cname}=${cvalue};${expires};path=/`;
}

function ynGetCookie(cname) {
  const name = `${cname}=`;
  const decodedCookie = decodeURIComponent(document.cookie);
  const ca = decodedCookie.split(";");
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) === " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) === 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function addScripts(target, scripts) {
  for (let i = 0; i < scripts.length; i++) {
    const existingScript = document.querySelector(
      `script[src="${scripts[i]}"]`
    );

    if (!existingScript) {
      const script = document.createElement("script");
      script.src = scripts[i];

      target.appendChild(script);
    }
  }
}

function ynCheckForCookieConsents() {
  const cookie = ynGetCookie("ynfinite-cookies");
  if (cookie) {
    const cookieData = JSON.parse(cookie);
    if (cookieData.done === true) {
      hideCookieConsent();
    } else {
      showCookieConsent();
    }
  } else {
    showCookieConsent();
  }
}

function ynSetCookieSettings() {
  const form = document.querySelector("#yn-cookies-form");
  const cookie = {};

  if (form) {
    const formData = new FormData(form);
    cookie.activeScripts = formData.getAll("activatedScripts[]");
  }

  cookie.done = true;

  ynSetCookie("ynfinite-cookies", JSON.stringify(cookie), 365);
  window.location.reload();
}

function ynSetDefaultCookieSettings() {
  const defaultScriptElements = document.querySelectorAll(
    "[data-yn-default='1'] input"
  );

  const cookie = {};

  if (defaultScriptElements.length > 0) {
    const defaultScripts = [];
    for (let i = 0; i < defaultScriptElements.length; i += 1) {
      defaultScripts.push(defaultScriptElements[i].value);
    }

    cookie.activeScripts = defaultScripts;
  }
  cookie.done = true;

  ynSetCookie("ynfinite-cookies", JSON.stringify(cookie), 365);
  window.location.reload();
}

function ynAcceptCookie(id) {
  let cookie = ynGetCookie("ynfinite-cookies");
  cookie = JSON.parse(cookie);
  const index = cookie.activeScripts.findIndex((s) => s === id);
  if (index === -1) {
    cookie.activeScripts.push(id);
  }
  ynSetCookie("ynfinite-cookies", JSON.stringify(cookie), 365);
  window.location.reload();
}

function ynDeclineCookie(id) {
  let cookie = ynGetCookie("ynfinite-cookies");
  cookie = JSON.parse(cookie);
  const index = cookie.activeScripts.findIndex((s) => s === id);
  if (index > -1) {
    cookie.activeScripts.splice(index, 1);
  }
  ynSetCookie("ynfinite-cookies", JSON.stringify(cookie), 365);
  window.location.reload();
}

document.addEventListener("DOMContentLoaded", () => {
  ynCheckForCookieConsents();
});

function handleResponse(form, response) {
  response.json().then((data) => {
    const { inline, redirect, type } = data;
    if (type === "information") {
      form.innerHTML = inline[0].template;
    }
    if (type === "redirect") {
      window.location.replace(redirect);
    }
    if (type === "inline") {
      inline.forEach((element) => {
        if (element.selector) {
          const selectorName = element.selector.substring(1);
          if (element.selector.charAt(0) === ".") {
            const containers = document.getElementsByClassName(selectorName);
            containers.forEach((container) => {
              container.innerHTML = element.template;
            });
          }
          if (element.selector.charAt(0) === "#") {
            const container = document.getElementsByClassName(selectorName);
            container.innerHTML = element.template;
          }
        }
      });
    }
  });
}

function functSubmit(event) {
  const form = event.target;

  const formData = new FormData(form);
  const formId = event.target.getAttribute("data-ynfiniteid");
  const lang = event.target.getAttribute("data-ynfinitelang");
  const contentId = event.target.getAttribute("data-ynfinite-content");
  const sectionId = event.target.getAttribute("data-ynfinite-section");
  formData.append("formId", formId);
  formData.append("lang", lang);
  formData.append("contentId", contentId);
  formData.append("sectionId", sectionId);
  formData.append("action", "submit");

  fetch(form.action, {
    method: form.method,
    body: formData,
  }).then(handleResponse.bind(null, form));

  event.preventDefault();
}

function functOnInput(event) {
  const { form } = event.target;
  const formData = new FormData(form);
  const formId = form.getAttribute("data-ynfiniteid");
  const lang = form.getAttribute("data-ynfinitelang");
  formData.append("formId", formId);
  formData.append("lang", lang);
  formData.append("action", "change");
  if (
    (event.inputType === "insertText" && event.target.value.length > 3) ||
    event.inputType !== "insertText"
  ) {
    fetch(form.action, {
      method: form.method,
      body: formData,
    }).then(handleResponse.bind(null, form));
  }

  event.preventDefault();
}

const forms = document.querySelectorAll('*[data-ynfiniteform="true"]');
for (const form of forms) {
  form.addEventListener("submit", functSubmit);
}
const onChangeForms = document.querySelectorAll(
  '*[data-ynfiniteform-onchange="true"]'
);

onChangeForms.forEach(function (form) {
  for (var i = 0, element; (element = form.elements[i++]); ) {
    element.addEventListener("input", functOnInput);
  }
});

const buttons = document.querySelectorAll(".gdpr-button");
for (const button of buttons) {
  button.addEventListener("click", function (e) {
    e.preventDefault();

    const formId = e.target.getAttribute("data-formid");
    const formAction = e.target.getAttribute("data-formaction");

    const form = document.getElementById(formId);
    const formData = new FormData(form);

    fetch(formAction || form.action, {
      method: form.method,
      body: formData,
    }).then((response) => {
      if (response.status === 200) {
        response.json().then((data) => {
          form.innerHTML = `<div class="response">${data.message}</div>`;
        });
      }
    });
  });
}
