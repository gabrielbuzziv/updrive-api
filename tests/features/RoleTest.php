<?php

use \Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function a_role_can_be_created()
    {
        $permission = create('App\Permission');
        $data = make('App\Role');
        $additional = [
            'permissions' => [$permission->id]
        ];

        $response = $this->json('POST', '/api/roles', prepare($this->token, 'post', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(201)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('roles', compareId($data->toArray(), $this->getData($response)->id));
        $this->seeInDatabase('permission_role', ['role_id' => $this->getData($response)->id, 'permission_id' => $permission->id]);
    }

    /** @test */
    public function a_role_can_be_edited()
    {
        $permission = create('App\Permission');
        $role = create('App\Role');
        $data = make('App\Role');
        $additional = [
            'permissions' => [$permission->id]
        ];

        $this->json('POST', "/api/roles/{$role->id}", prepare($this->token, 'patch', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('roles', compareId($data->toArray(), $role->id));
        $this->seeInDatabase('permission_role', ['role_id' => $role->id, 'permission_id' => $permission->id]);
    }

    /** @test */
    public function a_role_can_be_deleted()
    {
        $role = create('App\Role');
        $roleTwo = create('App\Role');

        $response = $this->json('POST', '/api/roles/', prepare($this->token, 'delete', ['items' => [$role->id, $roleTwo->id, hexdec(uniqid())]]))
            ->seeStatusCode(200)
            ->seeJsonStructure(['total']);

        $this->notSeeInDatabase('roles', ['id' => $role->id]);
        $this->notSeeInDatabase('roles', ['id' => $roleTwo->id]);
        $this->assertEquals(2, $this->getData($response)->total);
    }
}