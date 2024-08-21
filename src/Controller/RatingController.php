<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/rating', name: 'api.rating')]
class RatingController extends AbstractController
{
    public function __construct(
        private readonly RatingRepository $ratingRepo,
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

        $ratings = $this->ratingRepo->findBy([
            'user' => $user,
        ]);
        return $this->json(
            $ratings,
            200,
            [],
            [
                'groups' => ['rating:read', 'app:read'],
            ]
        );
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        Rating $rating
    ): JsonResponse {

        $this->em->persist($rating);
        $this->em->flush();

        return $this->json($rating, 201, [], [
            'groups' => ['rating:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('RATING_OWNER', 'rating', 'Rating not found', 404)]
    public function update(Request $request, ?Rating $rating): JsonResponse
    {
        if (!$rating) {
            return $this->json([
                'status' => 'error',
                'message' => 'Rating not found',
            ], 404);
        }

        $rating = $this->serializer->deserialize($request->getContent(), Rating::class, 'json', [
            'object_to_populate' => $rating,
        ]);

        $errors = $this->validator->validate($rating);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($rating);
        $this->em->flush();

        return $this->json($rating, 200, [], [
            'groups' => ['rating:read', 'app:read'],
        ]);
    }

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    #[IsGranted('RATING_OWNER', 'rating', 'Rating not found', 404)]
    public function delete(?Rating $rating): JsonResponse
    {
        if (!$rating) {
            return $this->json([
                'status' => 'error',
                'message' => 'Rating not found',
            ], 404);
        }

        $this->em->remove($rating);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Rating deleted',
        ], 200);
    }
}
