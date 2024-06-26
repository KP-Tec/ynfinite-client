const YnfiniteLogin = {
	setup() {
		const toggleDropdown = (dropdown) => {
			const content = dropdown.querySelector('.yn-dropdown__popup')
			if (content) {
				content.classList.toggle('hidden')
			}
		}

		const dropdowns = document.querySelectorAll('.yn-dropdown')
		dropdowns.forEach((dropdown) => {
			const dropdownButtons = dropdown.querySelectorAll('.yn-dropdown__button')
			dropdownButtons.forEach((button) => {
				button.addEventListener('click', () => {
					toggleDropdown(dropdown)
				})
			})
		})
	},
}

export default YnfiniteLogin
