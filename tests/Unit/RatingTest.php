<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class RatingTest
 *
 * Unit tests for the {@see RatingHandler} component.
 *
 * This test suite ensures that:
 * - The average rating of a {@see VideoGame} is correctly calculated
 *   based on its associated {@see Review} objects.
 * - The average is properly rounded according to the expected logic.
 *
 * @covers \App\Rating\RatingHandler
 */
final class RatingTest extends TestCase
{
    /**
     * The instance of the class under test.
     *
     * @var RatingHandler
     */
    private RatingHandler $handler;

    /**
     * Sets up the test environment before each test method runs.
     *
     * This method instantiates a fresh {@see RatingHandler}
     * for isolation between tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new RatingHandler();
    }

    /**
     * Ensures that the average rating calculation produces the expected result.
     *
     * This test uses a data provider to test multiple cases:
     * - A video game with no reviews (expected null average)
     * - A video game with a single review
     * - A video game with multiple reviews producing an integer average
     *
     * @dataProvider videoGamesProvider
     *
     * @param VideoGame $videoGame        The video game to test.
     * @param int|null  $expectedAverage  The expected average rating value.
     *
     * @return void
     */
    public function testShouldCalculateAverageRating(VideoGame $videoGame, ?int $expectedAverage): void
    {
        // Act
        $this->handler->calculateAverage($videoGame);

        // Assert
        self::assertSame($expectedAverage, $videoGame->getAverageRating());
    }

    /**
     * Provides different test cases for average rating calculation.
     *
     * Each dataset includes a {@see VideoGame} instance and the expected average value.
     *
     * @return iterable<array{0: VideoGame, 1: int|null}>
     */
    public static function videoGamesProvider(): iterable
    {
        yield 'no_review_returns_null' => [
            new VideoGame(),
            null,
        ];

        yield 'single_review_returns_same_value' => [
            self::makeVideoGameWithRatings(5),
            5,
        ];

        yield 'multiple_reviews_returns_integer_average' => [
            self::makeVideoGameWithRatings(
                1,
                2, 2,
                3, 3, 3,
                4, 4, 4, 4,
                5, 5, 5, 5, 5
            ),
            4,
        ];
    }

    /**
     * Helper factory that creates a {@see VideoGame} instance
     * pre-populated with reviews having the specified rating values.
     *
     * @param int ...$ratings  The rating values to assign to each review.
     *
     * @return VideoGame  The populated video game entity.
     */
    private static function makeVideoGameWithRatings(int ...$ratings): VideoGame
    {
        $videoGame = new VideoGame();

        foreach ($ratings as $rating) {
            $review = (new Review())->setRating($rating);
            $videoGame->getReviews()->add($review);
        }

        return $videoGame;
    }

    /**
     * Ensures that the calculated average rating is correctly rounded
     * when the computed average value contains a decimal component.
     *
     * Example:
     * - Reviews with ratings 4 and 5 produce an average of 4.5
     * - The expected rounded result is 5
     *
     * @return void
     */
    public function test_calculateAverage_rounds_decimal_average(): void
    {
        // Arrange
        $videoGame = self::makeVideoGameWithRatings(4, 5);
        $this->handler = new RatingHandler();

        // Act
        $this->handler->calculateAverage($videoGame);

        // Assert
        self::assertSame(5, $videoGame->getAverageRating());
    }
}
