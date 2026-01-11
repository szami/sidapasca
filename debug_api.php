<?php
require __DIR__ . '/vendor/autoload.php';

// Mock Leaf Request/App if needed, or just manual DB
// Load Env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
}

require __DIR__ . '/config/db.php';
use App\Utils\Database;

try {
    $db = Database::connection();

    // Simulate the query from apiData
    $sql = "SELECT p.*, 
            p.tpa_provider, p.tpa_certificate_url,
            (SELECT SUM(score) FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = p.id AND c.type = 'TPA') as tpa_score_saved
            FROM participants p 
            LIMIT 1";

    echo "<h1>Executing Query...</h1>";
    echo "<pre>$sql</pre>";

    $data = $db->query($sql)->fetchAll();

    echo "<h1>Success</h1>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";

} catch (\Throwable $e) {
    echo "<h1>Error</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
