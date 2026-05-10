<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'dialog_id',
        'messenger_id',
        'request_text',
        'response_text',
    ];

    public function dialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class);
    }

    public function messenger(): BelongsTo
    {
        return $this->belongsTo(Messenger::class);
    }
}
