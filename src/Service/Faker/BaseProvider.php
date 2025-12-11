<?php

namespace App\Service\Faker;

use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseProvider extends Base
{
    protected readonly string $lang;

    public function __construct(Generator $generator, string $locale, protected ParameterBagInterface $params, protected Filesystem $filesystem)
    {
        parent::__construct($generator);
        $this->lang = $locale;
        $this->init();
    }

    abstract protected function init(): void;
}
