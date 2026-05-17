<?php

namespace App\Tests\Static;

use PHPUnit\Framework\TestCase;

/**
 * Tests statiques : vérifie la structure des classes Repository
 * (existence, héritage ServiceEntityRepository, méthodes custom).
 */
class RepositoryClassStructureTest extends TestCase
{
    /**
     * Map chaque Repository à son Entity associée.
     */
    private const REPO_ENTITY_MAP = [
        'App\Repository\UserRepository'            => 'App\Entity\User',
        'App\Repository\WalletRepository'          => 'App\Entity\Wallet',
        'App\Repository\PortfolioRepository'       => 'App\Entity\Portfolio',
        'App\Repository\PortfolioAssetRepository'  => 'App\Entity\PortfolioAsset',
        'App\Repository\AssetRepository'           => 'App\Entity\Asset',
        'App\Repository\OrderRepository'           => 'App\Entity\Order',
        'App\Repository\P2pContractRepository'     => 'App\Entity\P2pContract',
        'App\Repository\TransactionRepository'     => 'App\Entity\Transaction',
        'App\Repository\CategoryRepository'        => 'App\Entity\Category',
        'App\Repository\NotificationRepository'    => 'App\Entity\Notification',
        'App\Repository\ActivityLogRepository'     => 'App\Entity\ActivityLog',
        'App\Repository\UserReputationRepository'  => 'App\Entity\UserReputation',
        'App\Repository\WalletGoalRepository'      => 'App\Entity\WalletGoal',
    ];

    // ──────────────────────────────────────────────
    //  1. Existence
    // ──────────────────────────────────────────────

    /**
     * @dataProvider repositoryClassProvider
     */
    public function testRepositoryClassExists(string $repoClass): void
    {
        $this->assertTrue(
            class_exists($repoClass),
            sprintf('Repository class "%s" does not exist.', $repoClass)
        );
    }

    // ──────────────────────────────────────────────
    //  2. Namespace
    // ──────────────────────────────────────────────

    /**
     * @dataProvider repositoryClassProvider
     */
    public function testRepositoryBelongsToCorrectNamespace(string $repoClass): void
    {
        $reflection = new \ReflectionClass($repoClass);
        $this->assertStringStartsWith(
            'App\Repository',
            $reflection->getNamespaceName(),
            sprintf('Repository "%s" is not in App\\Repository namespace.', $repoClass)
        );
    }

    // ──────────────────────────────────────────────
    //  3. Héritage ServiceEntityRepository
    // ──────────────────────────────────────────────

    /**
     * @dataProvider repositoryClassProvider
     */
    public function testRepositoryExtendsServiceEntityRepository(string $repoClass): void
    {
        $reflection = new \ReflectionClass($repoClass);
        $this->assertTrue(
            $reflection->isSubclassOf('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository'),
            sprintf('Repository "%s" must extend ServiceEntityRepository.', $repoClass)
        );
    }

    // ──────────────────────────────────────────────
    //  4. Constructor signature
    // ──────────────────────────────────────────────

    /**
     * @dataProvider repositoryClassProvider
     */
    public function testRepositoryConstructorAcceptsManagerRegistry(string $repoClass): void
    {
        $reflection = new \ReflectionClass($repoClass);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, sprintf('%s must have a constructor.', $repoClass));

        $params = $constructor->getParameters();
        $this->assertGreaterThanOrEqual(1, count($params));

        $firstParamType = $params[0]->getType();
        $this->assertNotNull($firstParamType);
        $this->assertSame(
            'Doctrine\Persistence\ManagerRegistry',
            $firstParamType instanceof \ReflectionNamedType ? $firstParamType->getName() : (string)$firstParamType
        );
    }

    // ──────────────────────────────────────────────
    //  5. Méthodes custom connues
    // ──────────────────────────────────────────────

    /**
     * @dataProvider repositoryCustomMethodProvider
     */
    public function testRepositoryHasCustomMethod(string $repoClass, string $method): void
    {
        $reflection = new \ReflectionClass($repoClass);
        $this->assertTrue(
            $reflection->hasMethod($method),
            sprintf('Repository "%s" must have method "%s".', $repoClass, $method)
        );
    }

    // ──────────────────────────────────────────────
    //  6. Entity-Repository binding coherence
    // ──────────────────────────────────────────────

    /**
     * @dataProvider repositoryEntityProvider
     */
    public function testEntityReferencesRepository(string $repoClass, string $entityClass): void
    {
        $this->assertTrue(class_exists($entityClass), sprintf('Entity "%s" must exist.', $entityClass));

        // Vérifie que l'entité a un attribut ORM\Entity avec repositoryClass
        $reflection = new \ReflectionClass($entityClass);
        $attributes = $reflection->getAttributes('Doctrine\ORM\Mapping\Entity');

        $this->assertNotEmpty(
            $attributes,
            sprintf('Entity "%s" must have #[ORM\\Entity] attribute.', $entityClass)
        );

        $args = $attributes[0]->getArguments();
        $this->assertArrayHasKey('repositoryClass', $args,
            sprintf('Entity "%s" #[ORM\\Entity] must specify repositoryClass.', $entityClass)
        );
        $this->assertSame(
            $repoClass,
            $args['repositoryClass'],
            sprintf('Entity "%s" repositoryClass mismatch.', $entityClass)
        );
    }

    // ──────────────────────────────────────────────
    //  Data Providers
    // ──────────────────────────────────────────────

    public static function repositoryClassProvider(): array
    {
        return array_map(fn(string $r) => [$r], array_keys(self::REPO_ENTITY_MAP));
    }

    public static function repositoryEntityProvider(): array
    {
        $cases = [];
        foreach (self::REPO_ENTITY_MAP as $repo => $entity) {
            $cases[$repo] = [$repo, $entity];
        }
        return $cases;
    }

    public static function repositoryCustomMethodProvider(): array
    {
        return [
            // AssetRepository
            ['App\Repository\AssetRepository', 'save'],
            ['App\Repository\AssetRepository', 'remove'],

            // OrderRepository
            ['App\Repository\OrderRepository', 'save'],
            ['App\Repository\OrderRepository', 'remove'],

            // PortfolioRepository
            ['App\Repository\PortfolioRepository', 'save'],
            ['App\Repository\PortfolioRepository', 'remove'],

            // PortfolioAssetRepository
            ['App\Repository\PortfolioAssetRepository', 'save'],
            ['App\Repository\PortfolioAssetRepository', 'remove'],

            // P2pContractRepository
            ['App\Repository\P2pContractRepository', 'save'],
            ['App\Repository\P2pContractRepository', 'remove'],

            // UserReputationRepository
            ['App\Repository\UserReputationRepository', 'save'],
            ['App\Repository\UserReputationRepository', 'remove'],

            // TransactionRepository (custom queries)
            ['App\Repository\TransactionRepository', 'getStatsByCategory'],
            ['App\Repository\TransactionRepository', 'getPerformanceHistory'],

            // NotificationRepository (custom queries)
            ['App\Repository\NotificationRepository', 'findUnreadByWallet'],
            ['App\Repository\NotificationRepository', 'findLatestByWallet'],

            // ActivityLogRepository
            ['App\Repository\ActivityLogRepository', 'findRecent'],

            // WalletRepository
            ['App\Repository\WalletRepository', 'findAllOrdered'],

            // WalletGoalRepository
            ['App\Repository\WalletGoalRepository', 'findAllOrdered'],
            ['App\Repository\WalletGoalRepository', 'findByWallet'],

            // UserRepository
            ['App\Repository\UserRepository', 'findOneByEmail'],
        ];
    }
}
