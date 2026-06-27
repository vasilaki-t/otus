<?php

declare(strict_types=1);

namespace App\Infrastructure\Conversation;

use App\Domain\Conversation\Dialog;
use App\Domain\Conversation\DialogRepository;
use App\Domain\Conversation\RequestHistoryEntry;
use App\Domain\Conversation\ValueObject\DialogId;
use App\Domain\Conversation\ValueObject\MessageText;
use App\Domain\Conversation\ValueObject\MessengerId;
use App\Domain\Conversation\ValueObject\RequestId;
use App\Domain\Conversation\ValueObject\UserId;
use App\Models\Dialog as DialogModel;
use App\Models\RequestHistory as RequestHistoryModel;
use Illuminate\Support\Facades\DB;

/**
 * Maps the pure {@see Dialog} aggregate to/from the Eloquent persistence
 * models. The Eloquent classes are now just storage details hidden behind
 * this repository; all conversation behaviour lives in the aggregate.
 */
final class EloquentDialogRepository implements DialogRepository
{
    public function save(Dialog $dialog): Dialog
    {
        $persistedId = DB::transaction(function () use ($dialog): DialogId {
            $model = DialogModel::query()->updateOrCreate(
                ['id' => $dialog->id()->value()],
                [
                    'user_id' => $dialog->ownerId()->value(),
                    'title' => $dialog->title(),
                ],
            );

            // The database may assign its own key (e.g. Postgres identity
            // columns ignore an explicitly provided id), so persist the
            // request history against the id that was actually stored.
            $persistedId = DialogId::fromInt((int) $model->getKey());

            foreach ($dialog->requests() as $entry) {
                $this->saveEntry($persistedId, $entry);
            }

            return $persistedId;
        });

        $reloaded = $this->findById($persistedId);

        // The row was just written inside this method, so it always exists.
        assert($reloaded instanceof Dialog);

        return $reloaded;
    }

    public function findById(DialogId $id): ?Dialog
    {
        $model = DialogModel::query()
            ->with(['requestHistories' => fn ($query) => $query->orderBy('id')])
            ->find($id->value());

        if ($model === null) {
            return null;
        }

        return $this->toAggregate($model);
    }

    public function nextIdentity(): DialogId
    {
        $max = (int) DialogModel::query()->max('id');

        return DialogId::fromInt($max + 1);
    }

    private function saveEntry(DialogId $dialogId, RequestHistoryEntry $entry): void
    {
        $attributes = [
            'dialog_id' => $dialogId->value(),
            'messenger_id' => $entry->messengerId()->value(),
            'request_text' => $entry->request()->value(),
            'response_text' => $entry->response()?->value(),
        ];

        $id = $entry->id();

        if ($id->isAssigned()) {
            RequestHistoryModel::query()
                ->where('id', $id->value())
                ->update($attributes);

            return;
        }

        $model = RequestHistoryModel::query()->create($attributes);

        $entry->assignId(RequestId::fromInt((int) $model->getKey()));
    }

    private function toAggregate(DialogModel $model): Dialog
    {
        $entries = [];

        /** @var RequestHistoryModel $row */
        foreach ($model->requestHistories as $row) {
            $entries[] = new RequestHistoryEntry(
                RequestId::fromInt((int) $row->getKey()),
                MessageText::fromString((string) $row->request_text),
                MessengerId::fromInt((int) $row->messenger_id),
                $row->response_text !== null
                    ? MessageText::fromString((string) $row->response_text)
                    : null,
            );
        }

        return Dialog::reconstitute(
            DialogId::fromInt((int) $model->getKey()),
            UserId::fromInt((int) $model->user_id),
            $model->title,
            $entries,
        );
    }
}
