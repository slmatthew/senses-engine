<?php

/**
 * Create, edit and get keyboard for VK
 * @author slmatthew
 * @package keyboard
 */

class Keyboard {

	/**
	 * Should VK hide the keyboard after first use
	 * @var bool
	 */
	public $one_time = false;

	/**
	 * Should keyboard be inline
	 * @var bool
	 */
	public $inline = false;

	/**
	 * @ignore
	 */
	public $buttons = [];

	/**
	 * @ignore
	 */
	public $currentIndex = 0;

	public const PRIMARY_BUTTON = 'primary';
	public const SECONDARY_BUTTON = 'secondary';
	public const NEGATIVE_BUTTON = 'negative';
	public const POSITIVE_BUTTON = 'positive';

	/**
	 * Constructor
	 * @param bool $one_time Should VK hide the keyboard after first use
	 * @param bool $inline Should keyboard be inline
	 * @return Keyboard
	 * @since v0.3
	 */
	public function __construct(bool $one_time = false, bool $inline = false) {
		$this->one_time = $one_time;
		$this->inline = $inline;

		return $this;
	}

	/**
	 * Should VK hide the keyboard after first use
	 * @param bool $enabled True or false
	 * @return Keyboard
	 * @since 0.6
	 */
	public function oneTime(bool $enabled) {
		$this->one_time = $enabled;

		return $this;
	}

	/**
	 * Should keyboard be inline
	 * @param bool $enabled True or false
	 * @return Keyboard
	 * @since 0.6
	 */
	public function inline(bool $enabled) {
		$this->inline = $enabled;

		return $this;
	}

	/**
	 * Button constructor
	 * @param array $action https://vk.com/dev/bots_docs_3
	 * @param string $color Button color
	 * @return Keyboard
	 * @since v0.3
	 */
	public function addButton(array $action, string $color = '') {
		if(isset($action['payload'])) $action['payload'] = json_encode($action['payload'], JSON_UNESCAPED_UNICODE);

		if(isset($action['type']) && $action['type'] == 'text') {
			$this->buttons[$this->currentIndex][] = [
				'action' => $action,
				'color' => $color
			];
		} else {
			$this->buttons[$this->currentIndex][] = [
				'action' => $action
			];
		}

		return $this;
	}

	/**
	 * Text button constructor
	 * @param string $label Text on the button
	 * @param array $payload Payload
	 * @param string $color Button color
	 * @return Keyboard
	 * @since 0.6
	 */
	public function addTextButton(string $label, array $payload = [], string $color = self::PRIMARY_BUTTON) {
		$this->addButton([
			'type' => 'text',
			'label' => $label,
			'payload' => $this->toJson($payload)
		], $color);

		return $this;
	}

	/**
	 * Location button constructor
	 * @param array $payload Payload
	 * @return Keyboard
	 * @since 0.6
	 */
	public function addLocationButton(array $payload = []) {
		$this->addButton([
			'type' => 'location',
			'payload' => $this->toJson($payload)
		]);

		return $this;
	}

	/**
	 * VK Pay button constructor
	 * @param array $hash Hash
	 * @param array $payload Payload
	 * @return Keyboard
	 * @since 0.6
	 */
	public function addPayButton(array $hash, array $payload = []) {
		$this->addButton([
			'type' => 'vkpay',
			'hash' => http_build_query($hash),
			'payload' => $this->toJson($payload)
		]);

		return $this;
	}

	/**
	 * Text button constructor
	 * @param int $app_id Your application ID
	 * @param int $owner_id Community or user id (for user ids owner_id < 0)
	 * @param string $label Text on the button
	 * @param string $hash Hash: https://vk.com/app123#{hash}
	 * @param array $payload
	 * @return Keyboard
	 * @since 0.6
	 */
	public function addAppButton(int $app_id, int $owner_id, string $label, string $hash = '', array $payload = []) {
		$this->addButton([
			'type' => 'open_app',
			'app_id' => $app_id,
			'owner_id' => $owner_id,
			'label' => $label,
			'hash' => $hash,
			'payload' => $this->toJson($payload)
		]);

		return $this;
	}

	/**
	 * Line constructor
	 * @return Keyboard
	 * @since v0.3
	 */
	public function addLine() {
		$this->currentIndex = $this->currentIndex + 1;

		return $this;
	}

	/**
	 * Keyboard getter
	 * @param bool $json Should function return array or json
	 * @return array|string
	 * @since v0.3
	 */
	public function get(bool $json = false) {
		$kb = [
			'one_time' => $this->one_time,
			'buttons' => $this->buttons,
			'inline' => $this->inline
		];

		return $json ? $this->toJson($kb) : $kb;
	}

	/**
	 * @ignore
	 */
	private function toJson(array $array) { return json_encode($array, JSON_UNESCAPED_UNICODE); }
}

?>