<?php

/**
 * Upload files
 * @author slmatthew
 * @package attachments
 */

class Attachments {

	protected $attach = [
		'type' => false
	];

	public function __construct($attach = []) {
		return $this->create($attach);
	}

	public function create($attach) {
		if(gettype($attach) == 'array') {
			if(isset($attach['type']) && isset($attach['owner_id']) && isset($attach['id'])) {
				$this->attach = [
					'type' => $attach['type'],
					'owner_id' => $attach['owner_id'],
					'id' => $attach['id'],
					'access_key' => isset($attach['access_key']) ? $attach['access_key'] : ''
				];

				return true;
			} else throw new ParameterException('Invalid $attach value');
		} elseif(gettype($attach) == 'string') {
			$m = [];
			
			if(preg_match('/(photo|video|audio|doc|audio_message|graffiti|wall|market|poll|gift)([-\d]+)_(\d+)_?(\w+)?/u', $attach, $m) && !empty($m)) {
				$this->attach = [
					'type' => $m[1],
					'owner_id' => (int)$m[2],
					'id' => (int)$m[3],
					'access_key' => isset($attach[4]) ? $attach[4] : ''
				];

				return true;
			} else throw new ParameterException('Invalid $attach value');
		}

		return false;
	}

	public function get() { return $this->attach['type'] === false ? [] : $this->attach; }

	public function getString() {
		if($this->attach['type'] === false) throw new EmptyAttachException('No attachment provided');

		$a = [
			$this->attach['type'],
			$this->attach['owner_id'],
			$this->attach['id']
		];

		if(isset($this->attach['access_key'])) $a[] = $this->attach['access_key'];

		return implode('', $a);
	}
}

?>