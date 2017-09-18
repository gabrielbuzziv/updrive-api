<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PermissionTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function a_permission_can_be_created()
    {
        $data = make('App\Permission');

        $response = $this->json('POST', '/api/permissions', prepare($this->token, 'post', $data->toArray()))
            ->seeStatusCode(201)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('permissions', compareId($data->toArray(), $this->getData($response)->id));
    }

    /** @test */
    public function a_permission_can_be_edited()
    {
        $permission = create('App\Permission');
        $data = make('App\Permission');

        $response = $this->json('POST', "/api/permissions/{$permission->id}", prepare($this->token, 'patch', $data->toArray()))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('permissions', compareId($this->getData($response, true), $permission->id));
    }

    /** @test */
    public function a_permission_can_be_deleted()
    {
        $permission = create('App\Permission');
        $permissionTwo = create('App\Permission');

        $response = $this->json('POST', '/api/permissions/', prepare($this->token, 'delete', ['items' => [$permission->id, $permissionTwo->id, hexdec(uniqid())]]))
            ->seeStatusCode(200)
            ->seeJsonStructure(['total']);

        $this->notSeeInDatabase('permissions', ['id' => $permission->id]);
        $this->notSeeInDatabase('permissions', ['id' => $permissionTwo->id]);
        $this->assertEquals(2, $this->getData($response)->total);
    }
}