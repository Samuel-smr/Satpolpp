document.addEventListener('DOMContentLoaded', () => {
    const searchRoot = document.querySelector('[data-global-search]');

    if (searchRoot) {
        const input = searchRoot.querySelector('[data-global-search-input]');
        const results = searchRoot.querySelector('[data-global-search-results]');
        let searchTimer;
        let searchRequest;

        const showMessage = (message) => {
            results.replaceChildren();
            const item = document.createElement('p');
            item.className = 'px-4 py-3 text-sm text-slate-500';
            item.textContent = message;
            results.append(item);
            results.classList.remove('hidden');
        };

        input.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchRequest?.abort();
            const query = input.value.trim();

            if (query.length < 2) {
                results.classList.add('hidden');
                results.replaceChildren();
                return;
            }

            searchTimer = setTimeout(async () => {
                searchRequest = new AbortController();

                try {
                    const response = await fetch(`${searchRoot.dataset.searchUrl}?q=${encodeURIComponent(query)}`, {
                        headers: { Accept: 'application/json' },
                        signal: searchRequest.signal,
                    });
                    if (!response.ok) throw new Error('Pencarian gagal.');
                    const payload = await response.json();
                    results.replaceChildren();

                    if (payload.results.length === 0) {
                        showMessage('Data tidak ditemukan.');
                        return;
                    }

                    payload.results.forEach((result) => {
                        const link = document.createElement('a');
                        link.href = result.url;
                        link.className = 'block px-4 py-3 transition-colors hover:bg-slate-50';
                        const type = document.createElement('span');
                        type.className = 'text-[10px] font-bold uppercase tracking-wide text-blue-600';
                        type.textContent = result.type;
                        const title = document.createElement('span');
                        title.className = 'block text-sm font-semibold text-slate-800';
                        title.textContent = result.title;
                        const description = document.createElement('span');
                        description.className = 'block truncate text-xs text-slate-500';
                        description.textContent = result.description || '-';
                        link.append(type, title, description);
                        results.append(link);
                    });
                    results.classList.remove('hidden');
                } catch (error) {
                    if (error.name !== 'AbortError') showMessage('Pencarian tidak dapat dimuat.');
                }
            }, 300);
        });

        document.addEventListener('click', (event) => {
            if (!searchRoot.contains(event.target)) results.classList.add('hidden');
        });
    }

    const notificationButton = document.querySelector('[data-notification-button]');
    const notificationList = document.querySelector('[data-notification-list]');

    if (!notificationButton || !notificationList) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let previousUnreadCount = 0;
    let notificationsInitialized = false;

    const refreshNotifications = async () => {
        if (document.hidden) return;

        try {
            const response = await fetch(notificationButton.dataset.notificationsUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const payload = await response.json();
            let badge = document.querySelector('[data-notification-badge]');

            if (payload.unread_count > 0 && !badge) {
                badge = document.createElement('span');
                badge.dataset.notificationBadge = '';
                badge.className = 'absolute right-1 top-1 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white';
                notificationButton.append(badge);
            } else if (payload.unread_count === 0) {
                badge?.remove();
            }

            notificationList.replaceChildren();
            if (payload.notifications.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'px-4 py-10 text-center text-sm text-slate-400';
                empty.textContent = 'Tidak ada notifikasi.';
                notificationList.append(empty);
            }

            payload.notifications.forEach((notification) => {
                if (notification.asset) {
                    const item = document.createElement('div');
                    item.className = `flex w-full items-start gap-3 px-4 py-3 ${notification.level === 'danger' ? 'bg-red-50/60' : 'bg-amber-50/60'}`;
                    const icon = document.createElement('span');
                    icon.className = `material-symbols-outlined flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[18px] ${notification.level === 'danger' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'}`;
                    icon.textContent = 'warning';
                    const content = document.createElement('span');
                    content.className = 'min-w-0 flex-1';
                    const title = document.createElement('span');
                    title.className = 'block text-sm font-medium text-slate-700';
                    title.textContent = notification.title;
                    const message = document.createElement('span');
                    message.className = 'block text-xs text-slate-500';
                    message.textContent = notification.message;
                    const time = document.createElement('span');
                    time.className = 'text-xs text-slate-400';
                    time.textContent = notification.time || 'Perlu tindakan';
                    content.append(title, message, time);
                    const dot = document.createElement('span');
                    dot.className = 'mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500';
                    item.append(icon, content, dot);
                    notificationList.append(item);
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/notifications/${encodeURIComponent(notification.id)}/read`;
                const token = document.createElement('input');
                token.type = 'hidden'; token.name = '_token'; token.value = csrfToken;
                const button = document.createElement('button');
                button.className = `flex w-full items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-50 ${notification.unread ? 'bg-orange-50/60' : ''}`;
                const content = document.createElement('span');
                content.className = 'min-w-0 flex-1';
                [notification.title, notification.message, notification.time].forEach((text, index) => {
                    const line = document.createElement('span');
                    line.className = index === 0 ? 'block text-sm font-medium text-slate-700' : 'block text-xs text-slate-500';
                    line.textContent = text;
                    content.append(line);
                });
                button.append(content);
                if (notification.unread) {
                    const dot = document.createElement('span');
                    dot.className = 'mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500';
                    button.append(dot);
                }
                form.append(token, button);
                notificationList.append(form);
            });

            if (notificationsInitialized && payload.notifications.some((n) => !n.asset) && payload.unread_count > previousUnreadCount) {
                window.tampilToast?.('Ada laporan baru yang masuk.', 'info', 'Notifikasi Baru');
            }
            previousUnreadCount = payload.unread_count;
            notificationsInitialized = true;
        } catch (_) {
            // Kegagalan polling tidak mengganggu penggunaan halaman.
        }
    };

    refreshNotifications();
    window.setInterval(refreshNotifications, 15000);
});
