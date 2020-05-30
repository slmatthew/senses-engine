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

	/**
	 * Call VK API methods
	 * @param string $method Method name
	 * @param array $params Request params
	 * @param bool $android Use android UA
	 * @return array
	 */
	public static function api(string $method, array $params = [], bool $android = false): ?array {
		$defaultParams = [
			'access_token' => !is_null(__vkAuthStorage::getClient()) && isset(__vkAuthStorage::getClient()['token']) ? __vkAuthStorage::getClient()['token'] : '',
			'v' => '5.130'
		];

		if(!isset($params['access_token'])) $params['access_token'] = $defaultParams['access_token'];
		if(!isset($params['v'])) $params['v'] = $defaultParams['v'];
		if(isset($params['unsetToken']) && $params['unsetToken']) unset($params['access_token']);

		$agent = $android ? "VKAndroidApp/5.50-4431 (1; 1; 1; 1; 1; 1)" : "Senses Bot Engine/1.0";

		$result = self::make("https://api.vk.com/method/{$method}", $params, $agent);

		return $result;
	}
}

?>