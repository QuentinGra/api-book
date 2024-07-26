<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/category', name: 'api.category')]
#[OA\Tag(name: 'Category')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepo,
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
            $this->categoryRepo->findAll(),
            200,
            [],
            [
                'groups' => ['category:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    public function show(?Category $category): JsonResponse
    {
        if (!$category) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        return $this->json($category, 200, [], [
            'groups' => ['category:read', 'app:read'],
        ]);
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        Category $category,
    ): JsonResponse {
        $errors = $this->validator->validate($category);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Category created',
        ], 201);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?Category $category): JsonResponse
    {
        if (!$category) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json', [
            'object_to_populate' => $category,
        ]);

        $errors = $this->validator->validate($category);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->json($category, 201, [], [
            'groups' => ['category:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(?Category $category): JsonResponse
    {
        if (!$category) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $this->em->remove($category);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Category deleted',
        ], 200);
    }
}
