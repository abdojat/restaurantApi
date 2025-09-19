<?php
echo "Testing PostgreSQL connection...\n";

// Try different connection configurations
$configs = [
    [
        'dsn' => 'pgsql:host=dpg-d369ajumcj7s73dce7kg-a.oregon-postgres.render.com;dbname=restaurant_db_2m6q;port=5432;sslmode=require',
        'desc' => 'External host with SSL required'
    ],
    [
        'dsn' => 'pgsql:host=dpg-d369ajumcj7s73dce7kg-a.oregon-postgres.render.com;dbname=restaurant_db_2m6q;port=5432;sslmode=prefer',
        'desc' => 'External host with SSL preferred'
    ],
    [
        'dsn' => 'pgsql:host=dpg-d369ajumcj7s73dce7kg-a;dbname=restaurant_db_2m6q;port=5432;sslmode=require',
        'desc' => 'Internal host with SSL required'
    ]
];

foreach ($configs as $config) {
    echo "\nTrying: {$config['desc']}\n";
    try {
        $pdo = new PDO(
            $config['dsn'],
            'restaurant_db_2m6q_user',
            '7Lb5mctUiulq8Pt2LfyUp2ZwEDFoYw1U'
        );
        echo "✅ Connection successful!\n";
        
        // Test a simple query
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "PostgreSQL Version: " . $version . "\n";
        break; // Exit loop on successful connection
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
?>
