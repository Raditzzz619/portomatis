<?php

namespace App\Support;

class DomicileRegions
{
    public static function provinces(): array
    {
        return json_decode(file_get_contents(resource_path('data/indonesia-domicile.json')), true, 512, JSON_THROW_ON_ERROR)['provinces'];
    }

    public static function label(array $province, array $regency): string
    {
        return $regency['name'].', '.$province['name'].', Indonesia';
    }
}
