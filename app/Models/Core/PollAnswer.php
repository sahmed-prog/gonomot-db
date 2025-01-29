<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    // Table associated with the model
    protected $table = 'core_poll_answers';

    // Primary key column
    protected $primaryKey = 'id';

    // Data type for primary key (if it's not an incrementing integer)
    protected $keyType = 'int';

    // Whether the primary key is auto-incrementing
    public $incrementing = true;

    // Timestamps to be managed by Eloquent
    public $timestamps = true;

    // Columns that are mass assignable
    protected $fillable = [
        'question_id',
        'respondent_id',
        'user_id',
        'answer_type_id',
        'created_at',
        'updated_at',
    ];

    // Columns that should not be mass assignable
    protected $guarded = [
        'id',
    ];

    // Specify default values for columns
    protected $attributes = [
        'created_at' => 'CURRENT_TIMESTAMP',
        'updated_at' => 'CURRENT_TIMESTAMP',
    ];

    // Foreign key relationships
    public function question()
    {
        return $this->belongsTo(PollQuestion::class, 'question_id', 'id');
    }

    public function respondent()
    {
        return $this->belongsTo(Respondent::class, 'respondent_id', 'id');
    }

    public function answerType()
    {
        return $this->belongsTo(AnswerType::class, 'answer_type_id', 'id');
    }

    // Define the date format for timestamps (if you need custom format)
    protected $dateFormat = 'Y-m-d H:i:s';
}
