<?php

declare(strict_types=1);

namespace App\Tests\Notification\Service;

use App\Api\SensorData\Entity\SensorData;
use App\Notification\Service\NotificationService;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Test class for NotificationService.
 */
class NotificationServiceTest extends TestCase
{
    private const SENDER_EMAIL = 'sender@example.com';
    private const RECEIVER_EMAIL = 'receiver@example.com';
    private const CO2_THRESHOLD = 1600;
    private MailerInterface&MockObject $mailer;
    private NotificationService $service;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->service = new NotificationService(
            $this->mailer,
            self::SENDER_EMAIL,
            self::RECEIVER_EMAIL
        );
    }

    /**
     * Test that no notification is sent when current CO2 is null.
     */
    public function testNotifyBasedOnCo2LevelsDoesNotSendWhenCurrentCo2IsNull(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: null,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: 1500.0,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->never())
            ->method('send');

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that no notification is sent when previous CO2 is null.
     */
    public function testNotifyBasedOnCo2LevelsDoesNotSendWhenPreviousCo2IsNull(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: 1700.0,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: null,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->never())
            ->method('send');

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that no notification is sent when previous data is null.
     */
    public function testNotifyBasedOnCo2LevelsDoesNotSendWhenPreviousDataIsNull(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: 1700.0,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $this->mailer->expects($this->never())
            ->method('send');

        $this->service->notifyBasedOnCo2Levels($currentData, null);
    }

    /**
     * Test that high CO2 alert is sent when crossing threshold upward.
     */
    public function testNotifyBasedOnCo2LevelsSendsHighAlertWhenCrossingThresholdUpward(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: 1700.0,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: 1500.0,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                $fromAddresses = $email->getFrom();
                $toAddresses = $email->getTo();

                static::assertCount(1, $fromAddresses);
                static::assertCount(1, $toAddresses);
                static::assertEquals(self::SENDER_EMAIL, $fromAddresses[0]->getAddress());
                static::assertEquals(self::RECEIVER_EMAIL, $toAddresses[0]->getAddress());
                static::assertEquals('High CO2 Alert!', $email->getSubject());
                static::assertStringContainsString('exceeds the threshold of 1600 ppm', $email->getTextBody());
                static::assertStringContainsString('CO2 Level: 1700 ppm', $email->getTextBody());
                static::assertStringContainsString('2024-01-15 10:00:00', $email->getTextBody());

                return true;
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that low CO2 alert is sent when crossing threshold downward.
     */
    public function testNotifyBasedOnCo2LevelsSendsLowAlertWhenCrossingThresholdDownward(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: 1500.0,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: 1700.0,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                $fromAddresses = $email->getFrom();
                $toAddresses = $email->getTo();

                static::assertCount(1, $fromAddresses);
                static::assertCount(1, $toAddresses);
                static::assertEquals(self::SENDER_EMAIL, $fromAddresses[0]->getAddress());
                static::assertEquals(self::RECEIVER_EMAIL, $toAddresses[0]->getAddress());
                static::assertEquals('Low CO2 Alert!', $email->getSubject());
                static::assertStringContainsString('is below the threshold of 1600 ppm', $email->getTextBody());
                static::assertStringContainsString('CO2 Level: 1500 ppm', $email->getTextBody());
                static::assertStringContainsString('2024-01-15 10:00:00', $email->getTextBody());

                return true;
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that no notification is sent when CO2 stays above threshold.
     */
    public function testNotifyBasedOnCo2LevelsDoesNotSendWhenCo2StaysAboveThreshold(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: 1800.0,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: 1700.0,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->never())
            ->method('send');

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that no notification is sent when CO2 stays below threshold.
     */
    public function testNotifyBasedOnCo2LevelsDoesNotSendWhenCo2StaysBelowThreshold(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: 1400.0,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: 1500.0,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->never())
            ->method('send');

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that high alert is sent when CO2 equals threshold exactly.
     */
    public function testNotifyBasedOnCo2LevelsSendsHighAlertWhenCo2EqualsThreshold(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: (float) self::CO2_THRESHOLD,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: 1500.0,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                static::assertEquals('High CO2 Alert!', $email->getSubject());

                return true;
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that high alert is sent with boundary value just above threshold.
     */
    public function testNotifyBasedOnCo2LevelsSendsHighAlertWithBoundaryValueJustAboveThreshold(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: self::CO2_THRESHOLD + 0.1,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: self::CO2_THRESHOLD - 0.1,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                static::assertEquals('High CO2 Alert!', $email->getSubject());

                return true;
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }

    /**
     * Test that low alert is sent with boundary value just below threshold.
     */
    public function testNotifyBasedOnCo2LevelsSendsLowAlertWithBoundaryValueJustBelowThreshold(): void
    {
        $currentData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: self::CO2_THRESHOLD - 0.1,
            measuredAt: new DateTime('2024-01-15 10:00:00'),
        );

        $previousData = new SensorData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.0,
            humidity: 64.0,
            pressure: 1013.0,
            carbonDioxide: self::CO2_THRESHOLD + 0.1,
            measuredAt: new DateTime('2024-01-15 09:00:00'),
        );

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                static::assertEquals('Low CO2 Alert!', $email->getSubject());

                return true;
            }));

        $this->service->notifyBasedOnCo2Levels($currentData, $previousData);
    }
}
