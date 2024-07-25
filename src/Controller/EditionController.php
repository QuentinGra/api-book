<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Repository\EditionRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/edition', name: 'api.edition')]
#[OA\Tag(name: 'Edition')]
class EditionController extends AbstractController
{
    public function __construct(
        private readonly EditionRepository $editionRepo,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // TODO: Modification findAll -> findAllWithPagination

        return $this->json(
            $this->editionRepo->findAll(),
            200,
            [],
            [
                'groups' => ['edition:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    public function show(?Edition $edition): JsonResponse
    {
        if (!$edition) {
            return $this->json([
                'status' => 'error',
                'message' => 'Edition not found',
            ], 404);
        }

        return $this->json($edition, 200, [], [
            'groups' => ['edition:read', 'app:read'],
        ]);
    }
}
