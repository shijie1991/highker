<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ChatParticipation.
 *
 * @property int id
 * @property int conversation_id
 * @property int messageable_id
 * @property string messageable_type
 * @property string settings
 * @property string created_at
 * @property string updated_at
 * @property ChatMessage messageable
 */
class ChatParticipation extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function messageable(): MorphTo
    {
        return $this->morphTo()->with('participation');
    }
}
