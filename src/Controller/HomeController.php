<?php

namespace App\Controller;

use App\Service\CoverGeneratorService;
use App\Service\MusicGeneratorService;
use App\Service\SoundGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route(
        '/{_locale}/wav/{seed}',
        name: 'wav',
        requirements: ['seed' => '[\d]+', 'page' => '[\d]+', 'record_index' => '[\d]+'],
        methods: ['GET']
    )]
    public function getWav(string $seed, string $_locale, SoundGeneratorService $soundGeneratorService, Request $request): StreamedResponse
    {
        $page = $request->query->get('page', 1);
        $recordIndex = $request->query->get('record_index', 1);
        return $soundGeneratorService->generateSound($seed, $_locale, $page, $recordIndex);
    }

    // int $seed, int $page, int $recordIndex
    #[Route(
        '/{_locale}/cover',
        name: 'cover',
        methods: ['POST']
    )]
    public function getCover(string $albumTitle, string $artist, CoverGeneratorService $coverGeneratorService): string
    {
        return $coverGeneratorService->generate($albumTitle, $artist);
    }

    #[Route(
        path: '/content',
        name: 'home_content_default'
    )]
    #[Route(
        path: '/{_locale}/content',
        name: 'home_content',
        defaults: ['_locale' => 'en']
    )]
    public function homeContent(string $_locale, Request $request, MusicGeneratorService $musicGeneratorService): Response
    {
        $page = $request->query->get('page', 1);
        $language = $request->query->get('language', 'en');
        $seed = $request->query->get('seed', 0);
        $likes = (float) $request->query->get('likes', 0.0);
        $view = $request->query->get('view', 'table'); // table | gallery
        $songs = $musicGeneratorService->generateSongs($seed, $likes, $_locale, $page, 8);
        $data = [
            'songs' => $songs,
            'view' => $view,
            'seed' => $seed,
            'language' => $language,
            'likes' => $likes,
            'page' => $page,
            'next_page' => $page + 1,
        ];

        return $this->render('_' . $view . '.html.twig', $data);
    }

    #[Route(
        path: '/',
        name: 'home_default',
        defaults: ['_locale' => 'en']
    )]
    #[Route(
        path: '/{_locale}',
        name: 'home',
    )]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }
}
