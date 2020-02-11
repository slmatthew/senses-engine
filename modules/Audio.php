<?php

if(is_null($config) || empty($config) || !isset($config))  throw new ConfigException('You need to set config');
if(!function_exists('request')) throw new RequestsException('Requests module is not loaded');

class VkAudio {
	private const RECEIPT = 'JSv5FBbXbY:APA91bF2K9B0eh61f2WaTZvm62GOHon3-vElmVq54ZOL5PHpFkIc85WQUxUH_wae8YEUKkEzLCcUC5V4bTWNNPbjTxgZRvQ-PLONDMZWo_6hwiqhlMM7gIZHM2K2KhvX-9oCcyD1ERw4';

	private $audioToken = '';

	public function __construct(bool $needRefresh = false) {
		global $config;

		if($config['type'] != 'user') throw new ClientException('');

		if($needRefresh) {
			$this->refreshToken();
		} else {
			$this->audioToken = $config['token'];
		}
	}

	private function refreshToken() {
		$refresh = $this->request('auth.refreshToken', ['receipt' => self::RECEIPT]);
		if(isset($refresh['error'])) throw new TokenRefreshException(json_encode($refresh));

		$this->audioToken = $refresh['response']['token'];
	}

	/* API methods */
	public function get(array $params = []) {
		global $config;

		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['count'])) $params['count'] = 25;

		return $this->request('audio.get', $params);
	}

	public function add(int $audio_id, int $owner_id, array $params = []) {
		$params['audio_id'] = $audio_id;
		$params['owner_id'] = $owner_id;

		return $this->request('audio.add', $params);
	}

	public function addAlbum(string $title, array $params = []) {
		$params['title'] = $title;

		return $this->request('audio.addAlbum', $params);
	}

	public function delete(int $audio_id, int $owner_id, array $params = []) {
		$params['audio_id'] = $audio_id;
		$params['owner_id'] = $owner_id;

		return $this->request('audio.delete', $params);
	}

	public function deleteAlbum(int $album_id, array $params = []) {
		$params['album_id'] = $album_id;

		return $this->request('audio.deleteAlbum', $params);
	}

	public function edit(int $owner_id, int $audio_id, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['audio_id'] = $audio_id;

		return $this->request('audio.deleteAlbum', $params);
	}

	public function editAlbum(int $album_id, string $title, array $params = []) {
		$params['album_id'] = $album_id;
		$params['title'] = $title;

		return $this->request('audio.editAlbum', $params);
	}

	public function getAlbums(array $params = []) {
		global $config;

		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['count'])) $params['count'] = 50;

		return $this->request('audio.getAlbums', $params);
	}

	public function getBroadcastList(array $params = []) {
		if(!isset($params['filter'])) $params['filter'] = 'all';

		return $this->request('audio.getBroadcastList', $params);
	}

	/* end API methods */

	private function request(string $method, array $params) { return call($method, $params, true); }
}

?>