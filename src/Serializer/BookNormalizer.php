<?php

namespace App\Serializer;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\EditionRepository;
use App\Repository\CategoryRepository;
use App\Repository\BookVariantRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BookNormalizer implements DenormalizerInterface
{
    public function __construct(
        private EditionRepository $editionRepo,
        private AuthorRepository $authorRepo,
        private CategoryRepository $categoryRepo,
        private BookVariantRepository $bookVariantRepo,
        private DenormalizerInterface $normalizer,
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $categories = [];
        $bookVariants = [];

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

        if (isset($data['bookVariants'])) {
            foreach ($data['bookVariants'] as $bookVariantId) {
                $bookVariant = $this->bookVariantRepo->find($bookVariantId);

                if ($bookVariant) {
                    $bookVariants[] = $bookVariant;
                }
            }
            unset($data['bookVariants']);
        }

        $book = $this->normalizer->denormalize($data, $type, $format, $context);

        if (!isset($context['object_to_populate']) || isset($data['edition'])) {
            $book->setEdition(isset($edition) ? $edition : null);
        }

        if (!isset($context['object_to_populate']) || isset($data['author'])) {
            $book->setAuthor(isset($author) ? $author : null);
        }

        foreach ($categories as $category) {
            $book->addCategory($category);
        }

        foreach ($bookVariants as $bookVariant) {
            $book->addBookVariant($bookVariant);
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
