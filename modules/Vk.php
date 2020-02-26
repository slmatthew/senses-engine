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
	 * @param string $client_type Type of data handling: "cb" (if you use Callback API) or "lp" (if you use Longpoll API)
	 * @param bool $needLowerCase Commands will be handled in lower case
	 */
	public function __construct(string $client_type, bool $needLowerCase = true) {
		if(!in_array($client_type, ['lp', 'cb'])) throw new TypeException();

		$this->init();

		$this->client_type = $client_type;
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