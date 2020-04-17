<?php

namespace App\Http\Controllers;

use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Laravel\TelegramServiceProvider;

class BotController extends Controller
{
    public function handle()
    {
        $response = Telegram::getWebhookUpdates();

        $token = '1012614917:AAGWeXuJzdmBB5fKaN7pQ6gEH9ucrK8eJr';
        $data = [
            'chat_id' => $response['message']['chat']['id'],
            'text' => 'Hello world!'
        ];
        /*
        $token = '1012614917:AAGWeXuJzdmBB5fKaN7pQ6gEH9ucrK8eJr';
        $data = [
            //'chat_id' => '@JavierT611',
            'text' => 'Hello world!'
        ];

        file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );
         */
        /*
        $response = file_get_contents("https://api.telegram.org/boto/sendMessage?" . http_build_query($data) );

        dd($response);
        */

        file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );
        
        Telegram::sendMessage([
            'chat_id' => $response['message']['chat']['id'],
            'text' => 'Hello World'
        ]);



    }
}
