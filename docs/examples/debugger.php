<?php

/**
 * Бот, использующий новый
 * класс Debugger.
 */

include './loader.php';

$vk = new vk([
	'token' => 'x6pstvcdeyp5y8c82gthdgc22h7za5aq5pf6cf7su3yf3ur2eassz8uxuxk6q2aacy5m6e5e3kq5eybw3upsk',
	'type' => 'lp'
]);

$dbg = new Debugger([
	DebuggerEvents::API_CALL,
	DebuggerEvents::LP_FIRST_UPDATES
]);

$dbg->on(DebuggerEvents::API_CALL, function($data) {
	echo "called {$data['method']} method\n";
});
$dbg->on(DebuggerEvents::LP_FIRST_UPDATES, function($data) {
	echo "got first lp updates\n";
});

$vk->bot->hear(['default'], function($data, $msg) {
	$msg->send('Не знаю такой команды');
});

$vk->bot->hear(['hello', 'привет', 'hi', 'здравствуй'], function($data, $msg) {
	$msg->send('Привет!');
});

$vk->listen();

?>