<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['persona_id', 'user_message', 'bot_response', 'gif_url', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
