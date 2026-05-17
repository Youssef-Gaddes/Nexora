<?php

namespace App\Tests\Doctrine;

use PHPUnit\Framework\TestCase;

/**
 * Tests Doctrine : vérifie la cohérence des relations bidirectionnelles,
 * les cascades, l'orphan removal et les JoinColumn (nullable, onDelete).
 */
class RelationshipIntegrityTest extends TestCase
{
    // ══════════════════════════════════════════════
    //  1. Bidirectional OneToOne — User ↔ Wallet
    // ══════════════════════════════════════════════

    public function testUserWalletBidirectional_UserSide(): void
    {
        // User.wallet → OneToOne mappedBy='user'
        $prop = $this->getProperty('App\Entity\User', 'wallet');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('user', $args['mappedBy'] ?? null,
            'User::$wallet must have mappedBy=user');
    }

    public function testUserWalletBidirectional_WalletSide(): void
    {
        // Wallet.user → OneToOne inversedBy='wallet'
        $prop = $this->getProperty('App\Entity\Wallet', 'user');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('wallet', $args['inversedBy'] ?? null,
            'Wallet::$user must have inversedBy=wallet');
    }

    // ══════════════════════════════════════════════
    //  2. Bidirectional OneToOne — User ↔ Portfolio
    // ══════════════════════════════════════════════

    public function testUserPortfolioBidirectional_UserSide(): void
    {
        $prop = $this->getProperty('App\Entity\User', 'portfolio');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('user', $args['mappedBy'] ?? null);
    }

    public function testUserPortfolioBidirectional_PortfolioSide(): void
    {
        $prop = $this->getProperty('App\Entity\Portfolio', 'user');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('portfolio', $args['inversedBy'] ?? null);
    }

    // ══════════════════════════════════════════════
    //  3. Bidirectional OneToOne — User ↔ UserReputation
    // ══════════════════════════════════════════════

    public function testUserReputationBidirectional_UserSide(): void
    {
        $prop = $this->getProperty('App\Entity\User', 'reputation');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('user', $args['mappedBy'] ?? null);
    }

    public function testUserReputationBidirectional_ReputationSide(): void
    {
        $prop = $this->getProperty('App\Entity\UserReputation', 'user');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('reputation', $args['inversedBy'] ?? null);
    }

    // ══════════════════════════════════════════════
    //  4. OneToMany — Wallet → WalletGoal (orphanRemoval)
    // ══════════════════════════════════════════════

