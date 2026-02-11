<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'system_prompt', 'description', 'avatar_url'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
