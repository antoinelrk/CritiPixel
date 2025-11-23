<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Constructor for VideoGameFixtures.
     *
     * @param Generator                 $faker                   the Faker generator for generating fake data
     * @param CalculateAverageRating    $calculateAverageRating  service to calculate average ratings for video games
     * @param CountRatingsPerValue      $countRatingsPerValue    service to count ratings per value for video games
     * @param EntityManagerInterface    $manager                 the entity manager for database operations
     */
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue,
        private readonly EntityManagerInterface $manager,
    ) {
    }

    /**
     * Load video game fixtures into the database.
     *
     * @param ObjectManager $manager the object manager to persist entities
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $users = $this->manager->getRepository(User::class)->findAll();

        $videoGames = array_fill_callback(0, 50, fn (int $index): VideoGame => (new VideoGame())
            ->setTitle(\sprintf('Jeu vidéo %d', $index))
            ->setDescription($this->faker->paragraphs(10, true))
            ->setReleaseDate(new \DateTimeImmutable())
            ->setTest($this->faker->paragraphs(6, true))
            ->setRating(($index % 5) + 1)
            ->setImageName(\sprintf('video_game_%d.png', $index))
            ->setImageSize(2_098_872)
        );

        // Attach tags to video games
        $this->withTags($videoGames);

        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();

        $this->withRatings($videoGames, $users);

        $manager->flush();
    }

    /**
     * Adds random tags to each video game from the available tags in the database.
     *
     * @param VideoGame[] $videoGames an array of VideoGame entities to which tags will be added
     */
    private function withTags(array $videoGames): void
    {
        $tags = $this->manager->getRepository(Tag::class)->findAll();

        foreach ($videoGames as $index => $videoGame) {
            for ($tagIndex = 0; $tagIndex < 5; ++$tagIndex) {
                $tagPosition = ($index + $tagIndex) % \count($tags);
                $tag         = $tags[$tagPosition];

                $videoGame->getTags()->add($tag);
            }
        }
    }

    /**
     * Adds reviews with ratings to each video game from a selection of users.
     *
     * @param VideoGame[] $videoGames an array of VideoGame entities to which reviews will be added
     * @param User[][]    $users      a two-dimensional array of User entities grouped for review assignment
     */
    private function withRatings(array $videoGames, array $users): void
    {
        foreach ($videoGames as $gameIndex => $videoGame) {
            $userGroupIndex = $gameIndex % 5;
            $selectedUsers  = $users[$userGroupIndex];

            foreach ($selectedUsers as $userIndex => $user) {
                $comment = $this->faker->paragraph();

                $review = new Review();
                $review->setUser($user);
                $review->setVideoGame($videoGame);
                $review->setRating($this->faker->numberBetween(1, 5));
                $review->setComment($comment);

                $videoGame->getReviews()->add($review);

                $this->manager->persist($review);

                $this->calculateAverageRating->calculateAverage($videoGame);
                $this->countRatingsPerValue->countRatingsPerValue($videoGame);
            }
        }
    }

    /**
     * This method returns an array of fixture classes that this fixture depends on.
     *
     * @return array an array of fixture class names that must be loaded before this fixture
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
