<?php

class Keyboard {

	public $one_time = false;
	public $inline = false;

	public $buttons = [];
	public $currentIndex = 0;

	/**
	 * Constructor
	 * @param bool $one_time Should VK hide the keyboard after first use
	 * @param bool $inline Should keyboard be inline
	 * @since v0.3
	 */
	public function __construct(bool $one_time, bool $inline) {
		$this->one_time = $one_time;
		$this->inline = $inline;
	}

	/**
	 * Button constructor
	 * @param array $action https://vk.com/dev/bots_docs_3
	 * @param string $color Button color
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
	}

	/**
	 * Line constructor
	 * @since v0.3
	 */
	public function addLine() {
		$this->currentIndex = $this->currentIndex + 1;
	}

	/**
	 * Keyboard getter
	 * @param bool $json Should function return array or json
	 * @return array | string
	 * @since v0.3
	 */
	public function getKeyboard(bool $json = false) {
		$kb = [
			'one_time' => $this->one_time,
			'buttons' => $this->buttons,
			'inline' => $this->inline
		];

		return $json ? json_encode($kb, JSON_UNESCAPED_UNICODE) : $kb;
	}
}

?>