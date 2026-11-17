<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\WeatherUtil;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use App\Entity\Weather;
use Symfony\Component\HttpFoundation\Response;

final class WeatherApiController extends AbstractController
{
    #[Route('/api/v1/weather', name: 'app_weather_api', methods: ['GET'])]
    public function index(
        WeatherUtil $util,
        #[MapQueryParameter('country')] string $country,
        #[MapQueryParameter('city')] string $city,
        #[MapQueryParameter('format', filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^(json|csv)$/'])]
        string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false,
    ): Response
    {
        $measurements = $util->getWeatherForCountryAndCity($country, $city);
        if ($twig) {
            $contentType = $format === 'csv' ? 'text/plain; charset=UTF-8' : 'application/json';

            return $this->render("weather_api/index.{$format}.twig", [
                'city' => $city,
                'country' => $country,
                'measurements' => $measurements,
            ], new Response(headers: ['Content-Type' => $contentType]));
        }
        if ($format === 'csv') {
            $csv = "city,country,date,celsius,fahrenheit,humidity,pressure,index uv\n";
            foreach ($measurements as $m) {
                $csv .= sprintf("%s, %s, %s, %s, %s, %s, %s ,%s\n",
                    $city,
                    $country,
                    $m->getDateTime()->format('Y-m-d'),
                    $m->getTemperature(),
                    $m->getFahrenheit(),
                    $m->getHumidity(),
                    $m->getPressure(),
                    $m->getIndexUv()
                );
            }
            return new Response($csv, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return $this->json([
            'city' => $city,
            'country' => $country,
            'measurements'=> array_map(fn(Weather $m) =>[
                'data'=>$m->getDateTime()->format('Y-m-d'),
                'celsius'=> (float) $m->getTemperature(),
                'fahrenheit' => $m->getFahrenheit(),
                'humidity'=> (float) $m->getHumidity(),
                'pressure'=> (float) $m->getPressure(),
                'index uv'=> (float) $m->getIndexUv(),

            ], $measurements),
        ]);
    }
}
