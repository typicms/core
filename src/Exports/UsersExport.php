<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Models\User;

/**
 * @implements WithMapping<mixed>
 */
class UsersExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStrictNullComparison
{
    use EscapesFormulas;

    /** @return Collection<int, User> */
    public function collection(): Collection
    {
        return QueryBuilder::for(User::class)
            ->allowedSorts(
                'first_name',
                'last_name',
                'email',
                'subscription_plan',
                'subscription_ends_at',
                'last_payment_at',
                'superuser',
            )
            ->allowedFilters(
                AllowedFilter::custom('first_name,last_name,email', new FilterOr),
            )
            ->get();
    }

    /** @return array<int, mixed> */
    public function map(mixed $row): array
    {
        return [
            Date::dateTimeToExcel($row->created_at),
            Date::dateTimeToExcel($row->updated_at),
            $this->escapeFormula($row->last_name),
            $this->escapeFormula($row->first_name),
            $this->escapeFormula($row->email),
            $this->escapeFormula($row->phone),
            $this->escapeFormula($row->street),
            $this->escapeFormula($row->number),
            $this->escapeFormula($row->box),
            $this->escapeFormula($row->postal_code),
            $this->escapeFormula($row->city),
            $this->escapeFormula($row->country),
            $row->locale,
            $row->privacy_policy_accepted,
        ];
    }

    /** @return string[] */
    public function headings(): array
    {
        return [
            __('Created at'),
            __('Updated at'),
            __('Last name'),
            __('First name'),
            __('Email'),
            __('Phone'),
            __('Street'),
            __('Number'),
            __('Box'),
            __('Postal code'),
            __('City'),
            __('Country'),
            __('Locale'),
            __('Privacy policy accepted'),
        ];
    }

    /** @return array<string, string> */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DATETIME,
            'B' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }
}
