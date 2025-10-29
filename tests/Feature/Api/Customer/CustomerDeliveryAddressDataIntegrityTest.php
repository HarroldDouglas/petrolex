<?php

namespace Tests\Feature\Api\Customer;

use App\Http\Api\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDeliveryAddressDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function customer_delivery_addresses_have_all_required_contact_fields(): void
    {
        $customers = Customer::with('deliveryAddresses')->get();

        $this->assertNotEmpty($customers, 'No customers found in database');

        foreach ($customers as $customer) {
            foreach ($customer->deliveryAddresses as $address) {
                $this->assertNotNull($address->phone, "Address #{$address->id} ({$address->label}) is missing phone");
                $this->assertNotEmpty($address->phone, "Address #{$address->id} ({$address->label}) has empty phone");

                $this->assertNotNull($address->phone_country_code, "Address #{$address->id} ({$address->label}) is missing phone_country_code");
                $this->assertNotEmpty($address->phone_country_code, "Address #{$address->id} ({$address->label}) has empty phone_country_code");

                $this->assertNotNull($address->contact_firstname, "Address #{$address->id} ({$address->label}) is missing contact_firstname");
                $this->assertNotEmpty($address->contact_firstname, "Address #{$address->id} ({$address->label}) has empty contact_firstname");

                $this->assertNotNull($address->contact_lastname, "Address #{$address->id} ({$address->label}) is missing contact_lastname");
                $this->assertNotEmpty($address->contact_lastname, "Address #{$address->id} ({$address->label}) has empty contact_lastname");

                $this->assertNotNull($address->email, "Address #{$address->id} ({$address->label}) is missing email");
                $this->assertNotEmpty($address->email, "Address #{$address->id} ({$address->label}) has empty email");

                $this->assertNotNull($address->address_precision, "Address #{$address->id} ({$address->label}) is missing address_precision");
                $this->assertNotEmpty($address->address_precision, "Address #{$address->id} ({$address->label}) has empty address_precision");
            }
        }
    }

    /** @test */
    public function customer_delivery_addresses_have_all_required_geographic_relations(): void
    {
        $customers = Customer::with('deliveryAddresses.neighborhood.municipality.city.country')->get();

        $this->assertNotEmpty($customers, 'No customers found in database');

        foreach ($customers as $customer) {
            foreach ($customer->deliveryAddresses as $address) {
                $this->assertNotNull($address->neighborhood, "Address #{$address->id} ({$address->label}) is missing neighborhood");
                $this->assertNotNull($address->neighborhood->municipality, "Address #{$address->id} ({$address->label}) is missing municipality");
                $this->assertNotNull($address->neighborhood->municipality->city, "Address #{$address->id} ({$address->label}) is missing city");
                $this->assertNotNull($address->neighborhood->municipality->city->country, "Address #{$address->id} ({$address->label}) is missing country");
            }
        }
    }

    /** @test */
    public function customer_resource_returns_delivery_addresses_with_all_geographic_relations(): void
    {
        $customer = Customer::with('deliveryAddresses')->first();

        $this->assertNotNull($customer, 'No customer found in database');

        $resource = new CustomerResource($customer);
        $data = $resource->toArray(request());

        $this->assertArrayHasKey('delivery_addresses', $data);
        $this->assertNotEmpty($data['delivery_addresses']);

        foreach ($data['delivery_addresses'] as $address) {
            // Check contact fields
            $this->assertNotNull($address['phone']);
            $this->assertNotEmpty($address['phone']);
            $this->assertNotNull($address['phone_country_code']);
            $this->assertNotEmpty($address['phone_country_code']);
            $this->assertNotNull($address['contact_firstname']);
            $this->assertNotEmpty($address['contact_firstname']);
            $this->assertNotNull($address['contact_lastname']);
            $this->assertNotEmpty($address['contact_lastname']);
            $this->assertNotNull($address['email']);
            $this->assertNotEmpty($address['email']);
            $this->assertNotNull($address['address_precision']);
            $this->assertNotEmpty($address['address_precision']);

            // Check geographic relations
            $this->assertNotNull($address['neighborhood'], "Address {$address['label']} has null neighborhood");
            $this->assertNotNull($address['municipality'], "Address {$address['label']} has null municipality");
            $this->assertNotNull($address['city'], "Address {$address['label']} has null city");
            $this->assertNotNull($address['country'], "Address {$address['label']} has null country");
        }
    }

    /** @test */
    public function customer1_test_user_has_all_delivery_addresses_with_complete_data(): void
    {
        $user = User::where('email', 'customer1@test.com')->first();

        $this->assertNotNull($user, 'customer1@test.com user not found');
        $this->assertNotNull($user->customer, 'customer1@test.com has no customer record');

        $addresses = $user->customer->deliveryAddresses()
            ->with('neighborhood.municipality.city.country')
            ->get();

        $this->assertNotEmpty($addresses, 'customer1@test.com has no delivery addresses');

        foreach ($addresses as $address) {
            // All contact fields must be present
            $this->assertNotNull($address->phone);
            $this->assertNotEmpty($address->phone);
            $this->assertEquals('+237', $address->phone_country_code);
            $this->assertNotNull($address->contact_firstname);
            $this->assertNotEmpty($address->contact_firstname);
            $this->assertNotNull($address->contact_lastname);
            $this->assertNotEmpty($address->contact_lastname);
            $this->assertNotNull($address->email);
            $this->assertNotEmpty($address->email);
            $this->assertNotNull($address->address_precision);
            $this->assertNotEmpty($address->address_precision);

            // All geographic relations must be present
            $this->assertNotNull($address->neighborhood);
            $this->assertNotNull($address->neighborhood->municipality);
            $this->assertNotNull($address->neighborhood->municipality->city);
            $this->assertNotNull($address->neighborhood->municipality->city->country);
            $this->assertEquals('Cameroun', $address->neighborhood->municipality->city->country->name);
        }
    }
}
