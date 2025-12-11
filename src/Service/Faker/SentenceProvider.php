<?php

namespace App\Service\Faker;

class SentenceProvider extends BaseProvider
{
    private static array $words = [];

    final protected function init(): void
    {
        self::$words = json_decode($this->filesystem->readFile(
            $this->params->get('faker.words_path')
        ), true);
    }

    public function musicTrackTitle(): string
    {
        return $this->generateTitle('music_titles');
    }

    public function albumTitle(): string
    {
        return $this->generateTitle('album_titles');
    }

    private function generateTitle(string $section): string
    {
        $words = self::$words[$section][$this->lang]['words'];
        $patterns = self::$words[$section][$this->lang]['patterns'];
        $pattern = static::randomElement($patterns);

        return preg_replace_callback('#{word}#', fn() => static::randomElement($words), $pattern);
    }

}
