<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service\Provider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;

interface GroupProviderInterface
{
    /**
     * @param StoreInterface $store
     *
     * @return array<int, GroupInterface>
     */
    public function get(StoreInterface $store): array;
}
