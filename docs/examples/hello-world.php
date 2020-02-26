<?php

include './loader.php';

$vk = new vk('lp');

$vk->bot->onCommands(['hello'], function($data, $msg) {
	$msg->send("World!");
});

$vk->listen();

?>