<script>
    // --- Sidebar (mobile) ---
    const sidebar = document.getElementById('sidebar');
    const btnSidebar = document.getElementById('btnSidebar');
    const backdrop = document.getElementById('backdrop');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }
    btnSidebar?.addEventListener('click', () => {
        const hidden = sidebar.classList.contains('-translate-x-full');
        hidden ? openSidebar() : closeSidebar();
    });
    backdrop?.addEventListener('click', closeSidebar);

    // --- Profile dropdown ---
    const btnProfile = document.getElementById('btnProfile');
    const profileMenu = document.getElementById('profileMenu');
    btnProfile?.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.classList.toggle('hidden');
    });
    document.addEventListener('click', () => profileMenu.classList.add('hidden'));
</script>

@stack('page-scripts')
