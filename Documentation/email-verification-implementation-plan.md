# Email Verification Implementation Plan

## Current State Analysis

### ✅ What's Already Implemented

**Infrastructure:**
- Mail configuration in `config/mail.php` with support for multiple mailers (SMTP, SES, Postmark, Resend, etc.)
- Environment variables configured for mail settings (currently using `log` mailer for development)
- Database table includes `email_verified_at` timestamp column

**User Model:**
- Implements `MustVerifyEmail` contract (`app/Models/User.php:17`)
- Has `email_verified_at` casting to datetime (`app/Models/User.php:62`)

**Authentication Routes:**
- Complete email verification route structure in `routes/auth.php`:
  - `GET /verify-email` - Verification prompt page
  - `GET /verify-email/{id}/{hash}` - Verification link handler
  - `POST /email/verification-notification` - Resend verification email

**Controllers:**
- `EmailVerificationPromptController` - Shows verification prompt
- `VerifyEmailController` - Handles verification link clicks
- `EmailVerificationNotificationController` - Handles resend requests
- `RegisteredUserController` - User registration (currently bypasses verification)

**Security Features:**
- Rate limiting for email verification (2 attempts per 5 minutes)
- Security event logging for verification attempts
- Signed URL verification with throttling
- Custom `EmailVerificationRequest` form request class

**Frontend:**
- `resources/js/Pages/Auth/VerifyEmail.vue` - Verification prompt page

**Testing:**
- Comprehensive test coverage for email verification flows
- Browser tests for verification UI
- Rate limiting tests
- Security event logging tests

### ❌ What's Missing

**Configuration Issues:**
1. **Mail Driver**: Currently set to `log` - needs real mail service for production
2. **From Address**: Using placeholder `hello@example.com`
3. **Registration Flow**: Users bypass email verification (see TODO comment in `RegisteredUserController.php:59`)

**Integration Gaps:**
1. **Registration Controller**: Sets `account_status` to `Active` instead of `Pending`
2. **Dashboard Access**: No middleware to prevent unverified users from accessing protected areas
3. **Production Mail Service**: No configured mail service (SMTP, SES, Mailgun, etc.)

## Ideal State

### User Experience Flow
1. User registers → receives "Please verify your email" message
2. User receives verification email → clicks verification link
3. Email verified → user gains full access to application
4. Unverified users can access verification page and resend emails

### Technical Implementation
1. **Registration**: Sets users to `Pending` status until verified
2. **Mail Service**: Production-ready mail service configured
3. **Middleware**: Protects routes requiring verification
4. **UI/UX**: Clear verification status indicators and flows

## Implementation Roadmap

### Phase 1: Configure Production Mail Service
**Priority: HIGH** | **Effort: LOW** | **Time: 30 minutes**

**Tasks:**
- [ ] Choose mail service provider (recommendation below)
- [ ] Update environment variables
- [ ] Test mail delivery
- [ ] Update from address and name

**Recommended Mail Services:**
1. **Resend** (Recommended) - Laravel 11+ native support, developer-friendly
2. **Postmark** - Excellent deliverability, transactional focus
3. **SendGrid/Mailgun** - Enterprise-grade, high volume
4. **Amazon SES** - Cost-effective for high volume

**Environment Changes Needed:**
```bash
# For Resend (recommended)
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@counsel-wise.com"
MAIL_FROM_NAME="Counsel Wise"
# Add RESEND_API_KEY to .env

# For SMTP (generic)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### Phase 2: Update Registration Flow
**Priority: HIGH** | **Effort: LOW** | **Time: 15 minutes**

**Tasks:**
- [ ] Modify `RegisteredUserController.php` to set status to `Pending`
- [ ] Update registration response to include verification message
- [ ] Redirect unverified users to verification page instead of dashboard

**Code Changes:**
```php
// In RegisteredUserController.php:59
'account_status' => AccountStatus::Pending, // Remove TODO, implement verification

// Redirect logic after registration
return redirect()->route('verification.notice');
```

### Phase 3: Add Verification Middleware Protection
**Priority: MEDIUM** | **Effort: MEDIUM** | **Time: 45 minutes**

**Tasks:**
- [ ] Create/update middleware to check email verification
- [ ] Apply middleware to protected routes
- [ ] Update user status to `Active` after verification
- [ ] Test protected route access

**Routes to Protect:**
```php
// In routes/web.php or separate route groups
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Other protected routes...
});
```

### Phase 4: Enhanced User Experience
**Priority: LOW** | **Effort: MEDIUM** | **Time: 1-2 hours**

**Tasks:**
- [ ] Add verification status indicator in navigation
- [ ] Improve verification email template
- [ ] Add verification reminder notifications
- [ ] Enhance error handling and user feedback

### Phase 5: Testing and Validation
**Priority: HIGH** | **Effort: LOW** | **Time: 30 minutes**

**Tasks:**
- [ ] Test complete registration → verification → access flow
- [ ] Verify mail delivery in staging environment
- [ ] Run existing test suite to ensure no regressions
- [ ] Test rate limiting and security features

## Quick Start Implementation

### Immediate Actions (Next 1 Hour)

1. **Set up Resend (Recommended)**:
   ```bash
   # Register at resend.com
   # Get API key
   # Update .env
   MAIL_MAILER=resend
   MAIL_FROM_ADDRESS="noreply@counsel-wise.com"
   MAIL_FROM_NAME="Counsel Wise"
   RESEND_API_KEY=your_api_key_here
   ```

2. **Update Registration Controller**:
   ```php
   // Change line 59 in RegisteredUserController.php
   'account_status' => AccountStatus::Pending,

   // Change line 79
   return redirect()->route('verification.notice');
   ```

3. **Test the Flow**:
   ```bash
   # Run tests to ensure everything works
   php artisan test --filter=email
   ```

### Verification Commands

After implementation, test with:
```bash
# Test mail configuration
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('your@email.com')->subject('Test'); });

# Check verification routes
php artisan route:list --name=verification

# Run verification tests
php artisan test tests/Feature/Auth/VerificationNotificationTest.php
```

## Configuration References

### Current Mail Settings
```env
# Current (development)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Target (production-ready)
MAIL_MAILER=resend  # or smtp/postmark/ses
MAIL_FROM_ADDRESS="noreply@counsel-wise.com"
MAIL_FROM_NAME="Counsel Wise"
```

### Files That Need Updates
1. `app/Http/Controllers/Auth/RegisteredUserController.php` (line 59, 79)
2. `.env` (mail configuration)
3. Optional: Add verification middleware to route groups

### Existing Security Features (Already Implemented)
- ✅ Rate limiting (2 attempts/5 minutes)
- ✅ Signed URL verification
- ✅ Security event logging
- ✅ Comprehensive test coverage
- ✅ CSRF protection
- ✅ Throttling protection

## Success Metrics

**After Implementation:**
- [ ] Users receive verification emails within 30 seconds
- [ ] Verification links work correctly
- [ ] Unverified users cannot access protected areas
- [ ] All existing tests pass
- [ ] Security logging captures verification events
- [ ] Rate limiting prevents abuse

**Next Steps After Email Verification:**
- HPCSA number verification system
- Account approval workflow for medical professionals
- Enhanced user profile management