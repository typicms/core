<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Loaders;

use Exception;
use Illuminate\Translation\FileLoader;
use Override;
use TypiCMS\Modules\Core\Models\Translation;

class MixedLoader extends FileLoader
{
    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  null|string  $namespace
     * @return array<string, string>
     */
    #[Override]
    public function load($locale, $group, $namespace = null): array
    {
        if ($group === '*' && $namespace === '*') {
            $jsonTranslations = $this->loadJsonPaths($locale);
            $databaseTranslations = $this->loadFromDatabase($locale, $group, $namespace);

            return array_replace_recursive($jsonTranslations, $databaseTranslations);
        }

        if (is_null($namespace) || $namespace === '*') {
            return $this->loadPaths($this->paths, $locale, $group);
        }

        return $this->loadNamespaced($locale, $group, $namespace);
    }

    /** @return array<string, string> */
    public function loadFromDatabase(string $locale, string $group, ?string $namespace = null): array
    {
        try {
            return Translation::query()
                ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(`translation`, ?)) AS translated', ['$.'.$locale])
                ->addSelect('key')
                ->pluck('translated', 'key')
                ->all();
        } catch (Exception) {
        }

        return [];
    }
}
