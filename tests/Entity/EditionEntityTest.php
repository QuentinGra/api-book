<?php

namespace App\Tests\Entity;

use App\Entity\Edition;
use App\Repository\EditionRepository;
use App\Tests\Utils\Providers\EnableTrait;
use App\Tests\Utils\Providers\UniqueNameTrait;
use App\Tests\Utils\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EditionEntityTest extends KernelTestCase
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
            \dirname(__DIR__).'/Fixtures/EditionFixtures.yaml',
        ]);

        $editionRepo = self::getContainer()->get(EditionRepository::class);

        $editions = $editionRepo->findAll();

        $this->assertCount(6, $editions);
    }

    private function getEntity(): Edition
    {
        return (new Edition())
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
        $edition = $this->getEntity()
            ->setEnable($enable);

        $this->assertHasErrors($edition, 1);
    }

    /**
     * @dataProvider provideName
     */
    public function testInvalideName(string $name): void
    {
        $edition = $this->getEntity()
            ->setName($name);

        $this->assertHasErrors($edition, 1);
    }

    public function testfindAllWithPagination(): void
    {
        $repo = self::getContainer()->get(EditionRepository::class);

        $editions = $repo->findAllWithPagination(1, 6);

        $this->assertCount(6, $editions);
    }

    public function testfindAllWithPaginationWithInvalidArgument(): void
    {
        $repo = self::getContainer()->get(EditionRepository::class);

        $this->expectException(\TypeError::class);

        $repo->findAllWithPagination('test', 6);
    }

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
