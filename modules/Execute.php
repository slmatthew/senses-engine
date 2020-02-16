<?php

class Execute {
	protected $code = [];

	public function __construct(array $methods) {
		$this->code = $methods;
	}

	public function getCode() {
		$code = [];
		foreach($this->code as $key => $val) {
			$code[] = "API.{$val[0]}(".json_encode($val[1], JSON_UNESCAPED_UNICODE).")";
		}

		return 'return ['.implode(',', $code).'];';
	}

	public function exec(bool $official = false) { return call('execute', ['code' => $this->getCode()], $official); }
}

?>