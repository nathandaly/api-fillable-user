<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Context\User\Services\ReqresService;
use App\Context\User\Events\UserDataImported;
use App\Context\User\Exceptions\ReqresHttpException;
use App\Context\User\Exceptions\InvalidApiUrlException;

class ImportReqresDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:reqres-data
        {--dry-run : Whether to persist user data to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import reqres.in\'s user data to fill our local user model';

    private ReqresService $reqresService;

    protected bool $dryRun = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ReqresService $reqresService)
    {
        parent::__construct();

        $this->reqresService = $reqresService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->dryRun = (bool) $this->option('dry-run');

        $this->info('Starting user data Import');

        if ($this->dryRun) {
            $this->output->info('DRY RUN MODE');
        }

        $collection = collect([]);

        try {
            $collection = $this->reqresService->consumedDataToCollection();
        } catch(InvalidApiUrlException $e) {
            $this->output->error($e->getMessage());
        } catch (ReqresHttpException) {
            $this->output->error('Failed to fetch data from REQRES.');
        }

        // I would maybe use a callback or events to update a progress bar?
        $this->reqresService->mapCollectionToModel($collection, $this->dryRun);

        UserDataImported::dispatch();

        $this->output->success('Import complete');
    }
}
