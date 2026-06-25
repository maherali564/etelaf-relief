<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase {
        refreshTestDatabase as parentRefreshTestDatabase;
    }

    protected function refreshTestDatabase()
    {
        $this->mockConsoleOutput = false;
        $this->parentRefreshTestDatabase();
        $this->mockConsoleOutput = true;
    }
}
