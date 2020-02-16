<?php

namespace Senses;

/**
 * Template constructor (template param in messages.send)
 * @author slmatthew
 * @package keyboard
 */

class Template {

	/**
	 * @ignore
	 */
	private $type = 'carousel';

	/**
	 * @ignore
	 */
	private $elements = [];

	/**
	 * Template constructor
	 * @param string $type Template type
	 * @return void
	 * @since v0.6
	 */
	public function __construct(string $type = 'carousel') {
		$this->type = $type;
	}

	/**
	 * Add carousel element to template
	 * @param string $title Card title
	 * @param string $description Card description
	 * @param string $photo_id Photo ID
	 * @param array $buttons Buttons array. Can be obtain via TemplateButtons
	 * @param array $action Docs: https://vk.cc/a8hzdu
	 * @return void
	 * @since v0.6
	 */
	public function addCarouselElement(string $title, string $description, string $photo_id, array $buttons, array $action) {
		if(empty($buttons) || (strlen($photo_id) == 0 && strlen($title) == 0) || (strlen($title) > 0 && strlen($description) == 0)) return false;

		$element = [];

		if(isset($title) && strlen($title) > 0) $element['title'] = $title;
		if(isset($description) && strlen($description) > 0) $element['description'] = $description;
		if(isset($photo_id) && strlen($photo_id) > 0) $element['photo_id'] = $photo_id;
		if(isset($buttons) && !empty($buttons)) $element['buttons'] = $buttons;
		if(isset($action) && !empty($action)) $element['action'] = $action;

		$this->elements[] = $element;

		echo json_encode($this->elements, JSON_UNESCAPED_UNICODE)."\n";
		echo json_encode($element, JSON_UNESCAPED_UNICODE)."\n";
	}

	/**
	 * Get template
	 * @param bool $json Should function return array or json
	 * @return array|string
	 * @since v0.6
	 */
	public function get(bool $json = true) {
		$template = [
			'type' => $this->type,
			'elements' => $this->elements
		];

		echo json_encode($template, JSON_UNESCAPED_UNICODE);
		echo "\n";

		return $json ? $this->toJson($template) : $template;
	}

	/**
	 * @ignore
	 */
	private function toJson(array $array) { return json_encode($array, JSON_UNESCAPED_UNICODE); }
}

/**
 * Buttons constructor for templates
 * @author slmatthew
 * @package keyboard
 */

class TemplateButtons {
	
	/**
	 * @ignore
	 */
	public $buttons = [];

	public const PRIMARY_BUTTON = 'primary';
	public const SECONDARY_BUTTON = 'secondary';
	public const NEGATIVE_BUTTON = 'negative';
	public const POSITIVE_BUTTON = 'positive';

	/**
	 * TemplateButtons constructor
	 * @return TemplateButtons
	 * @since v0.6
	 */
	public function __construct() {
		return $this;
	}

	/**
	 * Button constructor
	 * @param array $action https://vk.com/dev/bots_docs_3
	 * @param string $color Button color
	 * @return TemplateButtons
	 * @since v0.6
	 */
	public function addButton(array $action, string $color = '') {
		if(isset($action['payload'])) $action['payload'] = json_encode($action['payload'], JSON_UNESCAPED_UNICODE);

		if(isset($action['type']) && $action['type'] == 'text') {
			$this->buttons[] = [
				'action' => $action,
				'color' => $color
			];
		} else {
			$this->buttons[] = [
				'action' => $action
			];
		}

		return $this;
	}

	/**
	 * Text button constructor
	 * @param string $label Text on the button
	 * @param array $payload Payload
	 * @param string $color Button color
	 * @return TemplateButtons
	 * @since 0.6
	 */
	public function addTextButton(string $label, array $payload = [], string $color = self::PRIMARY_BUTTON) {
		$this->addButton([
			'type' => 'text',
			'label' => $label,
			'payload' => $this->toJson($payload)
		], $color);

		return $this;
	}

	/**
	 * Location button constructor
	 * @param array $payload Payload
	 * @return TemplateButtons
	 * @since 0.6
	 */
	public function addLocationButton(array $payload = []) {
		$this->addButton([
			'type' => 'location',
			'payload' => $this->toJson($payload)
		]);

		return $this;
	}

	/**
	 * VK Pay button constructor
	 * @param array $hash Hash
	 * @param array $payload Payload
	 * @return TemplateButtons
	 * @since 0.6
	 */
	public function addPayButton(array $hash, array $payload = []) {
		$this->addButton([
			'type' => 'vkpay',
			'hash' => http_build_query($hash),
			'payload' => $this->toJson($payload)
		]);

		return $this;
	}

	/**
	 * Text button constructor
	 * @param int $app_id Your application ID
	 * @param int $owner_id Community or user id (for user ids owner_id < 0)
	 * @param string $label Text on the button
	 * @param string $hash Hash: https://vk.com/app123#{hash}
	 * @param array $payload
	 * @return TemplateButtons
	 * @since 0.6
	 */
	public function addAppButton(int $app_id, int $owner_id, string $label, string $hash = '', array $payload = []) {
		$this->addButton([
			'type' => 'open_app',
			'app_id' => $app_id,
			'owner_id' => $owner_id,
			'label' => $label,
			'hash' => $hash,
			'payload' => $this->toJson($payload)
		]);

		return $this;
	}

	/**
	 * Buttons getter
	 * @return array
	 * @since v0.6
	 */
	public function get() { return $this->buttons; }

	/**
	 * @ignore
	 */
	private function toJson(array $array) { return json_encode($array, JSON_UNESCAPED_UNICODE); }
}

?>