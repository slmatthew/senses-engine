<?php

/**
 * Class for handle the data from VK
 * @author slmatthew
 * @package datahandler
 */

if(!isset($config) || is_null($config) || empty($config))  throw new ConfigException('You need to set config');
if(!function_exists('request')) throw new RequestsException('Requests module is not loaded');

$GLOBALS['config'] = $config;

function terminal($text) {
	if(NEED_LP_LOGS) echo "{$text}\n";
}

class DataHandler {

	/**
	 * Init DataHandler class
	 * @param string $type Type of data handling: "cb" (if you use Callback API) or "lp" (if you use Longpoll API). Default: "cb"
	 * @param BotEngine $be BotEngine class
	 * @throws TypeException
	 * @return void
	 * @since v0.1
	 */
	public function __construct(string $type, BotEngine $be, string $confirm_string = '') {
		if($type === 'cb' && $GLOBALS['config']['type'] === 'community') {
			// we need to handle request. Add in v0.2-alpha
			ini_set('display_errors', 0);

			$data = file_get_contents('php://input');
			if(!is_null($data)) {
				$data = @json_decode($data, true);
				if(!is_null($data)) {
					if(isset($GLOBALS['config']['secret'])) {
						if(isset($data['secret']) && $data['secret'] == $GLOBALS['config']['secret']) {
							if($data['type'] === 'confirmation' && $confirm_string) {
								echo $confirm_string;
							} elseif($data['type'] !== 'confirmation') {
								echo 'ok';
							}

							$be->onData($data, $GLOBALS['config']['type']);
						} else exit('Invalid secret key');
					} else {
						if($data['type'] === 'confirmation' && $confirm_string) {
							echo $confirm_string;
						} elseif($data['type'] !== 'confirmation') {
							echo 'ok';
						}
						
						$be->onData($data, $GLOBALS['config']['type']);
					}
				}
			}
		} elseif($type === 'lp') {
			// we need to start longpoll
			$this->startLp($be, $GLOBALS['config']['type']);
		} else throw new TypeException('Unknown type for DataHandler');
	}

	/**
	 * Longpolling
	 * @param BotEngine $be BotEngine class
	 * @param string $type Longpolling type: user or community
	 * @throws LongpollException
	 * @return void
	 * @since v0.1
	 */
	public function startLp(BotEngine $be, string $type) {
		if($type === 'community') {
			$lp = call('groups.getLongPollServer', ['group_id' => $GLOBALS['config']['api_id']])['response'];
		} elseif($type === 'user') {
			$lp = call('messages.getLongPollServer', ['lp_version' => 10])['response'];
		} else return false;

		$userlp_mode = 2 | 8 | 32 | 64 | 128;

		$server = $lp['server'];
		$key = $lp['key'];

		$baseurl = $type === 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode={$userlp_mode}&version=10&ts=%d";
		$url = sprintf($baseurl, $lp['ts']);

		terminal("Starting {$type} longpoll...");

		$li = 0;

		while(true) {
			$result = request($url);

			if(!is_null($result)) {
				if($li == 0) {
					terminal("Longpoll successfuly started. Got first updates");
					$li += 1;
				}

				if(isset($result['ts']) && isset($result['updates']) && !empty($result['updates'])) {
					$url = sprintf($baseurl, $result['ts']);
					$updates = $result['updates'];

					terminal("Got updates");

					foreach($updates as $key => $data) {
						$be->onData($data, $type);
					}
				} elseif(isset($result['failed'])) {
					terminal("Request new data");
					switch($result['failed']) {
						case 1:
							$url = sprintf($baseurl, $result['ts']);
							break;

						case 2: case 3:
							if($type === 'community') {
								$lp = call('groups.getLongPollServer', ['group_id' => $GLOBALS['config']['api_id']])['response'];
							} elseif($type === 'user') {
								$lp = call('messages.getLongPollServer', ['lp_version' => 10])['response'];
							}

							if(isset($lp['response'])) {
								$lp = $lp['response'];
								
								$server = $lp['server'];
								$key = $lp['key'];

								$baseurl = $type === 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode={$userlp_mode}&version=10&ts=%d";
								$url = sprintf($baseurl, $lp['ts']);
							} else {
								if($type === 'community') {
									$lp = call('groups.getLongPollServer', ['group_id' => $GLOBALS['config']['api_id']])['response'];
								} elseif($type === 'user') {
									$lp = call('messages.getLongPollServer', ['lp_version' => 10])['response'];
								}

								if(isset($lp['response'])) {
									$lp = $lp['response'];

									$server = $lp['server'];
									$key = $lp['key'];

									$baseurl = $type === 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode={$userlp_mode}&version=10&ts=%d";
									$url = sprintf($baseurl, $lp['ts']);
								} else throw new LongpollException('Can\'t to get new Longpoll data');
							}
							break;

						default: break;
					}
				} elseif(isset($result['ts'])) {
					$url = sprintf($baseurl, $result['ts']);
				}
			}
		}
	}
}

?>