<?php

namespace App\Controller;

use App\Entity\Book;
use OpenApi\Attributes as OA;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/book', name: 'api.book')]
#[OA\Tag(name: 'Book')]
class BookController extends AbstractController
{
    public function __construct(
        private readonly BookRepository $bookRepo,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->bookRepo->findAll(),
            200,
            [],
            [
                'groups' => ['book:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    public function show(?Book $book): JsonResponse
    {
        if (!$book) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        return $this->json($book, 200, [], [
            'groups' => ['book:read', 'app:read'],
        ]);
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        //#[MapRequestPayload]
        Book $book,
    ): JsonResponse {
        dd($book);
        $errors = $this->validator->validate($book);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($book);
        $this->em->flush();

        return $this->json($book, 201, [], [
            'groups' => ['book:read', 'app:read'],
        ]);
    }
}
