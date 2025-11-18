if (!window.__darkThemeScriptLoaded) {
  window.__darkThemeScriptLoaded = true

  const THEME_KEY = "theme"

  function toggleDarkTheme() {
    setTheme(
      document.documentElement.getAttribute("data-bs-theme") === 'dark'
        ? "light"
        : "dark"
    )
  }

  function setTheme(theme, persist = false) {
    document.body.classList.remove('light', 'dark')
    document.body.classList.add(theme)
    document.documentElement.setAttribute('data-bs-theme', theme)
    
    if (persist) {
      localStorage.setItem(THEME_KEY, theme)
    }
  }

  window.setTheme = setTheme
  window.toggleDarkTheme = toggleDarkTheme

  function initTheme() {
    const storedTheme = localStorage.getItem(THEME_KEY)
    if (storedTheme) {
      return setTheme(storedTheme)
    }
    if (!window.matchMedia) {
      return
    }

    const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)")

    mediaQuery.addEventListener("change", (e) =>
      setTheme(e.matches ? "dark" : "light", true)
    )
    return setTheme(mediaQuery.matches ? "dark" : "light", true)
  }

  window.addEventListener('DOMContentLoaded', () => {
    const toggler = document.getElementById("toggle-dark")
    const theme = localStorage.getItem(THEME_KEY)

    if(toggler) {
      toggler.checked = theme === "dark"
      
      toggler.addEventListener("input", (e) => {
        setTheme(e.target.checked ? "dark" : "light", true)
      })
    }
  });

  initTheme()
}
