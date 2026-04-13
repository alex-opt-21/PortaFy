<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connectors\PostgresConnector;

class NeonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('db.connector.pgsql', function () {
            return new class extends PostgresConnector {
                public function connect(array $config)
                {
                    $endpointId = explode('.', $config['host'])[0];
                    $dsn = "pgsql:host={$config['host']};dbname={$config['database']};port={$config['port']};sslmode=require;options=endpoint={$endpointId}";

                    $options = $this->getOptions($config);
                    return $this->createConnection($dsn, $config, $options);
                }
            };
        });
    }
}
