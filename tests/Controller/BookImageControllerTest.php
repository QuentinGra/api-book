<?php

namespace App\Tests\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Entity\BookImage;
use App\Repository\BookRepository;
use App\Repository\UserRepository;
use App\Repository\BookImageRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;

class BookImageControllerTest extends WebTestCase
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
            \dirname(__DIR__) . '/Fixtures/BookImageFixtures.yaml',
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

    private function getBook(): Book
    {
        return self::getContainer()->get(BookRepository::class)->findOneBy(['name' => 'test']);
    }

    public function testEndpointIndexWithAdmin(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/book-image');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointIndexWithUser(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/book-image');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithGoodIdAsUser(): void
    {
        $book = $this->getBook();
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/book-image/' . $book->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithGoodIdAsAdmin(): void
    {
        $book = $this->getBook();
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/api/book-image/' . $book->getId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEndpointShowWithBadId(): void
    {
        $this->client->loginUser($this->getUser());
        $this->client->request('GET', '/api/book_image/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEndpointCreateWithGoodCredentialsWithAdmin(): void
    {
        $data = [
            'book' => $this->getBook()->getId(),
            'enable' => true,
        ];

        $this->client->loginUser($this->getAdminUser());
        $this->client->request(
            'POST',
            '/api/book-image/create',
            $data,
            [
                'image' => new UploadedFile(\dirname(__DIR__) . '/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testEndpointCreateWithGoodCredentialsWithUser(): void
    {
        $data = [
            'book' => $this->getBook()->getId(),
            'enable' => true,
        ];

        $this->client->loginUser($this->getUser());
        $this->client->request(
            'POST',
            '/api/book-image/create',
            $data,
            [
                'image' => new UploadedFile(\dirname(__DIR__) . '/Assets/Images/sylius.png', 'sylius.png'),
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->databaseTool = null;
        $this->client = null;
    }
}
