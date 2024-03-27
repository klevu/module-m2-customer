<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service\Provider;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CustomerSessionProvider implements CustomerSessionProviderInterface
{
    /**
     * @var EncryptorInterface
     */
    private readonly EncryptorInterface $encryptor;
    /**
     * @var SessionManagerInterface
     */
    private readonly SessionManagerInterface $sessionManager;

    /**
     * @param EncryptorInterface $encryptor
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        EncryptorInterface $encryptor,
        SessionManagerInterface $sessionManager,
    ) {
        $this->encryptor = $encryptor;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @return string|null
     */
    public function get(): ?string
    {
        $sessionId = $this->sessionManager->getSessionId();

        return $this->encryptor->hash( // @phpstan-ignore-line
            $sessionId,
            Encryptor::HASH_VERSION_SHA256,
        );
    }
}
