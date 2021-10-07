<?php

namespace App\Context\User\Services;

use App\Context\User\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Promise\PromiseInterface;
use App\Context\User\Contract\ApiConsumer;
use App\Context\User\Exceptions\ReqresHttpException;
use App\Context\User\Exceptions\InvalidApiUrlException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

final class ReqresService implements ApiConsumer
{
    protected ?string $apiUrl;

    private ?LengthAwarePaginator $lengthAwarePaginator = null;

    public function __construct()
    {
        $this->apiUrl = config('api.user_data');
    }

    /**
     * @throws ReqresHttpException
     * @throws InvalidApiUrlException
     */
    public function fetchData(int $page = 1): Response
    {
        if (!$this->apiUrl) {
            throw new InvalidApiUrlException('Missing REQRES API URL.');
        }

        $response = Http::accept('application/json')
            ->get($this->apiUrl, [
                'page' => $page,
            ]);

        if ($response->failed()) {
            throw new ReqresHttpException('Failed to fetch from ' . $this->apiUrl);
        }

        return $response;
    }

    /**
     * @throws \App\Context\User\Exceptions\InvalidApiUrlException
     * @throws \App\Context\User\Exceptions\ReqresHttpException
     */
    public function consumedDataToCollection(): Collection
    {
        $responseCollection = $this->fetchData()->collect();
        $this->lengthAwarePaginator = $this->createPaginator($responseCollection);

        return $this->paginateApi();
    }

    // Missing validation for this quick test, and I'd maybe even queue a task up and churn through redis.
    public function mapCollectionToModel(Collection $dataCollection, bool $dryRun = false): void
    {
        if ($dryRun) {
            dump($dataCollection);
        } else {
            $dataCollection->chunk(config('api.import_chunksize', 10))
                ->map(static function ($users) {
                    User::query()->insert(collect($users)->values()->toArray());
                }
            );
        }
    }

    /**
     * @throws \App\Context\User\Exceptions\InvalidApiUrlException
     * @throws \App\Context\User\Exceptions\ReqresHttpException
     */
    private function paginateApi(): Collection
    {
        $dataCollection = collect();

        if ($this->lengthAwarePaginator) {
            foreach ($this->lengthAwarePaginator->items() as $page) {
                $dataCollection = $dataCollection->merge(
                    $this->fetchData($page)->collect()->get('data')
                );
            }
        }

        return $dataCollection;
    }

    private function createPaginator(Collection $collection): LengthAwarePaginator
    {
        $currentPage = $collection->get('page');
        $total = $collection->get('total');
        $perPage = $collection->get('per_page');

        $items = array_slice(
            range(1, $collection->get('total_pages')),
            ($currentPage * $perPage) - $perPage,
            $perPage,
            true
        );

        return new Paginator($items, $total, $perPage, $currentPage);
    }
}
