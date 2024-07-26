<?php

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use App\Tests\Utils\Providers\EnableTrait;
use App\Tests\Utils\Providers\NameTrait;
use App\Tests\Utils\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AuthorEntityTest extends KernelTestCase
{
    use TestTrait;
    use NameTrait;
    use EnableTrait;

    protected ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__).'/Fixtures/AuthorFixtures.yaml',
        ]);

        $authorRepo = self::getContainer()->get(AuthorRepository::class);

        $authors = $authorRepo->findAll();

        $this->assertCount(6, $authors);
    }

    private function getEntity(): Author
    {
        return (new Author())
            ->setFirstName('test')
            ->setLastName('test')
            ->setDescription('test')
            ->setEnable(false);
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    /**
     * @dataProvider provideName
     */
    public function testInvalideLastName(string $name): void
    {
        $author = $this->getEntity()
            ->setLastName($name);

        $this->assertHasErrors($author, 1);
    }

    /**
     * @dataProvider provideEnable
     */
    public function testInvalideEnable(?bool $enable): void
    {
        $author = $this->getEntity()
            ->setEnable($enable);

        $this->assertHasErrors($author, 1);
    }

    public function testfindAllWithPagination(): void
    {
        $repo = self::getContainer()->get(AuthorRepository::class);

        $authors = $repo->findAllWithPagination(1, 6);

        $this->assertCount(6, $authors);
    }

    public function testfindAllWithPaginationWithInvalidArgument(): void
    {
        $repo = self::getContainer()->get(AuthorRepository::class);

        $this->expectException(\TypeError::class);

        $repo->findAllWithPagination('test', 6);
    }

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
