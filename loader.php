<?php

/**
 * @package settings
 */

if(!extension_loaded('curl')) exit('I need curl extension to run');

/**
 * @ignore
 */
define("SEV", "0.7");

/**
 * @var bool Should engine show LP logs
 */
define("NEED_LP_LOGS", false);

include __DIR__.'/config.php';

if(isset($config['dev'])) define("CURL_VERIFY", false);
else define("CURL_VERIFY", true);

include __DIR__.'/modules/Exceptions.php';
include __DIR__.'/modules/Requests.php';
include __DIR__.'/modules/BotEngine.php';
include __DIR__.'/modules/SBSC.php';
include __DIR__.'/modules/DataHandler.php';
include __DIR__.'/modules/Keyboard.php';
include __DIR__.'/modules/Template.php';
include __DIR__.'/modules/LpDecoder.php';
include __DIR__.'/modules/Audio.php';

?>