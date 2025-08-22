<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\OpenWeatherDataService;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-open-weather-data',
    description: 'Fetches and saves weather data from the OpenWeather API.'
)]
class OpenWeatherCommand extends Command
{
    public function __construct(private readonly OpenWeatherDataService $weatherDataService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'cityName',
            InputArgument::REQUIRED,
            'The name of the city to fetch weather data for.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cityName = $input->getArgument('cityName');

        try {
            $weatherData = $this->weatherDataService->fetchWeatherData($cityName);
            $this->weatherDataService->saveOpenWeatherData($weatherData);

            $output->writeln(\sprintf('Weather data for "%s" saved successfully.', $cityName));
        } catch (RuntimeException $e) {
            $output->writeln('<error>Error fetching weather data: '.$e->getMessage().'</error>');

            return Command::FAILURE;
        } catch (Exception $e) {
            $output->writeln('<error>An unexpected error occurred: '.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
