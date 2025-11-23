<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class NotationTest.
 *
 * Unit tests for the {@see RatingHandler::countRatingsPerValue()} method.
 *
 * This test suite verifies that the rating distribution (number of ratings per value)
 * is correctly computed and updated on the {@see VideoGame} entity.
 *
 * @covers \App\Rating\RatingHandler::countRatingsPerValue
 */
final class NotationTest extends TestCase
{
    /**
     * The instance of the class under test.
     */
    private RatingHandler $handler;

    /**
     * Sets up the test environment before each test method runs.
     *
     * This method initializes a new {@see RatingHandler} instance
     * to ensure test isolation.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new RatingHandler();
    }

    /**
     * Verifies that {@see RatingHandler::countRatingsPerValue()} correctly updates
     * the {@see NumberOfRatingPerValue} object on a {@see VideoGame}.
     *
     * The method is tested against multiple scenarios provided by
     * {@see self::videoGamesProvider()}:
     *  - When no reviews exist, all counts should remain zero.
     *  - When one review exists, the corresponding rating value should be incremented.
     *  - When multiple reviews exist, each value (1–5) should be properly counted.
     *
     * @dataProvider videoGamesProvider
     *
     * @param VideoGame              $videoGame      the video game being tested
     * @param NumberOfRatingPerValue $expectedCounts the expected count object after computation
     */
    public function testCountRatingsPerValueUpdatesExpectedCounts(
        VideoGame $videoGame,
        NumberOfRatingPerValue $expectedCounts,
    ): void {
        // Act
        $this->handler->countRatingsPerValue($videoGame);

        // Assert
        self::assertEquals($expectedCounts, $videoGame->getNumberOfRatingsPerValue());
    }

    /**
     * Provides various test cases for the rating count computation.
     *
     * Each dataset includes a {@see VideoGame} with predefined reviews
     * and the corresponding expected {@see NumberOfRatingPerValue} state.
     *
     * @return iterable<array{0: VideoGame, 1: NumberOfRatingPerValue}>
     */
    public static function videoGamesProvider(): iterable
    {
        yield 'no_review_returns_empty_counts' => [
            new VideoGame(),
            new NumberOfRatingPerValue(),
        ];

        yield 'single_review_counts_correct_value' => [
            self::makeVideoGameWithRatings(5),
            self::makeExpectedCounts(five: 1),
        ];

        yield 'multiple_reviews_counts_each_value' => [
            self::makeVideoGameWithRatings(1, 2, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5),
            self::makeExpectedCounts(one: 1, two: 2, three: 3, four: 4, five: 5),
        ];
    }

    /**
     * Helper factory method that builds a {@see VideoGame} entity
     * pre-populated with {@see Review} objects containing the given ratings.
     *
     * @param int ...$ratings  The rating values to assign to each review (1–5).
     *
     * @return VideoGame  the constructed video game entity
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
     * Helper factory method that constructs an expected {@see NumberOfRatingPerValue}
     * instance with the given counts for each rating level.
     *
     * Each count is applied by invoking the appropriate increment method
     * (`increaseOne`, `increaseTwo`, etc.), mimicking real behavior.
     *
     * @param int $one   the number of "1-star" ratings
     * @param int $two   the number of "2-star" ratings
     * @param int $three the number of "3-star" ratings
     * @param int $four  the number of "4-star" ratings
     * @param int $five  the number of "5-star" ratings
     *
     * @return NumberOfRatingPerValue  the expected counter state
     */
    private static function makeExpectedCounts(
        int $one = 0,
        int $two = 0,
        int $three = 0,
        int $four = 0,
        int $five = 0,
    ): NumberOfRatingPerValue {
        $counts = new NumberOfRatingPerValue();

        for ($i = 0; $i < $one; ++$i) {
            $counts->increaseOne();
        }

        for ($i = 0; $i < $two; ++$i) {
            $counts->increaseTwo();
        }

        for ($i = 0; $i < $three; ++$i) {
            $counts->increaseThree();
        }

        for ($i = 0; $i < $four; ++$i) {
            $counts->increaseFour();
        }

        for ($i = 0; $i < $five; ++$i) {
            $counts->increaseFive();
        }

        return $counts;
    }
}
