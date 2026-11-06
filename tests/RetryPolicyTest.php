<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Exception\InvalidSettingsException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RetryPolicyTest extends TestCase
{
    #[Test]
    public function itShouldCalculateExponentialBackoff(): void
    {
        $policy = new RetryPolicy(maxRetries: 4, delay: 100, multiplier: 2.0);

        $this->assertSame(100, $policy->delayFor(1));
        $this->assertSame(200, $policy->delayFor(2));
        $this->assertSame(400, $policy->delayFor(3));
    }

    #[Test]
    public function itShouldUseConstantDelayByDefault(): void
    {
        $policy = new RetryPolicy(maxRetries: 3, delay: 50);

        $this->assertSame(50, $policy->delayFor(1));
        $this->assertSame(50, $policy->delayFor(3));
    }

    #[Test]
    #[DataProvider('invalidPolicies')]
    public function itShouldRejectInvalidPolicies(int $retries, int $delay, float $multiplier, string $message): void
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage($message);

        new RetryPolicy($retries, $delay, $multiplier);
    }

    /**
     * @return array<string, array{int, int, float, string}>
     */
    public static function invalidPolicies(): array
    {
        return [
            'no retries' => [0, 0, 1.0, 'Max retries must be at least 1.'],
            'negative delay' => [1, -1, 1.0, 'Retry delay must not be negative.'],
            'shrinking multiplier' => [1, 0, 0.5, 'Retry multiplier must be at least 1.0.'],
        ];
    }
}
