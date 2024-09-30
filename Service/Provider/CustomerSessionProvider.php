<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service\Provider;

use Klevu\Customer\Service\HasherInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Session\SessionManagerInterface;

class CustomerSessionProvider implements CustomerSessionProviderInterface
{
    /**
     * @var HasherInterface
     */
    private HasherInterface $hasher;
    /**
     * @var SessionManagerInterface
     */
    private readonly SessionManagerInterface $sessionManager;

    /**
     * @param HasherInterface $hasher
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        HasherInterface $hasher,
        SessionManagerInterface $sessionManager,
    ) {
        $this->hasher = $hasher;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        $sessionId = $this->sessionManager->getSessionId();

        return $this->hasher->execute(
            data: $sessionId,
            version: Encryptor::HASH_VERSION_SHA256,
            binary: false,
        );
    }
}
