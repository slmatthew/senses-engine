<?php

/**
 * Heart of engine
 * @author slmatthew
 * @package botengine
 */

class BotEngine {
	
	/**
	 * @ignore
	 */
	public $commands = [];

	/**
	 * @ignore
	 */
	public $payloadCommands = []; // This commands will be detected from payload

	/**
	 * @ignore
	 */
	public $aliases = [];

	/**
	 * @ignore
	 */
	public $dataHandlers = [];

	/**
	 * Commands will be handled in lower case
	 * @var bool
	 * @since v0.1
	 */
	public $needLowerCase = true;

	/**
	 * BotEngine constructor
	 * @param bool $needLowerCase Commands will be handled in lower case
	 * @return void
	 * @since v0.1
	 */
	public function __construct(bool $needLowerCase = true) {
		$this->needLowerCase = $needLowerCase;

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
	 * @return bool
	 * @since v0.1
	 */
	public function addCommand(string $name, callable $handler) {
		if($this->needLowerCase) $name = mb_strtolower($name);

		if(strlen($name) > 0) {
			$this->commands[$name] = $handler;
			return true;
		}

		return false;
	}

	/**
	 * Commands aliases constructor
	 * @param array $names Names of commands
	 * @param callable $handler Function-handler of commands. This construction will be used when commands are called: $handler($data)
	 * @return void
	 * @since v0.1
	 */
	public function addCommands(array $names, callable $handler) {
		foreach($names as $key => $name) {
			if(strlen($name) == 0) continue;
			if($this->needLowerCase) $name = mb_strtolower($name);

			$this->commands[$name] = $handler;
		}
	}

	/**
	 * Payload commands constructor (handle payload param from message object: {"command": "start"})
	 * @param array $names Name of command
	 * @param callable $handler Function-handler of command. This construction will be used when command is called: $handler($data)
	 * @return bool
	 * @since v0.4
	 */
	public function addPayloadCommands(array $names, callable $handler) {
		if(count($names) == 1) {
			if(strlen($names[0]) == 0) return false;
			if($this->needLowerCase) $names[0] = mb_strtolower($names[0]);

			$this->payloadCommands[$names[0]] = $handler;
		} else {
			foreach($names as $key => $name) {
				if(strlen($name) == 0) continue;
				if($this->needLowerCase) $name = mb_strtolower($name);

				$this->payloadCommands[$name] = $handler;
			}
		}

		return true;
	}

	/**
	 * Payload and text commands aliases
	 * @param string $payloadName
	 * @param string $textName
	 * @return bool
	 * @since v0.4
	 */
	public function addCommandsAlias(string $payloadName, string $textName) {
		if($this->needLowerCase) {
			$payloadName = mb_strtolower($payloadName);
			$textName = mb_strtolower($textName);
		}

		if(
			($this->checkPayloadCommand($payloadName) && !$this->checkCommand($textName)) || 
			(!$this->checkPayloadCommand($payloadName) && $this->checkCommand($textName)) || 
			($this->checkPayloadCommand($payloadName) && $this->checkCommand($textName))
		) {
			$this->aliases[$payloadName] = $textName;
			$this->aliases[$textName] = $payloadName;
			return true;
		}

		return false;
	}

	/**
	 * Payload and text commands checker
	 * @param string $payloadName Payload command name
	 * @param string $textName Text command name
	 * @param array $data Message data
	 * @return bool
	 * @since v0.4
	 */
	public function checkAllCommands(string $payloadName, string $textName, array $data) {
		if($this->needLowerCase) {
			$payloadName = mb_strtolower($payloadName);
			$textName = mb_strtolower($textName);
		}

		if(isset($this->aliases[$payloadName])) {
			if($this->aliases[$payloadName] == $textName) {
				if($this->checkPayloadCommand($payloadName)) {
					return $this->runPayloadCommand($payloadName, $data); 
				} elseif($this->checkCommand($textName)) {
					return $this->runCommand($textName, $data); 
				} else return $this->runCommand('default', $data);
			} elseif($this->checkPayloadCommand($payloadName)) {
				return $this->runPayloadCommand($payloadName, $data);
			} elseif($this->checkCommand($this->aliases[$payloadName])) {
				return $this->runCommand($this->aliases[$payloadName], $data);
			} else return $this->runCommand('default', $data);
		} elseif($this->checkPayloadCommand($payloadName)) {
			return $this->runPayloadCommand($payloadName, $data);
		} elseif(isset($this->aliases[$textName])) {
			if($this->aliases[$textName] == $payloadName) {
				if($this->checkCommand($textName)) {
					return $this->runCommand($textName, $data); 
				} elseif($this->checkPayloadCommand($payloadName)) {
					return $this->runPayloadCommand($payloadName, $data); 
				} else return $this->runCommand('default', $data);
			} elseif($this->checkCommand($textName)) {
				return $this->runCommand($textName, $data);
			} elseif($this->checkPayloadCommand($this->aliases[$textName])) {
				return $this->runPayloadCommand($this->aliases[$textName], $data);
			} else return $this->runCommand('default', $data);
		} elseif($this->checkCommand($textName)) {
			return $this->runCommand($textName, $data);
		} else return $this->runCommand('default', $data);
	}

	/**
	 * Payload commands checker
	 * @param string $name Name of command
	 * @return bool
	 * @since v0.4
	 */
	protected function checkPayloadCommand(string $name) {
		if($this->needLowerCase) $name = mb_strtolower($name);

		return isset($this->payloadCommands[$name]);
	}

	/**
	 * Payload commands runner
	 * @param string $name Name of command
	 * @param array $data Message data
	 * @return int|bool
	 * @since v0.4
	 */
	public function runPayloadCommand(string $name, array $data) {
		if($this->needLowerCase) $name = mb_strtolower($name);

		if($this->checkPayloadCommand($name)) {
			return $this->payloadCommands[$name]($data);
		}

		return -1;
	}

	/**
	 * Commands checker
	 * @param string $name Name of command
	 * @return bool
	 * @since v0.1
	 */
	protected function checkCommand(string $name) {
		if($this->needLowerCase) $name = mb_strtolower($name);

		return isset($this->commands[$name]);
	}

	/**
	 * Commands runner
	 * @param string $name Name of command
	 * @param array $data Message data
	 * @return bool
	 * @since v0.1
	 */
	public function runCommand(string $name, array $data) {
		if($this->needLowerCase) $name = mb_strtolower($name);

		if($this->checkCommand($name)) {
			return $this->commands[$name]($data);
		}

		return -1;
	}

	/**
	 * @ignore
	 */
	public function onData(array $data) {
		if(!is_null($data)) {
			if($data['type'] == 'message_new') {
				if($this->checkDataHandler('message_new') && $this->runDataHandler('message_new', $data) === false) return;
				
				$text = mb_strtolower($data['object']['message']['text']);
				$exp = strlen($text) > 0 ? explode(' ', $text) : [''];

				$check = isset($data['object']['message']['payload']) && isset(json_decode($data['object']['message']['payload'], true)['command']);
				if($check) {
					$this->checkAllCommands(json_decode($data['object']['message']['payload'], true)['command'], $exp[0], $data);
				} else {
					$this->checkAllCommands('', $exp[0], $data);
				}
			} elseif($this->checkDataHandler($data['type'])) {
				$this->runDataHandler($data['type'], $data);
			}
		}
	}

	/**
	 * Data handlers constructor
	 * @param string $name Name of event type
	 * @param callable $handler Data handler
	 * @return void
	 * @since v0.1
	 */
	public function addDataHandler(string $name, callable $handler) {
		$name = mb_strtolower($name);

		$this->dataHandlers[$name] = $handler;
	}

	/**
	 * Data handlers checker
	 * @param string $name Name of event type
	 * @return bool
	 * @since v0.1
	 */
	protected function checkDataHandler(string $name) {
		$name = mb_strtolower($name);

		return isset($this->dataHandlers[$name]);
	}

	/**
	 * Data handlers runner
	 * @param string $name Name of datahandler
	 * @param array $data Event data
	 * @return int|bool
	 * @since v0.1
	 */
	public function runDataHandler(string $name, array $data) {
		$name = mb_strtolower($name);

		if($this->checkDataHandler($name)) {
			return $this->dataHandlers[$name]($data);
		}

		return -1;
	}
}

?>