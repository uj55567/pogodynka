<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\LocationRepository;
use App\Service\WeatherUtil;


#[AsCommand(
    name: 'weather:location',
    description: 'Add a short description for your command',
)]
class WeatherLocationCommand{


    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly WeatherUtil $weatherUtil,
    ) {
    }
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Location ID')] int $id,
    ): int {
        $location = $this->locationRepository->find($id);

        $weathers = $this->weatherUtil->getWeatherForLocation($location);
        $io->writeln(sprintf('Location: %s', $location->getCityName()));
        $io->table(['Date', 'Temperature','Humidity','Pressure','IndexUV'], array_map(fn($m) => [
            $m->getDateTime()->format('Y-m-d'),
            $m->getTemperature(),
            $m->getHumidity(),
            $m->getPressure(),
            $m->getIndexUv()

        ], $weathers));

        return Command::SUCCESS;
    }
}
