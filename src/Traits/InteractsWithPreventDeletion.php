<?php

declare(strict_types=1);

namespace Joserojasrodriguez\FilamentDeleteGuard\Traits;

use Joserojasrodriguez\FilamentDeleteGuard\Exceptions\CannotDeleteModelException;

trait InteractsWithPreventDeletion
{
    /*
     |--------------------------------------------------------------------------
     | Default Implementations (Contract)
     |--------------------------------------------------------------------------
     */

    public function deletionRelations(): array
    {
        return [];
    }

    public function customDeletionRules(): void
    {
        // Default: allow deletion
    }

    public function deletionMessages(): array
    {
        return [];
    }

    /*
     |--------------------------------------------------------------------------
     | Core Logic
     |--------------------------------------------------------------------------
     */

    public function ensureCanBeDeleted(): void
    {
        $this->checkDeletionRelations();
        $this->customDeletionRules();
    }

    protected function checkDeletionRelations(): void
    {
        foreach ($this->deletionRelations() as $relation => $label) {

            if (! method_exists($this, $relation)) {
                continue;
            }

            if ($this->{$relation}()->exists()) {

                throw CannotDeleteModelException::because(
                    $this->resolveDeletionMessage($relation, $label)
                );
            }
        }
    }

    protected function resolveDeletionMessage(string $relation, string $label): string
    {
        return $this->deletionMessages()[$relation]
            ?? __('filament-delete-guard::messages.has_relation', [
                'relation' => $label,
            ]);
    }

    public function showDeleteAction(): bool
    {
        try {
            $this->ensureCanBeDeleted();
            return true;
        }catch (CannotDeleteModelException){
            return false;
        }
    }

    public function showDeleteActionMessage(): ?string
    {
        try {
            $this->ensureCanBeDeleted();
        }catch (CannotDeleteModelException $exception){
            return $exception->getMessage();
        }
        return null;
    }

}