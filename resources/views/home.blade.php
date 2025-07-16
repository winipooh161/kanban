@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2 class="float-start">Kanban-доска</h2>
            <button class="btn btn-primary float-end" id="addColumnModal">
                <i class="fas fa-plus"></i> Добавить колонку
            </button>
        </div>
    </div>
    
    <div class="kanban-board">
        <div class="row flex-nowrap overflow-auto pb-3" style="height: 100vh" id="kanbanColumns">
            @foreach($columns as $column)
            <div class="col-md-3 kanban-column" data-column-id="{{ $column->id }}">
                <div class="card" style="height: 100%;">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: {{ $column->color }}">
                        <h5 class="mb-0">{{ $column->title }}</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item edit-column" href="#" data-column-id="{{ $column->id }}" data-column-title="{{ $column->title }}" data-column-color="{{ $column->color }}">
                                    <i class="fas fa-edit"></i> Редактировать
                                </a>
                                <a class="dropdown-item delete-column" href="#" data-column-id="{{ $column->id }}">
                                    <i class="fas fa-trash"></i> Удалить
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="task-list" data-column-id="{{ $column->id }}">
                            @foreach($column->tasks as $task)
                            <div class="card mb-2 task-card" data-task-id="{{ $task->id }}">
                                <div class="card-body p-2" style="{{ $task->color ? 'border-left: 5px solid '.$task->color : '' }}">
                                    <h6 class="card-title mb-1">{{ $task->title }}</h6>
                                    @if($task->description)
                                    <p class="card-text small text-muted mb-1">{{ \Illuminate\Support\Str::limit($task->description, 50) }}</p>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($task->due_date)
                                        <small class="text-{{ \Carbon\Carbon::parse($task->due_date)->isPast() ? 'danger' : 'muted' }}">
                                            <i class="far fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($task->due_date)->format('d.m.Y') }}
                                        </small>
                                        @endif
                                        <div class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
                                            {{ $task->priority }}
                                        </div>
                                    </div>
                                    @if($task->assignedTo)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> Назначено: {{ $task->assignedTo->name }}
                                        </small>
                                    </div>
                                    @endif
                                    <div class="task-actions mt-2">
                                        <button class="btn btn-sm btn-outline-primary edit-task" data-task-id="{{ $task->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-task" data-task-id="{{ $task->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button class="btn btn-sm btn-outline-secondary w-100 mt-2 add-task" data-column-id="{{ $column->id }}">
                            <i class="fas fa-plus"></i> Добавить задачу
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Модальное окно для добавления/редактирования задачи -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Добавить задачу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <input type="hidden" id="taskId">
                    <input type="hidden" id="columnId">
                    
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Название задачи</label>
                        <input type="text" class="form-control" id="taskTitle" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="taskDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskAssignedTo" class="form-label">Назначить пользователю</label>
                        <select class="form-select" id="taskAssignedTo">
                            <option value="">-- Выберите пользователя --</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskDueDate" class="form-label">Срок выполнения</label>
                        <input type="date" class="form-control" id="taskDueDate">
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskPriority" class="form-label">Приоритет</label>
                        <select class="form-select" id="taskPriority">
                            <option value="low">Низкий</option>
                            <option value="medium" selected>Средний</option>
                            <option value="high">Высокий</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskColor" class="form-label">Цвет метки</label>
                        <input type="color" class="form-control form-control-color" id="taskColor" value="#3498db">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveTask">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления/редактирования колонки -->
