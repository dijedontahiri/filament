<?php

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\StateCasts\EnumStateCast;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tests\Fixtures\Livewire\Livewire;
use Filament\Tests\TestCase;

uses(TestCase::class);

it('returns cast state from an existing child schema when `Get` is used by the parent component', function (): void {
    $parentComponent = (new Component)
        ->schema([
            (new Component)
                ->statePath('status')
                ->stateCast(new EnumStateCast(GetCastedStateTestStatus::class)),
        ]);

    Schema::make(Livewire::make())
        ->statePath('data')
        ->components([$parentComponent])
        ->fill(['status' => GetCastedStateTestStatus::Active->value]);

    expect((new Get($parentComponent))('status'))
        ->toBe(GetCastedStateTestStatus::Active);
});

it('does not evaluate an uncached child schema while `Get` searches from the parent component', function (): void {
    $childSchemaEvaluationCount = 0;

    $parentComponent = (new Component)
        ->schema(function () use (&$childSchemaEvaluationCount): array {
            $childSchemaEvaluationCount++;

            return [
                (new Component)->statePath('status'),
            ];
        });

    $schema = Schema::make(Livewire::make())
        ->statePath('data')
        ->components([$parentComponent]);

    $schema->getComponents();

    expect((new Get($parentComponent))('missing'))
        ->toBeNull()
        ->and($childSchemaEvaluationCount)
        ->toBe(0);
});

enum GetCastedStateTestStatus: string
{
    case Active = 'active';
}
