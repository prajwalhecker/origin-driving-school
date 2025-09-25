<?php
// Load your database config
require_once "../config/database.php";

echo "<h2>Database Connection Test</h2>";

try {
    // Try a simple query
    $stmt = $pdo->query("SHOW TABLES");
    echo "<p><strong>Connected successfully!</strong></p>";
    echo "<p>Tables in database <code>$DB_NAME</code>:</p><ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p style='color:red'><strong>Failed:</strong> " . $e->getMessage() . "</p>";
}
