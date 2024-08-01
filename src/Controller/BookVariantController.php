<?php

namespace App\Controller;

use App\Entity\BookVariant;
use App\Repository\BookVariantRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/book-variant', name: 'api.book-variant')]
#[OA\Tag(name: 'BookVariant')]
class BookVariantController extends AbstractController
{
    public function __construct(
        private readonly BookVariantRepository $bookVariantRepo,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->bookVariantRepo->findAll(),
            200,
            [],
            [
                'groups' => ['bookVariant:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    public function show(?BookVariant $bookVariant): JsonResponse
    {
        if (!$bookVariant) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        return $this->json($bookVariant, 200, [], [
            'groups' => ['bookVariant:read', 'app:read']
        ]);
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        BookVariant $bookVariant,
    ): JsonResponse {
        $errors = $this->validator->validate($bookVariant);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($bookVariant);
        $this->em->flush();

        return $this->json($bookVariant, 201, [], [
            'groups' => ['bookVariant:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?BookVariant $bookVariant): JsonResponse
    {
        if (!$bookVariant) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $bookVariant = $this->serializer->deserialize($request->getContent(), BookVariant::class, 'json', [
            'object_to_populate' => $bookVariant,
        ]);

        $errors = $this->validator->validate($bookVariant);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($bookVariant);
        $this->em->flush();

        return $this->json($bookVariant, 201, [], [
            'groups' => ['bookVariant:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(?BookVariant $bookVariant): JsonResponse
    {
        if (!$bookVariant) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $this->em->remove($bookVariant);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'BookVariant deleted',
        ], 200);
    }
}
