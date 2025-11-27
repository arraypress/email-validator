<?php
/**
 * Email Validation and Sanitization Functions
 *
 * Standalone email validation and sanitization functions for PHP applications.
 * RFC 5321 compliant with practical constraints.
 *
 * @package ArrayPress\EmailValidator
 * @version 1.0.0
 * @license GPL-2.0-or-later
 */

if ( ! function_exists( 'is_valid_email' ) ) {
	/**
	 * Validate an email address.
	 *
	 * Checks for proper format including length constraints, @ position,
	 * valid local part characters, and valid domain structure.
	 * Does not support internationalized (IDN) email addresses.
	 *
	 * @param string $email The email address to validate.
	 *
	 * @return bool True if the email address is valid.
	 */
	function is_valid_email( string $email ): bool {
		$email = trim( $email );

		// No whitespace or control characters allowed
		if ( preg_match( '/\s/', $email ) ) {
			return false;
		}

		// Length constraints (RFC 5321)
		if ( strlen( $email ) < 6 || strlen( $email ) > 254 ) {
			return false;
		}

		// Must have single @ after first position
		$at_pos = strpos( $email, '@' );
		if ( $at_pos === false || $at_pos < 1 || substr_count( $email, '@' ) > 1 ) {
			return false;
		}

		[ $local, $domain ] = explode( '@', $email, 2 );

		// Local part: max 64 chars (RFC 5321)
		if ( strlen( $local ) > 64 ) {
			return false;
		}

		// Local part: valid characters only
		if ( ! preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~.-]+$/', $local ) ) {
			return false;
		}

		// Local part: no leading/trailing/consecutive dots
		if ( str_starts_with( $local, '.' ) || str_ends_with( $local, '.' ) || str_contains( $local, '..' ) ) {
			return false;
		}

		// Domain: no consecutive periods or leading/trailing periods
		if ( preg_match( '/\.{2,}/', $domain ) || trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) {
			return false;
		}

		// Domain: must have at least two parts
		$subs = explode( '.', $domain );
		if ( count( $subs ) < 2 ) {
			return false;
		}

		// Validate each domain part
		foreach ( $subs as $sub ) {
			if ( $sub === '' || trim( $sub, '-' ) !== $sub || ! preg_match( '/^[a-zA-Z0-9-]+$/', $sub ) ) {
				return false;
			}
		}

		// TLD: at least 2 chars, alphabetic only
		$tld = end( $subs );
		if ( strlen( $tld ) < 2 || ! ctype_alpha( $tld ) ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	/**
	 * Sanitize an email address.
	 *
	 * Strips out all characters that are not allowable in an email address,
	 * normalizes to lowercase, and validates basic structure.
	 *
	 * Returns an empty string if the email cannot be sanitized to a valid format.
	 *
	 * @param string $email Email address to sanitize.
	 *
	 * @return string Sanitized email address or empty string if invalid.
	 */
	function sanitize_email( string $email ): string {
		// Trim whitespace and normalize to lowercase
		$email = trim( $email );
		$email = strtolower( $email );

		// Check minimum length (a@b.co = 6 chars)
		if ( strlen( $email ) < 6 ) {
			return '';
		}

		// Must have @ after first position
		if ( strpos( $email, '@', 1 ) === false ) {
			return '';
		}

		// Split into local and domain parts
		[ $local, $domain ] = explode( '@', $email, 2 );

		/*
		 * LOCAL PART
		 * Strip invalid characters, keeping only allowed characters per RFC 5321.
		 */
		$local = preg_replace( '/[^a-z0-9!#$%&\'*+\/=?^_`{|}~.-]/', '', $local );
		if ( $local === '' ) {
			return '';
		}

		/*
		 * DOMAIN PART
		 * Normalize consecutive periods to single period.
		 */
		$domain = preg_replace( '/\.{2,}/', '.', $domain );

		// Remove leading/trailing periods and whitespace
		$domain = trim( $domain, " \t\n\r\0\x0B." );
		if ( $domain === '' ) {
			return '';
		}

		// Split domain into subdomains
		$subs = explode( '.', $domain );

		// Must have at least two parts (domain + TLD)
		if ( count( $subs ) < 2 ) {
			return '';
		}

		// Sanitize each subdomain part
		$valid_subs = [];
		foreach ( $subs as $sub ) {
			// Remove leading/trailing hyphens and whitespace
			$sub = trim( $sub, " \t\n\r\0\x0B-" );

			// Strip invalid characters
			$sub = preg_replace( '/[^a-z0-9-]/', '', $sub );

			// Keep non-empty parts
			if ( $sub !== '' ) {
				$valid_subs[] = $sub;
			}
		}

		// Must still have at least two valid parts
		if ( count( $valid_subs ) < 2 ) {
			return '';
		}

		// Reconstruct sanitized email
		return $local . '@' . implode( '.', $valid_subs );
	}
}