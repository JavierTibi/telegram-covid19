<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Telegram\Bot\Laravel\Facades\Telegram;
use GuzzleHttp;
use Unirest;

class BotController extends Controller
{
    /**
     * Telegram Message Handler
     */
    public function handle()
    {
        $response = Telegram::getWebhookUpdates();

        //Get Country
        $text = $response['message']['text'];

        try {

            if (strlen($text) < 4) {
                $country = $this->getCoutryByCode($text);
            } else {
                $countries = $this->getCountries($text);
                $country = $countries[0];
            }



            $infoByCountry = $this->getInfoByIsoCountry($country->alpha2Code);

            $lastDay = $this->calculeLastDay($country->alpha2Code);

            $additionalInfo = '';
            if($country->name == 'Argentina') {
                $additionalInfo = 'Tranqui, ¡Mirtha esta bien!';
            }

            Telegram::sendMessage([
                'chat_id' => $response['message']['chat']['id'],
                'text' => 'Datos de ' . $country->name . ' hasta el momento:  ' . PHP_EOL .
                    'Casos confirmados: ' . $infoByCountry[0]->confirmed  .  PHP_EOL .
                    'Fallecidos: ' . $infoByCountry[0]->deaths . PHP_EOL .
                    'Recuperados: ' . $infoByCountry[0]->recovered . PHP_EOL . PHP_EOL .

                    'Datos del último día: ' . PHP_EOL .
                    'Casos confirmados: ' . $lastDay['confirmedLastDay']  .  PHP_EOL .
                    'Fallecidos: ' . $lastDay['deathsLastDay'] . PHP_EOL .
                    'Recuperados: ' . $lastDay['recoveredLastDay'] . PHP_EOL . PHP_EOL .

                    'Tasa de Mortalidad: ' . $this->deathRate($infoByCountry[0]->confirmed, $infoByCountry[0]->deaths) . '%' . PHP_EOL . PHP_EOL .

                    '¡Quedate en casa! ' . $additionalInfo . PHP_EOL . PHP_EOL . PHP_EOL .

                    'Original Data source: Provisto por JHU CSSE. Las actualizaciones se hacen una vez al día, la información puede no estar actualizada al día de hoy.'
            ]);


        } catch (\Exception $exception) {
            Telegram::sendMessage([
                'chat_id' => $response['message']['chat']['id'],
                'text' => 'Lo sentimos no hemos encontrado ningún país con ese nombre. Intente nuevamente con el código del país o el nombre en ingles.'
            ]);
        }
    }


    /**
     * Get the country by codes through an API "Rest Countries"
     * @param $code
     * @return mixed
     */
    private function getCoutryByCode($code)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->get("https://restcountries.eu/rest/v2/alpha/" . $code);

        return json_decode($res->getBody()->getContents());
    }

    /**
     * Get the country codes through an API "Rest Countries"
     * @param string $country
     * @return mixed
     */
    private function getCountries($country)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->get("https://restcountries.eu/rest/v2/name/" . $country);

        return json_decode($res->getBody()->getContents());
    }

    /**
     * Gets Coronavirus information by country through an API
     * @param $isoCountry
     * @return mixed
     */
    private function getInfoByIsoCountry($isoCountry)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->get("https://wuhan-coronavirus-api.laeyoung.endpoint.ainize.ai/jhu-edu/latest?iso2=$isoCountry&onlyCountries=true");
        return json_decode($res->getBody()->getContents());
    }

    /**
     * Gets Coronavirus time series by country through an API
     * @param $isoCountry
     * @return mixed
     */
    private function getTimeSeriesByIsoCountry($isoCountry)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->get("https://wuhan-coronavirus-api.laeyoung.endpoint.ainize.ai/jhu-edu/timeseries?iso2=$isoCountry&onlyCountries=true");
        return json_decode($res->getBody()->getContents());
    }

    /**
     * Calculates the information for the last available day with a time series
     * @param $isoCountry
     * @return array
     */
    private function calculeLastDay($isoCountry)
    {
        $timeSerie = $this->getTimeSeriesByIsoCountry($isoCountry);
        $col = new Collection();
        foreach ($timeSerie[0]->timeseries as  $value) {
            $col->push($value);
        }
        $tot = $col->count();

        $confirmedLastDay = $col->nth($tot-1)[1]->confirmed - $col->nth($tot-2)[1]->confirmed;
        $deathsLastDay = $col->nth($tot-1)[1]->deaths - $col->nth($tot-2)[1]->deaths;
        $recoveredLastDay = $col->nth($tot-1)[1]->recovered - $col->nth($tot-2)[1]->recovered;

        $result = [
            'confirmedLastDay' => $confirmedLastDay,
            'deathsLastDay' => $deathsLastDay,
            'recoveredLastDay' => $recoveredLastDay
        ];

        return $result;
    }

    /**
     * Get death rate
     * @param $confirmed
     * @param $deaths
     * @return float|int
     */
    private function deathRate($confirmed, $deaths)
    {
        return round($deaths * 100 / $confirmed,2);
    }
}
