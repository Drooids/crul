<?php 

require_once(dirname(__FILE__) . '/assets/hp-ansi-color.php');

use PhpAnsiColor\Color;

/**
 * The Curl helper.
 */
class CurlHelper {

	public $header = [];
	public $cookie;
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

		$this->cookie = dirname(__FILE__) . "/cookie.txt";

		echo "Testing...\n\n";

	}

	public function setHeader($token) {
		$this->header[] = "Authorization: " . " Bearer " . $token;
	}

	public function getHeader() {
		return $this->header;
	}

	public function httpGet($url, $custom_name, $header, $auth_obj = null) {

		$debug = "";

		$ch = curl_init();
	
		if($auth_obj != null) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_URL, $auth_obj->auth_url);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($auth_obj->login_data));
	        curl_exec($ch);
    	}

        curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($header != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		$output = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($this->debug) {
			$debug = $output;
		}

		$this->nicePrint($url, $custom_name, $http_status, $debug);
		$this->checkForErrors($output, $ch);

		$this->hookToSlack($http_status, $url);

		curl_close($ch);
		return $output;

	}

	public function httpPost($url, $custom_name, $params, $header = null, $auth_obj = null) {

		$debug = "";
		$postData = $params;

		$ch = curl_init();

		if($auth_obj != null) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_URL, $auth_obj->auth_url);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($auth_obj->login_data));
	        curl_exec($ch);
    	}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);

		if($header != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		curl_setopt($ch, CURLOPT_POST, count($postData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

		$output = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($this->debug) {
			$debug = $output;
		}

		$this->nicePrint($url, $custom_name, $http_status, $debug);
		$this->checkForErrors($output, $ch);

		$this->hookToSlack($http_status, $url);

		curl_close($ch);
		return $output;

	}

	public function httpPut($url, $custom_name, $params, $header = null, $auth_obj = null) {

		$debug = "";

		$ch = curl_init();

		if($auth_obj != null) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_URL, $auth_obj->auth_url);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($auth_obj->login_data));
	        curl_exec($ch);
    	}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_HEADER, false);

		if($header != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		$output = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($this->debug) {
			$debug = $output;
		}

		$this->nicePrint($url, $custom_name, $http_status, $debug);
		$this->checkForErrors($output, $ch);

		$this->hookToSlack($http_status, $url);

		curl_close($ch);
		return $output;

	}

	public function nicePrint($url, $custom_name, $http_status, $output = null) {
		echo( Color::set('[------------------------------------------------------------------] ', 'blue') . "\n");

		echo( Color::set('~URL: ', 'blue') . $url . "\n");
		echo( Color::set('~NAME: ', 'blue') . $custom_name . "\n");

		if($http_status == 200) {
			echo( Color::set('~Status: Test passed.', 'green') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'green') . "\n");
		} else if($http_status == 400) {
			echo( Color::set('~Status: Test -  Bad Request.', 'yellow') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'yellow') . "\n");
		} else if($http_status == 404) {
			echo( Color::set('~Status: Test - Not found.', 'yellow') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'yellow') . "\n");
		} else if($http_status == 405) {
			echo( Color::set('~Status: Test - Method Not Allowed.', 'yellow') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'yellow') . "\n");
		} else if($http_status == 500) {
			echo( Color::set('~Status: Test - Failed', 'red') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'red') . "\n");
		} else {
			echo( Color::set('~Status: Test - Unknown', 'magenta') . "\n");
			echo( Color::set('~Status Code: ' . $http_status, 'magenta') . "\n");
		}

		if($output != null && strlen($output) > 0) {
			echo( Color::set("~Output: \n" . $output, 'white') . "\n");
		}

		echo( Color::set('[------------------------------------------------------------------] ' . "\n", 'blue') . "\n");
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
				$slackText = ':underage: No permission alowed: ' . $test;
				break;

			case '400':
				$slackText = ':underage: No permission alowed: ' . $test;
				break;

			case 'custom_text':
				$slackText = $test;
			break;

			default:
				$slackText = ":smiling_imp: The Test: " . $test . " something happened to it ?";
			break;

		}

		$slackUrl = 'https://hooks.slack.com/services/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';
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
