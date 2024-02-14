// Upload Field
if (document.querySelector('.widget--file')) {
	const inputWidgets = document.querySelectorAll('.widget--file')

	inputWidgets.forEach((inputWidget) => {
		const input = inputWidget.querySelector('input')
		const label = inputWidget.querySelector('label')
		const fallbacktext = label.innerHTML

		input.addEventListener('cancel', (e) => {
			e.target.value = null
		})

		input.addEventListener('change', () => {
			let filelist = document.createElement('ul')
			const files = input.files
			const length = files.length

			for (let i = 0; i < files.length; i++) {
				const listItem = document.createElement('li')
				listItem.textContent = files[i].name
				filelist.appendChild(listItem)
			}

			if (length > 0) {
				if (length === 1) {
					label.innerHTML = length + ' Datei ausgewählt'
				} else {
					label.innerHTML = length + ' Dateien ausgewählt'
				}

				label.appendChild(filelist)
			} else {
				label.innerHTML = fallbacktext
			}
		})
	})
}
