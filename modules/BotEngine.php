<?php

if(!isset($config) || is_null($config) || empty($config))  throw new ConfigException('You need to set config');
$GLOBALS['config'] = $config;

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

		$this->commands['default'] = function($data) { };
	}

	/**
	 * Commands aliases constructor
	 * @param array $names Names of commands
	 * @param callable $handler Function-handler of commands. This construction will be used when commands are called: $handler($data)
	 * @return void
	 * @since v0.8
	 */
	public function onCommands(array $names, callable $handler) {
		foreach($names as $_ => $name) {
			if(strlen($name) == 0) continue;
			if($this->needLowerCase) $name = mb_strtolower($name);

			$this->commands[$name] = $handler;
		}

		return true;
	}

	/**
	 * Payload commands constructor (handle payload param from message object: {"command": "start"})
	 * @param array $names Name of command
	 * @param callable $handler Function-handler of command. This construction will be used when command is called: $handler($data)
	 * @return bool
	 * @since v0.8
	 */
	public function onPayload(array $names, callable $handler) {
		if(count($names) == 1) {
			if(strlen($names[0]) == 0) return false;
			if($this->needLowerCase) $names[0] = mb_strtolower($names[0]);

			$this->payloadCommands[$names[0]] = $handler;
		} else {
			foreach($names as $_ => $name) {
				if(strlen($name) == 0) continue;
				if($this->needLowerCase) $name = mb_strtolower($name);

				$this->payloadCommands[$name] = $handler;
			}
		}

		return true;
	}

	/**
	 * Universal commands constructor
	 * @param array $names Commands names
	 * @param callable $handler Function-handler
	 * @param bool $is_payload Register payload command?
	 * @return bool
	 */
	public function hear(array $names, callable $handler, bool $is_payload = false): bool {
		return $is_payload ? $this->onPayload($names, $handler) : $this->onCommands($names, $handler);
	}

	/**
	 * Payload and text commands aliases
	 * @param string $payloadName
	 * @param string $textName
	 * @return bool
	 * @since v0.8
	 */
	public function registerAlias(string $payloadName, string $textName) {
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
	public function checkAllCommands(string $payloadName, string $textName, array $data, object $some = null) {
		if($this->needLowerCase) {
			$payloadName = mb_strtolower($payloadName);
			$textName = mb_strtolower($textName);
		}

		if(isset($this->aliases[$payloadName])) {
			if($this->aliases[$payloadName] === $textName) {
				if($this->checkPayloadCommand($payloadName)) {
					return $this->runPayloadCommand($payloadName, $data, $some); 
				} elseif($this->checkCommand($textName)) {
					return $this->runCommand($textName, $data, $some); 
				} else return $this->runCommand('default', $data, $some);
			} elseif($this->checkPayloadCommand($payloadName)) {
				return $this->runPayloadCommand($payloadName, $data, $some);
			} elseif($this->checkCommand($this->aliases[$payloadName])) {
				return $this->runCommand($this->aliases[$payloadName], $data, $some);
			} else return $this->runCommand('default', $data, $some);
		} elseif($this->checkPayloadCommand($payloadName)) {
			return $this->runPayloadCommand($payloadName, $data, $some);
		} elseif(isset($this->aliases[$textName])) {
			if($this->aliases[$textName] === $payloadName) {
				if($this->checkCommand($textName)) {
					return $this->runCommand($textName, $data, $some); 
				} elseif($this->checkPayloadCommand($payloadName)) {
					return $this->runPayloadCommand($payloadName, $data, $some); 
				} else return $this->runCommand('default', $data, $some);
			} elseif($this->checkCommand($textName)) {
				return $this->runCommand($textName, $data, $some);
			} elseif($this->checkPayloadCommand($this->aliases[$textName])) {
				return $this->runPayloadCommand($this->aliases[$textName], $data, $some);
			} else return $this->runCommand('default', $data, $some);
		} elseif($this->checkCommand($textName)) {
			return $this->runCommand($textName, $data, $some);
		} else return $this->runCommand('default', $data, $some);
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
	public function runPayloadCommand(string $name, array $data, object $some = null) {
		if($this->needLowerCase) $name = mb_strtolower($name);

		if($this->checkPayloadCommand($name)) {
			return $this->payloadCommands[$name]($data, $some);
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
	public function runCommand(string $name, array $data, object $some = null) {
		if($this->needLowerCase) $name = mb_strtolower($name);

		if($this->checkCommand($name)) {
			return $this->commands[$name]($data, $some);
		}

		return -1;
	}

	/**
	 * @ignore
	 */
	public function onData(array $data, string $type) {
		if(!is_null($data)) {
			if($type === 'user') {
				if($data[0] == 4) {
					if($this->checkDataHandler('4') && $this->runDataHandler('4', $data) === false) return;

					$text = mb_strtolower($data[5]);
					$exp = strlen($text) > 0 ? explode(' ', $text) : [''];

					$some = new Message($data, false);

					if($some->isOut()) return;

					$this->checkAllCommands('', $exp[0], $data, $some);
				} elseif($this->checkDataHandler("{$data[0]}")) {
					$this->runDataHandler("{$data[0]}", $data);
				}
			} elseif($data['type'] === 'message_new') {
				if($this->checkDataHandler('message_new') && $this->runDataHandler('message_new', $data) === false) return;
				
				$text = mb_strtolower($data['object']['message']['text']);
				$exp = strlen($text) > 0 ? explode(' ', $text) : [''];

				$some = new Message($data);
				// I need not use $some->isOut() check because it is message_new event

				$check = false;
				if (isset($data['object']['message']['payload']))
				{
					// Fix for button start
					$v1 = json_decode($data['object']['message']['payload'], true);
					if (is_string($v1))
						$v2 = json_decode($v1, true);
					else
						$v2 = $v1;
					$check = isset($v2['command']);
				}
				if($check) {
					$this->checkAllCommands($v2['command'], $exp[0], $data, $some);
				} else {
					$this->checkAllCommands('', $exp[0], $data, $some);
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
	 * @since v0.8
	 */
	public function on(string $name, callable $handler) {
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