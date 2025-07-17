const YnfiniteConsents = {
	setup() {
		window.addEventListener('DOMContentLoaded', () => {
			const manager = document.getElementById('yn-cookies')

			if (manager) {
				if (manager.dataset.hideManager !== 'true') {
					this.ynCheckForConsents()
				}

				document.getElementById('yn-cookies__allow-all')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynAcceptAllConsentSettings()
				})

				document.getElementById('yn-cookies__deny-all')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynDenyAllConsentSettings()
				})

				document.getElementById('yn-cookies__show-configuration')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynConsentShowPage('configuration')
				})

				document.getElementById('yn-cookies__show-information')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynConsentShowPage('information')
				})

				document.getElementById('yn-cookies__set-cookies')?.addEventListener('click', (e) => {
					e.preventDefault()
					this.ynSetConsentSettings()
				})

				document.querySelectorAll('.toggleConsent').forEach(function (item) {
					item.addEventListener('click', function () {
						item.querySelector('[id="' + item.getAttribute('for') + '"]').checked = !item.querySelector('[id="' + item.getAttribute('for') + '"]').checked
					})
				})

				const changeSelectionButton = document.querySelectorAll('.yn-cookies__change-selection').length >= 1 ? document.querySelectorAll('.yn-cookies__change-selection') : document.querySelectorAll('#yn-cookies__change-selection')

				Array.from(changeSelectionButton).forEach((element) => {
					element.addEventListener('click', (e) => {
						e.preventDefault()
						this.ynConsentShowPage('configuration')
						this.showConsent(true)
					})
				})

				const consentButtons = document.querySelectorAll('.yn-cookie-consent--okay')

				for (let i = 0; i < consentButtons.length; i++) {
					let button = consentButtons[i]
					button.addEventListener('click', (e) => {
						e.preventDefault()
						const id = button.dataset.consentId
						this.ynAcceptConsent(id)
					})
				}
			}
		})
	},

	showConsent(hideBackButton = false) {
		const e = document.getElementById('yn-cookies')
		e && e.classList.add('yn-cookies--show')

		if (hideBackButton) {
			document.getElementById('yn-cookies__show-information').style.display = 'none'
		} else {
			document.getElementById('yn-cookies__show-information').style.display = 'block'
		}
	},

	hideConsent() {
		const e = document.getElementById('yn-cookies')
		e && e.classList.remove('yn-cookies--show')
	},

	ynConsentShowPage(e) {
		const t = document.querySelectorAll('[data-yn-cookie-page]')
		for (let e = 0; e < t.length; e++) t[e].classList.remove('yn-cookies__page--visible'), t[e].classList.add('yn-cookies__page--hidden')
		const n = document.querySelector(`[data-yn-cookie-page="${e}"]`)
		n && (n.classList.remove('yn-cookies__page--hidden'), n.classList.add('yn-cookies__page--visible'))
	},

	ynSetConsent(e, t, n) {
		const o = new Date()
		o.setTime(o.getTime() + 24 * n * 60 * 60 * 1e3)
		const i = `expires=${o.toUTCString()}`
		document.cookie = `${e}=${t};${i};path=/`
	},

	ynGetConsent(e) {
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

	ynCheckForConsents() {
		let e = this.ynGetConsent('ynfinite-cookies')

		if (e) {
			e = JSON.parse(e)
			let oldConsents = e.consents
			if (oldConsents == undefined) {
				oldConsents = e.activeScripts
			}
			const manager = document.getElementById('yn-cookies')
			const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
			const diff = consents.filter((x) => !oldConsents.includes(x))
			if (diff.length == 0) {
				this.hideConsent()
			} else {
				this.showConsent()
			}
		} else this.showConsent()
	},

	ynSetConsentSettings() {
		const manager = document.getElementById('yn-cookies')
		const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
		const e = document.querySelector('#yn-cookies-form'),
			t = {}
		if (e) {
			const n = new FormData(e)
			t.activeScripts = n.getAll('activeScripts[]')
			t.consents = consents
		}
		;(t.done = !0), this.ynSetConsent('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynAcceptAllConsentSettings() {
		const manager = document.getElementById('yn-cookies')
		const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
		const t = { done: true, activeScripts: consents, consents: consents }
		this.ynSetConsent('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynDenyAllConsentSettings() {
		const manager = document.getElementById('yn-cookies')
		const consents = JSON.parse(manager.getAttribute('data-consents') || '[]')
		const t = { done: true, activeScripts: [], consents: consents }
		this.ynSetConsent('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynAcceptConsent(e) {
		let t = this.ynGetConsent('ynfinite-cookies')
		;-1 === (t = JSON.parse(t)).activeScripts.findIndex((t) => t === e) && t.activeScripts.push(e), this.ynSetConsent('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},

	ynDeclineConsent(e) {
		let t = this.ynGetConsent('ynfinite-cookies')
		const n = (t = JSON.parse(t)).activeScripts.findIndex((t) => t === e)
		n > -1 && t.activeScripts.splice(n, 1), this.ynSetConsent('ynfinite-cookies', JSON.stringify(t), 365), window.location.reload()
	},
}

export default YnfiniteConsents
