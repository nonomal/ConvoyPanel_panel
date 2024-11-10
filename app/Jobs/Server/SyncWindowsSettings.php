<?php
namespace Convoy\Jobs\Server;

use Convoy\Models\Server;
use Convoy\Services\Servers\ServerAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class SyncWindowsSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;

    public int $timeout = 20;

    public function __construct(protected int $serverId, protected string $password)
    {
        //
    }

    public function middleware(): array
    {
        return [new SkipIfBatchCancelled(), new WithoutOverlapping(
            "server.sync-windows-settings#{$this->serverId}",
        )];
    }

    public function handle(ServerAuthService $service): void
    {
        $server = Server::findOrFail($this->serverId);

        $service->updateWindowsPassword($server, $this->password);
    }

    /**
     * Determine the time at which the job should retry.
     *
     * @return \DateTime
     */
    public function retryAfter(): \DateTime
    {
        return now()->addSeconds(20);
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
        return now()->addSeconds($this->timeout * $this->tries);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(): void
    {
        // Mark the job as completed
        $this->delete();
    }
}