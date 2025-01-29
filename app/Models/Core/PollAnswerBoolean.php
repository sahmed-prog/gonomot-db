<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class PollAnswerBoolean extends Model
{
    protected $table = 'core_poll_answers_boolean';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'answer_id',
        'value',
    ];
    protected $guarded = [
        'id',
    ];

    public function pollAnswer()
    {
        return $this->belongsTo(PollAnswer::class, 'answer_id', 'id');
    }
}
