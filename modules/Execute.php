<?php

/**
 * Class for `execute` method
 * @author slmatthew
 */

class Execute {
	/**
	 * Array with methods in format: ['method.name', ['name' => 'value']]
	 * @ignore
	 */
	protected $code = [];

	/**
	 * @param array $methods Array with arrays like this: ['method.name', ['name' => 'value']]. $methods in this case will be [['method.name', ['name' => 'value']]]
	 */
	public function __construct(array $methods) {
		$this->code = $methods;
	}

	/**
	 * Get `code` parameter for `execute` method
	 * @return string
	 */
	public function getCode() {
		$code = [];
		foreach($this->code as $_ => $val) {
			$code[] = "API.{$val[0]}(".json_encode($val[1], JSON_UNESCAPED_UNICODE).")";
		}

		return 'return ['.implode(',', $code).'];';
	}

	/**
	 * Call `execute` method
	 * @param bool $official Use Android app user-agent or no
	 * @return array
	 */
	public function exec(bool $official = false) { return call('execute', ['code' => $this->getCode()], $official); }
}

?>