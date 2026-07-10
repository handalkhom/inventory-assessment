<?php

namespace App\Enums;

enum ProductCategory: string
{
    case RAW_MATERIAL = 'raw_material';
    case FINISHED_GOODS = 'finished_goods';
    case PACKAGING = 'packaging';
    case SPARE_PART = 'spare_part';
}
