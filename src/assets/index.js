//SCSS Files
import './scss/cookieManager.scss'
import './scss/formGrid.scss'
import './scss/article.scss'

//JS Files
import './js/general'
import './js/lazysize'

const YnfiniteCookies = require('./js/cookies')
const YnfiniteForms = require('./js/forms')

YnfiniteCookies.setup()
YnfiniteForms.setup()

window.$_yn = {
    forms: {
        updateUrl: YnfiniteForms.updateUrl,
        repopulate: YnfiniteForms.repopulateForm,
        enable: YnfiniteForms.enableForm,
        disable: YnfiniteForms.disableForm,
        showResponse: YnfiniteForms.showResponse
    }
}
