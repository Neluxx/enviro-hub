<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\EnvironmentalData;
use App\Service\EnvironmentalDataNotificationService;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EnvironmentalDataNotificationServiceTest extends TestCase
{
    private MailerInterface $mailer;
    private EnvironmentalDataNotificationService $service;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->service = new EnvironmentalDataNotificationService($this->mailer);
    }

    public function testNotifyBasedOnCo2LevelsWhenCrossingHighThresholdWithoutPreviousData(): void
    {
        $currentData = $this->createEnvironmentalData(1000.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) use ($currentData) {
                return str_contains($email->getSubject(), 'High CO2 Alert!')
                    && str_contains($email->getTextBody(), 'exceeds the threshold of 1000 ppm')
                    && str_contains($email->getTextBody(), '1000 ppm')
                    && str_contains($email->getTextBody(), $currentData->getMeasuredAt()->format('Y-m-d H:i:s'));
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, null);
    }

    public function testNotifyBasedOnCo2LevelsWhenCrossingHighThresholdWithPreviousDataBelowThreshold(): void
    {
        $currentData = $this->createEnvironmentalData(1000.0);
        $previousData = $this->createEnvironmentalData(999.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return str_contains($email->getSubject(), 'High CO2 Alert!')
                    && str_contains($email->getTextBody(), 'exceeds');
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsWhenCrossingHighThresholdAboveThreshold(): void
    {
        $currentData = $this->createEnvironmentalData(1500.0);
        $previousData = $this->createEnvironmentalData(999.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return str_contains($email->getSubject(), 'High CO2 Alert!')
                    && str_contains($email->getTextBody(), 'exceeds')
                    && str_contains($email->getTextBody(), '1500 ppm');
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsWhenCrossingLowThresholdWithoutPreviousData(): void
    {
        $currentData = $this->createEnvironmentalData(999.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) use ($currentData) {
                return str_contains($email->getSubject(), 'Low CO2 Alert!')
                    && str_contains($email->getTextBody(), 'is below the threshold of 1000 ppm')
                    && str_contains($email->getTextBody(), '999 ppm')
                    && str_contains($email->getTextBody(), $currentData->getMeasuredAt()->format('Y-m-d H:i:s'));
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, null);
    }

    public function testNotifyBasedOnCo2LevelsWhenCrossingLowThresholdWithPreviousDataAboveThreshold(): void
    {
        $currentData = $this->createEnvironmentalData(999.0);
        $previousData = $this->createEnvironmentalData(1000.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return str_contains($email->getSubject(), 'Low CO2 Alert!')
                    && str_contains($email->getTextBody(), 'is below');
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsWhenCrossingLowThresholdBelowThreshold(): void
    {
        $currentData = $this->createEnvironmentalData(500.0);
        $previousData = $this->createEnvironmentalData(1000.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return str_contains($email->getSubject(), 'Low CO2 Alert!')
                    && str_contains($email->getTextBody(), 'is below')
                    && str_contains($email->getTextBody(), '500 ppm');
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsWhenNotCrossingThresholdBothAbove(): void
    {
        $currentData = $this->createEnvironmentalData(1100.0);
        $previousData = $this->createEnvironmentalData(1000.0);

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsWhenNotCrossingThresholdBothBelow(): void
    {
        $currentData = $this->createEnvironmentalData(900.0);
        $previousData = $this->createEnvironmentalData(999.0);

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsWhenCurrentIsExactlyAtThresholdAndPreviousBelow(): void
    {
        $currentData = $this->createEnvironmentalData(1000.0);
        $previousData = $this->createEnvironmentalData(999.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return str_contains($email->getSubject(), 'High CO2 Alert!');
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsWhenTransportExceptionIsThrown(): void
    {
        $currentData = $this->createEnvironmentalData(1000.0);
        $previousData = $this->createEnvironmentalData(999.0);

        $transportException = new TransportException('SMTP connection failed');

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willThrowException($transportException);

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    public function testNotifyBasedOnCo2LevelsEmailContainsCorrectSenderAndReceiver(): void
    {
        $currentData = $this->createEnvironmentalData(1000.0);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                $from = $email->getFrom();
                $to = $email->getTo();

                return \count($from) === 1
                    && $from[0]->getAddress() === 'info@fabian-arndt.dev'
                    && \count($to) === 1
                    && $to[0]->getAddress() === 'fabian.arndt96@proton.me';
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, null);
    }

    public function testNotifyBasedOnCo2LevelsHighAlertMessageFormat(): void
    {
        $measuredAt = new DateTime('2025-01-15 14:30:00');
        $currentData = new EnvironmentalData(25.5, 60.0, 1013.25, 1200.0, $measuredAt);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                $body = $email->getTextBody();

                return str_contains($body, 'The CO2 level exceeds the threshold of 1000 ppm')
                    && str_contains($body, 'Details:')
                    && str_contains($body, '- CO2 Level: 1200 ppm')
                    && str_contains($body, '- Measured At: 2025-01-15 14:30:00');
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, null);
    }

    public function testNotifyBasedOnCo2LevelsLowAlertMessageFormat(): void
    {
        $measuredAt = new DateTime('2025-01-15 14:30:00');
        $currentData = new EnvironmentalData(25.5, 60.0, 1013.25, 800.0, $measuredAt);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                $body = $email->getTextBody();

                return str_contains($body, 'The CO2 level is below the threshold of 1000 ppm')
                    && str_contains($body, 'Details:')
                    && str_contains($body, '- CO2 Level: 800 ppm')
                    && str_contains($body, '- Measured At: 2025-01-15 14:30:00');
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, null);
    }

    private function createEnvironmentalData(float $co2): EnvironmentalData
    {
        return new EnvironmentalData(
            25.5,
            60.0,
            1013.25,
            $co2,
            new DateTime('2025-01-15 12:00:00')
        );
    }
}
