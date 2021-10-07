<?php

namespace App\Context\User\Contract;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Promise\PromiseInterface;

interface ApiConsumer
{
    public function fetchData(int $page = 1): PromiseInterface|Response;

    public function consumedDataToCollection(): Collection;

    public function mapCollectionToModel(Collection $dataCollection, bool $dryRun = false): void;
}
