<?php

namespace App\Tests\Doctrine;

use PHPUnit\Framework\TestCase;

/**
 * Tests Doctrine : vérifie les détails du schéma ORM sur chaque entité
 * (longueurs de colonnes, types de données, valeurs par défaut, nullable).
 */
class EntitySchemaDefinitionTest extends TestCase
{
    // ══════════════════════════════════════════════
    //  1. Column lengths
    // ══════════════════════════════════════════════

    /**
     * @dataProvider columnLengthProvider
     */
    public function testColumnLength(string $class, string $property, int $expectedLength): void
    {
        $prop = $this->getProperty($class, $property);
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs, sprintf('%s::$%s must have #[ORM\\Column].', $class, $property));

        $args = $attrs[0]->getArguments();
        $this->assertSame(
            $expectedLength,
            $args['length'] ?? null,
            sprintf('%s::$%s column length should be %d.', $class, $property, $expectedLength)
        );
    }

    // ══════════════════════════════════════════════
    //  2. Column types (Types::DATETIME_IMMUTABLE, etc.)
    // ══════════════════════════════════════════════

    /**
     * @dataProvider columnTypeProvider
     */
    public function testColumnType(string $class, string $property, string $expectedType): void
    {
        $prop = $this->getProperty($class, $property);
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        // Le type peut être un FQCN de Types:: ou une string directe
        $actualType = $args['type'] ?? null;

        $this->assertNotNull($actualType, sprintf('%s::$%s must specify a column type.', $class, $property));
        $this->assertStringContainsString(
            $expectedType,
            (string)$actualType,
            sprintf('%s::$%s type should contain "%s".', $class, $property, $expectedType)
        );
    }

    // ══════════════════════════════════════════════
    //  3. Nullable columns
    // ══════════════════════════════════════════════

    /**
     * @dataProvider nullableColumnProvider
     */
    public function testColumnIsNullable(string $class, string $property): void
    {
        $prop = $this->getProperty($class, $property);
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertTrue(
            $args['nullable'] ?? false,
            sprintf('%s::$%s should be nullable.', $class, $property)
        );
    }

    // ══════════════════════════════════════════════
    //  4. Non-nullable required columns
    // ══════════════════════════════════════════════

    /**
     * @dataProvider requiredColumnProvider
     */
    public function testColumnIsNotNullable(string $class, string $property): void
    {
        $prop = $this->getProperty($class, $property);
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        // nullable defaults to false in Doctrine, so it should either be absent or explicitly false
        $this->assertFalse(
            $args['nullable'] ?? false,
            sprintf('%s::$%s should NOT be nullable.', $class, $property)
        );
    }

    // ══════════════════════════════════════════════
    //  5. Decimal precision & scale
    // ══════════════════════════════════════════════

    /**
     * @dataProvider decimalColumnProvider
     */
    public function testDecimalPrecisionAndScale(string $class, string $property, int $precision, int $scale): void
    {
        $prop = $this->getProperty($class, $property);
        $attrs = $prop->getAttributes('Doctrine\ORM\Mapping\Column');
        $this->assertNotEmpty($attrs);

        $args = $attrs[0]->getArguments();
        $this->assertSame($precision, $args['precision'] ?? null,
            sprintf('%s::$%s precision should be %d.', $class, $property, $precision));
        $this->assertSame($scale, $args['scale'] ?? null,
            sprintf('%s::$%s scale should be %d.', $class, $property, $scale));
    }

    // ══════════════════════════════════════════════
    //  6. Entity property count (structural sanity)
    // ══════════════════════════════════════════════

    /**
     * @dataProvider entityPropertyCountProvider
     */
    public function testEntityHasExpectedPropertyCount(string $class, int $minProps): void
    {
        $reflection = new \ReflectionClass($class);
        $props = $reflection->getProperties();

        $this->assertGreaterThanOrEqual(
            $minProps,
            count($props),
            sprintf('%s should have at least %d properties.', $class, $minProps)
        );
    }

    // ══════════════════════════════════════════════
    //  7. PHP type hints on properties
    // ══════════════════════════════════════════════

    /**
     * @dataProvider propertyTypeHintProvider
     */
    public function testPropertyHasTypeHint(string $class, string $property, string $expectedType): void
    {
        $prop = $this->getProperty($class, $property);
        $type = $prop->getType();

        $this->assertNotNull($type, sprintf('%s::$%s must have a type hint.', $class, $property));

        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : (string)$type;
        $this->assertSame(
            $expectedType,
            $typeName,
            sprintf('%s::$%s type should be %s, got %s.', $class, $property, $expectedType, $typeName)
        );
    }

    // ══════════════════════════════════════════════
    //  8. ID properties are nullable int
    // ══════════════════════════════════════════════

    /**
     * @dataProvider entityClassProvider
     */
    public function testIdPropertyIsNullableInt(string $class): void
    {
        $prop = $this->getProperty($class, 'id');
        $type = $prop->getType();

        $this->assertNotNull($type);
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertSame('int', $type->getName());
        $this->assertTrue($type->allowsNull(), sprintf('%s::$id must be nullable (?int).', $class));
    }

    // ══════════════════════════════════════════════
    //  Data Providers
    // ══════════════════════════════════════════════

    public static function columnLengthProvider(): array
    {
        return [
            // User
            ['App\Entity\User', 'email', 180],
            ['App\Entity\User', 'fullName', 100],
            ['App\Entity\User', 'riskProfile', 20],

            // Wallet
            ['App\Entity\Wallet', 'owner', 100],

            // Asset
            ['App\Entity\Asset', 'name', 255],
            ['App\Entity\Asset', 'symbol', 50],
            ['App\Entity\Asset', 'type', 100],

            // Order
            ['App\Entity\Order', 'type', 20],

            // P2pContract
            ['App\Entity\P2pContract', 'contractType', 20],
            ['App\Entity\P2pContract', 'status', 20],

            // Transaction
            ['App\Entity\Transaction', 'type', 10],

            // Notification
            ['App\Entity\Notification', 'message', 255],
            ['App\Entity\Notification', 'type', 20],

            // ActivityLog
            ['App\Entity\ActivityLog', 'type', 20],
            ['App\Entity\ActivityLog', 'message', 255],

            // Category
            ['App\Entity\Category', 'name', 100],
            ['App\Entity\Category', 'icon', 50],
            ['App\Entity\Category', 'color', 20],

            // WalletGoal
            ['App\Entity\WalletGoal', 'name', 100],
            ['App\Entity\WalletGoal', 'status', 20],
        ];
    }

    public static function columnTypeProvider(): array
    {
        return [
            ['App\Entity\User',        'createdAt',   'datetime_immutable'],
            ['App\Entity\Wallet',      'balance',     'decimal'],
            ['App\Entity\Wallet',      'createdAt',   'datetime_immutable'],
            ['App\Entity\Transaction', 'amount',      'decimal'],
            ['App\Entity\Transaction', 'createdAt',   'datetime_immutable'],
            ['App\Entity\P2pContract', 'createdAt',   'datetime'],
            ['App\Entity\P2pContract', 'acceptedAt',  'datetime'],
            ['App\Entity\P2pContract', 'completedAt', 'datetime'],
            ['App\Entity\WalletGoal',  'targetAmount','decimal'],
            ['App\Entity\WalletGoal',  'deadline',    'date_immutable'],
        ];
    }

    public static function nullableColumnProvider(): array
    {
        return [
            ['App\Entity\Category', 'icon'],
            ['App\Entity\Category', 'color'],
            ['App\Entity\P2pContract', 'acceptedAt'],
            ['App\Entity\P2pContract', 'completedAt'],
        ];
    }

    public static function requiredColumnProvider(): array
    {
        return [
            ['App\Entity\User', 'email'],
            ['App\Entity\User', 'fullName'],
            ['App\Entity\User', 'password'],
            ['App\Entity\Wallet', 'owner'],
            ['App\Entity\Asset', 'name'],
            ['App\Entity\Asset', 'symbol'],
            ['App\Entity\Order', 'type'],
            ['App\Entity\P2pContract', 'contractType'],
            ['App\Entity\P2pContract', 'status'],
            ['App\Entity\Notification', 'message'],
            ['App\Entity\ActivityLog', 'message'],
            ['App\Entity\WalletGoal', 'name'],
        ];
    }

    public static function decimalColumnProvider(): array
    {
        return [
            ['App\Entity\Wallet',      'balance',      15, 2],
            ['App\Entity\Transaction', 'amount',       15, 2],
            ['App\Entity\WalletGoal',  'targetAmount', 15, 2],
        ];
    }

    public static function entityPropertyCountProvider(): array
    {
        return [
            ['App\Entity\User',           7],
            ['App\Entity\Wallet',         7],
            ['App\Entity\Portfolio',      3],
            ['App\Entity\Asset',          4],
            ['App\Entity\Order',          5],
            ['App\Entity\P2pContract',    9],
            ['App\Entity\Transaction',    5],
            ['App\Entity\Category',       4],
            ['App\Entity\Notification',   5],
            ['App\Entity\ActivityLog',    4],
            ['App\Entity\UserReputation', 5],
            ['App\Entity\WalletGoal',     6],
            ['App\Entity\PortfolioAsset', 4],
        ];
    }

    public static function propertyTypeHintProvider(): array
    {
        return [
            ['App\Entity\User', 'email', 'string'],
            ['App\Entity\User', 'fullName', 'string'],
            ['App\Entity\User', 'roles', 'array'],
            ['App\Entity\User', 'password', 'string'],
            ['App\Entity\Wallet', 'owner', 'string'],
            ['App\Entity\Wallet', 'balance', 'string'],
            ['App\Entity\Asset', 'name', 'string'],
            ['App\Entity\Asset', 'symbol', 'string'],
            ['App\Entity\Asset', 'value', 'float'],
            ['App\Entity\Order', 'quantity', 'int'],
            ['App\Entity\Order', 'price', 'float'],
            ['App\Entity\P2pContract', 'quantity', 'int'],
            ['App\Entity\P2pContract', 'pricePerUnit', 'float'],
            ['App\Entity\UserReputation', 'completedContracts', 'int'],
            ['App\Entity\UserReputation', 'canceledContracts', 'int'],
            ['App\Entity\Notification', 'isRead', 'bool'],
        ];
    }

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
