<<<<<<< HEAD
</div> <!-- end .layout -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1) Render all feather icons on the page
  if (window.feather) {
    feather.replace();
  }

  // 2) Dropdown behavior
  const btn  = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userMenuDropdown');

  if (!btn || !menu) return;

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    menu.classList.toggle('show');
  });

  document.addEventListener('click', function () {
    menu.classList.remove('show');
  });
});
</script>



</body>
</html>
=======
</div> <!-- end .layout -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1) Render all feather icons on the page
  if (window.feather) {
    feather.replace();
  }

  // 2) Dropdown behavior
  const btn  = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userMenuDropdown');

  if (!btn || !menu) return;

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    menu.classList.toggle('show');
  });

  document.addEventListener('click', function () {
    menu.classList.remove('show');
  });
});
</script>



</body>
</html>
>>>>>>> 47c52acb341e02dac2adcee5692bda62f4bb8533
