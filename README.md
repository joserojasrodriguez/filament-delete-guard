# filament-delete-guard

Prevent unsafe deletions in Filament by enforcing deletion rules at the **model** level and showing friendly notifications when deletion is blocked.

---

## What does it do?

This package provides:

- A small **contract** (`HasPreventDeletion`) to describe deletion rules.
- A **trait** (`InteractsWithPreventDeletion`) with the core logic to validate relations and custom rules.
- A `CannotDeleteModelException` to block deletion.
- Filament integration that **customizes `DeleteAction`** to:
  - disable the action (optional),
  - show a tooltip (optional),
  - attempt `$record->delete()` and, if blocked, show a Filament notification.

---

## Installation

```bash
composer require joserojasrodriguez/filament-delete-guard
```

The package automatically registers its service provider (Laravel package discovery).

---

## Usage

### 1) Implement the contract and use the trait

In your Eloquent model:

```php
use Illuminate\Database\Eloquent\Model;
use Joserojasrodriguez\FilamentDeleteGuard\Contracts\HasPreventDeletion;
use Joserojasrodriguez\FilamentDeleteGuard\Traits\InteractsWithPreventDeletion;

class Invoice extends Model implements HasPreventDeletion
{
    use InteractsWithPreventDeletion;

    public function deletionRelations(): array
    {
        return [
            // relation method => human label (used in the default message)
            'payments' => 'payments',
            'lines' => 'lines',
        ];
    }

    public function deletionMessages(): array
    {
        return [
            // optional per-relation override
            'payments' => 'You cannot delete an invoice with associated payments.',
        ];
    }

    public function customDeletionRules(): void
    {
        // Optional: add any extra checks here.
        // To block deletion, throw CannotDeleteModelException.
        // throw CannotDeleteModelException::because('This invoice is locked.');
    }
}
```

> **Note:** You do not need to manually add the `deleting` hook in your model. The `InteractsWithPreventDeletion` trait handles it automatically.

---

## Filament integration

Filament integration globally configures the delete action:

- Calls `$record->delete()` and catches `CannotDeleteModelException`.
- If the exception occurs, shows a Filament notification with:
  - title: `filament-delete-guard::messages.deletion_prevented`
  - body: the exception message

### BulkActions support

The package works with Filament `DeleteBulkAction`.

Each record deletion is executed individually, so the `deleting` event is triggered for every model. If a record throws `CannotDeleteModelException`, Filament counts it as a failed deletion and continues processing the remaining records.

Partial success notifications are automatically handled by Filament.


### Optional: Disable the delete action and show tooltip

If your model implements these methods, the delete action can be disabled and display a tooltip:

```php
public function showDeleteAction(): bool
{
    return false; // disables the delete action
}

public function showDeleteActionMessage(): ?string
{
    return 'Cannot delete because it is reconciled.'; // tooltip text
}
```

> These methods are checked at runtime by the Filament action configuration.

---

## How it works internally

The trait registers a model `deleting` event using Laravel’s trait booting convention:

```php
protected static function bootInteractsWithPreventDeletion(): void
{
    static::deleting(fn ($model) => $model->ensureCanBeDeleted());
}
```
---

## Translations

The package ships translations for:

- `filament-delete-guard::messages.deletion_prevented`
- `filament-delete-guard::messages.has_relation`

Default messages (en):

- **Deletion blocked**
- **Cannot delete because it has :relation associated.**

---

## License

MIT
