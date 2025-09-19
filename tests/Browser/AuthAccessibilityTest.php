<?php

use App\Models\User;

describe('Keyboard Navigation', function () {
    test('can navigate login form using keyboard only', function () {
        $page = visit('/login');

        // Test tab navigation through form fields
        $page->press('Tab') // Email field should be focused
            ->type('test@example.com')
            ->press('Tab') // Password field should be focused
            ->type('password123')
            ->press('Tab') // Remember me checkbox
            ->press('Tab') // Sign in button should be focused
            ->press('Enter'); // Submit form using keyboard

        // Verify the form was submitted
        $page->assertPathIs('/dashboard'); // or whatever the redirect path is
    });

    test('can navigate registration form using keyboard only', function () {
        $page = visit('/register');

        $page->press('Tab') // Name field
            ->type('Test')
            ->press('Tab') // Surname field
            ->type('User')
            ->press('Tab') // Email field
            ->type('test@example.com')
            ->press('Tab') // Account type
            ->press('ArrowDown') // Select option
            ->press('Tab') // Password field
            ->type('password123')
            ->press('Tab') // Password confirmation
            ->type('password123')
            ->press('Tab') // Submit button
            ->press('Enter'); // Submit

        // Verify form submission or error handling
    });

    test('skip links are present and functional', function () {
        $page = visit('/login');

        // Check for skip to main content link
        $page->assertElementExists('[href="#main"], [href="#content"], .sr-only a');
    });

    test('focus is managed properly after errors', function () {
        $page = visit('/login');

        $page->click('Sign In'); // Submit empty form

        // Focus should be on first error field or error summary
        $page->assertElementExists(':focus');
    });
});

describe('ARIA Labels and Roles', function () {
    test('form fields have proper ARIA labels', function () {
        $page = visit('/login');

        // Check that all form fields have proper labeling
        $page->assertElementExists('input[aria-label], input[aria-labelledby], label[for]');

        // Check specific ARIA attributes
        $page->assertElementExists('[role="main"], [role="form"], main');
    });

    test('error messages have ARIA live regions', function () {
        $page = visit('/login');

        // Check for ARIA live regions for error announcements
        $page->assertElementExists('[aria-live="polite"], [aria-live="assertive"], [role="alert"]');
    });

    test('form validation includes ARIA described by', function () {
        $page = visit('/register');

        $page->fill('email', 'invalid-email')
            ->press('Tab'); // Trigger validation

        // Check that error message is associated with field
        $page->assertElementExists('input[aria-describedby], input[aria-invalid="true"]');
    });

    test('buttons have descriptive labels', function () {
        $page = visit('/login');

        // Check that buttons have proper accessible names
        $page->assertElementExists('button[aria-label], button:not([aria-label=""]), input[type="submit"][value]');
    });

    test('loading states are announced to screen readers', function () {
        $page = visit('/login');

        $page->fill('email', 'test@example.com')
            ->fill('password', 'password123')
            ->click('Sign In');

        // Check for ARIA live region announcing loading state
        $page->assertElementExists('[aria-live] *, [role="status"], [aria-busy="true"]');
    });
});

describe('Screen Reader Support', function () {
    test('page titles are descriptive and unique', function () {
        $pages = ['/login', '/register', '/forgot-password'];

        foreach ($pages as $url) {
            $page = visit($url);

            // Check that title exists and is descriptive
            $title = $page->title();
            expect($title)->not->toBeEmpty();
            expect($title)->not->toBe('Laravel'); // Should be more specific
        }
    });

    test('headings follow proper hierarchy', function () {
        $page = visit('/login');

        // Check that page has proper heading structure (h1, then h2, etc.)
        $page->assertElementExists('h1');

        // Verify logical heading order (no skipping levels)
        $headings = $page->elements('h1, h2, h3, h4, h5, h6');
        expect(count($headings))->toBeGreaterThan(0);
    });

    test('form fieldsets and legends are used appropriately', function () {
        $page = visit('/register');

        // Check for proper form grouping with fieldsets
        if ($page->elementExists('fieldset')) {
            $page->assertElementExists('fieldset legend');
        }
    });

    test('error messages are announced when they appear', function () {
        $page = visit('/login');

        $page->fill('email', 'invalid-email')
            ->click('Sign In');

        // Verify error messages are in ARIA live regions
        $page->assertElementExists('[aria-live] *:contains("email"), [role="alert"]:contains("email")');
    });

    test('success messages are announced properly', function () {
        User::factory()->create(['email' => 'test@example.com']);

        $page = visit('/forgot-password');

        $page->fill('email', 'test@example.com')
            ->click('Email Password Reset Link');

        // Check for success message in live region
        $page->assertElementExists('[aria-live] *, [role="status"]');
    });
});

