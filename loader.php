<?php

if(!extension_loaded('curl')) exit('I need curl extension to run');

include __DIR__.'/config.php';

include __DIR__.'/modules/Requests.php';
include __DIR__.'/modules/BotEngine.php';
include __DIR__.'/modules/DataHandler.php';

?>