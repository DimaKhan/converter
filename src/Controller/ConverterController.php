<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Request;

class ConverterController extends AbstractController
{
    protected $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/converter", name="converter")
     */
    public function index(): Response
    {
        // Получаем данные POST запроса
        $request = Request::createFromGlobals();
        $params = $request->toArray();

        // Делаем запрос курсов валют
        $response = $this->client->request('GET', 'http://www.cbr.ru/scripts/XML_daily.asp');
        $results = new SimpleXMLElement($response->getContent());

        // Получаем объект курса валют для исходной суммы
        $fromCurrency = $results->xpath('//ValCurs/Valute/CharCode[.="'.$params['from'].'"]/parent::*');

        // Получаем объект курса валют для конечной суммы
        $toCurrency = $results->xpath('//ValCurs/Valute/CharCode[.="'.$params['to'].'"]/parent::*');

        // Вычисляем исходную сумму в рублях
        $sumInRub = ($fromCurrency[0]->Value * $params['amount']) / $fromCurrency[0]->Nominal;

        // Вычисляем сумму в конечной валюте
        $result = ($sumInRub / $toCurrency[0]->Value) * $toCurrency[0]->Nominal;

        // Возвращаем результат в виде JSON с округлением конечной суммы до 2 знаков
        return $this->json([
            'result' => round($result, 2),
        ]);
    }
}
