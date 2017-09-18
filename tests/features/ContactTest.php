<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function a_contact_can_be_created()
    {
        $company = create('App\Company');
        $address = make('App\ContactAddress');

        $data = make('App\User', ['is_contact' => true, 'is_active' => false]);
        $additional = array_merge([
            'companies' => [$company->id],
            'phones'    => [
                [
                    'number' => '(99) 9999-9999',
                    'type'   => '0',
                ]
            ]
        ], $address->toArray());

        $response = $this->json('POST', '/api/contacts', prepare($this->token, 'post', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(201)
            ->seeJsonStructure(['id']);

        $contactId = $this->getData($response)->id;
        $this->assertTrue($this->getData($response)->contact);
        $this->assertFalse($this->getData($response)->active);
        $this->seeInDatabase('users', compareId($data->toArray(), $contactId));
        $this->seeInDatabase('company_contact', ['company_id' => $company->id, 'contact_id' => $contactId]);
        $this->seeInDatabase('contacts_phones', array_merge($additional['phones'][0], ['contact_id' => $contactId]));
        $this->seeInDatabase('contacts_address', array_merge($address->toArray(), ['contact_id' => $contactId]));
    }

    /** @test */
    public function a_contact_can_be_edited()
    {
        $contact = create('App\User', ['is_contact' => true, 'is_active' => false]);
        $company = create('App\Company');
        $address = make('App\ContactAddress');

        $data = make('App\User', ['is_contact' => true, 'is_active' => false]);
        $additional = array_merge([
            'companies' => [$company->id],
            'phones'    => [
                [
                    'number' => '(99) 9999-9999',
                    'type'   => '0',
                ]
            ]
        ], $address->toArray());

        $response = $this->json('POST', "/api/contacts/{$contact->id}", prepare($this->token, 'patch', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->assertTrue($this->getData($response)->contact);
        $this->assertFalse($this->getData($response)->active);
        $this->seeInDatabase('users', compareId($data->toArray(), $contact->id));
        $this->seeInDatabase('company_contact', ['company_id' => $company->id, 'contact_id' => $contact->id]);
        $this->seeInDatabase('contacts_phones', array_merge($additional['phones'][0], ['contact_id' => $contact->id]));
        $this->seeInDatabase('contacts_address', array_merge($address->toArray(), ['contact_id' => $contact->id]));
    }

    /** @test */
    public function a_contact_can_be_deleted()
    {
        $contact = create('App\User', ['is_contact' => true, 'is_active' => false]);
        $contactTwo = create('App\User', ['is_contact' => true, 'is_active' => false]);

        $response = $this->json('POST', '/api/contacts/', prepare($this->token, 'delete', ['items' => [$contact->id, $contactTwo->id, hexdec(uniqid())]]))
            ->seeStatusCode(200)
            ->seeJsonStructure(['total']);

        $this->notSeeInDatabase('users', ['id' => $contact->id]);
        $this->notSeeInDatabase('users', ['id' => $contactTwo->id]);
        $this->assertEquals(2, $this->getData($response)->total);
    }

    /** @test */
    public function a_contact_can_be_activated_as_user()
    {
        $contact = create('App\User', ['is_contact' => true, 'is_active' => false]);

        $response = $this->json('POST', "/api/contacts/{$contact->id}/activate", prepare($this->token, 'post', $contact->toArray()))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->assertTrue($this->getData($response)->contact);
        $this->assertTrue($this->getData($response)->active);
    }
    
    /** @test */
    public function a_contact_can_be_disabled()
    {
        $contact = create('App\User', ['is_contact' => true, 'is_active' => true]);

        $response = $this->json('POST', "/api/contacts/{$contact->id}/disable", prepare($this->token, 'post'))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->assertFalse($this->getData($response)->active);
    }
}