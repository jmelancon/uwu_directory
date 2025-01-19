<?php
declare(strict_types=1);

namespace App\Interface;

interface PageableInterface
{
    /**
     * When fetching pageable data from a source, we may have certain requirements
     * to interface with the source properly. This method ensures that the proper
     * parameters are available to the implementor.
     *
     * @param int $pageSize
     * The number of rows requested by the client. Some sources may have a cap
     * on data sizes, so this allows the size to be checked before issuing the request.
     *
     * @param array $context
     * A key:value set of context items to be passed to the data source
     * (if needed).
     *
     * @return bool
     * Whether the implementing class can support the request with the given parameters.
     */
    public function supports(int $pageSize, array $context = []): bool;

    /**
     * Process the request for paginated data.
     *
     * @param int $pageSize
     * Number of rows requested.
     *
     * @param int $offset
     * Row offset. Defaults to zero.
     *
     * @param array $context
     * Context to provide to the data provider
     *
     * @return array<array>|array<void>
     * Returns the data requested. Array will be empty if there is no data to return.
     */
    public function fetch(int $pageSize, int $offset = 0, array $context = []): array;
}