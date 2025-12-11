<?php

namespace App\Service;

use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Colors\Rgb\Color;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CoverGeneratorService
{
    private const int TITLE_SIZE = 24;
    private const int ARTIST_SIZE = 16;
    private const int CANVAS_SIZE = 400;

    public function __construct(private readonly ImageManager $manager, private ParameterBagInterface $parameterBag)
    {
    }

    public function generate(string $title, string $artist): string
    {
        $size = self::CANVAS_SIZE;

        // создаём пустое изображение
        $img = $this->manager->create($size, $size);

        // случайный фон в RGB
        // можно подключить DRNG и тогда обложки тоже будут выдаваться детерминировано
        $bgColor = new Color(
            random_int(0, 255),
            random_int(0, 255),
            random_int(0, 255)
        );
        $img->fill($bgColor);

        $textColor = new Color(255, 255, 255);
        $projectDir = $this->parameterBag->get('kernel.project_dir');

        // Заголовок
        $img->text($title, $size / 2, $size * 0.45, function ($font) use ($textColor, $projectDir) {
            $font->file($projectDir . '/public/fonts/inter_bold.ttf');
            $font->size(self::TITLE_SIZE);
            $font->align('center');
            $font->valign('middle');
            $font->color($textColor);
        });

        // Артист
        $img->text($artist, $size / 2, $size * 0.60, function ($font) use ($textColor, $projectDir) {
            $font->file($projectDir . '/public/fonts/inter_regular.ttf');
            $font->size(self::ARTIST_SIZE);
            $font->align('center');
            $font->valign('middle');
            $font->color($textColor);
        });

        return 'data:image/png;base64,' . base64_encode(
            $img->encode(new PngEncoder())
        );
    }
}
