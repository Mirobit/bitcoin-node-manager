<?php

	namespace App;

	class Config {

	// Bitcoin Node Manager (BNM) password for login. You should additionally change the name of
	// BNM folder to something unique, if accessible via the web. You can also limit the access
	// to a specific IP with the option below.
	const PASSWORD = "LOGIN-PASSWORD";
	// IP that can can access BNM (by default only localhost (IPv4/v6) can access BNM).
	// If empty (""), any IP can access BNM. If "localhost", only localhost can access BNM.
	// If specific IP (e.g. "84.12.32.297"), localhost and the specific IP can access BNM.
	const ACCESS_IP = "localhost";


	// IP:Port of bitcoind RPC server. Usually localhost. Be careful with remote servers. The connection is not encrypted.
	// DOCKER:
	// -> Windows/Mac: host.docker.internal:8332
	// -> Linux: 172.17.0.1:8332 (Don't forget to open the port since docker uses by default a different network interface: 
	// 'sudo ufw allow in on docker0 from 172.17.0.0/16 to any port 8332')
	const RPC_IP = "127.0.0.1:8332";
	// RPC username / rpcauth specified in bitcoin.conf
	const RPC_USER = "USERNAME";
	// RPC password / rpcauth specified in bitcoin.conf
	const RPC_PASSWORD = "PASSWORD";


	// Ip-api.com is used to get the country and the ISP of peers. API is limited to 15 requests per minutes.
	// Peer geo data is stored as long as the peer is connected. A page reload (main/peers) only
	// causes an API request if new peers connected (older than 5 minutes) since the last load. Up to
	// 100 ips/peers are checked per request. You should not run into any trouble with the API limit.
	// Set the FALSE to not use the Geo API.
	const PEERS_GEO = TRUE;
	// Maximum of seconds to wait for response from ip-api.com
	const PEERS_GEO_TIMEOUT = 2;

	// Number of TXs displayed on mempool page
	const DISPLAY_TXS = 100;
	// Number of blocks displayed on blocks page
	const DISPLAY_BLOCKS = 25;
	// Number of forks displayed on forks page
	const DISPLAY_FORKS = 25;
	}
?>
