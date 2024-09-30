<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service;

use Magento\Framework\Encryption\Encryptor;

interface HasherInterface
{
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
    ): string;
}
