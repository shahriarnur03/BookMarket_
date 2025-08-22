<?php
/**
 * BookMarket Dummy Data Execution Script
 * This script runs the dummy data SQL files to populate the database
 */

require_once '../config/database.example.php';

try {
    $db = getDB();
    $pdo = $db->getConnection();
    
    echo "🚀 Starting BookMarket Dummy Data Population...\n\n";
    
    // Read and execute the main dummy data file
    echo "📚 Loading main dummy data...\n";
    $mainSQL = file_get_contents('dummy_data.sql');
    $statements = explode(';', $mainSQL);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✅ Main dummy data loaded successfully!\n\n";
    
    // Read and execute the reviews data file
    echo "⭐ Loading book reviews and additional data...\n";
    $reviewsSQL = file_get_contents('dummy_reviews.sql');
    $statements = explode(';', $reviewsSQL);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✅ Reviews and additional data loaded successfully!\n\n";
    
    echo "🎉 All dummy data has been successfully loaded!\n";
    echo "Your BookMarket website now has:\n";
    echo "• 15+ diverse users (students, professionals, teachers, collectors)\n";
    echo "• 40+ books across all categories with realistic statuses\n";
    echo "• 11 orders with different statuses (delivered, processing, shipped, pending)\n";
    echo "• 50+ book reviews with ratings and detailed feedback\n";
    echo "• Cart items for realistic shopping experience\n";
    echo "• Admin audit trail for professional appearance\n";
    echo "• Realistic inventory management with varying stock levels\n\n";
    
    echo "🌐 Your website is now ready for professional demonstration!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
