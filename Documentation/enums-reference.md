# User System Enums Reference

## Overview
This document provides a complete reference for all enum classes used in the user management system. These enums ensure type safety and consistent data values across the application.

---

## AccountType
**File**: `app/Enums/AccountType.php`  
**Purpose**: Defines user account classifications and roles within the CounselWise platform

| Enum Value | Database Value | Description | HPCSA Required |
|------------|----------------|-------------|----------------|
| `CounsellorFree` | `counsellor_free` | Free tier counsellor account | Yes |
| `CounsellorPaid` | `counsellor_paid` | Premium paid counsellor account | Yes |
| `Researcher` | `researcher` | Research access account | No |
| `SuperAdmin` | `super_admin` | System administrator | No |
| `StudentRc` | `student_rc` | Student counsellor account | Yes |

**Default**: `CounsellorFree`  
**Usage**: Account creation, permission checks, feature access

---

## AccountStatus
**File**: `app/Enums/AccountStatus.php`  
**Purpose**: Manages user account lifecycle states

| Enum Value | Database Value | Description | User Access |
|------------|----------------|-------------|-------------|
| `Pending` | `pending` | Account awaiting verification | Limited |
| `Active` | `active` | Fully verified and active account | Full |
| `Suspended` | `suspended` | Temporarily disabled account | None |
| `Deleted` | `deleted` | Soft-deleted account | None |

**Default**: `Pending`  
**Usage**: Authentication checks, access control, account management

---

## Gender
**File**: `app/Enums/Gender.php`  
**Purpose**: Demographic information for user profiles

| Enum Value | Database Value | Description |
|------------|----------------|-------------|
| `Male` | `male` | Male gender |
| `Female` | `female` | Female gender |
| `PreferNotToSay` | `prefer_not_to_say` | User prefers not to disclose |

**Default**: None (nullable field)  
**Usage**: Profile management, demographics, optional field

---

## Language
**File**: `app/Enums/Language.php`  
**Purpose**: South African official language preferences for localization

| Enum Value | Database Value | Language Name |
|------------|----------------|---------------|
| `English` | `english` | English |
| `Afrikaans` | `afrikaans` | Afrikaans |
| `Zulu` | `zulu` | isiZulu |
| `Xhosa` | `xhosa` | isiXhosa |
| `Sotho` | `sotho` | Sesotho |
| `Tswana` | `tswana` | Setswana |
| `Venda` | `venda` | Tshivenda |
| `Tsonga` | `tsonga` | Xitsonga |
| `Ndebele` | `ndebele` | isiNdebele |
| `Swati` | `swati` | siSwati |
| `Pedi` | `pedi` | Sepedi |

**Default**: `English`  
**Usage**: UI localization, communication preferences

---

## SouthAfricanProvince
**File**: `app/Enums/SouthAfricanProvince.php`  
**Purpose**: Regional identification for South African users

| Enum Value | Database Value | Province Name |
|------------|----------------|---------------|
| `WesternCape` | `western_cape` | Western Cape |
| `Gauteng` | `gauteng` | Gauteng |
| `KwaZuluNatal` | `kwazulu_natal` | KwaZulu-Natal |
| `EasternCape` | `eastern_cape` | Eastern Cape |
| `Limpopo` | `limpopo` | Limpopo |
| `Mpumalanga` | `mpumalanga` | Mpumalanga |
| `NorthWest` | `north_west` | North West |
| `NorthernCape` | `northern_cape` | Northern Cape |
| `FreeState` | `free_state` | Free State |

**Default**: None (nullable field)  
**Usage**: Regional services, location-based features

---

## ThemePreference
**File**: `app/Enums/ThemePreference.php`  
**Purpose**: User interface theme selection

| Enum Value | Database Value | Description |
|------------|----------------|-------------|
| `Light` | `light` | Light theme UI |
| `Dark` | `dark` | Dark theme UI |
| `System` | `system` | Follow system theme preference |

**Default**: None (nullable field)  
**Usage**: UI customization, user preferences

---

## Usage Examples

### In Models
```php
// User.php casts
protected function casts(): array
{
    return [
        'account_type' => AccountType::class,
        'account_status' => AccountStatus::class,
        'gender' => Gender::class,
        'language' => Language::class,
        'region' => SouthAfricanProvince::class,
        'theme_preference' => ThemePreference::class,
    ];
}
```

### In Migrations
```php
// Using enum values in migrations
$table->enum('account_type', array_column(AccountType::cases(), 'value'))
      ->default(AccountType::CounsellorFree->value);
```

### In Factory States
```php
// UserFactory.php
public function freeCounsellor(): static
{
    return $this->state(fn (array $attributes) => [
        'account_type' => AccountType::CounsellorFree->value,
        'account_status' => AccountStatus::Active->value,
    ]);
}
```

## Notes
- All enums are backed by string values for database storage
- Enum casting is handled automatically by Laravel 12
- Adding new enum values requires database migration for existing enum columns
- Enum values should maintain backwards compatibility