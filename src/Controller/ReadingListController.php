<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Entity\ReadingList;
use OpenApi\Attributes as OA;
use App\Security\Voter\UserApiVoter;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReadingListRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

#[Route('/api/list', name: 'api.list')]
#[OA\Tag(name: 'List')]
class ReadingListController extends AbstractController
{
    public function __construct(
        private readonly ReadingListRepository $readingListRepo,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $readingLists = $this->readingListRepo->findBy([
            'user' => $user,
        ]);

        return $this->json(
            $readingLists,
            200,
            [],
            [
                'groups' => ['readingList:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}/{status}', name: '.show', methods: ['GET'], defaults: ['status' => null])]
    #[IsGranted('READING_LIST_OWNER', 'readingList', 'List not found', 404)]
    /**
     * Show all books in a reading list.
     *
     * @param iterable $readingList
     */
    public function show(
        #[MapEntity(class: Book::class, expr: 'repository.findBooksByReadingList(id, status)')]
        iterable $books,
        ?ReadingList $readingList,
    ): JsonResponse {

        if (!$books) {
            return $this->json([
                'status' => 'error',
                'message' => 'there are no books in the reading list',
            ], 404);
        }

        return $this->json($books, 200, [], [
            'groups' => ['readingList:read', 'app:read'],
        ]);
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        ReadingList $readingList,
    ): JsonResponse {
        $user = $this->getUser();
        $existingReadingLists = $this->readingListRepo->count([
            'user' => $user,
        ]);

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        if ($existingReadingLists >= 10) {
            return $this->json([
                'status' => 'error',
                'message' => 'You can only have 10 reading lists',
            ], 400);
        }

        $errors = $this->validator->validate($readingList);

        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $readingList->setUser($user);
        $this->em->persist($readingList);
        $this->em->flush();

        return $this->json($readingList, 201, [], [
            'groups' => ['readingList:read', 'app:read'],
        ]);
    }
}
