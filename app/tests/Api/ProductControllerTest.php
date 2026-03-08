<?php

declare(strict_types=1);

namespace App\Tests\Api;

final class ProductControllerTest extends ApiTestCase
{
    public function testCreateProductReturnsCreatedResponse(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/products',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Gaming Mouse X1',
                'sku' => 'GMX1-TEST-001',
                'price' => '199.99',
                'currency' => 'PLN',
                'status' => 'active',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $data);
        self::assertNotNull($data['id']);
        self::assertSame('Gaming Mouse X1', $data['name']);
        self::assertSame('GMX1-TEST-001', $data['sku']);
        self::assertSame('199.99', $data['price']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame('active', $data['status']);
        self::assertSame(1, $data['version']);
    }

    public function testCreateProductWithDuplicateSkuReturnsConflict(): void
    {
        $client = self::createClient();

        $payload = [
            'name' => 'Gaming Mouse X1',
            'sku' => 'GMX1-DUP-001',
            'price' => '199.99',
            'currency' => 'PLN',
            'status' => 'active',
        ];

        $client->request(
            'POST',
            '/api/products',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            '/api/products',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(409);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Active product with given SKU already exists.', $data['message']);
    }

    public function testGetProductReturnsDetailsWithPriceHistory(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/products',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Keyboard X1',
                'sku' => 'KEY-GET-001',
                'price' => '299.99',
                'currency' => 'PLN',
                'status' => 'active',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $created);
        self::assertNotNull($created['id']);

        $client->request('GET', '/api/products/' . $created['id']);

        self::assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($created['id'], $data['id']);
        self::assertSame('KEY-GET-001', $data['sku']);
        self::assertArrayHasKey('priceHistory', $data);
        self::assertIsArray($data['priceHistory']);
    }

    public function testUpdatePriceCreatesPriceHistoryEntry(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/products',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Monitor X1',
                'sku' => 'MON-UPD-001',
                'price' => '999.99',
                'currency' => 'PLN',
                'status' => 'active',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $created);
        self::assertNotNull($created['id']);
        self::assertArrayHasKey('version', $created);
        self::assertNotNull($created['version']);

        $client->request(
            'PUT',
            '/api/products/' . $created['id'],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Monitor X1',
                'sku' => 'MON-UPD-001',
                'price' => '1099.99',
                'currency' => 'PLN',
                'status' => 'active',
                'version' => $created['version'],
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $updated = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('1099.99', $updated['price']);
        self::assertCount(1, $updated['priceHistory']);
        self::assertSame('999.99', $updated['priceHistory'][0]['oldPrice']);
        self::assertSame('1099.99', $updated['priceHistory'][0]['newPrice']);
    }

    public function testUpdateWithStaleVersionReturnsConflict(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/products',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Laptop X1',
                'sku' => 'LAP-LOCK-001',
                'price' => '3999.99',
                'currency' => 'PLN',
                'status' => 'active',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $created);
        self::assertNotNull($created['id']);
        self::assertArrayHasKey('version', $created);
        self::assertNotNull($created['version']);

        $client->request(
            'PUT',
            '/api/products/' . $created['id'],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Laptop X1',
                'sku' => 'LAP-LOCK-001',
                'price' => '4099.99',
                'currency' => 'PLN',
                'status' => 'active',
                'version' => $created['version'],
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);

        $client->request(
            'PUT',
            '/api/products/' . $created['id'],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Laptop X1',
                'sku' => 'LAP-LOCK-001',
                'price' => '4199.99',
                'currency' => 'PLN',
                'status' => 'active',
                'version' => $created['version'],
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(409);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Product has been modified. Please refresh and try again.', $data['message']);
    }

    public function testSoftDeleteMakesProductUnavailable(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/products',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Headset X1',
                'sku' => 'HEAD-DEL-001',
                'price' => '499.99',
                'currency' => 'PLN',
                'status' => 'active',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $created);
        self::assertNotNull($created['id']);

        $client->request('DELETE', '/api/products/' . $created['id']);
        self::assertResponseStatusCodeSame(204);

        $client->request('GET', '/api/products/' . $created['id']);
        self::assertResponseStatusCodeSame(404);
    }
}
