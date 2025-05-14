$(document).ready(function () {
    // Get all todo list containers as DOM elements
    let todoListContainers = document.querySelectorAll(".todo-list-container");

    // Initialize Sortable on each container with shared group
    todoListContainers.forEach(function (container) {
        new Sortable(container, {
            handle: ".todo-drag-handle", // Dragging allowed only on the move icon
            animation: 150,
            group: "todos", // Shared group name allows dragging between containers
            onEnd: function (evt) {
                // This function runs when an item is dropped
                const item = evt.item; // The dragged item
                const from = evt.from; // Source list
                const to = evt.to; // Destination list

                // Check if the todo was moved between different lists
                if (from !== to) {
                    // Get the todo ID from the item
                    const todoId = $(item).data('todo-id');

                    // If item was moved to completed list
                    if ($(to).closest('.todo-card').find('.todo-card-header').hasClass('todo-gradient-success')) {
                        // Add completed class to the item
                        $(item).addClass('todo-completed');

                        // Update the checkbox
                        $(item).find('.todo-check-input').prop('checked', true);

                        // Replace priority badge with completed tag
                        const metaContainer = $(item).find('.todo-meta');
                        metaContainer.find('.todo-priority-badge').replaceWith(
                            '<span class="todo-completed-tag"><i class="bx bx-check-double me-1"></i>' +
                            'Completed</span>'
                        );

                        // Here you would typically make an AJAX call to update the database
                        updateTodoStatus(todoId, true);
                    }
                    // If item was moved to incomplete list
                    else {
                        // Remove completed class
                        $(item).removeClass('todo-completed');


                        // Uncheck the checkbox with slight delay
                        setTimeout(() => {
                            $(item).find('.todo-check-input').prop('checked', false);
                        }, 10);
                        // Get priority from data attribute
                        const priority = $(item).hasClass('todo-priority-high') ? 'high' :
                            ($(item).hasClass('todo-priority-medium') ? 'medium' : 'low');

                        // Get proper color class based on priority
                        const colorClass = priority === 'high' ? 'danger' :
                            (priority === 'medium' ? 'warning' : 'success');

                        // Replace completed tag with priority badge
                        const metaContainer = $(item).find('.todo-meta');
                        metaContainer.find('.todo-completed-tag').replaceWith(
                            '<span class="todo-priority-badge todo-bg-' + colorClass + '-subtle">' +
                            priority.charAt(0).toUpperCase() + priority.slice(1) +
                            '</span>'
                        );

                        // Here you would typically make an AJAX call to update the database
                        updateTodoStatus(todoId, false);
                    }

                    // Update the counter on both containers
                    updateCounters();
                }
            }
        });
    });

    // Function to update the counters after drag and drop
    function updateCounters() {
        const incompleteContainer = document.querySelector('.todo-gradient-primary').closest('.todo-card').querySelector('.todo-list-container');
        const completeContainer = document.querySelector('.todo-gradient-success').closest('.todo-card').querySelector('.todo-list-container');

        // Count todos
        const incompleteCount = incompleteContainer.querySelectorAll('.todo-item').length;
        const completeCount = completeContainer.querySelectorAll('.todo-item').length;
        const totalCount = incompleteCount + completeCount;

        // Update counters
        document.querySelector('.todo-gradient-primary').closest('.todo-card-header').querySelector('.todo-counter').textContent = incompleteCount;
        document.querySelector('.todo-gradient-success').closest('.todo-card-header').querySelector('.todo-counter').textContent = completeCount;

        // Calculate progress
        let progress = totalCount > 0 ? (completeCount / totalCount) * 100 : 0;
        progress = progress.toFixed(2); // same formatting as PHP

        // Update progress text and bar
        $('.todo-progress-value').text(`${completeCount} / ${totalCount} (${progress}%)`);
        $('.progress-bar').css('width', `${progress}%`);
        $('.progress-bar').attr('aria-valuenow', progress);
    }


    // Function to send AJAX request to update todo status
    function updateTodoStatus(todoId, isCompleted) {
        $.ajax({
            url: '/todos/update_status',  // Replace with your route
            type: 'PUT',
            data: {
                id: todoId,
                status: isCompleted ? 1 : 0,
                _token: $('meta[name="csrf-token"]').attr('content')  // Laravel CSRF token
            },
            success: function (response) {
                if (response.error == false) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                console.error('Error updating todo status:', xhr.responseText);
            }
        });
    }
});
