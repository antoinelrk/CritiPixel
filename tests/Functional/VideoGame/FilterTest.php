<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

/**
 * Class FilterTest
 *
 * Functional tests for the video games listing page:
 * - Pagination & offsets rendering
 * - Sorting (via form submission)
 * - Filtering by search and by tags (via form submission)
 *
 * The tests assert both the HTTP responses and the DOM content using CSS selectors.
 *
 * @group functional
 * @coversNothing
 */
final class FilterTest extends FunctionalTestCase
{
    /**
     * Provides use-case datasets to validate pagination, offsets and listing content.
     *
     * Each dataset is an array-shaped payload containing:
     *  - query: Query parameters to send with the GET request.
     *  - expectedCount: Expected number of cards on the page.
     *  - expectedOffsetFrom / expectedOffsetTo: Expected "from/to" counters in the summary.
     *  - expectedTotal: Expected total number of video games.
     *  - expectedPage: Expected current page (null when no pagination should be shown).
     *  - expectedPaginationLinks: Labels of pagination links expected to be present.
     *  - expectedVideoGames: Expected ordered titles for the cards on the page.
     *
     * @return iterable<string, array{
     *     query: array<string, mixed>,
     *     expectedCount: int,
     *     expectedOffsetFrom: int,
     *     expectedOffsetTo: int,
     *     expectedTotal: int,
     *     expectedPage: int|null,
     *     expectedPaginationLinks: string[],
     *     expectedVideoGames: string[]
     * }>
     */
    public static function provideUseCases(): iterable
    {
        yield 'First page' => self::createUseCase();

        yield 'Page #2' => self::createUseCase(
            query: ['page' => 2],
            expectedOffsetFrom: 11,
            expectedOffsetTo: 20,
            expectedPage: 2,
            expectedPaginationLinks: ['1', '2', '3', '4', '5'],
        );

        yield 'Last page' => self::createUseCase(
            query: ['page' => 5],
            expectedOffsetFrom: 41,
            expectedOffsetTo: 50,
            expectedPage: 5,
            expectedPaginationLinks: ['2', '3', '4', '5'],
        );

        yield 'First page, limit at 25' => self::createUseCase(
            query: ['limit' => 25],
            expectedCount: 25,
            expectedOffsetTo: 25,
            expectedPaginationLinks: ['1', '2'],
        );

        yield 'First page, limit at 50' => self::createUseCase(
            query: ['limit' => 50],
            expectedCount: 50,
            expectedOffsetTo: 50,
            expectedPage: null,
        );

        yield 'First page, sorting by title' => self::createUseCase(
            query: ['sorting' => 'Title'],
            expectedVideoGames: [
                'Jeu vidéo 9',
                'Jeu vidéo 8',
                'Jeu vidéo 7',
                'Jeu vidéo 6',
                'Jeu vidéo 5',
                'Jeu vidéo 49',
                'Jeu vidéo 48',
                'Jeu vidéo 47',
                'Jeu vidéo 46',
                'Jeu vidéo 45',
            ]
        );

        yield 'First page, sorting by title, direction on ascending' => self::createUseCase(
            query: ['sorting' => 'Title', 'direction' => 'Ascending'],
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 1',
                'Jeu vidéo 10',
                'Jeu vidéo 11',
                'Jeu vidéo 12',
                'Jeu vidéo 13',
                'Jeu vidéo 14',
                'Jeu vidéo 15',
                'Jeu vidéo 16',
                'Jeu vidéo 17',
            ]
        );

        yield 'First page, filter by search' => self::createUseCase(
            query: ['filter' => ['search' => 'Jeu vidéo 49']],
            expectedCount: 1,
            expectedOffsetTo: 1,
            expectedTotal: 1,
            expectedPage: null,
            expectedVideoGames: ['Jeu vidéo 49']
        );

