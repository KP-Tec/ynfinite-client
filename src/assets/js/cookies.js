const YnfiniteCookies = {
	setup() {
		document.addEventListener('DOMContentLoaded', () => {
			const manager = document.getElementById('yn-cookies')

			if (manager) {
				if (manager.dataset.hideManager !== 'true') {
					this.ynCheckForCookieConsents()
				}

				document.getElementById('yn-cookies__allow-all')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynAcceptAllCookieSettings()
				})

				document.getElementById('yn-cookies__deny-all')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynDenyAllCookieSettings()
				})

				document.getElementById('yn-cookies__show-configuration')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynCookiesShowPage('configuration')
				})

				document.getElementById('yn-cookies__show-information')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynCookiesShowPage('information')
				})

				document.getElementById('yn-cookies__set-cookies')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynSetCookieSettings()
				})

				const changeSelectionButton = document.getElementById('yn-cookies__change-selection')

				changeSelectionButton &&
					changeSelectionButton.addEventListener('click', (e) => {
						e.preventDefault()
						this.ynCookiesShowPage('configuration')
						this.showCookieConsent(true)
					})

				const consentButtons = document.querySelectorAll('.yn-cookie-consent--okay')

				for (let i = 0; i < consentButtons.length; i++) {
					let button = consentButtons[i]
					button.addEventListener('click', (e) => {
						e.preventDefault()
						const id = button.dataset.consentId
						this.ynAcceptCookie(id)
					})
				}
			}
		})
	},

	showCookieConsent(hideBackButton = false) {
		const e = document.getElementById('yn-cookies')
		e && e.classList.add('yn-cookies--show')

		if (hideBackButton) {
			document.getElementById('yn-cookies__show-information').style.display = 'none'
		} else {
			document.getElementById('yn-cookies__show-information').style.display = 'block'
		}
	},

	hideCookieConsent() {
		const e = document.getElementById('yn-cookies')
		e && e.classList.remove('yn-cookies--show')
	},

	ynCookiesShowPage(e) {
		const t = document.querySelectorAll('[data-yn-cookie-page]')
		for (let e = 0; e < t.length; e++) t[e].classList.remove('yn-cookies__page--visible'), t[e].classList.add('yn-cookies__page--hidden')
		const n = document.querySelector(`[data-yn-cookie-page="${e}"]`)
		n && (n.classList.remove('yn-cookies__page--hidden'), n.classList.add('yn-cookies__page--visible'))
	},

	ynSetCookie(e, t, n) {
		const o = new Date()
		o.setTime(o.getTime() + 24 * n * 60 * 60 * 1e3)
		const i = `expires=${o.toUTCString()}`
		document.cookie = `${e}=${t};${i};path=/`
	},

	ynGetCookie(e) {
		const t = `${e}=`,
			n = decodeURIComponent(document.cookie).split(';')
		for (let e = 0; e < n.length; e++) {
			let o = n[e]
			for (; ' ' === o.charAt(0); ) o = o.substring(1)
			if (0 === o.indexOf(t)) return o.substring(t.length, o.length)
		}
		return ''
	},

	addScripts(e, t) {
		for (let n = 0; n < t.length; n++) {
			if (!document.querySelector(`script[src="${t[n]}"]`)) {
				const o = document.createElement('script')
				;(o.src = t[n]), e.appendChild(o)
			}
		}
	},

	ynCheckForCookieConsents() {
		let e = this.ynGetCookie('ynfinite-cookies')

		if (e) {
			e = JSON.parse(e)
			const manager = document.getElementById('yn-cookies')
			const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
			const diff = consents.filter((x) => !e.consents.includes(x))
			if (diff.length == 0) {
				this.hideCookieConsent()
			} else {
				this.showCookieConsent()
			}
		} else this.showCookieConsent()
	},

	ynSetCookieSettings() {
		const manager = document.getElementById('yn-cookies')
		const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
		const e = document.querySelector('#yn-cookies-form'),
			t = {}
		if (e) {
			const n = new FormData(e)
			t.givenConsents = n.getAll('givenConsents[]')
			t.consents = consents
		}
		;(t.done = !0), this.ynSetCookie('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynAcceptAllCookieSettings() {
		const manager = document.getElementById('yn-cookies')
		const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
		const t = { done: true, givenConsents: consents, consents: consents }
		this.ynSetCookie('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynDenyAllCookieSettings() {
		const manager = document.getElementById('yn-cookies')
		const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
		const t = { done: true, givenConsents: [], consents: consents }
		this.ynSetCookie('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynAcceptCookie(e) {
		let t = this.ynGetCookie('ynfinite-cookies')
		;-1 === (t = JSON.parse(t)).givenConsents.findIndex((t) => t === e) && t.givenConsents.push(e), this.ynSetCookie('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynDeclineCookie(e) {
		let t = this.ynGetCookie('ynfinite-cookies')
		const n = (t = JSON.parse(t)).givenConsents.findIndex((t) => t === e)
		n > -1 && t.givenConsents.splice(n, 1), this.ynSetCookie('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},
}

export default YnfiniteCookies
