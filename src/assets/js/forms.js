const YnfiniteForms = {
	addChangeEvent(element) {
		for (var i = 0; i < element.elements.length; i++) {
			element.elements[i].addEventListener('change', function (e) {
				e.preventDefault()
				element.submit()
			})
		}
	},

	resetForm(element) {
		element.reset()
	},

	async submitForm(element, eventType) {
		const ynBeforeAsyncChangeData = new Event('onPreAsyncChangeData')
		element.dispatchEvent(ynBeforeAsyncChangeData)

		const formData = new FormData(element)
		formData.set('eventAsync', true)
		formData.set('eventType', eventType)
		formData.set('method', element.getAttribute('data-ynformmethod'))
		formData.set('formId', element.getAttribute('data-ynformid'))
		formData.set('formLanguage', element.getAttribute('data-language'))
		if (element.hasAttribute('data-ynsectionid')) {
			formData.set('sectionId', element.getAttribute('data-ynsectionid'))
		}

		const data = new URLSearchParams()

		const params = new URLSearchParams(window.location.search)
		const perPage = params.get('__yPerPage')

		if (perPage) {
			data.append('__yPerPage', perPage)
		}

		for (const pair of formData) {
			data.append(pair[0], pair[1])
		}

		const ynBeforeAsyncChange = new Event('onPreAsyncChange')
		element.dispatchEvent(ynBeforeAsyncChange)

		const sendButton = element.querySelector('.button')
		const sendButton_text = sendButton.textContent
		sendButton.style.width = sendButton.offsetWidth + 'px'
		sendButton.style.textAlign = 'center'
		sendButton.style.opacity = 0.5
		sendButton.style.cursor = none
		sendButton.disabled = true

		const loading = setInterval(() => {
			if (sendButton.textContent.length > 3) {
				sendButton.textContent = '.'
			} else sendButton.textContent = sendButton.textContent + ' .'
		}, 150)

		const response = await fetch('/yn-form/send', {
			method: 'POST',
			body: formData,
		})

		sendButton.style.removeProperty('opacity')
		clearInterval(loading)

		if (response.ok) {
			sendButton.textContent = sendButton_text
			sendButton.style.removeProperty('width')
			sendButton.style.removeProperty('textAlign')
			sendButton.style.removeProperty('cursor')
			sendButton.disabled = false
		} else {
			sendButton.style.backgroundColor = 'red'
			sendButton.textContent = 'Error'
			console.log(response)
		}

		const ynAsyncChange = new CustomEvent('onAsyncChange', {
			detail: {
				response: await response.json(),
			},
		})
		element.dispatchEvent(ynAsyncChange)
	},

	addAsyncChangeEvent(element) {
		const formInputElements = element.querySelectorAll('select, input')

		for (var i = 0; i < formInputElements.length; i++) {
			formInputElements[i].addEventListener('change', async (e) => {
				e.preventDefault()
				await this.submitForm(element, 'onChange')
			})
		}
	},

	addAsyncSubmitEvent(element) {
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
					if (form.dataset.onchange === 'async') {
						this.addAsyncChangeEvent(form)
					} else {
						this.addChangeEvent(form)
					}
				}

				if (form.hasAttribute('data-onsubmit')) {
					if (form.dataset.onsubmit === 'async') {
						this.addAsyncSubmitEvent(form)
					}
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
				newFormLink.addEventListener('click', (e) => {
					e.preventDefault()
					this.resetForm(form)

					newFormLink.closest('form').querySelector('.form-content').classList.remove('inactive')
					newFormLink.closest('.yn-form-response').classList.remove('active')
				})

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

			if (!formElement) break

			let markup = `${element.options.map((option) => `<option value="${option.value}" ${option.value === element.value ? 'selected' : ''}>${option.label}</option>`).join('')}`
			if (!formElement.options[0].value) {
				markup = `<option value>${formElement.options[0].text}</option>${markup}`
			}

			formElement.innerHTML = markup
		}
	},

	showResponse(form, data) {
		const responseContainer = form.querySelector('.yn-form-response')
		const formContent = form.querySelector('.form-content')

		const innerContainer = responseContainer.querySelector('.yn-form-response__inner')

		formContent.classList.add('inactive')
		innerContainer.innerHTML = data.rendered
		responseContainer.classList.add('active')
	},
}

export default YnfiniteForms
