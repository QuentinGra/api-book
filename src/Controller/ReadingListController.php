<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\ReadingList;
use App\Repository\ReadingListRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    #[IsGranted('READING_LIST_OWNER', 'readingList', 'List not found', 404)]
    public function show(?ReadingList $readingList): JsonResponse
    {
        if (!$readingList) {
            return $this->json([
                'status' => 'error',
                'message' => 'Reading list not found',
            ], 404);
        }

        return $this->json($readingList, 200, [], [
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

        $readingList->setUser($user);
        $this->em->persist($readingList);
        $this->em->flush();

        return $this->json($readingList, 201, [], [
            'groups' => ['readingList:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('READING_LIST_OWNER', 'readingList', 'List not found', 404)]
    public function update(Request $request, ?ReadingList $readingList): JsonResponse
    {
        if (!$readingList) {
            return $this->json([
                'status' => 'error',
                'message' => 'Reading list not found',
            ], 404);
        }

        $readingList = $this->serializer->deserialize($request->getContent(), ReadingList::class, 'json', [
            'object_to_populate' => $readingList,
        ]);

        $errors = $this->validator->validate($readingList);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($readingList);
        $this->em->flush();

        return $this->json($readingList, 200, [], [
            'groups' => ['readingList:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    #[IsGranted('READING_LIST_OWNER', 'readingList', 'List not found', 404)]
    public function delete(?ReadingList $readingList): JsonResponse
    {
        if (!$readingList) {
            return $this->json([
                'status' => 'error',
                'message' => 'Reading list not found',
            ], 404);
        }

        $this->em->remove($readingList);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Reading list deleted',
        ], 200);
    }
}
