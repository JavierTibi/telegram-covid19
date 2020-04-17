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

        $response = Telegram::sendMessage([
            'chat_id' => $response[0]['message']['chat']['id'],
            'text' => 'Hello World'
        ]);

        $messageId = $response->getMessageId();

        return 'ok';
    }
}
