<?php

// Database connection
$host = '107.155.75.50';
$port = 5986;
$dbname = 'ionbec-new';
$user = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully!\n\n";

    // Check for scorer role
    $stmt = $pdo->prepare("SELECT id, slug, name, client_id FROM roles WHERE slug = 'scorer' OR name ILIKE '%scorer%' OR name ILIKE '%penilai%'");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($roles)) {
        echo "Found potential scorer roles:\n";
        foreach ($roles as $role) {
            printf("ID: %d, Slug: %s, Name: %s, Client ID: %s\n",
                $role['id'], $role['slug'], $role['name'],
                $role['client_id'] ?: 'NULL');
        }
        $scorerRoleId = $roles[0]['id'];
    } else {
        echo "No scorer role found. Creating new scorer role...\n";
        $stmt = $pdo->prepare("INSERT INTO roles (name, slug, description, client_id) VALUES (?, ?, ?, ?) RETURNING id");
        $stmt->execute(['Scorer', 'scorer', 'Role for users who can score exams', null]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $scorerRoleId = $result['id'];
        printf("Created scorer role with ID: %d\n", $scorerRoleId);
    }

    echo "\nScorer Role ID: $scorerRoleId\n";

} catch (PDOException $e) {
    echo "Database operation failed: " . $e->getMessage() . "\n";
}

?>