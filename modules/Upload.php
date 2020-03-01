<?php

/**
 * @ignore
 */
class UploadRequests {
	/**
	 * @ignore
	 */
	protected function plainRequest(string $url, array $fields) {
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, CURL_VERIFY);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Senses Bot Engine/'.SEV);

		$text = curl_exec($ch);
		curl_close($ch);

		return $text;
	}

	/**
	 * @ignore
	 */
	protected function request(string $url, array $fields) {
		return json_decode($this->plainRequest($url, $fields), true);
	}
}

/**
 * @ignore
 */
class UploadHeart extends UploadRequests {

	/**
	 * Files data
	 * @ignore
	 */
	private $files = null;

	/**
	 * Upload url
	 * @ignore
	 */
	private $url = '';

	/**
	 * @param mixed[] $files Path to files. Must be like ['path1', 'path2', ..., 'path5'] or 'path'
	 * @param string $url
	 * @throws ParameterException
	 */
	public function __construct($files, string $url) {
		if(gettype($files) === 'array') {
			if(count($files) > 5 || count($files) < 1) throw new ParameterException('No more 5 files');

			$this->files = $files;
			$this->url = $url;
		} elseif(gettype($files) === 'string') {
			$this->files[] = $files;
			$this->url = $url;
		} else throw new ParameterException('Invalid function arguments');
	}

	/**
	 * Upload function
	 * @throws ParameterException
	 * @return array
	 */
	public function upload(string $name = 'file', array $fields = []) {
		if((gettype($this->files) === 'array' && (count($this->files) > 5 || empty($this->files))) || (gettype($this->files) === 'string' && strlen($this->url) == 0)) throw new ParameterException('Invalid files count');

		if(gettype($this->files) === 'string') $count = 1;
		elseif(gettype($this->files) === 'array') $count = count($this->files);
		else throw new ParameterException();

		// 1 file in `file` field
		if($count == 1) {
			$fields[$name] = $this->files[0];
		} else {
			$i = 1;
			// `file{index}` fields
			while(isset($this->files[$i - 1])) {
				$fields["{$name}{$i}"] = new \CURLfile($this->files[$i]);
				$i++;
			}
		}

		return $this->request($this->url, $fields);
	}
}

/**
 * Main class
 * @ignore
 */
class UploadManager {
	/**
	 * @param string $url Upload url
	 * @param files array|string Path to file(s)
	 * @param string $name Field name
	 * @param array $fields Default fields
	 * @throws ParameterException
	 * @return array
	 */
	public function upload(string $url, $files, string $name = 'file', array $fields = []) {
		$uh = new UploadHeart($files, $url);
		return $uh->upload($name, $fields);
	}

	/**
	 * @param string $url Remote file url
	 * @param string $path Path to folder. By default it is __DIR__.'/senses-tmp'
	 * @return string
	 */
	public function download(string $url, string $path = '/senses-tmp') {
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => 'Senses Bot Engine/'.SEV,
			CURLOPT_SSL_VERIFYPEER => CURL_VERIFY
		]);

		$result = curl_exec($ch);
		curl_close($result);

		$file_path = __DIR__."{$path}/".time().random_int(1, 10000).mb_substr($url, strlen($url) - 4, 4);
		file_put_contents($file_path, $result);

		return $file_path;
	}

	/**
	 * Save and upload file
	 * @param string $save_url Remote file url
	 * @param string $upload_url Upload url
	 * @param string $save_path Path to folder. By default it is __DIR__.'/senses-tmp'
	 * @throws ParameterException
	 * @return array
	 */
	public function reUpload(string $save_url, string $upload_url, string $save_path = '/senses-tmp') {
		$path = $this->download($save_url, $save_path);
		return $this->upload($upload_url, $path);
	}
}

/**
 * Photos upload
 * @author slmatthew
 * @package attachs
 */
