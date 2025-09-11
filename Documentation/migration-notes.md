# User Database Migration Notes

## Overview
This document provides important notes and considerations for the user database migrations in CounselWise.

---

## Migration Sequence

### 1. Base Users Table
**File**: `0001_01_01_000000_create_users_table.php`  
**Date**: Laravel framework default  
**Status**: ✅ Foundation migration

Creates the foundational user authentication structure with Laravel's standard fields plus sessions and password reset tokens.

### 2. Account Creation Fields
**File**: `2025_09_03_143311_add_account_creation_required_fields_to_users_table.php`  
**Date**: September 3, 2025  
**Status**: ✅ Professional features added

Adds professional counsellor requirements and account management features.

### 3. User Profile Fields
**File**: `2025_09_11_000000_add_user_profile_fields_to_users_table.php`  
**Date**: September 11, 2025  
**Status**: ⚠️ Pending deployment

Adds comprehensive user profile and system tracking capabilities.

---

## Migration Execution

### Running Migrations
```bash
# Run all pending migrations
php artisan migrate

# Check migration status
php artisan migrate:status

# Run specific migration (if needed)
php artisan migrate --path=database/migrations/2025_09_11_000000_add_user_profile_fields_to_users_table.php
```

### Rollback Procedures
```bash
# Rollback last migration batch
php artisan migrate:rollback

# Rollback specific number of batches
php artisan migrate:rollback --step=2

# Rollback to specific migration
php artisan migrate:rollback --to=2025_09_03_143311
```

---

## Important Considerations

### Enum Dependencies
⚠️ **Critical**: Enum classes must exist before running migrations that reference them.

Required enum files:
- `app/Enums/AccountType.php`
- `app/Enums/AccountStatus.php`
- `app/Enums/Gender.php`
- `app/Enums/Language.php`
- `app/Enums/SouthAfricanProvince.php`
- `app/Enums/ThemePreference.php`

### Data Considerations

#### Default Values
- `account_type`: Defaults to `counsellor_free`
- `account_status`: Defaults to `pending`
- `language`: Defaults to `english`
- `notification_preferences`: Defaults to empty JSON object `{}`

#### Nullable Fields
Optional profile fields that can be null:
- `hpcsa_number`
- `hpcsa_verified_at`
- `profile_picture`
- `theme_preference`
- `data_privacy_consent`
- `terms_accepted_at`
- `phone_number`
- `gender`
- `region`
- `last_login_at`

### Performance Notes

#### Existing Indexes
- `users.email` - Unique index for authentication
- `sessions.user_id` - Foreign key index
- `sessions.last_activity` - Query performance index

#### Recommended Additional Indexes
Consider adding these indexes based on query patterns:
```sql
-- If filtering by account status frequently
ALTER TABLE users ADD INDEX idx_account_status (account_status);

-- If filtering by account type frequently  
ALTER TABLE users ADD INDEX idx_account_type (account_type);

-- If querying by HPCSA number
ALTER TABLE users ADD INDEX idx_hpcsa_number (hpcsa_number);

-- Composite index for active counsellors
ALTER TABLE users ADD INDEX idx_active_counsellors (account_type, account_status);
```

---

## Data Migration Considerations

### Existing User Data
If users exist before running the new migrations:

1. **Required Fields**: `surname` field is required - existing users need default values
2. **Account Types**: Existing users will get `counsellor_free` default
3. **Account Status**: Existing users will get `pending` status
4. **Language**: Existing users will get `english` default

### HPCSA Verification
- New professional accounts will need HPCSA verification workflow
- Existing users may need to provide HPCSA numbers
- Consider bulk verification process for existing professionals

### Soft Deletes
- Soft deletes are now enabled - update deletion logic in controllers
- Existing hard-deleted records cannot be recovered
- Consider data retention policies

---

## Testing Migrations

### Fresh Migration Testing
```bash
# Test complete migration sequence
php artisan migrate:fresh --seed

# Test rollback capability
php artisan migrate:fresh
php artisan migrate --step
php artisan migrate:rollback
```

### Factory Testing
```bash
# Test user creation with new fields
php artisan tinker
>>> User::factory()->create();
>>> User::factory()->freeCounsellor()->create();
>>> User::factory()->researcher()->create();
```

---

## Deployment Checklist

### Pre-deployment
- [ ] All enum classes are committed and available
- [ ] Migration files are in correct sequence
- [ ] Factory states work with new fields
- [ ] User model casts are properly configured
- [ ] Validation rules updated for new fields

### Deployment
- [ ] Backup database before migration
- [ ] Run migrations in staging environment first
- [ ] Verify enum values are correctly stored
- [ ] Test user registration with new fields
- [ ] Verify existing user data integrity

### Post-deployment
- [ ] Confirm all enum fields display correctly
- [ ] Test user profile updates
- [ ] Verify soft delete functionality
- [ ] Check performance with new fields
- [ ] Update API documentation if needed

---

## Troubleshooting

### Common Issues

#### Enum Class Not Found
```
Error: Class 'App\Enums\AccountType' not found
```
**Solution**: Ensure all enum files exist and are properly namespaced

#### Migration Rollback Fails
```
Error: Cannot drop column with foreign key constraint
```
**Solution**: Check for foreign key relationships, may need to drop constraints first

#### Default Value Issues
```
Error: Data truncated for column 'account_type'
```
**Solution**: Verify enum default values match actual enum cases

### Recovery Procedures
If migration fails:
1. Check database state with `php artisan migrate:status`
2. Review migration logs for specific error
3. Fix enum/model issues
4. Rollback if necessary: `php artisan migrate:rollback`
5. Re-run after fixes: `php artisan migrate`