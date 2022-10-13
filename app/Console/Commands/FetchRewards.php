<?php

namespace App\Console\Commands;

use App\Models\Reward;
use App\Models\Stake;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rewards:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all historical rewards for our stakes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Fetch current epoch
        $epochResponse = Http::post('https://api.devnet.solana.com', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getEpochInfo',
        ]);
        $currentEpoch = (int)$epochResponse->json()['result']['epoch'];

        // Fetch rewards for each stake for each epoch since activation epoch
        $stakes = Stake::all();
        foreach ($stakes as $stake) {
            $epoch = (int)$stake->activationEpoch;
            while ($epoch < $currentEpoch) {
                $this->info("Fetching rewards for $stake->pubkey at epoch $epoch");
                $rewardResponse = Http::post('https://api.devnet.solana.com', [
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'getInflationReward',
                    'params' => [[$stake->pubkey], [
                        'epoch' => $epoch,
                    ]]
                ]);
                $rewards = $rewardResponse->json()['result'][0];
                if (!is_null($rewards)) {
                    $this->info("Found rewards for $stake->pubkey at epoch $epoch");
                    Reward::updateOrCreate(
                        ['stake_id' => $stake->id, 'epoch' => (string)$epoch],
                        [
                            'amount' => (int)$rewards['amount'],
                            'commission' => (int)$rewards['commission'],
                            'effectiveSlot' => (int)$rewards['effectiveSlot'],
                            'postBalance' => (int)$rewards['postBalance'],
                        ]
                    );
                }
                $epoch++;
            }
        }

        return Command::SUCCESS;
    }
}
