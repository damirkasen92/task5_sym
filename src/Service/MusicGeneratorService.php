<?php

namespace App\Service;

use App\Service\Faker\FakerService;
use App\Service\Faker\MusicGenreProvider;
use App\Service\Faker\SentenceProvider;
use Faker\Generator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MusicGeneratorService
{
    private const int MAX_16_BITS = 32767;
    private const int SAMPLE_RATE = 44100;

    public function __construct(
        private readonly DeterministicGeneratorService $rngService,
        private readonly FakerService                  $fakerService
    )
    {
    }

    // dto но спешим
    public function generateSongData(string $seed, string $locale, int $pageNumber, int $recordIndex): array
    {
        $this->rngService->initialize($seed, $locale, $pageNumber, $recordIndex);
        /** @var Generator|MusicGenreProvider|SentenceProvider $faker */
        $faker = $this->fakerService->init($locale, $this->rngService->finalSeed);

        $title = $faker->musicTrackTitle();
        $artistType = $this->rngService->getRandomInt(0, 1) === 1 ? 'band' : 'person';
        $albumTitle = $faker->albumTitle();
        $genre = $faker->musicGenre();

        if ($artistType === 'band') {
            $artist = $faker->company();
        } else {
            $artist = $faker->name();
        }

        return [
            'title' => $title,
            'artist' => $artist,
            'albumTitle' => $albumTitle,
            'genre' => $genre,
        ];
    }

    public function generateSongs(string $seed, string $locale, int $page, int $totalSongsPerPage = 6): array
    {
        $songs = [];
        $endRecordIdx = $page * $totalSongsPerPage;
        $startRecordIdx = $endRecordIdx - $totalSongsPerPage + 1;

        for ($recordIdx = $startRecordIdx; $recordIdx <= $endRecordIdx; $recordIdx++) {
            $songs[$recordIdx] = $this->generateSongData($seed, $locale, $page, $recordIdx);
        }

        return $songs;
    }

    // далее конечно не библиотека, но большинство кода сгенерил AI, код был только подправлен
    public function generatePreview(string $seed, string $locale, int $pageNumber, int $recordIndex): StreamedResponse
    {
        $this->rngService->initialize($seed, $locale, $pageNumber, $recordIndex);
        $duration = $this->rngService->getRandomFloat(0.3, 0.5);
        $samplesPerNote = (int)(self::SAMPLE_RATE * $duration);
        $oscMap = [
            'sine' => range('0', '3'),
            'square' => array_merge(range('4', '6'), range('m', 'r')),
            'triangle' => array_merge(range('7', '9'), range('s', 'v')),
            'sawtooth' => array_merge(range('a', 'f'), range('w', 'z')),
        ];
        $musicHash = md5($this->rngService->finalSeed);
        $data = '';

        foreach (str_split($musicHash) as $char) {
            $freq = $this->letterToFreq($char);

            if (!$freq) continue;

            // Определяем осциллятор
            $oscType = 'sine';
            foreach ($oscMap as $type => $chars) {
                if (in_array($char, $chars)) {
                    $oscType = $type;
                    break;
                }
            }

            // Генерация ноты
            for ($i = 0; $i < $samplesPerNote; $i++) {
                $mix = $this->oscSample($oscType, $freq, $i);

                // Эффекты по символу
                if (ctype_digit($char)) {
                    $mix = $this->applyTremolo($mix, $i, 4.0, 0.3);
                } elseif ($char >= 'a' && $char <= 'f') {
                    $freq = $this->applyVibrato($freq, $i, 5.0, 0.02);
                    $freq = $this->quantizeToScale($freq);
                    $mix = $this->oscSample($oscType, $freq, $i);
                }

                $envelope = $this->adsr($i, $samplesPerNote, 0.1, 0.2, 0.4, 0.3);
                $val = (int)(self::MAX_16_BITS * $mix * $envelope);
                $data .= pack("v", $val);
            }
        }

        $header = "RIFF" .
            pack("V", 36 + strlen($data)) .
            "WAVEfmt " .
            pack("V", 16) .
            pack("v", 1) .
            pack("v", 1) .
            pack("V", self::SAMPLE_RATE) .
            pack("V", self::SAMPLE_RATE * 2) .
            pack("v", 2) .
            pack("v", 16) .
            "data" .
            pack("V", strlen($data));

        $header .= $data;
        $response = new StreamedResponse(
            function () use ($header) {
                echo $header;
            }
        );

        $response->headers->set('Content-Type', 'audio/wav');
        $response->headers->set('Content-Disposition', 'inline; filename="output.wav"');

        return $response;
    }

    private function letterToFreq($char): float|null
    {
        $scale = [261.63, 293.66, 329.63, 349.23, 392.00, 440.00, 493.88]; // C D E F G A B

        if (is_numeric($char))
            return $scale[(int)$char % count($scale)];

        $code = ord(strtoupper($char));

        if ($code >= 65 && $code <= 90) {
            return $scale[($code - 65) % count($scale)];
        }
        return null;
    }

    private function oscSample($type, $freq, $i): float
    {
        $t = $i / self::SAMPLE_RATE;

        return match ($type) {
            'sine' => sin(2 * M_PI * $freq * $t),
            'square' => sin(2 * M_PI * $freq * $t) >= 0 ? 1 : -1,
            'saw' => 2 * ($t * $freq - floor(0.5 + $t * $freq)),
            'triangle' => 2 * abs(2 * ($t * $freq - floor($t * $freq + 0.5))) - 1,
            'sawtooth' => 2 * ($t * $freq - floor($t * $freq + 0.5)),

            default => 0,
        };
    }

    private function adsr(int $i, int $totalSamples, float $attack, float $decay, float $sustain, float $release): float
    {
        // attack, decay, release заданы в долях от общей длины ноты (0..1)
        $attackSamples = (int)($totalSamples * $attack);
        $decaySamples = (int)($totalSamples * $decay);
        $releaseSamples = (int)($totalSamples * $release);

        if ($i < $attackSamples) {
            // Attack: линейный рост от 0 до 1
            return $i / $attackSamples;
        } elseif ($i < $attackSamples + $decaySamples) {
            // Decay: спад от 1 до sustain
            $progress = ($i - $attackSamples) / $decaySamples;
            return 1 - (1 - $sustain) * $progress;
        } elseif ($i < $totalSamples - $releaseSamples) {
            // Sustain: держим уровень sustain
            return $sustain;
        } else {
            // Release: спад от sustain до 0
            $progress = ($i - ($totalSamples - $releaseSamples)) / $releaseSamples;
            return $sustain * (1 - $progress);
        }
    }

    private function applyVibrato(float $freq, int $i, float $rate = 5.0, float $depth = 0.01): float
    {
        // rate = скорость вибрато (Гц)
        // depth = глубина модуляции (в долях от частоты)
        return $freq * (1 + $depth * sin(2 * M_PI * $rate * $i / self::SAMPLE_RATE));
    }

    private function applyTremolo(float $sample, int $i, float $rate = 5.0, float $depth = 0.5): float
    {
        // rate = скорость модуляции (Гц)
        // depth = глубина модуляции (0..1)
        $mod = 1.0 - $depth + $depth * sin(2 * M_PI * $rate * $i / self::SAMPLE_RATE);
        return $sample * $mod;
    }

    private function quantizeToScale(float $freq): float
    {
        // равномерно‑темперированный строй, A4 = 440 Гц
        $noteNumber = round(12 * log($freq / 440, 2));
        return 440 * pow(2, $noteNumber / 12);
    }

}
