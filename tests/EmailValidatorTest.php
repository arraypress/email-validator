<?php

declare( strict_types=1 );

namespace ArrayPress\EmailValidator\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EmailValidatorTest extends TestCase {

	// ========================================
	// is_valid_email() tests
	// ========================================

	#[DataProvider( 'validEmailsProvider' )]
	public function test_valid_emails( string $email ): void {
		$this->assertTrue( is_valid_email( $email ) );
	}

	#[DataProvider( 'invalidEmailsProvider' )]
	public function test_invalid_emails( string $email ): void {
		$this->assertFalse( is_valid_email( $email ) );
	}

	public static function validEmailsProvider(): array {
		return [
			'simple'              => [ 'test@example.com' ],
			'with dot'            => [ 'user.name@domain.org' ],
			'with plus'           => [ 'user+tag@gmail.com' ],
			'minimum length'      => [ 'a@b.co' ],
			'subdomain'           => [ 'test@sub.domain.com' ],
			'numbers in local'    => [ 'user123@example.com' ],
			'hyphen in domain'    => [ 'test@my-domain.com' ],
			'multiple subdomains' => [ 'test@a.b.c.example.com' ],
			'special chars local' => [ 'user!#$%@example.com' ],
			'underscore local'    => [ 'user_name@example.com' ],
			'long tld'            => [ 'test@example.museum' ],
			'mixed case'          => [ 'Test@Example.COM' ],
			'apostrophe local'    => [ "o'connor@example.com" ],
			'all numbers local'   => [ '12345@example.com' ],
			'equals sign local'   => [ 'user=name@example.com' ],
			'caret local'         => [ 'user^name@example.com' ],
			'backtick local'      => [ 'user`name@example.com' ],
			'curly braces local'  => [ 'user{name}@example.com' ],
			'pipe local'          => [ 'user|name@example.com' ],
			'tilde local'         => [ 'user~name@example.com' ],
		];
	}

	public static function invalidEmailsProvider(): array {
		return [
			'empty'                   => [ '' ],
			'no at'                   => [ 'testexample.com' ],
			'no domain'               => [ 'test@' ],
			'no local'                => [ '@domain.com' ],
			'double at'               => [ 'test@@domain.com' ],
			'no tld'                  => [ 'test@domain' ],
			'leading dot domain'      => [ 'test@.domain.com' ],
			'trailing dot domain'     => [ 'test@domain.com.' ],
			'consecutive dots domain' => [ 'test@domain..com' ],
			'leading dot local'       => [ '.test@domain.com' ],
			'trailing dot local'      => [ 'test.@domain.com' ],
			'consecutive dots local'  => [ 'te..st@domain.com' ],
			'leading hyphen domain'   => [ 'test@-domain.com' ],
			'trailing hyphen domain'  => [ 'test@domain-.com' ],
			'short tld'               => [ 'test@domain.c' ],
			'numeric tld'             => [ 'test@domain.123' ],
			'local too long'          => [ str_repeat( 'a', 65 ) . '@test.com' ],
			'too short'               => [ 'a@b.c' ],
			'too long'                => [ str_repeat( 'a', 64 ) . '@' . str_repeat( 'b', 190 ) . '.com' ],
			'space in local'          => [ 'te st@domain.com' ],
			'space in domain'         => [ 'test@dom ain.com' ],
			'multiple at'             => [ 'test@test@domain.com' ],
			'newline in email'        => [ "test\n@domain.com" ],
			'tab in email'            => [ "test\t@domain.com" ],
			'null byte'               => [ "test\0@domain.com" ],
			'unicode local'           => [ 'tëst@domain.com' ],
			'unicode domain'          => [ 'test@dömain.com' ],
			'only at'                 => [ '@' ],
			'only dots'               => [ '...@.....' ],
			'brackets local'          => [ 'test[1]@domain.com' ],
			'parentheses local'       => [ 'test(1)@domain.com' ],
			'comma local'             => [ 'test,user@domain.com' ],
			'semicolon local'         => [ 'test;user@domain.com' ],
			'colon local'             => [ 'test:user@domain.com' ],
			'backslash local'         => [ 'test\\user@domain.com' ],
			'quote local'             => [ 'test"user@domain.com' ],
			'less than local'         => [ 'test<user@domain.com' ],
			'greater than local'      => [ 'test>user@domain.com' ],
		];
	}

	// ========================================
	// sanitize_email() tests
	// ========================================

	#[DataProvider( 'sanitizeEmailsProvider' )]
	public function test_sanitize_emails( string $input, string $expected ): void {
		$this->assertSame( $expected, sanitize_email( $input ) );
	}

	public static function sanitizeEmailsProvider(): array {
		return [
			'already valid'           => [ 'test@example.com', 'test@example.com' ],
			'uppercase'               => [ 'TEST@EXAMPLE.COM', 'test@example.com' ],
			'mixed case'              => [ 'TeSt@ExAmPlE.cOm', 'test@example.com' ],
			'leading whitespace'      => [ '  test@example.com', 'test@example.com' ],
			'trailing whitespace'     => [ 'test@example.com  ', 'test@example.com' ],
			'both whitespace'         => [ '  test@example.com  ', 'test@example.com' ],
			'consecutive dots domain' => [ 'test@domain..com', 'test@domain.com' ],
			'leading dot domain'      => [ 'test@.domain.com', 'test@domain.com' ],
			'trailing dot domain'     => [ 'test@domain.com.', 'test@domain.com' ],
			'leading hyphen sub'      => [ 'test@-sub.domain.com', 'test@sub.domain.com' ],
			'trailing hyphen sub'     => [ 'test@sub-.domain.com', 'test@sub.domain.com' ],
			'invalid chars local'     => [ 'te<s>t@domain.com', 'test@domain.com' ],
			'invalid chars domain'    => [ 'test@dom<a>in.com', 'test@domain.com' ],
			'empty returns empty'     => [ '', '' ],
			'too short'               => [ 'a@b', '' ],
			'no at'                   => [ 'testexample.com', '' ],
			'no domain'               => [ 'test@', '' ],
			'no local'                => [ '@domain.com', '' ],
			'only invalid chars'      => [ '<>[]@domain.com', '' ],
			'domain only dots'        => [ 'test@...', '' ],
			'strips unicode local'    => [ 'tëst@domain.com', 'tst@domain.com' ],
			'strips unicode domain'   => [ 'test@dömain.com', 'test@dmain.com' ],
			'multiple issues'         => [ '  TE<S>T@..DOM-AIN..COM.  ', 'test@dom-ain.com' ],
		];
	}

	// ========================================
	// Integration tests
	// ========================================

	public function test_sanitized_email_is_valid(): void {
		$dirty_emails = [
			'  TEST@EXAMPLE.COM  ',
			'User@Domain..Com',
			'test@-domain.com',
		];

		foreach ( $dirty_emails as $dirty ) {
			$sanitized = sanitize_email( $dirty );
			if ( $sanitized !== '' ) {
				$this->assertTrue(
					is_valid_email( $sanitized ),
					"Sanitized email '{$sanitized}' should be valid"
				);
			}
		}
	}

	public function test_valid_email_unchanged_by_sanitize(): void {
		$valid_emails = [
			'test@example.com',
			'user.name@domain.org',
			'user+tag@gmail.com',
		];

		foreach ( $valid_emails as $email ) {
			$this->assertSame(
				strtolower( $email ),
				sanitize_email( $email ),
				"Valid email should only be lowercased by sanitize"
			);
		}
	}
}