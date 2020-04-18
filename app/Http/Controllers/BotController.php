<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use GuzzleHttp;

class BotController extends Controller
{
    public function handle()
    {
        $response = Telegram::getWebhookUpdates();
        $client = new GuzzleHttp\Client();

        //Get Country
        $country = $response['message']['text'];
        try {
            //API Countries
            $res = $client->get("https://restcountries.eu/rest/v2/name/" . $country);

            $responseCountry = $res->getBody()->getContents();
            $isoCountry = json_decode($responseCountry)[0]->alpha2Code;

            //API Coronavirus
            $res = $client->get("https://wuhan-coronavirus-api.laeyoung.endpoint.ainize.ai/jhu-edu/latest?iso2=$isoCountry&onlyCountries=true");
            $content = $res->getBody()->getContents();
            Telegram::sendMessage([
                'chat_id' => $response['message']['chat']['id'],
                'text' => 'Datos de ' . json_decode($responseCountry)[0]->name . ' hasta el momento: ' .
                    'Casos confirmados: ' . json_decode($content)[0]->confirmed . '.' .
                    'Fallecidos: ' . json_decode($content)[0]->deaths . '.'.
                    'Recuperados: ' . json_decode($content)[0]->recovered . '.  '.
                    '¡Quedate en casa!'
            ]);
        } catch (\Exception $exception) {
            Telegram::sendMessage([
                'chat_id' => $response['message']['chat']['id'],
                'text' => 'Lo sentimos no hemos encontrado ningun país con ese nombre. Intente nuevamente.'
            ]);
        }
    }
}
