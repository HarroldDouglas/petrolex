<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Force sync queue connection for tests to prevent transaction conflicts
        Config::set('queue.default', 'sync');
        Config::set('broadcasting.default', 'log');

        // Clear any existing transactions before each test
        $this->clearDatabaseTransactions();
    }

    protected function clearDatabaseTransactions(): void
    {
        try {
            // Close all open transactions for all database connections
            $connections = ['mysql', 'sqlite', 'pgsql'];

            foreach ($connections as $connection) {
                try {
                    $db = DB::connection($connection);
                    while ($db->transactionLevel() > 0) {
                        $db->rollBack();
                    }
                } catch (\Exception $e) {
                    // Ignore if connection doesn't exist or isn't configured
                    continue;
                }
            }
        } catch (\Exception $e) {
            // Ignore any errors during transaction cleanup
        }
    }
}
