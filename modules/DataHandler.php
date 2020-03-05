<?php

/**
 * Class for handle the data from VK
 * @author slmatthew
 * @package datahandler
 */

if(!function_exists('request')) throw new RequestsException('Requests module is not loaded');

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
		if($type === 'cb' && vkAuthStorage::get()['api_type'] === 'community') {
			// we need to handle request. Add in v0.2-alpha
			ini_set('display_errors', 0);

			$data = file_get_contents('php://input');
			if(!is_null($data)) {
				$data = @json_decode($data, true);
				if(!is_null($data)) {
					if(isset(vkAuthStorage::get()['secret']) && vkAuthStorage::get()['secret']) {
						if(isset($data['secret']) && $data['secret'] == vkAuthStorage::get()['secret']) {
							if($data['type'] === 'confirmation' && $confirm_string) {
								echo $confirm_string;
							} elseif($data['type'] !== 'confirmation') {
								echo 'ok';
							}

							$be->onData($data, vkAuthStorage::get()['api_type']);
						} else exit('Invalid secret key');
					} else {
						if($data['type'] === 'confirmation' && $confirm_string) {
							echo $confirm_string;
						} elseif($data['type'] !== 'confirmation') {
							echo 'ok';
						}
						
						$be->onData($data, vkAuthStorage::get()['api_type']);
					}
				}
			}
		} elseif($type === 'lp') {
			// we need to start longpoll
			$this->startLp($be, vkAuthStorage::get()['api_type']);
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
			$lp = call('groups.getLongPollServer', ['group_id' => vkAuthStorage::get()['api_id']])['response'];
		} elseif($type === 'user') {
			$lp = call('messages.getLongPollServer', ['lp_version' => 10])['response'];
		} else return false;

		$userlp_mode = 2 | 8 | 32 | 64 | 128;

		$server = $lp['server'];
		$key = $lp['key'];

		$baseurl = $type === 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode={$userlp_mode}&version=10&ts=%d";

		@mkdir(__DIR__.'/.senses');

		$cached_ts = @file_get_contents(__DIR__.'/.senses/ts');
		if($cached_ts === false) {
			$url = sprintf($baseurl, $lp['ts']);
		} else {
			$url = sprintf($baseurl, $cached_ts);

			unlink(__DIR__.'/.senses/ts');
		}

		file_put_contents(__DIR__.'/.senses/ts', $lp['ts']);

		terminal("Starting {$type} longpoll..."); // terminal() will be deleted
		sensesDebugger::event(DebuggerEvents::LP_START, [
			'lp_server' => $lp,
			'url' => $url,
			'type' => $type
		]);

		$li = 0;

		while(true) {
			$result = request($url);

			if(!is_null($result)) {
				if($li == 0) {
					terminal("Longpoll successfuly started. Got first updates");
					sensesDebugger::event(DebuggerEvents::LP_FIRST_UPDATES, []);
					$li += 1;
				}

				if(isset($result['ts']) && isset($result['updates']) && !empty($result['updates'])) {
					$url = sprintf($baseurl, $result['ts']);
					$updates = $result['updates'];

					unlink(__DIR__.'/.senses/ts');
					file_put_contents(__DIR__.'/.senses/ts', $result['ts']);

					terminal("Got updates");

					foreach($updates as $key => $data) {
						$be->onData($data, $type);
					}
				} elseif(isset($result['failed'])) {
					terminal("Request new data");
					sensesDebugger::event(DebuggerEvents::LP_FAILED, [
						'failed' => $result['failed'],
						'result' => $result
					]);
					switch($result['failed']) {
						case 1:
							$url = sprintf($baseurl, $result['ts']);

							unlink(__DIR__.'/.senses/ts');
							file_put_contents(__DIR__.'/.senses/ts', $result['ts']);
							break;

						case 2: case 3:
							if($type === 'community') {
								$lp = call('groups.getLongPollServer', ['group_id' => vkAuthStorage::get()['api_id']])['response'];
							} elseif($type === 'user') {
								$lp = call('messages.getLongPollServer', ['lp_version' => 10])['response'];
							}

							if(isset($lp['response'])) {
								$lp = $lp['response'];
								
								$server = $lp['server'];
								$key = $lp['key'];

								$baseurl = $type === 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode={$userlp_mode}&version=10&ts=%d";
								$url = sprintf($baseurl, $lp['ts']);

								unlink(__DIR__.'/.senses/ts');
								file_put_contents(__DIR__.'/.senses/ts', $lp['ts']);

								sensesDebugger::event(DebuggerEvents::LP_DATA_UPDATED, [
									'lp' => $lp,
									'url' => $url
								]);
							} else {
								if($type === 'community') {
									$lp = call('groups.getLongPollServer', ['group_id' => vkAuthStorage::get()['api_id']])['response'];
								} elseif($type === 'user') {
									$lp = call('messages.getLongPollServer', ['lp_version' => 10])['response'];
								}

								if(isset($lp['response'])) {
									$lp = $lp['response'];

									$server = $lp['server'];
									$key = $lp['key'];

									$baseurl = $type === 'community' ? "{$server}?act=a_check&key={$key}&wait=25&mode=2&ts=%d" : "https://{$server}?act=a_check&key={$key}&wait=25&mode={$userlp_mode}&version=10&ts=%d";
									$url = sprintf($baseurl, $lp['ts']);

									unlink(__DIR__.'/.senses/ts');
									file_put_contents(__DIR__.'/.senses/ts', $lp['ts']);

									sensesDebugger::event(DebuggerEvents::LP_DATA_UPDATED, [
										'lp' => $lp,
										'url' => $url
									]);
								} else throw new LongpollException('Can\'t to get new Longpoll data');
							}
							break;

						default: break;
					}
				} elseif(isset($result['ts'])) {
					$url = sprintf($baseurl, $result['ts']);

					unlink(__DIR__.'/.senses/ts');
					file_put_contents(__DIR__.'/.senses/ts', $result['ts']);

					sensesDebugger::event(DebuggerEvents::LP_TS_UPDATED, [
						'ts' => $result['ts'],
						'url' => $url
					]);
				}
			}
		}
	}
}

?>