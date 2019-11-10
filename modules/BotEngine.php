<?php

/**
 * Heart of engine
 */

class BotEngine {
	
	/**
	 * Structure:
	 * [name1 => handler1, name2 => handler2]
	 */
	public $commands = [];
	public $dataHandlers = [];

	public function __construct() {
		$this->commands['default'] = function($data) {
			if(function_exists('request')) {
				call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Unknown command', 'random_id' => 0]);
			}
		};
	}

	/**
	 * Commands constructor
	 * @param string $name Name of command
	 * @param callable $handler Function-handler of command. This construction will be used when command is called: $handler($data)
	 * @since v0.1
	 */
	public function addCommand(string $name, callable $handler) {
		$this->commands[$name] = $handler;
	}

	/**
	 * Commands aliases constructor
	 * @param string $name Names of commands
	 * @param callable $handler Function-handler of commands. This construction will be used when commands are called: $handler($data)
	 * @since v0.1
	 */
	public function addCommands(array $names, callable $handler) {
		foreach($names as $key => $name) {
			$this->commands[$name] = $handler;
		}
	}

	/**
	 * Commands checker
	 * @param string $name Name of command
	 * @since v0.1
	 */
	private function checkCommand(string $name) {
		return isset($this->commands[$name]);
	}

	/**
	 * Commands runner
	 * @param string $name Name of command
	 * @param array $data Message data
	 * @since v0.1
	 */
	public function runCommand(string $name, array $data) {
		if($this->checkCommand($name)) {
			return $this->commands[$name]($data);
		}

		return -1;
	}

	/**
	 * Data handler
	 * @param array $data Data from CB or LP
	 * @since v0.1
	 */
	public function onData(array $data) {
		if(!is_null($data)) {
			if($data['type'] == 'message_new') {
				$run = $this->runCommand(explode(' ', $data['object']['message']['text'])[0], $data);
				if(!$run || $run === -1) $this->runCommand('default', $data);
			} elseif($this->checkDataHandler($data['type'])) {
				$this->dataHandlers[$name]($data);
			}
		}
	}

	/**
	 * Data handlers constructor
	 * @param string $name Name of event type
	 * @param callable $handler Data handler
	 * @since v0.1
	 */
	public function addDataHandler(string $name, callable $handler) {
		$this->dataHandlers[$name] = $handler;
	}

	/**
	 * Data handlers checker
	 * @param string $name Name of event type
	 * @since v0.1
	 */
	private function checkDataHandler(string $name) {
		return isset($this->dataHandlers[$name]);
	}
}

?>