class PhotosUpload extends UploadManager {
	/**
	 * Upload files to album
	 * @param array|string $files Path to file(s)
	 * @param int $album_id album_id
	 * @param int $group_id group_id
	 * @param array $serverParams photos.getUploadServer parameters
	 * @param array $saveParams photos.save parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function default($files, int $album_id = -1, int $group_id = -1, array $serverParams = [], array $saveParams = []) {
		if($album_id != -1) $serverParams['album_id'] = $album_id;
		if($group_id != -1) $serverParams['group_id'] = $group_id;

		$server = call('photos.getUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $files, 'file');
			if(isset($result['server'])) { // success check
				$saveParams['album_id'] = $result['aid'];
				$saveParams['server'] = $result['server'];
				$saveParams['photos_list'] = $result['photos_list'];
				$saveParams['hash'] = $result['hash'];
				if($group_id != -1) $saveParams['group_id'] = $group_id;

				return call('photos.save', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload files to wall
	 * @param string $file Path to file
	 * @param int $group_id group_id
	 * @param array $serverParams photos.getWallUploadServer parameters
	 * @param array $saveParams photos.saveWallPhoto parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function wall(string $file, int $group_id = -1, array $serverParams = [], array $saveParams = []) {
		if($group_id != -1) $serverParams['group_id'] = $group_id;

		$server = call('photos.getWallUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'photo');
			if(isset($result['server'])) {
				$saveParams['server'] = $result['server'];
				$saveParams['photo'] = $result['photo'];
				$saveParams['hash'] = $result['hash'];
				if($group_id != -1) $saveParams['group_id'] = $group_id;

				return call('photos.saveWallPhoto', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload main photo
	 * @param string $file Path to file
	 * @param int $owner_id owner_id (by default is api_id from config)
	 * @param array $serverParams photos.getOwnerPhotoUploadServer parameters
	 * @param array $uploadFields Upload parameters
	 * @param array $saveParams photos.saveOwnerPhoto parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function main(string $file, int $owner_id = -1, array $serverParams = [], array $uploadFields = [], array $saveParams = []) {
		global $config;

		if($owner_id == -1) $serverParams['owner_id'] = $config['type'] === 'user' ? $config['api_id'] : $config['api_id'] * -1;
		else $serverParams['owner_id'] = $owner_id;

		$server = call('photos.getOwnerPhotoUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'photo', $uploadFields);
			if(isset($result['server'])) {
				$saveParams['server'] = $result['server'];
				$saveParams['hash'] = $result['hash'];
				$saveParams['photo'] = $result['photo'];

				return call('photos.saveOwnerPhoto', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload files to messages
	 * @param string $file Path to file
	 * @param int $peer_id peer_id
	 * @param array $serverParams photos.getMessagesUploadServer parameters
	 * @param array $saveParams photos.saveMessagesPhoto parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function message(string $file, int $peer_id = -1, array $serverParams = [], array $saveParams = []) {
		if($peer_id != -1) $serverParams['peer_id'] = $peer_id;

		$server = call('photos.getMessagesUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'photo');
			if(isset($result['server'])) {
				$saveParams['server'] = $result['server'];
				$saveParams['hash'] = $result['hash'];
				$saveParams['photo'] = $result['photo'];

				return call('photos.saveMessagesPhoto', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload chat main photo
	 * @param string $file Path to file
	 * @param int $chat_id chat_id
	 * @param array $serverParams photos.getChatUploadServer parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return string
	 */
	public function chat(string $file, int $chat_id, array $serverParams = []) {
		$serverParams['chat_id'] = $chat_id;

		$server = call('photos.getChatUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'file');
			if(isset($result['response'])) {
				return $result['response'];
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload photo for market item
	 * @param string $file Path to file
	 * @param bool $main_photo https://vk.com/dev/photos.getMarketUploadServer
	 * @param array $serverParams photos.getMarketUploadServer parameters
	 * @param array $saveParams photos.saveMarketPhoto parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function market(string $file, bool $main_photo = false, array $serverParams = [], array $saveParams = []) {
		global $config;

		if($config['type'] === 'community' && !isset($serverParams['group_id'])) $serverParams['group_id'] = $config['api_id'];

		$serverParams['main_photo'] = (int)$main_photo;

		$server = call('photos.getMarketUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'file');
			if(isset($result['server'])) {
				$saveParams['server'] = $result['server'];
				$saveParams['hash'] = $result['hash'];
				$saveParams['photo'] = stripslashes($result['photo']);

				if($main_photo) {
					$saveParams['crop_data'] = $result['crop_data'];
					$saveParams['crop_hash'] = $result['crop_hash'];
				}

				return call('photos.saveMarketPhoto', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload photo for market album
	 * @param string $file Path to file
	 * @param int $group_id Community ID
	 * @param array $serverParams photos.getMarketAlbumUploadServer parameters
	 * @param array $saveParams photos.saveMarketAlbumPhoto parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function marketAlbum(string $file, int $group_id, array $serverParams = [], array $saveParams = []) {
		$serverParams['group_id'] = $group_id;

		$server = call('photos.getMarketAlbumUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'file');
			if(isset($result['server'])) {
				$saveParams['group_id'] = $group_id;
				$saveParams['server'] = $result['server'];
				$saveParams['hash'] = $result['hash'];
				$saveParams['photo'] = $result['photo'];

				return call('photos.saveMarketAlbumPhoto', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload cover
	 * @param string $file Path to file
	 * @param int $group_id Community ID
	 * @param array $serverParams photos.getOwnerCoverPhotoUploadServer parameters
	 * @param array $saveParams photos.saveOwnerCoverPhoto parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function cover(string $file, int $group_id, array $serverParams = [], array $saveParams = []) {
		$serverParams['group_id'] = $group_id;

		$server = call('photos.getOwnerCoverPhotoUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'photo');
			if(isset($result['hash'])) {
				$saveParams['hash'] = $result['hash'];
				$saveParams['photo'] = $result['photo'];

				return call('photos.saveOwnerCoverPhoto', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}
}

/**
 * Audios upload
 * @author slmatthew
 * @pacakge attachs
 */
class AudioUpload extends UploadManager {
	/**
	 * audio.getUploadServer
	 * @param string $file Path to file
	 * @param array $serverParams audio.getUploadServer parameters
	 * @param array $saveParams audio.save parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function default(string $file, array $serverParams = [], array $saveParams = []) {
		$server = call('audio.getUploadServer', $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $files, 'file');
			if(isset($result['server'])) {
				$saveParams['server'] = $result['server'];
				$saveParams['audio'] = $result['audio'];
				$saveParams['hash'] = $result['hash'];

				return call('audio.save', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}
}

/**
 * Videos upload
 * @author slmatthew
 * @package attachs
 */
class VideoUpload extends UploadManager {
	/**
	 * video.save
	 * @param string $file Path to file
	 * @param array $params video.save parameters
	 * @throws ParameterException
	 * @return array
	 */
	public function default(string $file = '', array $params = []) {
		$result = call('video.save', $params);
		if(isset($params['link'])) {
			return $result;
		} elseif($file) {
			$url = $result['response']['upload_url'];
			return $this->upload($url, $file, 'video_file');
		} else throw new ParameterException();
	}
}

/**
 * Documents upload
 * @author slmatthew
 * @package attachs
 */
class DocsUpload extends UploadManager {
	/**
	 * @param string $file Path to file
	 * @param string $serverMethod getUploadServer method
	 * @param array $serverParams
	 * @param array $saveParams
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	protected function template(string $file, string $serverMethod, array $serverParams = [], array $saveParams = []) {
		$server = call($serverMethod, $serverParams);
		if(isset($server['response'])) {
			$url = $server['response']['upload_url'];
			$result = $this->upload($url, $file, 'file');
			if(isset($result['server'])) {
				$saveParams['file'] = $result['file'];

				return call('docs.save', $saveParams);
			} else throw new UploadException(json_encode($result, JSON_UNESCAPED_UNICODE));
		} else throw new ApiException(json_encode($server, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Upload document to
	 * @param string $file Path to file
	 * @param array $serverParams docs.getUploadServer parameters
	 * @param array $saveParams docs.save parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function default(string $file, array $serverParams = [], array $saveParams = []) {
		return $this->template($file, 'docs.getUploadServer', $serverParams, $saveParams);
	}

	/**
	 * Upload document to
	 * @param string $file Path to file
	 * @param array $serverParams docs.getWallUploadServer parameters
	 * @param array $saveParams docs.save parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function wall(string $file, array $serverParams = [], array $saveParams = []) {
		return $this->template($file, 'docs.getWallUploadServer', $serverParams, $saveParams);
	}

	/**
	 * Upload document to
	 * @param string $file Path to file
	 * @param string $type Document type: doc, graffiti or audio_message
	 * @param array $serverParams docs.getMessagesUploadServer parameters
	 * @param array $saveParams docs.save parameters
	 * @throws UploadException
	 * @throws ApiException
	 * @return array
	 */
	public function messages(string $file, string $type = 'doc', array $serverParams = [], array $saveParams = []) {
		$serverParams['type'] = $type;
		return $this->template($file, 'docs.getMessagesUploadServer', $serverParams, $saveParams);
	}
}

?>