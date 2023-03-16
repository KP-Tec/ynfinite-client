import Accordion from 'accordion-js'

const YnfiniteAccordions = {
	setup() {
		const accordions = Array.from(document.querySelectorAll('.yn-accordions'))

		accordions.forEach((accordion) => {
			accordion._accordion = new Accordion(accordion, {
				duration: 400,
				showMultiple: true,
				elementClass: 'yn-accordion',
				triggerClass: 'yn-accordion__header',
				panelClass: 'yn-accordion__content',
			})
		})
	},
}

export default YnfiniteAccordions
