# User Database Schema Changes Summary

## Overview
This document summarizes the database schema changes made to implement the comprehensive user management system for CounselWise.

## Schema Evolution

### Base User Table (Initial Migration)
**File**: `0001_01_01_000000_create_users_table.php`

Initial Laravel user table structure:
- `id` - Primary key
- `name` - User's first name
- `email` - Unique email address (indexed)
- `email_verified_at` - Email verification timestamp
- `password` - Hashed password
- `remember_token` - Session management
- `timestamps` - Created/updated tracking

### Account Creation Fields (Sept 3, 2025)
**File**: `2025_09_03_143311_add_account_creation_required_fields_to_users_table.php`

Added professional and account management fields:
- `surname` - User's last name (required)
- `hpcsa_number` - Health Professions Council registration number
- `hpcsa_verified_at` - HPCSA verification timestamp
- `profile_picture` - Profile image path
- `account_type` - User classification (enum)
- `deleted_at` - Soft delete support

### User Profile Fields (Sept 11, 2025)
**File**: `2025_09_11_000000_add_user_profile_fields_to_users_table.php`

Added comprehensive user profile and system fields:
- `last_login_at` - Login tracking
- `account_status` - Account lifecycle management
- `theme_preference` - UI theme selection
- `data_privacy_consent` - GDPR/POPIA compliance
- `terms_accepted_at` - Terms acceptance tracking
- `notification_preferences` - JSON notification settings
- `phone_number` - Contact information
- `gender` - Demographics
- `language` - Preferred language
- `region` - South African province

## Key Features

### Security & Compliance
- Password hashing via Laravel's built-in system
- Soft deletes for data retention
- GDPR/POPIA compliance tracking
- Email verification system

### Professional Integration
- HPCSA registration tracking
- Professional verification workflow
- Account type classification

### User Experience
- Theme preference storage
- Language localization support
- Comprehensive notification settings
- Regional customization

### Performance Optimizations
- Unique index on email field
- Session table indexes for performance
- Enum-based fields for type safety

## Field Summary

| Category | Field Count | Examples |
|----------|-------------|----------|
| Core Identity | 4 | name, surname, email, password |
| Professional | 3 | hpcsa_number, hpcsa_verified_at, account_type |
| System Status | 3 | account_status, last_login_at, deleted_at |
| Preferences | 4 | theme_preference, language, region, notification_preferences |
| Compliance | 3 | email_verified_at, data_privacy_consent, terms_accepted_at |
| Contact | 2 | phone_number, profile_picture |
| Demographics | 1 | gender |
| Timestamps | 2 | created_at, updated_at |

## Total Fields: 22 fields in users table

## Related Infrastructure
- User factory with comprehensive states
- Multiple enum classes for type safety
- Proper model casting and relationships
- Session and password reset token tables