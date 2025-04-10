<?php

namespace Tests\Unit\Responses;

use App\Http\Responses\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiResponseTraitTest extends TestCase
{
    use ApiResponseTrait;

    #[Test]
    public function it_returns_success_response()
    {
        $response = $this->successResponse('Success message', ['key' => 'value']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Success message', $content['message']);
        $this->assertEquals(['key' => 'value'], $content['data']);
    }

    #[Test]
    public function it_returns_error_response()
    {
        $response = $this->errorResponse('Error message', ['error' => 'details']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Error message', $content['message']);
        $this->assertEquals(['error' => 'details'], $content['data']);
    }

    #[Test]
    public function it_returns_not_found_response()
    {
        $response = $this->notFoundResponse('Not found message', ['id' => 123]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Not found message', $content['message']);
        $this->assertEquals(['id' => 123], $content['data']);
    }

    #[Test]
    public function it_returns_unauthorized_response()
    {
        $response = $this->unauthorizedResponse('Unauthorized message');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized message', $content['message']);
    }

    #[Test]
    public function it_returns_validation_error_response()
    {
        $response = $this->validationErrorResponse('Validation message', ['field' => 'Invalid']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Validation message', $content['message']);
        $this->assertEquals(['field' => 'Invalid'], $content['data']);
    }

    #[Test]
    public function it_returns_forbidden_response()
    {
        $response = $this->forbiddenResponse('Forbidden message');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Forbidden message', $content['message']);
    }

    #[Test]
    public function it_returns_server_error_response()
    {
        $response = $this->serverErrorResponse('Server error message');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Server error message', $content['message']);
    }
}
