<?php

/**
 * User LP events decoder
 * @author slmatthew
 * @package botengine
 */

class LpDecoder {
	/**
	 * @ignore
	 */
	private $id = 0;

	/**
	 * @ignore
	 */
	private $event = [];

	/**
	 * @ignore
	 */
	private $success = false;

	/**
	 * Constructor
	 * @param array $event Event from longpoll
	 * @return object|bool
	 * @since v0.6
	 */
	public function __construct(array $event) {
		if(isset($event[0])) {
			$this->id = (int)$event[0];
			$this->event = $event;
			$this->success = true;
		}
	}

	/**
	 * Event decoder
	 * @return array|bool
	 * @since v0.6
	 */
	public function decode() {
		if($this->success) {
			$e = $this->event;

			$data = [
				'decoded' => true,
				'event_id' => $e[0]
			];

			switch($this->id) {
				case 2:
					$data = $data + [
						'peer_id' => $e[3],
						'msg_id' => $e[1],
						'flags' => $e[2]
					];
					break;

				case 4: case 5: case 18:
					$data = $data + [
						'msg_id' => $e[1],
						'flags' => $e[2],
						'peer_id' => $e[3],
						'timestamp' => $e[4],
						'text' => $e[5],
						'random_id' => $e[8],
						'conversation_msg_id' => $e[9],
						'edit_time' => $e[10]
					];

					if(isset($e[6]['source_act'])) {
						$data['action']['type'] = $e[6]['source_act'];
						switch($e[6]['source_act']) {
							case 'chat_create':
								$data['action']['text'] = $e[6]['source_text'];
								break;

							case 'chat_title_update':
								$data['action']['old_text'] = $e[6]['source_old_text'];
								$data['action']['text'] = $e[6]['source_text'];
								break;

							case 'chat_pin_message':
								$data['action']['from_id'] = $e[6]['source_mid'];
								$data['action']['message'] = $e[6]['source_message'];
								$data['action']['conversation_msg_id'] = $e[6]['source_chat_local_id'];
								break;

							case 'chat_unpin_message':
								$data['action']['from_id'] = $e[6]['source_mid'];
								$data['action']['conversation_msg_id'] = $e[6]['source_chat_local_id'];
								break;

							case 'chat_invite_user': case 'chat_kick_user':
								$data['action']['from_id'] = $e[6]['source_mid'];
								break;

							default: break;
						}

						foreach($e[6] as $key => $val) {
							if(mb_substr($key, 0, 6) == 'source') unset($e[6][$key]);
						}
					}

					$data = $data + $e[6];
					$data['attachs'] = $e[7];

					return $data;
					break;

				case 6: case 7:
					$data = $data + [
						'peer_id' => $e[1],
						'msg_id' => $e[2],
						'count' => $e[3]
					];
					break;

				case 8:
					$data = $data + [
						'user_id' => -1 * $e[1],
						'platform' => $e[2],
						'timestamp' => $e[3],
						'app_id' => $e[4]
					];
					break;

				case 9:
					$data = $data + [
						'user_id' => -1 * $e[1],
						'isTimeout' => $e[2],
						'timestamp' => $e[3],
						'app_id' => $e[4]
					];
					break;

				case 10: case 12:
					$data = $data + [
						'peer_id' => $e[1],
						'flags' => $e[2]
					];
					break;

				case 13:
					$data = $data + [
						'peer_id' => $e[1],
						'last_msg_id' => $e[2]
					];
					break;

				case 19:
					$data = $data + [
						'msg_id' => $e[1]
					];
					break;

				case 51:
					$data = $data + [
						'chat_id' => $e[1]
					];
					break;

				case 52:
					$data = $data + [
						'peer_id' => $e[2],
						'type' => $e[1],
						'extra' => $e[3]
					];
					break;

				case 63: case 64:
					$data = $data + [
						'peer_id' => $e[1],
						'from_ids' => $e[2],
						'from_ids_count' => $e[3],
						'timestamp' => $e[4]
					];
					break;

				case 80:
					$data = $data + [
						'count' => $e[1],
						'count_with_notifications' => $e[2]
					];
					break;

				case 81:
					$data = $data + [
						'user_id' => -1 * $e[1],
						'state' => $e[2],
						'timestamp' => $e[3]
					];
					break;

				case 114:
					$data = $data + $e[1];
					break;

				default: return ['decoded' => false, 'event' => $e]; break;
			}

			return $data;
		}

		return false;
	}

	/**
	 * Attachments parser
	 * @return array
	 * @since v0.6
	 */
	public function getAttachments() {
		if(!$this->success) return [];

		$data = $this->event;
		$attachs = [];

		if(isset($data['geo'])) {
			$attachs[] = [
				'type' => 'geo'
			];
		}

		foreach($data as $key) {
			if(preg_match('/attach(\d+)$/', $key, $match)) {
				$id = $match[1];
				$kind = isset($data["attach{$id}_kind"]) ? $data["attach{$id}_kind"] : '';
				$type = $data["attach{$id}_type"];

				if($kind == 'audiomsg') $type = 'audio_message';
				if($kind == 'graffiti') $type = 'graffiti';
				if($type == 'group') $type = 'event';

				$attachs[] = [
					'type' => $type
				];
			}
		}

		return $attachs;
	}
}

?>