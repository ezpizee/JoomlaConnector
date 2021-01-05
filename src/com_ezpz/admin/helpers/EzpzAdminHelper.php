<?php

use Joomla\CMS\Factory;

class EzpzAdminHelper
{
    public static function assetRoot(): string {
        $arr = explode('/administrator/', dirname(__DIR__));
        return '/administrator/'.$arr[1].'/asset';
    }

    public static function assetFileRoot(): string {
        $arr = explode('/administrator/', dirname(__DIR__));
        return $arr[0].'/administrator/'.$arr[1].'/asset';
    }

    public static function loadConfigData(): array {
        $sql = 'SELECT *'.' FROM '.Constants::DB_TB_EZPZ.
            ' WHERE config_key IN(\''.implode("','", Constants::API_CONFIG_KEYS).'\')';
        $dbo = Factory::getDbo();
        $rows = $dbo->setQuery($sql)->loadAssocList();
        if (empty($rows)) {
            return [];
        }
        else {
            $data = [];
            foreach ($rows as $val) {
                $data[$val['config_key']] = $val['config_value'];
            }
            return $data;
        }
    }
}