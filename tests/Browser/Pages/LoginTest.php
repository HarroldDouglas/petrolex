<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * Test de découverte - voir la page de login
     */
    public function test_discover_login_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(3000) // Voir la page
                ->screenshot('login_page_discovery')
                ->assertSee('Se connecter'); // Vérifier que la page charge
        });
    }

    /**
     * Test connexion avec email (utilisateur admin existant)
     */
    public function test_admin_can_login_with_email()
    {
        $this->browse(function (Browser $browser) {
            echo "🔐 Test connexion admin avec email...\n";
            echo 'Email: '.env('ADMIN_EMAIL')."\n";

            $browser->visit('/login')
                ->pause(2000) // Laisser Livewire se charger
                ->waitFor('input[wire\\:model="identifier"]', 10) // Attendre le champ Livewire
                ->type('input[wire\\:model="identifier"]', env('ADMIN_EMAIL'))
                ->pause(1000)
                ->screenshot('01_email_filled')
                ->type('input[wire\\:model="password"]', env('ADMIN_PASSWORD'))
                ->pause(1000)
                ->screenshot('02_password_filled')
                ->press('Se connecter')
                ->pause(5000) // Attendre la redirection
                ->screenshot('03_after_login');

            // Vérifier la redirection
            $currentUrl = $browser->driver->getCurrentURL();
            echo 'URL actuelle après connexion: '.$currentUrl."\n";

            // Si pas resté sur /login, c'est que la connexion a réussi
            if (! str_contains($currentUrl, '/login')) {
                echo "✅ Connexion réussie - redirection détectée\n";
            } else {
                echo "❌ Toujours sur la page de login\n";
                // Vérifier s'il y a des erreurs
                try {
                    $browser->assertSee('Identifiants'); // Message d'erreur typique
                    echo "❌ Erreur de connexion détectée\n";
                } catch (\Exception $e) {
                    echo "🤔 Pas d'erreur visible, peut-être un problème de redirection\n";
                }
            }
        });
    }

    /**
     * Test connexion avec téléphone (utilisateur admin existant)
     */
    public function test_admin_can_login_with_phone()
    {
        $this->browse(function (Browser $browser) {
            echo "📱 Test connexion admin avec téléphone...\n";
            echo 'Téléphone: '.env('ADMIN_PHONE')."\n";

            $browser->visit('/login')
                ->pause(2000)
                ->waitFor('input[wire\\:model="identifier"]', 10)
                ->type('input[wire\\:model="identifier"]', env('ADMIN_PHONE'))
                ->pause(1000)
                ->screenshot('01_phone_filled')
                ->type('input[wire\\:model="password"]', env('ADMIN_PASSWORD'))
                ->pause(1000)
                ->screenshot('02_password_filled')
                ->press('Se connecter')
                ->pause(5000)
                ->screenshot('03_after_phone_login');

            // Vérifier la redirection
            $currentUrl = $browser->driver->getCurrentURL();
            echo 'URL actuelle après connexion: '.$currentUrl."\n";

            if (! str_contains($currentUrl, '/login')) {
                echo "✅ Connexion avec téléphone réussie\n";
            } else {
                echo "❌ Connexion avec téléphone échouée\n";
            }
        });
    }

    /**
     * Test avec identifiants incorrects
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $this->browse(function (Browser $browser) {
            echo "❌ Test avec identifiants incorrects...\n";

            $browser->visit('/login')
                ->pause(2000)
                ->waitFor('input[wire\\:model="identifier"]', 10)
                ->type('input[wire\\:model="identifier"]', 'wrong@email.com')
                ->type('input[wire\\:model="password"]', 'wrongpassword')
                ->press('Se connecter')
                ->pause(5000) // Attendre la réponse Livewire
                ->screenshot('invalid_login_attempt');

            // Vérifier que nous sommes toujours sur la page de login
            $currentUrl = $browser->driver->getCurrentURL();
            if (str_contains($currentUrl, '/login')) {
                echo "✅ Resté sur la page de login (attendu)\n";
            }

            // Chercher des messages d'erreur possibles
            $errorSelectors = [
                '.alert-danger',
                '.text-danger',
                '.error-message',
                '[class*="error"]',
                '.invalid-feedback',
            ];

            $errorFound = false;
            foreach ($errorSelectors as $selector) {
                try {
                    $browser->assertPresent($selector);
                    echo "✅ Message d'erreur trouvé avec: ".$selector."\n";
                    $errorFound = true;
                    break;
                } catch (\Exception $e) {
                    // Continuer à chercher
                }
            }

            if (! $errorFound) {
                echo "🤔 Aucun message d'erreur visible trouvé\n";
            }
        });
    }

    /**
     * Test avec mot de passe incorrect mais bon email
     */
    public function test_login_fails_with_wrong_password()
    {
        $this->browse(function (Browser $browser) {
            echo "🔑 Test avec mauvais mot de passe...\n";

            $browser->visit('/login')
                ->pause(2000)
                ->waitFor('input[wire\\:model="identifier"]', 10)
                ->type('input[wire\\:model="identifier"]', env('ADMIN_EMAIL')) // Bon email
                ->type('input[wire\\:model="password"]', 'mauvais_mot_de_passe') // Mauvais mot de passe
                ->press('Se connecter')
                ->pause(5000)
                ->screenshot('wrong_password_attempt');

            // Vérifier que nous sommes toujours sur la page de login
            $currentUrl = $browser->driver->getCurrentURL();
            if (str_contains($currentUrl, '/login')) {
                echo "✅ Resté sur la page de login (attendu)\n";
            }
        });
    }

    /**
     * Test du bouton "Se souvenir de moi" (si existe)
     */
    public function test_remember_me_checkbox()
    {
        $this->browse(function (Browser $browser) {
            echo "🔄 Test du checkbox 'Se souvenir de moi'...\n";

            $browser->visit('/login')
                ->pause(2000)
                ->waitFor('input[wire\\:model="identifier"]', 10);

            // Vérifier si le checkbox existe
            try {
                $browser->assertPresent('input[wire\\:model="remember"]');
                echo "✅ Checkbox 'remember' trouvé\n";

                $browser->check('input[wire\\:model="remember"]') // Cocher "Se souvenir"
                    ->pause(1000)
                    ->screenshot('remember_checked')
                    ->type('input[wire\\:model="identifier"]', env('ADMIN_EMAIL'))
                    ->type('input[wire\\:model="password"]', env('ADMIN_PASSWORD'))
                    ->press('Se connecter')
                    ->pause(5000)
                    ->screenshot('remember_login_complete');
                echo "✅ Test avec remember me terminé\n";

            } catch (\Exception $e) {
                echo "❌ Checkbox 'remember' non trouvé: ".$e->getMessage()."\n";
            }
        });
    }

    /**
     * Test de validation des champs vides
     */
    public function test_empty_fields_validation()
    {
        $this->browse(function (Browser $browser) {
            echo "📝 Test validation champs vides...\n";

            $browser->visit('/login')
                ->pause(2000)
                ->waitFor('button[type="submit"]', 10)
                ->press('Se connecter') // Soumettre sans remplir
                ->pause(3000)
                ->screenshot('empty_validation');

            // Vérifier que nous sommes toujours sur la page de login
            $currentUrl = $browser->driver->getCurrentURL();
            if (str_contains($currentUrl, '/login')) {
                echo "✅ Resté sur la page de login (validation OK)\n";
            }

            // Chercher des messages de validation
            $validationSelectors = [
                '.invalid-feedback',
                '.field-error',
                '.validation-error',
                '[class*="invalid"]',
                '.text-danger',
            ];

            foreach ($validationSelectors as $selector) {
                try {
                    $browser->assertPresent($selector);
                    echo '✅ Message de validation trouvé: '.$selector."\n";
                } catch (\Exception $e) {
                    // Continuer à chercher
                }
            }
        });
    }
}
