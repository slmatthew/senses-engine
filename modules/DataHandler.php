<?php

/**
 * Class for handle the data from VK
 * @author slmatthew
 * @package datahandler
 */

if(is_null($config) || empty($config) || !isset($config))  throw new Exception('You need to set config');
if(!function_exists('request')) throw new Exception('Requests module is not loaded');

$GLOBALS['config'] = $config;

function terminal($text) {
	if(NEED_LP_LOGS) echo "{$text}\n";
}

class DataHandler {

	/**
	 * Init DataHandler class
	 * @param string $type Type of data handling: "cb" (if you use Callback API) or "lp" (if you use Longpoll API). Default: "cb"
	 * @param BotEngine $be BotEngine class
     * @return void
     * @throws Exception
	 * @since v0.1
	 */
	public function __construct(string $type, $be) {
		if($type == 'cb' && $GLOBALS['config']['type'] == 'community') {
			// we need to handle request. Add in v0.2-alpha
			$data = file_get_contents('php://input');
			if(!is_null($data)) {
				$data = @json_decode($data, true);
				if(!is_null($data)) {
					if(isset($GLOBALS['config']['secret'])) {
						if(isset($data['secret']) && $data['secret'] == $GLOBALS['config']['secret']) {
							if($data['type'] != 'confirmation') echo 'ok';
							$be->onData($data);
						} else exit('Invalid secret key');
					} else {
						if($data['type'] != 'confirmation') echo 'ok';
						$be->onData($data);
					}
				}
			}
		} elseif($type == 'lp') {
			// we need to start longpoll
			$this->startLp($be, $GLOBALS['config']['type']);
		} else throw new Exception('Unknown type for DataHandler');
	}

	/**
	 * Longpolling
	 * @param BotEngine $be BotEngine class
	 * @param string $type Longpolling type: user or community
   * @return void
   * @throws Exception
	 * @since v0.1
	 */
	public function startLp($be, string $type) {
		if($type == 'community') {
			$lp = call('groups.getLongPollServer', ['group_id' => $GLOBALS['config']['api_id']])['response'];
		} elseif($type == 'user') {
			$lp = call('messages.getLongPollServer', ['lp_version' => 7])['response'];
		} else return false;

		$server = $lp['server'];
		$key = $lp['key'];

		$baseurl = $type == 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode=2&version=7&ts=%d";
		$url = sprintf($baseurl, $lp['ts']);

		terminal("Starting {$type} longpoll...");

		$li = 0;

		while(true) {
			$result = request($url);

			if(!is_null($result)) {
				if($li == 0) {
					terminal("Started longpoll. Got first updates");
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
							if($type == 'community') {
								$lp = call('groups.getLongPollServer', ['group_id' => $GLOBALS['config']['api_id']])['response'];
							} elseif($type == 'user') {
								$lp = call('messages.getLongPollServer', ['lp_version' => 7])['response'];
							}

							if(isset($lp['response'])) {
								$lp = $lp['response'];
								
								$server = $lp['server'];
								$key = $lp['key'];

								$baseurl = $type == 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode=2&version=7&ts=%d";
								$url = sprintf($baseurl, $lp['ts']);
							} else {
								if($type == 'community') {
									$lp = call('groups.getLongPollServer', ['group_id' => $GLOBALS['config']['api_id']])['response'];
								} elseif($type == 'user') {
									$lp = call('messages.getLongPollServer', ['lp_version' => 7])['response'];
								}

								if(isset($lp['response'])) {
									$lp = $lp['response'];

									$server = $lp['server'];
									$key = $lp['key'];

									$baseurl = $type == 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode=2&version=7&ts=%d";
									$url = sprintf($baseurl, $lp['ts']);
								} else throw new Exception('Can\'t to get new Longpoll data');
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