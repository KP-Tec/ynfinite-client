//JS Files
import './js/general'
import './js/lazysize'

import YnfiniteConsents from './js/cookies'
import YnfiniteForms from './js/forms'

YnfiniteConsents.setup()
YnfiniteForms.setup()

window.$_yn = {
	forms: { 
		updateUrl: YnfiniteForms.updateUrl,
		repopulate: YnfiniteForms.repopulateForm,
		enable: YnfiniteForms.enableForm,
		disable: YnfiniteForms.disableForm,
		showResponse: YnfiniteForms.showResponse,
	},
}
