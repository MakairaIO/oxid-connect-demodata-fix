<?php

namespace Makaira\OxidConnect\DemoDataFix\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnect\DemoDataFix\Modifier\VariantModifierInterface;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\TableTranslator;

class InheritManufacturer extends Modifier implements VariantModifierInterface
{
    private static ?array $manufacturerIdMap = null;

    public function __construct(
        private Connection $connection,
        private TableTranslator $tableTranslator,
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
                $query  = "SELECT DISTINCT a.OXID, m.OXID AS id, m.OXTITLE AS title
                        FROM oxarticles a
                        LEFT JOIN oxmanufacturers m ON m.OXID = a.OXMANUFACTURERID
                        WHERE a.OXPARENTID = '' AND a.OXACTIVE = 1";
                $result = $this->connection->executeQuery($this->tableTranslator->translate($query));

                self::$manufacturerIdMap = $result->fetchAllAssociativeIndexed();
            }

            if (empty($type->manufacturerid)) {
                $manufacturer = self::$manufacturerIdMap[$type->parent] ?? null;
                if (null !== $manufacturer) {
                    $type->manufacturerid     = self::$manufacturerIdMap[$type->parent]['id'];
                    $type->manufacturer_title = self::$manufacturerIdMap[$type->parent]['title'];
                }
            }
        }

        return $type;
    }
}
