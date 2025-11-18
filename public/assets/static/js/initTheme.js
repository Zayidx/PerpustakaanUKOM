if (!window.__initThemeScriptLoaded) {
  window.__initThemeScriptLoaded = true

  const body = document.body
  const theme = localStorage.getItem('theme')

  if (theme) {
    document.documentElement.setAttribute('data-bs-theme', theme)
  }
}
