<?php

/**
 * Web-based Database Migration Runner
 * 
 * This script runs database migrations to set up or update the database schema.
 * It can be accessed via a web browser.
 */

// Load required files
require_once 'app/core/Database.php';
require_once 'app/core/Migration.php';

// Set content type to plain text for better readability
header('Content-Type: text/plain');

// Create migration instance
$migration = new Migration();

// Apply migrations
$appliedMigrations = $migration->applyMigrations();

// Output results
if (empty($appliedMigrations)) {
    echo "No new migrations to apply.\n";
} else {
    echo "Applied " . count($appliedMigrations) . " migrations:\n";
    foreach ($appliedMigrations as $migration) {
        echo "- {$migration}\n";
    }
}

echo "\nMigration completed successfully.\n";
echo "\nYou can now go to the <a href='/'>homepage</a> to use the application.";
