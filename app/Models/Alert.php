<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model {
    protected $fillable = ['title', 'message', 'role_target', 'created_by'];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}