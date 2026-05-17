<?php

namespace App\Tests\Static;

use PHPUnit\Framework\TestCase;

/**
 * Tests statiques : vérifie la structure des classes Entity
 * (existence, namespace, méthodes getter/setter, types de retour).
 */
class EntityClassStructureTest extends TestCase
{
    /**
     * Liste complète des entités du projet Nexora.
     */
    private const ENTITY_CLASSES = [
        'App\Entity\User',
        'App\Entity\Wallet',
        'App\Entity\Portfolio',
        'App\Entity\PortfolioAsset',
        'App\Entity\Asset',
        'App\Entity\Order',
        'App\Entity\P2pContract',
        'App\Entity\Transaction',
        'App\Entity\Category',
        'App\Entity\Notification',
        'App\Entity\ActivityLog',
        'App\Entity\UserReputation',
        'App\Entity\WalletGoal',
    ];

    // ──────────────────────────────────────────────
    //  1. Existence & Namespace
    // ──────────────────────────────────────────────

    /**
     * @dataProvider entityClassProvider
     */
    public function testEntityClassExists(string $className): void
    {
        $this->assertTrue(
            class_exists($className),
            sprintf('Entity class "%s" does not exist.', $className)
        );
    }

    /**
     * @dataProvider entityClassProvider
     */
    public function testEntityBelongsToCorrectNamespace(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertStringStartsWith(
            'App\Entity',
            $reflection->getNamespaceName(),
            sprintf('Entity "%s" is not in the App\\Entity namespace.', $className)
        );
    }

    // ──────────────────────────────────────────────
    //  2. ID property
    // ──────────────────────────────────────────────

    /**
     * @dataProvider entityClassProvider
     */
    public function testEntityHasIdGetter(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertTrue(
            $reflection->hasMethod('getId'),
            sprintf('Entity "%s" must have a getId() method.', $className)
        );

        $method = $reflection->getMethod('getId');
        $this->assertTrue($method->isPublic(), 'getId() must be public.');
    }

    // ──────────────────────────────────────────────
    //  3. Getter / Setter pairs
    // ──────────────────────────────────────────────

