<?php

if(is_null($config) || empty($config) || !isset($config))  throw new ConfigException('You need to set config');
if(!function_exists('request')) throw new RequestsException('Requests module is not loaded');

class VkAudio {
	private const RECEIPT = 'JSv5FBbXbY:APA91bF2K9B0eh61f2WaTZvm62GOHon3-vElmVq54ZOL5PHpFkIc85WQUxUH_wae8YEUKkEzLCcUC5V4bTWNNPbjTxgZRvQ-PLONDMZWo_6hwiqhlMM7gIZHM2K2KhvX-9oCcyD1ERw4';

	private $audioToken = '';

	public function __construct() {
		if($config['type'] != 'user') throw new ClientException('');

		$refresh = $this->request('auth.refreshToken', ['receipt' => self::RECEIPT]);
		if(isset($refresh['error'])) throw new TokenRefreshException(json_encode($refresh));

		$this->audioToken = $refresh['response']['token'];
	}

	public function get(array $params = []) {
		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['count'])) $params['count'] = 25;

		return $this->request('audio.get', $params);
	}

	private function request(string $url, array $postfields = []) { return request($url, $postfields, 'VKAndroidApp/5.11.1-2316'); }
}

?>