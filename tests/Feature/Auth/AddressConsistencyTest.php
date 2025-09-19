<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressConsistencyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\GeographicSeeder::class);
    }

    /**
     * Test que les adresses de livraison sont cohérentes entre login et api/user
     */
    public function test_delivery_addresses_consistency_between_login_and_profile(): void
    {
        // Créer un utilisateur customer avec plusieurs adresses
        $user = User::factory()
            ->customer(addressesCount: 3)
            ->create([
                'email' => 'test@consistency.com',
                'password' => bcrypt('password'),
            ]);

        // 1. Login et récupérer la réponse
        $loginResponse = $this->postJson('/api/login', [
            'login' => 'test@consistency.com',
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $loginResponse->assertSuccessful();
        $loginData = $loginResponse->json('data');
        $loginAddresses = $loginData['user']['delivery_addresses'] ?? [];

        // 2. Appeler api/user avec le token
        $token = $loginData['access_token'];
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $profileResponse->assertSuccessful();
        $profileData = $profileResponse->json('data');
        $profileAddresses = $profileData['delivery_addresses'] ?? [];

        // 3. Vérifications de cohérence
        $this->assertSame(
            count($loginAddresses),
            count($profileAddresses),
            'Le nombre d\'adresses doit être identique entre login et profile'
        );

        $this->assertGreaterThan(
            0,
            count($loginAddresses),
            'L\'utilisateur doit avoir au moins une adresse'
        );

        // 4. Vérifier que chaque adresse de login existe dans profile
        foreach ($loginAddresses as $loginAddress) {
            $found = false;
            foreach ($profileAddresses as $profileAddress) {
                if ($loginAddress['id'] === $profileAddress['id']) {
                    $found = true;
                    // Vérifier que la structure est identique
                    $this->assertEquals($loginAddress, $profileAddress);
                    break;
                }
            }
            $this->assertTrue($found, "L'adresse ID {$loginAddress['id']} de login doit exister dans profile");
        }

        // 5. Vérifier que la structure des adresses est complète
        if (!empty($loginAddresses)) {
            $firstAddress = $loginAddresses[0];
            
            // Champs obligatoires
            $this->assertArrayHasKey('id', $firstAddress);
            $this->assertArrayHasKey('label', $firstAddress);
            $this->assertArrayHasKey('address', $firstAddress);
            $this->assertArrayHasKey('latitude', $firstAddress);
            $this->assertArrayHasKey('longitude', $firstAddress);
            $this->assertArrayHasKey('is_default', $firstAddress);
            
            // Données géographiques
            $this->assertArrayHasKey('neighborhood', $firstAddress);
            $this->assertArrayHasKey('municipality', $firstAddress);
            $this->assertArrayHasKey('city', $firstAddress);
            $this->assertArrayHasKey('country', $firstAddress);

            // Vérifier que les données géographiques ne sont pas null
            $this->assertNotNull($firstAddress['neighborhood'], 'Le quartier ne doit pas être null');
            $this->assertNotNull($firstAddress['municipality'], 'La municipalité ne doit pas être null');
            $this->assertNotNull($firstAddress['city'], 'La ville ne doit pas être null');
            $this->assertNotNull($firstAddress['country'], 'Le pays ne doit pas être null');
        }
    }

    /**
     * Test que toutes les adresses créées ont un quartier (neighborhood_id)
     */
    public function test_all_addresses_have_neighborhood(): void
    {
        // Créer un customer avec des adresses
        $user = User::factory()
            ->customer(addressesCount: 3)
            ->create();

        $customer = $user->customer;
        $addresses = $customer->deliveryAddresses;

        $this->assertGreaterThan(0, $addresses->count(), 'Le customer doit avoir des adresses');

        foreach ($addresses as $address) {
            $this->assertNotNull(
                $address->neighborhood_id,
                "L'adresse ID {$address->id} doit avoir un neighborhood_id"
            );
            
            $this->assertNotNull(
                $address->neighborhood,
                "L'adresse ID {$address->id} doit avoir une relation neighborhood chargée"
            );
        }
    }

    /**
     * Test qu'une nouvelle adresse créée via API a le bon format
     */
    public function test_newly_created_address_has_consistent_format(): void
    {
        // Créer un customer
        $user = User::factory()
            ->customer(addressesCount: 1)
            ->create();

        // S'authentifier
        $token = $user->createToken('test')->plainTextToken;

        // Créer une nouvelle adresse
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/customers/{$user->customer->id}/delivery-addresses", [
            'label' => 'Test Address',
            'address' => '123 Test Street',
            'latitude' => 3.8617882,
            'longitude' => 11.5835694,
            'neighborhood_id' => 1, // Assumant qu'il existe
        ]);

        $response->assertSuccessful();
        $newAddress = $response->json('data');

        // Maintenant récupérer le profil et vérifier que la nouvelle adresse est là
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $profileResponse->assertSuccessful();
        $profileAddresses = $profileResponse->json('data.delivery_addresses');

        // Trouver la nouvelle adresse dans le profil
        $foundAddress = collect($profileAddresses)->firstWhere('id', $newAddress['id']);
        $this->assertNotNull($foundAddress, 'La nouvelle adresse doit apparaître dans le profil');

        // Vérifier que le format est identique
        $this->assertEquals($newAddress, $foundAddress);
    }
}