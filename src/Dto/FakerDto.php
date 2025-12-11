<?php

namespace App\Dto;

use Faker\Generator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

readonly class FakerDto
{
    public Generator $faker;
    public string $locale;
    public ParameterBagInterface $params;
    public Filesystem $filesystem;

    public static function factory(Generator $faker, string $locale, ParameterBagInterface $params, Filesystem $filesystem): self
    {
        $dto = new self();
        $dto->faker = $faker;
        $dto->locale = $locale;
        $dto->params = $params;
        $dto->filesystem = $filesystem;
        return $dto;
    }
}
