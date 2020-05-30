<?php

namespace slmatthew\senses;

class InvalidAuthDataException extends \Exception {}
class LostConnectionException extends \Exception {}

class UnableToAuthException extends \Exception {
	private array $result = [];

	public function __construct(array $result) {
		Exception::__construct('Unable to auth');
		$this->result = $result;
	}

	public function getResult() {
		return $this->result;
	}
}

?>