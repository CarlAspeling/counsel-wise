# CounselWise UI Architecture & Design System

## Overview
CounselWise features a comprehensive UI architecture combining Tailwind CSS with SCSS, modern Vue.js components, and a cohesive design system. The application includes a complete landing page experience, enhanced authentication flows, and a flexible component library.

## Architecture Stack

### Core Technologies
- **Tailwind CSS v3**: Utility-first CSS framework with custom configuration
- **SCSS**: Modular styling architecture with design tokens and mixins
- **Vue.js 3**: Component-based UI with Composition API
- **Heroicons**: SVG icon system for consistent iconography
- **Vite**: Modern build system with Sass integration

### Build System
- **Vite Configuration**: SCSS compilation and asset processing
- **PostCSS**: CSS processing with Autoprefixer
- **Sass**: SCSS preprocessing for enhanced styling capabilities

## File Structure

### Stylesheets
```
resources/
├── css/
│   ├── app.css              # Tailwind integration and component classes
│   └── variables.css        # CSS custom properties (design tokens)
└── scss/
    ├── app.scss             # Main SCSS entry point
    ├── _variables.scss      # Design tokens and SCSS variables
    ├── _mixins.scss        # Reusable mixins library
    ├── _components.scss    # Component-specific styles
    └── _utilities.scss     # Custom utility classes
```

### Vue Components
```
resources/js/Components/
├── Auth/                    # Authentication components
│   ├── ErrorAlert.vue       # Error message display
│   ├── FormValidation.vue   # Form error handling
│   ├── LoadingSpinner.vue   # Loading states
│   └── SuccessAlert.vue     # Success messages
├── Landing/                 # Landing page components
│   ├── HeroSection.vue      # Main hero section
│   └── HowItWorks.vue       # Process explanation
├── Layout/                  # Layout components
│   ├── SiteFooter.vue       # Application footer
│   └── SiteHeader.vue       # Application header
└── [Reusable Components]
    ├── InputError.vue       # Input error display
    ├── InputLabel.vue       # Form labels
    ├── PasswordStrengthIndicator.vue  # Password validation
    ├── PrimaryButton.vue    # Primary button component
    └── TextInput.vue        # Text input component
```

### Pages & Layouts
```
resources/js/
├── layouts/
│   └── AppLayout.vue        # Authenticated app layout
└── pages/
    ├── Welcome.vue          # Landing page
    └── auth/
        ├── Login.vue        # Login form
        └── Register.vue     # Registration form
```

## Design System

### Color Palette
```scss
// Brand Colors
--color-brand-primary: #14b8a6      // Teal-500
--color-brand-secondary: #334155    // Slate-700

// Functional Colors
--color-success: #10b981            // Green-500
--color-error: #ef4444              // Red-500
--color-warning: #f59e0b            // Amber-500
--color-info: #3b82f6               // Blue-500

// Neutral Colors
--gray-50: #f8fafc
--gray-100: #f1f5f9
--gray-200: #e2e8f0
// ... extending through gray-900
```

### Typography
```scss
--font-family-sans: 'Inter', 'Figtree', system-ui, sans-serif

// Font Sizes
--text-xs: 0.75rem     // 12px
--text-sm: 0.875rem    // 14px
--text-base: 1rem      // 16px
--text-lg: 1.125rem    // 18px
--text-xl: 1.25rem     // 20px
--text-2xl: 1.5rem     // 24px
--text-3xl: 1.875rem   // 30px
--text-4xl: 2.25rem    // 36px
--text-5xl: 3rem       // 48px

// Font Weights
--font-normal: 400
--font-medium: 500
--font-semibold: 600
--font-bold: 700
```

### Spacing & Layout
```scss
// Spacing Scale
--spacing-xs: 0.25rem   // 4px
--spacing-sm: 0.5rem    // 8px
--spacing-md: 1rem      // 16px
--spacing-lg: 1.5rem    // 24px
--spacing-xl: 2rem      // 32px
--spacing-2xl: 2.5rem   // 40px

// Border Radius
--radius-sm: 0.25rem    // 4px
--radius-md: 0.375rem   // 6px
--radius-lg: 0.5rem     // 8px
--radius-xl: 0.75rem    // 12px
--radius-full: 9999px   // Full radius
```

## Component Classes

### Form Components
```html
<!-- Input fields -->
<input class="form-input" />
<input class="form-input form-input-error" />

<!-- Labels with icons -->
<label class="form-label">
  <EnvelopeIcon class="h-4 w-4 text-brand mr-2" />
  Email Address
</label>

<!-- Form validation -->
<FormValidation :field-error="errors.email" error-id="email-error" />
```

### Button Components
```html
<!-- Primary actions -->
<button class="btn-primary">Primary Action</button>
<button class="btn-primary btn-large">Large Primary</button>

<!-- Secondary actions -->
<button class="btn-secondary">Secondary Action</button>
```

### Card Components
```html
<div class="card">
  <div class="card-header">
    <h3>Card Title</h3>
  </div>
  <p>Card content goes here...</p>
</div>
```

