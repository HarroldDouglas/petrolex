<?php

namespace Tests\Unit\DTOs;

use App\DTOs\Customer\CreateCustomerDTO;
use PHPUnit\Framework\TestCase;

class CreateCustomerDTOTest extends TestCase
{
    public function test_can_create_customer_dto_with_all_properties()
    {
        $dto = new CreateCustomerDTO(
            first_name: 'Jean',
            last_name: 'Dupont',
            email: 'jean.dupont@example.com',
            phone_number: '+237699123456',
            password: 'password123',
            language: 'fr',
            address: '123 Rue Principale',
            current_balance: 100.50
        );

        $this->assertEquals('Jean', $dto->first_name);
        $this->assertEquals('Dupont', $dto->last_name);
        $this->assertEquals('jean.dupont@example.com', $dto->email);
        $this->assertEquals('+237699123456', $dto->phone_number);
        $this->assertEquals('password123', $dto->password);
        $this->assertEquals('fr', $dto->language);
        $this->assertEquals('123 Rue Principale', $dto->address);
        $this->assertEquals(100.50, $dto->current_balance);
    }

    public function test_can_create_customer_dto_with_required_properties_only()
    {
        $dto = new CreateCustomerDTO(
            first_name: 'John',
            last_name: 'Smith',
            email: 'john.smith@example.com',
            phone_number: '+237699654321',
            password: 'password123'
        );

        $this->assertEquals('John', $dto->first_name);
        $this->assertEquals('Smith', $dto->last_name);
        $this->assertEquals('john.smith@example.com', $dto->email);
        $this->assertEquals('+237699654321', $dto->phone_number);
        $this->assertEquals('password123', $dto->password);
        $this->assertNull($dto->language);
        $this->assertNull($dto->address);
        $this->assertEquals(0.0, $dto->current_balance);
    }

    public function test_language_can_be_english()
    {
        $dto = new CreateCustomerDTO(
            first_name: 'Alice',
            last_name: 'Johnson',
            email: 'alice@example.com',
            phone_number: '+237699999999',
            password: 'password123',
            language: 'en'
        );

        $this->assertEquals('en', $dto->language);
    }

    public function test_language_can_be_null()
    {
        $dto = new CreateCustomerDTO(
            first_name: 'Bob',
            last_name: 'Wilson',
            email: 'bob@example.com',
            phone_number: '+237699888888',
            password: 'password123',
            language: null
        );

        $this->assertNull($dto->language);
    }
}
