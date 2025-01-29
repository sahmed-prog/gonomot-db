<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class AnswerType extends Model
{
    protected $table = 'core_answer_types';
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'type_name',
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

    public function pollAnswers()
    {
        return $this->hasMany(PollAnswer::class, 'answer_type_id', 'id');
    }

    protected $dateFormat = 'Y-m-d H:i:s';
}
