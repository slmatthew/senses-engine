<?php

class Message {
	private $message = null;

	public function __construct(array $message) {
		$this->message = $message;
	}

	public function isChat() { return $this->message['object']['message']['peer_id'] > 2000000000; }
	public function hasAttachs() { return !empty($this->message['object']['message']['attachments']); }

	public function reply(string $text, array $params = [], bool $for_execute = false) {
		global $config;

		$params['peer_id'] = $this->message['object']['message']['peer_id'];
		$params['random_id'] = 0;
		$params['message'] = $text;

		return $for_execute ? ['messages.send', $params] : call('messages.send', $params, $config['type'] == 'user');
	}
}

class MessageUser {
	private $message = null;

	public function __construct(array $message) {
		$message = new LpDecoder($message);
		$this->message = $message->decode();
	}

	public function isChat() { return $this->message['peer_id'] > 2000000000; }
	public function hasAttachs() { return false; }

	public function reply(string $text, array $params = [], bool $for_execute = false) {
		global $config;

		$params['peer_id'] = $this->message['peer_id'];
		$params['random_id'] = 0;
		$params['message'] = $text;

		return $for_execute ? ['messages.send', $params] : call('messages.send', $params, $config['type'] == 'user');
	}
}

?>