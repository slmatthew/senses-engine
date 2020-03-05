<?php

/**
 * Storage for authorization data
 * @author slmatthew
 */
class vkAuthStorage {
	/**
	 * @var array Aviable users
	 */
	private static $auth_data = [];

	/**
	 * @var int Active user id
	 */
	private static $active = 0;

	/**
	 * @var int peer_id for VK API errors
	 */
	private static $api_errors = 0;

	/**
	 * Add new auth data
	 * @param array $data
	 * - @var string auth_type
	 * - @var int api_id
	 * - @var string api_type
	 * - @var string token
	 * - @var string secret
	 * - @var string v
	 * @return void
	 */
	public static function set(array $data) {
		if(isset($data['api_id'])) {
			vkAuthStorage::$auth_data["{$data['api_id']}"] = $data;
			vkAuthStorage::$active = $data['api_id'];
		} else {
			vkAuthStorage::$auth_data['0'] = $data;
			vkAuthStorage::$active = 0;
		}
	}

	/**
	 * Set active user
	 * @param int $active User api_id
	 * @return bool
	 */
	public static function setActive(int $active) {
		if(isset(vkAuthStorage::$auth_data[$active])) {
			vkAuthStorage::$active = $active;
			return true;
		}

		return false;
	}

	/**
	 * Set peer_id for VK API errors
	 * @param int $peer_id
	 * @return void
	 */
	public static function setErrorsPeer(int $peer_id) {
		vkAuthStorage::$api_errors = $peer_id;
	}

	/**
	 * Get active user
	 * @return array
	 */
	public static function get() {
		return isset(vkAuthStorage::$auth_data[vkAuthStorage::$active]) ? vkAuthStorage::$auth_data[vkAuthStorage::$active] : [];
	}

	/**
	 * Get active user api_id
	 * @return int
	 */
	public static function getActive() {
		return vkAuthStorage::$active;
	}

	/**
	 * Get aviable user api_ids
	 * @return array
	 */
	public static function getAviableIds() {
		return array_column(vkAuthStorage::$auth_data, 'api_id');
	}

	/**
	 * Get current peer_id for VK API errors
	 * @return int
	 */
	public static function getErrorsPeer() {
		return vkAuthStorage::$api_errors;
	}

	/**
	 * @ignore
	 */
	public static function removeTemp() {
		unset(vkAuthStorage::$auth_data[0]);
	}
}

/**
 * API Wrapper
 * @author slmatthew
 */
class vkApiWrapper {
	/**
	 * @param string $name Method name. _ will be converted to . Example: users_get > users.get
	 * @param array $params Function parameters. $params[0] - method params, $params[1] (bool) - $official
	 * @return array
	 */
	public function __call(string $name, array $params) {
		$method_name = implode('.', explode('_', $name));
		$method_params = isset($params[0]) ? $params[0] : [];
		$official = isset($params[1]) && gettype($params[1]) === 'bool' && $params[1];

		return call($method_name, $method_params, $official);
	}
}

class vk {
	/**
	 * @var BotEngine
	 */
	public $bot = null;

	/**
	 * @var DataHandler
	 */
	public $client = null;

	/**
	 * @var VkAudio
	 */
	public $audio = null;

	/**
	 * @var vkApiWrapper
	 */
	public $api = null;

	/**
	 * @var string Type of data handling: "cb" (if you use Callback API) or "lp" (if you use Longpoll API)
	 */
	public $client_type = 'lp';

	/**
	 * @var string Confirmation string
	 */
	private $confirm_string = '';

	/**
	 * @var array
	 */
	private $auth_data = [];

	/**
	 * @param array $auth_data Authorization data
	 * - @var string token
	 * - @var string username
	 * - @var string password
	 * - @var string type
	 * - @var string v
	 * - @var string secret
	 * @param bool $needLowerCase Commands will be handled in lower case
	 */
	public function __construct(array $auth_data, bool $needLowerCase = true) {
		if(isset($auth_data['token'])) {
			$auth_data['auth_type'] = 'token';
		} elseif(isset($auth_data['username']) && isset($auth_data['password'])) {
			$auth_data['auth_type'] = 'bypass';
			$auth_data['trusted_hash'] = isset($auth_data['trusted_hash']) ? $auth_data['trusted_hash'] : '';

			$auth = new AuthPassword();
			$auth_result = $auth->auth('android', $auth_data['username'], $auth_data['password'], $auth_data['trusted_hash']);
			if($auth_result['success']) {
				$result_data = json_decode($auth_result['data']['data'], true);
				if(!isset($result_data['error'])) {
					$auth_data['token'] = $result_data['access_token'];
				} else throw new VkAuthException($auth_result['data']['data'], 2);
			} else throw new VkAuthException($auth_result['data']['data'], 1);
		} else throw new ParameterException('unknown auth type', 1);

		if(isset($auth_data['type']) && in_array($auth_data['type'], ['lp', 'cb'])) {
			$this->client_type = $auth_data['type'];
		} else throw new TypeException();

		if(isset($auth_data['v'])) {
			if((int)$auth_data['v'] < 5) throw new ParameterException('Library does not support VK API version < 5', 2);
		} else {
			$auth_data['v'] = '5.118';
		}
		if(!isset($auth_data['secret'])) $auth_data['secret'] = '';

		$this->api = new vkApiWrapper();

		$auth_data_ready = [
			'auth_type' => $auth_data['auth_type'],
			'token' => $auth_data['token'],
			'secret' => $auth_data['secret'],
			'v' => $auth_data['v']
		];
		vkAuthStorage::set($auth_data_ready);

		if($auth_data_ready['auth_type'] === 'bypass') {
			$auth_data_ready['api_id'] = call('users.get')['response'][0]['id'];
			$auth_data_ready['api_type'] = 'user';
		} else {
			$api_result = call('users.get');
			if(isset($api_result['response']) && !empty($api_result['response'])) {
				$auth_data_ready['api_id'] = $api_result['response'][0]['id'];
				$auth_data_ready['api_type'] = 'user';
			} else {
				$auth_data_ready['api_id'] = call('groups.getById')['response'][0]['id'];
				$auth_data_ready['api_type'] = 'community';
			}
		}

		$this->auth_data = $auth_data_ready;
		vkAuthStorage::removeTemp(); // not ready data from storage
		vkAuthStorage::set($auth_data_ready);

		$this->init();
		$this->newBot($needLowerCase);
	}

	/**
	 * Create new BotEngine
	 * @param bool $needLowerCase Commands will be handled in lower case
	 * @return BotEngine
	 */
	public function newBot(bool $needLowerCase = true) {
		$this->bot = new BotEngine($needLowerCase);
		return $this->bot;
	}

	/**
	 * Create new DataHandler
	 * @param bool $cache ts cache
	 * @return DataHandler
	 */
	public function listen(bool $cache = true) {
		$this->client = new DataHandler($this->client_type, $this->bot, $this->confirm_string, $cache);
		return $this->client;
	}

	/**
	 * Set confirmation string for Callback API
	 * @param string $confirm_string Confirmation string
	 * @return void
	 */
	public function setConfirmation(string $confirm_string) {
		$this->confirm_string = $confirm_string;
	}

	/**
	 * Set class properties
	 * @ignore
	 */
	protected function init() {
		global $config;

		if(!empty($config) && isset($config['type']) && $config['type'] === 'user') $this->audio = new VkAudio();
	}
}

?>