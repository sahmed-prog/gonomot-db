<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class CoreCampaign extends Model
{
    protected $table = 'core_campaigns';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'description',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $casts = [
        'id' => 'integer',
    ];
}
