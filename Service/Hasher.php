<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;

class Hasher implements HasherInterface
{
    /**
     * Map of simple hash versions
     *
     * @var array<int, string>
     */
    private array $hashVersionMap = [
        Encryptor::HASH_VERSION_MD5 => 'md5',
        Encryptor::HASH_VERSION_SHA256 => 'sha256',
    ];
    /**
     * Version of encryption key
     *
     * @var int
     */
    private int $keyVersion;
    /**
     * Array of encryption keys
     *
     * @var string[]
     */
    private array $keys = [];

    /**
     * @param DeploymentConfig $deploymentConfig
     *
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
    ) {
        $this->keys = preg_split(
            '/\s+/s',
            trim((string)$deploymentConfig->get(Encryptor::PARAM_CRYPT_KEY)),
        );
        $this->keyVersion = count($this->keys) - 1;
    }

    /**
     * Hash a string.
     * Returns one-way encrypted string, always the same result for the same value. Suitable for signatures.
     *
     * @param string $data
     * @param int $version
     * @param bool $binary
     *
     * @return string
     *
     * Magento updated this method in 2.4.7 and decoded the key.
     * This lead to our customer and session identifiers changing for the same data.
     * @see Encryptor::hash
     */
    public function execute(
        string $data,
        int $version = Encryptor::HASH_VERSION_SHA256,
        bool $binary = false,
    ): string {
        if (empty($this->keys[$this->keyVersion])) {
            throw new \RuntimeException(message: 'No key available');
        }
        if (!array_key_exists(key: $version, array: $this->hashVersionMap)) {
            throw new \InvalidArgumentException(message: 'Unknown hashing algorithm');
        }

        return hash_hmac(
            algo: $this->hashVersionMap[$version],
            data: $data,
            key: $this->keys[$this->keyVersion],
            binary: $binary,
        );
    }
}
