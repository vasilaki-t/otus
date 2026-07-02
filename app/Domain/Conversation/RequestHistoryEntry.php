<?php

declare(strict_types=1);

namespace App\Domain\Conversation;

use App\Domain\Conversation\ValueObject\MessageText;
use App\Domain\Conversation\ValueObject\MessengerId;
use App\Domain\Conversation\ValueObject\RequestId;
use DomainException;

/**
 * A single request/response exchange — an entity living inside the
 * {@see Dialog} aggregate. It is never referenced or modified from outside
 * the aggregate root; the root mediates every change.
 *
 * State transitions are expressed through behaviour ({@see self::attachResponse()})
 * and protected by invariants, never through public setters.
 */
final class RequestHistoryEntry
{
    private ?MessageText $response;

    public function __construct(
        private RequestId $id,
        private readonly MessageText $request,
        private readonly MessengerId $messengerId,
        ?MessageText $response = null,
    ) {
        $this->response = $response;
    }

    public function id(): RequestId
    {
        return $this->id;
    }

    public function request(): MessageText
    {
        return $this->request;
    }

    public function messengerId(): MessengerId
    {
        return $this->messengerId;
    }

    public function response(): ?MessageText
    {
        return $this->response;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * Attach the assistant's answer to this request.
     *
     * Invariant: a request may be answered exactly once.
     */
    public function attachResponse(MessageText $response): void
    {
        if ($this->response !== null) {
            throw new DomainException('This request already has a response.');
        }

        $this->response = $response;
    }

    /**
     * Assign the persistence identifier once the entry has been stored.
     * Internal to the aggregate/repository mapping; the id is write-once.
     */
    public function assignId(RequestId $id): void
    {
        if ($this->id->isAssigned()) {
            throw new DomainException('Request id is already assigned.');
        }

        $this->id = $id;
    }
}
