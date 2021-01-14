<?php

namespace App;

function formatServices($servicesArray) {
	$services = [];

	if(empty($servicesArray)) {
		$services['None'] = "None";
		return $services;
	}

	foreach($servicesArray as $service) {
		$name = ucwords(strtolower(str_replace("_", " ", $service)));
		$words = str_word_count($name, 2);
		$shortName = '';
		foreach($words as $word) {
			$shortName .= substr($word, 0, 1);
		}
		$services[$name] = $shortName;
	}

	return $services;
}

function getServices2($hex){
	$bit = base_convert($hex, 16, 2);
	$services = [];

	// 1 = Network, 2 = Getutxo, 3 = Bloom, 4 = Witness, 5 = Xthin, 6 = Cash, 7 = Segwit2X, 10 = Network Limited
	// No services
	if($bit === "0"){
		$services['None'] = "None";
		return $services;
	}

	// Fixed length, no if(lenght < xx) necessary
	$bit = sprintf('%010d', $bit);

	if(substr($bit, -1) == 1){
		$services['Network'] = "N";
	}
	if(substr($bit, -2, 1) == 1){
		$services['Getutxo'] = "GT";
	}
	if(substr($bit, -3, 1) == 1){
		$services['Bloom'] = "BL";
	}
	if(substr($bit, -4, 1) == 1){
		$services['Witness'] = "WI";
	}
	if(substr($bit, -5, 1) == 1){
		$services['Xthin'] = "XT";
	}
	if(substr($bit, -6, 1) == 1){
		$services['Cash'] = "CA";
  }
  if(substr($bit, -7, 1) == 1){
		$services['Compact Filters'] = "CF";
	}
	if(substr($bit, -8, 1) == 1){
		$services['Segwit2X'] = "2X";
	}
	if(substr($bit, -11, 1) == 1){
		$services['Network Limited'] = "NL";
	}

	// Unknown services
	if(empty($services)){
		$services['Unknown'] = "Unknown";
	}

	return $services;
}

function getVoting($hex){
	$vote = [];
	if($hex[7] == 2) {
		$vote['Segwit'] = true;
	}
	if($hex[6] == 1) {
		$vote['BIP91'] = true;
	}
	return $vote;
}

function checkAsicBoost($versionHex){
	if($versionHex[0] == "3" || $versionHex[2] == "8" || $versionHex[2] == "4" || $versionHex[2] == "c" || $versionHex[4] == "e") {
		return true;
	}else{
		return false;
	}
}

function checkInt($int){
	if(!is_numeric($int)){
		$int = 0;
	}
	return $int;
}

function getCleanIP($ip){
	$ip = checkIpPort($ip);
	$ip = preg_replace("/:[0-9]{1,5}$/", "", $ip);
	$ip = str_replace(array('[', ']'), '', $ip);
	return $ip;
}


function checkIpBanList($ip){
	if(preg_match("/^[0-9a-z:\.]{7,39}\/[0-9]{1,3}$/", $ip)) {
		return TRUE;
	}else{
		return FALSE;
	}
}

function checkIfIpv6($ip){
	if(preg_match("/]|:/",$ip)){
		return true;
	}else{
		return false;
	}
}

function checkIpPort($ip){
	if(preg_match("/^\[{0,1}[0-9a-z:\.]{7,39}\]{0,1}:[0-9]{1,5}$/", $ip)) {
		return $ip;
	}else{
		return "unknown";
	}
}

function checkBool($bool){
	if(is_bool($bool)){
		return $bool;
	}else{
		return false;
	}
}

function checkServiceString($services){
	if(preg_match("/^[0-9a-z]{16}$/",$services)){
		return $services;
	}else{
		return "unknown";
	}
}

function checkArray($array){
	foreach ($array as $key => $value){
		if(!preg_match("/^[a-z\*]{2,11}$/",$key) OR !is_int($value)){
			unset($array[$key]);
		}
	}
	return $array;
}

function checkCountryCode($countryCode){
	if(preg_match("/^[A-Z]{2}$/", $countryCode)){
		return $countryCode;
	}else{
		return "UN";
	}
}

function checkString($string){
	$string = substr($string,0,50);
	if(preg_match("/^[0-9a-zA-Z- \.,&()]{2,50}$/",$string)){
		return $string;
	}else{
		return "Unknown";
	}
}

