<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use GuzzleHttp;

class BotController extends Controller
{
    public function handle()
    {
        $response = Telegram::getWebhookUpdates();

        $country = $response['message']['text'];

        $responseCountry = file_get_contents("https://restcountries.eu/rest/v2/name/" . $country);
        $isoCountry = json_decode($responseCountry)[0]->alpha2Code;


        if ($isoCountry) {
            $client = new GuzzleHttp\Client();
            $res = $client->get("https://wuhan-coronavirus-api.laeyoung.endpoint.ainize.ai/jhu-edu/latest?iso2=AR&onlyCountries=true");
            $content = $res->getBody()->getContents();
            Telegram::sendMessage([
                'chat_id' => $response['message']['chat']['id'],
                'text' => 'Datos de ' . json_decode($responseCountry)[0]->name . 'hasta el momento: \n' .
                           'Casos confirmados: ' . json_decode($content)[0]->confirmed . '\n' .
                           'Fallecidos: ' . json_decode($content)[0]->deaths . '\n'.
                           'Recuperados: ' . json_decode($content)[0]->recovered . '\n'.
                            '¡Quedate en casa!'
            ]);
        } else {
            Telegram::sendMessage([
                'chat_id' => $response['message']['chat']['id'],
                'text' => 'Lo sentimos no hemos encontrado ningun país con ese nombre. Intente nuevamente.'
            ]);
        }
    }
}