    /**
     * @dataProvider entityMethodProvider
     */
    public function testEntityHasExpectedMethod(string $className, string $method): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertTrue(
            $reflection->hasMethod($method),
            sprintf('Entity "%s" must have method "%s".', $className, $method)
        );
    }

    /**
     * @dataProvider entitySetterProvider
     */
    public function testSetterReturnsSelf(string $className, string $setter): void
    {
        $reflection = new \ReflectionClass($className);
        if (!$reflection->hasMethod($setter)) {
            $this->markTestSkipped(sprintf('%s::%s does not exist.', $className, $setter));
        }

        $method = $reflection->getMethod($setter);
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType, sprintf('%s::%s must declare a return type.', $className, $setter));

        // Check return type is 'self' or 'static' or the actual class name
        $typeName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string)$returnType;
        $this->assertTrue(
            in_array($typeName, ['self', 'static', $className], true),
            sprintf('%s::%s should return self/static, got "%s".', $className, $setter, $typeName)
        );
    }

    // ──────────────────────────────────────────────
    //  4. User implements SecurityInterfaces
    // ──────────────────────────────────────────────

    public function testUserImplementsUserInterface(): void
    {
        $reflection = new \ReflectionClass('App\Entity\User');
        $this->assertTrue(
            $reflection->implementsInterface('Symfony\Component\Security\Core\User\UserInterface'),
            'User must implement UserInterface.'
        );
    }

    public function testUserImplementsPasswordAuthenticatedUserInterface(): void
    {
        $reflection = new \ReflectionClass('App\Entity\User');
        $this->assertTrue(
            $reflection->implementsInterface('Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface'),
            'User must implement PasswordAuthenticatedUserInterface.'
        );
    }

    public function testUserHasEraseCredentials(): void
    {
        $reflection = new \ReflectionClass('App\Entity\User');
        $this->assertTrue($reflection->hasMethod('eraseCredentials'));
    }

    public function testUserHasGetUserIdentifier(): void
    {
        $reflection = new \ReflectionClass('App\Entity\User');
        $this->assertTrue($reflection->hasMethod('getUserIdentifier'));
    }

    // ──────────────────────────────────────────────
    //  5. Constructor defaults
    // ──────────────────────────────────────────────

    public function testUserConstructorSetsDefaults(): void
    {
        $user = new \App\Entity\User();
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertSame('Balanced', $user->getRiskProfile());
    }

    public function testWalletConstructorSetsDefaults(): void
    {
        $wallet = new \App\Entity\Wallet();
        $this->assertSame('0.00', $wallet->getBalance());
        $this->assertInstanceOf(\DateTimeImmutable::class, $wallet->getCreatedAt());
    }

    public function testPortfolioConstructorSetsDefaults(): void
    {
        $portfolio = new \App\Entity\Portfolio();
        $this->assertSame(0.0, $portfolio->getTotalValue());
    }

    public function testP2pContractConstructorSetsDefaults(): void
    {
        $contract = new \App\Entity\P2pContract();
        $this->assertSame('OPEN', $contract->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $contract->getCreatedAt());
    }

    public function testTransactionConstructorSetsDefaults(): void
    {
        $tx = new \App\Entity\Transaction();
        $this->assertInstanceOf(\DateTimeImmutable::class, $tx->getCreatedAt());
    }

    public function testActivityLogConstructorSetsDefaults(): void
    {
        $log = new \App\Entity\ActivityLog();
        $this->assertInstanceOf(\DateTimeImmutable::class, $log->getCreatedAt());
    }

    public function testNotificationConstructorSetsDefaults(): void
    {
        $notif = new \App\Entity\Notification();
        $this->assertFalse($notif->isRead());
        $this->assertInstanceOf(\DateTimeImmutable::class, $notif->getCreatedAt());
    }

    public function testWalletGoalConstructorSetsDefaults(): void
    {
        $goal = new \App\Entity\WalletGoal();
        $this->assertInstanceOf(\DateTimeImmutable::class, $goal->getCreatedAt());
    }

    // ──────────────────────────────────────────────
    //  Data Providers
    // ──────────────────────────────────────────────

    public static function entityClassProvider(): array
    {
        return array_map(fn(string $c) => [$c], self::ENTITY_CLASSES);
    }

    public static function entityMethodProvider(): array
    {
        return [
            // User
            ['App\Entity\User', 'getEmail'],
            ['App\Entity\User', 'setEmail'],
            ['App\Entity\User', 'getFullName'],
            ['App\Entity\User', 'setFullName'],
            ['App\Entity\User', 'getRoles'],
            ['App\Entity\User', 'setRoles'],
            ['App\Entity\User', 'getPassword'],
            ['App\Entity\User', 'setPassword'],
            ['App\Entity\User', 'getCreatedAt'],
            ['App\Entity\User', 'getWallet'],
            ['App\Entity\User', 'getPortfolio'],
            ['App\Entity\User', 'getReputation'],
            ['App\Entity\User', 'getRiskProfile'],
            ['App\Entity\User', 'setRiskProfile'],
            ['App\Entity\User', 'hasRole'],

            // Wallet
            ['App\Entity\Wallet', 'getOwner'],
            ['App\Entity\Wallet', 'setOwner'],
            ['App\Entity\Wallet', 'getBalance'],
            ['App\Entity\Wallet', 'setBalance'],
            ['App\Entity\Wallet', 'getUser'],
            ['App\Entity\Wallet', 'setUser'],
            ['App\Entity\Wallet', 'getUsdtBalance'],
            ['App\Entity\Wallet', 'getEurBalance'],
            ['App\Entity\Wallet', 'getWalletGoals'],
            ['App\Entity\Wallet', 'getActivityLogs'],
            ['App\Entity\Wallet', 'getNotifications'],

            // Portfolio
            ['App\Entity\Portfolio', 'getUser'],
            ['App\Entity\Portfolio', 'setUser'],
            ['App\Entity\Portfolio', 'getTotalValue'],
            ['App\Entity\Portfolio', 'setTotalValue'],
            ['App\Entity\Portfolio', 'getPortfolioAssets'],
            ['App\Entity\Portfolio', 'addPortfolioAsset'],
            ['App\Entity\Portfolio', 'removePortfolioAsset'],
            ['App\Entity\Portfolio', 'recalculateTotalValue'],

            // Asset
            ['App\Entity\Asset', 'getName'],
            ['App\Entity\Asset', 'setName'],
            ['App\Entity\Asset', 'getSymbol'],
            ['App\Entity\Asset', 'setSymbol'],
            ['App\Entity\Asset', 'getValue'],
            ['App\Entity\Asset', 'setValue'],
            ['App\Entity\Asset', 'getType'],
            ['App\Entity\Asset', 'setType'],

            // Order
            ['App\Entity\Order', 'getAsset'],
            ['App\Entity\Order', 'setAsset'],
            ['App\Entity\Order', 'getUser'],
            ['App\Entity\Order', 'setUser'],
            ['App\Entity\Order', 'getQuantity'],
            ['App\Entity\Order', 'setQuantity'],
            ['App\Entity\Order', 'getPrice'],
            ['App\Entity\Order', 'setPrice'],
            ['App\Entity\Order', 'getType'],
            ['App\Entity\Order', 'setType'],

            // P2pContract
            ['App\Entity\P2pContract', 'getCreator'],
            ['App\Entity\P2pContract', 'setCreator'],
            ['App\Entity\P2pContract', 'getAsset'],
            ['App\Entity\P2pContract', 'setAsset'],
            ['App\Entity\P2pContract', 'getQuantity'],
            ['App\Entity\P2pContract', 'setQuantity'],
            ['App\Entity\P2pContract', 'getPricePerUnit'],
            ['App\Entity\P2pContract', 'setPricePerUnit'],
            ['App\Entity\P2pContract', 'getContractType'],
            ['App\Entity\P2pContract', 'setContractType'],
            ['App\Entity\P2pContract', 'getStatus'],
            ['App\Entity\P2pContract', 'setStatus'],
            ['App\Entity\P2pContract', 'getAcceptor'],
            ['App\Entity\P2pContract', 'setAcceptor'],

            // Transaction
            ['App\Entity\Transaction', 'getWallet'],
            ['App\Entity\Transaction', 'setWallet'],
            ['App\Entity\Transaction', 'getCategory'],
            ['App\Entity\Transaction', 'setCategory'],
            ['App\Entity\Transaction', 'getAmount'],
            ['App\Entity\Transaction', 'setAmount'],
            ['App\Entity\Transaction', 'getType'],
            ['App\Entity\Transaction', 'setType'],

            // Category
            ['App\Entity\Category', 'getName'],
            ['App\Entity\Category', 'setName'],
            ['App\Entity\Category', 'getIcon'],
            ['App\Entity\Category', 'setIcon'],
            ['App\Entity\Category', 'getColor'],
            ['App\Entity\Category', 'setColor'],
            ['App\Entity\Category', 'getTransactions'],

            // Notification
            ['App\Entity\Notification', 'getWallet'],
            ['App\Entity\Notification', 'setWallet'],
            ['App\Entity\Notification', 'getMessage'],
            ['App\Entity\Notification', 'setMessage'],
            ['App\Entity\Notification', 'getType'],
            ['App\Entity\Notification', 'setType'],
            ['App\Entity\Notification', 'isRead'],
            ['App\Entity\Notification', 'setRead'],

            // ActivityLog
            ['App\Entity\ActivityLog', 'getWallet'],
            ['App\Entity\ActivityLog', 'setWallet'],
            ['App\Entity\ActivityLog', 'getMessage'],
            ['App\Entity\ActivityLog', 'setMessage'],
            ['App\Entity\ActivityLog', 'getType'],
            ['App\Entity\ActivityLog', 'setType'],

            // UserReputation
            ['App\Entity\UserReputation', 'getUser'],
            ['App\Entity\UserReputation', 'setUser'],
            ['App\Entity\UserReputation', 'getCompletedContracts'],
            ['App\Entity\UserReputation', 'setCompletedContracts'],
            ['App\Entity\UserReputation', 'getCanceledContracts'],
            ['App\Entity\UserReputation', 'setCanceledContracts'],
            ['App\Entity\UserReputation', 'getTotalScore'],
            ['App\Entity\UserReputation', 'setTotalScore'],
            ['App\Entity\UserReputation', 'getRatingCount'],
            ['App\Entity\UserReputation', 'setRatingCount'],
            ['App\Entity\UserReputation', 'getAverageRating'],

            // WalletGoal
            ['App\Entity\WalletGoal', 'getWallet'],
            ['App\Entity\WalletGoal', 'setWallet'],
            ['App\Entity\WalletGoal', 'getName'],
            ['App\Entity\WalletGoal', 'setName'],
            ['App\Entity\WalletGoal', 'getTargetAmount'],
            ['App\Entity\WalletGoal', 'setTargetAmount'],
            ['App\Entity\WalletGoal', 'getDeadline'],
            ['App\Entity\WalletGoal', 'setDeadline'],
            ['App\Entity\WalletGoal', 'getStatus'],
            ['App\Entity\WalletGoal', 'setStatus'],
            ['App\Entity\WalletGoal', 'getProgression'],
            ['App\Entity\WalletGoal', 'isUrgent'],

            // PortfolioAsset
            ['App\Entity\PortfolioAsset', 'getPortfolio'],
            ['App\Entity\PortfolioAsset', 'setPortfolio'],
            ['App\Entity\PortfolioAsset', 'getAsset'],
            ['App\Entity\PortfolioAsset', 'setAsset'],
            ['App\Entity\PortfolioAsset', 'getQuantity'],
            ['App\Entity\PortfolioAsset', 'setQuantity'],
            ['App\Entity\PortfolioAsset', 'getAvgPrice'],
            ['App\Entity\PortfolioAsset', 'setAvgPrice'],
        ];
    }

    public static function entitySetterProvider(): array
    {
        return [
            ['App\Entity\User', 'setEmail'],
            ['App\Entity\User', 'setFullName'],
            ['App\Entity\User', 'setRoles'],
            ['App\Entity\User', 'setPassword'],
            ['App\Entity\User', 'setRiskProfile'],
            ['App\Entity\Wallet', 'setOwner'],
            ['App\Entity\Wallet', 'setBalance'],
            ['App\Entity\Wallet', 'setUser'],
            ['App\Entity\Portfolio', 'setUser'],
            ['App\Entity\Portfolio', 'setTotalValue'],
            ['App\Entity\Asset', 'setName'],
            ['App\Entity\Asset', 'setSymbol'],
            ['App\Entity\Asset', 'setValue'],
            ['App\Entity\Asset', 'setType'],
            ['App\Entity\Order', 'setAsset'],
            ['App\Entity\Order', 'setUser'],
            ['App\Entity\Order', 'setQuantity'],
            ['App\Entity\Order', 'setPrice'],
            ['App\Entity\Order', 'setType'],
            ['App\Entity\P2pContract', 'setCreator'],
            ['App\Entity\P2pContract', 'setAsset'],
            ['App\Entity\P2pContract', 'setQuantity'],
            ['App\Entity\P2pContract', 'setPricePerUnit'],
            ['App\Entity\P2pContract', 'setContractType'],
            ['App\Entity\P2pContract', 'setStatus'],
            ['App\Entity\Transaction', 'setWallet'],
            ['App\Entity\Transaction', 'setCategory'],
            ['App\Entity\Transaction', 'setAmount'],
            ['App\Entity\Transaction', 'setType'],
            ['App\Entity\Category', 'setName'],
            ['App\Entity\Notification', 'setWallet'],
            ['App\Entity\Notification', 'setMessage'],
            ['App\Entity\Notification', 'setType'],
            ['App\Entity\Notification', 'setRead'],
            ['App\Entity\ActivityLog', 'setWallet'],
            ['App\Entity\ActivityLog', 'setMessage'],
            ['App\Entity\ActivityLog', 'setType'],
            ['App\Entity\UserReputation', 'setUser'],
            ['App\Entity\UserReputation', 'setCompletedContracts'],
            ['App\Entity\UserReputation', 'setCanceledContracts'],
            ['App\Entity\WalletGoal', 'setWallet'],
            ['App\Entity\WalletGoal', 'setName'],
            ['App\Entity\WalletGoal', 'setTargetAmount'],
            ['App\Entity\WalletGoal', 'setDeadline'],
            ['App\Entity\WalletGoal', 'setStatus'],
            ['App\Entity\PortfolioAsset', 'setPortfolio'],
            ['App\Entity\PortfolioAsset', 'setAsset'],
            ['App\Entity\PortfolioAsset', 'setQuantity'],
            ['App\Entity\PortfolioAsset', 'setAvgPrice'],
        ];
    }
}
