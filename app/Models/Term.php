<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model {
    protected $fillable = ['name', 'order'];

    public function marks() {
        return $this->hasMany(Mark::class);
    }
}