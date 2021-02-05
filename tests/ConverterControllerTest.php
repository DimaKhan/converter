<?php

namespace App\Tests;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConverterControllerTest extends WebTestCase
{
    public function testSuccessResult()
    {
        $data = [
            'from'=> 'USD',
            'to' =>  'EUR',
            'amount' => 100
        ];

        $client = HttpClient::create();
        $response = $client->request('POST', 'http://currency.local/converter',  [
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json',],
        ]);

        // Пришёл JSON
        $this->assertJson($response->getContent());

        $content = $response->toArray();

        // В ответе есть поле result и amount
        $this->assertArrayHasKey('result', $content);
        $this->assertArrayHasKey('amount', $content);

        // Конвертация прошла успешно
        $this->assertEquals('success', $content['result']);
        // 100 долларов это 84.27 евро
        //$this->assertEquals(84.27, $content['amount']);
    }

    public function testFailedResult()
    {
        $data = ['from' => '', 'to' => '', 'amount' => null];

        $client = HttpClient::create();
        $response = $client->request('POST', 'http://currency.local/converter',  [
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json',],
        ]);

        // Пришёл JSON
        $this->assertJson($response->getContent());
        $content = $response->toArray();

        // В ответе есть поле result и amount
        $this->assertArrayHasKey('result', $content);
        $this->assertArrayHasKey('errors', $content);

        // Не удалось сконвертировать
        $this->assertEquals('failed', $content['result']);
    }
}
