<?php
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=trimexas', 'postgres', 'netlab123');
    echo "CONNECTED OK\n";
    var_dump($pdo->query('SELECT version()')->fetchColumn());
} catch (PDOException $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
