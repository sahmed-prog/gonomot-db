<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\CoreCampaign;

class Poll extends Model
{
    protected $table = 'core_polls';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'campaign_id',
        'title',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $guarded = [
        'id',
    ];

    protected $attributes = [
        'created_at' => 'CURRENT_TIMESTAMP',
        'updated_at' => 'CURRENT_TIMESTAMP',
    ];

    public function campaign()
    {
        return $this->belongsTo(CoreCampaign::class, 'campaign_id', 'id');
    }

    protected $dateFormat = 'Y-m-d H:i:s';
}
