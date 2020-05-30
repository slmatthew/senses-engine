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

?>