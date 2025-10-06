# Support Options Update Error Fix

## Issue
There was an error when updating support options due to references to the old `icon` field that no longer exists in the database schema.

## Root Cause
1. The database schema was updated to replace the `icon` column with an `avatar` column
2. However, some code files still referenced the old `icon` field:
   - SupportOptionSeeder.php - Still trying to insert `icon` values
   - SupportOptionControllerTest.php - Still trying to access `$supportOption->icon`

## Fixes Applied

### 1. Updated SupportOptionSeeder.php
Changed references from `icon` to `avatar` in the seeder data:

**Before:**
```php
[
    'title' => 'Technical Support',
    'description' => 'Troubleshoot technical issues and platform bugs',
    'icon' => 'shield',  // Old field
    'whatsapp_number' => '+1234567893',
    'whatsapp_message' => 'Hello, I am experiencing technical issues',
    'sort_order' => 4,
    'is_active' => true,
],
[
    'title' => 'Billing Inquiries',
    'description' => 'Questions about packages, pricing, and billing',
    'icon' => 'wallet',  // Old field
    'whatsapp_number' => '+1234567894',
    'whatsapp_message' => 'Hello, I have a billing question',
    'sort_order' => 5,
    'is_active' => true,
],
```

**After:**
```php
[
    'title' => 'Technical Support',
    'description' => 'Troubleshoot technical issues and platform bugs',
    'avatar' => null,  // New field
    'whatsapp_number' => '+1234567893',
    'whatsapp_message' => 'Hello, I am experiencing technical issues',
    'sort_order' => 4,
    'is_active' => true,
],
[
    'title' => 'Billing Inquiries',
    'description' => 'Questions about packages, pricing, and billing',
    'avatar' => null,  // New field
    'whatsapp_number' => '+1234567894',
    'whatsapp_message' => 'Hello, I have a billing question',
    'sort_order' => 5,
    'is_active' => true,
],
```

### 2. Updated SupportOptionControllerTest.php
Changed reference from `icon` to `avatar` in the test:

**Before:**
```php
$data = [
    'id' => $supportOption->id,
    'title' => $supportOption->title,
    'description' => $supportOption->description,
    'icon' => $supportOption->icon,  // Old field
    'whatsapp_link' => $supportOption->whatsapp_link,
];
```

**After:**
```php
$data = [
    'id' => $supportOption->id,
    'title' => $supportOption->title,
    'description' => $supportOption->description,
    'avatar' => $supportOption->avatar,  // New field
    'whatsapp_link' => $supportOption->whatsapp_link,
];
```

## How Support Options Work Now

1. **Database Schema**: The `support_options` table now has an `avatar` column instead of `icon`
2. **File Storage**: Avatars are stored in the `storage/app/public/support-avatars` directory
3. **URL Generation**: Avatar URLs are generated using `Storage::url($supportOption->avatar)`
4. **Frontend Display**: The edit form shows the current avatar if one exists
5. **File Handling**: When updating, old avatars are deleted before uploading new ones

## Controllers

Both controllers (Admin and API) properly handle avatar uploads:
- Validate image files with appropriate mime types
- Delete old avatars when replacing them
- Store new avatars in the correct directory
- Update the database with the new file path

## Views

The admin edit view properly displays:
- Current avatar image if one exists
- File input for uploading new avatars
- All other support option fields

## Testing

The fix has been applied to ensure:
- Seeder data is consistent with the current schema
- Tests access the correct model attributes
- No references to the deprecated `icon` field remain