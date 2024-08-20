<?php

namespace App\Controller;

use App\Entity\ReadingListBook;
use App\Repository\ReadingListBookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/list/book', name: 'api.list.book')]
class ReadingListBookController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        private readonly ReadingListBookRepository $readingListBookRepo,
    ) {
    }

    #[Route('/add', name: '.add', methods: ['POST'])]
    public function addBook(
        #[MapRequestPayload]
        ReadingListBook $readingListBook,
    ): JsonResponse {
        $errors = $this->validator->validate($readingListBook);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($readingListBook);
        $this->em->flush();

        return $this->json($readingListBook, 201, [], [
            'groups' => ['readingListBook:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('READING_LIST_OWNER', 'readingListBook', 'Book not found', 404)]
    public function updateStatus(Request $request, ?ReadingListBook $readingListBook): JsonResponse
    {
        if (!$readingListBook) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $readingListBook = $this->serializer->deserialize($request->getContent(), ReadingListBook::class, 'json', [
            'object_to_populate' => $readingListBook,
        ]);

        $errors = $this->validator->validate($readingListBook);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($readingListBook);
        $this->em->flush();

        return $this->json($readingListBook, 200, [], [
            'groups' => ['readingListBook:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    #[IsGranted('READING_LIST_OWNER', 'readingListBook', 'Book not found', 404)]
    public function removeBook(?ReadingListBook $readingListBook): JsonResponse
    {
        if (!$readingListBook) {
            return $this->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $this->em->remove($readingListBook);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Book removed from the list',
        ], 200);
    }
}
