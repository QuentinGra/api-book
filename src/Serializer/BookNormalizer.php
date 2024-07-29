<?php

namespace App\Serializer;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\EditionRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BookNormalizer implements DenormalizerInterface
{
    public function __construct(
        private EditionRepository $editionRepo,
        private AuthorRepository $authorRepo,
        private CategoryRepository $categoryRepo,
        private DenormalizerInterface $normalizer,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $categories = [];

        if (isset($data['edition'])) {
            $edition = $this->editionRepo->find($data['edition']);
        }

        if (isset($data['author'])) {
            $author = $this->authorRepo->find($data['author']);
        }

        if (isset($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {
                $category = $this->categoryRepo->find($categoryId);

                if ($category) {
                    $categories[] = $category;
                }
            }
            unset($data['categories']);
        }

        $book = $this->normalizer->denormalize($data, $type, $format, $context);
        $book->setEdition(isset($edition) ? $edition : null)
            ->setAuthor(isset($author) ? $author : null);

        foreach ($categories as $category) {
            $book->addCategory($category);
        }

        return $book;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Book::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Book::class => true,
        ];
    }
}
