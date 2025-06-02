<?php

namespace App\Tests\Controller;

use App\Tests\AuthenticatedWebTestCase;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;

class CategoryControllerTest extends AuthenticatedWebTestCase
{
    public function testGetCategories(): void
    {
        $this->logInSession();
        $this->client->request('GET', '/api/category');
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testAddCategory(): void
    {
        $this->logInSession();
        $data = ['nazwa' => 'Nowa kategoria'];
        $this->client->request(
            'POST',
            '/api/category',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponse($this->client->getResponse(), 201);

        $this->client->request(
            'POST',
            '/api/category',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testDeleteCategory(): void
    {
        $this->logInSession();
        $category = new Category();
        $category->setNazwa('To Be Deleted ' . uniqid());
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/category/' . $category->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request('DELETE', '/api/category/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCategoryAppearsInListAfterAdd(): void
    {
        $this->logInSession();
        $name = 'TestListCategory ' . uniqid();
        $this->client->request('POST', '/api/category', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['nazwa' => $name]));
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->client->request('GET', '/api/category');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $names = array_column($response, 'nazwa');
        $this->assertContains($name, $names);
    }

    private function assertJsonResponse($response, int $statusCode): void
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            'Response does not contain the expected "Content-Type: application/json" header'
        );
    }
}

