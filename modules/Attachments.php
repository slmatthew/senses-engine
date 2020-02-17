<?php

/**
 * Upload files
 * @author slmatthew
 * @package attachments
 */

class Attachments {
	/**
	 * @ignore
	 */
	protected $attach = [
		'type' => false
	];

	/**
	 * Create Attachments class
	 * @param array|string $attach Array with attachment data or string like photo1_1 or photo1_1_ACCESSKEY
	 * @throws ParameterException
	 * @return bool
	 * @since v0.6
	 */
	public function __construct($attach = []) {
		return $this->create($attach);
	}

	/**
	 * This function add attachment info to variable in class
	 * @param array|string $attach Array with attachment data or string like photo1_1 or photo1_1_ACCESSKEY
	 * @throws ParameterException
	 * @return bool
	 * @since v0.6
	 */
	public function create($attach) {
		if(gettype($attach) === 'array') {
			if(isset($attach['type']) && isset($attach['owner_id']) && isset($attach['id'])) {
				$this->attach = [
					'type' => $attach['type'],
					'owner_id' => $attach['owner_id'],
					'id' => $attach['id'],
					'access_key' => isset($attach['access_key']) ? $attach['access_key'] : ''
				];

				return true;
			} else throw new ParameterException('Invalid $attach value');
		} elseif(gettype($attach) === 'string') {
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

	/**
	 * Get attachment info from class
	 * @return array
	 * @since v0.6
	 */
	public function get() { return $this->attach['type'] === false ? [] : $this->attach; }

	/**
	 * Get attachment info in string format
	 * @throws EmptyAttachException
	 * @return string
	 * @since v0.6
	 */
	public function getString() {
		if($this->attach['type'] === false) throw new EmptyAttachException('No attachment provided');

		$a = [
			$this->attach['type'],
			$this->attach['owner_id'],
			"_".$this->attach['id']
		];

		if(isset($this->attach['access_key'])) $a[] = "_".$this->attach['access_key'];

		return implode('', $a);
	}
}

?>