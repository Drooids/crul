<?php

require_once(dirname(__FILE__) . '/assets/hp-ansi-color.php');
use PhpAnsiColor\Color;

/**
 * The Curl helper.
 */
class Crul {

	public $header = [];
	public $debug = false;
	
	public function __construct() {
		if(is_callable('curl_init')){
		   echo( Color::set('~Status: CURL is installed successfully', 'green') . "\n");
		}
		else
		{
		   echo( Color::set('~Status: CURL not found, should be installed first!~.', 'red') . "\n");
		   exit;
		}

		echo "Testing...\n\n";

	}

	public function setHeader($token) {
		$this->header[] = "Authorization: " . " Bearer " . $token;
	}

	public function getHeader() {
		return $this->header;
	}

	public function httpGet($url, $header) {

		$ch = curl_init();  

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($header != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		$output = curl_exec($ch);
		
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		echo( Color::set('[------------------------------------------------------------------] ', 'blue') . "\n");

		if($http_status == 200) {
			echo( Color::set('~URL: ', 'blue') . $url . "\n");
			echo( Color::set('~Status: Test passed.', 'green') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'green') . "\n");
		} else {
			echo( Color::set('~URL: ' . $url, 'blue') . "\n");
			echo( Color::set('~Status: Test failed(or prohibited).', 'red') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'red') . "\n");
		}

		echo( Color::set('[------------------------------------------------------------------] ' . "\n", 'blue') . "\n");

		$this->checkForErrors($output, $ch);
		$this->hookToSlack($http_status, $url);

		if($this->debug) {
			var_dump($output);
		}

		curl_close($ch);
		return $output;

	}

	public function httpPost($url, $params, $header = null) {

		$postData = '';

		foreach($params as $k => $v) { 
			$postData .= $k . '='. $v . '&'; 
		}
		rtrim($postData, '&');

		$ch = curl_init();  

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		
		if($header != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		curl_setopt($ch, CURLOPT_POST, count($postData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		$output = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		echo( Color::set('[------------------------------------------------------------------] ', 'blue') . "\n");

		if($http_status == 200) {
			echo( Color::set('~URL: ', 'blue') . $url . "\n");
			echo( Color::set('~Status: Test passed.', 'green') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'green') . "\n");
		} else {
			echo( Color::set('~URL: ' . $url, 'blue') . "\n");
			echo( Color::set('~Status: Test failed(or prohibited).', 'red') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'red') . "\n");
		}

		echo( Color::set('[------------------------------------------------------------------] ' . "\n", 'blue') . "\n");

		$this->checkForErrors($output, $ch);
		$this->hookToSlack($http_status, $url);

		if($this->debug) {
			var_dump($output);
		}

		curl_close($ch);
		return $output;

	}

	public function checkForErrors($output, $ch) {
		if($output === false) {
	        echo Color::set("Error Number:", "red") . curl_errno($ch) . "\n";
	        echo Color::set("Error String:", "red") . curl_error($ch);
	    }
	}

	public function hookToSlack($code, $test) {

		switch ($code) {

			case '200':
				$slackText = ':green_heart: The test: ' . $test . ' passed successfully.';
				break;

			case '500':
				$slackText = ':collision: The test: ' . $test . ' failed!.';
				break;

			case '404':
				$slackText = ':alien: The test: ' . $test . ' item or route not found! Werid, either the `data` or `route` not found, did we move anything ?';
				break;

			case '401':
				$slackText = ':fire: No permission alowed: ' . $test;
				break;

			case 'custom_text':
				$slackText = $test;
			break;

			default: 
				$slackText = ":smiling_imp: The Test: " . $test . " something happened to it ?";
			break;

		}

		$slackUrl = 'https://hooks.slack.com/services/T024F-x-B7FF/B03U9T-x-1D8/AoTGZe8eXDURKu-x-7UbZABc4GU';
		$slackData = 'payload={"channel": "#logs", "username": "webhookbot", "text": "' . $slackText . '", "icon_emoji": ":dolphin:"}';

		$slackOptions = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => $slackData,
				'timeout' => 60,
			),
		);

		if(!$this->debug) {
			$slackContext  = stream_context_create($slackOptions);
			file_get_contents($slackUrl, false, $slackContext);
		}

	}

}
