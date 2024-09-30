<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Test\Integration\Service;

use Klevu\Customer\Service\Hasher;
use Klevu\Customer\Service\HasherInterface;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers Hasher::class
 * @method HasherInterface instantiateTestObject(?array $arguments = null)
 * @method HasherInterface instantiateTestObjectFromInterface(?array $arguments = null)
 */
class HasherTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null; // @phpstan-ignore-line

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = Hasher::class;
        $this->interfaceFqcn = HasherInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testExecute_ThrowsException_WhParamKeyDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No key available');

        $mockDeploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockDeploymentConfig->expects($this->once())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->willReturn('');

        $hasher = $this->instantiateTestObject([
            'deploymentConfig' => $mockDeploymentConfig,
        ]);
        $hasher->execute(data: 'string', version: Encryptor::HASH_VERSION_SHA256);
    }

    public function testExecute_ThrowsException_WhenVersionDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown hashing algorithm');

        $hasher = $this->instantiateTestObject();
        $hasher->execute(data: 'string', version: 9999999999);
    }

    public function testExecute_ReturnsHashOfData_UsingSha256(): void
    {
        $data = 'string';

        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $keys = preg_split(
            '/\s+/s',
            trim((string)$deploymentConfig->get(Encryptor::PARAM_CRYPT_KEY)),
        );
        $version = count($keys) - 1;

        $expected = hash_hmac(
            algo: 'sha256',
            data: $data,
            key: $keys[$version],
            binary: false,
        );

        $hasher = $this->instantiateTestObject();
        $result = $hasher->execute(data: $data, version: Encryptor::HASH_VERSION_SHA256);

        $this->assertSame(
            expected: $expected,
            actual: $result,
        );
    }
}
