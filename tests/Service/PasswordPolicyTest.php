<?php

namespace App\Tests\Service;

use App\Service\PasswordPolicy;
use PHPUnit\Framework\TestCase;

class PasswordPolicyTest extends TestCase
{
    private PasswordPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new PasswordPolicy();
    }

    public function testComplexPassword(): void
    {
        $this->assertTrue($this->policy->isComplexEnough('Abcdef1!'));
    }

    public function testTooShort(): void
    {
        $this->assertFalse($this->policy->isComplexEnough('Abc1!'));
        $this->assertFalse($this->policy->isComplexEnough('Abcdef1!', 12), 'minimum length is configurable');
    }

    public function testMissingCharacterClasses(): void
    {
        $this->assertFalse($this->policy->isComplexEnough('abcdefg1!'), 'no uppercase letter');
        $this->assertFalse($this->policy->isComplexEnough('ABCDEFG1!'), 'no lowercase letter');
        $this->assertFalse($this->policy->isComplexEnough('Abcdefgh!'), 'no digit');
        $this->assertFalse($this->policy->isComplexEnough('Abcdefgh1'), 'no special character');
    }

    public function testGeneratedPasswordsAreComplexEnough(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $password = $this->policy->generate(16);
            $this->assertSame(16, strlen($password));
            $this->assertTrue($this->policy->isComplexEnough($password, 16), "generated password $password");
        }
    }

    public function testGeneratedPasswordsAreDifferent(): void
    {
        $this->assertNotSame($this->policy->generate(), $this->policy->generate());
    }

    public function testGenerateMinimumLength(): void
    {
        $this->assertSame(4, strlen($this->policy->generate(2)), 'at least one character of each class');
    }
}
