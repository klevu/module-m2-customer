<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service\Provider;

interface CustomerIdProviderInterface
{
    /**
     * @param string $email
     *
     * @return string
     */
    public function get(string $email): string;
}
