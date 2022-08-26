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


const form = document.getElementById('option1_form');

form.addEventListener('click', function(event){
    
    event.preventDefault();
    
    const formattedFormData = new FormData(form);
    postData(formattedFormData);
});

async function postData(formattedFormData){

    const response = await fetch('api',{
        method: 'POST',
        body: formattedFormData
    });
    
    const data = await response.text();
    //This should now print out the values that we sent to the backend-side
    console.log(data);
}
