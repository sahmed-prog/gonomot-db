<?php

namespace App\Models\Surveys;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimarySurveyEmbedding extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'primary_survey_embeddings';

    // The primary key associated with the table
    protected $primaryKey = 'id';

    // Define the fillable attributes
    protected $fillable = [
        'user_id',
        'government_embeddings',
        'pm_candidate_embeddings',
        'cm_1_embeddings',
        'cm_2_embeddings',
        'cm_3_embeddings',
        'cm_4_embeddings',
        'cm_5_embeddings',
        'surveyor_leadership_embeddings',
    ];

    // Timestamps are maintained automatically by Eloquent
    public $timestamps = true;

    // Define the relationship with the PrimarySurvey model
    public function primarySurvey()
    {
        return $this->belongsTo(PrimarySurvey::class, 'user_id', 'user_id');
    }
}
