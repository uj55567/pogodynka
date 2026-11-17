<?php

namespace App\Tests\Entity;

use App\Entity\Weather;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WeatherTest extends TestCase
{
    public static function dataGetFahrenheit(): array
    {
        return [
            ['0', '32'],
            ['-100', '-148'],
            ['100', '212'],
            ['0.5', '32.9'],
            ['-0.5', '31.1'],
            ['-40', '-40'],
            ['-6', '21.20'],
            ['36.6', '97.88'],
            ['-17.5', '0.5'],
            ['67.00', '152.6'],
        ];
    }

    #[DataProvider('dataGetFahrenheit')]
    public function testGetFahrenheit(string $celsius, string $expectedFahrenheit): void
    {
        $weather = new Weather();
        $weather->setTemperature($celsius);

        $this->assertEqualsWithDelta((float) $expectedFahrenheit, $weather->getFahrenheit(), 0.0001);
    }
}
