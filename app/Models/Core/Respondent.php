<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Respondent extends Model
{
    // Table associated with the model
    protected $table = 'core_respondents';

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
        'name',
        'email',
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

    // Define relationships if any
    // For example, a respondent can have many poll answers:
    public function pollAnswers()
    {
        return $this->hasMany(PollAnswer::class, 'respondent_id', 'id');
    }

    // Define the date format for timestamps (if you need custom format)
    protected $dateFormat = 'Y-m-d H:i:s';
}
