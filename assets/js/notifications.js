// assets/js/notifications.js
document.addEventListener('DOMContentLoaded', function () {
    const badge = document.getElementById('notif-badge');

    function checkNotifications() {
        if (!badge) return; // Not logged in or no badge present

        fetch('/city/pages/notifications/get_count.php')
            .then(response => response.json())
            .then(data => {
                const count = data.count;
                if (count > 0) {
                    badge.style.display = 'inline-block';
                    badge.innerText = count;
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    // Poll every 10 seconds
    setInterval(checkNotifications, 10000);
});
