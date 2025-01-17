<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
    ) {}

    #[Route('', name: '.index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/{id<\d+>}', name: '.show', methods: ['GET'])]
    #[IsGranted('USER_OWNER', 'user', 'User not found', 404)]
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
    public function create(
        #[MapRequestPayload]
        User $user,
    ): JsonResponse {

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

    #[Route('/{id<\d+>}', name: '.update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('USER_OWNER', 'user', 'User not found', 404)]
    public function update(Request $request, ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $userData = json_decode($request->getContent(), true);

        $this->serializer->deserialize(json_encode($userData), User::class, 'json', [
            'object_to_populate' => $user,
        ]);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        if (isset($userData['password'])) {
            $hashedPassword = $this->hasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($user, 201, [], [
            'groups' => ['user:read', 'app:read'],
        ]);
    }

    #[Route('/{id<\d+>}', name: '.delete', methods: ['DELETE'])]
    #[IsGranted('USER_OWNER', 'user', 'User not found', 404)]
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
            'message' => 'User deleted',
        ], 200);
    }

    #[Route('/me', name: '.me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        return $this->json($user, 200, [], [
            'groups' => ['user:read', 'app:read'],
        ]);
    }

    #[Route('/logout', name: 'api.user.logout', methods: ['GET'])]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse([
            'status' => 'success',
            'message' => 'User logged out',
        ], 200);

        // Supprimez le cookie en définissant une date d'expiration passée
        $response->headers->clearCookie('BEARER', '/', null, true, true);

        return $response;
    }
}
