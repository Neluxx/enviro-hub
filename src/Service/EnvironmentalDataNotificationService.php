<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EnvironmentalData;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Environmental Data Notification Service.
 */
class EnvironmentalDataNotificationService
{
    /** The CO2 threshold */
    private const CO2_THRESHOLD = 1000;

    /**
     * The sender mail address.
     *
     * @todo Replace with the actual sender email
     */
    private const SENDER_EMAIL = 'info@fabian-arndt.dev';

    /**
     * The receiver mail address.
     *
     * @todo Replace with the actual receiver email
     */
    private const RECEIVER_EMAIL = 'fabian.arndt96@proton.me';

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

        if ($currentCo2Value === null || $lastCo2Value === null) {
            return;
        }

        if ($this->isCrossingHighThreshold($currentCo2Value, $lastCo2Value)) {
            $message = $this->formatNotificationMessage('exceeds', $currentCo2Value, $currentData);
            $this->sendNotification('High CO2 Alert!', $message);
        } elseif ($this->isCrossingLowThreshold($currentCo2Value, $lastCo2Value)) {
            $message = $this->formatNotificationMessage('is below', $currentCo2Value, $currentData);
            $this->sendNotification('Low CO2 Alert!', $message);
        }
    }

    private function formatNotificationMessage(string $statusText, float $co2Value, EnvironmentalData $data): string
    {
        return \sprintf(
            "The CO2 level %s the threshold of %d ppm.\n\nDetails:\n- CO2 Level: %d ppm\n- Measured At: %s",
            $statusText,
            self::CO2_THRESHOLD,
            $co2Value,
            $data->getMeasuredAt()->format('Y-m-d H:i:s')
        );
    }

    private function isCrossingHighThreshold(float $currentCo2Value, ?float $lastCo2Value): bool
    {
        return $currentCo2Value >= self::CO2_THRESHOLD && (null === $lastCo2Value || $lastCo2Value < self::CO2_THRESHOLD);
    }

    private function isCrossingLowThreshold(float $currentCo2Value, ?float $lastCo2Value): bool
    {
        return $currentCo2Value < self::CO2_THRESHOLD && (null === $lastCo2Value || $lastCo2Value >= self::CO2_THRESHOLD);
    }

    private function sendNotification(string $subject, string $message): void
    {
        $email = $this->createEmail($subject, $message);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            error_log('Email sending failed: '.$exception->getMessage());
        }
    }

    private function createEmail(string $subject, string $text): Email
    {
        return (new Email())
            ->from(self::SENDER_EMAIL)
            ->to(self::RECEIVER_EMAIL)
            ->subject($subject)
            ->text($text);
    }
}
