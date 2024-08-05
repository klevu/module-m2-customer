<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Customer\Test\Integration\Service\Provider;

use Klevu\Customer\Service\Provider\GroupProvider;
use Klevu\Customer\Service\Provider\GroupProviderInterface;
use Klevu\TestFixtures\Customer\CustomerGroupTrait;
use Klevu\TestFixtures\Customer\Group\CustomerGroupFixturePool;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Klevu\TestFixtures\Website\WebsiteFixturesPool;
use Klevu\TestFixtures\Website\WebsiteTrait;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers GroupProvider
 * @method GroupProviderInterface instantiateTestObject(?array $arguments = null)
 * @method GroupProviderInterface instantiateTestObjectFromInterface(?array $arguments = null)
 */
class GroupProviderTest extends TestCase
{
    use CustomerGroupTrait;
    use ObjectInstantiationTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;
    use WebsiteTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->implementationFqcn = GroupProvider::class;
        $this->interfaceFqcn = GroupProviderInterface::class;

        $this->websiteFixturesPool = $this->objectManager->get(WebsiteFixturesPool::class);
        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);
        $this->customerGroupFixturePool = $this->objectManager->get(CustomerGroupFixturePool::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->customerGroupFixturePool->rollback();
        $this->storeFixturesPool->rollback();
        $this->websiteFixturesPool->rollback();
    }

    public function testGet_ReturnsArrayOfCustomerGroups_WithoutExcluded(): void
    {
        $this->createStore();
        $storeFixture1 = $this->storeFixturesPool->get('test_store');

        $this->createWebsite();
        $websiteFixture = $this->websiteFixturesPool->get('test_website');

        $this->createStore([
            'key' => 'test_store_2',
            'code' => 'klevu_test_store_2',
            'website_id' => $websiteFixture->getId(),
        ]);
        $storeFixture2 = $this->storeFixturesPool->get('test_store_2');

        $this->createCustomerGroup([
            'excluded_website_ids' => [
                $websiteFixture->getId(),
            ],
        ]);
        $customerGroupFixture = $this->customerGroupFixturePool->get('test_customer_group');
        $customerGroup = $customerGroupFixture->getCustomerGroup();

        $provider = $this->instantiateTestObject();

        // STORE 1
        $result1 = $provider->get(store: $storeFixture1->get());
        $this->assertIsArray(actual: $result1);
        $this->assertArrayHasKey(key: $customerGroupFixture->getId(), array: $result1);
        /** @var GroupInterface $customerGroupResult1 */
        $customerGroupResult1 = $result1[$customerGroupFixture->getId()];
        $this->assertSame(expected: $customerGroup->getCode(), actual: $customerGroupResult1->getCode());
        $this->assertSame(
            expected: (int)$customerGroup->getTaxClassId(),
            actual: (int)$customerGroupResult1->getTaxClassId(),
        );
        $this->assertNotNull(actual: $customerGroupResult1->getTaxClassName());
        $this->assertSame(
            expected: $customerGroup->getTaxClassName(),
            actual: $customerGroupResult1->getTaxClassName(),
        );

        // STORE 2
        $result2 = $provider->get(store: $storeFixture2->get());
        $this->assertIsArray(actual: $result2);
        $this->assertArrayNotHasKey(key: $customerGroupFixture->getId(), array: $result2);
    }
}