function checkSegWitTx($size, $vsize){
	$segwit = false;
	if($size != $vsize){
		$segwit = true;
	}

	return $segwit;
}

function getSegWitTx($txs){
	$i = 0;
	foreach($txs as $tx){
		if(checkSegWitTx($tx["size"], $tx["vsize"])){
			$i++;
		}
	}
	return $i;
}

function checkHosted($hoster){
	$hosterList = json_decode(file_get_contents('data/hoster.json'), true);
	if (in_array($hoster, $hosterList) OR preg_match("/server/i",$hoster)){
		return true;
	}else{
		return false;
	}
}

function updateHosted($hoster, $hosted){
	$peers = file_get_contents('data/geodatapeers.inc');
	$peers = unserialize($peers);
	foreach($peers as &$peer){
		if ($peer[3] == $hoster){
			$peer[4] = $hosted;
		}
	}
	file_put_contents('data/geodatapeers.inc',serialize($peers));
}

function bytesToMb($size, int $round = 1){
	$size = round(checkInt($size) / 1000000,$round);
	return $size;
}

function bytesToGb($size, int $round = 1){
	$size = round(checkInt($size) / 1000000000,$round);
	return $size;
}

function getDateTime($timestamp){
	$date = date("Y-m-d H:i:s",$timestamp);
	return $date;
}

function timeToString($time){
	$seconds = checkInt($time);
	switch (true) {
		case $seconds < 60:
			return $seconds . " seconds";
    case $seconds < 3600:
      return round($seconds/60) . " minutes";
    case $seconds < 86400:
			return round($seconds/3600) . " hours";
		default:
			return round($seconds/86400) . " days";
	}
}

function checkMemPoolLimited($memPoolFee, $relayTxFee){
	$result = false;
	if($memPoolFee > $relayTxFee){
		$result = true;
	}
	return $result;
}

function checkSoftFork($softForks){
	foreach($softForks as $name => &$sf){
		if($sf['type'] === 'bip9' && $sf['bip9']['status'] === "started"){
			if(!preg_match("/[A-Za-z0-9 ]{2,25}/", $name)){
				unset($softForks[$name]);
				continue;
			}
			$sf['status'] = ucfirst(preg_replace("/[^A-Za-z]/", '', $sf['bip9']['status']));
			$sf['start_time'] = date("Y-m-d",$sf['bip9']['start_time']);
			$sf['timeout'] = date("Y-m-d",$sf['bip9']['timeout']);
			$sf['since'] = checkInt($sf['bip9']['since']);
			if(isset($sf['bip9']['statistics'])){
				$sf['process'] = round(($sf['bip9']['statistics']['count']/$sf['bip9']['statistics']['period'])*100,1);
			}
		}else{
			unset($softForks[$name]);
		}
	}
	return $softForks;
}

function getTrafficLimitSet($target){
	$result = false;
	if($target != 0) {
		$result = true;
	}
	return $result;
}

function calcMpUsage($usage, $max){
	$value = ceil(($usage/$max)*100);
	if($value <= 50){
		$icon = "fa-battery-1";
		$color = "green";
	}elseif($value > 50 AND $value < 80){
		$icon = "fa-battery-2";
		$color = "orange";
	}else{
		$icon = "fa-battery-3";
		$color = "red";
	}
	$usageP = array('value' => $value, 'color' => $color, 'icon' => $icon);
	return $usageP;

}

function getBanReason($banreason){
	switch ($banreason) {
		case "manually added":
			$banreason = 'User';
			break;
		case "node misbehaving":
			$banreason = 'Core';
			break;
		default:
			$banreason = 'Unknown';
			break;
	}
	return $banreason;
}

function getCleanClient($client){
	$client =  ltrim($client,"/");
	$client =  rtrim($client,"/");

	if(preg_match("/^Satoshi:([0]\.[0-9]{1,2}\.[0-9]{1,2})/",$client, $matches)) {
		$client = "Core ".$matches[1];
	}elseif(preg_match("/^Satoshi:([1]\.[0-9]{1,2}\.[0-9]{1,2})/",$client, $matches)) {
		$client = "btc1 ".$matches[1];
	}elseif(preg_match("/MultiBitHD:([0-9]\.[0-9]{1,2}\.[0-9]{1,2})/i",$client, $matches)){
		$client = "MultiBit HD ".$matches[1];
	}elseif(preg_match("/MultiBit:([0-9]\.[0-9]{1,2}\.[0-9]{1,2})/i",$client, $matches)){
		$client = "MultiBit ".$matches[1];
	}elseif(preg_match("/Bitcoin Wallet:([0-9]\.[0-9]{1,2})/i",$client, $matches)){
		$client = "Bitcoin Wallet ".$matches[1];
	}else{
		$replace = array(":", "-SNAPSHOT", "\"", "'", "<", ">", "=");
		$client = str_replace($replace, " ", $client);
	}
	return $client;
}

