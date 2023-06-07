import flatpickr from './node_modules/flatpickr/dist/flatpickr.min.js'
import weekSelect from './node_modules/flatpickr/dist/plugins/weekSelect/weekSelect.js'
import monthSelect from './node_modules/flatpickr/dist/plugins/monthSelect/index.js'
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

window.weekSelect = weekSelect
window.monthSelect = monthSelect
