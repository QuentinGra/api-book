<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\Utils\Providers\EnableTrait;
use App\Tests\Utils\Providers\UniqueNameTrait;
use App\Tests\Utils\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryEntityTest extends KernelTestCase
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

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/CategoryFixtures.yaml',
        ]);

        $categoryRepo = self::getContainer()->get(CategoryRepository::class);

        $categories = $categoryRepo->findAll();

        $this->assertCount(6, $categories);
    }

    private function getEntity(): Category
    {
        return (new Category())
            ->setName('lorem')
            ->setDescription('lorem')
            ->setEnable(false);
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
        $category = $this->getEntity()
            ->setEnable($enable);

        $this->assertHasErrors($category, 1);
    }

    /**
     * @dataProvider provideName
     */
    public function testInvalideName(string $name): void
    {
        $category = $this->getEntity()
            ->setName($name);

        $this->assertHasErrors($category, 1);
    }

    public function testfindAllWithPagination(): void
    {
        $repo = self::getContainer()->get(CategoryRepository::class);

        $categories = $repo->findAllWithPagination(1, 6);

        $this->assertCount(6, $categories);
    }

    public function testfindAllWithPaginationWithInvalidArgument(): void
    {
        $repo = self::getContainer()->get(CategoryRepository::class);

        $this->expectException(\TypeError::class);

        $repo->findAllWithPagination('test', 6);
    }

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
