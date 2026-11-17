<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Components;

use Carbon\Carbon;
use Illuminate\View\Component;
use Illuminate\View\View;

class DateRange extends Component
{
    private const NBSP = "\u{00A0}";

    public function __construct(
        public Carbon $start,
        public Carbon $end,
        public string $format = 'D MMMM',
        public string $spacer = '–',
    ) {}

    public function render(): View
    {
        $format = str_replace(' ', self::NBSP, $this->format);
        $formatWithYear = $format.self::NBSP.'YYYY';
        $spacer = trim($this->spacer);

        $showYear = ! $this->start->isSameYear(now()) || ! $this->end->isSameYear(now());
        $startFormat = $showYear ? $formatWithYear : $format;
        $separator = self::NBSP."{$spacer} ";

        if ($this->start->isSameYear($this->end)) {
            $startFormat = $format;
        }

        if ($this->start->isSameMonth($this->end)) {
            $startFormat = 'D';
            $separator = $spacer;
        }

        return view('public::components.date-range', [
            'startFormat' => $startFormat,
            'endFormat' => $formatWithYear,
            'separator' => $separator,
        ]);
    }
}
