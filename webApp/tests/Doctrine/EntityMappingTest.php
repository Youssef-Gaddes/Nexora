<?php

namespace App\Tests\Doctrine;

use PHPUnit\Framework\TestCase;

/**
 * Tests Doctrine : vérifie les annotations/attributs ORM sur chaque entité
 * (colonnes, types, relations, table names) sans connexion à la base de données.
 */
class EntityMappingTest extends TestCase
{
    // ──────────────────────────────────────────────
    //  1. Chaque entité a l'attribut #[ORM\Entity]
    // ──────────────────────────────────────────────

    /**
     * @dataProvider entityClassProvider
     */
    public function testEntityHasOrmEntityAttribute(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $attributes = $reflection->getAttributes('Doctrine\ORM\Mapping\Entity');

        $this->assertNotEmpty(
            $attributes,
            sprintf('Entity "%s" must have #[ORM\\Entity] attribute.', $className)
        );
    }

    // ──────────────────────────────────────────────
    //  2. ID — #[ORM\Id] + #[ORM\GeneratedValue] + #[ORM\Column]
    // ──────────────────────────────────────────────

    /**
     * @dataProvider entityClassProvider
     */
    public function testEntityIdHasOrmIdAttribute(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertTrue($reflection->hasProperty('id'), sprintf('%s must have $id property.', $className));

        $prop = $reflection->getProperty('id');

        $this->assertNotEmpty(
            $prop->getAttributes('Doctrine\ORM\Mapping\Id'),
            sprintf('%s::$id must have #[ORM\\Id].', $className)
        );
        $this->assertNotEmpty(
            $prop->getAttributes('Doctrine\ORM\Mapping\GeneratedValue'),
            sprintf('%s::$id must have #[ORM\\GeneratedValue].', $className)
        );
        $this->assertNotEmpty(
            $prop->getAttributes('Doctrine\ORM\Mapping\Column'),
            sprintf('%s::$id must have #[ORM\\Column].', $className)
        );
    }

    // ──────────────────────────────────────────────
    //  3. Table names explicites
    // ──────────────────────────────────────────────

    /**
     * @dataProvider tableNameProvider
     */
    public function testEntityHasExplicitTableName(string $className, string $expectedTable): void
    {
        $reflection = new \ReflectionClass($className);
        $attributes = $reflection->getAttributes('Doctrine\ORM\Mapping\Table');

        $this->assertNotEmpty(
            $attributes,
            sprintf('Entity "%s" should have an explicit #[ORM\\Table] attribute.', $className)
        );

        $args = $attributes[0]->getArguments();
        $this->assertArrayHasKey('name', $args);
        $this->assertSame(
            $expectedTable,
            $args['name'],
            sprintf('Entity "%s" table name mismatch.', $className)
        );
    }

    // ──────────────────────────────────────────────
    //  4. User — colonnes et relations
    // ──────────────────────────────────────────────