    public function testWalletGoalsOrphanRemoval(): void
    {
        $prop = $this->getProperty('App\Entity\Wallet', 'walletGoals');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToMany');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue($args['orphanRemoval'] ?? false,
            'Wallet::$walletGoals must have orphanRemoval=true');
    }

    // ══════════════════════════════════════════════
    //  5. OneToMany — Wallet → ActivityLog (orphanRemoval)
    // ══════════════════════════════════════════════

    public function testWalletActivityLogsOrphanRemoval(): void
    {
        $prop = $this->getProperty('App\Entity\Wallet', 'activityLogs');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToMany');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue($args['orphanRemoval'] ?? false,
            'Wallet::$activityLogs must have orphanRemoval=true');
    }

    // ══════════════════════════════════════════════
    //  6. OneToMany — Wallet → Notification (orphanRemoval)
    // ══════════════════════════════════════════════

    public function testWalletNotificationsOrphanRemoval(): void
    {
        $prop = $this->getProperty('App\Entity\Wallet', 'notifications');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToMany');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue($args['orphanRemoval'] ?? false,
            'Wallet::$notifications must have orphanRemoval=true');
    }

    // ══════════════════════════════════════════════
    //  7. OneToMany — Wallet → Transaction (orphanRemoval)
    // ══════════════════════════════════════════════

    public function testWalletTransactionsOrphanRemoval(): void
    {
        $prop = $this->getProperty('App\Entity\Wallet', 'transactions');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToMany');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue($args['orphanRemoval'] ?? false,
            'Wallet::$transactions must have orphanRemoval=true');
    }

    // ══════════════════════════════════════════════
    //  8. Portfolio → PortfolioAsset cascade persist+remove
    // ══════════════════════════════════════════════

    public function testPortfolioAssetsCascade(): void
    {
        $prop = $this->getProperty('App\Entity\Portfolio', 'portfolioAssets');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToMany');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $cascade = $args['cascade'] ?? [];
        $this->assertContains('persist', $cascade, 'Portfolio::$portfolioAssets must cascade persist');
        $this->assertContains('remove', $cascade, 'Portfolio::$portfolioAssets must cascade remove');
    }

    // ══════════════════════════════════════════════
    //  9. User → Portfolio cascade persist+remove
    // ══════════════════════════════════════════════

    public function testUserPortfolioCascade(): void
    {
        $prop = $this->getProperty('App\Entity\User', 'portfolio');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $cascade = $args['cascade'] ?? [];
        $this->assertContains('persist', $cascade, 'User::$portfolio must cascade persist');
        $this->assertContains('remove', $cascade, 'User::$portfolio must cascade remove');
    }

    // ══════════════════════════════════════════════
    //  10. User → UserReputation cascade persist+remove
    // ══════════════════════════════════════════════

    public function testUserReputationCascade(): void
    {
        $prop = $this->getProperty('App\Entity\User', 'reputation');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $cascade = $args['cascade'] ?? [];
        $this->assertContains('persist', $cascade, 'User::$reputation must cascade persist');
        $this->assertContains('remove', $cascade, 'User::$reputation must cascade remove');
    }

    // ══════════════════════════════════════════════
    //  11. JoinColumn — nullable
    // ══════════════════════════════════════════════

    public function testWalletUserJoinColumnIsNullable(): void
    {
        $prop = $this->getProperty('App\Entity\Wallet', 'user');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\JoinColumn');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue($args['nullable'] ?? false,
            'Wallet::$user JoinColumn should be nullable');
    }

    public function testWalletUserJoinColumnOnDelete(): void
    {
        $prop = $this->getProperty('App\Entity\Wallet', 'user');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\JoinColumn');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('SET NULL', $args['onDelete'] ?? null,
            'Wallet::$user JoinColumn must have onDelete=SET NULL');
    }

    public function testPortfolioUserJoinColumnIsNotNullable(): void
    {
        $prop = $this->getProperty('App\Entity\Portfolio', 'user');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\JoinColumn');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertFalse($args['nullable'] ?? true,
            'Portfolio::$user JoinColumn should NOT be nullable');
    }

    public function testP2pContractAcceptorJoinColumnIsNullable(): void
    {
        $prop = $this->getProperty('App\Entity\P2pContract', 'acceptor');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\JoinColumn');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue($args['nullable'] ?? false,
            'P2pContract::$acceptor JoinColumn should be nullable');
    }

    public function testTransactionCategoryJoinColumnIsNullable(): void
    {
        $prop = $this->getProperty('App\Entity\Transaction', 'category');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\JoinColumn');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue($args['nullable'] ?? false,
            'Transaction::$category JoinColumn should be nullable');
    }

    // ══════════════════════════════════════════════
    //  12. Bidirectional OneToMany — mapped sides
    // ══════════════════════════════════════════════

    /**
     * @dataProvider oneToManyMappedByProvider
     */
    public function testOneToManyMappedByIsCorrect(string $class, string $property, string $expectedMappedBy): void
    {
        $prop = $this->getProperty($class, $property);
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\OneToMany');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame($expectedMappedBy, $args['mappedBy'] ?? null,
            sprintf('%s::$%s must have mappedBy=%s', $class, $property, $expectedMappedBy));
    }

    /**
     * @dataProvider manyToOneInversedByProvider
     */
    public function testManyToOneInversedByIsCorrect(string $class, string $property, string $expectedInversedBy): void
    {
        $prop = $this->getProperty($class, $property);
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\ManyToOne');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame($expectedInversedBy, $args['inversedBy'] ?? null,
            sprintf('%s::$%s must have inversedBy=%s', $class, $property, $expectedInversedBy));
    }

    // ══════════════════════════════════════════════
    //  13. Column types for specific fields
    // ══════════════════════════════════════════════

    public function testWalletBalanceIsDecimalType(): void
    {
        $prop = $this->getProperty('App\Entity\Wallet', 'balance');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('decimal', $args['type'] ?? null);
        $this->assertSame(15, $args['precision'] ?? null);
        $this->assertSame(2, $args['scale'] ?? null);
    }

    public function testTransactionAmountIsDecimalType(): void
    {
        $prop = $this->getProperty('App\Entity\Transaction', 'amount');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('decimal', $args['type'] ?? null);
        $this->assertSame(15, $args['precision'] ?? null);
        $this->assertSame(2, $args['scale'] ?? null);
    }

    public function testWalletGoalTargetAmountIsDecimalType(): void
    {
        $prop = $this->getProperty('App\Entity\WalletGoal', 'targetAmount');
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame('decimal', $args['type'] ?? null);
    }

    // ──────────────────────────────────────────────
    //  Data Providers
    // ──────────────────────────────────────────────

    public static function oneToManyMappedByProvider(): array
    {
        return [
            ['App\Entity\Wallet',    'walletGoals',    'wallet'],
            ['App\Entity\Wallet',    'activityLogs',   'wallet'],
            ['App\Entity\Wallet',    'notifications',  'wallet'],
            ['App\Entity\Wallet',    'transactions',   'wallet'],
            ['App\Entity\Portfolio', 'portfolioAssets', 'portfolio'],
            ['App\Entity\Category',  'transactions',   'category'],
        ];
    }

    public static function manyToOneInversedByProvider(): array
    {
        return [
            ['App\Entity\Transaction',  'wallet',   'transactions'],
            ['App\Entity\Transaction',  'category', 'transactions'],
            ['App\Entity\ActivityLog',  'wallet',   'activityLogs'],
            ['App\Entity\Notification', 'wallet',   'notifications'],
            ['App\Entity\PortfolioAsset', 'portfolio', 'portfolioAssets'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Helper
    // ──────────────────────────────────────────────

    private function getProperty(string $class, string $property): \ReflectionProperty
    {
        $reflection = new \ReflectionClass($class);
        $this->assertTrue(
            $reflection->hasProperty($property),
            sprintf('%s must have property $%s.', $class, $property)
        );
        return $reflection->getProperty($property);
    }
}
