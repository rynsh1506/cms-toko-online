document.getElementById('menu-toggle')?.addEventListener('click', function() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('w-full');
            sidebar.classList.toggle('absolute');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('h-screen');
        });

        // Theme toggle logic
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleSun = document.getElementById('theme-toggle-sun');
        const themeToggleMoon = document.getElementById('theme-toggle-moon');

        if (document.documentElement.classList.contains('dark')) {
            themeToggleSun.classList.remove('hidden');
        } else {
            themeToggleMoon.classList.remove('hidden');
        }

        themeToggleBtn.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                themeToggleSun.classList.add('hidden');
                themeToggleMoon.classList.remove('hidden');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                themeToggleMoon.classList.add('hidden');
                themeToggleSun.classList.remove('hidden');
            }
        });
