<?php

namespace App;

ini_set('display_startup_errors',1); 
ini_set('display_errors',1);
error_reporting(1);

require_once 'src/Autoloader.php';
Autoloader::register();

// Check IP, deny access if not allowed
if(!(empty(Config::ACCESS_IP) OR $_SERVER['REMOTE_ADDR'] == "127.0.0.1" OR $_SERVER['REMOTE_ADDR'] == "::1" OR $_SERVER['REMOTE_ADDR'] == Config::ACCESS_IP)){
	header('Location: login.html');
	exit; 
}

// Cronjob Rule Run
if(isset($_GET['job']) AND $_GET['job'] === substr(hash('sha256', Config::PASSWORD."ebe8d532"),0,24)){
	require_once 'src/Utility.php';
	$bitcoind = new jsonRPCClient('http://'.Config::RPC_USER.':'.Config::RPC_PASSWORD.'@'.Config::RPC_IP.'/');
	Rule::run();
	exit;
}


// Start check user session
session_start();
$passToken = hash('sha256', Config::PASSWORD."ibe81rn6");

// Active Session
if(isset($_SESSION['login']) AND $_SESSION['login'] === TRUE){
	// Nothing needs to be done
	
// Login Cookie available	
}elseif(isset($_COOKIE["Login"]) AND $_COOKIE["Login"] == $passToken){
		$_SESSION['login'] = TRUE;
		$_SESSION["csfrToken"] = hash('sha256', random_bytes(20));

// Login		
}elseif(!isset($_SESSION['login']) AND isset($_POST['password']) AND $_POST['password'] == Config::PASSWORD){
	ini_set('session.cookie_httponly', '1');
	$passHashed = hash('sha256', Config::PASSWORD);
	
		$_SESSION['login'] = TRUE;
		$_SESSION["csfrToken"] = hash('sha256', random_bytes(20));
		if(isset($_POST['stayloggedin'])){		
			setcookie("Login", $passToken, time()+2592000, "","",FALSE, TRUE);
		}

// Not logged in or invalid data
}else{
	header('Location: login.html');
	exit; 	
}

// Load ulitily and content creator functions
require_once 'src/Utility.php';
require_once 'src/Content.php';

// Globals
$error = "";
$message = "";
$bitcoind = new jsonRPCClient('http://'.Config::RPC_USER.':'.Config::RPC_PASSWORD.'@'.Config::RPC_IP.'/');

