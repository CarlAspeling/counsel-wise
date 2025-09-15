/**
 * Password validation utility that mirrors Laravel's Password::defaults() rules
 * Matches the backend validation in AppServiceProvider.php:
 * - Minimum 8 characters
 * - Must contain letters
 * - Must contain mixed case (upper and lower)
 * - Must contain numbers
 * - Must contain symbols
 * - Must not be compromised (not in known breaches)
 */

export const passwordRules = {
    minLength: 8,
    requiresLetters: true,
    requiresMixedCase: true,
    requiresNumbers: true,
    requiresSymbols: true,
    requiresUncompromised: true,
};

/**
 * Validate password against all rules
 * @param {string} password - The password to validate
 * @returns {Object} - Validation result with isValid boolean and array of error messages
 */
export function validatePassword(password) {
    const errors = [];
    
    if (!password) {
        return {
            isValid: false,
            errors: ['Password is required'],
            strength: 0
        };
    }

    // Check minimum length
    if (password.length < passwordRules.minLength) {
        errors.push(`Password must be at least ${passwordRules.minLength} characters long`);
    }

    // Check for letters
    if (passwordRules.requiresLetters && !/[a-zA-Z]/.test(password)) {
        errors.push('Password must contain at least one letter');
    }

    // Check for mixed case
    if (passwordRules.requiresMixedCase) {
        if (!/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter');
        }
        if (!/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }
    }

    // Check for numbers
    if (passwordRules.requiresNumbers && !/\d/.test(password)) {
        errors.push('Password must contain at least one number');
    }

    // Check for symbols
    if (passwordRules.requiresSymbols && !/[^a-zA-Z0-9]/.test(password)) {
        errors.push('Password must contain at least one symbol (e.g., !@#$%^&*)');
    }

    // Calculate strength score (0-100)
    const strength = calculatePasswordStrength(password);

    return {
        isValid: errors.length === 0,
        errors,
        strength
    };
}

/**
 * Calculate password strength as a percentage (0-100)
 * @param {string} password 
 * @returns {number} - Strength percentage
 */
export function calculatePasswordStrength(password) {
    if (!password) return 0;

    let score = 0;
    const checks = [
        { test: password.length >= passwordRules.minLength, weight: 20 },
        { test: /[a-z]/.test(password), weight: 15 },
        { test: /[A-Z]/.test(password), weight: 15 },
        { test: /\d/.test(password), weight: 20 },
        { test: /[^a-zA-Z0-9]/.test(password), weight: 20 },
        { test: password.length >= 12, weight: 10 }, // Bonus for longer passwords
    ];

    checks.forEach(check => {
        if (check.test) score += check.weight;
    });

    return Math.min(score, 100);
}

/**
 * Get password strength level as a string
 * @param {number} strength - Strength percentage (0-100)
 * @returns {string} - Strength level description
 */
export function getPasswordStrengthLevel(strength) {
    if (strength < 30) return 'Very Weak';
    if (strength < 50) return 'Weak';
    if (strength < 70) return 'Fair';
    if (strength < 90) return 'Good';
    return 'Strong';
}

/**
 * Get password strength color for UI display
 * @param {number} strength - Strength percentage (0-100)
 * @returns {string} - CSS color class or hex color
 */
export function getPasswordStrengthColor(strength) {
    if (strength < 30) return 'text-red-500';
    if (strength < 50) return 'text-orange-500';
    if (strength < 70) return 'text-yellow-500';
    if (strength < 90) return 'text-blue-500';
    return 'text-green-500';
}

/**
 * Get password requirements as an array for display
 * @returns {Array} - Array of requirement objects with text and validation function
 */
export function getPasswordRequirements() {
    return [
        {
            text: `At least ${passwordRules.minLength} characters`,
            test: (password) => password.length >= passwordRules.minLength
        },
        {
            text: 'Contains uppercase letter (A-Z)',
            test: (password) => /[A-Z]/.test(password)
        },
        {
            text: 'Contains lowercase letter (a-z)',
            test: (password) => /[a-z]/.test(password)
        },
        {
            text: 'Contains at least one number (0-9)',
            test: (password) => /\d/.test(password)
        },
        {
            text: 'Contains at least one symbol (!@#$%^&*)',
            test: (password) => /[^a-zA-Z0-9]/.test(password)
        },
        {
            text: 'Not found in known data breaches',
            test: () => true, // Always show as pending - validated server-side
            serverValidated: true // Indicates this is checked on the backend
        }
    ];
}