<?php

namespace App\Tests\Static;

use PHPUnit\Framework\TestCase;

/**
 * Tests statiques : vérifie que les contraintes de validation Symfony
 * sont correctement déclarées sur les propriétés des entités.
 */
class ValidationConstraintsTest extends TestCase
{
    // ──────────────────────────────────────────────
    //  1. User — Validation Constraints
    // ──────────────────────────────────────────────

    public function testUserEmailHasNotBlankConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\User', 'email',
            'Symfony\Component\Validator\Constraints\NotBlank'
        );
    }

    public function testUserEmailHasEmailConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\User', 'email',
            'Symfony\Component\Validator\Constraints\Email'
        );
    }

    public function testUserFullNameHasNotBlankConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\User', 'fullName',
            'Symfony\Component\Validator\Constraints\NotBlank'
        );
    }

    public function testUserFullNameHasLengthConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\User', 'fullName',
            'Symfony\Component\Validator\Constraints\Length'
        );
    }

    // ──────────────────────────────────────────────
    //  2. Wallet — Validation Constraints
    // ──────────────────────────────────────────────

    public function testWalletOwnerHasNotBlankConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Wallet', 'owner',
            'Symfony\Component\Validator\Constraints\NotBlank'
        );
    }

    public function testWalletBalanceHasPositiveOrZeroConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Wallet', 'balance',
            'Symfony\Component\Validator\Constraints\PositiveOrZero'
        );
    }

    // ──────────────────────────────────────────────
    //  3. Asset — Validation Constraints
    // ──────────────────────────────────────────────

    public function testAssetNameHasNotBlankConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Asset', 'name',
            'Symfony\Component\Validator\Constraints\NotBlank'
        );
    }

    public function testAssetSymbolHasNotBlankConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Asset', 'symbol',
            'Symfony\Component\Validator\Constraints\NotBlank'
        );
    }

    public function testAssetSymbolHasLengthConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Asset', 'symbol',
            'Symfony\Component\Validator\Constraints\Length'
        );
    }

    public function testAssetValueHasPositiveOrZeroConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Asset', 'value',
            'Symfony\Component\Validator\Constraints\PositiveOrZero'
        );
    }

    // ──────────────────────────────────────────────
    //  4. Order — Validation Constraints
    // ──────────────────────────────────────────────

    public function testOrderQuantityHasPositiveConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Order', 'quantity',
            'Symfony\Component\Validator\Constraints\Positive'
        );
    }

    public function testOrderTypeHasChoiceConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Order', 'type',
            'Symfony\Component\Validator\Constraints\Choice'
        );
    }

    public function testOrderTypeChoiceValuesAreCorrect(): void
    {
        $reflection = new \ReflectionClass('App\Entity\Order');
        $property = $reflection->getProperty('type');
        $attributes = $property->getAttributes('Symfony\Component\Validator\Constraints\Choice');

        $this->assertNotEmpty($attributes);
        $args = $attributes[0]->getArguments();
        $this->assertContains('BUY', $args['choices']);
        $this->assertContains('SELL', $args['choices']);
    }

    // ──────────────────────────────────────────────
    //  5. P2pContract — Validation Constraints
    // ──────────────────────────────────────────────

    public function testP2pContractQuantityHasPositiveConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\P2pContract', 'quantity',
            'Symfony\Component\Validator\Constraints\Positive'
        );
    }

    public function testP2pContractTypeHasChoiceConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\P2pContract', 'contractType',
            'Symfony\Component\Validator\Constraints\Choice'
        );
    }

    public function testP2pContractStatusHasChoiceConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\P2pContract', 'status',
            'Symfony\Component\Validator\Constraints\Choice'
        );
    }

    public function testP2pContractStatusChoiceValuesAreCorrect(): void
    {
        $reflection = new \ReflectionClass('App\Entity\P2pContract');
        $property = $reflection->getProperty('status');
        $attributes = $property->getAttributes('Symfony\Component\Validator\Constraints\Choice');

        $this->assertNotEmpty($attributes);
        $args = $attributes[0]->getArguments();
        $this->assertContains('OPEN', $args['choices']);
        $this->assertContains('ACCEPTED', $args['choices']);
        $this->assertContains('COMPLETED', $args['choices']);
        $this->assertContains('CANCELLED', $args['choices']);
    }

    // ──────────────────────────────────────────────
    //  6. Portfolio — Validation Constraints
    // ──────────────────────────────────────────────

    public function testPortfolioUserHasNotNullConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Portfolio', 'user',
            'Symfony\Component\Validator\Constraints\NotNull'
        );
    }

    public function testPortfolioTotalValueHasPositiveOrZeroConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\Portfolio', 'totalValue',
            'Symfony\Component\Validator\Constraints\PositiveOrZero'
        );
    }

    // ──────────────────────────────────────────────
    //  7. UserReputation — Validation Constraints
    // ──────────────────────────────────────────────

    public function testUserReputationCompletedContractsHasPositiveOrZero(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\UserReputation', 'completedContracts',
            'Symfony\Component\Validator\Constraints\PositiveOrZero'
        );
    }

    public function testUserReputationCanceledContractsHasPositiveOrZero(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\UserReputation', 'canceledContracts',
            'Symfony\Component\Validator\Constraints\PositiveOrZero'
        );
    }

    public function testUserReputationTotalScoreHasPositiveOrZero(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\UserReputation', 'totalScore',
            'Symfony\Component\Validator\Constraints\PositiveOrZero'
        );
    }

    // ──────────────────────────────────────────────
    //  8. WalletGoal — Validation Constraints
    // ──────────────────────────────────────────────

    public function testWalletGoalNameHasNotBlankConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\WalletGoal', 'name',
            'Symfony\Component\Validator\Constraints\NotBlank'
        );
    }

    public function testWalletGoalTargetAmountHasPositiveConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\WalletGoal', 'targetAmount',
            'Symfony\Component\Validator\Constraints\Positive'
        );
    }

    public function testWalletGoalStatusHasChoiceConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\WalletGoal', 'status',
            'Symfony\Component\Validator\Constraints\Choice'
        );
    }

    public function testWalletGoalDeadlineHasNotNullConstraint(): void
    {
        $this->assertPropertyHasAttribute(
            'App\Entity\WalletGoal', 'deadline',
            'Symfony\Component\Validator\Constraints\NotNull'
        );
    }

    // ──────────────────────────────────────────────
    //  Helper
    // ──────────────────────────────────────────────

    private function assertPropertyHasAttribute(string $class, string $property, string $attribute): void
    {
        $reflection = new \ReflectionClass($class);
        $this->assertTrue(
            $reflection->hasProperty($property),
            sprintf('Class "%s" must have property "%s".', $class, $property)
        );

        $prop = $reflection->getProperty($property);
        $attrs = $prop->getAttributes($attribute);

        $this->assertNotEmpty(
            $attrs,
            sprintf(
                'Property "%s::%s" must have #[%s] attribute.',
                $class, $property, (new \ReflectionClass($attribute))->getShortName()
            )
        );
    }
}
