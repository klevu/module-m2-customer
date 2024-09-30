<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service\Provider;

use Klevu\Customer\Service\HasherInterface;
use Magento\Framework\Encryption\Encryptor;

class CustomerIdProvider implements CustomerIdProviderInterface
{
    private const CUSTOMER_EMAIL_PREFIX = 'cep';
    
    /**
     * @var HasherInterface
     */
    private HasherInterface $hasher;

    /**
     * @param HasherInterface $hasher
     */
    public function __construct(
        HasherInterface $hasher,
    ) {
        $this->hasher = $hasher;
    }

    /**
     * @param string $email
     *
     * @return string
     */
    public function get(string $email): string
    {
        return sprintf(
            '%s-%s',
            self::CUSTOMER_EMAIL_PREFIX,
            $this->hasher->execute(
                data: $email,
                version: Encryptor::HASH_VERSION_SHA256,
                binary: false,
            ),
        );
    }
}
