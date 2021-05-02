<?php

require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Lib\Timer;
use Workerman\Worker;
use App\Http\Controllers\AuthController;
use App\GameHandler;
use App\TextQuestions;

//массив подключений
$connections = [];

//подключаем контроллер для авторизации
$authController = new AuthController();

$worker = new Worker("websocket://0.0.0.0:27800");

$worker->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection) 
    {
        $connections[] = $connection;
    };
};

$worker->onMessage = function($connection, $data)
{
    //$data = json_decode("{\"TOKEN\":\"af\", \"STATUS\":\"1\"}", true);
    $data = json_decode($data, true);
    // $headers = array(
    //     'Authorization' => 'Bearer '.$data["TOKEN"]
    // );
    //$user = JWTAuth::toUser($data["TOKEN"]);
    $_REQUEST['Authorization'] = 'Bearer '.$data["TOKEN"];
    $user = $authController->me();

    print_r($user);

    switch ($data["STATUS"])
    {
        //1 - поиск игры
        case 1:
            //получить id по токену
            
            //внести id в список, в дальнейшем насчет рейтинга подумать
            //$GLOBALS["SIMPLE_MATCHMAKING"] =  
            //$GLOBALS["RANKED_MATCHMAKING"] =  

            //запуск скрипта на подбор оппонента из массива?
            
            $connection->send("result:ok,message:finding_game");
            break;
        default:
            $connection->send("result:error,message:without_status");
            break;
    }
};

Worker::runAll();



//открыть порт
// iptables -I INPUT -p tcp --dport 27800 --syn -j ACCEPT

//запуск скрипта
// php GameSocket.php start

// ws://212.109.218.92:27800