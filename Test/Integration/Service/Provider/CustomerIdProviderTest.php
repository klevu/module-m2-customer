<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Test\Integration\Service\Provider;

use Klevu\Customer\Service\Provider\CustomerIdProvider;
use Klevu\Customer\Service\Provider\CustomerIdProviderInterface;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\Customer\Service\Provider\CustomerIdProvider
 */
class CustomerIdProviderTest extends TestCase
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
     * @var string[]|false|null
     */
    private array|false|null $keys = null;

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    protected function setUp(): void
    {
        $this->implementationFqcn = CustomerIdProvider::class;
        $this->interfaceFqcn = CustomerIdProviderInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $this->keys = preg_split('/\s+/s', trim((string)$deploymentConfig->get(Encryptor::PARAM_CRYPT_KEY)));
    }

    public function testGet_ReturnsEncryptedEmail(): void
    {
        $email = 'some.body@klevu.com';

        /** @var CustomerIdProvider $provider */
        $provider = $this->instantiateTestObject();

        $expectedHash = 'cep-' .
            hash_hmac(
                algo: self::HASH_SHA_256,
                data: $email,
                key: $this->keys[count($this->keys) - 1],
            );
        $this->assertSame(
            expected: $expectedHash,
            actual: $provider->get($email),
        );
    }
}
