<?php

namespace App\Services\Ticket;

use App\Models\Ticket\Ticket;
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
        
        // Получаем параметр per_page из запроса
        $perPage = (int)$request->input('per_page', 15);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 15;
        }
        
        // Получаем результаты запроса с пагинацией
        $tickets = $query->paginate($perPage);
        
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
        $filters = $request->filters;
        
        // ИСПРАВЛЕНИЕ: Используем isset вместо !empty для более точной проверки
        if (isset($filters['id']) && $filters['id'] !== '') {
            $query->where('id', 'like', $filters['id'] . '%');
        }
        
        if (isset($filters['title']) && $filters['title'] !== '') {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }
        
        if (isset($filters['description']) && $filters['description'] !== '') {
            $query->where('description', 'like', '%' . $filters['description'] . '%');
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['priority']) && $filters['priority'] !== '') {
            $query->where('priority', $filters['priority']);
        }
        
        if ($user->hasRole('admin') && isset($filters['user_name']) && $filters['user_name'] !== '') {
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