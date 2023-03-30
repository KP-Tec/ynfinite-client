import SHA256 from 'crypto-js/sha256'

let nonce = 0

function calculateHash(previousHash, timestamp, nonce, form) {
	return SHA256(previousHash + timestamp + form + nonce).toString()
}

onmessage = (e) => {
	const difficulty = e.data.difficulty
	const chances = Math.min(Math.max(e.data.chances, 0), 9)
	const form = e.data.form
	const timestamp = e.data.timestamp
	const previousHash = e.data.previousHash

	let chancesArray = []
	let hash = ''

	for (let i = 0; i <= chances; i++) {
		chancesArray.push(String(i).repeat(difficulty))
	}

	while (!chancesArray.includes(hash.substring(0, difficulty))) {
		nonce++
		if (timestamp + 3000 < Date.now()) {
			hash = calculateHash(previousHash, timestamp, nonce, form)
		}
	}

	postMessage(hash)
}
