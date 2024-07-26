<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/UserFixtures.yaml',
            \dirname(__DIR__) . '/Fixtures/CategoryFixtures.yaml',
        ]);
    }

    private function getAdminUser(): User
    {
        return self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@test.com']);
    }

    private function getUser(): User
    {
        return self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user@test.com']);
    }

    private function getCategory(): Category
    {
        return self::getContainer()->get(CategoryRepository::class)->findOneBy(['name' => 'test']);
    }

    public function testEndpointIndexWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/category');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointIndexWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/category');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithBadId(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/category/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointShowWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/category/' . $this->getCategory()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/category/' . $this->getCategory()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointCreateWithGoodCredentialsWithAdmin(): void
    {
        $data = [
            'name' => 'lorem',
            'description' => 'lorem',
            'enable' => true,
        ];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/category/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointCreateWithGoodCredentialsWithUser(): void
    {
        $data = [
            'name' => 'lorem',
            'description' => 'lorem',
            'enable' => true,
        ];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'POST',
            '/api/category/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointCreateWithBadCredentialsWithAdmin(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
            'description' => 'lorem',
            'enable' => true,
        ];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/category/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEndpointUpdateWithBadId(): void
    {
        $data = ['name' => 'lorem'];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/category/0',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointUpdateWithAdmin(): void
    {
        $data = ['name' => 'lorem'];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/category/' . $this->getCategory()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointUpdateWithBadCredentialsWithAdmin(): void
    {
        $data = ['name' => str_repeat('a', 256)];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/category/' . $this->getCategory()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEndpointUpdateWithUser(): void
    {
        $data = ['name' => 'lorem'];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'PATCH',
            '/api/category/' . $this->getCategory()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointDeleteWithBadId(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('DELETE', '/api/category/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointDeleteWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('DELETE', '/api/category/' . $this->getCategory()->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointDeleteWithAdminUser(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('DELETE', '/api/category/' . $this->getCategory()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->databaseTool = null;
        $this->client = null;
    }
}
