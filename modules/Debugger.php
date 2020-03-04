<?php

/**
 * @ignore
 */
class sensesDebugger {
	/**
	 * @ignore
	 */
	private static $debugger = null;

	/**
	 * @ignore
	 */
	public static function set(Debugger $debugger) {
		sensesDebugger::$debugger = $debugger;
	}

	/**
	 * @ignore
	 */
	public static function get() {
		return sensesDebugger::$debugger;
	}

	/**
	 * @ignore
	 */
	public static function event(string $name, array $data = []) {
		if(!is_null(sensesDebugger::$debugger)) sensesDebugger::$debugger->__event($name, $data);
	}
}

class DebuggerEvents {
	/**
	 * @ignore
	 */
	public const API_CALL = 'api';

	/**
	 * @ignore
	 */
	public const API_ERROR = 'api-error';
	
	/**
	 * @ignore
	 */
	public const API_RESULT = 'api-result';

	/**
	 * @ignore
	 */
	public const LP_START = 'lp-start';
	
	/**
	 * @ignore
	 */
	public const LP_FIRST_UPDATES = 'lp-started';
	
	/**
	 * @ignore
	 */
	public const LP_FAILED = 'lp-failed';
	
	/**
	 * @ignore
	 */
	public const LP_DATA_UPDATED = 'lp-server-updated';
	
	/**
	 * @ignore
	 */
	public const LP_TS_UPDATED = 'lp-ts-updated';
}

/**
 * Debugger
 * @author slmatthew
 */
class Debugger {
	/**
	 * @var array
	 */
	private $types = [];

	/**
	 * @var array
	 */
	private $handlers = [];

	/**
	 * @param array $types Events types
	 * @return void
	 */
	public function __construct(array $types) {
		$this->types = $types;

		sensesDebugger::set($this);
	}

	/**
	 * Add event handler
	 * @param string $type Event type
	 * @param callable $handler Function-handler
	 * @return void
	 */
	public function on(string $type, callable $handler) {
		$this->handlers[$type] = $handler;
	}

	/**
	 * @ignore
	 */
	public function __event(string $name, array $data = []) {
		if(in_array($name, $this->types) && isset($this->handlers[$name])) {
			$this->handlers[$name]($data);
		}
	}
}

?>