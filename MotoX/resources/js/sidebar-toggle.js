document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar = document.getElementById('sidebar');
  
  toggleBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    sidebar.classList.toggle('hidden');
  });
  
  document.addEventListener('click', function(e) {
    if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
      sidebar.classList.add('hidden');
    }
  });
});
