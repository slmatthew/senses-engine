<?php

namespace slmatthew\senses;

class __vkAuthStorage implements IVkAuthStorage {
	private static array $clients = [];
	private static int $currentClient = 0;

	public static function addClient(string $token, int $ownerId, bool $changeClient = true) {
		self::$clients[$ownerId] = [
			'token' => $token,
			'owner_id' => $ownerId
		];

		if($changeClient) self::$currentClient = $ownerId;
	}

	public static function getClient(): ?array {
		if(!isset(self::$clients[self::$currentClient]) || !self::$currentClient) {
			self::$currentClient = 0;

			return null;
		}

		return self::$clients[self::$currentClient];
	}

	public static function getCurrentClient(): int {
		return self::$currentClient;
	}

	public static function getAviableClients(): array {
		return array_keys(self::$clients);
	}

	public static function removeClient(int $ownerId, ?int $changeTo): bool {
		if(isset(self::$clients[$ownerId])) {
			unset(self::$clients[$ownerId]);

			if(isset(self::$clients[$changeTo])) {
				self::$currentClient = $changeTo;
			}

			return true;
		}

		return false;
	}
}

class vk implements IVk {
	public function token(string $token) {
		$ownerId = $this->__getTokenOwnerId($token);
		if(!is_null($ownerId)) {
			__vkAuthStorage::addClient($token, $ownerId);
		} else throw new InvalidAuthDataException('Invalid token');
	}

	public function auth(string $username, string $password, array $params = []): array {
		$params['scope'] = 'all';
		$params['client_id'] = 2274003;
		$params['client_secret'] = 'hHbZxrka2uZ6jB1inYsH';
		$params['username'] = $username;
		$params['password'] = $password;
		$params['2fa_supported'] = 1;
		$params['grant_type'] = 'password';
		$params['lang'] = 'ru';
		$params['v'] = '5.130';

		$url = 'https://oauth.vk.com/token?'.http_build_query($params);

		$result = Request::make($url);
		if(!is_null($result)) {
			if(isset($result['access_token'])) {
				__vkAuthStorage::addClient($result['access_token'], $result['user_id']);
			} elseif(isset($result['error'])) {
				if($result['error'] == 'need_validation') {
					return [
						'success' => false,
						'error' => '2fa',
						'resend' => function(string $code) use($username, $password, $params) {
							$params['code'] = $code;
							return $this->auth($username, $password, $params);
						}
					];
				} elseif($result['error'] == 'need_captcha') {
					return [
						'success' => false,
						'error' => 'captcha',
						'captcha_url' => "https://api.vk.com/captcha.php?sid={$result['captcha_sid']}&s=1",
						'resend' => function(string $captcha) use($result, $username, $password, $params) {
							$params['captcha_sid'] = $result['captcha_sid'];
							$params['captcha_key'] = $captcha;

							return $this->auth($username, $password, $params);
						}
					];
				} else throw new UnableToAuthException($result);
			} else throw new UnableToAuthException($result);
		} else throw new LostConnectionException();
	}

	private function __getTokenOwnerId(string $token): ?int {
		$maybeUser = Request::api('users.get', ['access_token' => $token]);
		if(isset($maybeUser['response']) && !empty($maybeUser['response'])) {
			return $maybeUser['response'][0]['id'];
		}

		$maybeGroup = Request::api('groups.getById', ['access_token' => $token]);
		if(isset($maybeGroup['response']) && !empty($maybeGroup['response'])) {
			return -$maybeGroup['response'][0]['id'];
		}

		return null;
	}
}

?>