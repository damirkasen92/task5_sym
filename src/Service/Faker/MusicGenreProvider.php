<?php

namespace App\Service\Faker;

class MusicGenreProvider extends BaseProvider
{
    private static array $genres = [];

    final protected function init(): void
    {
        self::$genres = json_decode($this->dto->filesystem->readFile(
            $this->dto->params->get('faker.music_genres_path')
        ), true)['music_genres'];
    }

    public function musicGenre(): string
    {
        return static::randomElement(static::$genres[$this->lang]);
    }
}
