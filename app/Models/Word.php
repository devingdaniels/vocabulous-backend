<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'definition',
        'word_type',
        'example_sentence',
        'phonetic_spelling',
        'is_irregular',
        'past_participle',
        'conjugations',
    ];

    protected $casts = [
        'is_irregular' => 'boolean',
        'conjugations' => 'array',
    ];

    public function decks()
    {
        return $this->belongsToMany(Deck::class);
    }
}
