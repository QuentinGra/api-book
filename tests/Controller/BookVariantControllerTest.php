<?php

namespace App\Tests\Controller;

use App\Entity\BookVariant;
use App\Entity\User;
use App\Repository\BookVariantRepository;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookVariantControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__).'/Fixtures/UserFixtures.yaml',
            \dirname(__DIR__).'/Fixtures/BookVariantFixtures.yaml',
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

    private function getBookVariant(): BookVariant
    {
        return self::getContainer()->get(BookVariantRepository::class)->findOneBy(['type' => 'poche']);
    }

    public function testEndpointIndexWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/book-variant');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointIndexWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/book-variant');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithBadId(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/book-variant/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointShowWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/book-variant/'.$this->getBookVariant()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/book-variant/'.$this->getBookVariant()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointCreateWithGoodCredentialsWithAdmin(): void
    {
        $data = [
            'type' => 'brocher',
            'enable' => true,
        ];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/book-variant/create',
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
            'type' => 'brocher',
            'enable' => true,
        ];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'POST',
            '/api/book-variant/create',
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
            'type' => str_repeat('a', 51),
            'enable' => true,
        ];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/book-variant/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEndpointUpdateWithBadId(): void
    {
        $data = ['type' => 'brocher'];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/book-variant/0',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointUpdateWithAdmin(): void
    {
        $data = ['type' => 'brocher'];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/book-variant/'.$this->getBookVariant()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointUpdateWithBadCredentialsWithAdmin(): void
    {
        $data = ['type' => str_repeat('a', 51)];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/book-variant/'.$this->getBookVariant()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEndpointUpdateWithUser(): void
    {
        $data = ['type' => 'brocher'];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'PATCH',
            '/api/book-variant/'.$this->getBookVariant()->getId(),
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
        $this->client->request('DELETE', '/api/book-variant/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointDeleteWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('DELETE', '/api/book-variant/'.$this->getBookVariant()->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointDeleteWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('DELETE', '/api/book-variant/'.$this->getBookVariant()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->databaseTool = null;
        $this->client = null;
    }
}
