<?php

class Keyboard {
	public const PRIMARY_BUTTON = 'primary';
	public const SECONDARY_BUTTON = 'secondary';
	public const NEGATIVE_BUTTON = 'negative';
	public const POSITIVE_BUTTON = 'positive';

	/**
	 * Button constructor
	 * @param array $action https://vk.com/dev/bots_docs_3
	 * @param string $color Button color
	 * @return Keyboard
	 * @since v0.3
	 */
	static function button(array $action, string $color = '') {
		if(isset($action['payload'])) $action['payload'] = json_encode($action['payload'], JSON_UNESCAPED_UNICODE);

		if(isset($action['type']) && $action['type'] === 'text') {
			return [
				'action' => $action,
				'color' => $color
			];
		} else {
			return [
				'action' => $action
			];
		}
	}

	/**
	 * Text button constructor
	 * @param string $label Text on the button
	 * @param array $payload Payload
	 * @param string $color Button color
	 * @return Keyboard
	 * @since 0.6
	 */
	static function textButton(string $label, array $payload = [], string $color = Keyboard::PRIMARY_BUTTON) {
		return Keyboard::button([
			'type' => 'text',
			'label' => $label,
			'payload' => Keyboard::toJson($payload)
		], $color);
	}

	/**
	 * Location button constructor
	 * @param array $payload Payload
	 * @return Keyboard
	 * @since 0.6
	 */
	static function locationButton(array $payload = []) {
		return Keyboard::button([
			'type' => 'location',
			'payload' => Keyboard::toJson($payload)
		]);
	}

	/**
	 * VK Pay button constructor
	 * @param array $hash Hash
	 * @param array $payload Payload
	 * @return Keyboard
	 * @since 0.6
	 */
	static function payButton(array $hash, array $payload = []) {
		return Keyboard::button([
			'type' => 'vkpay',
			'hash' => http_build_query($hash),
			'payload' => Keyboard::toJson($payload)
		]);
	}

	/**
	 * Text button constructor
	 * @param int $app_id Your application ID
	 * @param int $owner_id Community or user id (for user ids owner_id < 0)
	 * @param string $label Text on the button
	 * @param string $hash Hash: https://vk.com/app123#{hash}
	 * @param array $payload
	 * @return Keyboard
	 * @since 0.6
	 */
	static function appButton(int $app_id, int $owner_id, string $label, string $hash = '', array $payload = []) {
		return Keyboard::button([
			'type' => 'open_app',
			'app_id' => $app_id,
			'owner_id' => $owner_id,
			'label' => $label,
			'hash' => $hash,
			'payload' => Keyboard::toJson($payload)
		]);
	}

	static function toJson(array $array) { return json_encode($array, JSON_UNESCAPED_UNICODE); }
}

?>