import Accordion from 'accordion-js'

const YnfiniteAccordions = {
	setup() {
		const accordions = Array.from(document.querySelectorAll('.yn-accordions'))

		accordions.forEach((accordion) => {
			accordion._accordion = new Accordion(accordion, {
				duration: 400,
				showMultiple: false,
				elementClass: 'yn-accordion',
				triggerClass: 'yn-accordion__header',
				panelClass: 'yn-accordion__content',
			})

			const accButtons = accordion.querySelectorAll('.yn-accordion__header')

			accButtons.forEach((accButton) => {
				accButton.addEventListener('keydown', (event) => {
					if (event.key === 'Enter' || event.key === ' ') {
						event.preventDefault()
						accordion._accordion.toggle(accButton.getAttribute('index'))
					}
				})
			})
		})
	},
}

export default YnfiniteAccordions
