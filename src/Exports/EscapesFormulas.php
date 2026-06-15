<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Exports;

trait EscapesFormulas
{
    /**
     * Cast user content to a string and neutralize leading spreadsheet
     * formula metacharacters so it is never interpreted as a formula
     * when the export is opened in Excel, LibreOffice or Google Sheets.
     */
    protected function escapeFormula(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
