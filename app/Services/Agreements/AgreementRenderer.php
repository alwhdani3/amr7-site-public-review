<?php

namespace App\Services\Agreements;

use App\Models\AgreementTemplate;

class AgreementRenderer
{
    /**
     * Render a template body using supported {{placeholder}} tokens only.
     */
    public function render(AgreementTemplate $template, array $data): string
    {
        $body = $template->body ?: AgreementTemplate::defaultBodyTemplate();
        $replacements = [];

        foreach ($this->supportedPlaceholders() as $placeholder) {
            $replacements['{{' . $placeholder . '}}'] = $this->stringValue($data[$placeholder] ?? '');
        }

        return strtr($body, $replacements);
    }

    /**
     * @return array<int, string>
     */
    public function supportedPlaceholders(): array
    {
        return AgreementTemplate::supportedPlaceholders();
    }

    /**
     * Return placeholders used by a body but not recognised by the renderer.
     *
     * @return array<int, string>
     */
    public function missingPlaceholders(string $body): array
    {
        preg_match_all('/{{\s*([a-zA-Z0-9_]+)\s*}}/', $body, $matches);

        $used = array_values(array_unique($matches[1] ?? []));
        $supported = $this->supportedPlaceholders();

        return array_values(array_diff($used, $supported));
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->map(fn ($item) => '- ' . $item)
                ->implode(PHP_EOL);
        }

        return trim((string) $value);
    }
}
