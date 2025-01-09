<?php

namespace App\Models\Surveys;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimarySurvey extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'primary_survey_details';

    // The primary key associated with the table
    protected $primaryKey = 'id';

    // If you do not want the timestamps to be automatically maintained by Eloquent
    public $timestamps = false;

    // Define the fillable attributes
    protected $fillable = [
        'user_id',
        'government',
        'cm_1',
        'cm_2',
        'cm_3',
        'cm_4',
        'cm_5',
        'pm_candidate',
        'surveyor_electoral_area',
        'surveyor_administrative',
        'surveyor_occupation',
        'surveyor_leadership',
        'referral_code',
        'survey_date',
        'ingested_at',
    ];

    // Optionally, define the date format for the model
    protected $dateFormat = 'Y-m-d H:i:s';

    // Cast attributes to specific types
    protected $casts = [
        'survey_date' => 'datetime',
        'ingested_at' => 'datetime',
    ];
}
