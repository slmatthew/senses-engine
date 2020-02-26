<?php

/**
 * @package requests
 */

if(!isset($config) || is_null($config) || empty($config))  throw new ConfigException('You need to set config');
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
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, CURL_VERIFY);
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
	global $config;

	if(!isset($params['access_token'])) $params['access_token'] = isset($config['token']) ? $config['token'] : '';
	if(!isset($params['v'])) $params['v'] = isset($config['version']) ? $config['version'] : '5.118';
	if(isset($params['unsetToken']) && $params['unsetToken']) unset($params['access_token']);

	$agent = $official ? "VKAndroidApp/5.50-4431 (1; 1; 1; 1; 1; 1)" : "Senses Bot Engine/".SEV;

	return request("https://api.vk.com/method/{$method}", $params, $agent);
}

?>