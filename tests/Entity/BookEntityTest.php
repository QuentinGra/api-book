<?php

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Edition;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\EditionRepository;
use App\Tests\Utils\Providers\EnableTrait;
use App\Tests\Utils\Providers\UniqueNameTrait;
use App\Tests\Utils\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookEntityTest extends KernelTestCase
{
    use TestTrait;
    use EnableTrait;
    use UniqueNameTrait;

    protected ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    private function getEdition(): Edition
    {
        return self::getContainer()->get(EditionRepository::class)->findOneBy(['name' => 'test']);
    }

    private function getAuthor(): Author
    {
        return self::getContainer()->get(AuthorRepository::class)->findOneBy(['firstName' => 'test']);
    }

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/BookFixtures.yaml',
        ]);

        $bookRepo = self::getContainer()->get(BookRepository::class);

        $books = $bookRepo->findAll();

        $this->assertCount(6, $books);
    }

    private function getEntity(): Book
    {
        return (new Book())
            ->setName('lorem')
            ->setDescription('lorem')
            ->setDateEdition(new \DateTime())
            ->setEnable(false)
            ->setAuthor($this->getAuthor())
            ->setEdition($this->getedition());
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    /**
     * @dataProvider provideName
     */
    public function testInvalideName(string $name): void
    {
        $book = $this->getEntity()
            ->setName($name);

        $this->assertHasErrors($book, 1);
    }

    /**
     * @dataProvider provideEnable
     */
    public function testInvalideEnable(?bool $enable): void
    {
        $book = $this->getEntity()
            ->setEnable($enable);

        $this->assertHasErrors($book, 1);
    }

    public function testfindAllWithPagination(): void
    {
        $repo = self::getContainer()->get(BookRepository::class);

        $books = $repo->findAllWithPagination(1, 6);

        $this->assertCount(6, $books);
    }

    public function testfindAllWithPaginationWithInvalidArgument(): void
    {
        $repo = self::getContainer()->get(BookRepository::class);

        $this->expectException(\TypeError::class);

        $repo->findAllWithPagination('test', 6);
    }

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
