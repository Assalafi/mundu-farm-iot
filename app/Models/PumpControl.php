<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PumpControl extends Model
{
    protected $fillable = [
        'action',
        'triggered_by',
        'triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
        ];
    }

    public static function currentState(): bool
    {
        $last = static::latest('triggered_at')->first();
        if (!$last) {
            return false;
        }
        return $last->action === 'on';
    }

    public static function history(int $limit = 50)
    {
        return static::latest('triggered_at')->limit($limit)->get();
    }
}
