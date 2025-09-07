<?php

namespace App\Traits;

trait HasSpecifications
{
    /**
     * Get the specifications for the model with multilingual support.
     */
    public function getSpecificationsAttribute($value): array
    {
        if ($value) {
            $specifications = json_decode($value, true) ?? [];

            return $this->translateSpecifications($specifications);
        }

        $defaultSpecs = $this->getDefaultSpecifications();

        return $this->translateSpecifications($defaultSpecs);
    }

    /**
     * Translate specifications based on current locale.
     */
    protected function translateSpecifications(array $specifications): array
    {
        $locale = app()->getLocale();

        return array_map(function ($spec) use ($locale) {
            $translatedName = $spec['name'];

            if ($locale === 'en' && isset($spec['name_en']) && ! empty($spec['name_en'])) {
                $translatedName = $spec['name_en'];
            }

            return [
                'name' => $translatedName,
                'value' => $spec['value'],
                'unit' => $spec['unit'] ?? null,
            ];
        }, $specifications);
    }

    /**
     * Get default specifications from existing fields.
     * Should be implemented by the model using this trait.
     */
    abstract protected function getDefaultSpecifications(): array;
}
