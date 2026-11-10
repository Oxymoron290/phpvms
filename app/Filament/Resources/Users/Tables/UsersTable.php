<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserState;
use App\Models\Airport;
use App\Models\User;
use App\Notifications\Messages\AdminPilotEmail;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification as LaravelNotification;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ident is built from the airline's code
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('airline'))
            ->columns([
                TextColumn::make('ident')
                    ->label('ID')
                    ->searchable(['pilot_id'])
                    ->sortable(['pilot_id']),

                TextColumn::make('callsign')
                    ->toggleable()
                    ->label(__('flights.callsign'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(__('common.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->toggleable()
                    ->label(__('common.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->toggleable()
                    ->label(__('common.registered_on'))
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('home_airport_id')
                    ->label(__('airports.home'))
                    ->toggleable()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('state')
                    ->label(__('common.state'))
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('home_airport_id')
                    ->label(__('airports.home'))
                    ->relationship('home_airport', 'icao')
                    ->getOptionLabelFromRecordUsing(fn (Airport $record): string => $record->icao.' - '.$record->name)
                    ->searchable()
                    ->preload(),

                SelectFilter::make('curr_airport_id')
                    ->label(__('airports.current'))
                    ->relationship('current_airport', 'icao')
                    ->getOptionLabelFromRecordUsing(fn (Airport $record): string => $record->icao.' - '.$record->name)
                    ->searchable()
                    ->preload(),

                SelectFilter::make('state')
                    ->label(__('common.state'))
                    ->options(UserState::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('email_pilots')
                        ->label(__('filament.email_pilots'))
                        ->authorizeIndividualRecords(
                            fn (User $record): bool => !$record->trashed() && $record->state !== UserState::DELETED,
                        )
                        ->schema([
                            TextInput::make('subject')
                                ->label(__('filament.news_subject'))
                                ->required()
                                ->maxLength(255),
                            Textarea::make('message')
                                ->label(__('filament.news_body'))
                                ->required()
                                ->rows(8),
                        ])
                        ->requiresConfirmation()
                        ->action(function (Collection $records, array $data): void {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title(__('filament.email_pilots_no_eligible_recipients'))
                                    ->danger()
                                    ->send();

                                return;
                            }

                            LaravelNotification::send(
                                $records,
                                new AdminPilotEmail($data['subject'], $data['message']),
                            );

                            Notification::make()
                                ->title(__('filament.email_pilots_queued', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
            ]);
    }
}
