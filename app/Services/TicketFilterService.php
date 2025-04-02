<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Сервис для фильтрации и сортировки тикетов
 */
class TicketFilterService
{
    /**
     * Применяет фильтры и сортировку к запросу
     *
     * @param Request $request HTTP-запрос с параметрами фильтрации и сортировки
     * @return array Массив с результатами запроса и дополнительными данными
     */
    public function getFilteredTickets(Request $request): array
    {
        $user = Auth::user();
        
        // Создаем базовый запрос с загрузкой данных пользователя
        $query = Ticket::with('user');
        
        // Фильтрация по пользователю для не-админов
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }
        
        // Применяем фильтры
        $this->applyFilters($query, $request, $user);
        
        // Применяем сортировку
        $this->applySorting($query, $request);
        
        // Получаем результаты запроса
        $tickets = $query->get();
        
        // Проверяем, является ли пользователь администратором
        $isAdmin = $user->hasRole('admin');
        
        return [
            'tickets' => $tickets,
            'filters' => $request->input('filters', []),
            'sort' => [
                'field' => $request->input('sort.field', 'updated_at'),
                'order' => $request->input('sort.order', 'desc')
            ],
            'isAdmin' => $isAdmin
        ];
    }
    
    /**
     * Применяет фильтры к запросу
     *
     * @param Builder $query Запрос к базе
     * @param Request $request HTTP-запрос с параметрами фильтрации
     * @param mixed $user Текущий пользователь
     * @return Builder Модифицированный запрос
     */
    private function applyFilters(Builder $query, Request $request, $user): Builder
    {
        if (!$request->has('filters')) {
            return $query;
        }
        
        $filters = $request->filters;
        
        // Фильтрация по ID тикета
        if (!empty($filters['id'])) {
            $query->where('id', 'like', $filters['id'] . '%');
        }
        
        // Фильтрация по заголовку
        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }
        
        // Фильтрация по описанию
        if (!empty($filters['description'])) {
            $query->where('description', 'like', '%' . $filters['description'] . '%');
        }
        
        // Фильтрация по статусу (если статус не пуст)
        if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        
        // Фильтрация по приоритету (если приоритет не пуст)
        if (isset($filters['priority']) && $filters['priority'] !== null && $filters['priority'] !== '') {
            $query->where('priority', $filters['priority']);
        }
        
        // Фильтрация по имени пользователя (только для админов)
        if ($user->hasRole('admin') && !empty($filters['user_name'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['user_name'] . '%');
            });
        }
        
        return $query;
    }
    
    /**
     * Применяет сортировку к запросу
     *
     * @param Builder $query Запрос к базе
     * @param Request $request HTTP-запрос с параметрами сортировки
     * @return Builder Модифицированный запрос
     */
    private function applySorting(Builder $query, Request $request): Builder
    {
        $sortField = $request->input('sort.field', 'updated_at');
        $sortOrder = $request->input('sort.order', 'desc');
        
        if ($sortField === 'user_name') {
            // Сортировка по имени пользователя через join
            $query->join('users', 'tickets.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('tickets.*');
        } else {
            $query->orderBy($sortField, $sortOrder);
        }
        
        return $query;
    }
} 