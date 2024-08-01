<?php

namespace App\Controller;

use App\Entity\BookImage;
use App\Repository\BookImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/book-image', name: 'api.book-image')]
#[OA\Tag(name: 'BookImage')]
class BookImageController extends AbstractController
{
    public function __construct(
        private readonly BookImageRepository $bookImageRepo,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {
    }

    // TODO: Route show ou modification de la route index.

    #[Route('', name: '.index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->bookImageRepo->findAll(),
            200,
            [],
            [
                'groups' => ['bookImage:read', 'app:read']
            ]
        );
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        BookImage $bookImage,
        #[MapUploadedFile]
        array|UploadedFile $image
    ): JsonResponse {

        if (!$image instanceof UploadedFile) {
            return $this->json([
                'status' => 'error',
                'message' => 'Image not found',
            ], 404);
        }

        $bookImage->setImage($image);

        $errors = $this->validator->validate($bookImage);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($bookImage);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Image add',
        ], 201);
    }

    // FIXME: Supprime en base de donnée mais renvoie 500 "Expected argument of type "string" "null" given"

    #[Route('/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(?BookImage $bookImage): JsonResponse
    {
        if (!$bookImage) {
            return $this->json([
                'status' => 'error',
                'message' => 'Image not found',
            ], 404);
        }

        $this->em->remove($bookImage);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Image deleted',
        ], 200);
    }
}
