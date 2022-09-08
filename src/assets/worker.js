import SHA256  from "crypto-js/sha256"

let nonce = 0;

function calculateHash(previousHash, timestamp, nonce, form) {
    return SHA256(previousHash + timestamp + form + nonce).toString();
}

onmessage = (e) => {
    const difficulty = e.data.difficulty;
    const form = e.data.form;
    const timestamp = e.data.timestamp;
    const previousHash = e.data.previousHash;

    let hash = "";
    while (hash.substring(0, difficulty) !== Array(difficulty + 1).join("0")) {
        nonce++;
        hash = calculateHash(previousHash, timestamp, nonce, form);
    }
    
    postMessage(hash)
}