function checkSPV($client){
	if (preg_match('/MultiBit|bitcoinj|bread/i',$client)){
		return true;
	}else{
		return false;
	}
}

function checkMonitoring($client){
	if (preg_match('/Snoopy|Coinscope|bitnodes|dsn.tm.kit.edu|multiven|walle /i',$client)){
		return true;
	}else{
		return false;
	}
}

function checkAltClient($client){
	if (preg_match('/Unlimited|Classic|XT|ABC|BUCash|bcoin/i',$client)){
		return true;
	}else{
		return false;
	}
}


// Creates chart and legend (list)
function getTopClients($peers){
	$clients = [];
	$chartLabels = "";
	$chartValue = "";

	foreach($peers as $peer){
		if(isset($clients[$peer->client])){
			$clients[substr($peer->client,0,27)]['count']++;
		}else{
			$clients[substr($peer->client,0,27)]['count'] = 1;
		}
	}

	$peerCount = count($peers);
	$clientCount = count($clients);
	arsort($clients);
	$clients = array_slice($clients,0,9);
	if($clientCount > 9){
		$clients['Other']['count'] = $clientCount-9;
	}


	foreach($clients as $cName => &$client){
		$chartLabels .= '"'.$cName.'",';
		$chartValue .= $client['count'].',';
		$client['share'] = round($client['count']/$peerCount,2)*100;
	}

	$chartData['labels'] = rtrim($chartLabels, ",");
	$chartData['values'] = rtrim($chartValue, ",");
	$chartData['legend'] = $clients;

	return $chartData;
}


function getMostPop($peers){
	$segWitCount = 0;
	$clCountAr = [];
	$ctCountAr = [];
	$htCountAr = [];
	$result = [];

	foreach($peers as $peer){
		// Count Witness
		if(isset($peer->services['Witness']) AND $peer->services['Witness']){
			$segWitCount++;
		}

		// Count Client 1
		if(array_key_exists($peer->client,$clCountAr)){
			$clCountAr[$peer->client]++;
		}else{
			$clCountAr[$peer->client] = 1;
		}

		if(CONFIG::PEERS_GEO){
			// Count Country 1
			if(array_key_exists($peer->countryCode,$ctCountAr)){
				$ctCountAr[$peer->countryCode]++;
			}else{
				$ctCountAr[$peer->countryCode] = 1;
			}

			// Count ISP 1
			if(array_key_exists($peer->isp,$htCountAr)){
				$htCountAr[$peer->isp]++;
			}else{
				$htCountAr[$peer->isp] = 1;
			}
		}
	}

	// Count Client 2
	arsort($clCountAr);
	$result['mpCli'] = key($clCountAr);
	$result['mpCliC'] = reset($clCountAr);

	if(CONFIG::PEERS_GEO){
		// Count Country 2
		arsort($ctCountAr);
		$result['mpCou'] = key($ctCountAr);
		$result['mpCouC'] = reset($ctCountAr);

		// Count ISP 2
		arsort($htCountAr);
		$result['mpIsp'] = substr(key($htCountAr),0,8);
		$result['mpIspC'] = reset($htCountAr);
	}

	$result['segWitC'] = $segWitCount;
	return $result;
}


// Peer functions

function getPeerData(bool $geo = CONFIG::PEERS_GEO){
  global $bitcoind;
  $peersData = [];

	$peerInfo = $bitcoind->getpeerinfo();

	if($geo){
		$peersData = createPeersGeo($peerInfo);
	}else{
    $peersData["peers"] = [];
    $peersData["cTraffic"] = 0;
    $peersData["cTrafficIn"] = 0;
    $peersData["cTrafficOut"] = 0;
    $peersData["newPeersCount"] = 0;

    foreach($peerInfo as $peer){
      $peerObj = new Peer($peer);
      $peersData["peers"][] = $peerObj;
      $peersData["cTraffic"] += $peerObj->traffic;
      $peersData["cTrafficIn"] += $peerObj->trafficIn;
      $peersData["cTrafficOut"] += $peerObj->trafficOut;
    }
	}

	return $peersData;
}

