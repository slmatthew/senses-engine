<?php

if(is_null($config) || empty($config) || !isset($config))  throw new ConfigException('You need to set config');
if(!function_exists('request')) throw new RequestsException('Requests module is not loaded');

/**
 * Audio
 * @author slmatthew
 * @package audio
 */

class VkAudio {
	/**
	 * @ignore
	 */
	private const RECEIPT = 'JSv5FBbXbY:APA91bF2K9B0eh61f2WaTZvm62GOHon3-vElmVq54ZOL5PHpFkIc85WQUxUH_wae8YEUKkEzLCcUC5V4bTWNNPbjTxgZRvQ-PLONDMZWo_6hwiqhlMM7gIZHM2K2KhvX-9oCcyD1ERw4';

	/**
	 * @ignore
	 */
	private $audioToken = '';

	/**
	 * Constructor
	 * @param bool $needRefresh Need refresh token or no
	 * @return void
	 * @since v0.6.1
	 */
	public function __construct(bool $needRefresh = false) {
		global $config;

		if($config['type'] != 'user') throw new ClientException('');

		if($needRefresh) {
			$this->refreshToken();
		} else {
			$this->audioToken = $config['token'];
		}
	}

	/**
	 * @ignore
	 */
	private function refreshToken() {
		$refresh = $this->request('auth.refreshToken', ['receipt' => self::RECEIPT]);
		if(isset($refresh['error'])) throw new TokenRefreshException(json_encode($refresh));

		$this->audioToken = $refresh['response']['token'];
	}

	/* API methods */

	/**
	 * audio.get
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function get(array $params = []) {
		global $config;

		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['count'])) $params['count'] = 25;

		return $this->request('audio.get', $params);
	}

	/**
	 * audio.add
	 * @param int $audio_id Audio ID
	 * @param int $owner_id Owner ID
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function add(int $audio_id, int $owner_id, array $params = []) {
		$params['audio_id'] = $audio_id;
		$params['owner_id'] = $owner_id;

		return $this->request('audio.add', $params);
	}

	/**
	 * audio.addPlaylist
	 * @param string $title Playlist title
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function addPlaylist(string $title, array $params = []) {
		$params['title'] = $title;

		return $this->request('audio.addPlaylist', $params);
	}

	/**
	 * audio.delete
	 * @param int $audio_id Audio ID
	 * @param int $owner_id Owner ID
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function delete(int $audio_id, int $owner_id, array $params = []) {
		$params['audio_id'] = $audio_id;
		$params['owner_id'] = $owner_id;

		return $this->request('audio.delete', $params);
	}

	/**
	 * audio.deletePlaylist
	 * @param int $owner_id Owner ID
	 * @param int $playlist_id Playlist ID
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function deletePlaylist(int $owner_id, int $playlist_id, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['playlist_id'] = $playlist_id;

		return $this->request('audio.deletePlaylist', $params);
	}

	/**
	 * audio.edit
	 * @param int $owner_id Owner ID
	 * @param int $audio_id Audio ID
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function edit(int $owner_id, int $audio_id, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['audio_id'] = $audio_id;

		return $this->request('audio.edit', $params);
	}

	/**
	 * audio.editPlaylist
	 * @param int $owner_id Owner ID
	 * @param int $playlist_id Playlist ID
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function editPlaylist(int $owner_id, int $playlist_id, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['playlist_id'] = $playlist_id;

		return $this->request('audio.editPlaylist', $params);
	}

	/**
	 * audio.getPlaylists
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getPlaylists(array $params = []) {
		global $config;

		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['count'])) $params['count'] = 50;

		return $this->request('audio.getPlaylists', $params);
	}

	/**
	 * audio.getBroadcastList
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getBroadcastList(array $params = []) {
		if(!isset($params['filter'])) $params['filter'] = 'all';

		return $this->request('audio.getBroadcastList', $params);
	}

	/**
	 * audio.getById
	 * @param array $audios Audio IDs list
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getById(array $audios, array $params = []) {
		$params['audios'] = implode(',', $audios);

		return $this->request('audio.getById', $params);
	}

	/**
	 * audio.getCount
	 * @param int $owner_id Owner ID
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getCount(int $owner_id, array $params = []) {
		$params['owner_id'] = $owner_id;

		return $this->request('audio.getCount', $params);
	}

	/**
	 * audio.getLyrics
	 * @param int $lyrics_id Lyrics ID from audio object
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getLyrics(int $lyrics_id, array $params = []) {
		$params['lyrics_id'] = $lyrics_id;

		return $this->request('audio.getLyrics', $params);
	}

	/**
	 * audio.getPopular
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getPopular(array $params = []) {
		if(!isset($params['count'])) $params['count'] = 100;

		return $this->request('audio.getPopular', $params);
	}

	/**
	 * audio.getRecommendations
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getRecommendations(array $params = []) {
		if(!isset($params['count'])) $params['count'] = 100;

		return $this->request('audio.getRecommendations', $params);
	}

	/**
	 * audio.getUploadServer
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function getUploadServer(array $params = []) { return $this->request('audio.getUploadServer', $params); }

	/**
	 * audio.addToPlaylist
	 * @param int $owner_id Owner ID
	 * @param array $audio_ids Audio IDs in `{owner_id}_{audio_id}` format
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function addToPlaylist(int $owner_id, array $audio_ids, array $params = []) {
		$params['owner_id'] = $owner_id;
		$params['audio_ids'] = implode(',', $audio_ids);

		return $this->request('audio.addToPlaylist', $params);
	}

	/**
	 * audio.reorder
	 * @param int $audio_id Audio ID
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function reorder(int $audio_id, array $params = []) {
		$params['audio_id'] = $audio_id;

		return $this->request('audio.reorder', $params);
	}

	/**
	 * audio.restore
	 * @param int $audio_id
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function restore(int $audio_id, array $params = []) {
		$params['audio_id'] = $audio_id;

		return $this->request('audio.restore', $params);
	}

	/**
	 * audio.save
	 * @param int $server Field from audio.getUplaodServer
	 * @param string $audio Field from audio.getUploadServer
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function save(int $server, string $audio, array $params = []) {
		$params['server'] = $server;
		$params['audio'] = $audio;

		return $this->request('audio.save', $params);
	}

	/**
	 * audio.search
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function search(array $params = []) { return $this->request('audio.search', $params); }

	/**
	 * audio.setBroadcast
	 * @param array $params Parameters
	 * @return array
	 * @since v0.6.1
	 */
	public function setBroadcast(array $params = []) { return $this->request('audio.setBroadcast', $params); }