    public function testUserEmailColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\User', 'email');
    }

    public function testUserFullNameColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\User', 'fullName');
    }

    public function testUserRolesColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\User', 'roles');
    }

    public function testUserPasswordColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\User', 'password');
    }

    public function testUserCreatedAtColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\User', 'createdAt');
    }

    public function testUserRiskProfileColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\User', 'riskProfile');
    }

    public function testUserWalletRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\User', 'wallet',
            'Doctrine\ORM\Mapping\OneToOne'
        );
    }

    public function testUserPortfolioRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\User', 'portfolio',
            'Doctrine\ORM\Mapping\OneToOne'
        );
    }

    public function testUserReputationRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\User', 'reputation',
            'Doctrine\ORM\Mapping\OneToOne'
        );
    }

    public function testUserHasUniqueEmailConstraint(): void
    {
        $reflection = new \ReflectionClass('App\Entity\User');
        $tableAttrs = $reflection->getAttributes('Doctrine\ORM\Mapping\Table');
        $this->assertNotEmpty($tableAttrs);

        $args = $tableAttrs[0]->getArguments();
        $this->assertArrayHasKey('uniqueConstraints', $args,
            'User table must define uniqueConstraints for email.'
        );
    }

    // ──────────────────────────────────────────────
    //  5. Wallet — colonnes et relations
    // ──────────────────────────────────────────────

    public function testWalletOwnerColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\Wallet', 'owner');
    }

    public function testWalletBalanceColumnMapping(): void
    {
        $this->assertPropertyHasColumnAttribute('App\Entity\Wallet', 'balance');
    }

    public function testWalletUserRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Wallet', 'user',
            'Doctrine\ORM\Mapping\OneToOne'
        );
    }

    public function testWalletGoalsRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Wallet', 'walletGoals',
            'Doctrine\ORM\Mapping\OneToMany'
        );
    }

    public function testWalletActivityLogsRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Wallet', 'activityLogs',
            'Doctrine\ORM\Mapping\OneToMany'
        );
    }

    public function testWalletNotificationsRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Wallet', 'notifications',
            'Doctrine\ORM\Mapping\OneToMany'
        );
    }

    public function testWalletTransactionsRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Wallet', 'transactions',
            'Doctrine\ORM\Mapping\OneToMany'
        );
    }

    // ──────────────────────────────────────────────
    //  6. Portfolio — relations
    // ──────────────────────────────────────────────

    public function testPortfolioUserRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Portfolio', 'user',
            'Doctrine\ORM\Mapping\OneToOne'
        );
    }

    public function testPortfolioAssetsRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Portfolio', 'portfolioAssets',
            'Doctrine\ORM\Mapping\OneToMany'
        );
    }

    // ──────────────────────────────────────────────
    //  7. Order — relations
    // ──────────────────────────────────────────────

    public function testOrderAssetRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Order', 'asset',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    public function testOrderUserRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Order', 'user',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    public function testOrderHasEscapedTableName(): void
    {
        $reflection = new \ReflectionClass('App\Entity\Order');
        $tableAttrs = $reflection->getAttributes('Doctrine\ORM\Mapping\Table');
        $this->assertNotEmpty($tableAttrs);

        $args = $tableAttrs[0]->getArguments();
        // 'orders' is escaped with backticks because ORDER is a SQL reserved word
        $this->assertStringContainsString('orders', $args['name']);
    }

    // ──────────────────────────────────────────────
    //  8. P2pContract — relations
    // ──────────────────────────────────────────────

    public function testP2pContractCreatorRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\P2pContract', 'creator',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    public function testP2pContractAssetRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\P2pContract', 'asset',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    public function testP2pContractAcceptorRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\P2pContract', 'acceptor',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    // ──────────────────────────────────────────────
    //  9. Transaction — relations
    // ──────────────────────────────────────────────

    public function testTransactionWalletRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Transaction', 'wallet',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    public function testTransactionCategoryRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Transaction', 'category',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    // ──────────────────────────────────────────────
    //  10. Category — relations
    // ──────────────────────────────────────────────

    public function testCategoryTransactionsRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Category', 'transactions',
            'Doctrine\ORM\Mapping\OneToMany'
        );
    }

    // ──────────────────────────────────────────────
    //  11. PortfolioAsset — relations
    // ──────────────────────────────────────────────

    public function testPortfolioAssetPortfolioRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\PortfolioAsset', 'portfolio',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    public function testPortfolioAssetAssetRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\PortfolioAsset', 'asset',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    // ──────────────────────────────────────────────
    //  12. Notification — relations
    // ──────────────────────────────────────────────

    public function testNotificationWalletRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\Notification', 'wallet',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    // ──────────────────────────────────────────────
    //  13. ActivityLog — relations
    // ──────────────────────────────────────────────

    public function testActivityLogWalletRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\ActivityLog', 'wallet',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    // ──────────────────────────────────────────────
    //  14. UserReputation — relations
    // ──────────────────────────────────────────────

    public function testUserReputationUserRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\UserReputation', 'user',
            'Doctrine\ORM\Mapping\OneToOne'
        );
    }

    // ──────────────────────────────────────────────
    //  15. WalletGoal — relations
    // ──────────────────────────────────────────────

    public function testWalletGoalWalletRelation(): void
    {
        $this->assertPropertyHasRelation(
            'App\Entity\WalletGoal', 'wallet',
            'Doctrine\ORM\Mapping\ManyToOne'
        );
    }

    // ──────────────────────────────────────────────
    //  Data Providers
    // ──────────────────────────────────────────────

    public static function entityClassProvider(): array
    {
        return [
            ['App\Entity\User'],
            ['App\Entity\Wallet'],
            ['App\Entity\Portfolio'],
            ['App\Entity\PortfolioAsset'],
            ['App\Entity\Asset'],
            ['App\Entity\Order'],
            ['App\Entity\P2pContract'],
            ['App\Entity\Transaction'],
            ['App\Entity\Category'],
            ['App\Entity\Notification'],
            ['App\Entity\ActivityLog'],
            ['App\Entity\UserReputation'],
            ['App\Entity\WalletGoal'],
        ];
    }

    public static function tableNameProvider(): array
    {
        return [
            ['App\Entity\User',         'users'],
            ['App\Entity\Wallet',       'wallets'],
            ['App\Entity\Order',        '`orders`'],
            ['App\Entity\Transaction',  'transactions'],
            ['App\Entity\Category',     'categories'],
            ['App\Entity\Notification', 'notifications'],
            ['App\Entity\WalletGoal',   'wallet_goals'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    private function assertPropertyHasColumnAttribute(string $class, string $property): void
    {
        $reflection = new \ReflectionClass($class);
        $this->assertTrue($reflection->hasProperty($property));
        $prop = $reflection->getProperty($property);
        $this->assertNotEmpty(
            $prop->getAttributes('Doctrine\ORM\Mapping\Column'),
            sprintf('%s::$%s must have #[ORM\\Column].', $class, $property)
        );
    }

    private function assertPropertyHasRelation(string $class, string $property, string $relationType): void
    {
        $reflection = new \ReflectionClass($class);
        $this->assertTrue(
            $reflection->hasProperty($property),
            sprintf('%s must have property $%s.', $class, $property)
        );
        $prop = $reflection->getProperty($property);
        $attrs = $prop->getAttributes($relationType);
        $this->assertNotEmpty(
            $attrs,
            sprintf('%s::$%s must have #[%s].', $class, $property, (new \ReflectionClass($relationType))->getShortName())
        );
    }
}