function createPeersGeo($peerinfo){
	$peerData['countryList'] = [];
	$peerData["peers"] = [];
	// Current traffic
	$peerData["cTraffic"] = 0;
	$peerData["cTrafficIn"] = 0;
	$peerData["cTrafficOut"] = 0;
	$peerData["newPeersCount"] = 0;
	// Not used yet
	$peerData["hosterCount"] = 0;
	$peerData["privateCount"] = 0;
	$arrayPeers = [];
	$noGeoData = false;

  // Check if peer file exists and enabled
  if (file_exists('data/geodatapeers.inc')){
    // Loads serialized stored peers from disk
    $serializedPeers = file_get_contents('data/geodatapeers.inc');
    $arrayPeers = unserialize($serializedPeers);
    // Check if client was restarted and IDs reassigned
    $oldestPeerId = reset($peerinfo)["id"];
    $oldestPeerIp = getCleanIP(reset($peerinfo)["addr"]);
    $delete = false;
    // Checks if we know about the oldest peer, if not we assume that we don't known any peer
		foreach($arrayPeers as $key => $peer){
			if($oldestPeerIp == $peer[0]){
				$delete = true;
				// Either bitcoind was restarted or peer reconnected. Since peer is the oldest, all other peers we known disconnected
				if($oldestPeerId != $key){
					$delete = false;
				}
				break;
			}
			// For removing old peers that disconnected. Value of all peers that are still conected will be changed to 1 later. All peers with 0 at the end of the function will be deleted.
			$arrayPeers[$key][5] = 0;
		}
		// Oldest peer hasn't shown up -> Node isn't connected to any of the previously stored peers
		if(!$delete){
			unset($arrayPeers);
			$noGeoData = true;
		}
	}else{
		$noGeoData = true;
	}

	// Find Ips that we don't have geo data for and that are "older" than 5 minutes
	// First interation through all peers is used to collect ips for geo api call. This way the batch functionality can be used
  $ips = [];
  $ipData = [];

	foreach($peerinfo as $peer){
		$tempIP = getCleanIP($peer['addr']);
		$age = round((time()-$peer["conntime"])/60);
		if ($age >  5 AND ($noGeoData OR !in_array($tempIP,array_column($arrayPeers,0)))){
			$ips[] = $tempIP;
		}
	}

	if(!empty($ips)){
    $apiData = getIpData($ips);
    $ipData = $apiData['geojson'];
    $peerData['api'] = $apiData['api'];
  }

	// 2nd interation through peers to create final peer list for output
	foreach($peerinfo as $peer){
		// Creates new peer object
		$peerObj = new Peer($peer);

		// Checks if peer is new or if we can read data from disk (geodatapeers.inc)
		if($noGeoData OR !in_array($peerObj->ip,array_column($arrayPeers,0))){
      $index = array_search($peerObj->ip, array_column($ipData, 'query'));
			if(isset($ipData[0]) AND $peerObj->age > 5 AND is_numeric($index)){
				$ipInfo = $ipData[$index];
				$countryCode = checkCountryCode($ipInfo['countryCode']);
				$country = checkString($ipInfo['country']);
				$isp = checkString($ipInfo['isp']);
				$hosted = checkHosted($isp);
				// Adds the new peer to the save list
        $arrayPeers[$peerObj->id] = array($peerObj->ip, $countryCode, $country, $isp, $hosted, 1);
        // Only counted for peers older than 5 minutes
				$peerData["newPeersCount"]++;
			}elseif($peerObj->age > 5){
				// If IP-Api.com call failed we set all data to Unknown and don't store the data
				$countryCode = "UN";
				$country = "Unknown";
				$isp = "Unknown";
				$hosted = false;
				// Only counted for peers older than 5 minutes
				$peerData["newPeersCount"]++;
			}else{
				// If peer is younger than 5 minutes
				$countryCode = "NX";
				$country = "New";
				$isp = "New";
				$hosted = false;
			}
		}else{
			$id = $peerObj->id;
			// Nodes that we know about but reconnected
			if(!isset($arrayPeers[$id])){
				$id = array_search($peerObj->ip, array_column($arrayPeers,0));
				$id = array_keys($arrayPeers)[$id];
			}
			$countryCode = $arrayPeers[$id][1];
			$country = $arrayPeers[$id][2];
			$isp = $arrayPeers[$id][3];
			$hosted = $arrayPeers[$id][4];
			$arrayPeers[$id][5] = 1;
		}

		// Counts the countries
		if(isset($peerData['countryList'][$country])){
      $peerData['countryList'][$country]['count']++;
		}else{
			$peerData['countryList'][$country]['code'] = $countryCode;
			$peerData['countryList'][$country]['count'] = 1;
		}

		// Adds country data to peer object
		$peerObj->countryCode = $countryCode;
		$peerObj->country = $country;
		$peerObj->isp = $isp;
		$peerObj->hosted = $hosted;
		if($hosted){
			$peerData["hosterCount"]++;
		}else{
			$peerData["privateCount"]++;
		}
		// Adds traffic of each peer to total traffic (in MB)
		$peerData["cTraffic"] += $peerObj->traffic;
		$peerData["cTrafficIn"] += $peerObj->trafficIn;
		$peerData["cTrafficOut"] += $peerObj->trafficOut;

		// Adds peer to peer array
		$peerData["peers"][] = $peerObj;

	}



  if(!empty($arrayPeers)) {
    // Removes all peers that the node is not connected to anymore.
    foreach($arrayPeers as $key => $peer){
      if($peer[5] === 0){
        unset($arrayPeers[$key]);
      }
    }

    $newSerializePeers = serialize($arrayPeers);
    file_put_contents('data/geodatapeers.inc', $newSerializePeers);
  }

	return $peerData;
}

