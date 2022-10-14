<?php

namespace App\Console\Commands;

use App\Models\Stake;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchStakes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:stakes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all stakes from our solana vote account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Fetch stakes on our vote account
        $stakeAccountsResponse = Http::post(env('SOLANA_RPC'), [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getProgramAccounts',
            'params' => ["Stake11111111111111111111111111111111111111", [
                'encoding' => 'jsonParsed',
                'filters' => [
                    [
                        'memcmp' => [
                            'bytes' => env('SOLANA_VOTE_ACCOUNT'),
                            'offset' => 124
                        ]
                    ]
                ]
            ]]
        ]);

        $stakeAccounts = $stakeAccountsResponse->json()['result'];

        // Fetch stake's state and store or update stake in DB
        foreach ($stakeAccounts as $stakeAccount) {
            $stateResponse = Http::post(env('SOLANA_RPC'), [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getStakeActivation',
                'params' => [$stakeAccount['pubkey']]
            ]);
            $state = $stateResponse->json()['result']['state'];

            Stake::updateOrCreate(
                ['pubkey' => $stakeAccount['pubkey']],
                [
                    'withdrawer' => $stakeAccount['account']['data']['parsed']['info']['meta']['authorized']['withdrawer'],
                    'staker' => $stakeAccount['account']['data']['parsed']['info']['meta']['authorized']['staker'],
                    'activationEpoch' => $stakeAccount['account']['data']['parsed']['info']['stake']['delegation']['activationEpoch'],
                    'initial_balance_lamports' => (int)$stakeAccount['account']['data']['parsed']['info']['stake']['delegation']['stake'],
                    'delegated_vote_account' => $stakeAccount['account']['data']['parsed']['info']['stake']['delegation']['voter'],
                    'balance_lamports' => (int)$stakeAccount['account']['lamports'],
                    'state' => $state,
                ]
            );
        }
        return Command::SUCCESS;
    }
}
