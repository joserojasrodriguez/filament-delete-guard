<?php

declare(strict_types=1);

namespace Joserojasrodriguez\FilamentDeleteGuard;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Joserojasrodriguez\FilamentDeleteGuard\Contracts\HasPreventDeletion;
use Joserojasrodriguez\FilamentDeleteGuard\Exceptions\CannotDeleteModelException;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FilamentDeleteGuardServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-delete-guard')
            ->hasTranslations();
    }

    public function bootingPackage(): void
    {
        $this->configureDeleteAction();
    }

    private function configureDeleteAction(): void
    {
        DeleteAction::configureUsing(function (DeleteAction $deleteAction): void {

            $deleteAction
                ->tooltip(fn ($record): ?string =>
                $record instanceof HasPreventDeletion
                    ? $record->showDeleteActionMessage()
                    : null
                )
                ->disabled(fn ($record): bool =>
                    $record instanceof HasPreventDeletion
                    && $record->showDeleteAction() === false
                )
                ->action(function (Model $record, DeleteAction $action): void {

                    try {
                        $record->delete();

                        $action->success();

                    } catch (CannotDeleteModelException $exception) {

                        Notification::make()
                            ->title(__('filament-delete-guard::messages.deletion_prevented'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                });
        });
    }
}