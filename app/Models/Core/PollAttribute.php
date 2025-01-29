<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class PollAttribute extends Model
{
    protected $table = 'core_poll_attributes';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'poll_id',
        'attribute_name',
        'attribute_value',
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

    public function poll()
    {
        return $this->belongsTo(Poll::class, 'poll_id', 'id');
    }

    protected $dateFormat = 'Y-m-d H:i:s';
}
