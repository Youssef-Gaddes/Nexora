<?php

namespace App\Tests\Static;

use PHPUnit\Framework\TestCase;

/**
 * Tests statiques : vérifie la structure des classes Service et Controller
 * (existence, namespace, méthodes publiques, dépendances constructeur).
 */
class ServiceControllerStructureTest extends TestCase
{
    // ──────────────────────────────────────────────
    //  Services
    // ──────────────────────────────────────────────

    private const SERVICE_CLASSES = [
        'App\Service\AiService',
        'App\Service\AssetPriceService',
        'App\Service\CurrencyService',
        'App\Service\PdfService',
        'App\Service\PortfolioReportService',
        'App\Service\ReputationService',
        'App\Service\SentimentAiService',
    ];

    private const CONTROLLER_CLASSES = [
        'App\Controller\AssetController',
        'App\Controller\DashboardController',
        'App\Controller\HomeController',
        'App\Controller\NotificationController',
        'App\Controller\OrderController',
        'App\Controller\P2pContractController',
        'App\Controller\PortfolioController',
        'App\Controller\UserController',
        'App\Controller\UserReputationController',
        'App\Controller\WalletController',
        'App\Controller\WalletGoalController',
    ];

    // ──────────────────────────────────────────────
    //  1. Existence & Namespace — Services
    // ──────────────────────────────────────────────

    /**
     * @dataProvider serviceClassProvider
     */
    public function testServiceClassExists(string $className): void
    {
        $this->assertTrue(
            class_exists($className),
            sprintf('Service class "%s" does not exist.', $className)
        );
    }

    /**
     * @dataProvider serviceClassProvider
     */
    public function testServiceBelongsToCorrectNamespace(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertStringStartsWith(
            'App\Service',
            $reflection->getNamespaceName()
        );
    }

    // ──────────────────────────────────────────────
    //  2. Existence & Namespace — Controllers
    // ──────────────────────────────────────────────

    /**
     * @dataProvider controllerClassProvider
     */
    public function testControllerClassExists(string $className): void
    {
        $this->assertTrue(
            class_exists($className),
            sprintf('Controller class "%s" does not exist.', $className)
        );
    }

    /**
     * @dataProvider controllerClassProvider
     */
    public function testControllerBelongsToCorrectNamespace(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertStringStartsWith(
            'App\Controller',
            $reflection->getNamespaceName()
        );
    }

    /**
     * @dataProvider controllerClassProvider
     */
    public function testControllerExtendsAbstractController(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertTrue(
            $reflection->isSubclassOf('Symfony\Bundle\FrameworkBundle\Controller\AbstractController'),
            sprintf('Controller "%s" must extend AbstractController.', $className)
        );
    }

    // ──────────────────────────────────────────────
    //  3. Service public methods
    // ──────────────────────────────────────────────

    /**
     * @dataProvider serviceMethodProvider
     */
    public function testServiceHasExpectedPublicMethod(string $className, string $method): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertTrue(
            $reflection->hasMethod($method),
            sprintf('Service "%s" must have method "%s".', $className, $method)
        );
        $this->assertTrue(
            $reflection->getMethod($method)->isPublic(),
            sprintf('%s::%s must be public.', $className, $method)
        );
    }

    // ──────────────────────────────────────────────
    //  4. Services are not abstract
    // ──────────────────────────────────────────────

    /**
     * @dataProvider serviceClassProvider
     */
    public function testServiceIsInstantiable(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertFalse(
            $reflection->isAbstract(),
            sprintf('Service "%s" must not be abstract.', $className)
        );
    }

    // ──────────────────────────────────────────────
    //  5. Controllers are not abstract
    // ──────────────────────────────────────────────

    /**
     * @dataProvider controllerClassProvider
     */
    public function testControllerIsInstantiable(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertFalse(
            $reflection->isAbstract(),
            sprintf('Controller "%s" must not be abstract.', $className)
        );
    }

    // ──────────────────────────────────────────────
    //  6. PdfService generates output
    // ──────────────────────────────────────────────

    public function testPdfServiceGeneratePdfReturnsString(): void
    {
        $reflection = new \ReflectionClass('App\Service\PdfService');
        $method = $reflection->getMethod('generatePdf');

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertSame('string', $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string)$returnType);
    }

    // ──────────────────────────────────────────────
    //  7. ReputationService returns array
    // ──────────────────────────────────────────────

    public function testReputationServiceReturnsArray(): void
    {
        $reflection = new \ReflectionClass('App\Service\ReputationService');
        $method = $reflection->getMethod('getReputationStats');

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertSame('array', $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string)$returnType);
    }

    // ──────────────────────────────────────────────
    //  Data Providers
    // ──────────────────────────────────────────────

    public static function serviceClassProvider(): array
    {
        return array_map(fn(string $c) => [$c], self::SERVICE_CLASSES);
    }

    public static function controllerClassProvider(): array
    {
        return array_map(fn(string $c) => [$c], self::CONTROLLER_CLASSES);
    }

    public static function serviceMethodProvider(): array
    {
        return [
            ['App\Service\PdfService', 'generatePdf'],
            ['App\Service\ReputationService', 'getReputationStats'],
            ['App\Service\PortfolioReportService', 'generatePortfolioPdf'],
        ];
    }
}
