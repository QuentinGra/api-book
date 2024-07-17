<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user', name: 'api.user')]
#[OA\Tag(name: 'User')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('', name: '.index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user',
        summary: 'List all users',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'Page',
                in: 'path',
                description: 'Page',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'Limit',
                in: 'path',
                description: 'Number of users per page',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Response(
                response: 200,
                description: 'Return all users',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'array',
                        description: 'An array of users',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'email', type: 'string'),
                                new OA\Property(
                                    property: 'roles',
                                    type: 'array',
                                    items: new OA\Items(type: 'string')
                                ),
                                new OA\Property(property: 'firstName', type: 'string'),
                                new OA\Property(property: 'lastName', type: 'string'),
                                new OA\Property(property: 'birthDate', type: 'date'),
                                new OA\Property(property: 'createdAt', type: 'datetime'),
                                new OA\Property(property: 'updatedAt', type: 'datetime'),
                            ]
                        )
                    )
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        return $this->json(
            $this->userRepo->findAllWithPagination($page, $limit),
            200,
            [],
            [
                'groups' => ['user:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/{id}',
        summary: 'List one user by id',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'User id',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Response(
                response: 200,
                description: 'Return user',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string')
                        ),
                        new OA\Property(property: 'firstName', type: 'string'),
                        new OA\Property(property: 'lastName', type: 'string'),
                        new OA\Property(property: 'birthDate', type: 'date'),
                        new OA\Property(property: 'createdAt', type: 'datetime'),
                        new OA\Property(property: 'updatedAt', type: 'datetime'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
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
    public function show(?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return $this->json($user, 200, [], [
            'groups' => ['user:read', 'app:read'],
        ]);
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/create',
        summary: 'Create a new user',
        tags: ['User'],
        requestBody: new OA\RequestBody(
            description: 'User data to create a new user',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['email', 'password'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'password', type: 'string'),
                        new OA\Property(property: 'firstName', type: 'string'),
                        new OA\Property(property: 'lastName', type: 'string'),
                        new OA\Property(property: 'birthDate', type: 'date'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'field', type: 'string', description: 'Field with validation error'),
                                new OA\Property(property: 'message', type: 'string', description: 'Validation error message'),
                            ]
                        )
                    )
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $user->setPassword(
            $this->hasher->hashPassword($user, $user->getPassword())
        );

        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'User created',
        ], 201);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    // TODO: Documenter les routes PUT et PATCH
    public function update(Request $request, ?User $user): JsonResponse
    {
        // FIXME: Refactoriser et Sécuriser, seul l'utilisateur peut modifier sont mot de passe

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $userData = json_decode($request->getContent(), true);

        // Vérifier et hacher le mot de passe si présent
        if (isset($userData['password'])) {
            $hashedPassword = $this->hasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
            // Supprimer le mot de passe du tableau pour éviter la désérialisation sur ce champ
            unset($userData['password']);
        }

        // Désérialiser les autres champs
        $this->serializer->deserialize(json_encode($userData), User::class, 'json', [
            'object_to_populate' => $user,
        ]);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($user, 201, [], [
            'groups' => ['user:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/user/{id}',
        summary: 'Delete a new user',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'User id',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Response(
                response: 204,
                description: 'User delete successfully',
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
                description: 'User not found',
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
    public function delete(?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $this->em->remove($user);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Gender deleted',
        ], 204);
    }
}
