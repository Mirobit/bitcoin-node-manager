<?php

/*
EasyBitcoin-PHP
A simple class for making calls to Bitcoin's API using PHP.
https://github.com/aceat64/EasyBitcoin-PHP
Copyright (c) 2013 Andrew LeCody
*/

namespace App;

class jsonRPCClient
{
    // Configuration options
    private $username;
    private $password;
    private $proto;
    private $host;
    private $port;
    private $url;
    private $CACertificate;

    // Information and debugging
    public $status;
    public $error;
    public $raw_response;
    public $response;

    private $id = 0;

    /**
     * @param string $username
     * @param string $password
     * @param string $host
     * @param int $port
     */
    public function __construct($username, $password, $host = 'localhost', $port = 8332)
    {
        $this->username      = $username;
        $this->password      = $password;
        $this->host          = $host;
        $this->port          = $port;

        // Set some defaults
        $this->proto         = 'http';
        $this->CACertificate = null;
    }

    /**
     * @param string|null $certificate
     */
    public function setSSL($certificate = null)
    {
        $this->proto         = 'https'; // force HTTPS
        $this->CACertificate = $certificate;
    }

    public function __call($method, $params)
    {
        $this->status       = null;
        $this->error        = null;
        $this->raw_response = null;
        $this->response     = null;

        // If no parameters are passed, this will be an empty array
        $params = array_values($params);

        // The ID should be unique for each call
        $this->id++;

        // Build the request, it's ok that params might have any empty array
        $request = json_encode(array(
            'method' => $method,
            'params' => $params,
            'id'     => $this->id
        ));

        // Build the cURL session
        $curl    = curl_init("{$this->proto}://{$this->host}:{$this->port}");
        $options = array(
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $this->username . ':' . $this->password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $request,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT        => 10
        );

        if(defined('App\Config::PROXY') && !empty(Config::PROXY) && defined('App\Config::PROXY_TYPE') && !empty(Config::PROXY_TYPE)) {
          $options[CURLOPT_PROXYTYPE] = Config::PROXY_TYPE;
          $options[CURLOPT_PROXY] = Config::PROXY;
        }

        // This prevents users from getting the following warning when open_basedir is set:
        // Warning: curl_setopt() [function.curl-setopt]:
        //   CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set
        if (ini_get('open_basedir')) {
            unset($options[CURLOPT_FOLLOWLOCATION]);
        }

        if ($this->proto == 'https') {
            // If the CA Certificate was specified we change CURL to look for it
            if (!empty($this->CACertificate)) {
                $options[CURLOPT_CAINFO] = $this->CACertificate;
                $options[CURLOPT_CAPATH] = DIRNAME($this->CACertificate);
            } else {
                // If not we need to assume the SSL cannot be verified
                // so we set this flag to FALSE to allow the connection
                $options[CURLOPT_SSL_VERIFYPEER] = false;
            }
        }

        curl_setopt_array($curl, $options);

        // Execute the request and decode to an array
        $this->raw_response = curl_exec($curl);
        $this->response = json_decode($this->raw_response, true);

        // If the status is not 200, something is wrong
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // If there was no error, this will be an empty string
        $curl_error = curl_error($curl);

        curl_close($curl);
        if(false){
          echo $this->raw_response;
          echo $this->status;
          echo $curl_error;
        }

        if($this->status === 500 && $this->response['error']) {
          $this->error = $this->response['error']['message'];
        }elseif ($this->status !== 200) {
            // If bitcoind didn't return a nice error message, we need to make our own
            switch ($this->status) {
                case 401:
                    $this->error = 'Invalid RPC credentials';
                    break;
                case 0:
                    $this->error = 'Bitcoin Core not reachable: '.$curl_error;
                    break;
                case 404:
                    $this->error = 'The RPC call does not exist: '.$method;
                    break;
                default:
                  $this->error = 'Unkown Error '.$this->status.': '.$curl_error;
            }
        }

        if ($this->error) {
          throw new \Exception($this->error);
        }

        return $this->response['result'];
    }
}
?>