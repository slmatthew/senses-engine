<?php

/**
 * Class for working with message
 * @author slmatthew
 * @package message
 */

class Message {
	/**
	 * @ignore
	 */
	private $message = null;

	/**
	 * @param array $message Message object (from cb or bots lp)
	 */
	public function __construct(array $message) {
		$this->message = $message;
	}

	/**
	 * Is current message from chat?
	 * @return bool
	 */
	public function isChat() { return $this->message['object']['message']['peer_id'] > 2000000000; }

	/**
	 * Has current message attachments?
	 * @return bool
	 */
	public function hasAttachs() { return !empty($this->message['object']['message']['attachments']); }

	/**
	 * Reply to current message
	 * @param string $text Reply text
	 * @param array $params Additional parameters
	 * @param bool $for_execute Return array (for Execute class) or `call` function response
	 * @return array
	 */
	public function reply(string $text, array $params = [], bool $for_execute = false) {
		global $config;

		$params['peer_id'] = $this->message['object']['message']['peer_id'];
		$params['random_id'] = 0;
		$params['message'] = $text;

		return $for_execute ? ['messages.send', $params] : call('messages.send', $params, $config['type'] === 'user');
	}
}

/**
 * Class for working with message from user longpoll
 * @author slmatthew
 * @package message
 */

class MessageUser {
	/**
	 * @ignore
	 */
	private $message = null;

	/**
	 * @param array $message Message from user lp
	 */
	public function __construct(array $message) {
		$message = new LpDecoder($message);
		$this->message = $message->decode();
	}

	/**
	 * Is current message from chat?
	 * @return bool
	 */
	public function isChat() { return $this->message['peer_id'] > 2000000000; }

	/**
	 * TODO
	 * @ignore
	 */
	public function hasAttachs() { return false; }

	/**
	 * Reply to current message
	 * @param string $text Reply text
	 * @param array $params Additional parameters
	 * @param bool $for_execute Return array (for Execute class) or `call` function response
	 * @return array
	 */
	public function reply(string $text, array $params = [], bool $for_execute = false) {
		global $config;

		$params['peer_id'] = $this->message['peer_id'];
		$params['random_id'] = 0;
		$params['message'] = $text;

		return $for_execute ? ['messages.send', $params] : call('messages.send', $params, $config['type'] === 'user');
	}
}

?>