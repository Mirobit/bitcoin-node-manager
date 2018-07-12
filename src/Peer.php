<?php

namespace App;

class Peer{	
	public $id; // int
	public $ip; // string
	public $ipOriginal;
	public $ipv6; // bool
	public $services; // string
	public $servicesOriginal; // string
	public $relayTx; // bool
	public $feeFilter; // int (BTC)
	public $lastSend;  // int (seconds)
	public $lastReceived;  // int (seconds)
	public $trafficOut; // int (MB)
	public $trafficIn; // int (MB)
	public $traffic; // int (MB)
	public $connectTime; // string
	public $age; // int (seconds)
	public $timeOffSet; // int (seconds)
	public $ping; // int (seconds)
	public $minPing; // int (seconds)
	public $version; // int
	public $client; // string
	public $orgClient; // string
	public $spv; // bool
	public $snoopy; // bool
	public $altClient; // bool
	public $inbound; // bool
	public $startingHeight; // int
	public $banScore; // int
	public $syncedHeaders; // int
	public $syncedBlocks; // int
	//public $inflight; // array
	public $whitelisted; // bool
	public $bytessentPerMsg; // array
	public $bytesrecvPerMsg; // array
	public $country; // string
	public $countryCode; // string
	public $region; // string
	public $city; // string
	public $isp; // string
	public $hosted; // bool
	
	function __construct($peer) {
		$this->id = checkInt($peer["id"]);
		$this->ip = getCleanIP($peer["addr"]);
		$this->ipOriginal = checkIpPort($peer["addr"]);
		$this->ipv6 = checkIfIpv6($this->ip);
		$this->services = getServices($peer["services"]);
		$this->servicesOriginal = checkServiceString($peer["services"]);
		$this->relayTx = checkBool($peer["relaytxes"]);
		if(isset($peer["feefilter"])){
			$this->feeFilter = checkInt($peer["feefilter"]);
		}else{
			$this->feeFilter = 0;
		}
		$this->lastSend = checkInt($peer["lastsend"]);
		$this->lastReceived = checkInt($peer["lastrecv"]);
		$this->trafficOut = bytesToMb($peer["bytesrecv"]);
		$this->trafficIn = bytesToMb($peer["bytessent"]);
		$this->traffic = $this->trafficOut + $this->trafficIn;
		$this->connectTime = getDateTime($peer["conntime"]);
		$this->age = round((time()-$peer["conntime"])/60);
		$this->timeOffSet = checkInt($peer["timeoffset"]);
		if(isset($peer["pingtime"])){
			$this->ping = round(checkInt($peer["pingtime"]),2);
		}else{
			$this->ping = 0;
		}
		if(isset($peer["pingtime"])){
			$this->minPing = checkInt($peer["minping"]);
		}else{
			$this->minPing = 0;
		}
		$this->version = checkInt($peer["version"]);
		$this->client = getCleanClient($peer["subver"]);
		$this->orgClient = htmlspecialchars($peer["subver"]);
		$this->spv = checkSPV($this->client);
		$this->snoopy = checkSnooping($this->client);
		$this->altClient = checkAltClient($this->client);
		$this->inbound = checkBool($peer["inbound"]);
		$this->startingHeight = checkInt($peer["startingheight"]);
		$this->banScore = checkInt($peer["banscore"]);
		$this->syncedHeaders = checkInt($peer["synced_headers"]);
		$this->syncedBlocks = checkInt($peer["synced_blocks"]);
		//$this->inflight = $peer["inflight"];
		$this->whitelisted = checkBool($peer["whitelisted"]);
		$this->bytessentPerMsg = checkArray($peer["bytessent_per_msg"]);
		$this->bytesrecvPerMsg = checkArray($peer["bytesrecv_per_msg"]);		
	}			
}
?>