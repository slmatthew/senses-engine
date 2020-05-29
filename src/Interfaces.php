<?php

namespace slmatthew\senses;

interface IRequests {
	public static function make(string $url, array $fields = [], string $agent = 'Senses Bot Engine/1.0'): ?array;
}

?>