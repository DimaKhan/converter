<?php


namespace App\Utils;


use SimpleXMLElement;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Класс для работы с котировками
 *
 * Class Currencies
 * @package App\Utils
 */
class Currencies
{
    /**
     * Конвертирует сумму из исходной валюту в валюту котировки
     *
     * @param string $from базовая валюта
     * @param string $to валюта котировки
     * @param float $amount сумма для конвертации
     * @return float итоговая сумма после конвертации с округлением до 2 знаков
     */
    public function convert(string $from, string $to, float $amount): float
    {
        // Делаем запрос курсов валют
        $currencies = $this->getCurrentCurrencies();

        // Получаем объект курса валют для исходной суммы
        $fromCurrency = $currencies->xpath('//ValCurs/Valute/CharCode[.="'.$from.'"]/parent::*');

        // Получаем объект курса валют для конечной суммы
        $toCurrency = $currencies->xpath('//ValCurs/Valute/CharCode[.="'.$to.'"]/parent::*');

        // Вычисляем исходную сумму в рублях
        $sumInRub = ($fromCurrency[0]->Value * $amount) / $fromCurrency[0]->Nominal;

        // Вычисляем сумму в конечной валюте
        $result = ($sumInRub / $toCurrency[0]->Value) * $toCurrency[0]->Nominal;

        return round($result, 2);
    }

    /**
     * Получает актуаильные значения котировок валют
     * @return SimpleXMLElement объект содержащий актуальные котировки
     */
    public function getCurrentCurrencies(): SimpleXMLElement
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://www.cbr.ru/scripts/XML_daily.asp');
        return new SimpleXMLElement($response->getContent());
    }
}