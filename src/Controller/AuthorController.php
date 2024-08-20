<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/author', name: 'api.author')]
#[OA\Tag(name: 'Author')]
class AuthorController extends AbstractController
{
    public function __construct(
        private readonly AuthorRepository $authorRepo,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->authorRepo->findAll(),
            200,
            [],
            [
                'groups' => ['author:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    public function show(?Author $author): JsonResponse
    {
        if (!$author) {
            return $this->json([
                'status' => 'error',
                'message' => 'Author not found',
            ], 404);
        }

        return $this->json($author, 200, [], [
            'groups' => ['author:read', 'app:read'],
        ]);
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        Author $author,
        #[MapUploadedFile]
        array|UploadedFile $image
    ): JsonResponse {
        if (!$image instanceof UploadedFile) {
            return $this->json([
                'status' => 'error',
                'message' => 'Image not found',
            ], 404);
        }

        $author->setImage($image);

        $this->em->persist($author);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Author created',
        ], 201);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?Author $author): JsonResponse
    {
        if (!$author) {
            return $this->json([
                'status' => 'error',
                'message' => 'Author not found',
            ], 404);
        }

        $author = $this->serializer->deserialize($request->getContent(), Author::class, 'json', [
            'object_to_populate' => $author,
        ]);

        $errors = $this->validator->validate($author);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($author);
        $this->em->flush();

        return $this->json($author, 201, [], [
            'groups' => ['author:read', 'app:read'],
        ]);
    }

    #[Route('/{id}/image', name: '.update_image', methods: ['POST'])]
    public function updateImage(
        ?Author $author,
        #[MapUploadedFile()]
        array|UploadedFile $image,
    ): JsonResponse {
        if (!$author) {
            return $this->json([
                'status' => 'error',
                'message' => 'Author not found',
            ], 404);
        }

        if (!$image instanceof UploadedFile) {
            return $this->json([
                'status' => 'error',
                'message' => 'Image not found',
            ], 404);
        }

        $author->setImage($image);

        $errors = $this->validator->validate($author);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($author);
        $this->em->flush();

        return $this->json($author, 201, [], [
            'groups' => ['author:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete one author',
        tags: ['Author'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Author delete successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Author not found',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
        ]
    )]
    public function delete(?Author $author): JsonResponse
    {
        if (!$author) {
            return $this->json([
                'status' => 'error',
                'message' => 'Author not found',
            ], 404);
        }

        $this->em->remove($author);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Author deleted',
        ], 200);
    }
}
