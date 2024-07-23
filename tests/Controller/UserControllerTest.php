<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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

    // public function testEndpointLogin(): void
    // {
    //     $data = [
    //         'username' => 'admin@test.com',
    //         'password' => 'Test1234!',
    //     ];

    //     $this->client->request(
    //         'POST',
    //         '/api/login',
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode($data)
    //     );

    //     $this->assertResponseStatusCodeSame(204);
    //     $this->assertResponseHasCookie('BEARER');
    // }

    public function testEndpointIndexWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/user');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointIndexWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/user');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointShowWithBadId(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/user/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointShowWithUserNotOwner(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/user/' . $this->getAdminUser()->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointShowWithUserOwner(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/user/' . $this->getUser()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointCreateWithGoodCredentials(): void
    {
        $data = [
            'email' => 'admin@hotmail.com',
            'password' => 'Test1234!',
            'firstName' => 'Test',
            'lastName' => 'Test',
        ];

        $this->client->request(
            'POST',
            '/api/user/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointCreateWithBadCredentials(): void
    {
        $data = [
            'email' => 'admin@hotmail.com',
            'password' => 'Test1234!',
            'firstName' => str_repeat('a', 256),
            'lastName' => 'Test',
        ];

        $this->client->request(
            'POST',
            '/api/user/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEndpointUpdateWithBadId(): void
    {
        $data = [
            'lastName' => 'test',
        ];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'PATCH',
            '/api/user/0',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointUpdateWithUserNotOwner(): void
    {
        $data = [
            'lastName' => 'test',
        ];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'PATCH',
            '/api/user/' . $this->getAdminUser()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointUpdateWithUserOwner(): void
    {
        $data = ['lastName' => 'test'];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'PATCH',
            '/api/user/' . $this->getUser()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointDeleteWithBadId(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/user/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointDeleteWithUserNotOwner(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/user/' . $this->getAdminUser()->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointDeleteWithUserOwner(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/user/' . $this->getUser()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->databaseTool = null;
        $this->client = null;
    }
}
