<?php

namespace App\Service\Faker;

use App\Dto\FakerDto;
use Faker\Provider\Base;

abstract class BaseProvider extends Base
{
    protected readonly string $lang;
    protected readonly FakerDto $dto;

    public function __construct(FakerDto $dto)
    {
        parent::__construct($dto->faker);
        $this->lang = $dto->locale;
        $this->dto = $dto;
        $this->init();
    }

    abstract protected function init(): void;
}
