<?php

/**
 * @package requests
 */

if(!defined("SEV")) define("SEV", "unknown");

/**
 * Function for API requests. Supports only JSON response
 * @param string $url Request URL
 * @param array $postfields Request params
 * @param string $agent User-Agent header
 * @return array
 * @since v0.1
 */
function request(string $url, array $postfields = [], string $agent = 'Senses Bot Engine/'.SEV) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // temp
	if(!empty($postfields)) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
	}
	$json = curl_exec($ch);
	curl_close($ch);
	return json_decode($json, true);
}

/**
 * Call VK API methods
 * @param string $method Method name
 * @param array $params Request params
 * @param bool $official Made request as official VK App
 * @return array
 * @since v0.1
 */
function call(string $method, array $params = [], bool $official = false) {
	$defaultParams = [
		'access_token' => isset(vkAuthStorage::get()['token']) ? vkAuthStorage::get()['token'] : '',
		'v' => isset(vkAuthStorage::get()['version']) ? vkAuthStorage::get()['version'] : '5.131'
	];

	if(!isset($params['access_token'])) $params['access_token'] = $defaultParams['access_token'];
	if(!isset($params['v'])) $params['v'] = $defaultParams['v'];
	if(isset($params['unsetToken']) && $params['unsetToken']) unset($params['access_token']);

	$agent = $official ? "VKAndroidApp/5.55-4758 (Android 9; SDK 28; arm64-v8a; SM-G960F; ru; 1920x1080)" : "Senses Bot Engine/".SEV;

	$result = request("https://api.vk.com/method/{$method}", $params, $agent);
	sensesDebugger::event(DebuggerEvents::API_CALL, [
		'method' => $method,
		'params' => $params,
		'agent' => $agent
	]);

	if(vkAuthStorage::getErrorsPeer() != 0 && isset($result['error'])) {
		$code = $result['error']['error_code'];
		$msg = $result['error']['error_msg'];
		$req_params = [];

		foreach($result['error']['request_params'] as $_ => $reqp) {
			if($reqp['key'] === 'oauth') continue;

			$req_params[] = "{$reqp['key']} = {$reqp['value']}";
		}

		$req_params[] = "\nUser-Agent: {$agent}";

		$message = "VK API Error #{$code}: {$msg}";
		if(!empty($req_params)) {
			$message .= "\n\n".implode("\n", $req_params);
		}

		$sendParams = $defaultParams + [
			'peer_id' => vkAuthStorage::getErrorsPeer(),
			'random_id' => 0,
			'message' => $message
		];

		request("https://api.vk.com/method/messages.send", $sendParams, $agent);
		sensesDebugger::event(DebuggerEvents::API_ERROR, [
			'on' => [
				'method' => $method,
				'params' => $params,
				'agent' => $agent
			],
			'error' => [
				'code' => $code,
				'msg' => $msg,
				'other' => $result['error']
			]
		]);
	}

	sensesDebugger::event(DebuggerEvents::API_RESULT, [
		'method' => $method,
		'params' => http_build_query($params),
		'result' => $result
	]);

	return $result;
}

?>