<?php

namespace Tests\Unit\Responses;

use App\Http\Responses\ApiResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    #[Test]
    public function it_creates_success_response_correctly()
    {
        $data = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $message = 'User data retrieved successfully';

        $response = ApiResponse::success($message, $data);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $responseData['code']);
        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($data, $responseData['data']);
    }

    #[Test]
    public function it_creates_error_response_correctly()
    {
        $message = 'User not found';
        $statusCode = 404;
        $errors = ['id' => 'The requested user does not exist'];

        $response = ApiResponse::error($message, $errors, $statusCode);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(404, $responseData['code']);
        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($errors, $responseData['data']);
    }

    #[Test]
    public function it_creates_validation_error_response_correctly()
    {
        $errors = [
            'email' => ['The email field is required'],
            'password' => ['The password field is required'],
        ];

        $response = ApiResponse::validationError('Validation failed', $errors);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(422, $responseData['code']);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertEquals($errors, $responseData['data']);
    }

    #[Test]
    public function it_creates_unauthorized_response_correctly()
    {
        $message = 'Authentication required';

        $response = ApiResponse::unauthorized($message);

        $this->assertEquals(401, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(401, $responseData['code']);
        $this->assertEquals($message, $responseData['message']);
    }

    #[Test]
    public function it_creates_forbidden_response_correctly()
    {
        $message = 'Insufficient permissions';

        $response = ApiResponse::forbidden($message);

        $this->assertEquals(403, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(403, $responseData['code']);
        $this->assertEquals($message, $responseData['message']);
    }

    #[Test]
    public function it_creates_not_found_response_correctly()
    {
        $message = 'Resource not found';

        $response = ApiResponse::notFound($message);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(404, $responseData['code']);
        $this->assertEquals($message, $responseData['message']);
    }

    #[Test]
    public function it_includes_debug_info_in_development_environment()
    {
        // Set environment to local for testing
        app()['env'] = 'local';
        config(['app.debug' => true]);

        $debugData = ['exception-message' => ['message' => 'Test exception', 'file' => 'test.php', 'line' => 123]];
        $response = ApiResponse::error('An error occurred', $debugData, 500);

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('debug', $responseData);
    }

    #[Test]
    public function it_excludes_debug_info_in_production_environment()
    {
        // Set environment to production for testing
        app()['env'] = 'production';
        config(['app.debug' => false]);

        $debugData = ['exception-message' => ['message' => 'Test exception', 'file' => 'test.php', 'line' => 123]];
        $response = ApiResponse::error('An error occurred', $debugData, 500);

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('debug', $responseData);
    }
}
