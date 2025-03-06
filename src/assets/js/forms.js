import { load } from '@fingerprintjs/botd'

const debug = false
const renderedKey = Math.random().toString(36).substring(2)
const focusedElements = []
let botD = undefined
let humanMovement = false
let botScore = 0

function checkHoneypot(form) {
	const honeypot_name = form.querySelector('input[name="yn_name"], [name="consents[]_v2"]')
	if (!honeypot_name.value) {
		honeypot_name.value = renderedKey
	}

	if (honeypot_name && honeypot_name.value !== renderedKey) {
		if (debug) {
			console.log('%cBot detected by name honeypot (added 100 Score)', 'color: red')
		}
		botScore += 100
	} else if (debug) {
		console.log('%cHoneypot (name) check passed', 'color: green')
	}

	const honeypot_mail = form.querySelector('input[name="confirm_email"]')
	if (!honeypot_mail.value) {
		honeypot_mail.value = 'my@email.com'
	}

	if (honeypot_mail && honeypot_mail.value !== 'my@email.com') {
		if (debug) {
			console.log('%cBot detected by mail honeypot (added 100 Score)', 'color: red')
		}
		botScore += 100
	} else if (debug) {
		console.log('%cHoneypot (mail) check passed', 'color: green')
	}

	const consent_honeypots = form.querySelectorAll('.yn_consents_v2')
	consent_honeypots.forEach((consent) => {
		const input = consent.querySelector('input[type="checkbox"]')
		if (input.checked) {
			if (debug) {
				console.log('%cBot detected by consent honeypot (added 100 Score)', 'color: red')
			}
			botScore += 100
		} else {
			consent.remove()
		}
	})
}

function setHoneypotClickEvent() {
	const forms = document.querySelectorAll('form')
	forms.forEach((form) => {
		const submitButtons = form.querySelectorAll('[type=submit]:not([tabindex="-1"], [type="hidden"], .hidden')
		submitButtons.forEach((submitButton) => {
			submitButton.addEventListener('click', () => {
				checkHoneypot(form)
			})
		})
	})
}

function checkHumanMovement() {
	const setTabIndexFocus = (e) => {
		// Check for Tab key specifically
		if (e.key === 'Tab') {
			humanMovement = true
			if (debug) {
				console.log('%cMovement check (tabindex) passed', 'color: green')
			}
			document.removeEventListener('keydown', setTabIndexFocus)
		}
	}

	document.addEventListener('keydown', setTabIndexFocus)

	const handleMovement = () => {
		humanMovement = true
		if (debug) {
			console.log('%cMovement check (mouse or touch) passed', 'color: green')
		}
		document.removeEventListener('mousemove', handleMovement)
		document.removeEventListener('touchmove', handleMovement)
	}

	document.addEventListener('mousemove', handleMovement)
	document.addEventListener('touchmove', handleMovement)
}

function setFocusEvent() {
	const fields = document.querySelectorAll(':is(input, select, textarea)[data-ynfield][required]:not([tabindex="-1"], [type="hidden"], .hidden, [name="yn_name"], [name="consents[]_v2"])')

	fields.forEach((field) => {
		const handleFocusOrInput = () => {
			if (!focusedElements.includes(field.getAttribute('id'))) {
				focusedElements.push(field.getAttribute('id'))
				// Remove event listeners once the field has been focused
				field.removeEventListener('focusin', handleFocusOrInput)
				field.removeEventListener('input', handleFocusOrInput)
			}
		}

		// Handle manual focus events
		field.addEventListener('focusin', handleFocusOrInput)

		// Additional event for detecting autofill in most browsers
		field.addEventListener('input', handleFocusOrInput)
	})
}

function checkFocus(form) {
	const fields = form.querySelectorAll(':is(input, select, textarea)[data-ynfield][required]:not([tabindex="-1"], [type="hidden"], .hidden, [name="yn_name"], [name="consents[]_v2"])')
	let notAllFieldsFocused = false

	fields.forEach((field) => {
		if (!focusedElements.includes(field.getAttribute('id'))) {
			notAllFieldsFocused = true
		}
	})
	if (notAllFieldsFocused) {
		if (debug) {
			console.log('%cBot detected by focus (added 100 Score)', 'color: red')
		}
		botScore += 100
	} else {
		if (debug) {
			console.log('%cFocus check passed', 'color: green')
		}
	}
}

