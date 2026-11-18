<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // the table has created_at only (no updated_at)
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'order_id', 'status', 'message', 'is_read'];
}
