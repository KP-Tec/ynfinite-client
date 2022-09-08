import SHA256  from "crypto-js/sha256"

class Block {
    constructor(data, previousHash = '') {
        this.previousHash = previousHash;
        this.timestamp = Date.now();
        this.data = data;

        this.hash = this.calculateHash();

        this.nonce = 0;
    }

    calculateHash() {
        return SHA256(this.previousHash + this.timestamp + JSON.stringify(this.data)).toString();
    }

    startProofOfWork(difficulty = 4) {
        if (window.Worker) {
            const blockWorker = new Worker('/assets/vendor/ypsolution/js/worker.min.js');

            

            blockWorker.onmessage = (e) => {
                this.hash = e.data

                this.data.form.dataset.hasProof = "true";
                this.data.form.dataset.proofenHash = this.hash;

                const formSubmitButton = this.data.form.querySelector("button[type=submit]");
                 
                formSubmitButton.classList.remove("show-form-spinner")
                formSubmitButton.textContent = formSubmitButton.dataset.label;

                blockWorker.terminate();
                console.timeEnd();
            }

            console.time();

            blockWorker.postMessage({form: JSON.stringify(this.data.form), previousHash: this.previousHash, timestamp: this.timestamp, difficulty})
        }
    }
}


class BlockChain {
    constructor() {
        this.chain = [this.createGenesisBlock()];
    }

    getLatestBlock() {  
        return this.chain[this.chain.length - 1];
    }

    addBlock(newBlock){
        newBlock.previousHash = this.getLatestBlock().hash;
        this.chain.push(newBlock);

        return newBlock;
    }

    createGenesisBlock() {
        return new Block("Genesis block", "0");
    }
}

const YnfiniteBotProtection = {
    setup() {
        document.addEventListener("DOMContentLoaded", () => {
            const blockchain = new BlockChain();
           
            const forms = document.querySelectorAll("form[data-ynform=true][method=post]");
            if(forms.length === 0) {
                return;
            }

            forms.forEach((form) => {
                form.dataset.hasProof = "false";
                form.dataset.proofenHash = "";

                const formSubmitButton = form.querySelector("button[type=submit]");

                form.addEventListener('change', function() {
                    if(form.dataset.hasProof === "false" && !form.dataset.working) {
                        form.dataset.working = true;

                        formSubmitButton.dataset.label = formSubmitButton.textContent;
                        formSubmitButton.classList.add("show-form-spinner")
                        formSubmitButton.textContent = "Warte auf Bot-Prüfung...";
                
                        const block = blockchain.addBlock(new Block({form: form}))
                        block.startProofOfWork()
                    }
                });
            });
        })
    },
}

export default YnfiniteBotProtection