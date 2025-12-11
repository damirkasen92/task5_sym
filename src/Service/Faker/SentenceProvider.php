<?php

namespace App\Service\Faker;

class SentenceProvider extends BaseProvider
{
    private static array $sentences = [];

    final protected function init(): void
    {
        self::$sentences = json_decode($this->dto->filesystem->readFile(
            $this->dto->params->get('faker.sentences_path')
        ), true)['sentences'];
    }

    public function randomSentence(): string
    {
        return static::randomElement(self::$sentences)[$this->lang];
    }
}
