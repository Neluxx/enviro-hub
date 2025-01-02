<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use DateTime;
use InvalidArgumentException;

class EnvironmentalDataService
{
    private const CO2_THRESHOLD = 1000;
    private const REQUIRED_FIELDS = ['temperature', 'humidity', 'pressure', 'co2', 'created'];

    public function __construct(
        private readonly EnvironmentalDataRepository $repository,
        private readonly ValidatorInterface $validator,
        private readonly MailerInterface $mailer
    ) {}

    public function saveEnvironmentalData(array $data): void
    {
        if (!$this->hasAllRequiredFields($data)) {
            throw new InvalidArgumentException('Incomplete data provided.');
        }

        $environmentalData = $this->createEnvironmentalDataFromArray($data);

        $errors = $this->validator->validate($environmentalData);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new ValidatorException(implode('; ', $errorMessages));
        }

        $this->repository->save($environmentalData);

        // Check CO2 threshold and send email if necessary
        if ($environmentalData->getCo2() > self::CO2_THRESHOLD) {
            $this->notifyHighCo2($environmentalData);
        }
    }

    private function hasAllRequiredFields(array $data): bool
    {
        return !array_diff(self::REQUIRED_FIELDS, array_keys($data));
    }

    private function createEnvironmentalDataFromArray(array $data): EnvironmentalData
    {
        $measuredAt = DateTime::createFromFormat('Y-m-d H:i:s', $data['created']);
        if (!$measuredAt) {
            throw new InvalidArgumentException('Invalid date format for "created" field.');
        }

        $environmentalData = new EnvironmentalData();
        $environmentalData->setTemperature((float)$data['temperature']);
        $environmentalData->setHumidity((float)$data['humidity']);
        $environmentalData->setPressure((float)$data['pressure']);
        $environmentalData->setCo2((float)$data['co2']);
        $environmentalData->setMeasuredAt($measuredAt);
        $environmentalData->setCreatedAt(new DateTime('now'));

        return $environmentalData;
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
                $environmentalData->getCo2(),
                $environmentalData->getMeasuredAt()->format('Y-m-d H:i:s')
            ));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            error_log('Email sending failed: '.$exception->getMessage());
        }
    }
}