<?php

namespace slmatthew\senses;

/**
 * @package requests
 */

class Request implements IRequests {
	/**
	 * Function for API requests. Supports only JSON response
	 * @param string $url Request URL
	 * @param array $fields Request params
	 * @param string $agent User-Agent header
	 * @return array
	 * @since v1.0
	 */
	public static function make(string $url, array $fields = [], string $agent = 'Senses Bot Engine/1.0'): ?array {
		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_USERAGENT => $agent
		]);

		if(!empty($fields)) {
			curl_setopt_array($ch, [
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($fields)
			]);
		}

		if(stripos($agent, 'vkandroidapp') !== false) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'X-VK-Android-Client: new'
			]);
		}

		$json = curl_exec($ch);
		curl_close($ch);

		return @json_decode($json, true);
	}
}

?>