# Resend Email Service Setup Guide

This guide provides step-by-step instructions for setting up Resend as the email service provider for the CounselWise application.

## Overview

Resend is a modern email API service that provides reliable email delivery with a developer-friendly interface. This guide will walk you through the complete setup process, from creating a Resend account to sending your first production emails.

---

## Prerequisites

- Active Resend account (sign up at https://resend.com)
- Verified domain for sending emails
- Laravel 12 application (already configured)
- Composer installed

---

## Step 1: Install Resend Package

Install the official Resend Laravel package:

```bash
composer require resend/resend-laravel
```

This package provides native Laravel and Symfony Mailer integration for Resend.

---

## Step 2: Create Resend Account and Get API Key

### 2.1 Sign Up for Resend

1. Go to https://resend.com
2. Click "Sign Up" and create your account
3. Verify your email address

### 2.2 Generate API Key

1. Log in to your Resend dashboard
2. Navigate to **API Keys** section
3. Click **Create API Key**
4. Give it a descriptive name (e.g., "CounselWise Production" or "CounselWise Development")
5. Select appropriate permissions (typically "Sending access")
6. Copy the API key immediately (you won't be able to see it again)

**Note**: For development, you can use the same API key. For production, create a separate API key with production-specific settings.

---

## Step 3: Verify Your Domain

To send emails from your domain (e.g., `noreply@counselwise.com`), you need to verify domain ownership.

### 3.1 Add Your Domain in Resend

1. In the Resend dashboard, go to **Domains**
2. Click **Add Domain**
3. Enter your domain (e.g., `counselwise.com`)
4. Click **Add**

### 3.2 Configure DNS Records

Resend will provide you with DNS records to add to your domain:

1. **DKIM Record**: Authenticates your emails
2. **SPF Record** (if not already present): Authorizes Resend to send on your behalf
3. **DMARC Record** (recommended): Provides email authentication policy

Add these records to your DNS provider (e.g., Cloudflare, AWS Route 53, Namecheap):

**Example DKIM Record:**
```
Type: TXT
Name: resend._domainkey.counselwise.com
Value: [provided by Resend]
```

**Example SPF Record:**
```
Type: TXT
Name: @
Value: v=spf1 include:resend.com ~all
```

**Note**: DNS propagation can take up to 48 hours, but typically completes within 1-2 hours.

### 3.3 Verify Domain Status

1. After adding DNS records, return to Resend dashboard
2. Click **Verify** next to your domain
3. Wait for verification to complete (green checkmark)

---

## Step 4: Configure Environment Variables

Update your `.env` file with Resend configuration:

```env
# Mail Configuration
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@counselwise.com"
MAIL_FROM_NAME="CounselWise"

# Resend API Key
RESEND_API_KEY=re_your_api_key_here
```

**Important**:
- Replace `noreply@counselwise.com` with your verified domain email
- Replace `re_your_api_key_here` with your actual Resend API key
- Never commit your `.env` file to version control

---

## Step 5: Update Environment Example File

Update `.env.example` to document the Resend configuration for other developers:

```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Resend Configuration
RESEND_API_KEY=
```

---

## Step 6: Update Mail Configuration (if needed)

The application's `config/mail.php` already includes Resend configuration:

```php
'resend' => [
    'transport' => 'resend',
],
```

**No changes needed** - Laravel 12 includes native Resend support.

---

## Step 7: Update Services Configuration

Add the Resend API key to `config/services.php`:

```php
return [
    // ... other services

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],
];
```

---

## Step 8: Test Email Sending

Create a test command to verify email functionality:

```bash
php artisan make:command TestResendEmail
```

Update `app/Console/Commands/TestResendEmail.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestResendEmail extends Command
{
    protected $signature = 'mail:test {email}';

    protected $description = 'Send a test email via Resend';

    public function handle(): int
    {
        $email = $this->argument('email');

        Mail::raw('This is a test email from CounselWise using Resend!', function ($message) use ($email) {
            $message->to($email)
                ->subject('Test Email - Resend Integration');
        });

        $this->info("Test email sent to {$email}");
        $this->info('Check your inbox (and spam folder) for the test email.');

        return 0;
    }
}
```

Run the test command:

```bash
php artisan mail:test your-email@example.com
```

---

## Step 9: Monitor in Resend Dashboard

1. Log in to your Resend dashboard
2. Navigate to **Emails** section
3. Verify your test email appears in the logs
4. Check delivery status (delivered, bounced, failed, etc.)

---

## Step 10: Update Existing Email Functionality

### 10.1 Email Verification

The application already sends email verification emails using Laravel's built-in functionality. No changes needed - it will automatically use Resend.

### 10.2 Password Reset Emails

Password reset emails also use Laravel's built-in functionality. No changes needed.

### 10.3 Custom Notifications

If you have custom notifications, they will automatically use Resend through the mail channel.

---

## Production Checklist

Before deploying to production:

- [ ] Domain verified in Resend dashboard (green checkmark)
- [ ] DNS records properly configured (DKIM, SPF, DMARC)
- [ ] Production API key created with appropriate permissions
- [ ] `MAIL_FROM_ADDRESS` uses verified domain
- [ ] Test email successfully delivered
- [ ] Email delivery monitored in Resend dashboard
- [ ] Rate limits understood (Resend free tier: 100 emails/day, 3,000/month)
- [ ] Bounce handling configured (optional)
- [ ] Webhook integration set up (optional, for advanced tracking)

---

## Rate Limits

Resend has different rate limits based on your plan:

- **Free Tier**: 100 emails/day, 3,000 emails/month
- **Pro Tier**: Custom limits based on plan

Monitor your usage in the Resend dashboard to avoid hitting limits.

---

## Troubleshooting

### Email Not Sending

1. **Check API Key**: Ensure `RESEND_API_KEY` is correctly set in `.env`
2. **Verify Domain**: Domain must be verified (green checkmark in dashboard)
3. **Check From Address**: `MAIL_FROM_ADDRESS` must use verified domain
4. **Review Logs**: Check `storage/logs/laravel.log` for errors
5. **Check Resend Dashboard**: Review email logs for delivery status

### DNS Verification Failing

1. **Wait for Propagation**: DNS changes can take up to 48 hours
2. **Verify Records**: Use `dig` or `nslookup` to verify DNS records are published
3. **Check Record Format**: Ensure TXT records are correctly formatted (no extra quotes)

```bash
# Check DKIM record
dig TXT resend._domainkey.counselwise.com

# Check SPF record
dig TXT counselwise.com
```

### Emails Going to Spam

1. **Verify Domain**: Ensure DKIM, SPF, and DMARC are properly configured
2. **Check Content**: Avoid spam trigger words in subject/body
3. **Warm Up Domain**: Send gradually increasing volumes (important for new domains)
4. **Monitor Reputation**: Check domain reputation using tools like MXToolbox

### Rate Limit Exceeded

1. **Upgrade Plan**: Consider upgrading to Pro tier for higher limits
2. **Queue Emails**: Use Laravel's queue system to batch emails
3. **Monitor Usage**: Set up alerts in Resend dashboard

---

## Advanced Configuration (Optional)

### Webhook Integration

Set up webhooks to track email events (delivered, opened, clicked, bounced):

1. In Resend dashboard, go to **Webhooks**
2. Click **Add Endpoint**
3. Enter your webhook URL: `https://counselwise.test/webhooks/resend`
4. Select events to track (delivered, bounced, complained, etc.)
5. Save and copy the signing secret

Create a webhook controller:

```bash
php artisan make:controller WebhookController
```

### Custom Email Templates

Use Resend's HTML email support with Laravel's Mailable classes:

```bash
php artisan make:mail WelcomeEmail
```

### Batch Email Sending

Use Laravel queues for sending bulk emails:

```php
Mail::to($user)->queue(new WelcomeEmail($user));
```

---

## Support and Resources

- **Resend Documentation**: https://resend.com/docs
- **Resend PHP SDK**: https://github.com/resend/resend-php
- **Laravel Mail Documentation**: https://laravel.com/docs/12.x/mail
- **Resend Status Page**: https://status.resend.com
- **Resend Support**: support@resend.com

---

## Security Best Practices

1. **Never commit API keys**: Keep `RESEND_API_KEY` in `.env` only
2. **Use environment-specific keys**: Separate keys for development/staging/production
3. **Rotate keys periodically**: Create new API keys and revoke old ones
4. **Limit key permissions**: Only grant necessary permissions to each API key
5. **Monitor for suspicious activity**: Review Resend dashboard logs regularly
6. **Use HTTPS**: Ensure all webhook endpoints use HTTPS

---

## Cost Considerations

**Free Tier:**
- 3,000 emails per month
- 100 emails per day
- Perfect for development and small applications

**Pro Tier (Starting at $20/month):**
- 50,000 emails per month included
- Additional emails at $0.80 per 1,000
- Dedicated IP available
- Priority support

Review your email volume requirements and upgrade as needed.

---

## Next Steps

After successfully setting up Resend:

1. Test all email functionality (registration, password reset, verification)
2. Monitor delivery rates in Resend dashboard
3. Set up email tracking/webhooks if needed
4. Configure bounce handling
5. Document any custom email templates or workflows
6. Train team members on monitoring email delivery

---

## Rollback Plan

If you need to rollback to log-based emails:

1. Update `.env`:
   ```env
   MAIL_MAILER=log
   ```

2. Restart application (if using queue workers):
   ```bash
   php artisan queue:restart
   ```

Emails will be logged to `storage/logs/laravel.log` instead of being sent.

---

**Last Updated**: 2025-10-07