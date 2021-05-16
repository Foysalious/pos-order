<?php

namespace App\Http\Controllers;

use App\Traits\ResponseAPI;
use Illuminate\Http\Request;



class TestController extends Controller
{
    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="POS Order API Documentation",
     *      description="L5 Swagger OpenApi description",
     *      @OA\Contact(
     *          email="admin@admin.com"
     *      )
     * )
     *
     * @OA\Server(
     *      url=L5_SWAGGER_CONST_HOST,
     *      description="API Server"
     * )
     */

    use ResponseAPI;

    /**
     * @OA\Get(
     *      path="/api/v1/greetings",
     *      operationId="getGreeting",
     *      tags={"SWAGGER TEST API"},
     *      summary="Api to get all the greetings",
     *      description="Return all types of greetings stored",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function getGreetings()
    {
        $return_data = [ 'greeting' => ['Hello', 'hi-ya', 'hey' ] ];
        return $this->success('Successful', $return_data);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *      path="/api/v1/create-greeting",
     *      operationId="creatinggreeting",
     *      tags={"SWAGGER TEST API"},
     *      summary="To create a greeting",
     *      description="creating greeting description",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\Schema (ref="{}")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function createGreeting(Request $request)
    {
        return $this->success('greetings created',[]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put (
     *      path="/api/v1/update-greeting",
     *      operationId="updatinggreeting",
     *      tags={"SWAGGER TEST API"},
     *      summary="To update a greeting",
     *      description="updating greeting description",
     *      @OA\Parameter (
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *          type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function update(Request $request)
    {
        return $this->success('greetings updated',[]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete (
     *      path="/api/v1/delete-greeting",
     *      operationId="deletinggreeting",
     *      tags={"SWAGGER TEST API"},
     *      summary="To delete a greeting",
     *      description="deleting greeting description",
     *      @OA\Parameter (
     *          name="id",
     *          required=true,
     *          in="path",
     *      @OA\Schema(
     *          type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function delete($id)
    {
        return $this->success('greetings deleted',[]);
    }
}
