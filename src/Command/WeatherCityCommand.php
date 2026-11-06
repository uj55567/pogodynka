<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:city',
    description: 'Wyświetla prognozę pogody dla kodu kraju i nazwy miejscowości',
)]
class WeatherCityCommand
{
    public function __construct(
        private readonly WeatherUtil $weatherUtil,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Country code')] string $country,
        #[Argument('City name')] string $city,
    ): int {
        $weathers = $this->weatherUtil->getWeatherForCountryAndCity($country, $city);

        $io->writeln(sprintf('Location: %s, %s', $city, $country));
        $io->table(['Date', 'Temperature','Humidity','Pressure','IndexUv'], array_map(fn($w) => [
            $w->getDateTime()->format('Y-m-d'),
            $w->getTemperature(),
            $w->getHumidity(),
            $w->getPressure(),
            $w->getIndexUv()
        ], $weathers));

        return Command::SUCCESS;
    }
}
