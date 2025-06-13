const YnfiniteLanguageSwitch = {
	setup() {
		// Wait for DOM to be ready
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', () => this.init())
		} else {
			this.init()
		}
	},

	init() {
		const languageSwitchWrapper = document.querySelector('.yn-languageSwitch')

		if (!languageSwitchWrapper) return

		const languageSwitchButton = languageSwitchWrapper.querySelector('.yn-languageSwitch-button')

		if (!languageSwitchButton) return

		languageSwitchButton.addEventListener('click', (e) => {
			e.stopPropagation()
			languageSwitchWrapper.classList.toggle('yn-open')
		})
	},
}

export default YnfiniteLanguageSwitch