// Content
// Main Page
if(empty($_GET) OR $_GET['p'] == "main") {   
	try{
	$content = createMainContent();
	}catch(\Exception $e) {
	   $error = "Node offline or incorrect RPC data";
	}
	$data = array('section' => 'main', 'title' => 'Home', 'content' => $content);   
   
// Peers Page   
}elseif($_GET['p'] == "peers") {
	
	// Check if command
	if(isset($_GET['c']) AND $_GET['t'] == $_SESSION["csfrToken"]){
		// Ban Command
		if($_GET['c'] == "ban"){
			$err = 0;
			if(preg_match("/^([0-9a-z:\.]{7,39})$/", $_GET['ip'], $match)) {
				$ip = $match[1];
			}else{
				$err += 1;
			}
			if(is_numeric($_GET['time'])) {
				$bantime = intval($_GET['time']);
			}else{
				$err += 1;
			}
			if($err == 0){
				try {
					$result = $bitcoind->setban($ip, "add", $bantime);
					// Sleep necessary otherwise peer is still returned by bitcoin core
					sleep(1);
					$message = "Peer successfully banned";
				} catch (\Exception $e) {
					$error = "Peer could not be banned";
				}
			}else{
				$error = "Invalid Peer/Ban time";
			}
			
		// Disconnect Command
		}elseif($_GET['c'] == "disconnect"){
			if(preg_match("/^(\[{0,1}[0-9a-z:\.]{7,39}\]{0,1}:[0-9]{1,5})$/", $_GET['ip'], $match)) {
				$ip = $match[1];
				try {
					$result = $bitcoind->disconnectnode($ip);
					// Sleep necessary otherwise peer is still returned by bitcoin core
					sleep(1);
					$message = "Peer successfully disconnected";
				} catch (\Exception $e) {
					$error = "Peer could not be found";
				}
			}
		// Add Hoster Command
		}elseif($_GET['c'] == "addhoster"){
			if(preg_match("/^[0-9a-zA-Z-,\. ]{3,40}$/", $_GET['n'])) {
				$hosterJson = file_get_contents('data/hoster.json');
				$hoster = json_decode($hosterJson);
				$hoster[] = $_GET['n'];
				file_put_contents('data/hoster.json',json_encode($hoster));
				updateHosted($_GET['n'], true);
				$message = "Hoster succesfully added";	
			}else{
				$error = "Invalid Hoster";
			}
		}
		// Apply rules		  
		elseif($_GET['c'] == "run"){
			try{
				Rule::run();
			}catch(\Exception $e){
				$error = "Error while running rules";
			}
			if(empty($e)){
				$message = "Rules succesfully run. See log file for details";
			}
		}
	}
	// Information for header
	$content = createPeerContent();
	
	// Create page specfic variables
	$data = array('section' => 'peers', 'title' => 'Peers', 'content' => $content);

// Hoster Page	
}elseif($_GET['p'] == "hoster") {	
	
	$hosterList = json_decode(file_get_contents('data/hoster.json'),true);

	if(isset($_GET['c']) AND $_GET['t'] == $_SESSION["csfrToken"]){
	// Remove Hoster Command
		if($_GET['c'] == "remove"){
			if(preg_match("/^[0-9a-zA-Z-,\. ]{3,40}$/", $_GET['n'])) {
				if(($key = array_search($_GET['n'], $hosterList)) != false) {
					unset($hosterList[$key]);
					file_put_contents('data/hoster.json',json_encode($hosterList)); 
					updateHosted($_GET['n'], false);
					$message = "Hoster succesfully removed";  
				}else{
					$error = "Hoster not found";	
				}			
			}else{
				$error = "Invalid Hoster";
			}
	   // Add Hoster Command
		}elseif($_GET['c'] == "add"){
			if(preg_match("/^[0-9a-zA-Z-,\. ]{3,40}$/", $_GET['n'])) {
				if(!in_array($_GET['n'], $hosterList)){
					$hosterList[] = $_GET['n'];
					file_put_contents('data/hoster.json',json_encode($hosterList));
					updateHosted($_GET['n'], true);
					$message = "Hoster succesfully added"; 
				}else{
					$error = "Hoster already in list";
				}
			}else{
				$error = "Invalid Hoster";
			}
		}
	}
	
	$content = json_decode(file_get_contents('data/hoster.json'), TRUE);
	// Sort Hoster ascending
	natcasesort($content);
	
	// Create page specfic variables
	$data = array('section' => 'hoster', 'title' => 'Hoster Manager', 'content' => $content);
	
// Ban List Page	
}elseif($_GET['p'] == "banlist") {
	
	// Check if commands needs to be run   
	if(isset($_GET['c']) AND $_GET['t'] == $_SESSION["csfrToken"]){	  
		if($_GET['c'] == "unban"){
			if(preg_match("/^([0-9a-z:\.]{7,39}\/[0-9]{1,3})$/", $_GET['ip'], $match)) {
				$ip = $match[1];
				try {
					$result = $bitcoind->setban($ip, "remove");
					$message = "Node successfully unbanned";
				} catch (\Exception $e) {
					$error = "Node could not be unbanned";
				}
			}else{
				$error = "Invalid Node";
			}
		}elseif($_GET['c'] == "clearlist"){
			try {
				$result = $bitcoind->clearbanned();
				$message = "Banlist cleared";
			} catch (\Exception $e) {
				$error = "Could not clear banlist";
			}
		}elseif($_GET['c'] == "importbanlist"){
			try {
				$imNa = $_FILES['banlist']['tmp_name'];
				$banlist = array_map('str_getcsv', file($imNa));
				unlink($imNa);
				$i = 0;
				foreach($banlist as $ban){
					$timestamp = strtotime($ban[2]);
					if(checkIpBanList($ban[0]) AND $timestamp !== FALSE){
						$result = $bitcoind->setban($ban[0], "add", $timestamp, true);
						$i++;
					}
				}
				if($i !== 0){
					$message = "Banlist imported";
				}else{
					$error = "No valid data in file";
				}
			} catch (\Exception $e) {
				$error = "IPs already banned or node offline";
			}
		}
	}
	
	// Create ban list info
	$content = createBanListContent();
	$data = array('section' => 'banlist', 'title' => 'Ban List', 'content' => $content);  

// Rules Page
}elseif($_GET['p'] == "rules") {
	
	$editID = NULL;
	// Check if commands needs to be run   
	if(isset($_GET['c'])  AND $_GET['t'] == $_SESSION["csfrToken"]){	  
		// Save new or edited rule	
		if($_GET['c'] == "save"){			
			$rule = new Rule();
			$response = $rule->save($_POST);
				if($response){
					$message = "Rule succesfully saved";
				}else{
					$error = "Invalid rule data";
				}
 		// Apply rules		  
		}elseif($_GET['c'] == "run"){
			try{
				Rule::run();
			}catch(\Exception $e){
				$error = "Error while running rules";
			}
			if(empty($e)){
				$message = "Rules succesfully run. See log file for details";
			}
 		// Edit rule		   
		}elseif($_GET['c'] == "edit"){
			if(ctype_digit($_GET['id'])){
				$editID = $_GET['id'];
			}else{
				$error = "Invalid rule ID";
			}
 		// Delete single rule or all		  
		}elseif($_GET['c'] == "delete"){		 
			if(isset($_GET['id']) AND ctype_digit($_GET['id'])){
				$reponse =  Rule::deleteByID($_GET['id']);
				if($reponse){
					$message = "Rule succesfully deleted";					
				}else{
					$error = "Could not delete rule";   
			   }
			}elseif(!isset($_GET['id'])){
			   $reponse =  Rule::deleteAll();
				if($reponse){
					$message = "Rules succesfully deleted";					
				}else{
					$error = "Could not delete rules";   
			   }
			}else{
			   $error = "Invalid rule ID";
			}
		// Reset rule counter
		}elseif($_GET['c'] == "resetc"){
			$reponse =  Rule::resetCounter();
			if($reponse){
					$message = "Counter succesfully reseted";					
			}else{
					$error = "Could not reseted counter";   
			}			
		// Delete logfile
		}elseif($_GET['c'] == "dellog"){
			$reponse =  Rule::deleteLogfile();
			if($reponse){
					$message = "Logfile succesfully deleted";					
			}else{
					$error = "Could not delete logfile";   
			}
		}
	}
	
	$content = createRulesContent($editID);
	
	$data = array('section' => 'rules', 'title' => 'Rules Manager', 'content' => $content); 
	 
// Memory Pool Page	
}elseif($_GET['p'] == "mempool") {
	
	if(isset($_GET['e']) AND ctype_digit($_GET['id'])){
		$end = $_GET['e'];
	}else{
		$end = Config::DISPLAY_TXS;
	}
	
	$content = createMempoolContent($end);
	$data = array('section' => 'mempool', 'title' => 'Memory Pool', 'content' => $content);  
 
 
// Wallet Page
}elseif($_GET['p'] == "wallet") {
	
	$content = createWalletContent();
	$data = array('section' => 'wallet', 'title' => 'Wallet Overview', 'content' => $content);  
 
// Blocks Page 
}elseif($_GET['p'] == "blocks") {
	$content= createBlocksContent();
	$data = array('section' => 'blocks', 'title' => 'Blocks', 'content' => $content);
  
// Forks Page 
}elseif($_GET['p'] == "forks") {
	$content= createForksContent();
	$data = array('section' => 'forks', 'title' => 'Forks', 'content' => $content);
  
// Settings Page	
}elseif($_GET['p'] == "settings") {
	$geoPeers = Config::PEERS_GEO;
	if(isset($_GET['c'])  AND $_GET['t'] == $_SESSION["csfrToken"]){
		if(isset($_GET['c']) AND $_GET['c'] == "geosave"){
			// Check if Geo Peer Tracing was changed
			if(isset($_POST['geopeers']) AND $_POST['geopeers'] == "on"){
				 $geoPeers = "true";
			}else{
				$geoPeers = "false";
			}

			// Write new settings in config.php
			if (file_exists('src/Config.php')){
				$conf = file_get_contents('src/Config.php');
				$conf = preg_replace("/PEERS_GEO = (true|false);/i", 'PEERS_GEO = '.$geoPeers.';', $conf);
				file_put_contents('src/Config.php', $conf);
				$message = "Setings succesfully saved";
			}else{
				$error = "Config file does not exists";
			}
		}
	}
   $data = array('section' => 'settings', 'title' => 'Settings', 'geoPeers' => $geoPeers);

	
// About Page	
}elseif($_GET['p'] == "about") {
	$data = array('section' => 'about', 'title' => 'About'); 
	
}else{
	header('Location: index.php');
	exit; 	
}


// Create HTML output
if(isset($error)){
	$data['error'] = $error;
}
if(isset($message)){
	$data['message'] = $message;
}

$tmpl = new Template($data);
echo $tmpl->render();

?>