# Projects and Tasks Controllers Optimization Plan

## Current State Analysis

### ProjectsController
- **Size**: 4,076 lines
- **Methods**: 49 public methods
- **Issues**:
  - Massive controller with multiple responsibilities
  - Business logic embedded in controller
  - Complex query logic scattered throughout
  - Validation logic mixed with business logic
  - No separation of concerns

### TasksController
- **Size**: 3,026 lines
- **Methods**: 36 public methods
- **Issues**:
  - Similar issues as ProjectsController
  - Duplicated patterns with Projects
  - Complex filtering logic
  - No service layer

## Optimization Strategy

Following the same pattern as HomeController optimization, we'll:
1. Extract business logic to Services
2. Create Query Services for complex queries
3. Create Form Requests for validation
4. Keep controllers thin and focused on HTTP concerns

## Phase 1: Create Service Layer

### 1.1 Project Services

#### ProjectService (`app/Services/ProjectService.php`)
**Responsibilities:**
- Project CRUD operations (create, update, delete)
- Project relationship management (users, clients, tags)
- Project business logic (duplicate, favorite, pin)
- Project status/priority updates
- Project date management

**Methods:**
- `createProject(array $data, array $userIds, array $clientIds, array $tagIds): Project`
- `updateProject(Project $project, array $data, array $userIds, array $clientIds, array $tagIds): Project`
- `deleteProject(Project $project): bool`
- `duplicateProject(Project $project): Project`
- `updateFavorite(Project $project, bool $isFavorite): void`
- `updatePinned(Project $project, bool $isPinned): void`
- `updateStatus(Project $project, Status $status): void`
- `updatePriority(Project $project, Priority $priority): void`
- `updateDates(Project $project, array $dates): void`

#### ProjectQueryService (`app/Services/ProjectQueryService.php`)
**Responsibilities:**
- Complex project queries and filtering
- List/API list queries with all filters
- Permission-based query building
- Date range filtering
- Tag filtering
- Status filtering
- User/Client filtering

**Methods:**
- `getFilteredProjects(Workspace $workspace, User $user, array $filters): Collection`
- `getProjectList(Workspace $workspace, User $user, array $filters): array`
- `getProjectApiList(Workspace $workspace, User $user, array $filters): array`
- `buildProjectQuery(Workspace $workspace, User $user, array $filters): Builder`

#### ProjectMediaService (`app/Services/ProjectMediaService.php`)
**Responsibilities:**
- Media upload handling
- Media retrieval
- Media deletion
- Bulk media operations

**Methods:**
- `uploadMedia(Project $project, Request $request): Media`
- `getMedia(Project $project): Collection`
- `deleteMedia(Media $media): bool`
- `deleteMultipleMedia(array $mediaIds): void`

#### ProjectCommentService (`app/Services/ProjectCommentService.php`)
**Responsibilities:**
- Comment creation/updating/deletion
- Comment attachment handling
- Comment retrieval

**Methods:**
- `createComment(Project $project, array $data, array $attachments = []): Comment`
- `updateComment(Comment $comment, array $data): Comment`
- `deleteComment(Comment $comment): bool`
- `getComments(Project $project): Collection`

#### ProjectMilestoneService (`app/Services/ProjectMilestoneService.php`)
**Responsibilities:**
- Milestone CRUD operations
- Milestone retrieval

**Methods:**
- `createMilestone(Project $project, array $data): Milestone`
- `updateMilestone(Milestone $milestone, array $data): Milestone`
- `deleteMilestone(Milestone $milestone): bool`
- `getMilestones(Project $project): Collection`

### 1.2 Task Services

#### TaskService (`app/Services/TaskService.php`)
**Responsibilities:**
- Task CRUD operations
- Task relationship management (users, project)
- Task business logic (duplicate, favorite, pin)
- Task status/priority updates
- Task date management

**Methods:**
- `createTask(array $data, array $userIds): Task`
- `updateTask(Task $task, array $data, array $userIds): Task`
- `deleteTask(Task $task): bool`
- `duplicateTask(Task $task): Task`
- `updateFavorite(Task $task, bool $isFavorite): void`
- `updatePinned(Task $task, bool $isPinned): void`
- `updateStatus(Task $task, Status $status): void`
- `updatePriority(Task $task, Priority $priority): void`
- `updateDates(Task $task, array $dates): void`

#### TaskQueryService (`app/Services/TaskQueryService.php`)
**Responsibilities:**
- Complex task queries and filtering
- List/API list queries with all filters
- Permission-based query building
- Date range filtering
- Status/Priority filtering
- User/Project/Client filtering

**Methods:**
- `getFilteredTasks(Workspace $workspace, User $user, array $filters): Collection`
- `getTaskList(Workspace $workspace, User $user, array $filters): array`
- `getTaskApiList(Workspace $workspace, User $user, array $filters): array`
- `buildTaskQuery(Workspace $workspace, User $user, array $filters): Builder`

#### TaskMediaService (`app/Services/TaskMediaService.php`)
**Responsibilities:**
- Media upload handling
- Media retrieval
- Media deletion
- Bulk media operations

**Methods:**
- `uploadMedia(Task $task, Request $request): Media`
- `getMedia(Task $task): Collection`
- `deleteMedia(Media $media): bool`
- `deleteMultipleMedia(array $mediaIds): void`

