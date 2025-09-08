<?php

use App\Enums\AccountType;

test('account type enum has correct values', function () {
    expect(AccountType::CounsellorFree->value)->toBe('counsellor_free');
    expect(AccountType::CounsellorPaid->value)->toBe('counsellor_paid');
    expect(AccountType::Researcher->value)->toBe('researcher');
    expect(AccountType::SuperAdmin->value)->toBe('super_admin');
    expect(AccountType::StudentRc->value)->toBe('student_rc');
});

test('account type enum has correct count', function () {
    expect(AccountType::cases())->toHaveCount(5);
});

test('account type enum can be created from string values', function () {
    expect(AccountType::from('counsellor_free'))->toBe(AccountType::CounsellorFree);
    expect(AccountType::from('counsellor_paid'))->toBe(AccountType::CounsellorPaid);
    expect(AccountType::from('researcher'))->toBe(AccountType::Researcher);
    expect(AccountType::from('super_admin'))->toBe(AccountType::SuperAdmin);
    expect(AccountType::from('student_rc'))->toBe(AccountType::StudentRc);
});

test('account type enum throws exception for invalid values', function () {
    AccountType::from('invalid_type');
})->throws(ValueError::class);

test('account type enum tryFrom returns null for invalid values', function () {
    expect(AccountType::tryFrom('invalid_type'))->toBeNull();
});