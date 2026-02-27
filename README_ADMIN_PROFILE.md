# Admin Profile Page - BookMarket

## Overview

The Admin Profile page provides administrators with a comprehensive interface to manage their account settings, preferences, and administrative permissions.

## Features

### 1. Personal Information Management

-   **First Name & Last Name**: Editable personal information
-   **Email**: Read-only email address (primary identifier)
-   **Phone Number**: Editable contact information
-   **Admin Since**: Display of when the admin account was created

### 2. Password Management

-   **Current Password Verification**: Secure password change process
-   **New Password**: Password strength validation (minimum 6 characters)
-   **Password Confirmation**: Double-entry verification for new passwords

### 3. Admin Settings

-   **Role Selection**: Choose between Super Admin, Admin, or Moderator
-   **Permission Management**: Granular control over system access
    -   User Management
    -   Book Management
    -   Order Management
    -   Sales Reports
    -   System Settings

### 4. Preferences

-   **Email Notifications**: Configure notification preferences
    -   Order Updates
    -   Book Approval Requests
    -   User Registration
    -   System Alerts
-   **Language**: English or Bengali
-   **Timezone**: Multiple timezone options

### 5. Security Features

-   **Account Deletion**: Secure account removal with password confirmation
-   **Session Management**: Proper logout functionality
-   **Access Control**: Role-based permission system

## File Structure

```
pages/admin/profile.html          # Admin profile page frontend
backend/api/admin_profile.php     # Backend API for profile operations
js/admin-navbar.js               # Updated navbar with profile link
```

## API Endpoints

### GET Profile

-   **Action**: `get_profile`
-   **Parameters**: `admin_id`
-   **Response**: Admin profile data including personal info, role, and permissions

### Update Profile

-   **Action**: `update_profile`
-   **Parameters**: `admin_id`, `first_name`, `last_name`, `phone`
-   **Response**: Success/failure status

### Change Password

-   **Action**: `change_password`
-   **Parameters**: `admin_id`, `current_password`, `new_password`
-   **Response**: Success/failure status

### Update Admin Settings

-   **Action**: `update_admin_settings`
-   **Parameters**: `admin_id`, `role`, `permissions`
-   **Response**: Success/failure status

### Delete Account

-   **Action**: `delete_account`
-   **Parameters**: `admin_id`, `password`
-   **Response**: Success/failure status

## Navigation

The admin profile page is accessible through:

1. **Admin Navbar**: Profile dropdown menu
2. **Sidebar Navigation**: Direct link in admin dashboard sidebar
3. **URL**: `/pages/admin/profile.html`

## Styling

-   **Dark Theme**: Consistent with admin dashboard design
-   **Responsive Design**: Mobile-friendly layout
-   **Tabbed Interface**: Organized content sections
-   **Form Validation**: Client-side and server-side validation

## Security Considerations

-   **Password Verification**: Required for sensitive operations
-   **Session Validation**: Ensures admin is logged in
-   **Input Sanitization**: Prevents XSS and injection attacks
-   **Permission Checks**: Role-based access control

## Future Enhancements

-   **Profile Picture Upload**: Avatar management
-   **Two-Factor Authentication**: Enhanced security
-   **Audit Logging**: Track profile changes
-   **Bulk Permission Updates**: Efficient permission management
-   **Integration with LDAP**: Enterprise authentication

## Usage Instructions

1. **Access**: Navigate to the admin profile page
2. **Edit Information**: Click on any tab to modify settings
3. **Save Changes**: Use the save buttons in each section
4. **Password Change**: Ensure current password is correct
5. **Account Deletion**: Use with extreme caution (irreversible)

## Browser Compatibility

-   Chrome 80+
-   Firefox 75+
-   Safari 13+
-   Edge 80+

## Dependencies

-   **Frontend**: HTML5, CSS3, JavaScript (ES6+)
-   **Backend**: PHP 7.4+, MySQL 5.7+
-   **Libraries**: Font Awesome 6.4.0
-   **Frameworks**: None (vanilla implementation)
