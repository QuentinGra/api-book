<?php

namespace App\Tests\Entity;

use App\Entity\BookVariant;
use App\Repository\BookVariantRepository;
use App\Tests\Utils\Providers\EnableTrait;
use App\Tests\Utils\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookVariantEntityTest extends KernelTestCase
{
    use TestTrait;
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
            \dirname(__DIR__) . '/Fixtures/BookVariantFixtures.yaml',
        ]);

        $bookVariantRepo = self::getContainer()->get(BookVariantRepository::class);

        $bookVariants = $bookVariantRepo->findAll();

        $this->assertCount(2, $bookVariants);
    }

    private function getEntity(): BookVariant
    {
        return (new BookVariant())
            ->setType('brocher')
            ->setEnable(false);
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    /**
     * @dataProvider provideType
     */
    public function testInvalideType(string $type, int $number): void
    {
        $bookVariant = $this->getEntity()
            ->setType($type);

        $this->assertHasErrors($bookVariant, $number);
    }

    /**
     * @dataProvider provideEnable
     */
    public function testInvalideEnable(?bool $enable): void
    {
        $bookVariant = $this->getEntity()
            ->setEnable($enable);

        $this->assertHasErrors($bookVariant, 1);
    }

    public function provideType(): array
    {
        return [
            'non_unique' => [
                'type' => 'poche',
                'number' => 1,
            ],
            'max_length' => [
                'type' => str_repeat('a', 51),
                'number' => 2,
            ],
            'empty' => [
                'type' => '',
                'number' => 2,
            ]
        ];
    }

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
