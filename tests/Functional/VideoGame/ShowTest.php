<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional tests for the Video Game "show" page and posting a review.
 *
 * @coversNothing
 */
final class ShowTest extends FunctionalTestCase
{
    /**
     * Ensures a game page is reachable and displays the expected title.
     */
    public function testShowDisplaysTitleForExistingGame(): void
    {
        // Arrange
        $slug = '/jeu-video-0';

        // Act
        $this->get($slug);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Jeu vidéo 0');
    }

    /**
     * Ensures an authenticated user can post a review via the form.
     *
     * Steps:
     *  - GET the page
     *  - submit the form using submitForm()
     *  - expect a redirect
     *  - follow it and assert the last review contains our values
     */
    public function testPostReviewPersistsAndDisplaysLatestReview(): void
    {
        // Arrange
        $slug       = '/jeu-video-49';
        $buttonText = 'Poster';
        $formData   = [
            'review[rating]'  => 4,
            'review[comment]' => 'Mon commentaire',
        ];

        // Act
        $this->login();
        $this->get($slug);
        self::assertResponseIsSuccessful();

        // Submit the form
        $this->client->submitForm($buttonText, $formData);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Act
        $this->client->followRedirect();

        // Assert
        self::assertSelectorTextContains('div.list-group-item:last-child h3', 'user+0');
        self::assertSelectorTextContains('div.list-group-item:last-child p', 'Mon commentaire');
        self::assertSelectorTextContains('div.list-group-item:last-child span.value', '4');
    }
}
