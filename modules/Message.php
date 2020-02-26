<?php

/**
 * @ignore
 */
class executeGetProfile {
	/**
	 * @ignore
	 */
	private $result = [];

	/**
	 * @ignore
	 */
	public function __construct($profile, string $name_case = 'nom') {
		$code = 'var profile = "'.$profile.'";
var name_case = "'.$name_case.'";

var user = API.users.get({"user_ids": profile,"fields": "screen_name","lang": "ru","v": "5.118"});

if(user.length > 0) {
	user = user[0];

	var mainUser = {"id": user.id,"screen_name": user.screen_name,"fn": user.first_name,"ln": user.last_name,"full": user.first_name + " " + user.last_name,"full_push": "@id" + user.id + " (" + user.first_name + " " + user.last_name + ")","short_push": "@id" + user.id + " (" + user.first_name + ")","last_push": "@id" + user.id + " (" + user.last_name + ")","is_community": false,"success": true};
	
	return mainUser;
} else {
	if(parseInt(profile) != 0) {
		profile = profile * -1;
	}
	
	var group = API.groups.getById({"group_ids": profile,"fields": "screen_name","lang": "ru","v": "5.118"});
	
	if(group.length > 0) {
		var cases = {"nom": "Сообщество","gen": "Сообщества","dat": "Сообществу","acc": "Сообщество","ins": "Сообществом","abl": "Сообществе"};
		
		if(!cases[name_case]) {
			name_case = "nom";
		}
		
		group = group[0];

		var mainUser = {"id":group.id,"screen_name":group.screen_name,"fn":cases.nom,"ln":group.name,"full":cases[name_case]+" «"+group.name+"»","full_push":cases[name_case]+" «@club"+group.id+" ("+group.name+")»","short_push":cases[name_case]+" «@club"+group.id+" ("+group.name+")»","last_push":"«@club"+group.id+"("+group.name+")","is_community":true,"success":true};
		return mainUser;
	}
}
return {"success": false};';

		$this->result = call('execute', ['code' => $code]);
	}

	/**
	 * @ignore
	 */
	public function getResult() { return $this->result; }
}

/**
 * Class for working with message
 * @author slmatthew
 * @package message
 */

class Message {
	/**
	 * @ignore
	 */
	private $message = null;

	/**
	 * @param array $message Message object (from cb or bots lp)
	 * @param array $is_community Object from CB/Bots LP or from User LP
	 */
	public function __construct(array $message, bool $is_community = true) {
		if($is_community) {
			$this->message = $message['object']['message'];

			// Attachments handler
			$this->message['attachments_types'] = [];
			foreach($this->message['attachments'] as $_ => $val) {
				$this->message['attachments_types'][] = $val['type'];
			}

			$this->message['data_source'] = 'comminuty';
		} else {
			$message = new LpDecoder($message);
			$this->message = $message->decode();
			$this->message['id'] = $this->message['msg_id'];

			if($this->message['peer_id'] > 2e9) {
				$this->message['from_id'] = $this->message['from'];
			} else {
				$this->message['from_id'] = $this->message['peer_id'];
			}

			// Attachments handler
			$this->message['attachments_types'] = [];
			foreach($this->message['attachs'] as $key => $_) {
				if(preg_match('/attach(\d+)$/', $key, $match)) {
					$id = $match[1];
					$type = $this->message['attachs']["attach{$id}_type"];

					$this->message['attachments_types'][] = $type;
				}
			}

			$this->message['data_source'] = 'user';
		}
	}

	/**
	 * Get message sender id
	 * @return int
	 */
	public function from(): int { return $this->message['from_id']; }

	/**
	 * Is current message from chat?
	 * @return bool
	 */
	public function isChat(): bool { return $this->message['peer_id'] > 2000000000; }

	/**
	 * Is message from current user?
	 * @return bool
	 */
	public function isOut(): bool {
		global $config;

		if($this->message['data_source'] === 'comminuty') {
			return $this->message['from_id'] == $config['api_id'];
		} else {
			return $this->message['flags'] & 2;
		}
	}

	/**
	 * Has current message attachments?
	 * @return bool
	 */
	public function hasAttachs(): bool { return !empty($this->message['attachments_types']); }

	/**
	 * Has current message an id?
	 * @return bool
	 */
	public function hasId(): bool { return $this->message['id'] > 0; }

	/**
	 * Send a message
	 * @param string $text Message text
	 * @param array $params Additional parameters
	 * @param bool $for_execute Return array (for Execute class) or `call` function response
	 * @return array
	 */
	public function send(string $text, array $params = [], bool $for_execute = false): array {
		global $config;

		$params['peer_id'] = $this->message['peer_id'];
		if(!isset($params['random_id'])) $params['random_id'] = 0;

		if($text) {
			$mentions = new executeGetProfile($this->from(), 'nom');
			$mentions = $mentions->getResult();
			if(isset($mentions['response']) && $mentions['response']['success']) {
				/**
				 * Tags list
				 * %fn% - first name
				 * %ln% - last name
				 * %full% - full name
				 * %fnp% - full name push
				 * %fip% - first name push
				 * %lnp% - last name push
				 * %sn% - screen name
				 * %id% - user id
				 */

				$mentions = $mentions['response'];
				$text = str_replace(['%fn%', '%ln%', '%full%', '%fnp%', '%fip%', '%lnp%', '%sn%', '%id%'], [$mentions['fn'], $mentions['ln'], $mentions['full'], $mentions['full_push'], $mentions['short_push'], $mentions['last_push'], $mentions['screen_name'], $mentions['id']], $text);
			}

			$params['message'] = $text;
		}

		return $for_execute ? ['messages.send', $params] : call('messages.send', $params, $config['type'] === 'user');
	}

	/**
	 * Reply to current message
	 * @param string $text Reply text
	 * @param array $params Additional parameters
	 * @param bool $for_execute Return array (for Execute class) or `call` function response
	 * @return array
	 */
	public function reply(string $text, array $params = [], bool $for_execute = false): array {
		if($this->hasId()) $params['reply_to'] = $this->message['id'];
		return $this->send($text, $params, $for_execute);
	}

	/**
	 * messages.removeChatUser
	 * @param int $member_id member_id
	 * @param array $params Additional parameters
	 * @throws MessageApiException
	 * @return array
	 */
	public function kickMember(int $member_id, array $params = []): array {
		if(!$this->isChat()) throw new MessageApiException();

		$params['chat_id'] = $this->message['peer_id'] - 2e9;
		$params['member_id'] = $member_id;

		return call('messages.removeChatUser', $params);
	}

	/**
	 * messages.pin for current message
	 * @param array $params Additional parameters
	 * @throws MessageApiException
	 * @return array
	 */
	public function pin(array $params = []): array {
		if(!$this->hasId()) throw new MessageApiException();

		$params['peer_id'] = $this->message['peer_id'];
		$params['message_id'] = $this->message['id'];

		return call('messages.pin', $params);
	}

	/**
	 * messages.unpin
	 * @param array $params Additional parameters
	 * @return array
	 */
	public function unpin(array $params = []): array {
		$params['peer_id'] = $this->message['peer_id'];

		return call('messages.unpin', $params);
	}
}

?>