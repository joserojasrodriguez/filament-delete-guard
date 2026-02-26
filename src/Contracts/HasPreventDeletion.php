<?php
declare(strict_types=1);

namespace Joserojasrodriguez\FilamentDeleteGuard\Contracts;

interface HasPreventDeletion
{
    /**
     * Return relations that should block deletion.
     *
     * Example:
     * [
     *     'orders' => 'orders',
     *     'invoices' => 'invoices',
     * ]
     */
    public function deletionRelations(): array;

    /**
     * Custom deletion rules.
     * Throw CannotDeleteModelException if deletion must be prevented.
     */
    public function customDeletionRules(): void;

    /**
     * Custom messages per relation.
     *
     * Example:
     * [
     *     'orders' => 'This record has active orders.',
     * ]
     */
    public function deletionMessages(): array;
}