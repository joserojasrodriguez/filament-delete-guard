# filament-delete-guard

Prevent unsafe deletions in Filament by enforcing deletion rules at the **model** level and showing a friendly Filament notification when deletion is blocked.

---

## What it does

This package provides:

- A small **contract** (`HasPreventDeletion`) to describe deletion rules.
- A **trait** (`InteractsWithPreventDeletion`) with the core logic to validate relations and custom rules.
- A `CannotDeleteModelException` you can throw to block deletion.
- A Filament integration that **customizes `DeleteAction`** to:
  - disable the action (optional),
  - show a tooltip (optional),
  - attempt `$record->delete()` and, if blocked, show a Filament **danger notification**.

---

## Installation

```bash
composer require joserojasrodriguez/filament-delete-guard
```

The package registers its service provider automatically (Laravel package discovery).

---

## Usage

### 1) Implement the contract + use the trait

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
            // relationMethod => human label (used in the default message)
            'payments' => 'pagos',
            'lines' => 'líneas',
        ];
    }

    public function deletionMessages(): array
    {
        return [
            // optional per-relation override
            'payments' => 'No puedes eliminar una factura con pagos asociados.',
        ];
    }

    public function customDeletionRules(): void
    {
        // Optional: put any extra checks here.
        // If you want to block deletion, throw CannotDeleteModelException.
        // throw CannotDeleteModelException::because('Esta factura está bloqueada.');
    }
}
```

### 2) Call the guard before deleting (recommended via model event)

The trait exposes:

- `ensureCanBeDeleted(): void` (calls relation checks + `customDeletionRules()`)

The package does not force how you wire this into your model lifecycle, so the typical approach is a `deleting` hook:

```php
use Illuminate\Database\Eloquent\Model;

protected static function booted(): void
{
    static::deleting(function (self $model): void {
        $model->ensureCanBeDeleted();
    });
}
```

When deletion must be prevented, throw:

```php
use Joserojasrodriguez\FilamentDeleteGuard\Exceptions\CannotDeleteModelException;

throw CannotDeleteModelException::because('No se puede eliminar este registro.');
```

---

## Filament integration details

The service provider configures Filament’s `DeleteAction` globally:

- It calls `$record->delete()` and catches `CannotDeleteModelException`.
- On exception, it sends a Filament `Notification` with:
  - title: `filament-delete-guard::messages.deletion_prevented`
  - body: the exception message

### Optional: Disable the delete action + show tooltip

If your model implements these methods, the delete action can be disabled and display a tooltip:

```php
public function showDeleteAction(): bool
{
    return false; // disables the delete action
}

public function showDeleteActionMessage(): ?string
{
    return 'No se puede eliminar porque está conciliado.'; // tooltip text
}
```

> These methods are checked at runtime by the Filament action configuration.

---

## Translations

The package ships translations under:

- `filament-delete-guard::messages.deletion_prevented`
- `filament-delete-guard::messages.has_relation`

Current default messages (es):

- **Eliminación bloqueada**
- **No se puede eliminar porque tiene :relation asociados.**

---

## License

MIT