        yield 'First page, filter by 1 tag' => self::createUseCase(
            query: ['filter' => ['tags' => ['1']]],
            expectedTotal: 10,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 21',
                'Jeu vidéo 22',
                'Jeu vidéo 23',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 46',
                'Jeu vidéo 47',
                'Jeu vidéo 48',
                'Jeu vidéo 49',
            ]
        );

        yield 'First page, filter by 2 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2']]],
            expectedCount: 8,
            expectedOffsetTo: 8,
            expectedTotal: 8,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 22',
                'Jeu vidéo 23',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 47',
                'Jeu vidéo 48',
                'Jeu vidéo 49',
            ]
        );

        yield 'First page, filter by 3 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2', '3']]],
            expectedCount: 6,
            expectedOffsetTo: 6,
            expectedTotal: 6,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 23',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 48',
                'Jeu vidéo 49',
            ]
        );

        yield 'First page, filter by 4 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2', '3', '4']]],
            expectedCount: 4,
            expectedOffsetTo: 4,
            expectedTotal: 4,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 49',
            ]
        );

        yield 'First page, filter by 5 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2', '3', '4', '5']]],
            expectedCount: 2,
            expectedOffsetTo: 2,
            expectedTotal: 2,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 25',
            ]
        );
    }

    /**
     * Asserts the listing page matches the given dataset:
     * - Response status
     * - Number of cards displayed
     * - "list-info" summary text (from/to/total)
     * - Presence/absence of pagination and its labels
     * - Ordered titles of the game cards
     *
     * @param array<string, mixed>      $query
     * @param int                       $expectedCount
     * @param int                       $expectedOffsetFrom
     * @param int                       $expectedOffsetTo
     * @param int                       $expectedTotal
     * @param int|null                  $expectedPage
     * @param list<string>              $expectedPaginationLinks
     * @param list<string>              $expectedVideoGames
     *
     * @dataProvider provideUseCases
     */
    public function testShouldShowVideoGamesByUseCase(
        array $query,
        int $expectedCount,
        int $expectedOffsetFrom,
        int $expectedOffsetTo,
        int $expectedTotal,
        ?int $expectedPage,
        array $expectedPaginationLinks,
        array $expectedVideoGames
    ): void {
        // Act
        $value = $this->client->request('GET', '/', $query);
//        $this->get('/', $query);


        // Assert: response & count
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedCount, 'article.game-card');

        // Assert: summary text (from/to/total)
        self::assertSelectorTextSame(
            'div.fw-bold',
            sprintf(
                'Affiche %d jeux vidéo de %d à %d sur les %d jeux vidéo',
                $expectedCount,
                $expectedOffsetFrom,
                $expectedOffsetTo,
                $expectedTotal
            )
        );

        // Assert: pagination (or none)
        if ($expectedPage === null) {
            self::assertSelectorNotExists('nav[aria-label="Pagination"]');
        } else {
            self::assertSelectorTextSame('li.page-item.active', (string) $expectedPage);
            self::assertSelectorCount(\count($expectedPaginationLinks), 'li.page-item');

            foreach ($expectedPaginationLinks as $expectedPaginationLink) {
                self::assertSelectorExists(sprintf('li.page-item[aria-label="%s"]', $expectedPaginationLink));
            }
        }

        // Assert: ordered titles
        foreach (\array_values($expectedVideoGames) as $index => $expectedVideoGame) {
            self::assertSelectorTextSame(
                sprintf('article.game-card:nth-child(%d) h2.game-card-title a', $index + 1),
                $expectedVideoGame
            );
        }
    }

    // ---------- Helpers ----------


    /**
     * Helper to build a use-case array with sensible defaults.
     *
     * It also builds pagination labels depending on the current page position
     * (adds "Première page"/"Précédent" for middle/end pages, and
     * "Suivant"/"Dernière page" for middle/start pages).
     *
     * If no explicit expected titles are provided, it generates a sequence of
     * "Jeu vidéo N" using {@see array_fill_callback()}.
     *
     * @param array<string, mixed>   $query
     * @param int                    $expectedCount
     * @param int                    $expectedOffsetFrom
     * @param int                    $expectedOffsetTo
     * @param int                    $expectedTotal
     * @param int|null               $expectedPage
     * @param list<string>|null      $expectedPaginationLinks
     * @param list<string>|null      $expectedVideoGames
     *
     * @return array{
     *     query: array<string, mixed>,
     *     expectedCount: int,
     *     expectedOffsetFrom: int,
     *     expectedOffsetTo: int,
     *     expectedTotal: int,
     *     expectedPage: int|null,
     *     expectedPaginationLinks: string[],
     *     expectedVideoGames: string[]
     * }
     */
    private static function createUseCase(
        array $query = [],
        int $expectedCount = 10,
        int $expectedOffsetFrom = 1,
        int $expectedOffsetTo = 10,
        int $expectedTotal = 50,
        ?int $expectedPage = 1,
        ?array $expectedPaginationLinks = null,
        ?array $expectedVideoGames = null
    ): array {
        if ($expectedPage !== null) {
            $expectedPaginationLinks = $expectedPaginationLinks ?? ['1', '2', '3', '4'];

            if ($expectedPage > 1) {
                $expectedPaginationLinks = \array_merge(['Première page', 'Précédent'], $expectedPaginationLinks);
            }

            if ($expectedPage < \ceil($expectedTotal / $expectedCount)) {
                $expectedPaginationLinks = \array_merge($expectedPaginationLinks, ['Suivant', 'Dernière page']);
            }
        }

        return [
            'query' => $query,
            'expectedCount' => $expectedCount,
            'expectedOffsetFrom' => $expectedOffsetFrom,
            'expectedOffsetTo' => $expectedOffsetTo,
            'expectedTotal' => $expectedTotal,
            'expectedPage' => $expectedPage,
            'expectedPaginationLinks' => $expectedPaginationLinks ?? [],
            'expectedVideoGames' => $expectedVideoGames ?? \array_fill_callback(
                    $expectedOffsetFrom - 1,
                    $expectedCount,
                    static fn (int $index): string => \sprintf('Jeu vidéo %d', $index)
                ),
        ];
    }
}
