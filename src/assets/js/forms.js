const YnfiniteForms = {
	resetForm(element) {
		element.reset()
	},

	async submitForm(element, eventType) {
		const redirect = element.getAttribute('redirect')

		if (redirect === 'true') {
			element.submit()
			return
		}

		const method = element.getAttribute('method')
		const hasProof = method == 'get' ? true : element.getAttribute('data-has-proof')
		const proofenHash = method == 'get' ? true : element.getAttribute('data-proofen-hash')

		if (!hasProof || !proofenHash) {
			console.log('Sorry, there is no proof here that you are a human. The form can not be sent.')
			return
		}

		const formSubmitButton = element.querySelector('button[type=submit]')
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

			forms.forEach((form) => {
				if (form.hasAttribute('data-onchange')) {
					this.addChangeEvent(form)
				}

				if (form.hasAttribute('data-onsubmit') || !form.hasAttribute('data-onchange')) {
					this.addSubmitEvent(form)
				}

				// Handle reset action
				const resetButton = form.querySelector("button[type='reset']")
				if (resetButton) {
					resetButton.addEventListener('click', async (e) => {
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

module.exports = YnfiniteForms
