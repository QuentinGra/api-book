<?php

namespace App\Serializer;

use App\Entity\ReadingListBook;
use App\Repository\BookRepository;
use App\Repository\ReadingListBookRepository;
use App\Repository\ReadingListRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

class ReadingListBookNormalizer implements DenormalizerInterface
{
    public function __construct(
        private BookRepository $bookRepo,
        private ReadingListRepository $readingListRepo,
        private ReadingListBookRepository $readingListBookRepo,
        private DenormalizerInterface $normalizer,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        /** @var ReadingListBook $readingListBook */
        $readingListBook = $this->normalizer->denormalize($data, $type, $format, $context);

        if (!isset($context['object_to_populate'])) {
            Assert::nullOrKeyExists($data, 'book', 'Missing required book key');
            Assert::nullOrKeyExists($data, 'readingList', 'Missing required readingList key');

            $readingList = $this->readingListRepo->find($data['readingList']);
            $book = $this->bookRepo->find($data['book']);

            Assert::notNull($book, 'Book not found');
            Assert::notNull($readingList, 'List not found');
            Assert::isEmpty($this->readingListBookRepo->findBy([
                'readingList' => $readingList,
                'book' => $book,
            ]), 'Book already in list');
            $readingListBook->setReadingList($readingList);
            $readingListBook->setBook($book);
        } else {
            Assert::keyNotExists($data, 'book', 'The key book is not allowed');
            Assert::keyNotExists($data, 'readingList', 'The key readingList is not allowed');
        }

        return $readingListBook;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return ReadingListBook::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ReadingListBook::class => true,
        ];
    }
}
