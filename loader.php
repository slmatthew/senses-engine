<?php

if(!extension_loaded('curl')) exit('I need curl extension to run');

define("SEV", "0.5-beta");
define("NEED_LP_LOGS", false);

include __DIR__.'/config.php';

include __DIR__.'/modules/Requests.php';
include __DIR__.'/modules/BotEngine.php';
include __DIR__.'/modules/SBSC.php';
include __DIR__.'/modules/DataHandler.php';
include __DIR__.'/modules/Keyboard.php';

?>