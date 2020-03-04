<?php

class sensesDebugger {
	private static $debugger = null;

	public static function set(Debugger $debugger) {
		sensesDebugger::$debugger = $debugger;
	}

	public static function get() {
		return sensesDebugger::$debugger;
	}

	public static function event(string $name, array $data = []) {
		if(!is_null(sensesDebugger::$debugger)) sensesDebugger::$debugger->__event($name, $data);
	}
}

class DebuggerEvents {
	public const API_CALL = 'api';
	public const API_ERROR = 'api-error';
	public const API_RESULT = 'api-result';

	public const LP_START = 'lp-start';
	public const LP_FIRST_UPDATES = 'lp-started';
	public const LP_FAILED = 'lp-failed';
	public const LP_DATA_UPDATED = 'lp-server-updated';
	public const LP_TS_UPDATED = 'lp-ts-updated';
}

class Debugger {
	private $types = [];
	private $handlers = [];

	public function __construct(array $types) {
		$this->types = $types;

		sensesDebugger::set($this);
	}

	public function on(string $type, callable $handler) {
		$this->handlers[$type] = $handler;
	}

	public function __event(string $name, array $data = []) {
		if(in_array($name, $this->types) && isset($this->handlers[$name])) {
			$this->handlers[$name]($data);
		}
	}
}

?>