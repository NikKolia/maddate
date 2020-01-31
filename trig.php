<?php
require_once 'vendor/autoload.php';
require_once 'lib/db.php';

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

global $config;

if (isset($_POST['username']) && !empty($_POST['username'])) {
    $username = $_POST['username'];
}

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
}
if (isset($_POST['userid']) && !empty($_POST['userid'])) {
    $userid = $_POST['userid'];
}

$trigger = $db->where('id', (int) $userid)->getOne('users', array(
    'id',
    'first_name',
    'last_name',
    'src'
));

//$trigger->{'src'} === "Fake"
if ($trigger->{'src'} === "site") {
    $saved = $db->insert('messages', array(
        'from' => (int)$userid,
        'to' => (int)$id,
        'text' => html_entity_decode("Hello! " . $username . "! (id=" . $id . ") You liked me:)) My userid: " . $userid . "!", ENT_QUOTES),
        'media' => NULL,
        'sticker' => NULL,
        'seen' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ));
    if ($config->message_request_system == 'on') {
        $last_decline = CheckIfUserDeclinedBefore($userid = $_POST['userid'], $id = $_POST['id']);
        if (!empty($last_decline)) {
            if ($last_decline->status == "2" && intval($last_decline->created_at) > strtotime("-1 day")) {
                $raminhours = 24 - intval(date('H', time() - intval($last_decline->created_at)));
                if ($raminhours <= 24) {
                    return array(
                        'status' => 400,
                        'declined' => true,
                        'message' => __('This user decline your chat before so you can chat with this user after') . ' ' . $raminhours . ' ' . __('hours.'),
                        'data' => $last_decline,
                        'raminhours' => $raminhours,
                        'time' => time(),
                        'hours' => date('H', time()),
                        'r' => intval($last_decline->created_at),
                        'x' => intval(date('H', time() - intval($last_decline->created_at)))
                    );
                }
            }

        }
    }


    $sid = $db->where('sender_id', $userid = $_POST['userid'])->where('receiver_id', (int)$id = $_POST['id'])->getOne('conversations', array(
        'id',
        'created_at',
        'status'
    ));
    if ($sid['id'] > 0) {

        $data = [];
        $data['created_at'] = time();

        if ($config->message_request_system == 'on') {
            if ((int)$sid['status'] == 2) {
                if (intval($sid['created_at']) < strtotime("-1 day")) {
                    $data['status'] = 0;
                    $db->where('sender_id', $userid = $_POST['userid'])->where('receiver_id', (int)$id = $_POST['id'])->update('conversations', array('status' => 0));
                    $db->where('sender_id', (int)$id = $_POST['id'])->where('receiver_id', $userid = $_POST['userid'])->update('conversations', array('status' => 1));
                    $isnew = true;
                }
            }
        }
        $db->where('id', $sid['id'])->update('conversations', $data);

    } else {
        $dat = array(
            'sender_id' => $userid = $_POST['userid'],
            'receiver_id' => (int)$id = $_POST['id'],
            'created_at' => time()
        );
        if ($config->message_request_system == 'on') {
            $dat['status'] = 1;
        } else {
//          $dat['status'] = 0;
            $dat['status'] = 1;
        }
        $db->insert('conversations', $dat);
        $isnew = true;
    }


    $rid = $db->where('sender_id', (int)$id = $_POST['id'])->where('receiver_id', $userid = $_POST['userid'])->getOne('conversations', array(
        'id'
    ));

    if ($rid['id'] > 0) {
        $db->where('id', $rid['id'])->update('conversations', array(
            'created_at' => time()
        ));
    } else {
        $dat2 = array(
            'sender_id' => (int)$id = $_POST['id'],
            'receiver_id' => $userid = $_POST['userid'],
            'created_at' => time(),
            'status' => 1
        );
        $db->insert('conversations', $dat2);
        $isnew = true;
    }

    $saved_views = $db->insert('views', array('user_id' => $userid, 'view_userid' => $id, 'created_at' => date('Y-m-d H:i:s')));

}
if ($isnew === true) {
    if ($config->message_request_system == 'on') {
        $Notification = LoadEndPointResource('Notifications');
        if ($Notification) {
            $Notification->createNotification(auth()->web_device_id, auth()->id, $id = $_POST['id'], 'message', '', '/@' . auth()->username . '/chat_request');
        }
    }
}

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
    $reply = "Hello, my friend!! Let's conversate!";
    $bot->reply($reply);
});

$botman->fallback(function ($bot) {
    $bot->reply('Sorry, I did not understand these commands. Here is a list of commands I understand: ...');
});

// Start listening
$botman->listen();


/*$botman->hears('{userid}|{username}', function (BotMan $bot, $username) {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
    }
    if (isset($_POST['userid']) && !empty($_POST['userid'])) {
        $userid = $_POST['userid'];
    }
    $reply = "Hello! " . $username . "! (id=" . $id . ") You liked me:)) My userid: " . $userid . "!";
    $bot->reply($reply);
});*/

/*echo "\nIT Works \n";*/

/*if ($saved) {
    $_msg = LoadEndPointResource('messages');
    if ($_msg) {
        $_msg->createNewConversation((int) $id = $_POST[ 'id' ]);
    }
    return array(
        'status' => 200,
        'message' => __('Message sent'),
        'to' => (int) $id = $_POST[ 'id' ],
        'msg' => $saved
    );
}

echo "\nIT Works \n";*/

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

/*$listener = $db->Where('to_delete', '0')->where('`to`', $id)->Where('`from`', $userid)->orderBy('id', 'DESC')->get('messages', 50, array(
    '`text`',
    'from_delete',
    'to_delete',
    'seen',
    '`from`',
    'media',
    'sticker',
    'created_at'
));*/

/*$listener = $db->Where('to_delete', '0')->where('`to`', $id)->Where('`from`', $userid)->getValue('messages','text', 20);*/
/*$listener = $db->where('`to`', $id)->getValue('messages','text', 200);

print_r($listener);*/
/*print_r (isset($listener->{'text'}));*/
/*print_r($listener['text'] === "hello");*/