function getIpData($ips){
	global $error;
  $apiData['api']['callc'] = 0;

  // ip-api.com allows 15 requests with 100 ips per minute. The limit here is lower since
  // new peers could connect within a minute a trigger additional calls.
  $numOfIps = count($ips);
	if($numOfIps > 1200) $numOfIps = 1200;

  $apiData['api']['ipc'] = $numOfIps;
	$j = 0;
	// A mamxium of 100 Ips can be checked per API call (limit by ip-api.com)
	$m = 100;
	// Creates Postvar data with a maximum of 100 IPs per request
	while($j < $numOfIps){
		if($numOfIps-$j < 100){
			$m=$numOfIps-$j;
		}
		for($i = 0; $i < $m; $i++){
			$postvars[$j][] =  array("query" => $ips[$i+$j]);
		}
		$j += $i;
  }

	// Curl
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_URL,'http://ip-api.com/batch?fields=query,country,countryCode,city,isp,status');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , CONFIG::PEERS_GEO_TIMEOUT);
	curl_setopt($ch, CURLOPT_TIMEOUT, CONFIG::PEERS_GEO_TIMEOUT+1);

	// One call for each 100 ips
	$apiData['geojson'] = [];
	foreach($postvars as $postvar){
    $apiData['api']['callc']++;
		$postvarJson = json_encode($postvar);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $postvarJson);
		$result = json_decode(curl_exec($ch),true);
		if(empty($result)){
			$error = "Geo API (ip-api.com) Timeout";
			$result = [];
		}
		$apiData['geojson'] = array_merge($apiData['geojson'], $result);
  }

	return $apiData;
}

function createMapJs(int $peerCount, array $countryList){

	// Sorting country list
	function compare($a, $b)
	{
		return $b['count'] - $a['count'];
	}
	uasort($countryList, "App\compare");

	$i = 0;
	$jqvData = 'var peerData = {';
	$mapDesc = [];

	// Creates map Legend. Top 9 countries + Others
	foreach($countryList as $countryName => $country){
		$jqvData .= "\"".strtolower($country['code'])."\":".$country['count'].",";

		if($i<9){
			$mapDesc[$countryName] = $country;
			$i++;
		}else{
			if(isset($mapDesc['Other']['count'])){
				$mapDesc['Other']['count']++;
			}else{
				$mapDesc['Other']['count'] = 1;
			}
		}
	}

	foreach($mapDesc as &$country){
		$country['share'] = round($country['count']/$peerCount,2)*100;
	}

	$jqvData = rtrim($jqvData, ",");
	$jqvData .= '};';

	// Writes data file for JVQMap
	//file_put_contents('data/countries.js',$jqvData);
	$map['data'] = $jqvData;
	$map['desc'] = $mapDesc;

	return $map;
}

?>
