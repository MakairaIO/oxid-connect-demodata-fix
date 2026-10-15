<?php

namespace Makaira\OxidConnect\DemoDataFix\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnect\DemoDataFix\Modifier\VariantModifierInterface;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

class InheritManufacturer extends Modifier implements VariantModifierInterface
{
    private static array $manufacturerIdMap = [];

    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws DriverException
     * @throws DBALException
     */
    public function apply(Type $type): Type\Variant\Variant|Type
    {
        if ($type instanceof Type\Variant\Variant) {
            if (null === static::$manufacturerIdMap) {
                $result = $this->connection->executeQuery(
                    'SELECT OXID, OXMANUFACTURERID FROM oxarticles',
                );

                self::$manufacturerIdMap = $result->fetchAllKeyValue();
            }

            if (empty($type->manufacturerid) && isset(self::$manufacturerIdMap[$type->id])) {
                $type->manufacturerid = self::$manufacturerIdMap[$type->id];
            }
        }

        return $type;
    }
}
