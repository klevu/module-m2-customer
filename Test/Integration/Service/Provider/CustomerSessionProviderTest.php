<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Test\Integration\Service\Provider;

use Klevu\Customer\Service\Provider\CustomerSessionProvider;
use Klevu\Customer\Service\Provider\CustomerSessionProviderInterface;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\Customer\Service\Provider\CustomerSessionProvider
 */
class CustomerSessionProviderTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

    private const HASH_SHA_256 = 'sha256';

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var MockObject|SessionManagerInterface
     */
    private SessionManagerInterface|MockObject $mockSessionManager;
    /**
     * @var array|false|string[]
     */
    private array|false $keys;

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    protected function setUp(): void
    {
        $this->implementationFqcn = CustomerSessionProvider::class;
        $this->interfaceFqcn = CustomerSessionProviderInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
        $this->mockSessionManager = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $this->keys = preg_split(
            pattern: '/\s+/s',
            subject: trim((string)$deploymentConfig->get(Encryptor::PARAM_CRYPT_KEY)),
        );
    }

    public function testGet_ReturnsHashedSessionId(): void
    {
        $sessionId = '1234567890';

        $this->mockSessionManager->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        /** @var CustomerSessionProviderInterface $provider */
        $provider = $this->instantiateTestObject([
            'sessionManager' => $this->mockSessionManager,
        ]);

        $expectedHash = hash_hmac(
            algo: self::HASH_SHA_256,
            data: $sessionId,
            key: $this->keys[count($this->keys) - 1],
        );
        $this->assertSame(
            expected: $expectedHash,
            actual: $provider->get(),
        );
    }
}
