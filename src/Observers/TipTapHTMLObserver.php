<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Observers;

use DOMDocument;
use DOMElement;
use DOMXPath;
use TypiCMS\Modules\Core\Support\HtmlSanitizer;

class TipTapHTMLObserver
{
    public function saving(mixed $model): void
    {
        if (! property_exists($model, 'tipTapContent')) {
            return;
        }

        $isTranslatable = method_exists($model, 'getTranslations')
            && method_exists($model, 'setTranslation');

        foreach ($model->tipTapContent as $richTextElement) {
            if ($isTranslatable) {
                foreach ($model->getTranslations($richTextElement) as $locale => $content) {
                    $model->setTranslation(
                        $richTextElement,
                        $locale,
                        $this->patchTipTapHTML($content, $locale),
                    );
                }

                continue;
            }

            $model->{$richTextElement} = $this->patchTipTapHTML(
                $model->{$richTextElement},
                app()->getLocale(),
            );
        }
    }

    public function patchTipTapHTML(?string $content, string $locale): ?string
    {
        if ($content === null || $content === '<p></p>') {
            return null;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        try {
            $dom->loadHTML(
                '<!DOCTYPE html><html lang="'
                .$locale
                .'"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>'
                .$content
                .'</body></html>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
            );
            $xpath = new DOMXPath($dom);
            $listItems = $xpath->query('//li');
            if ($listItems) {
                foreach ($listItems as $li) {
                    if (! $li instanceof DOMElement) {
                        continue;
                    }

                    /*
                     * TipTap wraps list item text in a paragraph (<li><p>Text</p></li>): unwrap it,
                     * but keep the paragraphs of structured list items (<li><h3>Title</h3><p>Text</p></li>).
                     */
                    $p = $li->firstElementChild;
                    if (! $p instanceof DOMElement) {
                        continue;
                    }

                    if ($p->tagName !== 'p') {
                        continue;
                    }

                    while ($p->firstChild !== null) {
                        $li->insertBefore($p->firstChild, $p);
                    }

                    $li->removeChild($p);
                }
            }

            $body = $dom->getElementsByTagName('body')->item(0);
            if (! $body) {
                return $content;
            }

            (new HtmlSanitizer)->sanitizeNode($body);

            $result = '';
            foreach ($body->childNodes as $child) {
                $result .= $dom->saveHTML($child);
            }

            return $result;
        } finally {
            libxml_clear_errors();
        }
    }
}
