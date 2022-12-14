const ethUtil = require("ethereumjs-util")
const sigUtil = require("eth-sig-util")

var accounts = [undefined];

connectButton.addEventListener("click", function (event) {
    event.preventDefault()
    connect()
})

function connect() {
    try {
        if (typeof ethereum !== "undefined") {
            ethereum.request({
                method: "eth_requestAccounts"
            }).then(result => {
                accounts = result
                alert("Connected!");
                tokenHolder.value = accounts[0];
            }).catch(error => {
                alert("Error: " + error.message);
            });
        } else {
            alert("Extension not available!");
        }
    } catch(error) {
        alert(JSON.stringify(ethereum));
        alert("Error: " + error.message);
    }
}

ethSignButton.addEventListener("click", function (event) {
    event.preventDefault()
    if (SubstrateAddress.value=="") {
        alert("You need to specify a substrate address!");
        return;
    }
    const msg = SubstrateAddress.value;
    const from = accounts[0];
    if (!from) {
        alert("You need to connect!");
        return;
    }

    console.log("Sending personal_sign request");
    const params = [from, msg];
    console.dir(params);

    ethereum.request({
        method: "personal_sign",
        params: params,
    }).then(result => {
        console.log("result:", result);
        //alert("Signature: " + result)
        AutoFinalSignature.value = result;
    }).catch(error => {
        alert("Error: " + error.message);
    });
})


function ethSignTypedData(version) {

    const msgParams = JSON.stringify({
        "types": {
            "EIP712Domain": [{"name": "name", "type": "string"}, {
                "name": "version",
                "type": "string"
            }, {"name": "chainId", "type": "uint256"}, {"name": "verifyingContract", "type": "address"}],
            "Bet": [{"name": "roundId", "type": "uint32"}, {"name": "gameType", "type": "uint8"}, {
                "name": "number",
                "type": "uint256"
            }, {"name": "value", "type": "uint256"}, {"name": "balance", "type": "int256"}, {
                "name": "serverHash",
                "type": "bytes32"
            }, {"name": "userHash", "type": "bytes32"}, {"name": "gameId", "type": "uint256"}]
        },
        "primaryType": "Bet",
        "domain": {
            "name": "Dicether",
            "version": "2",
            "chainId": 1,
            "verifyingContract": "0xaEc1F783B29Aab2727d7C374Aa55483fe299feFa"
        },
        "message": {
            "roundId": 1,
            "gameType": 4,
            "num": 1,
            "value": "320000000000000",
            "balance": "-640000000000000",
            "serverHash": "0x4ed3c2d4c6acd062a3a61add7ecdb2fcfd988d944ba18e52a0b0d912d7a43cf4",
            "userHash": "0x6901562dd98a823e76140dc8728eca225174406eaa6bf0da7b0ab67f6f93de4d",
            "gameId": 2393,
            "number": 1
        }
    });

    const from = accounts[0];
    if (!from) {
        alert("You need to connect!");
        return;
    }

    const params = [from, msgParams];

    console.log("Sending eth_signTypedData_v" + version + " request");
    console.dir(params);

    ethereum.request({
        method: "eth_signTypedData_v" + version,
        params: params,
    }).then(result => {
        const recovered = sigUtil.recoverTypedSignature({data: JSON.parse(msgParams), sig: result})
        if (ethUtil.toChecksumAddress(recovered) === ethUtil.toChecksumAddress(from)) {
            alert("Successfully ecRecovered signer as " + from)
        } else {
            alert("Failed to verify signer when comparing " + result + " to " + from)
        }
    }).catch(error => {
        alert("Error: " + error.message);
    });
};


const form1 = document.getElementById('option1_form');

form1.addEventListener('submit', function(event){
    
    event.preventDefault();
    
    if (tokenHolder.value == "") {
        alert("You need to specify a token holder!");
        return;
    }
    if (SubstrateAddress.value=="") {
        alert("You need to specify a substrate address!");
        return;
    }
    if (AutoFinalSignature.value=="") {
        alert("You need to specify a auto final signature!");
        return;
    }

    const formattedFormData = {
        tokenHolder: this.tokenHolder.value,
        SubstrateAddress: this.SubstrateAddress.value,
        manuallysigned: this.manuallysigned.value,
        AutoFinalSignature: this.AutoFinalSignature.value
    }
    postData(formattedFormData);
});

const form2 = document.getElementById('option2_form');

form2.addEventListener('submit', function(event){
    
    event.preventDefault();

    if (tokenHolder2.value == "") {
        alert("You need to specify a token holder!");
        return;
    }
    if (SubstrateAddress2.value=="") {
        alert("You need to specify a substrate address!");
        return;
    }
    if (AutoFinalSignature2.value=="") {
        alert("You need to specify a auto final signature!");
        return;
    }
    
    const formattedFormData = {
        tokenHolder: this.tokenHolder2.value,
        SubstrateAddress: this.SubstrateAddress2.value,
        manuallysigned: this.manuallysigned2.value,
        AutoFinalSignature: this.AutoFinalSignature2.value
    }
    postData(formattedFormData);
});

async function postData(formattedFormData){

    const response = await fetch('api',{
        method: 'POST',
        body: JSON.stringify(formattedFormData)
    });
    
    const data = await response.text();
    //This should now print out the values that we sent to the backend-side
    
        Confirm.open({
            title: 'Your Airdrop request has been received!',
            message: data,
            onok: () => {
              document.location.href = "https://kylin.network";
            }
          });
    

    console.log(data);
}

const Confirm = {
    open (options) {
        options = Object.assign({}, {
            title: '',
            message: '',
            okText: 'OK',
            cancelText: 'Send Another',
            onok: function () {},
            oncancel: function () {}
        }, options);
        
        const html = `
            <div class="confirm">
                <div class="confirm__window">
                    <div class="confirm__titlebar">
                        <span class="confirm__title">${options.title}</span>
                        <button class="confirm__close">&times;</button>
                    </div>
                    <div class="confirm__content">${options.message}</div>
                    <div class="confirm__buttons">
                        <button class="confirm__button confirm__button--ok confirm__button--fill">${options.okText}</button>
                        <button class="confirm__button confirm__button--cancel">${options.cancelText}</button>
                    </div>
                </div>
            </div>
        `;

        const template = document.createElement('template');
        template.innerHTML = html;

        // Elements
        const confirmEl = template.content.querySelector('.confirm');
        const btnClose = template.content.querySelector('.confirm__close');
        const btnOk = template.content.querySelector('.confirm__button--ok');
        const btnCancel = template.content.querySelector('.confirm__button--cancel');

        confirmEl.addEventListener('click', e => {
            if (e.target === confirmEl) {
                options.oncancel();
                this._close(confirmEl);
            }
        });

        btnOk.addEventListener('click', () => {
            options.onok();
            this._close(confirmEl);
        });

        [btnCancel, btnClose].forEach(el => {
            el.addEventListener('click', () => {
                options.oncancel();
                this._close(confirmEl);
            });
        });

        document.body.appendChild(template.content);
    },

    _close (confirmEl) {
        confirmEl.classList.add('confirm--close');

        confirmEl.addEventListener('animationend', () => {
            document.body.removeChild(confirmEl);
        });
    }
};



