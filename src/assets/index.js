//JS Files
import './js/general'

import YnfiniteConsents from './js/cookies'
import YnfiniteForms from './js/forms'
import YnfiniteBotProtection from './js/botprotection'
import YnfiniteAccordions from './js/accordions'
import YnfiniteLogin from './js/login'
import YnfiniteFormSettings from './js/formSettings'

YnfiniteConsents.setup()
YnfiniteForms.setup()
YnfiniteBotProtection.setup()
YnfiniteAccordions.setup()
YnfiniteLogin.setup()
YnfiniteFormSettings.setup()

window.$_yn = {
	forms: {
		updateUrl: YnfiniteForms.updateUrl,
		repopulate: YnfiniteForms.repopulateForm,
		enable: YnfiniteForms.enableForm,
		disable: YnfiniteForms.disableForm,
		showResponse: YnfiniteForms.showResponse,
	},
}
