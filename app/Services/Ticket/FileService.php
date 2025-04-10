<?php

namespace App\Services\Ticket;

use App\Models\Ticket\FollowupFile;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FileService
{
    protected string $disk = 'local';

    /**
     * Загружает файлы для указанного тикета или ответа.
     *
     * @param Model $model Модель (Ticket или Followup)
     * @param array<UploadedFile> $uploadedFiles Загружаемые файлы
     * @return void
     */
    public function uploadFiles(Model $model, array $uploadedFiles): void
    {
        if (empty($uploadedFiles)) {
            return;
        }

        $modelType = $model instanceof Ticket ? 'ticket' : 'followup';
        // Создаем директорию для файлов модели на сервере
        $directory = $modelType . '_attachments/' . $model->id;

        foreach ($uploadedFiles as $file) {
            $path = $file->store($directory, $this->disk);

            $model->files()->create([
                'user_id' => Auth::id(),
                'original_filename' => $file->getClientOriginalName(),
                'filename' => basename($path),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
    
    /**
     * Находит файл по типу и ID и возвращает его вместе с моделью для авторизации.
     *
     * @param string $fileType Тип файла ('ticket' или 'followup')
     * @param int $fileId ID файла
     * @return array{file: object, authorizationModel: Ticket}
     */
    public function findFile(string $fileType, int $fileId): array
    {
        if ($fileType === 'ticket') {
            $file = TicketFile::findOrFail($fileId);
            $authModel = $file->ticket;
        } elseif ($fileType === 'followup') {
            $file = FollowupFile::findOrFail($fileId);
            $authModel = $file->followup->ticket;
        } else {
            abort(404, 'Unsupported file type');
        }

        return [
            'file' => $file,
            'authorizationModel' => $authModel
        ];
    }

    /**
     * Проверяет существование файла и отдает его для скачивания.
     *
     * @param TicketFile|FollowupFile $file
     * @return StreamedResponse|RedirectResponse
     */
    public function downloadFile($file): StreamedResponse|RedirectResponse
    {
        if (!$this->fileExists($file->path)) {
            return redirect()->back()->withErrors(['file_error' => 'File not found on server.']);
        }

        return Storage::disk($this->disk)->download($file->path, $file->original_filename);
    }
    
    /**
     * Находит и скачивает файл с проверкой авторизации.
     *
     * @param string $fileType Тип файла ('ticket' или 'followup')
     * @param int $fileId ID файла
     * @return StreamedResponse|RedirectResponse
     */
    public function downloadFileWithAuthorization(string $fileType, int $fileId): StreamedResponse|RedirectResponse
    {
        if (!in_array($fileType, ['ticket', 'followup'])) {
            abort(404, 'Invalid file type');
        }

        $result = $this->findFile($fileType, $fileId);
        
        // Проверяем авторизацию
        Gate::authorize('view', $result['authorizationModel']);
        
        // Скачиваем файл
        return $this->downloadFile($result['file']);
    }

    /**
     * Удаляет все файлы, связанные с моделью, и их директорию.
     *
     * @param Model $model Модель (Ticket или Followup)
     * @return void
     */
    public function deleteFiles(Model $model): void
    {
        $modelType = $model instanceof Ticket ? 'ticket' : 'followup';
        $directory = $modelType . '_attachments/' . $model->id;
        
        // Проверяем наличие связанных файлов
        if ($model->files->isEmpty()) {
            // Даже если файлов нет, все равно удаляем директорию
            Storage::disk($this->disk)->deleteDirectory($directory);
            return;
        }
        
        // Сохраняем пути ко всем файлам
        $filePaths = [];
        foreach ($model->files as $file) {
            if ($file->path) {
                $filePaths[] = $file->path;
            }
        }
        
        // Удаляем каждый физический файл
        foreach ($filePaths as $path) {
            $this->deleteSingleFile($path);
        }

        // Удаляем директорию модели
        Storage::disk($this->disk)->deleteDirectory($directory);
    }

    /**
     * Удаляет один физический файл с сервера
     *
     * @param string $path
     * @return void
     */
    public function deleteSingleFile(string $path): void
    {
        if ($this->fileExists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    /**
     * Проверяет существование файла
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Валидирует файлы в соответствии с требованиями
     *
     * @param array $files Файлы для валидации
     * @return array Правила валидации
     */
    public function getFileValidationRules(): array
    {
        return [
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,zip,rar,txt|max:10240',
        ];
    }

    /**
     * Выполняет валидацию и загрузку файлов для модели
     *
     * @param Model $model Модель, к которой прикрепляются файлы
     * @param Request $request Запрос, содержащий файлы
     * @return void
     */
    public function validateAndUpload(Model $model, $request): void
    {
        // Валидируем файлы, используя стандартные правила
        $request->validate($this->getFileValidationRules());
        
        // Загружаем файлы, если они существуют
        if ($request->hasFile('files')) {
            $this->uploadFiles($model, $request->file('files'));
        }
    }
}
