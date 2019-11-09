<?php

/**
 * It's a file for handle the data from VK
 */

if(is_null($config) || empty($config) || !isset($config))  throw new Exception('You need to set config');
if(!function_exists('request')) throw new Exception('Requests module is not loaded');

class DataHandler {

	/**
	 * Init DataHandler class
	 * @param string $type Type of data handling: "cb" (if you use Callback API) or "lp" (if you use Longpoll API). Default: "cb"
	 * @since v0.1
	 */
	public function __construct(string $type) {
		if($type == 'cb') {
			// ok, we shouldn't do anything
		} elseif($type == 'lp') {
			// we need to start longpoll
			$this->startLp();
		} else throw new Exception('Unknown type for DataHandler');
	}

	/**
	 * Longpolling
	 * @since v0.1
	 */
	public function startLp() {
		$lp = call('groups.getLongPollServer', ['group_id' => $config['group_id']])['response'];
		$server = $lp['server'];
		$key = $lp['key'];

		$baseurl = "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d";
		$url = sprintf($baseurl, $lp['ts']);

		while(true) {
			$result = request($url);

			if(!is_null($result)) {
				if(isset($result['ts']) && isset($result['updates']) && !empty($result['updates'])) {
					$url = sprintf($baseurl, $result['ts']);
					$updates = $result['updates'];

					foreach($updates as $key => $data) {
						switch($data['type']) {
							// $this->onData()
						}
					}
				} elseif(isset($result['failed'])) {
					switch($result['failed']) {
						case 1:
							$url = sprintf($baseurl, $result['ts']);
							break;

						case 2: case 3:
							$lp = call('groups.getLongPollServer', ['group_id' => $config['group_id']]);
							if(isset($lp['response'])) {
								$lp = $lp['response'];
								
								$server = $lp['server'];
								$key = $lp['key'];

								$baseurl = "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d";
								$url = sprintf($baseurl, $lp['ts']);
							} else {
								$lp = call('groups.getLongPollServer', ['group_id' => $config['group_id']]);

								if(isset($lp['response'])) {
									$lp = $lp['response'];

									$server = $lp['server'];
									$key = $lp['key'];

									$baseurl = "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d";
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

	/**
	 * Data provider
	 * @param array $data Data from CB or LP
	 * @since v0.1
	 */
	public function onData($data) {
		// BotEngine->onData($data)
	}
}

?>