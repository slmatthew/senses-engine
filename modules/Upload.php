<?php

/**
 * Upload files
 * @author slmatthew
 * @package attachments
 */

/**
 * @ignore
 */
class UploadHeart {

	private $files = [];
	private $url = '';

	public function __construct($files, string $url) {
		if(gettype($files) == 'array') {
			if(count($files) > 5) return false;

			$this->files = $files;
			$this->url = $url;

			return $this;
		} elseif(gettype($files) == 'string') {
			$this->files[] = $files;
			$this->url = $url;

			return $this;
		}

		return false;
	}

	public function upload(int $count, string $name) {
		if(empty($this->files) || count($this->files) > 5 || strlen($this->url) == 0) return false;

		if($count == 1) {
			$fields[$name] = $this->files[0];
		} else {
			$fields = [];
			$i = 0;
			while(isset($this->files[$i])) {
				$fields["{$name}{$i}"] = new CURLfile($this->files[$i]);
				$i++;
			}
		}

		$ch = curl_init($this->url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, CURL_VERIFY);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Senses Bot Engine/'.SEV);

		$json = curl_exec($ch);
		curl_close($ch);

		return json_decode($json, true);
	}
}

class Upload {
	public function photoAlbum($files, array $uploadParams, array $saveParams) {
		global $config;

		$r = call('photos.getUploadServer', $params);
		if(isset($r['response'])) {
			$u = new UploadHeart($files, $r['response']['upload_url'])->upload(count($files), 'file');
			if(isset($u['server'])) {
				$saveParams['server'] = $u['server'];
				$saveParams['photos_list'] = $u['photos_list'];
				$saveParams['hash'] = $u['hash'];

				return call('photos.save', $saveParams);
			}
		}

		return false;
	}
}

?>