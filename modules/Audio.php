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

	public function addPlaylist(string $title, array $params = []) {
		$params['title'] = $title;

		return $this->request('audio.addPlaylist', $params);
	}

	public function delete(int $audio_id, int $owner_id, array $params = []) {
		$params['audio_id'] = $audio_id;
		$params['owner_id'] = $owner_id;

		return $this->request('audio.delete', $params);
	}

	public function deletePlaylist(int $owner_id, int $playlist_id, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['playlist_id'] = $playlist_id;

		return $this->request('audio.deletePlaylist', $params);
	}

	public function edit(int $owner_id, int $audio_id, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['audio_id'] = $audio_id;

		return $this->request('audio.edit', $params);
	}

	public function editPlaylist(int $owner_id, int $playlist_id, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['playlist_id'] = $playlist_id;

		return $this->request('audio.editPlaylist', $params);
	}

	public function getPlaylists(array $params = []) {
		global $config;

		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['count'])) $params['count'] = 50;

		return $this->request('audio.getPlaylists', $params);
	}

	public function getBroadcastList(array $params = []) {
		if(!isset($params['filter'])) $params['filter'] = 'all';

		return $this->request('audio.getBroadcastList', $params);
	}

	public function getById(array $audios, array $params = []) {
		$params['audios'] = implode(',', $audios);

		return $this->request('audio.getById', $params);
	}

	public function getCount(int $owner_id, array $params = []) {
		$params['owner_id'] = $owner_id;

		return $this->request('audio.getCount', $params);
	}

	public function getLyrics(int $lyrics_id, array $params = []) {
		$params['lyrics_id'] = $lyrics_id;

		return $this->request('audio.getLyrics', $params);
	}

	public function getPopular(array $params = []) {
		if(!isset($params['count'])) $params['count'] = 100;

		return $this->request('audio.getPopular', $params);
	}

	public function getRecommendations(array $params = []) {
		if(!isset($params['count'])) $params['count'] = 100;

		return $this->request('audio.getRecommendations', $params);
	}

	public function getUploadServer(array $params = []) { return $this->request('audio.getUploadServer', $params); }

	public function moveToPlaylist(array $audio_ids, array $params = []) {
		$params['audio_ids'] = implode(',', $audio_ids);

		return $this->request('audio.moveToPlaylist', $params);
	}

	public function reorder(int $audio_id, array $params = []) {
		$params['audio_id'] = $audio_id;

		return $this->request('audio.reorder', $params);
	}

	public function restore(int $audio_id, array $params = []) {
		$params['audio_id'] = $audio_id;

		return $this->request('audio.restore', $params);
	}

	public function save(int $server, string $audio, array $params = []) {
		$params['server'] = $server;
		$params['audio'] = $audio;

		return $this->request('audio.save', $params);
	}

	public function search(array $params = []) { return $this->request('audio.search', $params); }
	public function setBroadcast(array $params = []) { return $this->request('audio.setBroadcast', $params); }

	/* end API methods */

	private function request(string $method, array $params) { return call($method, $params, true); }
}

?>