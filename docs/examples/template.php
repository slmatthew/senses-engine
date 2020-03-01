<?php

/**
 * Пример бота, отправляющего
 * в ответ на сообщение `template`
 * сообщение-шаблон с каруселью.
 */

include './loader.php';

$vk = new vk('lp');

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