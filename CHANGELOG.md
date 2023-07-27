# Bitcoin Node Manager Changelog

## 1.4.0 - 2023-07-27

The release is important for the compatibility with Bitcoin Core 25.0.0

- [**Improved**] Symbol for Inbound/Outbound in peer list ([dc74dbe](https://github.com/Mirobit/bitcoin-node-manager/commit/dc74dbe0f7dfa9c7508756ca6f4189d9653f6eaa))
- [**Improved**] I2P detection ([42e646b](https://github.com/Mirobit/bitcoin-node-manager/commit/42e646b30d15882a83db13868c95da50cfb924a9))
- [**Improved**] Show watchonly balance (thanks @[MontejoJorge](https://github.com/MontejoJorge), [b639364](https://github.com/Mirobit/bitcoin-node-manager/commit/b6393649055e145b51b21a9184720475dce34c97))
- [**Fixed**] Show node ip address with the highest score ([d0a16c9](https://github.com/Mirobit/bitcoin-node-manager/commit/d0a16c9b7c8453ff35a8a9b2a7dd72aa77cc6286))
- [**Fixed**] Show Tor address instead of unkown ([da4f362](https://github.com/Mirobit/bitcoin-node-manager/commit/da4f362e2674aa39349d72571ce840c642b6e6a3))
- [**Fixed**] Latest block number ([83eb9ef](https://github.com/Mirobit/bitcoin-node-manager/commit/83eb9efe40ba529b440bdf8743fee5db65215935))
- [**Fixed**] Replace deprecated rpc call ([04a805c](https://github.com/Mirobit/bitcoin-node-manager/commit/04a805c82170d038e5621a830b04c7d28aff15bb))
- [**Fixed**] Old clients softfork info (thanks @[d-rez](https://github.com/d-rez), [ae67b7c](https://github.com/Mirobit/bitcoin-node-manager/commit/ae67b7ca7d3c9da3a1ed64faaf51a3d5df07604d))
- [**Fixed**] Set session token if not password is set (thanks @[MontejoJorge](https://github.com/MontejoJorge), [8d8adc1](https://github.com/Mirobit/bitcoin-node-manager/commit/8d8adc12c393dddd3205f34442dbcb894440fc87))
- [**Fixed**] Typo (thanks @[MontejoJorge](https://github.com/MontejoJorge), [4794b8b](https://github.com/Mirobit/bitcoin-node-manager/commit/4794b8bd02f7da7044b7629917c85881cb89b8a3))
- [**Fixed**] Typo (thanks @[oonqt](https://github.com/oonqt), [d6d9ffe](https://github.com/Mirobit/bitcoin-node-manager/commit/d6d9ffe34ad9736c3c81c183b29ce4b537b0ee6c))

## 1.3.0 - 2021-02-23

This release brings proxy support und improves the docker support.

- [**New**] BNM can connect through a proxy to the Bitcoin Core RPC
- [**Improved**] You no longer need to set a password for BNM
- [**Improved**] `txindex=1` is no longer required
- [**Improved**] Docker documentation
- [**Improved**] Tor address styling
- [**Fixed**] Run cron job rules
- [**Fixed**] Show if own node uses compact filters
- [**Fixed**] Docker command
- [**Fixed**] IPv6 address detection

## 1.2.0 - 2021-01-17

The release is important for the compatibility with Bitcoin Core 0.21.0

- [**New**] You can now run BNM via Docker Compose (see the README)
- [**Improved**] See if a peer is incoming our outgoing (mouseover IP)
- [**Improved**] Moved ping column to IP column (mouseover)
- [**Improved**] Detect if peers support Compact Filters
- [**Improved**] More detailed error messages if something is wrong with the Core RPC
- [**Improved**] More reliable RPC calls
- [**Fixed**] Mobile layout
- [**Fixed**] Whitelist check (0.21.0 compatibility)
- [**Fixed**] Removed banscore (0.21.0 compatibility)
- [**Fixed**] Onion v3 address detection (0.21.0 compatibility)
- [**Fixed**] Removed list of memory pools transaction due to size (>100MB)
- [**Fixed**] Softfork voting time

## 1.1.0 - 2020-12-19

The release brings some new features and a few bug fixes.

- [**New**] You can click on countries and clients in the main page to see a filtered peer list
- [**New**] There is now a mobile menu
- [**Improved**] Hover over the traffic of a peer to see more detailed traffic information
- [**Improved**] Rules ignore by default all whitelisted peers
- [**Fixed**] Geo settings GUI
- [**Fixed**] Geo data file cleanup
- [**Fixed**] Peer list with deactivated geo data

## 1.0.0 - 2020-12-13

Three years after the first release, 1.0.0 is finally out :partying_face:

- [**New**] On the Main (Global Peer Distribution next to the help icon) and the Peers page (next to the help icon) is a small refresh icon displayed if Geo API calls were made
- [**Improved**] Link to block explorer that displays forks
- [**Improved**] Updated hoster detection list
- [**Improved**] Dynamic services detection
- [**Fixed**] Compatibility with Bitcoin Core 0.20.1
- [**Fixed**] Country flag icons
- [**Fixed**] Compatibility with IP-API.com limits
- [**Fixed**] Peer geo data
- [**Fixed**] Forks counter
- [**Fixed**] PHP Error namespace
- [**Fixed**] Wallet page

## 0.6.0 Beta - 2020-05-12

This will delete your `src\Config.php`. Copy the `src\Config.php.example`, remove `.example` and change your settings. Starting this release, `git pull` will not mess with your `src\Config.php` anymore.

- [**New**] Config.php.example to avoid git conflicts, git ignores Config.php
- [**New**] Proxy icon on main page if proxy is set
- [**Improved**] Modern favicon
- [**Improved**] More sensible units on main and blocks page
- [**Improved**] Show full peer client and isp on hover
- [**Improved**] Use wallet rpc calls only on wallet page
- [**Improved**] Wallet, peer and block page layout
- [**Fixed**] Ban information if zero bans
- [**Fixed**] Block fee calculation (Thanks to [TotalBuzzKit](https://github.com/drkskwlkr))

## 0.5.0 Beta - 2020-04-29

Please delete `data/geodatapeers.inc` to avoid any peer information issues

- [**New**] Display node uptime on start page
- [**New**] You can now execute your rules via command line. `php /path/to/your/index.php yourtoken` (see rules page for more information)
- [**Improved**] Better protection against XSS and other attacks
- [**Improved**] Layout improvements
- [**Fixed**] Block voting detection
- [**Fixed**] Execute rules
- [**Fixed**] Session error
- [**Fixed**] Saving geodata fpr new peers
- [**Fixed**] Various asset imports
- [**Fixed**] Removed unused .js files
- [**Fixed**] Removed unused variables

## 0.4.1 Beta - 2020-04-27

### Improved

- Peer page styling
- Hoster list
- Code cleanup

### Fixed

- Wallet page information
- External links
- GeoTracing GUI setting
- Soft fork dection (for >0.19.0.1)
- Network detection
- Various client stats

## 0.4.0 Beta - 2020-01-07

### New

- Display blockchain size on main page

### Improved

- New icons for some information
- Add tooltips for peer icons
- Simplify wallet code
- Update hoster list

## 0.3.0 Beta - 2019-06-16

### New

- AsicBoost dectection for blocks
- Icon for monitoring/spying nodes (like bitnodes)

### Improved

- Remove duplicate files
- Simplify code

### Fixed

- Ipv6 detection
- Multiple IPs

## 0.2.0 Beta - 2018-07-12

### New

- Wallet Overview

## 0.1.0 Beta - 2017-08-03

### First Release
