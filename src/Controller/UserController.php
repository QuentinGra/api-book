<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user', name: 'api.user')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly EntityManagerInterface $em,
        private readonly  UserPasswordHasherInterface $hasher,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->userRepo->findAll(),
            200,
            [],
            [
                'groups' => ['user:read', 'app:read'],
            ]
        );
    }

    #[Route('/{id}', name: '.show', methods: ['GET'])]
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
    public function create(Request $request): JsonResponse
    {
        $userData = $request->getContent();
        $user = $this->serializer->deserialize($userData, User::class, 'json');

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $user->setPassword(
            $this->hasher->hashPassword($user, $user->getPassword())
        );

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($user, 201, [], [
            'groups' => ['user:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    public function update(?User $user, Request $request): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
    }
}
