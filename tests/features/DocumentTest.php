<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class DocumentTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function a_document_can_be_uploaded()
    {
        $file = $this->uploadFile();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $data = make('App\Document', ['name' => $name, 'user_id' => $this->user->id]);
        $additional = ['file' => $file];

        $response = $this->json('POST', '/api/documents/upload', prepare($this->token, 'post', array_merge($data->toArray(), $additional)))
            ->seeStatusCode(201)
            ->seeJsonStructure(['id']);


        $data = $this->convertData($data->toArray());
        $data['filename'] = $this->getData($response)->filename;
        unset($data['cycle']);
        unset($data['validity']);
        unset($data['note']);

        $this->seeInDatabase('documents', compareId($data, $this->getData($response)->id));

        $path = storage_path(sprintf('app/updrive/%s', $this->getData($response)->filename));
        $this->assertFileExists($path);
        $this->deleteFile($path);
    }

    /** @test */
    public function a_document_can_be_edited()
    {
        $document = create('App\Document');
        $data = make('App\Document');

        $data = $data->toArray();
        unset($data['user_id']);
        unset($data['folder_id']);
        unset($data['filename']);
        unset($data['status']);

        $this->json('POST', "/api/documents/{$document->id}", prepare($this->token, 'patch', $data))
            ->seeStatusCode(200)
            ->seeJsonStructure(['id']);

        $data = $this->convertData($data);
        $this->seeInDatabase('documents', compareId($data, $document->id));
    }

    /** @test */
    public function a_document_can_be_deleted()
    {
        $file = $this->uploadFile(storage_path('app/updrive'));
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $document = create('App\Document', ['name' => $name, 'filename' => $file->getClientOriginalName()]);

        $this->json('POST', "/api/documents/{$document->id}", prepare($this->token, 'delete'))
            ->seeStatusCode(200)
            ->seeJsonStructure(['deleted']);

        $this->notSeeInDatabase('documents', ['id' => $document->id]);
        $this->assertFileNotExists(storage_path(sprintf('%s/%s', 'app/updrive', $file->getClientOriginalName())));
    }

    /**
     * Convert the status from data array.
     *
     * @param $data
     * @return mixed
     */
    private function convertData($data)
    {
        if (isset($data['status'])) {
            $data['status'] = $data['status']['id'];
        }

        if (isset($data['cycle'])) {
            $data['cycle'] = \Carbon\Carbon::createFromFormat('m/Y', $data['cycle'])->day(1)->format('Y-m-d');
        }

        if (isset($data['validity'])) {
            $data['validity'] = \Carbon\Carbon::createFromFormat('d/m/Y', $data['validity'])->format('Y-m-d');
        }

        return $data;
    }

}