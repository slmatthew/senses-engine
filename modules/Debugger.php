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

class Debugger {
	public const API_CALL = 'api';

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