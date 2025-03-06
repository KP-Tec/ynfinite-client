import SHA256 from 'crypto-js/sha256'

class Block {
	constructor(data, previousHash = '') {
		this.previousHash = previousHash
		this.timestamp = Date.now()
		this.data = data
		this.hash = this.calculateHash()
		this.nonce = 0
	}

	calculateHash() {
		if (this.data['form'] != undefined) {
			return SHA256(this.previousHash + this.timestamp + this.data['form'].id).toString()
		}
	}

	// difficulty = size of number (4 = 0000)
	// chances = number of chances (2 = 0000, 1111, 2222)
	startProofOfWork(difficulty = 6, chances = 9, minRunTime = 5000) {
		if (window.Worker) {
			const blockWorker = new Worker('/assets/vendor/ynfinite/js/worker.min.js')

			blockWorker.onmessage = (e) => {
				this.hash = e.data
				this.data.form.dataset.hasProof = 'true'
				this.data.form.dataset.proofenHash = this.hash

				const formSubmitButton = this.data.form.querySelector('button[type=submit]')

				formSubmitButton.classList.remove('yn-loader')
				formSubmitButton.classList.remove('yn-botprotection')
				formSubmitButton.style.removeProperty('padding-left')
				formSubmitButton.textContent = formSubmitButton.dataset.label

				blockWorker.terminate()
				console.timeEnd()
			}

			console.time()

			blockWorker.postMessage({
				form: this.data['form'].id,
				previousHash: this.previousHash,
				timestamp: this.timestamp,
				difficulty,
				chances,
				minRunTime,
			})
		}
	}
}

class BlockChain {
	constructor() {
		this.chain = [this.createGenesisBlock()]
	}

	getLatestBlock() {
		return this.chain[this.chain.length - 1]
	}

	addBlock(newBlock) {
		newBlock.previousHash = this.getLatestBlock().hash
		this.chain.push(newBlock)

		return newBlock
	}

	createGenesisBlock() {
		return new Block('Genesis block', '0')
	}
}

const YnfiniteBotProtection = {
	setup() {
		document.addEventListener('DOMContentLoaded', () => {
			const blockchain = new BlockChain()

			const forms = document.querySelectorAll('form[data-ynform=true][method=post]:not(.yn-no-bot-protection)')
			if (forms.length === 0) {
				return
			}

			forms.forEach((form) => {
				form.dataset.hasProof = 'false'
				form.dataset.proofenHash = ''

				const formSubmitButton = form.querySelector('button[type=submit]')

				form.addEventListener('focusin', function () {
					if (form.dataset.hasProof === 'false' && !form.dataset.working) {
						form.dataset.working = true

						const pos = 'var(--loader-size,16px) + ' + getComputedStyle(formSubmitButton).paddingLeft
						formSubmitButton.dataset.label = formSubmitButton.textContent
						formSubmitButton.style.paddingLeft = formSubmitButton.style.paddingLeft = 'calc(' + pos + ')'
						formSubmitButton.style.setProperty('--yn-loader-pos', 'calc((' + pos + ' - var(--loader-size,16px)) / 2);')
						formSubmitButton.classList.add('yn-botprotection')
						formSubmitButton.classList.add('yn-loader')
						formSubmitButton.textContent = 'Bot-Prüfung'

						const block = blockchain.addBlock(new Block({ form: form }))
						block.startProofOfWork()
					}
				})
			})
		})
	},
}

export default YnfiniteBotProtection
