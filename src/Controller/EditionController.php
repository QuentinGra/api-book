<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Repository\EditionRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        Edition $edition,
    ): JsonResponse {
        $errors = $this->validator->validate($edition);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($edition);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Edition created',
        ], 201);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?Edition $edition): JsonResponse
    {
        if (!$edition) {
            return $this->json([
                'status' => 'error',
                'message' => 'Edition not found',
            ], 404);
        }

        $edition = $this->serializer->deserialize($request->getContent(), Edition::class, 'json', [
            'object_to_populate' => $edition,
        ]);

        $errors = $this->validator->validate($edition);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($edition);
        $this->em->flush();

        return $this->json($edition, 201, [], [
            'groups' => ['edition:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(?Edition $edition): JsonResponse
    {
        if (!$edition) {
            return $this->json([
                'status' => 'error',
                'message' => 'Edition not found',
            ], 404);
        }

        $this->em->remove($edition);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Edition deleted',
        ], 200);
    }
}
