<?php

declare(strict_types=1);

namespace App\Notification\Service;

use App\Api\SensorData\Entity\SensorData;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Notification Service.
 */
class NotificationService
{
    /** The CO2 threshold */
    private const int CO2_THRESHOLD = 1600;

    private MailerInterface $mailer;
    private string $senderEmail;
    private string $receiverEmail;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmail,
        string $receiverEmail
    ) {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->receiverEmail = $receiverEmail;
    }

    /**
     * Decides whether a notification should be sent based on the current and last CO2 levels.
     */
    public function notifyBasedOnCo2Levels(SensorData $currentData, ?SensorData $previousData): void
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

    private function formatNotificationMessage(string $statusText, int $co2Value, SensorData $data): string
    {
        return \sprintf(
            "The CO2 level %s the threshold of %d ppm.\n\nDetails:\n- CO2 Level: %d ppm\n- Measured At: %s",
            $statusText,
            self::CO2_THRESHOLD,
            $co2Value,
            $data->getMeasuredAt()->format('Y-m-d H:i:s')
        );
    }

    private function isCrossingHighThreshold(int $currentCo2Value, int $lastCo2Value): bool
    {
        return $currentCo2Value >= self::CO2_THRESHOLD && $lastCo2Value < self::CO2_THRESHOLD;
    }

    private function isCrossingLowThreshold(int $currentCo2Value, int $lastCo2Value): bool
    {
        return $currentCo2Value < self::CO2_THRESHOLD && $lastCo2Value >= self::CO2_THRESHOLD;
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
            ->from($this->senderEmail)
            ->to($this->receiverEmail)
            ->subject($subject)
            ->text($text);
    }
}