	/**
	 * execute.getPlaylist
	 * @param array $params Parameters
	 * @return array
	 * @since v0.7
	 */
	public function getPlaylist(array $params = []) {
		global $config;

		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['need_playlists'])) $params['need_playlists'] = 1;

		return $this->request('execute.getPlaylist', $params);
	}

	/**
	 * execute.getMusicPage
	 * @param array $params Parameters
	 * @return array
	 * @since v0.7
	 */
	public function getMusicPage(array $params = []) {
		global $config;

		if(!isset($params['owner_id'])) $params['owner_id'] = $config['api_id'];
		if(!isset($params['func_v'])) $params['func_v'] = 3;
		if(!isset($params['need_playlists'])) $params['need_playlists'] = 1;

		return $this->request('execute.getMusicPage', $params);
	}

	/**
	 * audio.getCatalog
	 * @param array $params Parameters
	 * @return array
	 * @since v0.8
	 */
	public function getCatalog(array $params = []) { return $this->request('audio.getCatalog', $params); }

	/* end API methods */

	/**
	 * Get mp3 link from audio object
	 * @param array $audio Audio from audio.get or other method
	 * @return bool|string
	 * @since v0.7
	 */
	public function getMp3Link(array $audio) {
		$url = $audio['url'];
		if(mb_substr($url, 0, 10) == 'https://cs' && stripos($url, '/audios/') === false) {
			if(preg_match('/https:\/\/(.*)\/(.*)\/(.*)\/index\.m3u8\?extra=(.*)/i', $url, $m)) {
				return "https://{$m[1]}/{$m[3]}.mp3?extra={$m[4]}";
			}
		} elseif(mb_substr($url, 0, 10) == 'https://ps' && stripos($url, '/audios/') !== false) {
			if(preg_match('/https:\/\/(.*)\/(.*)\/(.*)\/(.*)\/index\.m3u8\?extra=(.*)/i', $url, $m)) {
				return "https://{$m[1]}/{$m[3]}/{$m[4]}.mp3?extra={$m[5]}";
			}
		}

		return false;
	}

	/**
	 * Request to VK API
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @return array
	 * @since v0.8
	 */
	public function request(string $method, array $params) { return call($method, $params, true); }
}

?>