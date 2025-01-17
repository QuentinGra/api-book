<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/book', name: 'api.book')]
#[OA\Tag(name: 'Book')]
class BookController extends AbstractController
{
    public function __construct(
        private readonly BookRepository $bookRepo,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {}

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

    #[Route('/{id<\d+>}', name: '.show', methods: ['GET'])]
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

    #[Route('/latest', name: '.latest', methods: ['GET'])]
    public function latest(): JsonResponse
    {
        $latestBooks = $this->bookRepo->findBy([], ['createdAt' => 'DESC'], 3);

        return $this->json($latestBooks, 200, [], [
            'groups' => ['book:read', 'app:read'],
        ]);
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        Book $book,
    ): JsonResponse {

        $this->em->persist($book);
        $this->em->flush();

        return $this->json($book, 201, [], [
            'groups' => ['book:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?Book $book): JsonResponse
    {
        if (!$book) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $book = $this->serializer->deserialize($request->getContent(), Book::class, 'json', [
            'object_to_populate' => $book,
        ]);

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

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(?Book $book): JsonResponse
    {
        if (!$book) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $this->em->remove($book);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Book deleted',
        ], 200);
    }
}
