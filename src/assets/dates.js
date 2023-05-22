import flatpickr from './node_modules/flatpickr/dist/flatpickr.min.js'
import { German } from './node_modules/flatpickr/dist/l10n/de.js'

flatpickr.localize(German)
flatpickr.setDefaults({
	disableMobile: true,
	minuteIncrement: 5,
	time_24hr: true,
	altInput: true,
	clickOpens: true,
	mode: 'single',
})
