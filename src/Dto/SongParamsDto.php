<?php

namespace App\Dto;

readonly class SongParamsDto
{
    public string $seed;
    public string $locale;
    public int $pageNumber;
    public int $recordIndex;

    public static function factory(string $seed, string $locale, int $pageNumber, int $recordIndex): self
    {
        $dto = new self();

        $dto->seed = $seed;
        $dto->locale = $locale;
        $dto->pageNumber = $pageNumber;
        $dto->recordIndex = $recordIndex;

        return $dto;
    }
}
