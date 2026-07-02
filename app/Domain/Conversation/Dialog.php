<?php

declare(strict_types=1);

namespace App\Domain\Conversation;

use App\Domain\Conversation\ValueObject\DialogId;
use App\Domain\Conversation\ValueObject\MessageText;
use App\Domain\Conversation\ValueObject\MessengerId;
use App\Domain\Conversation\ValueObject\RequestId;
use App\Domain\Conversation\ValueObject\UserId;

/**
 * Dialog — aggregate root of the Conversation subdomain.
 *
 * A pure domain object (no framework / persistence concerns). It owns its
 * {@see RequestHistoryEntry} entities and is the only legal entry point for
 * mutating them, which keeps the aggregate consistent as a whole.
 *
 * Design rules enforced here:
 *  - the aggregate is created in a valid state via the {@see self::start()}
 *    factory (correct-state-at-construction);
 *  - it exposes getters only — there are no public setters; every change
 *    goes through an intention-revealing behaviour method guarded by
 *    invariants;
 *  - it references the owning User aggregate by {@see UserId} only, never by
 *    a User object reference.
 */
final class Dialog
{
    /** @var list<RequestHistoryEntry> */
    private array $requests;

    /**
     * @param  list<RequestHistoryEntry>  $requests
     */
    private function __construct(
        private readonly DialogId $id,
        private readonly UserId $ownerId,
        private ?string $title,
        array $requests = [],
    ) {
        $this->title = $this->normalizeTitle($title);
        $this->requests = $requests;
    }

    /**
     * Start a brand new dialog in a guaranteed-valid initial state:
     * it has an identity, a known owner and no requests yet.
     */
    public static function start(DialogId $id, UserId $ownerId, ?string $title = null): self
    {
        return new self($id, $ownerId, $title, []);
    }

    /**
     * Reconstitute an existing dialog from persistence.
     *
     * @param  list<RequestHistoryEntry>  $requests
     */
    public static function reconstitute(DialogId $id, UserId $ownerId, ?string $title, array $requests): self
    {
        return new self($id, $ownerId, $title, $requests);
    }

    public function id(): DialogId
    {
        return $this->id;
    }

    public function ownerId(): UserId
    {
        return $this->ownerId;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * The request history as a defensive copy — callers cannot mutate the
     * aggregate's internal collection.
     *
     * @return list<RequestHistoryEntry>
     */
    public function requests(): array
    {
        return $this->requests;
    }

    public function requestCount(): int
    {
        return count($this->requests);
    }

    /**
     * Record a new user request in this dialog.
     *
     * The newly created entry has no response and no persistence id yet.
     */
    public function addRequest(MessageText $request, MessengerId $messengerId): RequestHistoryEntry
    {
        $entry = new RequestHistoryEntry(RequestId::none(), $request, $messengerId);
        $this->requests[] = $entry;

        return $entry;
    }

    /**
     * Attach the assistant's response to the most recent request.
     *
     * Invariants: there must be a request to answer, and (delegated to the
     * entry) a request may only be answered once.
     */
    public function attachResponseToLatest(MessageText $response): void
    {
        $entry = $this->latestRequest();

        if ($entry === null) {
            throw new \DomainException('Cannot attach a response: the dialog has no requests.');
        }

        $entry->attachResponse($response);
    }

    public function latestRequest(): ?RequestHistoryEntry
    {
        if ($this->requests === []) {
            return null;
        }

        return $this->requests[array_key_last($this->requests)];
    }

    private function normalizeTitle(?string $title): ?string
    {
        if ($title === null) {
            return null;
        }

        $trimmed = trim($title);

        return $trimmed === '' ? null : $trimmed;
    }
}
