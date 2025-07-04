<?php

namespace Tests;

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Configuration Firefox Driver
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new FirefoxOptions)
            ->addArguments([
                '--headless',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--window-size=1920,1080',
                '--disable-gpu',
                '--disable-web-security',
            ]);

        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            'http://127.0.0.1:4444',
            $capabilities
        );
    }

    /**
     * Nettoyer après chaque test
     */
    protected function tearDown(): void
    {
        static::closeAll();
        parent::tearDown();
    }

    /**
     * Méthode utilitaire pour déboguer le HTML d'une section de la page
     */
    protected function debugElement($browser, $selector = 'body')
    {
        try {
            $html = $browser->element($selector)->getAttribute('outerHTML');
            echo "====== Contenu HTML de '$selector' ======\n";
            echo $html."\n";
            echo "==========================================\n";
        } catch (\Exception $e) {
            echo "Impossible de récupérer le HTML de '$selector': ".$e->getMessage()."\n";
        }

        return $browser;
    }

    /**
     * Méthode utilitaire pour déboguer les éléments cliquables dans une zone
     * 
     * @param \Laravel\Dusk\Browser $browser
     * @param string $selector Sélecteur CSS de la zone à analyser
     * @return array Liste des éléments cliquables trouvés
     */
    protected function findClickableElements($browser, $selector = 'body')
    {
        try {
            // Capture un screenshot avant analyse
            $browser->screenshot('before_find_clickable');
            
            // Récupère les éléments cliquables (liens, boutons)
            $elements = $browser->elements($selector . ' a, ' . $selector . ' button, ' . $selector . ' [role="button"], ' . $selector . ' .btn');
            
            $clickableInfo = [];
            foreach ($elements as $index => $element) {
                try {
                    $text = $element->getText() ?: '[Pas de texte]';
                    $href = $element->getAttribute('href') ?: '[Pas de lien]';
                    $class = $element->getAttribute('class') ?: '[Pas de classe]';
                    $tagName = $element->getTagName();
                    
                    $clickableInfo[] = [
                        'index' => $index,
                        'tag' => $tagName,
                        'text' => $text,
                        'href' => $href,
                        'class' => $class
                    ];
                    
                    echo "Element cliquable #{$index}: <{$tagName}> '{$text}' (href: {$href}, class: {$class})\n";
                } catch (\Exception $e) {
                    echo "Erreur lors de l'analyse d'un élément cliquable: " . $e->getMessage() . "\n";
                }
            }
            
            echo count($clickableInfo) . " éléments cliquables trouvés dans '{$selector}'\n";
            return $clickableInfo;
            
        } catch (\Exception $e) {
            echo "Erreur lors de la recherche d'éléments cliquables: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    /**
     * Méthode utilitaire pour capturer une capture d'écran et l'HTML lors d'une erreur
     * 
     * @param \Laravel\Dusk\Browser $browser
     * @param string $errorName Nom de l'erreur pour identifier le screenshot
     * @param string $selector Sélecteur à déboguer (optionnel)
     */
    protected function captureErrorState($browser, $errorName, $selector = 'body')
    {
        // Prendre une capture d'écran avec un nom distinctif
        $browser->screenshot('ERROR_' . $errorName);
        
        // Déboguer l'élément si un sélecteur est fourni
        $this->debugElement($browser, $selector);
        
        // Afficher l'URL actuelle
        echo "URL lors de l'erreur: " . $browser->driver->getCurrentURL() . "\n";
        
        // Rechercher des éléments cliquables à proximité pour aider au diagnostic
        echo "Éléments cliquables à proximité de l'erreur:\n";
        $this->findClickableElements($browser, $selector);
    }
}
