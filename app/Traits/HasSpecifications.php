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
            $specifications = is_array($value) ? $value : (json_decode($value, true) ?? []);

            if (! is_array($specifications)) {
                $specifications = [];
            }

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

            // Combine value and unit into a single field with space
            $combinedValue = $spec['value'];
            if (isset($spec['unit']) && ! empty($spec['unit'])) {
                $combinedValue .= ' '.$spec['unit'];
            }

            return [
                'name' => $translatedName,
                'value' => $combinedValue,
            ];
        }, $specifications);
    }

    /**
     * Get default specifications from existing fields.
     * Should be implemented by the model using this trait.
     */
    abstract protected function getDefaultSpecifications(): array;
}
