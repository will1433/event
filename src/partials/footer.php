<script src="https://unpkg.com/feather-icons"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // 1) Render all feather icons
    if (window.feather) {
      feather.replace();
    }

    // --- User dropdown ---
    const userBtn   = document.getElementById('userMenuBtn');
    const userMenu  = document.getElementById('userMenuDropdown');

    if (userBtn && userMenu) {
      userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        userMenu.classList.toggle('show');
      });
    }

    // --- Notification dropdown ---
    const notifBtn      = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');

    if (notifBtn && notifDropdown) {
      notifBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        notifDropdown.classList.toggle('show');
      });
    }

    // --- Close dropdowns when clicking outside ---
    document.addEventListener('click', function (e) {
      if (userMenu && userBtn &&
          !userMenu.contains(e.target) &&
          e.target !== userBtn &&
          !userBtn.contains(e.target)) {
        userMenu.classList.remove('show');
      }

      if (notifDropdown && notifBtn &&
          !notifDropdown.contains(e.target) &&
          e.target !== notifBtn &&
          !notifBtn.contains(e.target)) {
        notifDropdown.classList.remove('show');
      }
    });
  });
</script>