<div class="modal fade" id="columnModal" tabindex="-1" aria-labelledby="columnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="columnModalLabel">Добавить колонку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="columnForm">
                    <input type="hidden" id="columnFormId">
                    
                    <div class="mb-3">
                        <label for="columnTitle" class="form-label">Название колонки</label>
                        <input type="text" class="form-control" id="columnTitle" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="columnColor" class="form-label">Цвет колонки</label>
                        <input type="color" class="form-control form-control-color" id="columnColor" value="#f0f0f0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveColumn">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmText">Вы уверены, что хотите удалить этот элемент?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Удалить</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // CSRF токен для Ajax запросов
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Загружаем список пользователей при загрузке страницы
        loadUsers();
        
        // Инициализация Sortable для колонок
        const columnList = document.getElementById('kanbanColumns');
        if (columnList) {
            new Sortable(columnList, {
                animation: 150,
                handle: '.card-header',
                onEnd: function() {
                    updateColumnOrder();
                }
            });
        }
        
        // Инициализация Sortable для задач в каждой колонке
        document.querySelectorAll('.task-list').forEach(taskList => {
            new Sortable(taskList, {
                group: 'tasks',
                animation: 150,
                onEnd: function(evt) {
                    const taskId = evt.item.getAttribute('data-task-id');
                    const newColumnId = evt.to.getAttribute('data-column-id');
                    const tasks = [];
                    
                    // Собираем информацию о порядке задач в новой колонке
                    evt.to.querySelectorAll('.task-card').forEach((task, index) => {
                        tasks.push({
                            id: task.getAttribute('data-task-id'),
                            column_id: newColumnId,
                            order: index
                        });
                    });
                    
                    // Обновляем порядок задач на сервере
                    updateTaskPositions(tasks);
                }
            });
        });
        
        // Обработчики событий для задач
        document.querySelectorAll('.add-task').forEach(button => {
            button.addEventListener('click', function() {
                const columnId = this.getAttribute('data-column-id');
                openTaskModal(null, columnId);
            });
        });
        
        document.querySelectorAll('.edit-task').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.getAttribute('data-task-id');
                fetchTaskAndOpenModal(taskId);
            });
        });
        
        document.querySelectorAll('.delete-task').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.getAttribute('data-task-id');
                openDeleteConfirmModal('task', taskId);
            });
        });
        
        // Обработчики событий для колонок
        const addColumnButton = document.getElementById('addColumnModal');
        if (addColumnButton) {
            addColumnButton.addEventListener('click', function() {
                openColumnModal();
            });
        }
        
        document.querySelectorAll('.edit-column').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const columnId = this.getAttribute('data-column-id');
                const title = this.getAttribute('data-column-title');
                const color = this.getAttribute('data-column-color');
                openColumnModal(columnId, title, color);
            });
        });
        
        document.querySelectorAll('.delete-column').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const columnId = this.getAttribute('data-column-id');
                openDeleteConfirmModal('column', columnId);
            });
        });
        
        // Обработчик сохранения задачи
        const saveTaskButton = document.getElementById('saveTask');
        if (saveTaskButton) {
            saveTaskButton.addEventListener('click', saveTask);
        }
        
        // Обработчик сохранения колонки
        const saveColumnButton = document.getElementById('saveColumn');
        if (saveColumnButton) {
            saveColumnButton.addEventListener('click', saveColumn);
        }
        
        // Обработчик подтверждения удаления
        const confirmDeleteButton = document.getElementById('confirmDelete');
        if (confirmDeleteButton) {
            confirmDeleteButton.addEventListener('click', confirmDelete);
        }
        
        // Функция для загрузки списка пользователей
        function loadUsers() {
            fetch('/users')
                .then(response => response.json())
                .then(users => {
                    const select = document.getElementById('taskAssignedTo');
                    select.innerHTML = '<option value="">-- Выберите пользователя --</option>';
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading users:', error));
        }
        
        // Функция для открытия модального окна задачи
        function openTaskModal(taskId = null, columnId = null) {
            const modalElement = document.getElementById('taskModal');
            const modal = window.bootstrap ? new bootstrap.Modal(modalElement) : null;
            
            if (!modal) {
                console.error('Bootstrap is not loaded');
                return;
            }
            
            const form = document.getElementById('taskForm');
            form.reset();
            
            document.getElementById('taskModalLabel').textContent = taskId ? 'Редактировать задачу' : 'Добавить задачу';
            document.getElementById('taskId').value = taskId || '';
            document.getElementById('columnId').value = columnId || '';
            
            modal.show();
        }
        
        // Функция для получения данных задачи и открытия модального окна
        function fetchTaskAndOpenModal(taskId) {
            fetch(`/tasks/${taskId}`)
                .then(response => response.json())
                .then(task => {
                    openTaskModal(task.id, task.column_id);
                    document.getElementById('taskTitle').value = task.title;
                    document.getElementById('taskDescription').value = task.description || '';
                    document.getElementById('taskAssignedTo').value = task.assigned_to_id || '';
                    document.getElementById('taskDueDate').value = task.due_date || '';
                    document.getElementById('taskPriority').value = task.priority;
                    document.getElementById('taskColor').value = task.color || '#3498db';
                })
                .catch(error => console.error('Error fetching task:', error));
        }
        
        // Функция для сохранения задачи
        function saveTask() {
            const taskId = document.getElementById('taskId').value;
            const columnId = document.getElementById('columnId').value;
            const title = document.getElementById('taskTitle').value;
            const description = document.getElementById('taskDescription').value;
            const assignedToId = document.getElementById('taskAssignedTo').value;
            const dueDate = document.getElementById('taskDueDate').value;
            const priority = document.getElementById('taskPriority').value;
            const color = document.getElementById('taskColor').value;
            
            const data = {
                title,
                description,
                column_id: columnId,
                assigned_to_id: assignedToId || null,
                due_date: dueDate || null,
                priority,
                color
            };
            
            const url = taskId ? `/tasks/${taskId}` : '/tasks';
            const method = taskId ? 'PUT' : 'POST';
            
            fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(task => {
                // Закрываем модальное окно
                const modalElement = document.getElementById('taskModal');
                const modal = window.bootstrap ? bootstrap.Modal.getInstance(modalElement) : null;
                if (modal) {
                    modal.hide();
                }
                
                // Перезагружаем страницу для обновления данных
                window.location.reload();
            })
            .catch(error => console.error('Error saving task:', error));
        }
        
        // Функция для открытия модального окна колонки
        function openColumnModal(columnId = null, title = '', color = '#f0f0f0') {
            const modalElement = document.getElementById('columnModal');
            const modal = window.bootstrap ? new bootstrap.Modal(modalElement) : null;
            
            if (!modal) {
                console.error('Bootstrap is not loaded');
                return;
            }
            
            const form = document.getElementById('columnForm');
            form.reset();
            
            document.getElementById('columnModalLabel').textContent = columnId ? 'Редактировать колонку' : 'Добавить колонку';
            document.getElementById('columnFormId').value = columnId || '';
            document.getElementById('columnTitle').value = title;
            document.getElementById('columnColor').value = color;
            
            modal.show();
        }
        
        // Функция для сохранения колонки
        function saveColumn() {
            const columnId = document.getElementById('columnFormId').value;
            const title = document.getElementById('columnTitle').value;
            const color = document.getElementById('columnColor').value;
            
            const data = {
                title,
                color
            };
            
            const url = columnId ? `/columns/${columnId}` : '/columns';
            const method = columnId ? 'PUT' : 'POST';
            
            fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(column => {
                // Закрываем модальное окно
                const modalElement = document.getElementById('columnModal');
                const modal = window.bootstrap ? bootstrap.Modal.getInstance(modalElement) : null;
                if (modal) {
                    modal.hide();
                }
                
                // Перезагружаем страницу для обновления данных
                window.location.reload();
            })
            .catch(error => console.error('Error saving column:', error));
        }
        
        // Функция для обновления порядка колонок
        function updateColumnOrder() {
            const columns = [];
            document.querySelectorAll('.kanban-column').forEach((column, index) => {
                columns.push({
                    id: column.getAttribute('data-column-id'),
                    order: index
                });
            });
            
            fetch('/columns/order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ columns })
            })
            .catch(error => console.error('Error updating column order:', error));
        }
        
        // Функция для обновления позиций задач
        function updateTaskPositions(tasks) {
            fetch('/tasks/position', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ tasks })
            })
            .catch(error => console.error('Error updating task positions:', error));
        }
        
        // Функция для открытия модального окна подтверждения удаления
        function openDeleteConfirmModal(type, id) {
            const modalElement = document.getElementById('deleteConfirmModal');
            const modal = window.bootstrap ? new bootstrap.Modal(modalElement) : null;
            
            if (!modal) {
                console.error('Bootstrap is not loaded');
                return;
            }
            
            const confirmText = type === 'task' 
                ? 'Вы уверены, что хотите удалить эту задачу?' 
                : 'Вы уверены, что хотите удалить эту колонку? Все задачи в этой колонке также будут удалены.';
            
            document.getElementById('deleteConfirmText').textContent = confirmText;
            document.getElementById('confirmDelete').setAttribute('data-type', type);
            document.getElementById('confirmDelete').setAttribute('data-id', id);
            
            modal.show();
        }
        
        // Функция для подтверждения удаления
        function confirmDelete() {
            const type = this.getAttribute('data-type');
            const id = this.getAttribute('data-id');
            const url = type === 'task' ? `/tasks/${id}` : `/columns/${id}`;
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                // Закрываем модальное окно
                const modalElement = document.getElementById('deleteConfirmModal');
                const modal = window.bootstrap ? bootstrap.Modal.getInstance(modalElement) : null;
                if (modal) {
                    modal.hide();
                }
                
                // Перезагружаем страницу для обновления данных
                window.location.reload();
            })
            .catch(error => console.error(`Error deleting ${type}:`, error));
        }
        
        // PWA и офлайн функциональность
        initPWAFeatures();
        
        function initPWAFeatures() {
            // Обработка статуса онлайн/офлайн
            function updateOnlineStatus() {
                const isOnline = navigator.onLine;
                const statusElement = document.getElementById('connection-status');
                
                if (!statusElement) {
                    // Создаем элемент статуса подключения, если его нет
                    const status = document.createElement('div');
                    status.id = 'connection-status';
                    status.style.cssText = `
                        position: fixed;
                        top: 10px;
                        right: 10px;
                        padding: 10px 15px;
                        border-radius: 5px;
                        color: white;
                        font-weight: bold;
                        z-index: 10000;
                        transition: all 0.3s ease;
                    `;
                    document.body.appendChild(status);
                }
                
                const statusEl = document.getElementById('connection-status');
                if (isOnline) {
                    statusEl.style.backgroundColor = '#28a745';
                    statusEl.textContent = 'Онлайн';
                    setTimeout(() => {
                        statusEl.style.display = 'none';
                    }, 3000);
                } else {
                    statusEl.style.backgroundColor = '#dc3545';
                    statusEl.textContent = 'Офлайн';
                    statusEl.style.display = 'block';
                }
            }
            
            // Слушатели событий для статуса сети
            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            
            // Показ кнопки установки PWA
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                
                // Показываем кнопку установки
                const installBtn = document.createElement('button');
                installBtn.textContent = 'Установить приложение';
                installBtn.className = 'btn btn-success position-fixed';
                installBtn.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000;';
                installBtn.addEventListener('click', () => {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('Пользователь установил PWA');
                        }
                        deferredPrompt = null;
                        installBtn.remove();
                    });
                });
                document.body.appendChild(installBtn);
            });
            
            // Обработка кэширования данных для офлайн работы
            if ('serviceWorker' in navigator && 'caches' in window) {
                // Кэшируем текущие данные канбан-доски
                caches.open('kanban-data-v1').then(cache => {
                    // Кэшируем важные API эндпоинты
                    cache.add('/users');
                });
            }
        }
    });
</script>
@endsection