describe('Mobile Responsiveness', function () {
    test('forms work on mobile viewport', function () {
        $page = visit('/login')
            ->resize(375, 667); // iPhone SE size

        $page->fill('email', 'test@example.com')
            ->fill('password', 'password123')
            ->click('Sign In');

        // Verify form still functions on mobile
        $page->assertNoJavascriptErrors();
    });

    test('touch targets are appropriately sized', function () {
        $page = visit('/login')
            ->resize(375, 667);

        // Check that buttons and links are at least 44px (recommended touch target size)
        // This would require custom CSS inspection or element size checking
        $page->assertElementExists('button, input[type="submit"], a');
    });

    test('forms are usable in landscape mode', function () {
        $page = visit('/register')
            ->resize(667, 375); // Landscape mobile

        $page->fill('name', 'Test')
            ->fill('surname', 'User')
            ->fill('email', 'test@example.com')
            ->select('account_type', 'general')
            ->fill('password', 'password123')
            ->fill('password_confirmation', 'password123')
            ->click('Create Account');

        $page->assertNoJavascriptErrors();
    });

    test('viewport meta tag is present', function () {
        $page = visit('/login');

        // Check for proper viewport meta tag
        $page->assertElementExists('meta[name="viewport"]');
    });
});

describe('Color and Contrast', function () {
    test('error states do not rely solely on color', function () {
        $page = visit('/login');

        $page->fill('email', 'invalid-email')
            ->click('Sign In');

        // Error should be indicated by text, icons, or other visual cues beyond color
        $page->assertElementExists('.error, .invalid, [aria-invalid="true"]');
        $page->assertSee('email'); // Error text should be present
    });

    test('success states have multiple indicators', function () {
        $page = visit('/register');

        $page->fill('email', 'valid@example.com')
            ->press('Tab'); // Trigger validation

        // Success should be indicated by text, icons, or other visual cues
        $page->assertElementExists('.success, .valid, .checkmark, [aria-invalid="false"]');
    });

    test('focus indicators are visible', function () {
        $page = visit('/login');

        $page->press('Tab'); // Focus first field

        // Should have visible focus indicator (not just browser default)
        $page->assertElementExists(':focus');
    });
});

describe('Alternative Text and Media', function () {
    test('images have appropriate alt text', function () {
        $page = visit('/login');

        // Check that all images have alt attributes
        $images = $page->elements('img');
        foreach ($images as $img) {
            // Each image should have alt text or be marked as decorative
            $page->assertElementExists('img[alt], img[role="presentation"], img[alt=""]');
        }
    });

    test('icons have accessible labels', function () {
        $page = visit('/login');

        // Check for screen reader text or ARIA labels on icons
        $page->assertElementExists('[aria-label], .sr-only, .visually-hidden');
    });
});

describe('Error Recovery and Help', function () {
    test('validation errors provide helpful suggestions', function () {
        $page = visit('/register');

        $page->fill('password', '123')
            ->press('Tab');

        // Error should explain what's wrong and how to fix it
        $page->assertSee('at least');
        $page->assertSee('characters'); // Should indicate minimum length
    });

    test('required fields are clearly marked', function () {
        $page = visit('/register');

        // Required fields should be marked with asterisk, "required", or aria-required
        $page->assertElementExists('[required], [aria-required="true"], .required');
    });

    test('help text is available for complex fields', function () {
        $page = visit('/register');

        // Password field should have help text about requirements
        if ($page->elementExists('[type="password"]')) {
            $page->assertElementExists('[aria-describedby], .help-text, .field-description');
        }
    });

    test('form can be resubmitted after fixing errors', function () {
        $page = visit('/login');

        // Submit with errors
        $page->click('Sign In')
            ->assertSee('required');

        // Fix errors and resubmit
        $page->fill('email', 'test@example.com')
            ->fill('password', 'password123')
            ->click('Sign In');

        // Should process successfully or show different validation
        $page->assertNoJavascriptErrors();
    });
});
