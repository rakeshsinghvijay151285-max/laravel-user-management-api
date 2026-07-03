<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FileUploadRequest;
use App\Http\Resources\UserFileResource;
use App\Services\FileUploadService;
use Illuminate\Http\Response;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
        $this->middleware('auth:api');
    }

    /**
     * Upload file
     */
    public function store(FileUploadRequest $request)
    {
        try {
            $file = $this->fileUploadService->uploadFile(
                auth('api')->user(),
                $request->file('file'),
                $request->get('description')
            );

            return response()->json([
                'status' => true,
                'message' => 'File uploaded successfully',
                'data' => new UserFileResource($file),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user files
     */
    public function index()
    {
        try {
            $files = $this->fileUploadService->getUserFiles(auth('api')->user()->id);

            return response()->json([
                'status' => true,
                'message' => 'Files retrieved successfully',
                'data' => UserFileResource::collection($files),
                'pagination' => [
                    'total' => $files->total(),
                    'per_page' => $files->perPage(),
                    'current_page' => $files->currentPage(),
                    'last_page' => $files->lastPage(),
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete file
     */
    public function destroy($fileId)
    {
        try {
            $file = $this->fileUploadService->deleteFile($fileId, auth('api')->user()->id);

            if (!$file) {
                return response()->json([
                    'status' => false,
                    'message' => 'File not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => true,
                'message' => 'File deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Download file
     */
    public function download($fileId)
    {
        try {
            $file = $this->fileUploadService->downloadFile($fileId, auth('api')->user()->id);

            if (!$file) {
                return response()->json([
                    'status' => false,
                    'message' => 'File not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return $file;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
