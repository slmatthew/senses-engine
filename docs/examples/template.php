<?php

/**
 * Пример бота, отправляющего
 * в ответ на сообщение `template`
 * сообщение-шаблон с каруселью.
 */

include './loader.php';

$vk = new vk([
	'token' => 'x6pstvcdeyp5y8c82gthdgc22h7za5aq5pf6cf7su3yf3ur2eassz8uxuxk6q2aacy5m6e5e3kq5eybw3upsk',
	'type' => 'lp'
]);

$vk->bot->hear(['template'], function($data, $msg) {
  $buttons = new TemplateButtons();
  $buttons
    ->addTextButton('First button', ['command' => 'test'])
    ->addTextButton('Second button', ['command' => 'any'], $buttons::SECONDARY_BUTTON);

  $template = new Template();
  $template->addCarouselElement('Carousel', $buttons->get(), 'Description', '', ['type' => 'open_link', 'link' => 'https://slmatthew.dev']);

  $msg->reply('Template', ['template' => $template->get()]);
});

$vk->listen();

?>