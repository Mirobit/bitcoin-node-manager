<?php

namespace App;

class Node {
  public $blockHeight;
  public $pruMode;
  public $chain;
  public $client;
  public $ipv4; // Bool: if ipv4 active
  public $ipv6; // Bool: if ipv6 active
  public $tor; // Bool: if tor active
  public $ipv4Address = 'Unknown';
  public $ipv6Address = 'Unknown';
  public $torAddress = 'Unknown';
  public $torVersion = 'Unkown';
  public $ipv4Proxy;
  public $ipv6Proxy;
  public $torProxy;
  public $toConn; // Int: total connections of node
  public $cTime; // Current node time
  public $uptime; // String: uptime of node
  public $serivces;
  public $proVer;
  public $localRelay;
  public $timeOffset;
  public $port;
  public $minRelayFee;
  public $mempoolTx;
  public $sizeOfmempoolTx;
  public $mempoolMinFee;
  public $maxMempool;
  public $mempoolUsage;
  public $mempoolUsageP;
  public $mempoolLimited; // Bool: If mempool is beeing limited
  public $tIn;
  public $tOut;
  public $tTotal; // Int : Total traffic
  public $tLimitSet; // Bool : If a t limit is set
  public $tLimited; // Bool: Is limit is active
  public $tUsed; // Int: In MB amount of t used in current cycle
  public $tMax; // Int: In MB the daily t limit
  public $tTimeLeft; // Int : Time in minutes that are left till the limit is reset
  public $tLimitP; // Int: Percentage of Limit used
  public $bHeight; // Int: current block height (as far as node knows)
  public $bHeightAgo; // Int: Minutes since last received block
  public $hHeight; // Int: current max header height (blocks not download by node)
  public $bcSize; // Int: in GB soze of blockchain
  public $diff; // Int: current network difficulty
  public $hashRate; // Int: current network hash rate
  public $mNetTime; // Int: current network mediatime
  public $softForks; // Arr: List of current forks
	
	function __construct() {
		global $bitcoind;
		$networkInfo = $bitcoind->getnetworkinfo();
		$mempoolInfo = $bitcoind->getmempoolinfo();
		$blockchainInfo = $bitcoind->getblockchaininfo();
		$miningInfo = $bitcoind->getmininginfo();
		$tInfo = $bitcoind->getnettotals();
		$uptimeInfo = $bitcoind->uptime();
		
		$this->blockHeight = checkInt($blockchainInfo["blocks"]);
		$this->pruMode = checkBool($blockchainInfo["pruned"]);
		$this->chain = ucfirst(htmlspecialchars($blockchainInfo["chain"]));
		//Get active networks
		$networks =$networkInfo["networks"];
		foreach($networks as $network){
			if($network["name"] === "ipv4"){
				$this->ipv4 = ($network["reachable"] ? true : false);
				$this->ipv4Proxy = $network["proxy"] ?? null;
			}
			elseif($network["name"] === "ipv6"){
				$this->ipv6 = ($network["reachable"] ? true : false);
				$this->ipv6Proxy = $network["proxy"] ?? null;
			}
			elseif($network["name"] === "onion"){
				$this->tor = ($network["reachable"] ? true : false);
				$this->torProxy = $network["proxy"] ?? null;
			}	
		}
    $ipAddresses = $networkInfo["localaddresses"];
		foreach($ipAddresses as $ipAddress){
			if(preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ipAddress["address"])){
				$this->ipv4Address = $ipAddress["address"];
			}
			elseif(preg_match("/^[0-9a-z]{1,4}(:[0-9a-z]{0,4}){1,7}$/", $ipAddress["address"])){
				$this->ipv6Address = $ipAddress["address"];
			}
			elseif(preg_match("/^[0-9a-z]{16}\.onion$/", $ipAddress["address"])){
        $this->torVersion = "v2";
        $this->torAddress = $ipAddress["address"];
			}elseif(preg_match("/^[0-9a-z]{56}\.onion$/", $ipAddress["address"])){
        $this->torVersion = "v3";
				$this->torAddress = $ipAddress["address"];
			}
		}
		$this->toConn = checkInt($networkInfo["connections"]);
		$this->uptime = timeToString($uptimeInfo);
		$this->client = str_replace('/','',htmlspecialchars($networkInfo["subversion"]));
		$this->proVer = checkInt($networkInfo["protocolversion"]);
		if(isset($networkInfo["localservicesnames"])) {
			$this->services = formatServices($networkInfo["localservicesnames"]);
		} else {
			$this->services = getServices($networkInfo["localservices"]);
		}

		$this->localRelay = checkBool($networkInfo["localrelay"]);
		$this->timeOffset = checkInt($networkInfo["timeoffset"]);
		$this->port = checkInt($networkInfo["localaddresses"][0]["port"] ?? 0);
		$this->cTime = getDateTime($tInfo["timemillis"]/1000);
		$this->minRelayFee = checkInt($networkInfo["relayfee"]);
		//Mempool
		$this->mempoolTx = checkInt($mempoolInfo["size"]);
		$this->mempoolSize =  round(checkInt($mempoolInfo["bytes"])/1000000,1);
		$this->mempoolMinFee = checkInt($mempoolInfo["mempoolminfee"]);
		$this->mempoolUsage = bytesToMb($mempoolInfo["usage"]);
		$this->maxMempool = bytesToMb($mempoolInfo["maxmempool"]);
		$this->mempoolUsageP = calcMpUsage($this->mempoolUsage,$this->maxMempool);
		$this->mempoolLimited = checkMemPoolLimited($this->mempoolMinFee, $this->minRelayFee);
		// Traffic
		$this->tIn = round(bytesToMb($tInfo["totalbytesrecv"])/1000,2);
		$this->tOut = round(bytesToMb($tInfo["totalbytessent"])/1000,2);
		$this->tTotal = $this->tIn + $this->tOut;
		$this->tLimitSet = getTrafficLimitSet($tInfo["uploadtarget"]["target"]);
		$this->tLimited = checkBool($tInfo["uploadtarget"]["target_reached"]);
		$this->tMax = bytesToGb($tInfo["uploadtarget"]["target"]);
		$this->tUsed = round($this->tMax - bytesToGb($tInfo["uploadtarget"]["bytes_left_in_cycle"]), 1);
		$this->tTimeLeft = round(checkInt($tInfo["uploadtarget"]["time_left_in_cycle"])/3600, 1); // In minutes
		if($this->tLimitSet){
			$this->tLimitP = ceil(($this->tUsed/$this->tMax)*100);
		}
		// Blockchain
		$this->bHeight = checkInt($blockchainInfo["blocks"]);
		$this->hHeight = checkInt($blockchainInfo["headers"]);
		
		$blockInfo = $bitcoind->getblock($blockchainInfo["bestblockhash"]);
		$this->bHeightAgo = round((time()-checkInt($blockInfo["time"]))/60, 1);
		$this->bcSize = bytesToGb($blockchainInfo["size_on_disk"], 1);
		$this->diff = checkInt($blockchainInfo["difficulty"]);
		$this->hashRate = round(checkInt($miningInfo["networkhashps"])/1000000000000000000,3);
		$this->mNetTime = getDateTime($blockchainInfo["mediantime"]);
		// Blockchain -> Soft forks
		if(isset($blockchainInfo["softforks"])) {
            		$this->softForks = checkSoftFork($blockchainInfo["softforks"]);
        	} else {
            		$this->softForks = [];
        	}
	}
}
