<?php

namespace App\Serializer;

use App\Entity\BookImage;
use App\Repository\BookRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BookImageNormalizer implements DenormalizerInterface
{
    public function __construct(
        private BookRepository $bookRepo,
        private DenormalizerInterface $normalizer,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (isset($data['book'])) {
            $book = $this->bookRepo->find($data['book']);
        }

        $bookImage = $this->normalizer->denormalize($data, $type, $format, $context);

        if (!isset($context['object_to_populate']) || isset($data['book'])) {
            $bookImage->setBook(isset($book) ? $book : null);
        }

        return $bookImage;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return BookImage::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BookImage::class => true,
        ];
    }
}
