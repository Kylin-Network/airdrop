executing bulk-transfer for the KylinNewtwork Airdrop

## Usage

```

async function usage() {
  const provider = new WsProvider('ws://local_rpc_endpoint');
  const api = await ApiPromise.create(
    options({
      types: {
        TAssetBalance: 'Balance'
      },
      provider
    })
  );
  const keyring = new Keyring();
  const sender = keyring.addFromUri(process.env.mnemonic!, { name: 'bulk transfer test' }, 'sr25519');
  const recipients = [
    'stAZnJwXAvRRo884Anfu2in9SBB6tssvcsjBAZnvnVn53krpP',
    'st8p7os56kbysAKCxRjC1PeUyobEP8b94sQkBbmeWSc2GJzEt'
  ];
  const amounts = ['1000', '1000'];
  await bulkTransfer(api, sender, recipients, amounts);
}

await usage();
```