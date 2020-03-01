<?php

class MemoryStorage {
	private static $data = [];

	public static function has(string $key) {
		return isset(self::$data[$key]);
	}

	public static function set(string $key, string $value) {
		self::$data[$key] = $value;
	}

	public static function get(string $key) {
		if(self::has($key)) {
			return self::$data[$key];
		}

		return false;
	}

	public function remove(string $key) {
		if(self::has($key)) {
			unset(self::$data[$key]);
			return true;
		}

		return false;
	}
}

class MemoryStream {
	private $pos;
	private $path;

	private function setStreamData(string $data) {
		return MemoryStorage::set("sensesmemory_{$this->path}", $data);
	}

	private function getStreamData() {
		return MemoryStorage::get("sensesmemory_{$this->path}");
	}

	private function removeStreamData() {
		return MemoryStorage::delete("sensesmemory_{$this->path}");
	}

	public function stream_open(string $path, string $mode, $options, &$opened_path) {
		$this->path = $path;
		if(stripos($mode, 'w') !== false) {
			$this->setStreamData('');
			$this->pos = 0;
		} else {
			$this->pos = 0;
		}

		return true;
	}

	public function stream_read(int $count) {
		$string = mb_substr($this->getStreamData(), $this->pos, $count);
		$this->pos += strlen($string);

		return $string;
	}

	public function stream_write(string $data) {
		$this->setStreamData(
			mb_substr($this->getStreamData(), 0, $this->pos).
			$data.
			mb_substr($this->getStreamData(), $this->pos + strlen($data))
		);
		$this->pos += strlen($data);

		return strlen($data);
	}

	public function stream_tell() {
		return $this->pos;
	}

	public function stream_eof() {
		return $this->pos >= strlen($this->getStreamData());
	}

	public function stream_seek(int $offset, int $whence) {
		switch($whence) {
			case SEEK_SET:
				if(strlen($this->getStreamData()) > $offset && $offset >= 0) {
					$this->pos = $offset;
					return true;
				}

				break;

			case SEEK_CUR:
				if($offset >= 0) {
					$this->pos += $offset;
					return true;
				}

				break;

			case SEEK_END:
				if(strlen($this->getStreamData()) + $offset >= 0) {
					$this->pos = strlen($this->getStreamData()) + $offset;
					return true;
				}

				break;

			default:
				return false;
		}

		return false;
	}

	public function stream_stat() { }

	public function url_stat($path) {
		return [
			'size' => strlen($this->getStreamData())
		];
	}

	public function unlink(string $path) {
		$this->path = $path;
		$this->removeStreamData();
	}
}

stream_wrapper_register('senses', 'MemoryStream');

?>