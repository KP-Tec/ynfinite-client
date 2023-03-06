//JS Files
import './js/general'

import YnfiniteConsents from './js/cookies'
import YnfiniteForms from './js/forms'
import YnfiniteBotProtection from "./js/botprotection"

YnfiniteConsents.setup()
YnfiniteForms.setup()
YnfiniteBotProtection.setup()

window.$_yn = {
	forms: { 
		updateUrl: YnfiniteForms.updateUrl,
		repopulate: YnfiniteForms.repopulateForm,
		enable: YnfiniteForms.enableForm,
		disable: YnfiniteForms.disableForm,
		showResponse: YnfiniteForms.showResponse,
	},
}
