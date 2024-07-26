<?php

namespace App\Tests\Controller;

use App\Entity\Author;
use App\Entity\User;
use App\Repository\AuthorRepository;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthorControllerTest extends WebTestCase
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
            \dirname(__DIR__).'/Fixtures/AuthorFixtures.yaml',
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

    private function getAuthor(): Author
    {
        return self::getContainer()->get(AuthorRepository::class)->findOneBy(['firstName' => 'test']);
    }

    public function testEndpointIndexWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/author');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointIndexWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/author');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithBadId(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/author/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointShowWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/author/'.$this->getAuthor()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/author/'.$this->getAuthor()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointCreateWithGoodCredentialsWithAdmin(): void
    {
        $data = [
            'firstName' => 'Test',
            'lastName' => 'Test',
            'enable' => true,
        ];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/author/create',
            $data,
            [
                'image' => new UploadedFile(\dirname(__DIR__).'/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointCreateWithGoodCredentialsWithUser(): void
    {
        $data = [
            'firstName' => 'Test',
            'lastName' => 'Test',
            'enable' => true,
        ];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'POST',
            '/api/author/create',
            $data,
            [
                'image' => new UploadedFile(\dirname(__DIR__).'/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointCreateWithBadCredentialsWithAdmin(): void
    {
        $data = [
            'firstName' => str_repeat('a', 256),
            'lastName' => 'Test',
            'enable' => true,
        ];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/author/create',
            $data,
            [
                'image' => new UploadedFile(\dirname(__DIR__).'/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEndpointUpdateWithBadId(): void
    {
        $data = ['lastName' => 'test'];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/author/0',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointUpdateWithAdmin(): void
    {
        $data = ['lastName' => 'test'];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/author/'.$this->getAuthor()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointUpdateWithBadCredentialsWithAdmin(): void
    {
        $data = ['lastName' => str_repeat('a', 256)];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'PATCH',
            '/api/author/'.$this->getAuthor()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEndpointUpdateWithUser(): void
    {
        $data = ['lastName' => 'test'];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'PATCH',
            '/api/author/'.$this->getAuthor()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointUpdateImageWithBadId(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/author/0/image',
            [],
            [
                'image' => new UploadedFile(\dirname(__DIR__).'/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointUpdateImageWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/author/'.$this->getAuthor()->getId().'/image',
            [],
            [
                'image' => new UploadedFile(\dirname(__DIR__).'/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointUpdateImageWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request(
            'POST',
            '/api/author/'.$this->getAuthor()->getId().'/image',
            [],
            [
                'image' => new UploadedFile(\dirname(__DIR__).'/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointDeleteWithBadId(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('DELETE', '/api/author/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointDeleteWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('DELETE', '/api/author/'.$this->getAuthor()->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEndpointDeleteWithAdminUser(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('DELETE', '/api/author/'.$this->getAuthor()->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->databaseTool = null;
        $this->client = null;
    }

    // TODO: REMOVE IMAGE FOLDER AFTER test of this file
}
