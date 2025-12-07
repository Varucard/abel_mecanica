(function () {
  const body = document.body;
  const btn  = document.getElementById('btnDarkMode');

  // Cargar preferencia guardada
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    body.classList.add('dark-mode');
  }

  function updateButtonText() {
    const isDark = body.classList.contains('dark-mode');
    if (btn) {
      btn.textContent = isDark ? '☀ Modo claro' : '🌙 Modo oscuro';
      // Si quisieras usar iconos, podrías usar innerHTML en vez de textContent
      // btn.innerHTML = isDark ? '<i class="fas fa-sun"></i> Modo claro' : '<i class="fas fa-moon"></i> Modo oscuro';
    }
  }

  // Actualiza el texto al cargar la página
  updateButtonText();

  if (btn) {
    btn.addEventListener('click', function () {
      body.classList.toggle('dark-mode');
      const isDark = body.classList.contains('dark-mode');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      updateButtonText();
    });
  }
})();
