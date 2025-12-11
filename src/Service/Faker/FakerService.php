<?php

namespace App\Service\Faker;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

readonly class FakerService
{
    public function __construct(private ParameterBagInterface $params, private Filesystem $filesystem)
    {
    }

    public function init(string $locale, string $seed): Generator
    {
        $faker = Factory::create($locale);
        $faker->seed((int)$seed);
        $faker->addProvider(new MusicGenreProvider($faker, $locale, $this->params, $this->filesystem));
        $faker->addProvider(new SentenceProvider($faker, $locale, $this->params, $this->filesystem));
        return $faker;
    }
}
