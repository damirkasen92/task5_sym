<?php

namespace App\Service;

use App\Dto\SongParamsDto;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Colors\Rgb\Color;
use Intervention\Image\Interfaces\ImageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CoverGeneratorService
{
    private const int TITLE_SIZE = 24;
    private const int ARTIST_SIZE = 16;
    private const int CANVAS_SIZE = 400;
    private readonly string $projectDir;

    public function __construct(
        private readonly ImageManager $manager,
        private readonly ParameterBagInterface $parameterBag,
        private readonly DeterministicGeneratorService $deterministicGeneratorService
    )
    {
        $this->projectDir = $this->parameterBag->get('kernel.project_dir');
    }

    public function generate(string $title, string $artist, SongParamsDto $dto): string
    {
        // можно еще через services.yaml вставить $dto и убрать отсюда рандомайзер
        $this->deterministicGeneratorService->initialize($dto);

        $size = self::CANVAS_SIZE;
        $img = $this->manager->create($size, $size);

        $bgColor = new Color(
            $this->deterministicGeneratorService->getRandomInt(0, 255),
            $this->deterministicGeneratorService->getRandomInt(0, 255),
            $this->deterministicGeneratorService->getRandomInt(0, 255),
        );
        $img->fill($bgColor);

        $textColor = new Color(255, 255, 255);
        $this->addTitle($img, $title, $size, $textColor);
        $this->addArtist($img, $artist, $size, $textColor);

        return 'data:image/png;base64,' . base64_encode(
            $img->encode(new PngEncoder())
        );
    }

    private function addTitle(ImageInterface $img, string $title, int $size, Color $rgb): void
    {
        $img->text($title, $size / 2, $size * 0.45, function ($font) use ($rgb) {
            $font->file($this->projectDir . '/public/fonts/inter_bold.ttf');
            $font->size(self::TITLE_SIZE);
            $font->align('center');
            $font->valign('middle');
            $font->color($rgb);
        });
    }

    private function addArtist(ImageInterface $img, string $artist, int $size, Color $rgb): void
    {
        $img->text($artist, $size / 2, $size * 0.60, function ($font) use ($rgb) {
            $font->file($this->projectDir . '/public/fonts/inter_regular.ttf');
            $font->size(self::ARTIST_SIZE);
            $font->align('center');
            $font->valign('middle');
            $font->color($rgb);
        });
    }
}
