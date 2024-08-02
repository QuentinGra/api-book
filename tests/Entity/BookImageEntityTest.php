<?php

namespace App\Tests\Entity;

use App\Entity\Book;
use App\Entity\BookImage;
use App\Repository\BookImageRepository;
use App\Repository\BookRepository;
use App\Tests\Utils\Providers\EnableTrait;
use App\Tests\Utils\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookImageEntityTest extends KernelTestCase
{
    use TestTrait;
    use EnableTrait;

    protected ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    private function getBook(): Book
    {
        return self::getContainer()->get(BookRepository::class)->findOneBy(['name' => 'test']);
    }

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/BookImageFixtures.yaml',
        ]);

        $bookImageRepo = self::getContainer()->get(BookImageRepository::class);

        $bookImages = $bookImageRepo->findAll();

        $this->assertCount(6, $bookImages);
    }

    // TODO: Test setImage

    private function getEntity(): BookImage
    {
        return (new BookImage())
            ->setEnable(false)
            ->setBook($this->getBook());
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
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

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
