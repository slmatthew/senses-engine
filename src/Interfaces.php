<?php

namespace slmatthew\senses;

interface IRequests {
	public static function make(string $url, array $fields = [], string $agent = 'Senses Bot Engine/1.0'): ?array;
	public static function api(string $method, array $params = [], bool $android = false): ?array;
}

interface IVk {
	public function token(string $token);
	public function auth(string $username, string $password, array $params = []): array;
}

/* internal */
interface IVkAuthStorage {
	public static function addClient(string $token, int $ownerId, bool $changeClient = true);

	public static function getClient(): ?array;
	public static function getCurrentClient(): int;
	public static function getAviableClients(): array;

	public static function removeClient(int $index, ?int $changeTo): bool;
}

?>