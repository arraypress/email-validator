# Email Validator

Standalone PHP email validation and sanitization functions. RFC 5321 compliant.

## Installation
```bash
composer require arraypress/email-validator
```

## Usage

### Validation
```php
if ( is_valid_email( 'user@example.com' ) ) {
    // Valid email
}
```

### Sanitization
```php
$clean = sanitize_email( '  USER@EXAMPLE.COM  ' );
// Returns: 'user@example.com'

$clean = sanitize_email( 'test@domain..com' );
// Returns: 'test@domain.com'

$clean = sanitize_email( 'invalid' );
// Returns: '' (empty string)
```

## Validation Rules

- Length: 6-254 characters
- Single `@` symbol after first position
- No whitespace or control characters
- Local part: max 64 characters, valid characters only, no leading/trailing/consecutive dots
- Domain: at least two parts, no consecutive dots, no leading/trailing hyphens
- TLD: minimum 2 characters, alphabetic only

## Sanitization Behavior

- Trims whitespace
- Converts to lowercase
- Strips invalid characters from local part
- Normalizes consecutive dots in domain
- Removes leading/trailing dots and hyphens from domain parts
- Returns empty string if email cannot be sanitized to valid format

## Requirements

- PHP 8.0+

## License

GPL-2.0-or-later