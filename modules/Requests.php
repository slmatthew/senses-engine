<?php

if(is_null($config) || empty($config) || !isset($config))  throw new Exception('You need to set config');

function request($url, $postfields = [], $agent = 'Senses Bot Engine/0.1') {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // only for dev mode
	if(!empty($postfields)) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
	}
	$json = curl_exec($ch);
	curl_close($ch);
	return json_decode($json, true);
}

function call($m, $p = [], $o = false) {
	global $config;

	if(!isset($p['access_token'])) $p['access_token'] = isset($config['token']) ? $config['token'] : '';
	if(!isset($p['v'])) $p['v'] = isset($config['version']) ? $config['version'] : '5.103';
	if(isset($p['unsetToken']) && $p['unsetToken']) unset($p['access_token']);

	$agent = $o ? "VKAndroidApp/5.11.1-2316" : "Senses Bot Engine/0.1";

	return request("https://api.vk.com/method/{$m}", $p, $agent);
}

?>