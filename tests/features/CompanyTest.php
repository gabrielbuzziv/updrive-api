<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class CompanyTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function a_company_can_be_created()
    {
        $data = make('App\Company');
        $additional = make('App\CompanyAddress');

        $response = $this->json('POST', '/api/companies', prepare($this->token, 'post', array_merge($data->toArray(), $additional->toArray())))
            ->seeStatusCode(201)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('companies', compareId($data->toArray(), $this->getData($response)->id));
        $this->seeInDatabase('companies_address', array_merge($additional->toArray(), ['company_id' => $this->getData($response)->id]));
    }

    /** @test */
    public function a_company_can_be_edited()
    {
        $company = create('App\Company');
        $data = make('App\Company');
        $additional = make('App\CompanyAddress');

        $this->json('POST', "/api/companies/{$company->id}", prepare($this->token, 'patch', array_merge($data->toArray(), $additional->toArray())))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('companies', compareId($data->toArray(), $company->id));
        $this->seeInDatabase('companies_address', array_merge($additional->toArray(), ['company_id' => $company->id]));
    }

    /** @test */
    public function a_company_can_be_deleted()
    {
        $company = create('App\Company');
        $companyTwo = create('App\Company');

        $response = $this->json('POST', '/api/companies/', prepare($this->token, 'delete', ['items' => [$company->id, $companyTwo->id, hexdec(uniqid())]]))
            ->seeStatusCode(200)
            ->seeJsonStructure(['total']);

        $this->notSeeInDatabase('companies', ['id' => $company->id]);
        $this->notSeeInDatabase('companies', ['id' => $companyTwo->id]);
        $this->assertEquals(2, $this->getData($response)->total);
    }

    /** @test */
    public function a_company_can_be_fetched_as_a_list()
    {
        $query = str_random();
        $company = create('App\Company', ['name' => $query]);
        $response = $this->json('GET', '/api/companies/list', ['token' => $this->token, 'q' => $query])
            ->seeStatusCode(200)
            ->seeJsonStructure(['items' => ['*' => ['value', 'label']]]);

        $this->assertEquals($company->id, $this->getData($response)->items[0]->value);
    }
}