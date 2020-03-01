<?php

/**
 * Код бота, работающего на Calback API и
 * удаляющего все комментарии,
 * в тексте которых меньше слов.
 * Для подсчета количества слов используется
 * функция explode.
 */

include './loader.php';

$vk = new vk('cb');
$vk->setConfirmation('1234abcd');

$vk->bot->on('wall_reply_new', function($data) {
	$text = $data['object']['text'];
	if(count(explode(' ', $text)) < 5) { // Если в тексте комментария меньше 5 слов, удаляем его
		call('wall.deleteComment', [
			'owner_id' => $data['object']['post_owner_id'],
			'comment_id' => $data['object']['id'],
			'access_token' => 'USER_TOKEN' // wall.deleteComment доступен только с токеном пользователя
		]);
		call('messages.send', [
			'peer_id' => 305360617,
			'random_id' => 0,
			'message' => "Удалён комментарий от @id{$data['object']['from_id']}"
		]); // Отчитываемся администратору о том, что удалили комментарий
	}
});

$vk->bot->hear(['default'], function($data, $msg) {
	$msg->reply('У меня есть только одна команда.');
});

$vk->bot->hear(['!помощь', '/помощь'], function($data, $msg) {
	$msg->reply('Я умею только одно — удалять короткие комментарии.');
});

$vk->listen();

?>