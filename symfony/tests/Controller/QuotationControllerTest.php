<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QuotationControllerTest extends WebTestCase
{
    public function testGetQuotations(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/quotation');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    // public function testAddQuotation(): void
    // {
    //     $client = static::createClient();
    //     $client->request('POST', '/api/quotation', [
    //         'json' => [
    //             'company' => 1,
    //             'status' => 'w trakcie',
    //             'data_wystawienia' => '2024-01-01',
    //             'data_poczatek' => '2024-01-10',
    //             'data_koniec' => '2024-01-20',
    //             'dane_kontaktowe' => 'Jan Kowalski, 123456789',
    //             'miejsce' => 'Warszawa',
    //             'rabat' => '5.00',
    //             'dodatkowe_informacje' => 'Opcjonalne',
    //         ],
    //     ]);

    //     $this->assertResponseStatusCodeSame(201);
    //     $data = json_decode($client->getResponse()->getContent(), true);
    //     $this->assertArrayHasKey('data', $data);
    //     $this->assertArrayHasKey('id', $data['data']);
    // }

    public function testGetQuotationById(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/quotation/1');

        if ($client->getResponse()->getStatusCode() === 200) {
            $this->assertResponseFormatSame('json');
            $data = json_decode($client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('id', $data);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    // public function testDeleteQuotation(): void
    // {
    //     $client = static::createClient();

    //     $client->request('POST', '/api/quotation', [
    //         'json' => [
    //             'company' => 1,
    //             'status' => 'w trakcie',
    //             'data_wystawienia' => '2024-01-01',
    //             'data_poczatek' => '2024-01-10',
    //             'data_koniec' => '2024-01-20',
    //             'dane_kontaktowe' => 'Jan Kowalski, 123456789',
    //             'miejsce' => 'Warszawa',
    //             'rabat' => '5.00',
    //             'dodatkowe_informacje' => 'Opcjonalne',
    //         ],
    //     ]);

    //     $responseData = json_decode($client->getResponse()->getContent(), true);
    //     $quoteId = $responseData['data']['id'];

    //     $client->request('DELETE', "/api/quotation/{$quoteId}");

    //     $this->assertResponseStatusCodeSame(200);
    //     $this->assertJsonContains(['message' => 'Wycena została usunięta']);
    // }

    public function testGetQuotationEquipment(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/quotation/equipment');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    // public function testGetQuotationEquipmentById(): void
    // {
    //     $client = static::createClient();
    //     $client->request('GET', '/api/quotation/equipment/1');

    //     if ($client->getResponse()->getStatusCode() === 200) {
    //         $data = json_decode($client->getResponse()->getContent(), true);
    //         $this->assertArrayHasKey('idEquipment', $data);
    //     } else {
    //         $this->assertResponseStatusCodeSame(404);
    //     }
    // }
}
