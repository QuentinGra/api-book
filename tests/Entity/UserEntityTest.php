<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Utils\Providers\NameTrait;
use App\Tests\Utils\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserEntityTest extends KernelTestCase
{
    use TestTrait;
    use NameTrait;

    protected ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__).'/Fixtures/UserFixtures.yaml',
        ]);

        $userRepo = self::getContainer()->get(UserRepository::class);

        $users = $userRepo->findAll();

        $this->assertCount(6, $users);
    }

    private function getEntity(): User
    {
        return (new User())
            ->setEmail('test@test.com')
            ->setFirstName('test')
            ->setLastName('test')
            ->setPassword('Test1234!')
            ->setBirthDate(new \DateTime());
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    /**
     * @dataProvider provideEmail
     */
    public function testInvalidEmail(string $email): void
    {
        $user = $this->getEntity()
            ->setEmail($email);

        $this->assertHasErrors($user, 1);
    }

    /**
     * @dataProvider providePassword
     */
    public function testInvalidPassword(string $password): void
    {
        $user = $this->getEntity()
            ->setPassword($password);

        $this->assertHasErrors($user, 1);
    }

    /**
     * @dataProvider provideName
     */
    public function testInvalideFirstName(string $name): void
    {
        $user = $this->getEntity()
            ->setFirstName($name);

        $this->assertHasErrors($user, 1);
    }

    /**
     * @dataProvider provideName
     */
    public function testInvalideLastName(string $name): void
    {
        $user = $this->getEntity()
            ->setLastName($name);

        $this->assertHasErrors($user, 1);
    }

    public function testfindAllWithPagination(): void
    {
        $repo = self::getContainer()->get(UserRepository::class);

        $users = $repo->findAllWithPagination(1, 6);

        $this->assertCount(6, $users);
    }

    public function testfindAllWithPaginationWithInvalidArgument(): void
    {
        $repo = self::getContainer()->get(UserRepository::class);

        $this->expectException(\TypeError::class);

        $repo->findAllWithPagination('test', 6);
    }

    public function provideEmail(): array
    {
        return [
            'non_unique' => [
                'email' => 'admin@test.com',
            ],
            'max_length' => [
                'email' => str_repeat('a', 180).'@test.com',
            ],
            'empty' => [
                'email' => '',
            ],
            'invalid' => [
                'email' => 'test.com',
            ],
        ];
    }

    public function providePassword(): array
    {
        return [
            'min_length' => [
                'password' => 'Test12!',
            ],
            'without_number' => [
                'password' => 'Testtest!',
            ],
            'without_uppercase' => [
                'password' => 'test123!',
            ],
            'without_special_character' => [
                'password' => 'Test1234',
            ],
            'empty' => [
                'password' => '',
            ],
        ];
    }

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
