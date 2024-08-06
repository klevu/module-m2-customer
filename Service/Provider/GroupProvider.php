<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Service\Provider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;

class GroupProvider implements GroupProviderInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    private readonly GroupRepositoryInterface $customerGroupRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private readonly SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private readonly GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository;
    /**
     * @var int[][]|null
     */
    private ?array $excludedWebsites = null;

    /**
     * @param GroupRepositoryInterface $customerGroupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository
     */
    public function __construct(
        GroupRepositoryInterface $customerGroupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository,
    ) {
        $this->customerGroupRepository = $customerGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
    }

    /**
     * @param StoreInterface $store
     *
     * @return array<int, GroupInterface>
     * @throws LocalizedException
     */
    public function get(StoreInterface $store): array
    {
        $return = [];
        foreach ($this->getCustomerGroups() as $customerGroup) {
            if ($this->isWebsiteExcluded($customerGroup, (int)$store->getWebsiteId())) {
                continue;
            }
            $return[(int)$customerGroup->getId()] = $customerGroup;
        }

        return $return;
    }

    /**
     * @return GroupInterface[]
     * @throws LocalizedException
     */
    private function getCustomerGroups(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $groupsSearchResults = $this->customerGroupRepository->getList(searchCriteria: $searchCriteria);

        return $groupsSearchResults->getItems();
    }

    /**
     * @param GroupInterface $group
     * @param int $websiteId
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isWebsiteExcluded(GroupInterface $group, int $websiteId): bool
    {
        $excludedWebsites = $this->getExcludedWebsites();

        return ($excludedWebsites[$group->getId()] ?? null)
            && in_array($websiteId, $excludedWebsites[$group->getId()], true);
    }

    /**
     * @return int[][]
     * @throws LocalizedException
     */
    private function getExcludedWebsites(): array
    {
        if (null === $this->excludedWebsites) {
            // @phpstan-ignore-next-line argument.type (Magento docBlock is incorrect int[] should be int[][])
            $this->excludedWebsites = $this->groupExcludedWebsiteRepository->getAllExcludedWebsites();
        }

        return $this->excludedWebsites;
    }
}
