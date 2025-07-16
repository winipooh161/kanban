@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Настройки уведомлений') }}</div>

                <div class="card-body">
                    <div class="mb-3">
                        <h5>Статус push-уведомлений</h5>
                        <p id="notification-status" class="text-muted">Проверяем поддержку уведомлений...</p>
                        
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success" id="enable-notifications" style="display: none;">
                                Включить уведомления
                            </button>
                            <button type="button" class="btn btn-danger" id="disable-notifications" style="display: none;">
                                Отключить уведомления
                            </button>
                            <button type="button" class="btn btn-info" id="test-notification">
                                Тестовое уведомление
                            </button>
                        </div>
                    </div>

                    <hr>

                    <form id="notification-settings-form">
                        @csrf
                        <h5>Типы уведомлений</h5>
                        <p class="text-muted">Выберите, какие уведомления вы хотите получать:</p>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="task_assigned" name="task_assigned" checked>
                            <label class="form-check-label" for="task_assigned">
                                <strong>Назначение задач</strong>
                                <br><small class="text-muted">Когда вам назначается новая задача</small>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="task_due_soon" name="task_due_soon" checked>
                            <label class="form-check-label" for="task_due_soon">
                                <strong>Приближающиеся дедлайны</strong>
                                <br><small class="text-muted">За день до срока выполнения задачи</small>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="task_overdue" name="task_overdue" checked>
                            <label class="form-check-label" for="task_overdue">
                                <strong>Просроченные задачи</strong>
                                <br><small class="text-muted">Когда задача просрочена</small>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="task_comments" name="task_comments" checked>
                            <label class="form-check-label" for="task_comments">
                                <strong>Комментарии к задачам</strong>
                                <br><small class="text-muted">Новые комментарии к вашим задачам</small>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="task_moved" name="task_moved">
                            <label class="form-check-label" for="task_moved">
                                <strong>Перемещение задач</strong>
                                <br><small class="text-muted">Когда ваши задачи перемещаются между колонками</small>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="column_updated" name="column_updated">
                            <label class="form-check-label" for="column_updated">
                                <strong>Обновления колонок</strong>
                                <br><small class="text-muted">Изменения в структуре доски</small>
                            </label>
                        </div>

                        <hr>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Сохранить настройки
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/notification-settings.js') }}"></script>
@endpush
@endsection
