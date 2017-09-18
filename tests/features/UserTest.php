<?php

use \Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function a_user_can_be_created()
    {
        $file = $this->uploadFile();
        $role = create('App\Role');
        $data = make('App\User', ['is_active' => true]);
        $additional = [
            'photo'                 => $file,
            'password'              => 'secret',
            'password_confirmation' => 'secret',
            'roles'                 => [$role->id],
        ];

        $response = $this->json('POST', '/api/users', prepare($this->token, 'post', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(201)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('users', compareId($data->toArray(), $this->getData($response)->id));
        $this->seeInDatabase('role_user', array_merge(['user_id' => $this->getData($response)->id, 'role_id' => $role->id]));
        $this->assertFileExists(storage_path(sprintf('%s/%s', 'app/users', $this->getData($response)->photo)));
        $this->deleteFile(storage_path(sprintf('%s/%s', 'app/users', $this->getData($response)->photo)));
    }

    /** @test */
    public function a_user_can_be_edited()
    {
        $file = $this->uploadFile();
        $user = create('App\User');
        $role = create('App\Role');
        $data = make('App\User');
        $additional = [
            'photo'                 => $file,
            'password'              => 'secret',
            'password_confirmation' => 'secret',
            'roles'                 => [$role->id],
        ];

        $response = $this->json('POST', "/api/users/{$user->id}", prepare($this->token, 'patch', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('users', compareId($data->toArray(), $user->id));
        $this->seeInDatabase('role_user', array_merge(['user_id' => $user->id, 'role_id' => $role->id]));
        $this->assertFileExists(storage_path(sprintf('%s/%s', 'app/users', $this->getData($response)->photo)));
        $this->deleteFile(storage_path(sprintf('%s/%s', 'app/users', $this->getData($response)->photo)));
    }

    /** @test */
    public function a_user_can_edit_his_own_profile()
    {
        $file = $this->uploadFile();
        $user = create('App\User');
        $data = make('App\User');
        $additional = [
            'photo'                 => $file,
            'password'              => 'secret',
            'password_confirmation' => 'secret',
        ];
        $token = \JWTAuth::fromUser($user);

        $response = $this->json('POST', "/api/users/profile", prepare($token, 'patch', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('users', compareId($data->toArray(), $user->id));
        $this->assertFileExists(storage_path(sprintf('%s/%s', 'app/users', $this->getData($response)->photo)));
        $this->deleteFile(storage_path(sprintf('%s/%s', 'app/users', $this->getData($response)->photo)));
    }

    /** @test */
    public function a_user_can_be_deleted()
    {
        $user = create('App\User');
        $userTwo = create('App\User');

        $response = $this->json('POST', '/api/users', prepare($this->token, 'delete', ['items' => [$user->id, $userTwo->id, hexdec(uniqid())]]))
            ->seeStatusCode(200)
            ->seeJsonStructure(['total']);

        $this->notSeeInDatabase('users', ['id' => $user->id]);
        $this->notSeeInDatabase('users', ['id' => $userTwo->id]);
        $this->assertEquals(2, $this->getData($response)->total);
    }


}