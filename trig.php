<?php
require_once 'vendor/autoload.php';
require_once 'lib/db.php';

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

global $config;

$config = [
    // Your driver-specific configuration
    // "telegram" => [
    //    "token" => "TOKEN"
    // ]
];

DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

$botman = BotManFactory::create($config);

// Give the bot something to listen for.
$botman->hears('Hello|Hi', function (BotMan $bot) {
    $bot->reply('Hello too');
});

if (isset($_POST[ 'username' ]) && !empty($_POST[ 'username' ])) {
    $username = $_POST[ 'username' ];
}

$botman->hears('{userid}|{username}' , function (BotMan $bot, $username) {
    if (isset($_POST[ 'id' ]) && !empty($_POST[ 'id' ])) {
        $id = $_POST[ 'id' ];
    }
    if (isset($_POST[ 'userid' ]) && !empty($_POST[ 'userid' ])) {
        $userid = $_POST[ 'userid' ];
    }
    $reply = "Hello,".$username."! (id=".$id.") Your liked userid is: ".$userid;
    $bot->reply($reply);
});

$botman->fallback(function($bot) {
    $bot->reply('Sorry, I did not understand these commands. Here is a list of commands I understand: ...');
});

// Start listening
$botman->listen();

$saved = false;
$saved = $db->insert('messages', array(
//  'id' =>
    'from' => $id = $_POST[ 'id' ],
    'from_delete' => 0,
    'to' => $userid = $_POST[ 'userid' ],
    'to_delete'=> 0,
    'text' => $reply = "Hello,".$username."! (id=".$id.") Your liked userid is: ".$userid,
//  'media' =>
//  'sticker' =>
//  'seen' => 0,
    'created_at' => date('Y-m-d H:i:s')
));

/*$userid = (isset($_POST['userid']))?
   $_POST['userid']:' не указано ';
$id = (isset($_POST['id']))?
    $_POST['id']:' не указано ';
$username = (isset($_POST['username']))?
    $_POST['username']:' не указано ';

echo "\n userid: $userid";
echo "\n id: $id";
echo "\n username: $username";*/

/*$botman->hears('what is the time in {city} located in {continent}' , function (BotMan $bot,$city,$continent) {
    date_default_timezone_set("$continent/$city");
    $reply = "The time in ".$city." ".$continent." is ".date("h:i:sa");
    $bot->reply($reply);
});*/

// $bot->startConversation(new MyConversation, 'FACEBOOK-MESSENGER-USER-ID', FacebookDriver::class);

/* $botman->hears('Hello', function($bot) {
   $bot->startConversation(new OnboardingConversation);
});*/