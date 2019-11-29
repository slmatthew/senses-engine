<?php

/**
 * Step-by-step commands class
 * @author slmatthew
 * @since v0.5
 * @package botengine
 */

class SBSCommands extends BotEngine {
	/**
	 * @ignore
	 */
	public $sbsc = [
		'payload' => [],
		'text' => []
	];

	/**
	 * @ignore
	 */
	public $users = [];

	/**
	 * @ignore
	 */
	public function onData(array $data) {
		if(!is_null($data)) {
			if($data['type'] == 'message_new') {
				if($this->checkDataHandler('message_new') && $this->runDataHandler('message_new', $data) === false) return;
				
				$user_id = $data['object']['message']['from_id'];
				if(isset($this->users["{$user_id}"]) && !is_null($this->users["{$user_id}"]) && $this->checkSbsCommand($this->users["{$user_id}"]['command']) !== false) {
					$u = $this->users["{$data['object']['message']['from_id']}"];
					$this->handleSbsCommand($u['type'], $u['command'], $data);
				} else {
					$text = mb_strtolower($data['object']['message']['text']);
					$exp = strlen($text) > 0 ? explode(' ', $text) : [''];

					$check = isset($data['object']['message']['payload']) && isset(json_decode($data['object']['message']['payload'], true)['command']);
					if($check) {
						$this->checkAllCommands(json_decode($data['object']['message']['payload'], true)['command'], $exp[0], $data);
					} else {
						$this->checkAllCommands('', $exp[0], $data);
					}
				}

				if($this->checkDataHandler('message_new')) {
					$this->runDataHandler('message_new', $data);
				}
			} elseif($this->checkDataHandler($data['type'])) {
				$this->runDataHandler($data['type'], $data);
			}
		}
	}

	/**
	 * Commands checker
	 * @param string $payloadName Payload command name
	 * @param string $textName Text command name
	 * @param array $data Message data
	 * @return bool
	 * @since v0.5
	 */
	public function checkAllCommands(string $payloadName, string $textName, array $data) {
		if($this->needLowerCase) {
			$payloadName = mb_strtolower($payloadName);
			$textName = mb_strtolower($textName);
		}

		$sbspayload = $this->checkSbsCommand($payloadName);
		$sbstext = $this->checkSbsCommand($textName);

		if($sbspayload == 'payload') {
			return $this->handleSbsCommand('payload', $payloadName, $data);
		} elseif($sbstext == 'text') {
			return $this->handleSbsCommand('text', $textName, $data);
		} elseif(isset($this->aliases[$payloadName])) {
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
	 * Step-by-step command constructor
	 * @param string $type Type of command: payload or text
	 * @param string $command Command
	 * @param int $steps Count of steps
	 * @param callable $handler Command handler
	 * @return bool
	 * @since v0.5
	 */
	public function addSbsCommand(string $type, string $command, int $steps, callable $handler) {
		if(!in_array($type, ['payload', 'text']) || $steps < 0) return false;

		$this->sbsc[$type][$command] = [
			'name' => $command,
			'steps' => $steps,
			'handler' => $handler
		];

		return true;
	}

	/**
	 * Step-by-step command handler
	 * @param string $type Command type: payload/text
	 * @param string $name Command name
	 * @param array $data Data from CB or LP
	 * @return bool
	 * @since v0.5
	 */
	public function handleSbsCommand(string $type, string $name, array $data) {
		if(!in_array($type, ['payload', 'text']) || !isset($this->sbsc[$type][$name])) return false;

		$cmd = $this->sbsc[$type][$name];

		$user_id = $data['object']['message']['from_id'];
		if(isset($this->users["{$user_id}"]) && $this->users["{$user_id}"]['command'] == $name && $this->users["{$user_id}"]['type'] == $type) {
			$nextStep = $this->users["{$user_id}"]['step'] + 1;
			if($nextStep >= $cmd['steps']) {
				$ed = $this->users["{$user_id}"];
				$ed['from_id'] = $user_id;

				if($cmd['handler']($ed, $data)) {
					unset($this->users["{$user_id}"]);
				}

				return true;
			}

			$ed = $this->users["{$user_id}"];
			$ed['from_id'] = $user_id;

			if($cmd['handler']($ed, $data)) {
				$this->users["{$user_id}"]['step'] = $nextStep;
			}

			return true;
		} else {
			$ed = [
				'command' => $name,
				'type' => $type,
				'step' => 0
			];

			$this->users["{$user_id}"] = $ed;

			$ed['from_id'] = $user_id;

			if($cmd['handler']($ed, $data)) {
				$this->users["{$user_id}"]['step'] += 1;
			}

			return true;
		}

		return false;
	}

	/**
	 * Step-by-step command checker
	 * @param string $name Command name
	 * @return string|bool
	 * @since v0.5
	 */
	public function checkSbsCommand(string $name) {
		if(isset($this->sbsc['payload'][$name])) return 'payload';
		if(isset($this->sbsc['text'][$name])) return 'text';

		return false;
	}
}

?>