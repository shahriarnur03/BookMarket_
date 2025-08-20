# Admin Book Upload System

## Overview

The Admin Book Upload system allows administrators to directly upload books to the BookMarket platform without going through the approval process. Books uploaded by admins are automatically approved and available for sale.

## Features

### 1. Book Upload Form

-   **Basic Information**: Title, Author, ISBN (optional), Description, Category
-   **Book Details**: Price, Book Condition (New, Excellent, Good, Fair, Poor)
-   **Image Upload**: Cover image (required) and additional images (optional)
-   **Drag & Drop**: Support for drag and drop image uploads
-   **Form Validation**: Client-side and server-side validation

### 2. Admin-Specific Features

-   **Auto-Approval**: Books are automatically approved (no manual approval needed)
-   **Admin Seller ID**: Books are associated with an admin seller account
-   **Statistics**: Track admin upload statistics and recent uploads

## File Structure

```
backend/books/
├── admin_book_upload.php      # Admin book upload API
├── book_manager.php           # General book management
└── README_ADMIN_UPLOAD.md    # This file

pages/admin/
├── book-upload.html           # Admin book upload page
├── dashboard.html             # Updated with book upload link
└── book-management.html       # Updated with book upload link

js/
└── admin-book-upload.js       # Frontend JavaScript functionality
```

## API Endpoints

### POST /backend/books/admin_book_upload.php

#### Actions:

1. **add_book**

    - Adds a new book as admin
    - Automatically sets status to 'approved'
    - Returns: `{success: true/false, message: string, book_id: int}`

2. **get_categories**

    - Retrieves all available book categories
    - Returns: `{success: true/false, data: array, message: string}`

3. **get_upload_stats**

    - Gets admin upload statistics
    - Returns: `{success: true/false, data: object, message: string}`

4. **get_recent_uploads**
    - Gets recent admin uploads
    - Parameters: `limit` (optional, default: 10)
    - Returns: `{success: true/false, data: array, message: string}`

## Usage

### 1. Access the Upload Page

Navigate to `/pages/admin/book-upload.html` from the admin dashboard.

### 2. Fill in Book Information

-   **Required Fields**: Title, Author, Category, Price, Book Condition, Cover Image
-   **Optional Fields**: ISBN, Description, Additional Images

### 3. Upload Images

-   **Cover Image**: Required, single image
-   **Additional Images**: Optional, multiple images supported
-   **Supported Formats**: JPG, JPEG, PNG, GIF, WebP
-   **Max File Size**: 5MB per image

### 4. Submit the Form

Click "Upload Book" to submit. The system will:

1. Upload images to the server
2. Create the book record in the database
3. Automatically approve the book
4. Show success/error messages

## Security Features

-   **Admin Authentication**: Only logged-in admin users can access
-   **Input Validation**: Server-side validation of all inputs
-   **File Type Validation**: Only image files are allowed
-   **File Size Limits**: Maximum 5MB per image
-   **SQL Injection Protection**: Prepared statements used

## Database Changes

The system uses the existing `books` table with the following considerations:

-   **seller_id**: Set to admin user ID
-   **status**: Automatically set to 'approved'
-   **cover_image_path**: Path to uploaded cover image
-   **additional_images**: JSON array of additional image paths

## Error Handling

-   **Form Validation**: Client-side validation with user-friendly messages
-   **File Upload Errors**: Detailed error messages for upload failures
-   **Database Errors**: Graceful error handling with logging
-   **Network Errors**: Timeout and connection error handling

## Browser Support

-   **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
-   **Features Used**: ES6+, Fetch API, FileReader API, Drag & Drop API
-   **Fallbacks**: Graceful degradation for older browsers

## Troubleshooting

### Common Issues:

1. **Images Not Uploading**

    - Check file size (max 5MB)
    - Verify file format (JPG, PNG, GIF, WebP)
    - Check server upload directory permissions

2. **Form Submission Fails**

    - Verify all required fields are filled
    - Check browser console for JavaScript errors
    - Verify admin authentication

3. **Categories Not Loading**
    - Check database connection
    - Verify categories table has data
    - Check server error logs

## Future Enhancements

-   **Bulk Upload**: Support for multiple book uploads
-   **CSV Import**: Import books from CSV files
-   **Image Optimization**: Automatic image resizing and compression
-   **Advanced Validation**: ISBN validation, price range validation
-   **Audit Trail**: Track all admin upload actions
-   **Email Notifications**: Notify admins of successful uploads

## Support

For technical support or questions about the Admin Book Upload system, please contact the development team or refer to the main project documentation.
