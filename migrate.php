<?php

/**
 * Database Migration Runner
 * 
 * This script runs database migrations to set up or update the database schema.
 */

// Load required files
require_once 'app/core/Database.php';
require_once 'app/core/Migration.php';

// Create migration instance
$migration = new Migration();

// Apply migrations
$appliedMigrations = $migration->applyMigrations();

// Output results
if (empty($appliedMigrations)) {
    echo "No new migrations to apply." . PHP_EOL;
} else {
    echo "Applied " . count($appliedMigrations) . " migrations." . PHP_EOL;
}

echo "Migration completed successfully." . PHP_EOL;
