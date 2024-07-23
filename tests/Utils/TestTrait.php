<?php

namespace App\Tests\Utils;

trait TestTrait
{
    public function assertHasErrors(mixed $entity, int $number = 0): void
    {
        self::bootKernel();

        // On test l'netité avec validator
        $errors = self::getContainer()->get('validator')->validate($entity);

        // On instancie un tableau vide pour stocker les erreurs
        $messageErrors = [];

        // On boucle sur les erreurs
        foreach ($errors as $error) {
            $messageErrors[] = $error->getPropertyPath().' => '.$error->getMessage();
        }

        $this->assertCount($number, $errors, implode(', ', $messageErrors));
    }
}
