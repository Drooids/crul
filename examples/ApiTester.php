<?php 

error_reporting(E_ALL);
require_once(dirname(__FILE__) . '../Crul.php');

/**
 * The api route tester.
 */
class ApiTester {

	public $debug = false;

	public $access_token;
	public $token_type;
	public $expires;
	public $expires_in;
	public $refresh_token;

	public $header_auth;

	public $curl;

	public $env = ['http://localhost:8000', 'http://dev.example.com', 'http://test.example.com', 'http://app.example.com'];

	public $current_env = "";

	public function __construct() {
		
		$this->curl = new Crul;
		$this->curl->debug = $this->debug;

		// Point to the right test domain.
		$this->current_env = $this->env[2];

		if(!$this->debug) {
			$this->curl->hookToSlack('custom_text', 
				"```" 
				. 'grant_type: password; '
				. 'client_id: 45xgmbg743; '
				. 'client_secret: S7Ixy91sqexn5BPXgS; '
				. 'username: demo@gmail.com; '
				. 'password: 123123123; '
				. 'state: 123123 '
				. '```'
			);
		}

		$getOauth = $this->curl->httpPost( $this->current_env . '/oauth/access_token', [
			'grant_type' => 'password', 
			'client_id' => '45xgmbg743', 
			'client_secret' => 'S7Ixy91sqexn5BPXgS',
			'username' => 'demo@gmail.com',
			'password' => '123123123',
			'state' => '123123'
		]);

		$get_json = json_decode($getOauth);
		
		$this->access_token = $get_json->access_token;
		$this->token_type = $get_json->access_token;
		$this->expires = $get_json->expires;
		$this->expires_in = $get_json->expires_in;
		$this->refresh_token = $get_json->refresh_token;

		$this->curl->setHeader($this->access_token);
		$this->header_auth = $this->curl->getHeader();

	}

	public function init() {

		// ----------------
		// Workitem Routes
		// ----------------

		// Store
		$this->curl->httpPost($this->current_env . '/OA/api/v1/cake/store', 

			[
				"user_id" => "2357", 
				"activity_id" => "2",
				"Bike_id" => "9552",
				"airline" => "Air Canada",
				"airline_id" => 330,
				"approval" => 1111,
				"ata_code_id" => 27,
				"date_performed" => "2015-04-29",
				"details" => "Yeah details 2..." . rand(0, 200),
				"duration_hours" => "0.1",
				"maintenance_id" => 1,
				"rating_id" => 3,
				"reference" => 222,
				"supervisor" => 2
			], 

		$this->header_auth);

		// Update
		$this->curl->httpPost($this->current_env . '/OA/api/v1/cake/update/1048122', 

			[
				"user_id" => "2357", 
				"activity_id" => "2",
				"Bike_id" => "9552",
				"airline" => "Air Canada",
				"airline_id" => 330,
				"approval" => 1111,
				"ata_code_id" => 27,
				"date_performed" => "2015-04-29",
				"details" => "Yeah details 2..." . rand(0, 200),
				"duration_hours" => "0.1",
				"maintenance_id" => 1,
				"rating_id" => 3,
				"reference" => 222,
				"supervisor" => 2
			], 

		$this->header_auth);

		// Get
		$this->curl->httpGet($this->current_env . '/OA/api/v1/cake/getEagerPerUser/2357?page=1&perPage=10', $this->header_auth);

		// Destroy
		$this->curl->httpGet($this->current_env . '/OA/api/v1/cake/destroy/2359', $this->header_auth);

		// Rating
		$this->curl->httpGet($this->current_env . '/OA/api/v1/cake/ratings/2357', $this->header_auth);

		// Categories
		$this->curl->httpGet($this->current_env . '/OA/api/v1/cake/categories', $this->header_auth);

		// Activities
		$this->curl->httpGet($this->current_env . '/OA/api/v1/cake/activities', $this->header_auth);


		// -----------------
		// Bikes Routes
		// -----------------

		// Get by user
		$this->curl->httpGet($this->current_env . '/OA/api/v1/bike/getPerUser/2357', $this->header_auth);

		// Get by ID
		$this->curl->httpGet($this->current_env . '/OA/api/v1/bike/getByID/2357', $this->header_auth);

		// Store
		$this->curl->httpPost($this->current_env . '/OA/api/v1/bike/store', 

			[
				"user_id" => "2357",
				"engine" =>  "PWC PW119",
				"manufacturer" =>  "328 Support Services",
				"model" =>  "328-100 series",
				"registration" =>  "NEW",
				"structure" =>  "None"
			],

		$this->header_auth);

		// Search bike Manufacturer
		$this->curl->httpPost($this->current_env . '/OA/api/v1/bike/searchBikeManufacturer', 

			[
				"engine" => "",
				"manufacturer" =>  "",
				"model" =>  "",
				"structure" =>  ""
			],

		$this->header_auth);

		// Search Bike Model
		$this->curl->httpPost($this->current_env . '/OA/api/v1/bike/searchBikeModel', 

			[
				"engine" => "",
				"manufacturer" =>  "328 Support Services",
				"model" =>  "",
				"structure" =>  ""
			],

		$this->header_auth);

		// Search Bike Engine
		$this->curl->httpPost($this->current_env . '/OA/api/v1/bike/searchBikeEngine', 

			[
				"engine" => "",
				"manufacturer" =>  "328 Support Services",
				"model" =>  "",
				"structure" =>  ""
			],

		$this->header_auth);

		// Search Bike Structure
		$this->curl->httpPost($this->current_env . '/OA/api/v1/bike/searchBikeStructure', 

			[
				"engine" => "PWC PW119",
				"manufacturer" =>  "328 Support Services",
				"model" =>  "",
				"structure" =>  ""
			],

		$this->header_auth);

		// Search
		$this->curl->httpGet($this->current_env . '/OA/api/v1/bike/searchBike?user_id=2357&term=a', $this->header_auth);

		// Destroy
		$this->curl->httpPost($this->current_env . '/OA/api/v1/bike/destroy', ["id" => 123, "user_id" => 2357], $this->header_auth);

		// ----------------
		// User Routes
		// ----------------

		// Update
		$this->curl->httpPost($this->current_env . '/OA/api/v1/user/update/2357', ["first_name" => "A", "last_name" => "C"], $this->header_auth);

		// Get By ID
		$this->curl->httpGet($this->current_env . '/OA/api/v1/user/getByID/2357', $this->header_auth);

 		// Get By Email
		$this->curl->httpGet($this->current_env . '/OA/api/v1/user/getCurrentUserByEmail?email=demo@gmail.com', $this->header_auth);

	}
	
}

$tester = new ApiTester;
$tester->init();