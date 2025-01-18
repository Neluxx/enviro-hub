<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use DateTime;
use InvalidArgumentException;

class EnvironmentalDataService
{
    private const CO2_THRESHOLD = 1000;

    private EnvironmentalDataRepository $repository;

    private MailerInterface $mailer;

    public function __construct(EnvironmentalDataRepository $repository, MailerInterface $mailer)
    {
        $this->repository = $repository;
        $this->mailer = $mailer;
    }

    public function saveEnvironmentalData(array $data): void
    {
        $this->validate($data);
        $environmentalData = $this->createEnvironmentalData($data);

        $this->repository->save($environmentalData);

        $currentCo2Value = $environmentalData->getCarbonDioxide();
        $lastCo2Value = $this->repository->getLastEntry()?->getCarbonDioxide();

        if ($currentCo2Value >= self::CO2_THRESHOLD && $lastCo2Value < self::CO2_THRESHOLD) {
            $this->notifyHighCo2($environmentalData);
        }

        if ($currentCo2Value < self::CO2_THRESHOLD && $lastCo2Value >= self::CO2_THRESHOLD) {
            $this->notifyLowCo2($environmentalData);
        }
    }

    private function validate(array $data): void
    {
        if (!DateTime::createFromFormat('Y-m-d H:i:s', $data['created'])) {
            throw new InvalidArgumentException('Invalid date format for "created" field.');
        }

        if (!is_numeric($data['temperature'])) {
            throw new InvalidArgumentException('Invalid type for "temperature". Expected a numeric value.');
        }

        if (!is_numeric($data['humidity'])) {
            throw new InvalidArgumentException('Invalid type for "humidity". Expected a numeric value.');
        }

        if (!is_numeric($data['pressure'])) {
            throw new InvalidArgumentException('Invalid type for "pressure". Expected a numeric value.');
        }

        if (!is_numeric($data['co2'])) {
            throw new InvalidArgumentException('Invalid type for "carbon dioxide". Expected a numeric value.');
        }
    }

    private function createEnvironmentalData(array $data): EnvironmentalData
    {
        return new EnvironmentalData(
            (float)$data['temperature'],
            (float)$data['humidity'],
            (float)$data['pressure'],
            (float)$data['co2'],
            DateTime::createFromFormat('Y-m-d H:i:s', $data['created']),
            new DateTime('now')
        );
    }

    /**
     * Sends an email notification when CO2 levels exceed the defined threshold.
     *
     * @param EnvironmentalData $environmentalData Contains the CO2 level and measurement details.
     */
    private function notifyHighCo2(EnvironmentalData $environmentalData): void
    {
        $email = (new Email())
            ->from('info@fabian-arndt.dev')
            ->to('fabian.arndt96@proton.me')
            ->subject('High CO2 Alert!')
            ->text(sprintf(
                "The CO2 level has exceeded the threshold of %d ppm.\n\nDetails:\n- CO2 Level: %d ppm\n- Measured At: %s",
                self::CO2_THRESHOLD,
                $environmentalData->getCarbonDioxide(),
                $environmentalData->getMeasuredAt()->format('Y-m-d H:i:s')
            ));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            error_log('Email sending failed: '.$exception->getMessage());
        }
    }

    /**
     * Sends an email notification when CO2 levels drop below the defined threshold.
     *
     * @param EnvironmentalData $environmentalData Contains the CO2 level and measurement details.
     */
    private function notifyLowCo2(EnvironmentalData $environmentalData): void
    {
        $email = (new Email())
            ->from('info@fabian-arndt.dev')
            ->to('fabian.arndt96@proton.me')
            ->subject('Low CO2 Alert!')
            ->text(sprintf(
                "The CO2 level is below the threshold of %d ppm.\n\nDetails:\n- CO2 Level: %d ppm\n- Measured At: %s",
                self::CO2_THRESHOLD,
                $environmentalData->getCarbonDioxide(),
                $environmentalData->getMeasuredAt()->format('Y-m-d H:i:s')
            ));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            error_log('Email sending failed: '.$exception->getMessage());
        }
    }
}