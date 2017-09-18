<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class FolderTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function a_folder_can_be_created()
    {
        $data = make('App\Folder');

        $response = $this->json('POST', '/api/folders', prepare($this->token, 'post', $data->toArray()))
            ->seeStatusCode(201)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('folders', compareId($data->toArray(), $this->getData($response)->id));
    }

    /** @test */
    public function a_folder_can_be_edited()
    {
        $folder = create('App\Folder');
        $data = make('App\Folder');
        $data = $data->toArray();
        unset($data['company_id']);

        $this->json('POST', "/api/folders/{$folder->id}", prepare($this->token, 'patch', $data))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('folders', compareId($data, $folder->id));
    }

    /** @test */
    public function a_folder_can_be_shared()
    {
        $folder = create('App\Folder');
        $contact = create('App\User', ['is_contact' => true, 'is_active' => true]);
        $contact->companies()->attach($folder->company->id);
        $data = ['contacts' => [$contact->id]];

        $this->json('POST', "/api/folders/{$folder->id}/share", prepare($this->token, 'post', $data))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->seeInDatabase('contact_folder', ['contact_id' => $contact->id, 'folder_id' => $folder->id]);
    }

    /** @test */
    public function a_folder_can_be_deleted()
    {
        $folder = create('App\Folder');
        $response = $this->json('POST', "/api/folders/{$folder->id}", prepare($this->token, 'delete'))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $this->notSeeInDatabase('folders', ['id' => $this->getData($response)->id]);
    }

    /** @test */
    public function a_folder_can_notify_about_new_documents()
    {
        $folder = create('App\Folder');
        $document = create('App\Document', ['folder_id' => $folder->id]);

        $this->json('POST', "/api/folders/{$folder->id}/notify", prepare($this->token, 'post'))
            ->seeStatusCode(200);

        $this->seeInDatabase('documents', ['id' => $document->id, 'status' => 2]);
    }
}