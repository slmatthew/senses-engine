<?php

/**
 * @package settings
 */

if(!extension_loaded('curl')) exit('I need curl extension to run');

/**
 * @ignore
 */
define("SEV", "0.8");

/**
 * @var bool Should engine show LP logs
 */
define("NEED_LP_LOGS", false);

include_once __DIR__.'/config.php';

if(isset($config['dev'])) define("CURL_VERIFY", false);
else define("CURL_VERIFY", true);

include_once __DIR__.'/modules/Exceptions.php';
include_once __DIR__.'/modules/Message.php';
include_once __DIR__.'/modules/Requests.php';
include_once __DIR__.'/modules/BotEngine.php';
//include_once __DIR__.'/modules/SBSC.php';
include_once __DIR__.'/modules/DataHandler.php';
include_once __DIR__.'/modules/Keyboard.php';
include_once __DIR__.'/modules/Template.php';
include_once __DIR__.'/modules/LpDecoder.php';
include_once __DIR__.'/modules/Audio.php';
include_once __DIR__.'/modules/Vk.php';
include_once __DIR__.'/modules/Auth.php';
include_once __DIR__.'/modules/Debugger.php';

?>