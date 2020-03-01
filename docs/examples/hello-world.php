<?php

/**
 * Простой бот на LongPoll API,
 * (как пользовательский, так и для ботов)
 * отправляющий в ответ на `hello`
 * сообщение `World!`
 */

include './loader.php';

$vk = new vk('lp');

$vk->bot->onCommands(['hello'], function($data, $msg) {
	$msg->send("World!");
});

$vk->listen();

?>