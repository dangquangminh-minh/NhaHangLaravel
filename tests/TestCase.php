<?php

namespace Tests;

use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    protected $seed = true;

    protected $seeder = ReferenceDataSeeder::class;

    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get('database.connections.'.$connection.'.database');
        if (($connection === 'sqlite' && $database !== ':memory:') || ($connection === 'mysql' && ! str_ends_with((string) $database, '_feature_test')) || ! in_array($connection, ['sqlite', 'mysql'], true)) {
            throw new LogicException('Chỉ được chạy test trên SQLite :memory: hoặc MySQL có tên kết thúc bằng _feature_test.');
        }

        return $app;
    }
}