### Navigation Components
```html
<a class="nav-link">Navigation Link</a>
<a class="nav-link nav-link-active">Active Link</a>
<a class="nav-brand">Brand Link</a>
```

## Icon System

### Heroicons Integration
All form fields and UI elements use Heroicons for consistent iconography:

```vue
<!-- Login Form -->
<EnvelopeIcon class="h-4 w-4 text-brand mr-2" />  <!-- Email field -->
<LockClosedIcon class="h-4 w-4 text-brand mr-2" /> <!-- Password field -->

<!-- Register Form -->
<UserIcon class="h-4 w-4 text-brand mr-2" />           <!-- Name fields -->
<IdentificationIcon class="h-4 w-4 text-brand mr-2" /> <!-- HPCSA number -->
<UserGroupIcon class="h-4 w-4 text-brand mr-2" />      <!-- Account type -->
<ShieldCheckIcon class="h-4 w-4 text-brand mr-2" />    <!-- Password confirmation -->

<!-- Footer Contact -->
<MapPinIcon class="h-4 w-4 text-primary-300 mr-3" />   <!-- Location -->
<PhoneIcon class="h-4 w-4 text-primary-300 mr-3" />    <!-- Phone -->
<EnvelopeIcon class="h-4 w-4 text-primary-300 mr-3" /> <!-- Email -->
```

### Icon Standards
- **Size**: `h-4 w-4` (16px) for form labels, `h-5 w-5` (20px) for larger elements
- **Color**: `text-brand` for primary icons, `text-primary-300` for footer
- **Spacing**: `mr-2` (8px) or `mr-3` (12px) margin-right for proper spacing
- **Semantic Usage**: Icons match their functional purpose (envelope for email, etc.)

## Layout System

### Sticky Footer Pattern
All pages implement a flexible layout ensuring footers stick to the bottom:

```vue
<div class="min-h-screen bg-white flex flex-col">
  <!-- Header -->
  <SiteHeader />

  <!-- Main content area -->
  <main class="flex-1 bg-gray-50">
    <!-- Page content -->
  </main>

  <!-- Footer -->
  <SiteFooter />
</div>
```

### Responsive Design
- **Mobile-first**: Base styles for mobile, enhanced with responsive utilities
- **Breakpoints**: `sm:`, `md:`, `lg:`, `xl:` for progressive enhancement
- **Grid System**: CSS Grid and Flexbox for layout structure
- **Component Responsiveness**: All components adapt to viewport size

## Page Implementations

### Landing Page (`Welcome.vue`)
- **Hero Section**: Value proposition with call-to-action
- **How It Works**: Process explanation with visual steps
- **Responsive Layout**: Mobile-optimized with desktop enhancements
- **Background System**: Proper gray background extension to footer

### Authentication Pages
**Login Form**:
- Email and password fields with icons
- Remember me checkbox
- Forgot password link
- Loading states and error handling

**Register Form**:
- Multi-step form with validation
- Password strength indicator
- Real-time password confirmation matching
- Professional account type selection

## Styling Architecture

### Tailwind Configuration
Extended configuration includes:
- **Custom Colors**: Brand palette with semantic naming
- **Typography**: Custom font family and enhanced scale
- **Spacing**: Extended spacing scale for consistent layouts
- **Components**: Pre-built component classes
- **Utilities**: Custom utility classes for common patterns

### SCSS Integration
SCSS provides additional capabilities:
- **Variables**: Design tokens and component-specific values
- **Mixins**: Reusable styling patterns and responsive breakpoints
- **Functions**: Color manipulation and calculation utilities
- **Nested Selectors**: Component-scoped styling when needed

## Development Workflow

### Building Styles
```bash
npm run dev         # Development server with watch mode
npm run build       # Production build
```

### Component Development
1. **Check Existing**: Review component library for reusable elements
2. **Follow Patterns**: Use established naming and structure conventions
3. **Use Design Tokens**: Reference CSS variables for consistency
4. **Icon Integration**: Include appropriate Heroicons for UI elements
5. **Responsive Design**: Ensure mobile-first responsive behavior

### Style Guidelines
1. **Tailwind First**: Use utility classes for standard styling
2. **Component Classes**: Use pre-built classes for common patterns
3. **SCSS Enhancement**: Use SCSS for complex or dynamic styling
4. **Design Tokens**: Reference CSS variables for maintainable code
5. **Icon Consistency**: Follow established icon sizing and spacing

## Maintenance & Updates

### Adding New Components
1. Follow existing component structure and naming
2. Include proper TypeScript/props definitions
3. Implement responsive design patterns
4. Add appropriate icons if UI elements require them
5. Update this documentation with new patterns

### Updating Styles
1. Modify design tokens in `_variables.scss` for global changes
2. Extend component classes in `app.css` for new patterns
3. Update Tailwind config for new utilities or extensions
4. Test across all breakpoints and components

### Future Considerations
- **Dark Mode**: CSS variables structure supports theme switching
- **Tailwind v4**: Current architecture can be migrated
- **Component Library**: Can be extracted into standalone package
- **Design System**: Foundation for comprehensive design system expansion