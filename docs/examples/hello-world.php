<?php

/**
 * Простой бот на LongPoll API,
 * (как пользовательский, так и для ботов)
 * отправляющий в ответ на `hello`
 * сообщение `World!`
 */

include './loader.php';

$vk = new vk([
	'token' => 'x6pstvcdeyp5y8c82gthdgc22h7za5aq5pf6cf7su3yf3ur2eassz8uxuxk6q2aacy5m6e5e3kq5eybw3upsk',
	'type' => 'lp'
]);

$vk->bot->onCommands(['hello'], function($data, $msg) {
	$msg->send("World!");
});

$vk->listen();

?>