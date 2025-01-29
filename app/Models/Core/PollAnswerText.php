<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class PollAnswerText extends Model
{
    protected $table = 'core_poll_answers_text';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

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
