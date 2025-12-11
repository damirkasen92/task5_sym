<?php

namespace App\Service;

use App\Dto\SongParamsDto;
use App\Service\Faker\FakerService;
use App\Service\Faker\MusicGenreProvider;
use App\Service\Faker\SentenceProvider;
use App\Service\Faker\SongTitleProvider;
use Faker\Generator;
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
    public function generateSongData(SongParamsDto $dto): array
    {
        $key = 'songs_' . md5($dto->seed . $dto->locale . $dto->pageNumber . $dto->recordIndex);

        return $this->cache->get($key, function (ItemInterface $item) use ($dto) {
            $item->expiresAfter(3600);

            // logic
            $this->rngService->initialize($dto);
            /** @var Generator|MusicGenreProvider|SongTitleProvider|SentenceProvider $faker */
            $faker = $this->fakerService->init($dto->locale, $this->rngService->finalSeed);
            $title = $faker->musicTrackTitle();
            $artistType = $this->rngService->getRandomInt(0, 1) === 1 ? 'band' : 'person';
            $artist = $artistType === 'band' ? $faker->company() : $faker->name();

            return [
                'title' => $title,
                'artist' => $artist,
                'albumTitle' => $faker->albumTitle(),
                'genre' => $faker->musicGenre(),
                'cover' => $this->coverGeneratorService->generate($title, $artist, $dto),
                'reviewText' => $faker->randomSentence(),
            ];
        });
    }

    public function generateSongs(string $seed, float $likesNum, string $locale, int $page, int $totalSongsPerPage = 6): array
    {
        $songs = [];
        $endRecordIdx = $page * $totalSongsPerPage;
        $startRecordIdx = $endRecordIdx - $totalSongsPerPage + 1;

        for ($recordIdx = $startRecordIdx; $recordIdx <= $endRecordIdx; $recordIdx++) {
            $songs[$recordIdx] = $this->generateSongData(SongParamsDto::factory($seed, $locale, $page, $recordIdx));
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
