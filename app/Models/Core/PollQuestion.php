<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\CoreQuestionType;

class PollQuestion extends Model
{
    protected $table = 'core_poll_questions';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'poll_id',
        'question_text',
        'question_type_id',
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

    public function questionType()
    {
        return $this->belongsTo(CoreQuestionType::class, 'question_type_id', 'id');
    }

    protected $dateFormat = 'Y-m-d H:i:s';
}
