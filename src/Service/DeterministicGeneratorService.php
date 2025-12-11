<?php

namespace App\Service;

class DeterministicGeneratorService
{
    public string $finalSeed {
        get {
            return $this->finalSeed;
        }
    }

    public function initialize(string $seed, string $locale, int $pageNumber, int $recordIndex = 0): void
    {
        $combinedKey = $locale . '|' . $seed . '|' . $pageNumber . '|' . $recordIndex;
        $hashedSeed = crc32($combinedKey);

        // mt_srand() устанавливает начальное состояние генератора mt_rand().
        mt_srand($hashedSeed);
        $this->finalSeed = (string)$hashedSeed;
    }

    public function getRandomInt(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    public function getRandomFloat(float $min, float $max): float
    {
        return $min + ($this->getRandomInt(0, mt_getrandmax() - 1) / mt_getrandmax()) * $max;
    }
}
