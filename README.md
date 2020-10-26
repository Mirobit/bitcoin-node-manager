# Bitcoin Node Manager Beta

![](https://i.imgur.com/wfUpY1t.png)

Bitcoin Node Manager (BNM) is a lightweight dashboard and control system for your Bitcoin node.

## Features

- Extensive dashboard with general information about the node, connected peers and the blockchain
- Create rules to manage your peers
  - Ban, disconnect or log peers that waste ressources, are slow or run alternative clients (e.g. BCash)
  - Set gobal events that trigger the execution of rules, run rules manually or set up a cron job
- Overview of all connected peers inlcuding country, ISP, client, traffic usage, supported services...
  - Ban or disconnect peers
  - Manage a list of web hoster to detect if peer is hosted or private
- Overview of all banned peers
  - Unbann specific peers
  - Export/Import your ban list
  - Generate iptables rules (reject banned peers at OS level)
- Overview of the last received blocks
- Overview of the last received forks (orphaned blocks / alternative chains)
- Overview of the memory pool and containing transactions
- Overview of the wallet (no functionality, information only)

## Requirements

- Bitcoin Core 0.20.0.1+
- Web server (e.g. Apache, nginx, PHP built-in web server)
- PHP 7.0.0+
  - cURL

## Installation

1. Download Bitcoin Node Manager either from [here](https://github.com/Mirobit/bitcoin-node-manager/releases) or by cloning this the repository.
2. Copy `src/Config.php.example` and remove `.example`. Open `src/Config.php` and enter your bitcoind RPC creditials and set the BNM password.
3. Make sure the BNM folder is in your web servers `www` folder. If the folder is publicly accesible, I recommend renaming the folder to something unique. Although BNM is password protected and access can be limited to a specific IP, there can be security flaws and bugs.
4. Open the URL to the folder in your browser and login with the password choosen in `src/Config.php`.
5. Optional: Run `chmod -R 770 /path-to-folder/{data, src, views}`. Only necessary for non Apache servers (`AllowOverride All` necessary), that are publicly accessible. For more information, read next section.
6. Optional: `txindex=1` is required in your `bitcoin.conf` for the `Blocks` page. Start bitcoind once with the `-reindex` param (this might take a while).

## Security

- All pages and control functionality are only accessible for logged in users. The only exception is if you use the `Run Rules' cron job functionality. But a password based token is requiered and the functionality is only able to apply rules.
- Access to BNM is by default limited to localhost. This can be expanded to a specific IP or disabled. If disabled, make sure to protect the BNM folder (.htaccess, rename it to something unique that an attacker will not guess). An attacker could "guess" your password, since there is no build-in brute force protection (if IP protection is disabled).
- The `data` folder contains your rules, logs and geo information about your peers. Make sure to protect (e.g. `chmod -R 770 data`) this sensitive information if your web server is publicly accessible. The previously mentioned IP protection doesn't work here. If you use `Apache` you are fine, since the folder is protected with `.htaccess` (make sure `AllowOverride All` is set in your `apache2.conf` file).

## Roadmap

- [ ] Improve project structure
- [ ] Improve OOP
- [ ] Improve error handling
- [ ] Import rules functionality
- [ ] More help icons
- [ ] Use popover for help
- [ ] Display expanded peer/block info (popup)
- [ ] More customization settings
- [ ] Highlight suspicious peers
- [ ] Sort mempool tx, request more
- [ ] Option to import blacklist of spy / resource wasting peers
