
function fetchNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationMenu = document.getElementById('notification-menu');
            const notificationCount = document.getElementById('notification-count');
            
            notificationMenu.innerHTML = '';
            let unreadCount = 0;

            data.forEach(notification => {
                const notificationItem = document.createElement('a');
                notificationItem.classList.add('dropdown-item');
                notificationItem.href = '#';
                notificationItem.textContent = notification.message;

                if (!notification.is_read) {
                    notificationItem.classList.add('font-weight-bold');
                    unreadCount++;
                }

                notificationItem.addEventListener('click', (e) => {
                    e.preventDefault();
                    markNotificationAsRead(notification.id);
                });

                notificationMenu.appendChild(notificationItem);
            });

            if (data.length === 0) {
                const noNotifications = document.createElement('span');
                noNotifications.classList.add('dropdown-item');
                noNotifications.textContent = 'No notifications';
                notificationMenu.appendChild(noNotifications);
            }

            notificationCount.textContent = unreadCount > 0 ? unreadCount : '';
        });
}

function markNotificationAsRead(notificationId) {
    fetch('mark_notification_as_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notification_id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchNotifications();
        }
    });
}

// Fetch notifications every 30 seconds
setInterval(fetchNotifications, 30000);

// Initial fetch
fetchNotifications();