function botDCheck() {
	load({ monitoring: false })
		.then((botd) => botd.detect())
		.then((result) => {
			botD = result
			if (botD.bot) {
				if (debug) {
					console.log('%cBot detected by BotD (added 100 Score)', 'color: red')
				}
				botScore += 100
			} else if (debug) {
				console.log('%cBotD check passed', 'color: green')
			}
		})
		.catch((error) => console.error(error))
}

function webGLCheck() {
	try {
		const canvas = document.createElement('canvas')
		const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl')

		if (!gl) {
			if (debug) {
				console.log('%cBot detected by WebGL (added 20 Score)', 'color: red')
			}
			botScore += 20
			return false
		}

		const debugInfo = gl.getExtension('WEBGL_debug_renderer_info')
		if (!debugInfo) {
			if (debug) {
				console.log('%cBot detected by WebGL debug info (added 20 Score)', 'color: red')
			}
			botScore += 20
			return false
		}

		if (debug) {
			console.log('%cWebGL check passed', 'color: green')
		}
		return true
	} catch (e) {
		console.log('Error during WebGL check (added 40 Score):', e)
		botScore += 40
		return false
	}
}

function localStorageCheck() {
	localStorage.setItem('ynfinite-bot-protection', renderedKey)
	sessionStorage.setItem('ynfinite-bot-protection', renderedKey)
	document.cookie = 'ynfinite-bot-protection=' + renderedKey + '; path=/'

	if (localStorage.getItem('ynfinite-bot-protection') !== renderedKey) {
		if (debug) {
			console.log('%cBot detected by localStorage (added 30 Score)', 'color: red')
		}
		botScore += 30
	} else if (debug) {
		console.log('%clocalStorage check passed', 'color: green')
	}

	if (sessionStorage.getItem('ynfinite-bot-protection') !== renderedKey) {
		if (debug) {
			console.log('%cBot detected by sessionStorage (added 30 Score)', 'color: red')
		}
		botScore += 30
	} else if (debug) {
		console.log('%csessionStorage check passed', 'color: green')
	}

	if (document.cookie.indexOf('ynfinite-bot-protection=' + renderedKey) === -1) {
		if (debug) {
			console.log('%cBot detected by cookie (added 30 Score)', 'color: red')
		}
		botScore += 30
	} else if (debug) {
		console.log('%ccookie check passed', 'color: green')
	}
}

function checkScreen() {
	if(window.screen.width > 0 && window.screen.height > 0) {
		if(debug) {
			console.log('%cscreen size check passed', 'color: green')
		}
	} else {
		if(debug) {
			console.log('%cBot detected by screen size (added 70 Score)', 'color: red')
		}
		botScore += 70
	}
}

