import { jest } from '@jest/globals';
import { options } from '@polkadot/api';
import { ApiPromise, WsProvider } from '@polkadot/api';
import { Keyring } from '@polkadot/keyring';
import { bulkTransfer } from '../src/index';

describe('kylin bulk transfer test', () => {
  let api: ApiPromise;

  beforeAll(async () => {
    jest.setTimeout(30000);

    process.env.NODE_ENV = 'test';

    const provider = new WsProvider('ws://localhost:9944');
    api = await ApiPromise.create(
      options({
        types: {
          TAssetBalance: 'Balance'
        },
        provider
      })
    );
  });

  afterAll(async () => {
    api && (await api.disconnect());
  });

  it('should send batch transfer', async () => {
    const keyring = new Keyring();
    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    const sender = keyring.addFromUri(process.env.mnemonic!, { name: 'bulk transfer test' }, 'sr25519');
    const recipients = [
      'stAZnJwXAvRRo884Anfu2in9SBB6tssvcsjBAZnvnVn53krpP',
      'st8p7os56kbysAKCxRjC1PeUyobEP8b94sQkBbmeWSc2GJzEt'
    ];
    const amounts = ['1000', '1000'];
    await bulkTransfer(api, sender, recipients, amounts);
  });

  it.skip('should decode evnets', async () => {
    const head = await api.rpc.chain.getBlockHash();
    await api.query.system.events.at(head);
  });
});
