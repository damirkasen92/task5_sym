<?php

namespace App\Service;

use App\Service\Faker\FakerService;
use App\Service\Faker\MusicGenreProvider;
use App\Service\Faker\SentenceProvider;
use Faker\Generator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MusicGeneratorService
{
    public function __construct(
        private readonly DeterministicGeneratorService $rngService,
        private readonly FakerService                  $fakerService,
        private readonly CoverGeneratorService         $coverGeneratorService,
        private readonly CacheInterface                $cache,
    )
    {
    }

    // dto но спешим
    public function generateSongData(string $seed, string $locale, int $pageNumber, int $recordIndex): array
    {
        $key = 'songs_' . md5($seed . $locale . $pageNumber . $recordIndex);

        return $this->cache->get($key, function (ItemInterface $item) use ($seed, $locale, $pageNumber, $recordIndex) {
            $item->expiresAfter(3600);

            // logic
            $this->rngService->initialize($seed, $locale, $pageNumber, $recordIndex);
            /** @var Generator|MusicGenreProvider|SentenceProvider $faker */
            $faker = $this->fakerService->init($locale, $this->rngService->finalSeed);

            $title = $faker->musicTrackTitle();
            $artistType = $this->rngService->getRandomInt(0, 1) === 1 ? 'band' : 'person';
            $albumTitle = $faker->albumTitle();
            $genre = $faker->musicGenre();

            if ($artistType === 'band') {
                $artist = $faker->company();
            } else {
                $artist = $faker->name();
            }

            return [
                'title' => $title,
                'artist' => $artist,
                'albumTitle' => $albumTitle,
                'genre' => $genre,
                'cover' => $this->coverGeneratorService->generate($title, $artist),
            ];
        });
    }

    public function generateSongs(string $seed, float $likesNum, string $locale, int $page, int $totalSongsPerPage = 6): array
    {
        $songs = [];
        $endRecordIdx = $page * $totalSongsPerPage;
        $startRecordIdx = $endRecordIdx - $totalSongsPerPage + 1;

        for ($recordIdx = $startRecordIdx; $recordIdx <= $endRecordIdx; $recordIdx++) {
            $songs[$recordIdx] = $this->generateSongData($seed, $locale, $page, $recordIdx);
            $songs[$recordIdx]['index'] = $recordIdx;
            $songs[$recordIdx]['likes'] = $this->generateLike($likesNum);
        }

        return $songs;
    }

    private function generateLike(float $avgLikes)
    {
        $averageLikes = max(0, min(10, $avgLikes));
        $base = (int)floor($averageLikes);
        $fraction = $averageLikes - $base;

        // Если дробная часть > 0, то с вероятностью fraction добавляем +1
        if ($fraction > 0) {
            // рандомное число от 0 до 1
            if (mt_rand() / mt_getrandmax() < $fraction) {
                return $base + 1;
            }
        }

        return $base;
    }
}
