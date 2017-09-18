<?php

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * The authenticated user.
     *
     * @var
     */
    protected $user;

    /**
     * The authentication token.
     *
     * @var
     */
    protected $token;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Before each test set an authenticated user.
     */
    protected function setUp()
    {
        parent::setUp();

        $roles = \App\Role::pluck('id')->all();
        $this->user = factory(\App\User::class)->create();
        $this->user->roles()->sync($roles);

        $this->token = \JWTAuth::fromUser($this->user);
        $this->actingAs($this->user);
    }

    /**
     * Clear the data array.
     *
     * @param $data
     * @param $discard
     * @return array
     */
    protected function clearData($data, $discard = ['token', '_method'])
    {
        return array_filter($data, function ($key) use ($discard) {
            return ! in_array($key, $discard);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the content from response.
     *
     * @param $response
     * @return mixed
     */
    protected function getData($response, $isArray = false)
    {
        if ($isArray) {
            return (array) json_decode($response->response->getContent());
        }

        return json_decode($response->response->getContent());
    }

    /**
     * Upload a file and test it.
     *
     * @param $uploadTo
     * @return UploadedFile
     */
    protected function uploadFile($uploadTo = null)
    {
        $filepath = sprintf('%s/%s', __DIR__, 'test.jpg');
        $filename = sprintf('%s.jpg', str_random());
        $path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
        copy($filepath, $path);

        if ($uploadTo) {
            $path = sprintf('%s/%s', $uploadTo, $filename);
            copy($filepath, $path);
        }

        return new \Illuminate\Http\UploadedFile($path, $filename, 'image/jpeg', filesize($path), null, true);
    }

    /**
     * Delete file from folder.
     *
     * @param $path
     */
    protected function deleteFile($path)
    {
        @unlink($path);
    }
}
