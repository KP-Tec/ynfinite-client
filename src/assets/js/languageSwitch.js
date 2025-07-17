const YnfiniteLanguageSwitch = {
	setup() {
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
