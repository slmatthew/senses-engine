<?php

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

		$this->auth_data = [
			'auth_type' => $auth_data['auth_type'],
			'token' => $auth_data['token'],
			'secret' => $auth_data['secret'],
			'v' => $auth_data['v']
		];

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
	 * @return DataHandler
	 */
	public function listen() {
		$this->client = new DataHandler($this->client_type, $this->bot, $this->confirm_string);
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