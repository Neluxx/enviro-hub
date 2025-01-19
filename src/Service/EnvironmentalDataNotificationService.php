<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EnvironmentalDataNotificationService
{
    private const CO2_THRESHOLD = 1000;
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Decides whether a notification should be sent based on the current and last CO2 levels.
     */
    public function notifyBasedOnCo2Levels(EnvironmentalData $currentData, ?EnvironmentalData $previousData): void
    {
        $currentCo2Value = $currentData->getCarbonDioxide();
        $lastCo2Value = $previousData?->getCarbonDioxide();

        if ($this->isCrossingHighThreshold($currentCo2Value, $lastCo2Value)) {
            $this->sendNotification($currentData, 'High CO2 Alert!', sprintf(
                "The CO2 level has exceeded the threshold of %d ppm.\n\nDetails:\n- CO2 Level: %d ppm\n- Measured At: %s",
                self::CO2_THRESHOLD,
                $currentCo2Value,
                $currentData->getMeasuredAt()->format('Y-m-d H:i:s')
            ));
        } elseif ($this->isCrossingLowThreshold($currentCo2Value, $lastCo2Value)) {
            $this->sendNotification($currentData, 'Low CO2 Alert!', sprintf(
                "The CO2 level is below the threshold of %d ppm.\n\nDetails:\n- CO2 Level: %d ppm\n- Measured At: %s",
                self::CO2_THRESHOLD,
                $currentCo2Value,
                $currentData->getMeasuredAt()->format('Y-m-d H:i:s')
            ));
        }
    }

    private function isCrossingHighThreshold(float $currentCo2Value, ?float $lastCo2Value): bool
    {
        return $currentCo2Value >= self::CO2_THRESHOLD && ($lastCo2Value === null || $lastCo2Value < self::CO2_THRESHOLD);
    }

    private function isCrossingLowThreshold(float $currentCo2Value, ?float $lastCo2Value): bool
    {
        return $currentCo2Value < self::CO2_THRESHOLD && ($lastCo2Value === null || $lastCo2Value >= self::CO2_THRESHOLD);
    }

    private function sendNotification(EnvironmentalData $environmentalData, string $subject, string $message): void
    {
        $email = $this->createEmail($subject, $message);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            error_log('Email sending failed: ' . $exception->getMessage());
        }
    }

    private function createEmail(string $subject, string $text): Email
    {
        return (new Email())
            ->from('info@fabian-arndt.dev') // replace with the actual sender email
            ->to('fabian.arndt96@proton.me') // replace with the actual receiver email
            ->subject($subject)
            ->text($text);
    }
}