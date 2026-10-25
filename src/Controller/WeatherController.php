<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\WeatherRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherController extends AbstractController
{
    #[Route('/weather/{country}/{city}', name: 'app_weather', requirements: ['country' => '[A-Za-z]{2}'])]
    public function city(
        #[MapEntity(mapping: ['country' => 'country', 'city' => 'city_name'])]
        Location $location,
        WeatherRepository $repository
    ): Response {
        $weathers = $repository->findByLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'weathers' => $weathers,
        ]);
    }
}
