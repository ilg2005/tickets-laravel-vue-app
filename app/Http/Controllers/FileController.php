<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;

class FileController extends Controller
{
    /**
     * Сервис для работы с файлами
     *
     * @var FileService
     */
    protected FileService $fileService;

    /**
     * Конструктор контроллера с внедрением зависимости FileService
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Обрабатывает запрос на скачивание файла.
     *
     * @param string $fileType Тип файла ('ticket' или 'followup')
     * @param int $fileId ID файла
     * @return StreamedResponse|RedirectResponse
     */
    public function download(string $fileType, int $fileId)
    {
        if (!in_array($fileType, ['ticket', 'followup'])) {
            abort(404, 'Invalid file type');
        }

        $result = $this->fileService->findFile($fileType, $fileId);
        
        // Проверяем авторизацию
        Gate::authorize('view', $result['authorizationModel']);
        
        // Скачиваем файл
        return $this->fileService->downloadFile($result['file']);
    }
} 