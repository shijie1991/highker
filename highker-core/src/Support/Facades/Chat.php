<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support\Facades;

use HighKer\Core\Models\ChatConversation;
use HighKer\Core\Services\ChatConversationService;
use HighKer\Core\Services\ChatMessageService;
use Illuminate\Support\Facades\Facade;

/**
 * Class ChatFacade.
 *
 * @method static ChatConversationService conversation($conversation)
 * @method static ChatConversationService conversations()
 * @method static Chat                    makePrivate()
 * @method static ChatConversation        createConversation($sender, $receiver, array $data = null)
 * @method static ChatMessageService      message($message)
 * @method static ChatMessageService      messages()
 */
class Chat extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @codeCoverageIgnore
     */
    protected static function getFacadeAccessor(): string
    {
        return \HighKer\Core\Support\Chat::class;
    }
}
