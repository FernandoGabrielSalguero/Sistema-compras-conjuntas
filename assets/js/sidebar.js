function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    
    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        content.classList.remove('collapsed');
    } else {
        sidebar.classList.add('collapsed');
        content.classList.add('collapsed');
    }
}
