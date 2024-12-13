<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Redis;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;

class ProductRecalculationCacheFacade
{
    protected const int TTL = 10800;

    /**
     * @param \Redis $redisClient
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisFacade $redisFacade
     */
    public function __construct(
        protected readonly Redis $redisClient,
        protected readonly RedisFacade $redisFacade,
    ) {
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function contains(
        int $productId,
    ): bool {
        $cacheId = $this->getCacheId($productId);

        return (bool)$this->redisClient->exists($cacheId);
    }

    /**
     * @param int $productId
     * @return string
     */
    protected function getCacheId(
        int $productId,
    ): string {
        return (string)$productId;
    }

    /**
     * @param int $productId
     * @param string[] $scopes
     */
    public function save(
        int $productId,
        array $scopes = [],
    ): void {
        $cacheId = $this->getCacheId($productId);
        $this->redisClient->set($cacheId, $this->buildValueByProductIdAndScopes($productId, $scopes), ['ex' => self::TTL]);
    }

    /**
     * @param int $productId
     * @param string[] $scopes
     * @return string
     */
    protected function buildValueByProductIdAndScopes(
        int $productId,
        array $scopes = [],
    ): string {
        if (count($scopes) > 0 && $this->contains($productId)) {
            $cacheId = $this->getCacheId($productId);
            $scopesString = $this->redisClient->get($cacheId);

            if ($scopesString !== false && $scopesString !== '') {
                $scopes = array_unique(array_merge($scopes, explode(',', $scopesString)));
            } else {
                $scopes = [];
            }
        }

        return implode('|', $scopes);
    }

    /**
     * @param int[] $productIds
     */
    public function delete(
        array $productIds,
    ): void {
        foreach ($productIds as $productId) {
            $cacheId = $this->getCacheId($productId);
            $this->redisClient->del($cacheId);
        }
    }

    /**
     * @param int $productId
     * @throws \RedisException
     * @return array
     */
    protected function getScopesByProductId(int $productId): array
    {
        $cacheId = $this->getCacheId($productId);
        $scopesString = $this->redisClient->get($cacheId);

        if ($scopesString !== '') {
            return explode(',', $scopesString);
        }

        return [];
    }

    /**
     * @param string[][] $exportScopesIndexedByProductId
     * @return array<int,string[]>
     */
    public function getScopesIndexedByProductId(array $exportScopesIndexedByProductId): array
    {
        $result = [];

        foreach ($exportScopesIndexedByProductId as $productId => $exportScopes) {
            if ($this->contains($productId)) {
                $cachedScopes = $this->getScopesByProductId($productId);

                $result[$productId] = array_unique(array_merge($cachedScopes, $exportScopes));
            } else {
                $result[$productId] = [];
            }
        }

        return $result;
    }

    public function clear(): void
    {
        $this->redisFacade->cleanCache();
    }
}
