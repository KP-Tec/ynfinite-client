const YnfiniteYoutube = {
	setup() {
		const videos = Array.from(document.querySelectorAll('.yn-video-youtube'))

		const addVideo = (e) => {
			const video = e.target
			const iframe = document.createElement('iframe')
			const src = video.getAttribute('yn-src')
			const title = video.getAttribute('yn-title')
			const width = video.getAttribute('yn-width')
			const height = video.getAttribute('yn-height')
			const attributes = video.getAttribute('yn-attributes').split(' ')

			if (src) {
				iframe.setAttribute('src', src)
				if (title) iframe.setAttribute('title', title)
				if (width) iframe.setAttribute('width', width)
				if (height) iframe.setAttribute('height', height)
				if (attributes) {
					attributes.forEach((attr) => {
						const attrSplit = attr.split('=')
						console.log('attr', attrSplit[0], attrSplit[1] || '')
						iframe.setAttribute(attrSplit[0], attrSplit[1] || '')
					})
				}
				video.append(iframe)
				video.removeEventListener('click', addVideo)
			}
		}

		videos.forEach((video) => {
			video.addEventListener('click', addVideo)
		})
	},
}

export default YnfiniteYoutube