const YnfiniteForms = {
	resetForm(element) {
		element.reset()
	},

	async submitForm(element) {
		const redirect = element.getAttribute('redirect')
		const method = element.getAttribute('method')
		const hasProof = method == 'get' ? true : element.getAttribute('data-has-proof')
		const proofenHash = method == 'get' ? true : element.getAttribute('data-proofen-hash')
		const formSubmitButton = element.querySelector('button[type=submit]')
		checkFocus(element)

		if(!hasProof){
			botScore += 100
		}

		if(!hasProof){
			botScore += 100

			if (debug) {
				console.log('%cBot detected by missing hasProof (added 100 Score)', 'color: red')
			} 
		} else {
			if (debug) {
				console.log('%cHasProof check passed', 'color: green')
			}
		}

		if(!proofenHash){
			botScore += 100

			if (debug) {
				console.log('%cBot detected by missing proofenHash (added 100 Score)', 'color: red')
			} 
		} else {
			if (debug) {
				console.log('%cProofenHash check passed', 'color: green')
			}
		}

		if(!humanMovement){
			botScore += 100

			if (debug){
				console.log('%cBot detected by movement (added 100 Score)', 'color: red')
			}
		}

		if (debug) {
			if(botScore >= 100){
				console.log(`%cBot score: ${botScore}`, 'color: red')
			} else if (botScore < 100){
				console.log(`%cBot score: ${botScore}`, 'color: green')
			}
		}

		if (botScore >= 100) {
			console.log('Sorry, there is no proof here that you are a human. The form can not be sent.', 'color: red')
			formSubmitButton.classList.remove('yn-loader')
			formSubmitButton.style.removeProperty('padding-left')
			formSubmitButton.style.borderColor = 'var(--error, red)'
			formSubmitButton.style.backgroundColor = 'var(--error, red)'
			formSubmitButton.style.color = 'var(--light, white)'
			formSubmitButton.style.pointerEvents = 'none'
			formSubmitButton.textContent = 'Bot-Schutz fehlgeschlagen. Bitte neu laden.'
			return
		}

		if (redirect === 'true') {
			element.submit()
			return
		}

		if (method == 'post' && formSubmitButton) {
			const pos = 'var(--loader-size,16px) + ' + getComputedStyle(formSubmitButton).paddingLeft
			formSubmitButton.classList.add('yn-loader')
			formSubmitButton.style.paddingLeft = formSubmitButton.style.paddingLeft = 'calc(' + pos + ')'
			formSubmitButton.style.setProperty('--yn-loader-pos', 'calc((' + pos + ' - var(--loader-size,16px)) / 2);')
		}

		const ynBeforeAsyncChangeData = new Event('onPreAsyncChangeData')
		element.dispatchEvent(ynBeforeAsyncChangeData)

		const formData = new FormData(element)
		formData.set('events', element.getAttribute('data-events'))
		formData.set('method', method)
		formData.set('formId', element.getAttribute('data-ynformid'))
		formData.set('formLanguage', element.getAttribute('data-language'))
		formData.set('hasProof', hasProof)
		formData.set('proofenHash', proofenHash)
		if (element.hasAttribute('data-ynsectionid')) {
			formData.set('sectionId', element.getAttribute('data-ynsectionid'))
		}

		const action = element.getAttribute('action')

		// const data = new URLSearchParams();

		const params = new URLSearchParams(window.location.search)
		const perPage = params.get('__yPerPage')

		if (perPage) {
			formData.append('__yPerPage', perPage)
		}

		const ynBeforeAsyncChange = new Event('onPreAsyncChange')
		element.dispatchEvent(ynBeforeAsyncChange)

		const response = await fetch(action, {
			method: 'POST',
			body: formData,
		})

		if (response.ok) {
			const jsonResponse = await response.json()
			switch (jsonResponse['type']) {
				case 'page':
					element.dispatchEvent(
						new CustomEvent('onAsyncChange', {
							detail: {
								response: jsonResponse,
							},
						})
					)
					break
				case 'redirect':
					window.location.replace(jsonResponse['url'])
					break
				case '404':
				case 'error':
					console.log('404/Error: ', jsonResponse['message'])
					break
			}

			if (method == 'post' && formSubmitButton) {
				formSubmitButton.classList.remove('yn-loader')
				formSubmitButton.style.removeProperty('padding-left')
			}
		} else {
			if (method == 'post' && formSubmitButton) {
				formSubmitButton.classList.remove('yn-loader')
				formSubmitButton.style.removeProperty('padding-left')
				formSubmitButton.style.backgroundColor = 'var(--error, red)'
				formSubmitButton.style.color = 'var(--light, white)'
				formSubmitButton.textContent = 'Error'
			}
			console.error(response)
		}
	},

	addChangeEvent(element) {
		const formInputElements = element.querySelectorAll('select, input')

		for (var i = 0; i < formInputElements.length; i++) {
			formInputElements[i].addEventListener('change', async (e) => {
				e.preventDefault()
				await this.submitForm(element, 'onChange')
			})
		}
	},

	addSubmitEvent(element) {
		element.addEventListener('submit', async (e) => {
			e.preventDefault()
			await this.submitForm(element, 'onSubmit')
		})
	},

	setup() {
		document.addEventListener('DOMContentLoaded', () => {
			const forms = document.querySelectorAll('[data-ynform=true]')

			if (forms) {
				botDCheck()
				webGLCheck()
				localStorageCheck()
				setFocusEvent()
				checkHumanMovement()
				setHoneypotClickEvent()
				checkScreen()
			}

			forms.forEach((form) => {
				if (form.hasAttribute('data-onchange')) {
					this.addChangeEvent(form)
					form.addEventListener('submit', async (e) => e.preventDefault()) // if we dont remove the submit event here, the second time you send the same formdata a submit will be triggert
				}

				if (form.hasAttribute('data-onsubmit') || !form.hasAttribute('data-onchange')) {
					this.addSubmitEvent(form)
				}

				// Handle reset action
				const resetButton = form.querySelector("button[type='reset']")
				if (resetButton) {
					resetButton.addEventListener('click', async () => {
						const formInputElements = form.querySelectorAll('select, input')

						for (var i = 0; i < formInputElements.length; i++) {
							formInputElements[i].value = ''
						}

						await this.submitForm(form)
					})
				}

				// Handle new form

				const newFormLink = form.querySelector('.yn-form-response__new-form')
				if (newFormLink) {
					newFormLink.addEventListener('click', (e) => {
						e.preventDefault()
						this.resetForm(form)

						newFormLink.closest('form').querySelector('.form-content').classList.remove('inactive')
						newFormLink.closest('.yn-form-response').classList.remove('active')
					})
				}

				// Handle list fields
				const listFields = form.querySelectorAll('.yn-listForm-wrapper')

				listFields.forEach((listField) => {
					const newAction = listField.querySelector('.yn-listForm-actions-new')

					const rowTemplate = listField.querySelector('#listField_' + listField.dataset.ynformalias)

					const dataContainer = listField.querySelector('.yn-listForm-data')

					newAction.addEventListener('click', (e) => {
						e.preventDefault()
						const newRow = rowTemplate.content.cloneNode(true)
						newRow.className = 'yn-listForm-row'

						const deleteButton = newRow.querySelector('.yn-listForm-actions-delete')

						const fields = newRow.querySelectorAll('[data-ynfield=true]')
						fields.forEach((f) => {
							f.setAttribute('name', f.name.replace('::count::', dataContainer.childElementCount))
						})

						dataContainer.appendChild(newRow)

						deleteButton.addEventListener('click', (e) => {
							e.preventDefault()
							const row = e.target.closest('.yn-listForm-row')
							dataContainer.removeChild(row)
						})
					})
				})
			})
		})
	},

	enableForm(form) {
		const fieldset = form.querySelector('fieldset')
		fieldset.disabled = false
	},

	disableForm(form) {
		const fieldset = form.querySelector('fieldset')
		fieldset.disabled = true
	},

	updateUrl(form) {
		const formData = new FormData(form)

		const data = new URLSearchParams()

		const params = new URLSearchParams(window.location.search)
		const perPage = params.get('__yPerPage')

		if (perPage) {
			data.append('__yPerPage', perPage)
		}

		for (const pair of formData) {
			if (pair[1]) {
				data.append(pair[0], pair[1])
			}
		}

		let newHref = `${window.location.protocol}//${window.location.hostname}${window.location.pathname}`
		if (data.toString()) {
			newHref += `?${data.toString()}`
		}

		history.pushState({}, '', newHref)
	},

	repopulateForm(form, data) {
		const keys = Object.keys(data.fields)
		for (var i = 0; i < keys.length; i++) {
			const element = data.fields[keys[i]]
			const formElement = form.querySelector(`[name="fields[${element.alias}]"]`)

			if (formElement && element && element.options) {
				let markup = `${element.options.map((option) => `<option value="${option.value}" ${option.value === element.value ? 'selected' : ''}>${option.label}</option>`).join('')}`
				if (!formElement.options[0].value) {
					markup = `<option value>${formElement.options[0].text}</option>${markup}`
				}

				formElement.innerHTML = markup
			}
		}
	},

	showResponse(form, data) {
		const responseContainer = form.querySelector('.yn-form-response')
		const formContent = form.querySelector('.form-content')

		const innerContainer = responseContainer.querySelector('.yn-form-response__inner')

		formContent.classList.add('inactive')
		innerContainer.innerHTML = data.rendered
		responseContainer.classList.add('active')
		responseContainer.scrollIntoView({
			behavior: 'auto',
			block: 'center',
			inline: 'center',
		})
	},
}

export default YnfiniteForms
