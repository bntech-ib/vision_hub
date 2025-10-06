<?php
/**
 * Test script to verify ad earning functionality
 * 
 * This script demonstrates how the ad earning system works and can be used to test it.
 */

// This is just for documentation purposes. In a real application, you would make HTTP requests.

echo "=== Ad Earning System Test ===\n\n";

echo "To test the ad earning system, make a POST request to:\n";
echo "POST /api/v1/ads/{ad_id}/interact\n\n";

echo "Request Body (for viewing an ad):\n";
echo "{\n";
echo "  \"type\": \"view\"\n";
echo "}\n\n";

echo "Request Body (for clicking an ad):\n";
echo "{\n";
echo "  \"type\": \"click\"\n";
echo "}\n\n";

echo "Expected Response (Success):\n";
echo "{\n";
echo "  \"success\": true,\n";
echo "  \"data\": {\n";
echo "    \"interaction\": {\n";
echo "      \"id\": \"1\",\n";
echo "      \"user_id\": \"1\",\n";
echo "      \"advertisement_id\": \"1\",\n";
echo "      \"type\": \"view\",\n";
echo "      \"reward_earned\": 0.5,\n";
echo "      \"interacted_at\": \"2025-01-01T10:00:00.000000Z\"\n";
echo "    },\n";
echo "    \"remaining_interactions\": 9\n";
echo "  },\n";
echo "  \"message\": \"Ad view recorded successfully\"\n";
echo "}\n\n";

echo "=== How the Earning System Works ===\n\n";

echo "1. When a user interacts with an ad (view or click), the system:\n";
echo "   - Validates the ad is active\n";
echo "   - Checks if the user has reached their daily limit\n";
echo "   - Calculates the reward based on the user's package\n";
echo "   - Records the interaction in the database\n";
echo "   - Awards the reward to the user's wallet\n";
echo "   - Creates a transaction record\n\n";

echo "2. Reward Calculation:\n";
echo "   - View reward = daily_earning_limit / ad_limits (from user's package)\n";
echo "   - Click reward = 2 × view reward\n\n";

echo "3. Package Fields Used:\n";
echo "   - daily_earning_limit: Maximum earnings per day\n";
echo "   - ad_limits: Maximum ad interactions per day\n\n";

echo "4. Example:\n";
echo "   - Package daily_earning_limit = $10\n";
echo "   - Package ad_limits = 20 interactions\n";
echo "   - View reward = $10 / 20 = $0.50 per view\n";
echo "   - Click reward = 2 × $0.50 = $1.00 per click\n\n";

echo "=== API Endpoints Related to Ad Earnings ===\n\n";

echo "GET /api/v1/ads/stats\n";
echo "  - Get user's ad interaction statistics\n\n";

echo "GET /api/v1/ads/history/my-interactions\n";
echo "  - Get user's ad interaction history\n\n";

echo "GET /api/v1/dashboard/available-ads\n";
echo "  - Get available ads count for user\n\n";
?>