<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vasilaki\DialogTools\Contracts\MessagePreviewer;

class RequestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'dialog_id',
        'messenger_id',
        'request_text',
        'response_text',
    ];

    /**
     * Short, word-aware preview of the request text built by the
     * vasilaki/dialog-tools package (contract resolved from the container).
     */
    protected function requestPreview(): Attribute
    {
        return Attribute::make(
            get: fn (): string => app(MessagePreviewer::class)
                ->preview((string) $this->request_text, 90),
        );
    }

    public function dialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class);
    }

    public function messenger(): BelongsTo
    {
        return $this->belongsTo(Messenger::class);
    }
}
