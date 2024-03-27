<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service\Provider;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;

class CustomerIdProvider implements CustomerIdProviderInterface
{
    private const CUSTOMER_EMAIL_PREFIX = 'cep';

    /**
     * @var EncryptorInterface
     */
    private readonly EncryptorInterface $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor,
    ) {
        $this->encryptor = $encryptor;
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
            $this->encryptor->hash( // @phpstan-ignore-line
                $email,
                Encryptor::HASH_VERSION_SHA256,
            ),
        );
    }
}
