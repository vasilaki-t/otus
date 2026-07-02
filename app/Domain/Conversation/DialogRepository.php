<?php

declare(strict_types=1);

namespace App\Domain\Conversation;

use App\Domain\Conversation\ValueObject\DialogId;

/**
 * Persistence boundary for the {@see Dialog} aggregate.
 *
 * The domain depends on this interface only; the concrete mapping to Eloquent
 * lives in the Infrastructure layer. The repository works with whole
 * aggregates — it never exposes the inner entities directly.
 */
interface DialogRepository
{
    /**
     * Persist the aggregate (insert or update) together with its requests.
     * Returns the saved aggregate, with database identifiers assigned.
     */
    public function save(Dialog $dialog): Dialog;

    public function findById(DialogId $id): ?Dialog;

    /**
     * Allocate the next identity for a not-yet-persisted dialog so the
     * aggregate can be created with a valid {@see DialogId} up front.
     */
    public function nextIdentity(): DialogId;
}
