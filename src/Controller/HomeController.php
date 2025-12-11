<?php

namespace App\Controller;

use App\Service\MusicGeneratorService;
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
    public function getWav(int $seed, string $_locale, MusicGeneratorService $musicGeneratorService, Request $request): StreamedResponse
    {
        $page = $request->query->get('page', 1);
        $recordIndex = $request->query->get('record_index', 1);
        return $musicGeneratorService->generatePreview($seed, $_locale, $page, $recordIndex);
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
        $seed = $request->query->getInt('seed', 0);
        $likes = (float) $request->query->get('likes', 0.0);
        $view = $request->query->get('view', 'table'); // table | gallery
        $songs = $musicGeneratorService->generateSongs($seed, $likes, $_locale, $page);
        $data = [
            'songs' => $songs,
            'view' => $view,
            'seed' => $seed,
            'language' => $language,
            'likes' => $likes,
            'page' => $page,
        ];

        return $this->render('_' . $view . '.html.twig', $data);
    }

    #[Route(
        path: '/{_locale}',
        name: 'home',
    )]
    public function home(string $_locale, Request $request, MusicGeneratorService $musicGeneratorService): Response
    {
        return $this->render('home.html.twig');
    }
}
