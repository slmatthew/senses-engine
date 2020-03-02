<?php

/**
 * @ignore
 */
class RequestExtended {
	/**
	 * @ignore
	 */
	protected function request(string $url, array $postfields = [], string $agent = 'Senses Bot Engine/'.SEV) {
		$ch = curl_init($url);
		$headers = [];

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, CURL_VERIFY);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
			function($curl, $header) use(&$headers) {
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if(count($header) < 2) {
					return $len;
				}

				$headers[strtolower(trim($header[0]))][] = trim($header[1]);

				return $len;
			}
		);

		if(!empty($postfields)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		}

		$exec = curl_exec($ch);
		curl_close($ch);

		return [
			'data' => $exec,
			'headers' => $headers
		];
	}
}

/**
 * Auth via login and password
 * @author slmatthew
 * @package auth
 */
class AuthPassword extends RequestExtended {
	/**
	 * @ignore
	 */
	private const CLIENTS = [
		[2274003, 'hHbZxrka2uZ6jB1inYsH'], # 'Android'
		[3140623, 'VeWdmVclDCtn6ihuP1nt'], # 'iPhone'
		[3682744, 'mY6CDUswIVdJLCD3j15n'], # 'iPad'
		[3697615, 'AlVXZFMUqyrnABp8ncuU'], # 'Windows PC'
		[2685278, 'lxhD8OD7dMsqtXIm5IUY'], # 'Kate Mobile'
		[5027722, 'Skg1Tn1r2qEbbZIAJMx3'], # 'VK Messenger'
		[4580399, 'wYavpq94flrP3ERHO4qQ'], # 'Snapster (Android)'
		[2037484, 'gpfDXet2gdGTsvOs7MbL'], # 'Nokia (Symbian)'
		[3502557, 'PEObAuQi6KloPM4T30DV'], # 'Windows Phone'
		[3469984, 'kc8eckM3jrRj8mHWl9zQ'], # 'Lynt'
		[3032107, 'NOmHf1JNKONiIG5zPJUu'] # 'Vika (Blackberry)'
	];

	/**
	 * @ignore
	 */
	private const UA = "VKAndroidApp/5.50-4431 (1; 1; 1; 1; 1; 1)";

	/**
	 * @ignore
	 */
	private $oauth_host = 'oauth.vk.com';

	/**
	 * Start auth
	 * @param string $app App name
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $trusted_hash trusted_hash for auth
	 * @param array $params Parameters
	 * @throws AuthAppException
	 * @throws AuthRequestException
	 * @throws AuthBannedException
	 * @return array
	 */
	public function auth(string $app, string $username, string $password, string $trusted_hash = '', array $params = []) {
		$app = mb_strtolower($app);
		$apps = [
			'android' => 0,
			'iphone' => 1,
			'ipad' => 2,
			'windowspc' => 3,
			'kate' => 4,
			'vkmessenger' => 5,
			'snapster' => 6,
			'nokia' => 7,
			'windowsphone' => 8,
			'lynt' => 9,
			'vika' => 10
		];

		if(!isset($apps[$app])) throw new AuthAppException($app);

		$client = self::CLIENTS[$apps[$app]];
		$auth_params = $params + [
			'scope' => 'all',
			'client_id' => $client[0],
			'client_secret' => $client[1],
			'username' => $username,
			'password' => $password,
			'2fa_supported' => 1,
			'grant_type' => 'password',
			'lang' => 'ru',
			'v' => vkAuthStorage::get()['v'],
			'trusted_hash' => $trusted_hash
		];

		$res = $this->oauthToken($auth_params);
		$data = @json_decode($res['data'], true);
		if(is_null($data)) {
			throw new AuthRequestException();
		} elseif(isset($data['error']) && $data['error'] == 'need_captcha') {
			return [
				'success' => false,
				'desc' => 'need_captcha',
				'captcha_sid' => $data['captcha_sid'],
				'send' => function(string $code) use($app, $username, $password, $trusted_hash) {
					return $this->auth($app, $username, $password, $trusted_hash, [
						'captcha_sid' => $data['captcha_sid'],
						'captcha_key' => $code
					]);
				}
			];
		} elseif(isset($data['ban_info'])) {
			throw new AuthBannedException($res['data']);
		}

		return [
			'success' => true,
			'data' => $res,
			'needSendCode' => isset($data['error']),
			'sendCode' => function($code) use($app, $username, $password, $trusted_hash) {
				return $this->auth($app, $username, $password, $trusted_hash, [
					'code' => $code
				]);
			}
		];
	}

	/**
	 * auth.validatePhone
	 * @param string $app App name
	 * @param string $sid validation_sid
	 * @throws AuthAppException
	 * @return array
	 */
	public function validatePhone(string $app, string $sid) {
		$app = mb_strtolower($app);
		$apps = [
			'android' => 0,
			'iphone' => 1,
			'ipad' => 2,
			'windowspc' => 3,
			'kate' => 4,
			'vkmessenger' => 5,
			'snapster' => 6,
			'nokia' => 7,
			'windowsphone' => 8,
			'lynt' => 9,
			'vika' => 10
		];

		if(!isset($apps[$app])) throw new AuthAppException($app);

		$client = self::CLIENTS[$apps[$app]];

		return call('https://api.vk.com/method/auth.validatePhone', [
			'unsetToken' => true,
			'client_id' => $client[0],
			'client_secret' => $client[1],
			'api_id' => $client[0],
			'sid' => $sid
		]);
	}