#### TaskCommentService (`app/Services/TaskCommentService.php`)
**Responsibilities:**
- Comment creation/updating/deletion
- Comment attachment handling
- Comment retrieval

**Methods:**
- `createComment(Task $task, array $data, array $attachments = []): Comment`
- `updateComment(Comment $comment, array $data): Comment`
- `deleteComment(Comment $comment): bool`
- `getComments(Task $task): Collection`

## Phase 2: Create Form Request Classes

### 2.1 Project Form Requests

- `app/Http/Requests/Project/StoreProjectRequest.php`
- `app/Http/Requests/Project/UpdateProjectRequest.php`
- `app/Http/Requests/Project/MilestoneRequest.php`
- `app/Http/Requests/Project/ProjectFilterRequest.php`

### 2.2 Task Form Requests

- `app/Http/Requests/Task/StoreTaskRequest.php`
- `app/Http/Requests/Task/UpdateTaskRequest.php`
- `app/Http/Requests/Task/TaskFilterRequest.php`

## Phase 3: Create Query Builder Abstractions

### 3.1 Shared Query Builders

- `app/QueryBuilders/BaseQueryBuilder.php` - Base class with common methods
- `app/QueryBuilders/DateRangeQueryBuilder.php` - Date filtering utilities
- `app/QueryBuilders/StatusQueryBuilder.php` - Status filtering
- `app/QueryBuilders/PriorityQueryBuilder.php` - Priority filtering
- `app/QueryBuilders/UserQueryBuilder.php` - User filtering
- `app/QueryBuilders/ClientQueryBuilder.php` - Client filtering
- `app/QueryBuilders/TagQueryBuilder.php` - Tag filtering

## Phase 4: Refactor Controllers

### 4.1 ProjectsController Refactoring

**Target Structure:**
- Thin controller methods (5-30 lines each)
- All business logic delegated to services
- Validation via Form Requests
- Query logic in QueryService
- Media operations in MediaService
- Comment operations in CommentService

**Methods to Refactor:**
1. `index()` → Use ProjectQueryService
2. `store()` → Use ProjectService + StoreProjectRequest
3. `update()` → Use ProjectService + UpdateProjectRequest
4. `destroy()` → Use ProjectService
5. `list()` → Use ProjectQueryService
6. `apiList()` → Use ProjectQueryService
7. Media methods → Use ProjectMediaService
8. Comment methods → Use ProjectCommentService
9. Milestone methods → Use ProjectMilestoneService
10. Other methods → Delegate to appropriate services

### 4.2 TasksController Refactoring

**Target Structure:**
- Similar to ProjectsController
- Thin controller methods
- Service-based architecture

**Methods to Refactor:**
1. `index()` → Use TaskQueryService
2. `store()` → Use TaskService + StoreTaskRequest
3. `update()` → Use TaskService + UpdateTaskRequest
4. `destroy()` → Use TaskService
5. `list()` → Use TaskQueryService
6. `apiList()` → Use TaskQueryService
7. Media methods → Use TaskMediaService
8. Comment methods → Use TaskCommentService
9. Other methods → Delegate to appropriate services

## Phase 5: Extract Common Patterns

### 5.1 Create Traits

- `app/Http/Controllers/Concerns/HasMediaOperations.php` - Shared media operations
- `app/Http/Controllers/Concerns/HasCommentOperations.php` - Shared comment operations
- `app/Http/Controllers/Concerns/HasFavoriteOperations.php` - Favorite/pin operations
- `app/Http/Controllers/Concerns/HasStatusOperations.php` - Status update operations
- `app/Http/Controllers/Concerns/HasPriorityOperations.php` - Priority update operations

## Implementation Order

1. **Phase 1** - Create all Services (Foundation)
   - Start with ProjectService and TaskService
   - Then Query Services
   - Then Media and Comment Services
   - Finally Milestone Service

2. **Phase 2** - Create Form Requests (Validation layer)

3. **Phase 3** - Create Query Builders (Reusable query logic)

4. **Phase 4** - Refactor Controllers (Use services)
   - Start with CRUD operations
   - Then list/filter operations
   - Then media/comment operations
   - Finally remaining methods

5. **Phase 5** - Extract Traits (Code reusability)

## Expected Results

### ProjectsController
- **Before**: 4,076 lines, 49 methods
- **After**: ~800-1,000 lines, 49 methods (all thin delegators)
- **Reduction**: ~75% reduction in controller complexity

### TasksController
- **Before**: 3,026 lines, 36 methods
- **After**: ~600-800 lines, 36 methods (all thin delegators)
- **Reduction**: ~75% reduction in controller complexity

## Benefits

1. **Maintainability**: Business logic separated from HTTP layer
2. **Testability**: Services can be unit tested independently
3. **Reusability**: Services can be used across multiple controllers
4. **Consistency**: Similar patterns to HomeController optimization
5. **Scalability**: Easier to add new features
6. **Code Quality**: Follows Laravel best practices

## Notes

- This is a large refactoring, should be done incrementally
- Test thoroughly after each phase
- Maintain backward compatibility
- Follow the same patterns established in HomeController refactoring

