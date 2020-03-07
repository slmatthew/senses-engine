<?php

/**
 * Template constructor (template param in messages.send)
 * @author slmatthew
 * @package keyboard
 */

class Template {

	/**
	 * @ignore
	 */
	private string $type = 'carousel';

	/**
	 * @ignore
	 */
	private array $elements = [];

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
	 * @param array $buttons Buttons array. Can be obtain via TemplateButtons
	 * @param string $title Card title
	 * @param string $description Card description
	 * @param string $photo_id Photo ID
	 * @param array $action Docs: https://vk.cc/a8hzdu
	 * @return void
	 */
	public function addCarouselElement(array $buttons, string $title = '', string $description = '', string $photo_id = '', array $action = []): Template {
		if($this->type !== 'carousel') throw new ParameterException();

		if(empty($buttons) || (!$photo_id && !$title) || ($title && !$description)) throw new ParameterException();

		$element = [];

		if($title) $element['title'] = $title;
		if($description) $element['description'] = $description;
		if($photo_id) $element['photo_id'] = $photo_id;
		if($buttons) $element['buttons'] = $buttons;
		if($action) $element['action'] = $action;

		$this->elements[] = $element;

		return $this;
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

		return $json ? $this->toJson($template) : $template;
	}

	/**
	 * @ignore
	 */
	private function toJson(array $array): string { return json_encode($array, JSON_UNESCAPED_UNICODE); }
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
	public array $buttons = [];

	public const PRIMARY_BUTTON = 'primary';
	public const SECONDARY_BUTTON = 'secondary';
	public const NEGATIVE_BUTTON = 'negative';
	public const POSITIVE_BUTTON = 'positive';

	/**
	 * Button constructor
	 * @param array $action https://vk.com/dev/bots_docs_3
	 * @param string $color Button color
	 * @return TemplateButtons
	 * @since v0.6
	 */
	public function addButton(array $action, string $color = ''): TemplateButtons {
		if(isset($action['payload'])) $action['payload'] = json_encode($action['payload'], JSON_UNESCAPED_UNICODE);

		if(isset($action['type']) && $action['type'] === 'text') {
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
	public function addTextButton(string $label, array $payload = [], string $color = self::PRIMARY_BUTTON): TemplateButtons {
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
	public function addLocationButton(array $payload = []): TemplateButtons {
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
	public function addPayButton(array $hash, array $payload = []): TemplateButtons {
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
	public function addAppButton(int $app_id, int $owner_id, string $label, string $hash = '', array $payload = []): TemplateButtons {
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
	public function get(): array { return $this->buttons; }

	/**
	 * @ignore
	 */
	private function toJson(array $array): string { return json_encode($array, JSON_UNESCAPED_UNICODE); }
}

?>