	/**
	 * Change oauth host
	 * @param string $host oauth host
	 * @return void
	 */
	public function setHost(string $host) {
		$this->oauth_host = $host;
	}

	/**
	 * @ignore
	 */
	private function oauthToken(array $params) { return $this->request("https://{$this->oauth_host}/token?".http_build_query($params), [], self::UA); }
}

/**
 * Implict Flow Users
 * @author slmatthew
 * @package auth
 */
class AuthImplictUser extends RequestExtended {
	/**
	 * Default VK OAuth redirect uri
	 * @ignore
	 */
	private const DEFAULT_REDIRECT_URI = 'https://oauth.vk.com/blank.html';

	/**
	 * Auth parameters
	 * @ignore
	 */
	private $params = [];

	/**
	 * OAuth host
	 * @ignore
	 */
	private $oauth_host = 'oauth.vk.com';

	/**
	 * @param int $client_id Your API_ID
	 * @param string $redirect_uri Redirect URI. If length == 0, will be used DEFAULT_REDIRECT_URI
	 * @param int $scope Mask of permissions
	 * @param array $params Parameters
	 */
	public function __construct(int $client_id, string $redirect_uri, int $scope, array $params = []) {
		if(strlen($redirect_uri) == 0) $redirect_uri = self::DEFAULT_REDIRECT_URI;

		$this->params = [
			'client_id' => $client_id,
			'redirect_uri' => $redirect_uri,
			'scope' => $scope,
			'response_type' => 'token',
			'v' => vkAuthStorage::get()['v']
		] + $params; // [] + $params because by this you can't change common parameters
	}

	/**
	 * Get formatted link
	 * @throws AuthEmptyParamsException
	 * @return string
	 */
	public function link() {
		if(empty($this->params)) throw new AuthEmptyParamsException();

		return "https://{$this->oauth_host}/authorize?".http_build_query($this->params);
	}

	/**
	 * Change oauth host
	 * @param string $host oauth host
	 * @return void
	 */
	public function setHost(string $host) {
		$this->oauth_host = $host;
	}
}

/**
 * Implict Flow Groups
 * @author slmatthew
 * @package auth
 */
class AuthImplictGroup extends AuthImplictUser {
	/**
	 * @param int $client_id Your API_ID
	 * @param string $redirect_uri Redirect URI. If length == 0, will be used DEFAULT_REDIRECT_URI
	 * @param int $scope Mask of permissions
	 * @param array $group_ids ID of communities
	 * @param array $params Parameters
	 */
	public function __construct(int $client_id, string $redirect_uri, int $scope, array $group_ids, array $params = []) {
		$params['group_ids'] = implode(',', $group_ids);
		parent::__construct($client_id, $redirect_uri, $scope, $params);
	}
}

/**
 * Authorization Code Flow Users
 * @author slmatthew
 * @package auth
 */
class AuthFlowUser extends RequestExtended {
	/**
	 * @ignore
	 */
	private $params = [];

	/**
	 * @ignore
	 */
	private $oauth_host = 'oauth.vk.com';

	/**
	 * @param int $client_id client_id of your app
	 * @param string $client_secret client_secret of your app
	 * @param string $redirect_uri Redirect address
	 * @param int $scope Mask of permissions
	 * @param array $params Parameters
	 */
	public function __construct(int $client_id, string $client_secret, string $redirect_uri, int $scope, array $params = []) {
		$this->params = [
			'client_id' => $client_id,
			'redirect_uri' => $redirect_uri,
			'scope' => $scope,
			'response_type' => 'code',
			'v' => vkAuthStorage::get()['v']
		] + $params; // [] + $params because by this you can't change common parameters
	}

	/**
	 * Get formatted link
	 * @throws AuthEmptyParamsException
	 * @return string
	 */
	public function link() {
		if(empty($this->params)) throw new AuthEmptyParamsException();

		return "https://{$this->oauth_host}/authorize?".http_build_query($this->params);
	}

	/**
	 * Change oauth host
	 * @param string $host oauth host
	 * @return void
	 */
	public function setHost(string $host) {
		$this->oauth_host = $host;
	}
}

/**
 * Authorization Code Flow Groups
 * @author slmatthew
 * @package auth
 */
class AuthFlowGroup extends AuthFlowUser {
	/**
	 * @param int $client_id client_id of your app
	 * @param string $client_secret client_secret of your app
	 * @param string $redirect_uri Redirect address
	 * @param int $scope Mask of permissions
	 * @param array $group_ids ID of communities
	 * @param array $params Parameters
	 */
	public function __construct(int $client_id, string $client_secret, string $redirect_uri, int $scope, array $group_ids, array $params = []) {
		$params['group_ids'] = implode(',', $group_ids);
		parent::__construct($client_id, $client_secret, $redirect_uri, $scope, $params);
	}
}

?>