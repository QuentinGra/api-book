<?php

namespace App\Serializer;

use App\Entity\Rating;
use App\Repository\BookRepository;
use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use Webmozart\Assert\Assert;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RatingNormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly BookRepository $bookRepo,
        private readonly UserRepository $userRepo,
        private readonly RatingRepository $ratingRepo,
        private readonly DenormalizerInterface $normalizer,
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        /** @var Rating $rating */
        $rating = $this->normalizer->denormalize($data, $type, $format, $context);

        if (!isset($context['object_to_populate'])) {
            Assert::nullOrKeyExists($data, 'book', 'Missing required book key');
            Assert::nullOrKeyExists($data, 'user', 'Missing required user key');

            $book = $this->bookRepo->find($data['book']);
            $user = $this->userRepo->find($data['user']);

            Assert::notNull($book, 'Book not found');
            Assert::notNull($user, 'User not found');
            Assert::isEmpty($this->ratingRepo->findBy([
                'book' => $book,
                'user' => $user,
            ]), 'You\'ve already rated this book');
            $rating->setBook($book);
            $rating->setUser($user);
        } else {
            Assert::keyNotExists($data, 'book', 'The key book is not allowed');
            Assert::keyNotExists($data, 'user', 'The key user is not allowed');
        }

        return $rating;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Rating::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Rating::class => true,
        ];
    }
}
